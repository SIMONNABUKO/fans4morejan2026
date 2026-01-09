<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ContentVisibilityService
{
    protected $locationService;
    protected $userLocationService;

    public function __construct(LocationService $locationService, UserLocationService $userLocationService)
    {
        $this->locationService = $locationService;
        $this->userLocationService = $userLocationService;
    }

    /**
     * Filter query based on user's location restrictions
     *
     * @param Builder $query
     * @param array|null $userLocation
     * @return Builder
     */
    public function filterContentByLocation(Builder $query, ?array $userLocation): Builder
    {
        if (!$userLocation || !isset($userLocation['country_code'])) {
            Log::info('No location data available, skipping location filtering');
            return $query;
        }

        $countryCode = $userLocation['country_code'];
        
        Log::info('Applying location-based content filtering', [
            'user_country' => $userLocation['country'] ?? 'Unknown',
            'country_code' => $countryCode
        ]);

        // Exclude users who have blocked this country
        return $query->whereDoesntHave('blockedLocations', function ($blockedQuery) use ($countryCode) {
            $blockedQuery->where('country_code', $countryCode);
        });
    }

    /**
     * Filter posts based on location restrictions
     *
     * @param Builder $query
     * @param array|null $userLocation
     * @return Builder
     */
    public function filterPostsByLocation(Builder $query, ?array $userLocation): Builder
    {
        if (!$userLocation || !isset($userLocation['country_code'])) {
            return $query;
        }

        $countryCode = $userLocation['country_code'];

        // Exclude posts from users who have blocked this country
        return $query->whereHas('user', function ($userQuery) use ($countryCode) {
            $userQuery->whereDoesntHave('blockedLocations', function ($blockedQuery) use ($countryCode) {
                $blockedQuery->where('country_code', $countryCode);
            });
        });
    }

    /**
     * Filter users based on location restrictions
     *
     * @param Builder $query
     * @param array|null $userLocation
     * @return Builder
     */
    public function filterUsersByLocation(Builder $query, ?array $userLocation): Builder
    {
        return $this->filterContentByLocation($query, $userLocation);
    }

    /**
     * Filter a collection of users based on blocked locations
     *
     * @param User $viewer
     * @param \Illuminate\Support\Collection $users
     * @return \Illuminate\Support\Collection
     */
    public function filterUsersByBlockedLocations(User $viewer, $users)
    {
        return $users->filter(function ($user) use ($viewer) {
            return !$this->userLocationService->isUserInBlockedLocation($viewer, $user);
        });
    }

    /**
     * Filter content based on user's stored location data
     *
     * @param Builder $query
     * @param User $viewer
     * @return Builder
     */
    public function filterContentByUserLocation(Builder $query, User $viewer): Builder
    {
        // If viewer has no location data, show all content
        if (!$viewer->country_code) {
            return $query;
        }

        // Exclude users who have blocked the viewer's location
        return $query->whereDoesntHave('blockedLocations', function ($blockedQuery) use ($viewer) {
            $blockedQuery->where(function ($q) use ($viewer) {
                // Check country level
                $q->where(function ($countryQ) use ($viewer) {
                    $countryQ->where('location_type', 'country')
                             ->where('country_code', $viewer->country_code);
                });
                
                // Check region level with flexible matching
                if ($viewer->region_name) {
                    $q->orWhere(function ($regionQ) use ($viewer) {
                        $regionQ->where('location_type', 'region')
                                ->where('country_code', $viewer->country_code)
                                ->where(function ($flexibleQ) use ($viewer) {
                                    // Exact match
                                    $flexibleQ->where('region_name', $viewer->region_name)
                                    // Partial matches (e.g., "Nairobi" in "Nairobi County")
                                    ->orWhere(function ($partialQ) use ($viewer) {
                                        $partialQ->whereRaw('LOWER(region_name) LIKE ?', ['%' . strtolower($viewer->region_name) . '%'])
                                                ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(region_name), "%")', [$viewer->region_name]);
                                    });
                                });
                    });
                }
                
                // Check city level with flexible region matching
                if ($viewer->city_name) {
                    $q->orWhere(function ($cityQ) use ($viewer) {
                        $cityQ->where('location_type', 'city')
                              ->where('country_code', $viewer->country_code)
                              ->where('city_name', $viewer->city_name)
                              ->where(function ($flexibleQ) use ($viewer) {
                                  // Exact region match
                                  $flexibleQ->where('region_name', $viewer->region_name)
                                  // Partial region matches
                                  ->orWhere(function ($partialQ) use ($viewer) {
                                      $partialQ->whereRaw('LOWER(region_name) LIKE ?', ['%' . strtolower($viewer->region_name) . '%'])
                                              ->orWhereRaw('LOWER(?) LIKE CONCAT("%", LOWER(region_name), "%")', [$viewer->region_name]);
                                  });
                              });
                    });
                }
            });
        });
    }

    /**
     * Check if a user is visible from a specific location
     *
     * @param User $user
     * @param array|null $userLocation
     * @return bool
     */
    public function isUserVisibleFromLocation(User $user, ?array $userLocation): bool
    {
        if (!$userLocation || !isset($userLocation['country_code'])) {
            return true;
        }

        $countryCode = $userLocation['country_code'];

        // Check if user has blocked this country
        $isBlocked = $user->blockedLocations()
            ->where('country_code', $countryCode)
            ->exists();

        // Log removed for production

        return !$isBlocked;
    }

    /**
     * Get blocked user IDs for a specific location
     *
     * @param string $countryCode
     * @return array
     */
    public function getBlockedUserIdsForLocation(string $countryCode): array
    {
        return User::whereHas('blockedLocations', function ($query) use ($countryCode) {
            $query->where('country_code', $countryCode);
        })->pluck('id')->toArray();
    }

    /**
     * Apply comprehensive location filtering to any query
     *
     * @param Builder $query
     * @param string $ip
     * @param string $filterType ('users'|'posts')
     * @return Builder
     */
    public function applyLocationFiltering(Builder $query, string $ip, string $filterType = 'users'): Builder
    {
        try {
            $userLocation = $this->locationService->getUserLocation($ip);
            
            if (!$userLocation) {
                return $query;
            }

            switch ($filterType) {
                case 'posts':
                    return $this->filterPostsByLocation($query, $userLocation);
                case 'users':
                default:
                    return $this->filterUsersByLocation($query, $userLocation);
            }
        } catch (\Exception $e) {
            Log::error('Error applying location filtering', [
                'ip' => $ip,
                'filter_type' => $filterType,
                'error' => $e->getMessage()
            ]);
            
            // Return original query if filtering fails
            return $query;
        }
    }
} 