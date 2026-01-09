<?php

namespace App\Services;

use App\Models\TrackingLink;
use App\Models\TrackingLinkEvent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TrackingLinkService
{
    public function generateTrackingLink(array $data)
    {
        // Generate a unique slug
        $slug = Str::slug($data['name']) . '-' . Str::random(6);

        // Create the tracking link
        $trackingLink = TrackingLink::create([
            'creator_id' => $data['creator_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        // Add the full tracking URL to the response
        $trackingLink->full_url = config('app.url') . '/track/' . $slug;

        return $trackingLink;
    }

    public function getTrackingLinkStats(TrackingLink $trackingLink)
    {
        Log::info('Getting tracking link stats', [
            'tracking_link_id' => $trackingLink->id,
            'total_clicks' => $trackingLink->clicks()->count(),
            'total_subscriptions' => $trackingLink->subscriptions()->count(),
            'total_purchases' => $trackingLink->purchases()->count()
        ]);

        $stats = [
            'total_clicks' => $trackingLink->clicks()->count(),
            'total_subscriptions' => $trackingLink->subscriptions()->count(),
            'total_purchases' => $trackingLink->purchases()->count(),
            'total_tips' => $trackingLink->actions()->where('action_type', 'tip')->count(),
            'clicks_by_date' => $trackingLink->clicks()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get(),
            'clicks_by_referrer' => $trackingLink->clicks()
                ->selectRaw('referrer_domain, COUNT(*) as count')
                ->whereNotNull('referrer_domain')
                ->groupBy('referrer_domain')
                ->orderBy('count', 'desc')
                ->get(),
            'subscriptions_by_date' => $trackingLink->subscriptions()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get(),
            'purchases_by_date' => $trackingLink->purchases()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get(),
            'tips_by_date' => $trackingLink->actions()
                ->where('action_type', 'tip')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get(),
            'conversion_rate' => $this->calculateConversionRate($trackingLink),
            'recent_events' => $trackingLink->events()
                ->with(['user' => function($query) {
                    $query->select('id', 'username', 'display_name', 'avatar');
                }])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
        ];

        Log::info('Tracking link stats calculated', [
            'tracking_link_id' => $trackingLink->id,
            'stats' => $stats
        ]);

        return $stats;
    }

    private function calculateConversionRate(TrackingLink $trackingLink)
    {
        $totalClicks = $trackingLink->clicks()->count();
        if ($totalClicks === 0) {
            return 0;
        }

        $totalConversions = $trackingLink->subscriptions()->count() + 
                           $trackingLink->purchases()->count() + 
                           $trackingLink->actions()->where('action_type', 'tip')->count();
        $rate = ($totalConversions / $totalClicks) * 100;

        Log::info('Conversion rate calculated', [
            'tracking_link_id' => $trackingLink->id,
            'total_clicks' => $totalClicks,
            'total_conversions' => $totalConversions,
            'conversion_rate' => $rate
        ]);

        return $rate;
    }

    public function recordEvent(TrackingLink $trackingLink, string $eventType, array $data)
    {
        // For click events, check if we already have a click from this IP
        if ($eventType === 'click' && !empty($data['ip_address'])) {
            $exists = TrackingLinkEvent::where('tracking_link_id', $trackingLink->id)
                ->where('event_type', 'click')
                ->where('ip_address', $data['ip_address'])
                ->exists();

            if ($exists) {
                Log::info('Duplicate click event detected, skipping', [
                    'tracking_link_id' => $trackingLink->id,
                    'ip_address' => $data['ip_address']
                ]);
                return null;
            }
        }

        try {
            return TrackingLinkEvent::create([
                'tracking_link_id' => $trackingLink->id,
                'event_type' => $eventType,
                'user_id' => $data['user_id'] ?? null,
                'subscription_id' => $data['subscription_id'] ?? null,
                'transaction_id' => $data['transaction_id'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'referrer_url' => $data['referrer_url'] ?? null,
                'referrer_domain' => $data['referrer_domain'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record tracking link event', [
                'error' => $e->getMessage(),
                'tracking_link_id' => $trackingLink->id,
                'event_type' => $eventType,
                'data' => $data
            ]);
            return null;
        }
    }
} 