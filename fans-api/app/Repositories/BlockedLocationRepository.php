<?php

namespace App\Repositories;

use App\Contracts\BlockedLocationRepositoryInterface;
use App\Models\BlockedLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BlockedLocationRepository implements BlockedLocationRepositoryInterface
{
    public function getBlockedLocations(User $user): Collection
    {
        return $user->blockedLocations;
    }

    public function create(array $data): BlockedLocation
    {
        return BlockedLocation::create($data);
    }

    public function delete(User $user, string $countryCode): bool
    {
        return $user->blockedLocations()->where('country_code', $countryCode)->delete();
    }
}

