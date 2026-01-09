<?php

namespace App\Http\Middleware;

use App\Services\UserLocationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserLocationMiddleware
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
        // Only update location for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            
            // Update location if it hasn't been updated recently (within 24 hours)
            if (!$user->location_updated_at || $user->location_updated_at->diffInHours(now()) > 24) {
                $this->userLocationService->updateUserLocationFromIp($user, $request);
            }
        }

        return $next($request);
    }
}

