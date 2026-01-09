<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TrackingLink;

class TrackLinkSession
{
    public function handle(Request $request, Closure $next)
    {
        // Check if there's a tracking link ID in the URL
        if ($trackingLinkId = $request->query('tracking_link_id')) {
            // Store the tracking link ID in the session
            session(['tracking_link_id' => $trackingLinkId]);
        }

        return $next($request);
    }
} 