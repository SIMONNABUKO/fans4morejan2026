<?php

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Contracts\UserRepositoryInterface;
use App\Http\Requests\UserRegistrationRequest;
use App\Services\VaultService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ReferralCodeService;

class UserRegistrationService
{
    private UserRepositoryInterface $userRepository;
    private VaultService $vaultService;
    private ReferralCodeService $referralCodeService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        VaultService $vaultService,
        ReferralCodeService $referralCodeService
    ) {
        $this->userRepository = $userRepository;
        $this->vaultService = $vaultService;
        $this->referralCodeService = $referralCodeService;
    }

    public function register(UserRegistrationRequest $request): User
    {
        Log::info('Starting user registration process', [
            'request_data' => $request->validated()
        ]);

        return DB::transaction(function () use ($request) {
            try {
                $validatedData = $request->validated();
                Log::info('Registration data validated', [
                    'validated_data' => $validatedData
                ]);

                $validatedData['avatar'] = $this->generateAvatar($validatedData['email']);
                
                // Store the referrer's code before generating the new user's code
                $referrerCode = $validatedData['referral_code'] ?? null;
                Log::info('Referral code handling', [
                    'referrer_code' => $referrerCode
                ]);
                
                // Remove the referrer's code from validated data
                unset($validatedData['referral_code']);
                
                // Generate a unique referral code for the new user
                $validatedData['referral_code'] = $this->generateUniqueReferralCode();
                Log::info('Generated new referral code', [
                    'new_referral_code' => $validatedData['referral_code']
                ]);
            
                $userData = $this->userRepository->createWithWallet($validatedData);
                Log::info('User and wallet created', [
                    'user_data' => $userData
                ]);

                if (!$userData['success']) {
                    Log::error('Failed to create user and wallet', [
                        'error' => $userData['error'] ?? 'Unknown error'
                    ]);
                    throw new \Exception($userData['error'] ?? 'Failed to create user and wallet');
                }

                $user = $userData['user'];
            
                $this->vaultService->createDefaultAlbums($user);
                Log::info('Default albums created for user', [
                    'user_id' => $user->id
                ]);

                // Handle referral if a referrer code was provided
                if ($referrerCode) {
                    $this->handleReferral($user, $referrerCode);
                    Log::info('Referral handled', [
                        'user_id' => $user->id,
                        'referrer_code' => $referrerCode
                    ]);
                }
            
                return $user;
            } catch (\Exception $e) {
                Log::error('Error in registration process', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    private function generateAvatar(string $email): string
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?d=mp";
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    private function handleReferral(User $user, string $referrerCode): void
    {
        try {
            Log::info('Processing referral', [
                'user_id' => $user->id,
                'referrer_code' => $referrerCode
            ]);

            // Find the referrer using the service that checks historical codes
            $referrer = $this->referralCodeService->findUserByReferralCode($referrerCode);
            
            if (!$referrer) {
                Log::info('No referrer found for code', [
                    'referrer_code' => $referrerCode
                ]);
                return;
            }

            // Prevent self-referral
            if ($referrer->id === $user->id) {
                Log::info('Self-referral prevented', [
                    'user_id' => $user->id
                ]);
                return;
            }

            // Check if user is already referred
            $existingReferral = Referral::where('referred_id', $user->id)->first();
            if ($existingReferral) {
                Log::info('User already referred', [
                    'user_id' => $user->id,
                    'existing_referral_id' => $existingReferral->id
                ]);
                return;
            }

            // Create the referral record
            $referral = new Referral();
            $referral->referrer_id = $referrer->id;
            $referral->referred_id = $user->id;
            $referral->referral_code = $referrerCode;
            $referral->status = 'active';
            $referral->save();

            Log::info('Referral record created', [
                'referral_id' => $referral->id,
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id
            ]);

        } catch (\Exception $e) {
            Log::error('Error handling referral', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'referrer_code' => $referrerCode
            ]);
            // Silently handle any errors
        }
    }
}

