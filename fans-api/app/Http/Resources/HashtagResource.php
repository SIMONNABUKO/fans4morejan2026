<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HashtagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'posts_count' => $this->posts_count,
            'followers_count' => $this->followers_count,
            'is_trending' => $this->is_trending,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 