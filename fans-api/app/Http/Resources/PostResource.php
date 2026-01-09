<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isOwner = $user && $user->id === $this->user_id;
        $viewAsFollower = $request->query('view_as_follower', false);
        
        // If viewing as follower, treat owner as if they don't have permission
        $userHasPermission = $viewAsFollower ? false : ($isOwner ? true : $this->user_has_permission);
        
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'content' => $this->content,
            'processed_content' => $this->processedContent(), // Add processed content with linked usernames
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'status' => $this->status,
            'pinned' => $this->pinned,
            'pinned_at' => $this->pinned_at,

            'media' => $this->when(!$this->preview_only, function () {
                return MediaResource::collection($this->whenLoaded('media'));
            }),
            'media_previews' => $this->when(!$userHasPermission || $this->preview_only, function () {
                return $this->media->flatMap(function ($media) {
                    // If media has previews, filter out video previews and use only image previews
                    if ($media->previews && $media->previews->count() > 0) {
                        $imagePreviews = $media->previews->filter(function ($preview) {
                            $url = is_array($preview) ? ($preview['url'] ?? '') : $preview->url;
                            // Only use image previews, exclude video previews
                            return !preg_match('/\\.(mp4|webm|ogg|mov|avi)$/i', $url) && 
                                   strpos($url, 'video') === false;
                        });
                        
                        // If we have image previews, use them
                        if ($imagePreviews->count() > 0) {
                            return $imagePreviews;
                        }
                    }
                    
                    // If no image previews exist, generate dummy previews
                    return collect([$this->generateDummyPreview($media)]);
                });
            }),
            'user' => new UserResource($this->whenLoaded('user')),
            'stats' => [
                'total_likes' => $this->stats->total_likes ?? 0,
                'total_views' => $this->stats->total_views ?? 0,
                'total_bookmarks' => $this->stats->total_bookmarks ?? 0,
                'total_comments' => $this->stats->total_comments ?? 0,
                'total_tips' => $this->stats->total_tips ?? 0,
                'total_tip_amount' => $this->stats->total_tip_amount ?? 0,
                'is_liked' => $user ? $this->isLikedByUser($user) : false,
                'is_bookmarked' => $user ? $this->isBookmarkedByUser($user) : false,
            ],
            'user_has_permission' => $userHasPermission,
            'required_permissions' => $this->when(!$userHasPermission, $this->required_permissions ?? []),

            // Add tagged users data using the dedicated resource
            'tagged_users' => $this->when($this->approvedTags()->exists(), function () {
                return TaggedUserResource::collection(
                    $this->taggedUsers()
                        ->wherePivot('status', 'approved')
                        ->get()
                );
            }),

            // Add tag status for the current user (if they are tagged in this post)
            'current_user_tag_status' => $this->when($user, function () use ($user) {
                $tag = $this->tags()->where('user_id', $user->id)->first();
                return $tag ? $tag->status : null;
            }),

            // Add permission_sets with permissions for editing
            'permission_sets' => $this->whenLoaded('permissionSets', function () {
                return $this->permissionSets->map(function ($set) {
                    return [
                        'id' => $set->id,
                        'permissions' => $set->permissions->map(function ($permission) {
                            return [
                                'type' => $permission->type,
                                'value' => $permission->value
                            ];
                        })
                    ];
                });
            }),
            // Add comments (with user) for single post view
            'comments' => $this->whenLoaded('comments', function () {
                return $this->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'created_at' => $comment->created_at,
                        'user' => $comment->user ? [
                            'id' => $comment->user->id,
                            'username' => $comment->user->username,
                            'avatar' => $comment->user->avatar
                        ] : null
                    ];
                });
            })
        ];
    }

    /**
     * Generate a dummy preview for media that doesn't have previews
     *
     * @param \App\Models\Media $media
     * @return array
     */
    private function generateDummyPreview($media)
    {
        // Create a dummy preview object that mimics MediaPreview structure
        return [
            'id' => 'dummy_' . $media->id,
            'media_id' => $media->id,
            'url' => $this->getDummyPreviewUrl($media->type),
            'created_at' => $media->created_at,
            'updated_at' => $media->updated_at,
        ];
    }

    /**
     * Get the appropriate dummy preview URL based on media type
     *
     * @param string $mediaType
     * @return string
     */
    private function getDummyPreviewUrl($mediaType)
    {
        // Use data URI for dummy previews to avoid file dependency
        // Simple placeholder without icons using the specified color
        return 'data:image/svg+xml;base64,' . base64_encode('
            <svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
                <rect width="400" height="400" fill="#2e3748"/>
            </svg>
        ');
    }
}
