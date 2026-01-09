<?php

namespace App\Services;

use App\Contracts\BlockedLocationRepositoryInterface;
use App\Models\BlockedLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class BlockedLocationService
{
    protected $blockedLocationRepository;
    protected $locationService;

    public function __construct(
        BlockedLocationRepositoryInterface $blockedLocationRepository,
        LocationService $locationService
    ) {
        $this->blockedLocationRepository = $blockedLocationRepository;
        $this->locationService = $locationService;
    }

    public function blockLocation(User $user, array $locationData): BlockedLocation
    {
        $countryCode = strtoupper($locationData['country_code']);
        $countryName = $locationData['country_name'] ?? 'Unknown';
        $locationType = $locationData['location_type'] ?? 'country';
        $regionName = $locationData['region_name'] ?? null;
        $cityName = $locationData['city_name'] ?? null;
        $latitude = $locationData['latitude'] ?? null;
        $longitude = $locationData['longitude'] ?? null;
        $displayName = $locationData['display_name'] ?? null;

        // Check if we have the new granular columns
        $hasGranularColumns = $this->columnExists('blocked_locations', 'location_type');
        
        if ($hasGranularColumns) {
            // New schema - check for exact location match
            if ($this->isExactLocationBlocked($user, $countryCode, $locationType, $regionName, $cityName)) {
                return $user->blockedLocations()
                    ->where('country_code', $countryCode)
                    ->where('location_type', $locationType)
                    ->where('region_name', $regionName)
                    ->where('city_name', $cityName)
                    ->first();
            }
            
            // Try to create the new granular entry
            try {
                $dataToStore = [
                    'user_id' => $user->id,
                    'country_code' => $countryCode,
                    'country_name' => $countryName,
                    'location_type' => $locationType,
                    'region_name' => $regionName,
                    'city_name' => $cityName,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'display_name' => $displayName,
                ];
                
                return $this->blockedLocationRepository->create($dataToStore);
            } catch (\Illuminate\Database\QueryException $e) {
                // If we get a duplicate key error, it means the old constraint still exists
                if (str_contains($e->getMessage(), 'Duplicate entry') && str_contains($e->getMessage(), 'user_id_country_code_unique')) {
                    // Fall back to country-level blocking
                    $existingBlock = $user->blockedLocations()
                        ->where('country_code', $countryCode)
                        ->first();
                    
                    if ($existingBlock) {
                        return $existingBlock;
                    }
                    
                    // Create country-level entry
                    $dataToStore = [
                        'user_id' => $user->id,
                        'country_code' => $countryCode,
                        'country_name' => $countryName,
                    ];
                    
                    return $this->blockedLocationRepository->create($dataToStore);
                }
                
                throw $e;
            }
        } else {
            // Old schema - check if country is already blocked at any level
            $existingBlock = $user->blockedLocations()
                ->where('country_code', $countryCode)
                ->first();
            
            if ($existingBlock) {
                return $existingBlock;
            }
            
            // Create country-level entry
            $dataToStore = [
                'user_id' => $user->id,
                'country_code' => $countryCode,
                'country_name' => $countryName,
            ];
            
            return $this->blockedLocationRepository->create($dataToStore);
        }
    }

    public function unblockLocation(User $user, int $locationId): bool
    {
        return $user->blockedLocations()->where('id', $locationId)->delete() > 0;
    }

    public function getBlockedLocations(User $user): Collection
    {
        return $this->blockedLocationRepository->getBlockedLocations($user);
    }

    /**
     * Check if a country is blocked by the user
     *
     * @param User $user
     * @param string $countryCode
     * @return bool
     */
    public function isLocationBlocked(User $user, string $countryCode): bool
    {
        return $user->blockedLocations()
            ->where('country_code', strtoupper($countryCode))
            ->exists();
    }

    /**
     * Check if an exact location is blocked by the user
     *
     * @param User $user
     * @param string $countryCode
     * @param string $locationType
     * @param string|null $regionName
     * @param string|null $cityName
     * @return bool
     */
    public function isExactLocationBlocked(User $user, string $countryCode, string $locationType, ?string $regionName = null, ?string $cityName = null): bool
    {
        $query = $user->blockedLocations()
            ->where('country_code', strtoupper($countryCode));

        // Only add location-specific checks if the columns exist
        if ($this->columnExists('blocked_locations', 'location_type')) {
            $query->where('location_type', $locationType);
        }
        if ($regionName && $this->columnExists('blocked_locations', 'region_name')) {
            $query->where('region_name', $regionName);
        }
        if ($cityName && $this->columnExists('blocked_locations', 'city_name')) {
            $query->where('city_name', $cityName);
        }

        return $query->exists();
    }

    /**
     * Get available countries for blocking
     *
     * @param User $user
     * @return array
     */
    public function getAvailableCountries(User $user): array
    {
        $allCountries = $this->locationService->getCountryList();
        $blockedCountryCodes = $user->blockedLocations()->pluck('country_code')->toArray();

        // Remove already blocked countries from the list
        return array_filter($allCountries, function($code) use ($blockedCountryCodes) {
            return !in_array($code, $blockedCountryCodes);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Check if a column exists in a table
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    private function columnExists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }
}

