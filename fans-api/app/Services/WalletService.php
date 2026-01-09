<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    public function createWallet(User $user)
    {
        return Wallet::create([
            'user_id' => $user->id,
            'total_balance' => 0,
            'pending_balance' => 0,
            'available_for_payout' => 0,
        ]);
    }

    /**
     * Add funds to a user's wallet
     * 
     * @param User $user The user to add funds to
     * @param float $amount The amount to add
     * @param string $type The balance type (total, pending, available)
     * @param string $description Description of the transaction
     * @param Transaction|null $transaction Associated transaction (if any)
     * @param mixed $transactionable Polymorphic relation
     * @return Wallet The updated wallet
     */
    public function addFunds(User $user, $amount, $type = 'total', $description = 'Funds Added', $transaction = null, $transactionable = null)
    {

        return DB::transaction(function () use ($user, $amount, $type, $description, $transaction, $transactionable) {
            $wallet = $user->wallet;

            switch ($type) {
                case 'total':
                    $wallet->total_balance += $amount;
                    break;
                case 'pending':
                    $wallet->pending_balance += $amount;
                    break;
                case 'available':
                    $wallet->available_for_payout += $amount;
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid balance type: {$type}");
            }

            $wallet->save();

            // Create wallet history entry
            $walletHistory = new WalletHistory([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'balance_type' => $type,
                'transaction_type' => WalletHistory::TYPE_CREDIT,
                'payment_type'=> $transaction ? $transaction->type : null,
                'transactionable_id' => $transactionable ? $transactionable->id : null,
                'transactionable_type' => $transactionable ? get_class($transactionable) : null,
                'description' => $description,
                'status' => WalletHistory::STATUS_COMPLETED,
                'reference_id' => $this->generateReferenceId(),
            ]);

            // Associate with transaction if provided
            if ($transaction instanceof Transaction) {
                $walletHistory->transactionable_id = $transaction->id;
                
                // Use transaction type for description if not provided
                if ($description === 'Funds Added' && $transaction->type) {
                    $walletHistory->description = $this->getDescriptionFromTransactionType($transaction->type, true);
                }
                
                // Explicitly set the transactionable relationship
                $walletHistory->transactionable_id = $transaction->id;
                $walletHistory->transactionable_type = get_class($transaction);
                
               
            } 
            // If transaction is a string (transaction type), use it for description
            else if (is_string($transaction) && !empty($transaction)) {
                $walletHistory->description = $this->getDescriptionFromTransactionType($transaction, true);
            }

            // If a separate transactionable is provided, use that instead
            if ($transactionable) {
                $walletHistory->transactionable_id = $transactionable->id;
                $walletHistory->transactionable_type = get_class($transactionable);
                    
            }

            $walletHistory->save();

            return $wallet;
        });
    }

    /**
     * Subtract funds from a user's wallet
     * 
     * @param User $user The user to subtract funds from
     * @param float $amount The amount to subtract
     * @param string $type The balance type (total, pending, available)
     * @param string $description Description of the transaction
     * @param Transaction|null $transaction Associated transaction (if any)
     * @param mixed $transactionable Polymorphic relation
     * @return Wallet The updated wallet
     */
    public function subtractFunds(User $user, $amount, $type = 'total', $description = 'Funds Subtracted', $transaction = null, $transactionable = null)
    {
        return DB::transaction(function () use ($user, $amount, $type, $description, $transaction, $transactionable) {
            $wallet = $user->wallet;

            switch ($type) {
                case 'total':
                    if ($wallet->total_balance < $amount) {
                        throw new \Exception("Insufficient total balance");
                    }
                    $wallet->total_balance -= $amount;
                    break;
                case 'pending':
                    if ($wallet->pending_balance < $amount) {
                        throw new \Exception("Insufficient pending balance");
                    }
                    $wallet->pending_balance -= $amount;
                    break;
                case 'available':
                    if ($wallet->available_for_payout < $amount) {
                        throw new \Exception("Insufficient available balance");
                    }
                    $wallet->available_for_payout -= $amount;
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid balance type: {$type}");
            }

            $wallet->save();

            // Create wallet history entry
            $walletHistory = new WalletHistory([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'balance_type' => $type,
                'transaction_type' => WalletHistory::TYPE_DEBIT,
                'payment_type'=> $transaction ? $transaction->type : null,
                'transactionable_id' => $transactionable ? $transactionable->id : null,
                'transactionable_type' => $transactionable ? get_class($transactionable) : null,
                'description' => $description,
                'status' => WalletHistory::STATUS_COMPLETED,
                'reference_id' => $this->generateReferenceId(),
            ]);

            // Associate with transaction if provided
            if ($transaction instanceof Transaction) {
                $walletHistory->transactionable_id = $transaction->id;
                
                // Use transaction type for description if not provided
                if ($description === 'Funds Subtracted' && $transaction->type) {
                    $walletHistory->description = $this->getDescriptionFromTransactionType($transaction->type, false);
                }
                
                // Explicitly set the transactionable relationship
                $walletHistory->transactionable_id = $transaction->id;
                $walletHistory->transactionable_type = get_class($transaction);
                
              
            }
            // If transaction is a string (transaction type), use it for description
            else if (is_string($transaction) && !empty($transaction)) {
                $walletHistory->description = $this->getDescriptionFromTransactionType($transaction, false);
            }

            // If a separate transactionable is provided, use that instead
            if ($transactionable) {
                $walletHistory->transactionable_id = $transactionable->id;
                $walletHistory->transactionable_type = get_class($transactionable);
                
                // Log the association for debugging
                Log::info('Associating transactionable with wallet history (subtractFunds)', [
                    'transactionable_id' => $transactionable->id,
                    'transactionable_type' => get_class($transactionable),
                    'wallet_history_id' => $walletHistory->id ?? 'not_saved_yet'
                ]);
            }

            $walletHistory->save();

            return $wallet;
        });
    }

    public function movePendingToAvailable(User $user, $amount, $description = 'Pending to Available Transfer', $transaction = null)
    {
        return DB::transaction(function () use ($user, $amount, $description, $transaction) {
            $wallet = $user->wallet;

            if ($wallet->pending_balance < $amount) {
                throw new \Exception("Insufficient pending balance");
            }

            $wallet->pending_balance -= $amount;
            $wallet->available_for_payout += $amount;
            $wallet->save();

            // Create wallet history entry
            $walletHistory = new WalletHistory([
                'user_id' => $user->id,
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'balance_type' => WalletHistory::BALANCE_PENDING,
                'transaction_type' => WalletHistory::TYPE_TRANSFER,
                'description' => $description,
                'status' => WalletHistory::STATUS_COMPLETED,
                'reference_id' => $this->generateReferenceId(),
            ]);
            
            // Associate with transaction if provided
            if ($transaction instanceof Transaction) {
                $walletHistory->transactionable_id = $transaction->id;
                
                // Explicitly set the transactionable relationship
                $walletHistory->transactionable_id = $transaction->id;
                $walletHistory->transactionable_type = get_class($transaction);
                
               
            }

            $walletHistory->save();

            return $wallet;
        });
    }

    public function getWalletBalance(User $user)
    {
        $wallet = $user->wallet;

        return [
            'total_balance' => $wallet->total_balance,
            'pending_balance' => $wallet->pending_balance,
            'available_for_payout' => $wallet->available_for_payout,
        ];
    }

    public function getWalletWithHistory(User $user, $limit = 5)
    {
        $wallet = $user->wallet;
        $history = $user->walletHistories()
            ->with(['transaction', 'transactionable']) // Include both relationships
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return [
            'wallet' => $wallet,
            'history' => $history
        ];
    }

    /**
     * Generate a reference ID for wallet history entries
     */
    private function generateReferenceId()
    {
        return time() . rand(1000000, 9999999);
    }
    
    /**
     * Get a human-readable description from a transaction type
     * 
     * @param string $type The transaction type
     * @param bool $isCredit Whether this is a credit transaction
     * @return string The description
     */
    private function getDescriptionFromTransactionType($type, $isCredit = true)
    {
        $action = $isCredit ? 'Received from' : 'Paid for';
        
        switch ($type) {
            case Transaction::ONE_TIME_PURCHASE:
                return $isCredit ? 'Payment received for content' : 'Content purchase';
            case Transaction::ONE_MONTH_SUBSCRIPTION:
                return $isCredit ? 'Payment received for 1-month subscription' : '1-month subscription payment';
            case Transaction::TWO_MONTHS_SUBSCRIPTION:
                return $isCredit ? 'Payment received for 2-month subscription' : '2-month subscription payment';
            case Transaction::THREE_MONTHS_SUBSCRIPTION:
                return $isCredit ? 'Payment received for 3-month subscription' : '3-month subscription payment';
            case Transaction::SIX_MONTHS_SUBSCRIPTION:
                return $isCredit ? 'Payment received for 6-month subscription' : '6-month subscription payment';
            case Transaction::YEARLY_SUBSCRIPTION:
                return $isCredit ? 'Payment received for yearly subscription' : 'Yearly subscription payment';
            case Transaction::TIP:
                return $isCredit ? 'Tip received' : 'Tip sent';
            case Transaction::SUBSCRIPTION_RENEWAL:
                return $isCredit ? 'Subscription renewal payment received' : 'Subscription renewal payment';
            default:
                return $isCredit ? 'Funds Added' : 'Funds Subtracted';
        }
    }
}