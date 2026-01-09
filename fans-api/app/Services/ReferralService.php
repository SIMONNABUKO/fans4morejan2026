<?php

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Models\ReferralEarning;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    /**
     * Calculate and create referral earnings for a transaction.
     */
    public function calculateEarnings(Transaction $transaction): void
    {
        // Get the buyer and creator from the transaction
        $buyer = $transaction->sender;
        $creator = $transaction->receiver;

        // Check if the buyer was referred by someone
        $buyerReferral = Referral::where('referred_id', $buyer->id)
            ->where('status', 'active')
            ->first();

        // Check if the creator was referred by someone
        $creatorReferral = Referral::where('referred_id', $creator->id)
            ->where('status', 'active')
            ->first();

        DB::transaction(function () use ($transaction, $buyerReferral, $creatorReferral) {
            // Calculate 1% of the transaction amount
            $referralAmount = $transaction->amount * 0.01;

            // Create referral earning for the buyer's referrer if exists
            if ($buyerReferral) {
                ReferralEarning::create([
                    'referral_id' => $buyerReferral->id,
                    'amount' => $referralAmount,
                    'type' => 'user',
                    'status' => 'pending',
                    'transaction_id' => $transaction->id,
                ]);
            }

            // Create referral earning for the creator's referrer if exists
            if ($creatorReferral) {
                ReferralEarning::create([
                    'referral_id' => $creatorReferral->id,
                    'amount' => $referralAmount,
                    'type' => 'creator',
                    'status' => 'pending',
                    'transaction_id' => $transaction->id,
                ]);
            }
        });
    }

    /**
     * Process pending referral earnings.
     */
    public function processPendingEarnings(): void
    {
        $pendingEarnings = ReferralEarning::where('status', 'pending')->get();

        foreach ($pendingEarnings as $earning) {
            DB::transaction(function () use ($earning) {
                // Get the referrer's wallet
                $referrer = $earning->referral->referrer;
                $wallet = $referrer->wallet;

                // Add the earning amount to the wallet
                $wallet->increment('balance', $earning->amount);

                // Create a wallet history record
                $wallet->histories()->create([
                    'amount' => $earning->amount,
                    'type' => 'credit',
                    'description' => 'Referral earning from ' . ($earning->type === 'user' ? 'user purchase' : 'creator earnings'),
                    'status' => 'completed'
                ]);

                // Mark the earning as paid
                $earning->update(['status' => 'paid']);
            });
        }
    }
} 