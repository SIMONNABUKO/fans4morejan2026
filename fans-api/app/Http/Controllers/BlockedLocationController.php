<?php

namespace App\Http\Controllers;

use App\Http\Requests\BlockLocationRequest;
use App\Services\BlockedLocationService;
use App\Services\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockedLocationController extends Controller
{
    protected $blockedLocationService;
    protected $locationService;

    public function __construct(
        BlockedLocationService $blockedLocationService,
        LocationService $locationService
    ) {
        $this->blockedLocationService = $blockedLocationService;
        $this->locationService = $locationService;
    }

    public function blockLocation(BlockLocationRequest $request): JsonResponse
    {
        $blockedLocation = $this->blockedLocationService->blockLocation(
            auth()->user(),
            $request->validated()
        );
        return response()->json($blockedLocation, 201);
    }

    public function unblockLocation(int $locationId): JsonResponse
    {
        $result = $this->blockedLocationService->unblockLocation(auth()->user(), $locationId);
        return response()->json(['success' => $result]);
    }

    public function getBlockedLocations(): JsonResponse
    {
        $blockedLocations = $this->blockedLocationService->getBlockedLocations(auth()->user());
        return response()->json($blockedLocations);
    }

    /**
     * Get available countries for blocking
     */
    public function getAvailableCountries(): JsonResponse
    {
        $countries = $this->blockedLocationService->getAvailableCountries(auth()->user());
        return response()->json($countries);
    }

    /**
     * Search for locations using OpenStreetMap
     */
    public function searchLocations(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100'
        ]);

        $results = $this->locationService->searchLocations($request->input('query'));
        return response()->json($results);
    }

    /**
     * Get all countries list
     */
    public function getCountries(): JsonResponse
    {
        $countries = $this->locationService->getCountryList();
        return response()->json($countries);
    }
}

