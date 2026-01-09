<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class UserLocationService
{
    protected $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Update user location based on IP address
     */
    public function updateUserLocationFromIp(User $user, Request $request): bool
    {
        try {
            $ipAddress = $request->ip();
            
            // Skip if IP is localhost or private
            if ($this->isPrivateIp($ipAddress)) {
                Log::info('Skipping location update for private IP', ['user_id' => $user->id, 'ip' => $ipAddress]);
                return false;
            }

            // Get location data from IP
            $locationData = $this->locationService->getUserLocation($ipAddress);
            
            if (!$locationData) {
                Log::warning('Could not get location data from IP', ['user_id' => $user->id, 'ip' => $ipAddress]);
                return false;
            }

            // Prepare update data
            $updateData = [
                'ip_address' => $ipAddress,
            ];

            // Only add location fields if the columns exist
            if ($this->columnExists('users', 'country_code')) {
                $updateData['country_code'] = $locationData['country_code'] ?? null;
                $updateData['country_name'] = $locationData['country'] ?? null;
                $updateData['region_name'] = $locationData['region'] ?? null;
                $updateData['city_name'] = $locationData['city'] ?? null;
                $updateData['latitude'] = $locationData['latitude'] ?? null;
                $updateData['longitude'] = $locationData['longitude'] ?? null;
                $updateData['location_updated_at'] = now();
            }

            // Update user location
            $user->update($updateData);

            Log::info('Updated user location from IP', [
                'user_id' => $user->id,
                'country' => $locationData['country'] ?? 'Unknown',
                'city' => $locationData['city'] ?? 'Unknown',
                'columns_exist' => $this->columnExists('users', 'country_code')
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error updating user location from IP', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Update user location from coordinates (browser geolocation)
     */
    public function updateUserLocationFromCoordinates(User $user, float $latitude, float $longitude): bool
    {
        try {
            // Reverse geocode coordinates to get location details
            $locationData = $this->locationService->reverseGeocode($latitude, $longitude);
            
            if (!$locationData) {
                Log::warning('Could not reverse geocode coordinates', [
                    'user_id' => $user->id,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ]);
                return false;
            }

            // Prepare update data
            $updateData = [];

            // Only add location fields if the columns exist
            if ($this->columnExists('users', 'country_code')) {
                $updateData['country_code'] = $locationData['country_code'] ?? null;
                $updateData['country_name'] = $locationData['country'] ?? null;
                $updateData['region_name'] = $locationData['region'] ?? null;
                $updateData['city_name'] = $locationData['city'] ?? null;
                $updateData['latitude'] = $latitude;
                $updateData['longitude'] = $longitude;
                $updateData['location_updated_at'] = now();
            }

            // Update user location
            $user->update($updateData);

            Log::info('Updated user location from coordinates', [
                'user_id' => $user->id,
                'country' => $locationData['country'] ?? 'Unknown',
                'city' => $locationData['city'] ?? 'Unknown'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error updating user location from coordinates', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if a user is in a blocked location
     */
    public function isUserInBlockedLocation(User $viewer, User $targetUser): bool
    {
        // Get target user's blocked locations
        $blockedLocations = $targetUser->blockedLocations;
        
        if ($blockedLocations->isEmpty()) {
            return false;
        }

        // Check if viewer has location data and columns exist
        if (!$this->columnExists('users', 'country_code') || !$viewer->country_code) {
            return false; // Can't determine if blocked
        }

        foreach ($blockedLocations as $blockedLocation) {
            if ($this->isLocationBlocked($viewer, $blockedLocation)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a specific location is blocked
     */
    private function isLocationBlocked(User $viewer, $blockedLocation): bool
    {
        // Check country level
        if ($blockedLocation->location_type === 'country' && 
            $viewer->country_code === $blockedLocation->country_code) {
            return true;
        }

        // Check region level
        if ($blockedLocation->location_type === 'region' && 
            $viewer->country_code === $blockedLocation->country_code) {
            
            // Check for exact match first
            if ($viewer->region_name === $blockedLocation->region_name) {
                return true;
            }
            
            // Check for partial matches (e.g., "Nairobi" in "Nairobi County")
            if ($blockedLocation->region_name && $viewer->region_name) {
                if (str_contains(strtolower($viewer->region_name), strtolower($blockedLocation->region_name)) ||
                    str_contains(strtolower($blockedLocation->region_name), strtolower($viewer->region_name))) {
                    return true;
                }
            }
        }

        // Check city level
        if ($blockedLocation->location_type === 'city' && 
            $viewer->country_code === $blockedLocation->country_code) {
            
            // Check region match first (with flexible matching)
            $regionMatches = false;
            if ($viewer->region_name === $blockedLocation->region_name) {
                $regionMatches = true;
            } elseif ($blockedLocation->region_name && $viewer->region_name) {
                if (str_contains(strtolower($viewer->region_name), strtolower($blockedLocation->region_name)) ||
                    str_contains(strtolower($blockedLocation->region_name), strtolower($viewer->region_name))) {
                    $regionMatches = true;
                }
            }
            
            // If region matches, check city
            if ($regionMatches && $viewer->city_name === $blockedLocation->city_name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter users based on blocked locations
     */
    public function filterUsersByBlockedLocations(User $viewer, $users)
    {
        return $users->filter(function ($user) use ($viewer) {
            return !$this->isUserInBlockedLocation($viewer, $user);
        });
    }

    /**
     * Check if IP address is private/localhost
     */
    private function isPrivateIp(string $ip): bool
    {
        return in_array($ip, ['127.0.0.1', '::1']) || 
               filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * Check if a column exists in a table
     */
    private function columnExists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }
}
