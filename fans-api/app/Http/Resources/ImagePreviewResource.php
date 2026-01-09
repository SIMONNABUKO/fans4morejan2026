<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImagePreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $mediaPreview = $this->resource;
        $media = $mediaPreview->media;
        $user = null;
        // Try to get the user from the parent post if mediable is a Post
        if ($media && $media->mediable_type === 'App\\Models\\Post' && $media->mediable) {
            $user = $media->mediable->user;
        } elseif ($media && $media->user) {
            $user = $media->user;
        }
        return [
            'id' => $mediaPreview->id,
            'url' => $mediaPreview->url,
            'user' => $user ? [
                'id' => $user->id,
                'username' => $user->username,
                'avatar' => $user->avatar,
            ] : null,
        ];
    }
} 