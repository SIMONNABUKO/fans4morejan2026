<?php

namespace App\Services;

use App\Contracts\LoginRepositoryInterface;
use App\Http\Requests\LoginRequest;
use Illuminate\Validation\ValidationException;
use App\Services\CoverPhotoService;
use App\Services\UserLocationService;

class LoginService
{
    public function __construct(
        private LoginRepositoryInterface $loginRepository,
        private CoverPhotoService $coverPhotoService,
        private UserLocationService $userLocationService
    ) {}

    private function generateAvatar(string $email): string
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=200";
    }

    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();

        $user = $this->loginRepository->login(
            $validatedData['login'],
            $validatedData['password']
        );

        if (!$user) {
            throw ValidationException::withMessages([
                'login' => ['Invalid credentials']
            ]);
        }

        if (!$user->avatar) {
            $user->avatar = $this->generateAvatar($user->email);
            $user->save();
        }

        if (!$user->cover_photo) {
            $this->coverPhotoService->generateAndStoreCoverPhoto($user);
        }

        // Automatically detect and update user location on login
        $this->userLocationService->updateUserLocationFromIp($user, $request);

        // Reload the user to get the updated avatar, cover_photo, and location data
        $user = $user->fresh();

        return [
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken
        ];
    }
}

