<?php

namespace App\Services;

use App\Contracts\CreatorApplicationRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use App\Services\MediaStorageService;

class CreatorApplicationService
{
    protected $repository;
    protected $mediaStorage;

    public function __construct(CreatorApplicationRepositoryInterface $repository, MediaStorageService $mediaStorage)
    {
        $this->repository = $repository;
        $this->mediaStorage = $mediaStorage;
    }

    public function createOrUpdateApplication(array $data, int $userId)
    {
        Log::info('createOrUpdateApplication', ['data' => $data, 'userId' => $userId]);
        $application = $this->repository->findByUserId($userId);

        $data['user_id'] = $userId;
        // Always set status to pending when creating or updating
        $data['status'] = 'pending';
        // Reset processed_at and feedback when resubmitting
        $data['processed_at'] = null;
        $data['feedback'] = null;

        // Handle file uploads
        $fileFields = ['front_id', 'back_id', 'holding_id'];
        foreach ($fileFields as $field) {
            if (isset($data[$field]) && $data[$field] instanceof UploadedFile) {
                $path = $this->storeMedia($data[$field]);
                $data[$field] = $path;
            }
        }

        if ($application) {
            $this->repository->update($application, $data);
        } else {
            $application = $this->repository->create($data);
        }

        return $application;
    }

    public function getApplicationByUserId(int $userId)
    {
        return $this->repository->findByUserId($userId);
    }

    protected function storeMedia(UploadedFile $file): string
    {
        $filename = $this->generateUniqueFilename($file);
        Log::info('Storing media file', [
            'filename' => $filename,
            'directory' => 'creator_applications'
        ]);
        
        $path = $this->mediaStorage->storeUploadedFile('creator_applications', $file, $filename);

        Log::debug('Media stored with key', [
            'path' => $path,
            'disk' => $this->mediaStorage->diskName()
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
}
