<?php

namespace App\Services;

use App\Models\User;
use App\Models\ReferralCodeHistory;

class ReferralCodeService
{
    /**
     * Find a user by their referral code, including historical codes
     */
    public function findUserByReferralCode(string $code): ?User
    {
        // First try to find user with current code
        $user = User::where('referral_code', $code)->first();
        
        if ($user) {
            return $user;
        }

        // If not found, look in history for the most recent change
        $history = ReferralCodeHistory::where('old_code', $code)
            ->latest('changed_at')
            ->first();

        if ($history) {
            // Return the user with the new code
            return User::find($history->user_id);
        }

        return null;
    }

    /**
     * Get all valid referral codes for a user (current + historical)
     */
    public function getAllValidCodesForUser(User $user): array
    {
        $codes = [$user->referral_code];
        
        $historicalCodes = $user->referralCodeHistory()
            ->pluck('old_code')
            ->toArray();

        return array_merge($codes, $historicalCodes);
    }
} 