<?php

namespace App\Repositories;

use App\Contracts\CreatorApplicationRepositoryInterface;
use App\Models\CreatorApplication;

class CreatorApplicationRepository implements CreatorApplicationRepositoryInterface
{
    public function create(array $data): CreatorApplication
    {
        return CreatorApplication::create($data);
    }

    public function findByUserId(int $userId): ?CreatorApplication
    {
       
        return CreatorApplication::where('user_id', $userId)->first();
    }

    public function update(CreatorApplication $application, array $data): bool
    {
        return $application->update($data);
    }
}