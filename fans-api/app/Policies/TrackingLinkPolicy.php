<?php

namespace App\Policies;

use App\Models\TrackingLink;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class TrackingLinkPolicy
{
    use HandlesAuthorization;

    public function view(User $user, TrackingLink $trackingLink)
    {
        Log::info('Checking view authorization for tracking link', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'tracking_link_id' => $trackingLink->id,
            'tracking_link_creator_id' => $trackingLink->creator_id,
            'is_authorized' => $user->role === 'creator' && $user->id === $trackingLink->creator_id
        ]);

        return $user->role === 'creator' && $user->id === $trackingLink->creator_id;
    }

    public function update(User $user, TrackingLink $trackingLink)
    {
        Log::info('Checking update authorization for tracking link', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'tracking_link_id' => $trackingLink->id,
            'tracking_link_creator_id' => $trackingLink->creator_id,
            'is_authorized' => $user->role === 'creator' && $user->id === $trackingLink->creator_id
        ]);

        return $user->role === 'creator' && $user->id === $trackingLink->creator_id;
    }

    public function delete(User $user, TrackingLink $trackingLink)
    {
        Log::info('Checking delete authorization for tracking link', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'tracking_link_id' => $trackingLink->id,
            'tracking_link_creator_id' => $trackingLink->creator_id,
            'is_authorized' => $user->role === 'creator' && $user->id === $trackingLink->creator_id
        ]);

        return $user->role === 'creator' && $user->id === $trackingLink->creator_id;
    }
} 