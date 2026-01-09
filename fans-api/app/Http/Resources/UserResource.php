<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Get the authenticated user to check follow status
        // Use Auth facade since route doesn't have auth middleware
        $authUser = Auth::user();
        $isFollowing = false;
        $isMuted = false;
        $isBlocked = false;
        
        Log::info('🔍 UserResource::toArray', [
            'auth_user_id' => $authUser?->id,
            'profile_user_id' => $this->id,
            'auth_user_null' => $authUser === null
        ]);
        
        if ($authUser && $authUser->id !== $this->id) {
            $isFollowing = $authUser->isFollowing($this->resource);
            
            // Check if user is muted or blocked
            $isMuted = $authUser->isUserInList($this->resource, 'Muted Accounts');
            $isBlocked = $authUser->isUserInList($this->resource, 'Blocked Accounts');
            
            Log::info('🔍 UserResource Mute/Block Check', [
                'auth_user_id' => $authUser->id,
                'profile_user_id' => $this->id,
                'is_muted' => $isMuted,
                'is_blocked' => $isBlocked
            ]);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'handle' => $this->handle,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'cover_photo' => $this->cover_photo,
            'bio' => $this->bio,
            'bio_color' => $this->bio_color,
            'bio_font' => $this->bio_font,
            'role' => $this->role,
            'can_be_followed' => $this->can_be_followed,
            'is_online' => $this->is_online,
            'location' => $this->location,
            'country_code' => $this->country_code,
            'country_name' => $this->country_name,
            'region_name' => $this->region_name,
            'city_name' => $this->city_name,
            'location_updated_at' => $this->location_updated_at,
            'social_links' => [
                'facebook' => $this->facebook,
                'twitter' => $this->twitter,
                'instagram' => $this->instagram,
                'linkedin' => $this->linkedin,
            ],
            'last_seen_at' => $this->last_seen_at,
            'total_likes_received' => $this->total_likes_received,
            'total_video_uploads' => $this->total_video_uploads,
            'total_image_uploads' => $this->total_image_uploads,
            'total_followers' => $this->followers_count,
            'is_following' => $isFollowing,
            'followed_by_current_user' => $isFollowing, // For backward compatibility
            'is_muted' => $isMuted,
            'is_blocked' => $isBlocked,
            'posts' => PostResource::collection($this->whenLoaded('posts')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
        ];
    }
}

