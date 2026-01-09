<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Services\MediaStorageService;

class MediaService
{
    protected $ffmpeg = null;
    protected $imageManager;
    protected $mediaStorage;

    public function __construct(MediaStorageService $mediaStorage)
    {
        // Initialize Image Manager for Intervention Image v3
        $this->imageManager = new ImageManager(new Driver());
        $this->mediaStorage = $mediaStorage;
    }

    private function resolveWatermarkFontPath(): ?string
    {
        $candidates = [
            storage_path('app/public/ttf/DejaVuSans-Bold.ttf'),
            resource_path('fonts/DejaVuSans-Bold.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/Library/Fonts/Arial.ttf',
            '/System/Library/Fonts/Supplemental/Arial.ttf',
        ];

        foreach ($candidates as $path) {
            if ($path && file_exists($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Add watermark to an image
     */
    public function addImageWatermark($imagePath, $watermarkText, $position = 'bottom-right')
    {
        try {
            Log::info('Adding watermark to image', ['image_path' => $imagePath, 'watermark_text' => $watermarkText]);
            
            // Create image instance using v3 syntax
            $image = $this->imageManager->read($imagePath);
            
            // Calculate text size and position
            $fontSize = max(24, min(40, $image->width() / 15)); // Larger, responsive font size
            $padding = 10;
            
            // Determine position coordinates
            switch ($position) {
                case 'top-left':
                    $x = $padding;
                    $y = $padding + $fontSize;
                    $align = 'left';
                    $valign = 'top';
                    break;
                case 'top-right':
                    $x = $image->width() - $padding;
                    $y = $padding + $fontSize;
                    $align = 'right';
                    $valign = 'top';
                    break;
                case 'bottom-left':
                    $x = $padding;
                    $y = $image->height() - $padding;
                    $align = 'left';
                    $valign = 'bottom';
                    break;
                case 'bottom-right':
                default:
                    $x = $image->width() - $padding;
                    $y = $image->height() - $padding;
                    $align = 'right';
                    $valign = 'bottom';
                    break;
            }

            // Resolve a usable font; if none, skip watermark to avoid failing uploads.
            $fontPath = $this->resolveWatermarkFontPath();
            if (!$fontPath) {
                Log::warning('Watermark font not found; skipping image watermark', [
                    'image_path' => $imagePath,
                    'watermark_text' => $watermarkText,
                ]);
                return $imagePath;
            }
            Log::info('Using font for watermark', ['fontPath' => $fontPath]);

            $image->text($watermarkText, $x, $y, function($font) use ($fontPath, $fontSize, $align, $valign) {
                $font->file($fontPath);
                $font->size($fontSize);
                $font->color('#ffffff');
                $font->align($align);
                $font->valign($valign);
            });
            Log::info('Watermark text drawn', ['text' => $watermarkText]);

            // Save the watermarked image (overwrite original)
            $image->save($imagePath);
            
            Log::info('Watermark added successfully', ['image_path' => $imagePath]);
            
            return $imagePath;
        } catch (\Exception $e) {
            Log::error('Error adding image watermark: ' . $e->getMessage(), [
                'image_path' => $imagePath,
                'watermark_text' => $watermarkText,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Add watermark to a video
     */
    public function addVideoWatermark($videoPath, $watermarkText)
    {
        try {
            Log::info('Adding watermark to video', ['video_path' => $videoPath, 'watermark_text' => $watermarkText]);
            
            // Initialize FFMpeg only when needed
            if (!$this->ffmpeg) {
                try {
                    $this->ffmpeg = \FFMpeg\FFMpeg::create([
                        'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
                        'ffprobe.binaries' => '/usr/bin/ffprobe',
                        'timeout'          => 3600,
                        'ffmpeg.threads'   => 12,
                    ]);
                } catch (\Exception $e) {
                    Log::warning('FFMpeg not available for video watermarking: ' . $e->getMessage());
                    return $videoPath; // Return original video without watermark
                }
            }
            
            // Open the video
            $video = $this->ffmpeg->open($videoPath);

            // Create a temporary image for the watermark using v3 syntax
            $watermarkImage = $this->imageManager->create(200, 50)
                ->fill([0, 0, 0, 0.5])
                ->text($watermarkText, 100, 25, function($font) {
                    $font->size(20);
                    $font->color('#ffffff');
                    $font->align('center');
                    $font->valign('middle');
                });

            $watermarkPath = storage_path('app/temp/watermark.png');
            
            // Ensure temp directory exists
            if (!file_exists(dirname($watermarkPath))) {
                mkdir(dirname($watermarkPath), 0755, true);
            }
            
            $watermarkImage->save($watermarkPath);

            // Add watermark to video
            $watermarkedPath = str_replace('.', '_watermarked.', $videoPath);
            
            $video->filters()
                ->watermark($watermarkPath, [
                    'position' => 'relative',
                    'bottom' => 10,
                    'right' => 10,
                ]);

            // Save the watermarked video
            $format = new \FFMpeg\Format\Video\X264();
            $format->setKiloBitrate(1000);
            
            $video->save($format, $watermarkedPath);

            // Clean up temporary watermark image
            if (file_exists($watermarkPath)) {
                unlink($watermarkPath);
            }

            // Replace original with watermarked version
            if (file_exists($watermarkedPath)) {
                unlink($videoPath);
                rename($watermarkedPath, $videoPath);
            }
            
            Log::info('Video watermark added successfully', ['video_path' => $videoPath]);
            
            return $videoPath;
        } catch (\Exception $e) {
            Log::error('Error adding video watermark: ' . $e->getMessage(), [
                'video_path' => $videoPath,
                'watermark_text' => $watermarkText,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Process uploaded media with watermark support
     */
    public function processMedia($file, $watermarkText = null, $type = 'image')
    {
        try {
            Log::info('Processing media with watermark', [
                'type' => $type,
                'watermark_text' => $watermarkText,
                'original_name' => $file->getClientOriginalName()
            ]);

            if (!$file instanceof UploadedFile) {
                throw new \InvalidArgumentException('MediaService::processMedia expects an UploadedFile');
            }

            $directory = 'media/' . $type . 's';
            $tmpDir = storage_path('app/tmp/media');
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }

            $tmpFilename = $file->hashName();
            $tmpPath = $file->move($tmpDir, $tmpFilename)->getPathname();

            Log::info('File stored successfully', [
                'tmp_path' => $tmpPath
            ]);
            
            // Apply watermark if text is provided and type is image
            if ($watermarkText && $type === 'image') {
                Log::info('Applying watermark to image', ['path' => $tmpPath, 'watermark' => $watermarkText]);
                $this->addImageWatermark($tmpPath, $watermarkText);
                Log::info('Watermark applied successfully');
            } elseif ($watermarkText && $type === 'video') {
                Log::info('Applying watermark to video', ['path' => $tmpPath, 'watermark' => $watermarkText]);
                $this->addVideoWatermark($tmpPath, $watermarkText);
                Log::info('Video watermark applied successfully');
            }

            $path = $this->mediaStorage->storeUploadedFile($directory, new File($tmpPath));
            $publicUrl = $this->mediaStorage->url($path);

            if (file_exists($tmpPath)) {
                unlink($tmpPath);
            }
            
            Log::info('Media processing completed', [
                'type' => $type,
                'path' => $path,
                'public_url' => $publicUrl,
                'watermark_applied' => !empty($watermarkText)
            ]);
            
            return $path;
        } catch (\Exception $e) {
            Log::error('Error processing media: ' . $e->getMessage(), [
                'type' => $type,
                'watermark_text' => $watermarkText,
                'original_name' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
} 
