<?php

namespace App\Http\Controllers;

use App\Services\UserLocationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserLocationController extends Controller
{
    protected $userLocationService;

    public function __construct(UserLocationService $userLocationService)
    {
        $this->userLocationService = $userLocationService;
    }

    /**
     * Update user location from coordinates (browser geolocation)
     */
    public function updateFromCoordinates(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $user = Auth::user();
        $success = $this->userLocationService->updateUserLocationFromCoordinates(
            $user,
            $request->latitude,
            $request->longitude
        );

        if ($success) {
            return response()->json([
                'message' => 'Location updated successfully',
                'user' => $user->fresh(['blockedLocations'])
            ]);
        }

        return response()->json([
            'message' => 'Failed to update location'
        ], 400);
    }

    /**
     * Get current user location
     */
    public function getCurrentLocation(): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'location' => [
                'country_code' => $user->country_code,
                'country_name' => $user->country_name,
                'region_name' => $user->region_name,
                'city_name' => $user->city_name,
                'latitude' => $user->latitude,
                'longitude' => $user->longitude,
                'location_updated_at' => $user->location_updated_at,
            ]
        ]);
    }

    /**
     * Update user location from IP address
     */
    public function updateFromIp(Request $request): JsonResponse
    {
        $user = Auth::user();
        $success = $this->userLocationService->updateUserLocationFromIp($user, $request);

        if ($success) {
            return response()->json([
                'message' => 'Location updated successfully from IP',
                'user' => $user->fresh(['blockedLocations'])
            ]);
        }

        return response()->json([
            'message' => 'Failed to update location from IP'
        ], 400);
    }

    /**
     * Update user location manually for testing
     */
    public function updateManually(Request $request): JsonResponse
    {
        $request->validate([
            'country_code' => 'required|string|max:10',
            'country_name' => 'required|string|max:255',
            'region_name' => 'required|string|max:255',
            'city_name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        
        $user->update([
            'country_code' => $request->country_code,
            'country_name' => $request->country_name,
            'region_name' => $request->region_name,
            'city_name' => $request->city_name,
            'location_updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Location updated successfully',
            'user' => $user->fresh(['blockedLocations'])
        ]);
    }
}

