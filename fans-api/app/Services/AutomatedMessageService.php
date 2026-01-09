<?php

namespace App\Services;

use App\Models\AutomatedMessage;
use App\Models\Media;
use App\Models\User;
use App\Models\PermissionSet;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\MediaStorageService;

class AutomatedMessageService
{
    protected $vaultService;
    protected $permissionService;
    protected $mediaService;
    protected $mediaStorage;

    public function __construct(
        VaultService $vaultService,
        PermissionService $permissionService,
        MediaService $mediaService,
        MediaStorageService $mediaStorage
    ) {
        $this->vaultService = $vaultService;
        $this->permissionService = $permissionService;
        $this->mediaService = $mediaService;
        $this->mediaStorage = $mediaStorage;
    }

    public function getMessages()
    {
        $user = auth()->user();
        return AutomatedMessage::where('user_id', $user->id)
            ->with(['media', 'permissionSets.permissions'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createMessage(array $data)
    {
        Log::info('Creating automated message', ['data' => array_diff_key($data, ['media' => null])]);
        
        $user = auth()->user();
        
        return DB::transaction(function () use ($user, $data) {
            try {
                // Process media files similar to MessageService
                Log::info('Starting media processing');
                $processedData = $this->processMediaFiles($data);
                Log::info('Media processing completed', ['processed_data_keys' => array_keys($processedData)]);
                
                // Create the automated message
                Log::info('Creating automated message record');
                $message = AutomatedMessage::create([
                    'user_id' => $user->id,
                    'trigger' => $data['trigger'],
                    'content' => $data['content'],
                    'sent_delay' => $data['sent_delay'] ?? 0,
                    'cooldown' => $data['cooldown'] ?? 0,
                    'is_active' => $data['is_active'] ?? true,
                ]);
                
                Log::info('Created automated message', ['id' => $message->id]);
                
                // Handle media attachments
                if (isset($processedData['media']) && is_array($processedData['media'])) {
                    Log::info('Processing ' . count($processedData['media']) . ' media attachments');
                    
                    foreach ($processedData['media'] as $index => $mediaItem) {
                        Log::info('Processing media item ' . ($index + 1), [
                            'type' => $mediaItem['type'] ?? 'unknown',
                            'has_url' => isset($mediaItem['url'])
                        ]);
                        
                        $media = Media::create([
                            'user_id' => $user->id,
                            'mediable_id' => $message->id,
                            'mediable_type' => AutomatedMessage::class,
                            'type' => $mediaItem['type'] ?? 'image',
                            'url' => $mediaItem['url'],
                        ]);
                        
                        Log::info('Created media', [
                            'media_id' => $media->id,
                            'type' => $media->type,
                            'url' => $media->url
                        ]);
                        
                        // Create preview versions if they exist
                        if (isset($mediaItem['previews']) && is_array($mediaItem['previews'])) {
                            Log::info('Creating ' . count($mediaItem['previews']) . ' preview versions');
                            
                            foreach ($mediaItem['previews'] as $previewIndex => $previewUrl) {
                                $media->previews()->create([
                                    'url' => $previewUrl
                                ]);
                                Log::info('Preview created', [
                                    'media_id' => $media->id, 
                                    'preview_index' => $previewIndex,
                                    'preview_url' => $previewUrl
                                ]);
                            }
                        }
                        
                        // Add media to 'All' and 'AutomatedMessages' albums in Vault
                        Log::info('Adding media to vault albums');
                        $this->vaultService->addMediaToAlbum($media, 'All', $user);
                        $this->vaultService->addMediaToAlbum($media, 'AutomatedMessages', $user);
                        Log::info('Added media to albums');
                    }
                }
                
                // Handle permissions if they exist
                if (isset($data['permissions'])) {
                    Log::info('Processing permissions');
                    // Parse the permissions if it's a string (JSON)
                    $permissions = $data['permissions'];
                    if (is_string($permissions)) {
                        $permissions = json_decode($permissions, true);
                    }
                    
                    // Ensure we have a valid array before proceeding
                    if (is_array($permissions)) {
                        Log::info('Creating message permissions');
                        $this->permissionService->createPermissions($message, $permissions);
                        Log::info('Permissions created successfully');
                    } else {
                        Log::warning('Invalid permissions format received: ' . gettype($permissions));
                    }
                }
                
                Log::info('Automated message creation completed successfully', ['message_id' => $message->id]);
                return $message;
                
            } catch (\Exception $e) {
                Log::error('Error in createMessage transaction: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'user_id' => $user->id,
                    'data_keys' => array_keys($data)
                ]);
                throw $e;
            }
        });
    }

    public function updateMessage($id, array $data)
    {
        Log::info('Updating automated message', ['id' => $id, 'data' => array_diff_key($data, ['media' => null])]);
        
        $user = auth()->user();
        $message = AutomatedMessage::where('user_id', $user->id)->findOrFail($id);
        
        return \DB::transaction(function () use ($message, $user, $data) {
            // Process media files
            $processedData = $this->processMediaFiles($data);
            
            // Update the message
            $message->update([
                'trigger' => $data['trigger'],
                'content' => $data['content'],
                'sent_delay' => $data['sent_delay'] ?? $message->sent_delay,
                'cooldown' => $data['cooldown'] ?? $message->cooldown,
                'is_active' => $data['is_active'] ?? $message->is_active,
            ]);
            
            Log::info('Updated automated message', ['id' => $message->id]);
            
            // Handle media attachments
            if (isset($processedData['media']) && is_array($processedData['media'])) {
                Log::info('Processing ' . count($processedData['media']) . ' media attachments');
                
                // Delete existing media
                foreach ($message->media as $existingMedia) {
                    // Delete the file from storage
                    $this->deleteMediaFile($existingMedia->url);
                    
                    // Delete preview files
                    foreach ($existingMedia->previews as $preview) {
                        $this->deleteMediaFile($preview->url);
                    }
                    
                    // Delete the media record
                    $existingMedia->delete();
                }
                
                // Add new media
                foreach ($processedData['media'] as $mediaItem) {
                    $media = Media::create([
                        'user_id' => $user->id,
                        'mediable_id' => $message->id,
                        'mediable_type' => AutomatedMessage::class,
                        'type' => $mediaItem['type'] ?? 'image',
                        'url' => $mediaItem['url'],
                    ]);
                    
                    Log::info('Created media', [
                        'media_id' => $media->id,
                        'type' => $media->type,
                        'url' => $media->url
                    ]);
                    
                    // Create preview versions if they exist
                    if (isset($mediaItem['previews']) && is_array($mediaItem['previews'])) {
                        Log::info('Creating ' . count($mediaItem['previews']) . ' preview versions');
                        
                        foreach ($mediaItem['previews'] as $previewUrl) {
                            $media->previews()->create([
                                'url' => $previewUrl
                            ]);
                            Log::info('Preview created', ['media_id' => $media->id, 'preview_url' => $previewUrl]);
                        }
                    }
                    
                    // Add media to 'All' and 'AutomatedMessages' albums in Vault
                    $this->vaultService->addMediaToAlbum($media, 'All', $user);
                    $this->vaultService->addMediaToAlbum($media, 'AutomatedMessages', $user);
                    Log::info('Added media to albums');
                }
            }
            
            // Handle permissions if they exist
            if (isset($data['permissions'])) {
                // Parse the permissions if it's a string (JSON)
                $permissions = $data['permissions'];
                if (is_string($permissions)) {
                    $permissions = json_decode($permissions, true);
                }
                
                // Ensure we have a valid array before proceeding
                if (is_array($permissions)) {
                    Log::info('Updating message permissions');
                    $this->permissionService->updatePermissions($message, $permissions);
                } else {
                    Log::warning('Invalid permissions format received: ' . gettype($permissions));
                }
            }
            
            return $message;
        });
    }

    public function deleteMessage($id)
    {
        try {
            $user = auth()->user();
            $message = AutomatedMessage::where('user_id', $user->id)->findOrFail($id);
            
            Log::info('Deleting automated message', ['id' => $id, 'has_media' => $message->media ? 'yes' : 'no']);
            
            // Check if media exists before trying to iterate
            if ($message->media && is_iterable($message->media) && count($message->media) > 0) {
                // Delete media files
                foreach ($message->media as $media) {
                    if ($media) {
                        $this->deleteMediaFile($media->url);
                        
                        // Check if previews exist before iterating
                        if ($media->previews && is_iterable($media->previews) && count($media->previews) > 0) {
                            foreach ($media->previews as $preview) {
                                if ($preview) {
                                    $this->deleteMediaFile($preview->url);
                                }
                            }
                        }
                        
                        // Delete the media record
                        $media->delete();
                    }
                }
            }
            
            // Delete permission sets if they exist
            if (method_exists($message, 'permissionSets') && $message->permissionSets && is_iterable($message->permissionSets)) {
                foreach ($message->permissionSets as $permissionSet) {
                    if ($permissionSet) {
                        // Delete permissions if they exist
                        if (method_exists($permissionSet, 'permissions') && $permissionSet->permissions && is_iterable($permissionSet->permissions)) {
                            foreach ($permissionSet->permissions as $permission) {
                                if ($permission) {
                                    $permission->delete();
                                }
                            }
                        }
                        $permissionSet->delete();
                    }
                }
            }
            
            // Now delete the message
            $result = $message->delete();
            Log::info('Automated message deleted successfully', ['id' => $id]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in deleteMessage: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function toggleMessageStatus($id)
    {
        $user = auth()->user();
        $message = AutomatedMessage::where('user_id', $user->id)->findOrFail($id);
        
        $message->is_active = !$message->is_active;
        $message->save();
        
        return $message;
    }

    protected function processMediaFiles(array $data): array
    {
        if (isset($data['media']) && is_array($data['media'])) {
            Log::info('Processing media files: ' . count($data['media']));
            
            foreach ($data['media'] as &$mediaItem) {
                if (isset($mediaItem['file']) && $mediaItem['file'] instanceof UploadedFile) {
                    Log::info('Processing uploaded file', [
                        'original_name' => $mediaItem['file']->getClientOriginalName(),
                        'mime_type' => $mediaItem['file']->getMimeType(),
                        'size' => $mediaItem['file']->getSize()
                    ]);
                    
                    $mediaItem['url'] = $this->storeMedia($mediaItem['file'], 'automated_messages');
                    $mediaItem['type'] = $mediaItem['type'] ??
                        (str_starts_with($mediaItem['file']->getMimeType(), 'image/') ? 'image' : 'video');
                    unset($mediaItem['file']);
                    
                    Log::info('File processed', [
                        'url' => $mediaItem['url'],
                        'type' => $mediaItem['type']
                    ]);
                }

                if (isset($mediaItem['previewVersions']) && is_array($mediaItem['previewVersions'])) {
                    Log::info('Processing preview versions: ' . count($mediaItem['previewVersions']));
                    
                    $mediaItem['previews'] = [];
                    foreach ($mediaItem['previewVersions'] as $previewFile) {
                        if ($previewFile instanceof UploadedFile) {
                            $previewUrl = $this->storeMedia($previewFile, 'previews');
                            $mediaItem['previews'][] = $previewUrl;
                            
                            Log::info('Preview processed', [
                                'url' => $previewUrl
                            ]);
                        }
                    }
                    unset($mediaItem['previewVersions']);
                }
            }
        }

        return $data;
    }

    protected function storeMedia(UploadedFile $file, string $directory = 'automated_messages'): string
    {
        $filename = $this->generateUniqueFilename($file);
        Log::info('Storing media file', [
            'filename' => $filename,
            'directory' => $directory
        ]);
        
        // Use MediaService to process the file with watermark support
        $type = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'video';
        
        // Get the authenticated user's watermark text
        $user = auth()->user();
        $watermarkText = null;
        if ($user) {
            $watermarkText = $user->media_watermark ?? $user->username;
        }
        
        Log::info('🔍 Processing automated message media with watermark', [
            'type' => $type,
            'watermark_text' => $watermarkText,
            'user_id' => $user ? $user->id : null
        ]);
        
        // Use MediaService to process the file
        $path = $this->mediaService->processMedia($file, $watermarkText, $type);
        
        Log::info('✅ Automated message media processed with watermark', [
            'url' => $path,
            'watermark_applied' => !empty($watermarkText)
        ]);

        return $path;
    }

    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->timestamp;
        $randomString = Str::random(8);

        return "{$originalName}_{$timestamp}_{$randomString}.{$extension}";
    }

    protected function deleteMediaFile(string $url): void
    {
        try {
            Log::info('Attempting to delete media file', [
                'url' => $url
            ]);

            $this->mediaStorage->delete($url);
        } catch (\Exception $e) {
            Log::error('Error in deleteMediaFile: ' . $e->getMessage(), [
                'url' => $url,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
