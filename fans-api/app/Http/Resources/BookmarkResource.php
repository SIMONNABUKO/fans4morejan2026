<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Media;
use App\Models\Post;
use App\Models\MediaPreview;

class BookmarkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'bookmark_album_id' => $this->bookmark_album_id,
            'bookmarkable_type' => $this->bookmarkable_type,
            'bookmarkable_id' => $this->bookmarkable_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user_has_permission' => $this->resource->user_has_permission ?? true,
            'required_permissions' => $this->when(
                !($this->resource->user_has_permission ?? true), 
                $this->resource->required_permissions ?? []
            ),
        ];

        // Add bookmarkable data
        if ($this->bookmarkable) {
            if ($this->bookmarkable_type === 'App\\Models\\Media') {
                $data['bookmarkable'] = [
                    'id' => $this->bookmarkable->id,
                    'url' => $this->bookmarkable->url,
                    'thumbnail_url' => $this->bookmarkable->thumbnail_url ?? $this->bookmarkable->url,
                    'mime_type' => $this->bookmarkable->mime_type ?? null,
                    'created_at' => $this->bookmarkable->created_at,
                    'updated_at' => $this->bookmarkable->updated_at,
                ];

                // Add stats if available
                if (isset($this->bookmarkable->stats)) {
                    $data['bookmarkable']['stats'] = [
                        'total_likes' => $this->bookmarkable->stats->total_likes ?? 0,
                        'total_views' => $this->bookmarkable->stats->total_views ?? 0,
                        'total_bookmarks' => $this->bookmarkable->stats->total_bookmarks ?? 0,
                        'total_tips' => $this->bookmarkable->stats->total_tips ?? 0,
                        'total_tip_amount' => $this->bookmarkable->stats->total_tip_amount ?? 0,
                        'is_liked' => $request->user() ? $this->bookmarkable->isLikedByUser($request->user()) : false,
                        'is_bookmarked' => $request->user() ? $this->bookmarkable->isBookmarkedByUser($request->user()) : false,
                    ];
                }
            } elseif ($this->bookmarkable_type === 'App\\Models\\Post') {
                $data['bookmarkable'] = [
                    'id' => $this->bookmarkable->id,
                    'content' => $this->bookmarkable->content,
                    'created_at' => $this->bookmarkable->created_at,
                    'updated_at' => $this->bookmarkable->updated_at,
                ];

                // Add stats if available
                if (isset($this->bookmarkable->stats)) {
                    $data['bookmarkable']['stats'] = [
                        'total_likes' => $this->bookmarkable->stats->total_likes ?? 0,
                        'total_views' => $this->bookmarkable->stats->total_views ?? 0,
                        'total_bookmarks' => $this->bookmarkable->stats->total_bookmarks ?? 0,
                        'total_tips' => $this->bookmarkable->stats->total_tips ?? 0,
                        'total_tip_amount' => $this->bookmarkable->stats->total_tip_amount ?? 0,
                        'is_liked' => $request->user() ? $this->bookmarkable->isLikedByUser($request->user()) : false,
                        'is_bookmarked' => $request->user() ? $this->bookmarkable->isBookmarkedByUser($request->user()) : false,
                    ];
                }

                // Include media with permission checks
                if ($this->bookmarkable->media) {
                    $data['bookmarkable']['media'] = $this->bookmarkable->media->map(function ($media) use ($request) {
                        $mediaUrl = $media->full_url;
                        $thumbnailUrl = $media->thumbnail_url
                            ? app(\App\Services\MediaStorageService::class)->url($media->thumbnail_url)
                            : $mediaUrl;
                        $mediaData = [
                            'id' => $media->id,
                            'url' => $mediaUrl,
                            'thumbnail_url' => $thumbnailUrl,
                            'mime_type' => $media->mime_type ?? null,
                            'created_at' => $media->created_at,
                            'updated_at' => $media->updated_at,
                        ];

                        // Add stats if available
                        if (isset($media->stats)) {
                            $mediaData['stats'] = [
                                'total_likes' => $media->stats->total_likes ?? 0,
                                'total_views' => $media->stats->total_views ?? 0,
                                'total_bookmarks' => $media->stats->total_bookmarks ?? 0,
                                'total_tips' => $media->stats->total_tips ?? 0,
                                'total_tip_amount' => $media->stats->total_tip_amount ?? 0,
                                'is_liked' => $request->user() ? $media->isLikedByUser($request->user()) : false,
                                'is_bookmarked' => $request->user() ? $media->isBookmarkedByUser($request->user()) : false,
                            ];
                        }

                        return $mediaData;
                    });
                }
            } elseif ($this->bookmarkable_type === 'App\\Models\\MediaPreview') {
                // Use your existing MediaPreviewResource for media previews
                $data['bookmarkable'] = (new MediaPreviewResource($this->bookmarkable))->toArray($request);
            } else {
                // For other types, just include basic data
                $data['bookmarkable'] = [
                    'id' => $this->bookmarkable->id,
                    'created_at' => $this->bookmarkable->created_at,
                    'updated_at' => $this->bookmarkable->updated_at,
                ];
            }
        }

        // Add media previews if user doesn't have permission
        if (!($this->resource->user_has_permission ?? true)) {
            if ($this->bookmarkable_type === 'App\\Models\\Media' && 
                method_exists($this->bookmarkable, 'previews') && 
                $this->bookmarkable->previews->isNotEmpty()) {
                
                $data['media'] = MediaPreviewResource::collection(
                    $this->bookmarkable->previews
                );
            } elseif ($this->bookmarkable_type === 'App\\Models\\Post') {
                $previews = [];
                foreach ($this->bookmarkable->media as $media) {
                    if (method_exists($media, 'previews') && $media->previews->isNotEmpty()) {
                        foreach ($media->previews as $preview) {
                            $previews[] = $preview;
                        }
                    }
                }
                
                if (!empty($previews)) {
                    $data['media'] = MediaPreviewResource::collection(
                        collect($previews)
                    );
                }
            }
        } else {
            // If user has permission, include the actual media
            if ($this->bookmarkable_type === 'App\\Models\\Media') {
                $data['media'] = [
                    [
                        'id' => $this->bookmarkable->id,
                        'url' => $this->bookmarkable->url,
                        'thumbnail_url' => $this->bookmarkable->thumbnail_url ?? $this->bookmarkable->url,
                        'mime_type' => $this->bookmarkable->mime_type ?? null,
                        'created_at' => $this->bookmarkable->created_at,
                        'updated_at' => $this->bookmarkable->updated_at,
                        'stats' => isset($this->bookmarkable->stats) ? [
                            'total_likes' => $this->bookmarkable->stats->total_likes ?? 0,
                            'total_views' => $this->bookmarkable->stats->total_views ?? 0,
                            'total_bookmarks' => $this->bookmarkable->stats->total_bookmarks ?? 0,
                            'total_tips' => $this->bookmarkable->stats->total_tips ?? 0,
                            'total_tip_amount' => $this->bookmarkable->stats->total_tip_amount ?? 0,
                            'is_liked' => $request->user() ? $this->bookmarkable->isLikedByUser($request->user()) : false,
                            'is_bookmarked' => $request->user() ? $this->bookmarkable->isBookmarkedByUser($request->user()) : false,
                        ] : null
                    ]
                ];
            } elseif ($this->bookmarkable_type === 'App\\Models\\Post' && $this->bookmarkable->media) {
                $data['media'] = $this->bookmarkable->media->map(function ($media) use ($request) {
                    $mediaUrl = $media->full_url;
                    $thumbnailUrl = $media->thumbnail_url
                        ? app(\App\Services\MediaStorageService::class)->url($media->thumbnail_url)
                        : $mediaUrl;
                    return [
                        'id' => $media->id,
                        'url' => $mediaUrl,
                        'thumbnail_url' => $thumbnailUrl,
                        'mime_type' => $media->mime_type ?? null,
                        'created_at' => $media->created_at,
                        'updated_at' => $media->updated_at,
                        'stats' => isset($media->stats) ? [
                            'total_likes' => $media->stats->total_likes ?? 0,
                            'total_views' => $media->stats->total_views ?? 0,
                            'total_bookmarks' => $media->stats->total_bookmarks ?? 0,
                            'total_tips' => $media->stats->total_tips ?? 0,
                            'total_tip_amount' => $media->stats->total_tip_amount ?? 0,
                            'is_liked' => $request->user() ? $media->isLikedByUser($request->user()) : false,
                            'is_bookmarked' => $request->user() ? $media->isBookmarkedByUser($request->user()) : false,
                        ] : null
                    ];
                })->toArray();
            }
        }

        return $data;
    }
}
