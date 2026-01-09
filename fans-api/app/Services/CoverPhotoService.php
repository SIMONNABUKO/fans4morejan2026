<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class CoverPhotoService
{
    public function generateAndStoreCoverPhoto(User $user): string
    {
        try {
            $imageUrl = "https://picsum.photos/1200/200";
            
            $user->cover_photo = $imageUrl;
            $user->save();
            
            Log::info('Cover photo generated and stored for user', ['user_id' => $user->id]);
            
            return $imageUrl;
        } catch (\Exception $e) {
            Log::error('Error generating cover photo', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            return '';
        }
    }
}

