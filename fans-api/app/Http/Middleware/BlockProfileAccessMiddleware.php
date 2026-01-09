<?php

namespace App\Http\Middleware;

use App\Services\UserLocationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BlockProfileAccessMiddleware
{
    protected $userLocationService;

    public function __construct(UserLocationService $userLocationService)
    {
        $this->userLocationService = $userLocationService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $viewer = Auth::user();
        $targetUsername = $request->route('username');

        // Get target user by username
        $targetUser = \App\Models\User::where('username', $targetUsername)->first();

        if (!$targetUser) {
            return $next($request); // Let the controller handle 404
        }

        // Check if viewer is in a blocked location
        if ($this->userLocationService->isUserInBlockedLocation($viewer, $targetUser)) {
            return response()->json([
                'error' => 'Access denied',
                'message' => 'This profile is not available in your location',
                'code' => 'LOCATION_BLOCKED'
            ], 403);
        }

        return $next($request);
    }
}

