<?php

namespace App\Services;

use App\Models\PlatformWallet;
use App\Models\PlatformWalletHistory;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlatformWalletService
{
    /**
     * Record a platform fee transaction
     */
    public function recordFee(
        Transaction $transaction,
        float $amount,
        float $originalAmount,
        float $feePercentage,
        string $feeType,
        ?User $sender = null,
        ?User $receiver = null
    ) {
        return DB::transaction(function () use ($transaction, $amount, $originalAmount, $feePercentage, $feeType, $sender, $receiver) {
            // Get or create platform wallet
            $platformWallet = PlatformWallet::firstOrCreate(['id' => 1], ['balance' => 0]);
            
            // Update platform wallet balance
            $platformWallet->balance += $amount;
            $platformWallet->save();

            // Create history record
            return PlatformWalletHistory::create([
                'amount' => $amount,
                'transaction_type' => 'CREDIT',
                'description' => "Platform fee from {$feeType}",
                'transaction_id' => $transaction->id,
                'sender_id' => $sender?->id,
                'receiver_id' => $receiver?->id,
                'fee_type' => $feeType,
                'original_amount' => $originalAmount,
                'fee_percentage' => $feePercentage,
                'status' => 'completed'
            ]);
        });
    }

    /**
     * Get platform wallet statistics
     */
    public function getStats()
    {
        $platformWallet = PlatformWallet::firstOrCreate(['id' => 1], ['balance' => 0]);
        
        return [
            'current_balance' => $platformWallet->balance,
            'total_earnings' => PlatformWalletHistory::where('transaction_type', 'CREDIT')->sum('amount'),
            'today_earnings' => PlatformWalletHistory::where('transaction_type', 'CREDIT')
                ->whereDate('created_at', today())
                ->sum('amount'),
            'month_earnings' => PlatformWalletHistory::where('transaction_type', 'CREDIT')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount')
        ];
    }

    /**
     * Get paginated platform wallet history
     */
    public function getHistory($perPage = 15)
    {
        return PlatformWalletHistory::with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Record a withdrawal from the platform wallet
     */
    public function withdraw(float $amount, string $description)
    {
        return DB::transaction(function () use ($amount, $description) {
            $platformWallet = PlatformWallet::firstOrCreate(['id' => 1], ['balance' => 0]);
            
            if ($platformWallet->balance < $amount) {
                throw new \Exception('Insufficient balance for withdrawal');
            }

            // Update platform wallet balance
            $platformWallet->balance -= $amount;
            $platformWallet->save();

            // Create history record
            return PlatformWalletHistory::create([
                'amount' => $amount,
                'transaction_type' => 'DEBIT',
                'description' => $description,
                'fee_type' => 'withdrawal',
                'original_amount' => $amount,
                'fee_percentage' => 0,
                'status' => 'completed'
            ]);
        });
    }
} 