<?php

namespace App\Services;

use App\Models\TrackingLink;
use App\Models\TrackingLinkAction;
use Illuminate\Http\Request;

class TrackingLinkActionService
{
    public function trackAction(
        TrackingLink $trackingLink,
        string $actionType,
        ?int $userId = null,
        ?array $actionData = null,
        ?Request $request = null
    ): TrackingLinkAction {
        return TrackingLinkAction::create([
            'tracking_link_id' => $trackingLink->id,
            'action_type' => $actionType,
            'user_id' => $userId,
            'action_data' => $actionData,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    public function getTrackingLinkStats(TrackingLink $trackingLink): array
    {
        $actions = TrackingLinkAction::where('tracking_link_id', $trackingLink->id)
            ->get()
            ->groupBy('action_type');

        return [
            'total_actions' => $actions->sum->count(),
            'signups' => $actions->get('signup', collect())->count(),
            'subscriptions' => $actions->get('subscription', collect())->count(),
            'purchases' => $actions->get('purchase', collect())->count(),
            'tips' => $actions->get('tip', collect())->count(),
            'action_details' => $actions->map(fn ($group) => [
                'count' => $group->count(),
                'users' => $group->pluck('user_id')->unique()->count(),
            ])->toArray(),
        ];
    }
} 