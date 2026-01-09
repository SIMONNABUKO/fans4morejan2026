<?php

namespace App\Repositories;

use App\Models\User;
use App\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Services\WalletService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class UserRepository implements UserRepositoryInterface
{
    private WalletService $walletService;
    protected $model;

    public function __construct(WalletService $walletService, User $model)
    {
        $this->walletService = $walletService;
        $this->model = $model;
    }

    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'avatar' => $data['avatar'] ?? null,
            'terms_accepted' => true,
            'email_verified_at' => now(),
            'confirmed_at' => now(),
            'referral_code' => $data['referral_code'] ?? null,
        ]);
    }

    public function createWithWallet(array $data): array
    {
        try {
            $user = DB::transaction(function () use ($data) {
                $user = $this->create($data);
                $this->walletService->createWallet($user);
                return $user;
            });
            return ['success' => true, 'user' => $user];
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByUsername(string $username): ?User
    {
        return User::where('username', $username)->first();
    }

    public function getAllUsers(): Collection
    {
        return $this->model->orderBy('created_at', 'desc')->get();
    }

    public function updateUser(int $id, array $data): ?User
    {
        $user = $this->findById($id);
        if (!$user) {
            return null;
        }

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);
        return $user;
    }

    public function deleteUser(int $id): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    public function getUsersNotFollowedBy(User $user, int $limit = 5, $excludedUserIds = []): Collection
    {
        $query = User::select('users.*')
            ->leftJoin('followers', function ($join) use ($user) {
                $join->on('users.id', '=', 'followers.followed_id')
                    ->where('followers.follower_id', '=', $user->id);
            })
            ->whereNull('followers.follower_id')
            ->where('users.id', '!=', $user->id)
            ->where('users.role', 'creator');

        // Filter out users who have disabled appearing in suggestions
        $query->where(function ($q) {
            $q->whereDoesntHave('settings', function ($settingsQuery) {
                $settingsQuery->whereHas('category', function ($categoryQuery) {
                    $categoryQuery->where('name', 'privacyAndSecurity');
                })->where('key', 'appear_in_suggestions')
                  ->where('value', 'false');
            });
        });

        // Filter out excluded user IDs (blocked/muted users)
        if (!empty($excludedUserIds)) {
            $query->whereNotIn('users.id', $excludedUserIds);
        }

        return $query->inRandomOrder()
            ->limit($limit)
            ->get();
    }
}
