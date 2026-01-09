<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaPreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        
        return [
            'id' => $this->resource->id,
            'url' => $this->resource->full_url,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'stats' => [
                'total_likes' => $this->resource->stats->total_likes ?? 0,
                'total_views' => $this->resource->stats->total_views ?? 0,
                'total_bookmarks' => $this->resource->stats->total_bookmarks ?? 0,
                'total_tips' => $this->resource->stats->total_tips ?? 0,
                'total_tip_amount' => $this->resource->stats->total_tip_amount ?? 0,
                'is_liked' => $user ? $this->resource->isLikedByUser($user) : false,
                'is_bookmarked' => $user ? $this->resource->isBookmarkedByUser($user) : false,
            ],
        ];
    }
}
