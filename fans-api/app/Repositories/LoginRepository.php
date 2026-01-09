<?php

namespace App\Repositories;

use App\Contracts\LoginRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginRepository implements LoginRepositoryInterface
{
    public function login(string $login, string $password): ?User
    {
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            return $user;
        }

        return null;
    }
}