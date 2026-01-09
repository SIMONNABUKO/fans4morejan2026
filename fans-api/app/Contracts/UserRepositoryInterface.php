<?php

namespace App\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface UserRepositoryInterface
{
    public function create(array $data): User;
    public function findByEmail(string $email): ?User;
    public function findByUsername(string $username): ?User;
    public function getUsersNotFollowedBy(User $user, int $limit = 5, $excludedUserIds = []): Collection;
    public function createWithWallet(array $data): array;
}

