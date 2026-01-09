<?php

namespace App\Contracts;

use App\Models\CreatorApplication;

interface CreatorApplicationRepositoryInterface
{
    public function create(array $data): CreatorApplication;
    public function findByUserId(int $userId): ?CreatorApplication;
    public function update(CreatorApplication $application, array $data): bool;
}