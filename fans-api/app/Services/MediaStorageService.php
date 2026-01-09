<?php

namespace App\Services;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorageService
{
    public function diskName(): string
    {
        return config('filesystems.media_disk', 'public');
    }

    public function disk()
    {
        return Storage::disk($this->diskName());
    }

    public function url(?string $pathOrUrl): ?string
    {
        if (!$pathOrUrl) {
            return null;
        }

        if (Str::startsWith($pathOrUrl, ['http://', 'https://'])) {
            return $pathOrUrl;
        }

        return $this->disk()->url(ltrim($pathOrUrl, '/'));
    }

    public function path(?string $pathOrUrl): ?string
    {
        if (!$pathOrUrl) {
            return null;
        }

        $raw = $pathOrUrl;

        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $baseUrl = rtrim($this->disk()->url(''), '/');
            if ($baseUrl && Str::startsWith($raw, $baseUrl)) {
                return ltrim(substr($raw, strlen($baseUrl)), '/');
            }

            $parsedPath = parse_url($raw, PHP_URL_PATH);
            if ($parsedPath && Str::startsWith($parsedPath, '/storage/')) {
                return ltrim(substr($parsedPath, strlen('/storage/')), '/');
            }

            return null;
        }

        if (Str::startsWith($raw, '/storage/')) {
            return ltrim(substr($raw, strlen('/storage/')), '/');
        }

        return ltrim($raw, '/');
    }

    public function storeUploadedFile(string $directory, UploadedFile|File $file, ?string $filename = null): string
    {
        $name = $filename ?: $this->generateUniqueFilename($file);
        $path = $this->disk()->putFileAs($directory, $file, $name, [
            'visibility' => 'public',
        ]);

        return $path;
    }

    public function delete(?string $pathOrUrl): void
    {
        $path = $this->path($pathOrUrl);
        if ($path) {
            $this->disk()->delete($path);
        }
    }

    private function generateUniqueFilename(UploadedFile|File $file): string
    {
        $originalName = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        $extension = $file->getExtension();
        if (!$extension && $file instanceof UploadedFile) {
            $extension = $file->getClientOriginalExtension();
        }
        $timestamp = now()->timestamp;
        $randomString = Str::random(8);

        $safeName = $originalName ?: 'media';
        $suffix = $extension ? ".{$extension}" : '';

        return "{$safeName}_{$timestamp}_{$randomString}{$suffix}";
    }
}
