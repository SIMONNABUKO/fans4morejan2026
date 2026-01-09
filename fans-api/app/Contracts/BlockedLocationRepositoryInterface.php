<?php

namespace App\Contracts;

use App\Models\BlockedLocation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface BlockedLocationRepositoryInterface
{
    public function getBlockedLocations(User $user): Collection;
    public function create(array $data): BlockedLocation;
    public function delete(User $user, string $countryCode): bool;
}

