<?php

namespace App\Contracts;

use App\Models\User;

interface LoginRepositoryInterface
{
    public function login(string $login, string $password): ?User;
}