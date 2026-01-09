<?php

namespace App\Http\Middleware;

use App\Models\TrackingLink;
use App\Services\TrackingLinkActionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackLinkActions
{
    public function __construct(
        private readonly TrackingLinkActionService $trackingLinkActionService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track actions if we have a tracking link ID in the session
        if (!$trackingLinkId = session('tracking_link_id')) {
            return $response;
        }

        // Get the tracking link
        $trackingLink = TrackingLink::find($trackingLinkId);
        if (!$trackingLink) {
            return $response;
        }

        // Track different actions based on the route
        $routeName = $request->route()->getName();
        $userId = Auth::id();

        match ($routeName) {
            'auth.register' => $this->trackingLinkActionService->trackAction(
                $trackingLink,
                'signup',
                $userId,
                ['method' => 'register'],
                $request
            ),
            'auth.login' => $this->trackingLinkActionService->trackAction(
                $trackingLink,
                'signup',
                $userId,
                ['method' => 'login'],
                $request
            ),
            'subscriptions.create' => $this->trackingLinkActionService->trackAction(
                $trackingLink,
                'subscription',
                $userId,
                ['plan_id' => $request->input('plan_id')],
                $request
            ),
            'media.purchase' => $this->trackingLinkActionService->trackAction(
                $trackingLink,
                'purchase',
                $userId,
                ['media_id' => $request->input('media_id')],
                $request
            ),
            'tips.send' => $this->trackingLinkActionService->trackAction(
                $trackingLink,
                'tip',
                $userId,
                [
                    'amount' => $request->input('amount'),
                    'receiver_id' => $request->input('receiver_id'),
                    'message' => $request->input('message')
                ],
                $request
            ),
            default => null,
        };

        return $response;
    }
} 