<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Subscription;
use App\Models\User;
use App\Models\GatewaySetting;
use App\Models\PostPurchase;
use App\Models\Post;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class TestPaymentService
{
    /**
     * Process a test payment directly and simulate the webhook response
     * 
     * @param Transaction $transaction The transaction to process
     * @param bool $shouldSucceed Whether the payment should succeed (for testing failures)
     * @return bool Whether the payment was successful
     */
    public function processPayment(Transaction $transaction, bool $shouldSucceed = true)
    {
        Log::info('Starting test payment processing', [
            'transaction_id' => $transaction->id,
            'type' => $transaction->type,
            'should_succeed' => $shouldSucceed,
            'sender_id' => $transaction->sender_id,
            'receiver_id' => $transaction->receiver_id,
            'amount' => $transaction->amount
        ]);

        if (!$shouldSucceed) {
            $transaction->status = Transaction::DECLINED_STATUS;
            $transaction->save();
            Log::info('Test payment declined as requested', [
                'transaction_id' => $transaction->id
            ]);
            return false;
        }

        // Begin transaction to ensure data consistency
        DB::beginTransaction();
        
        try {
            // Update transaction status to approved
            $transaction->status = Transaction::APPROVED_STATUS;
            
            // For subscription transactions, generate a test subscription ID
            if ($this->isSubscriptionType($transaction->type)) {
                Log::info('Processing subscription transaction', [
                    'transaction_id' => $transaction->id,
                    'type' => $transaction->type,
                    'tier_id' => $transaction->tier_id
                ]);

                // Generate a test subscription ID (format: TEST-SUB-{random-uuid})
                $subscriptionId = 'TEST-SUB-' . Str::uuid();
                $transaction->ccbill_subscription_id = $subscriptionId;
                
                Log::info('Generated test subscription ID', [
                    'transaction_id' => $transaction->id,
                    'subscription_id' => $subscriptionId
                ]);
            } 
            // For one-time purchase transactions, create a post purchase record
            else if ($transaction->type === Transaction::ONE_TIME_PURCHASE) {
                Log::info('Processing one-time purchase', [
                    'transaction_id' => $transaction->id,
                    'additional_data' => $transaction->additional_data
                ]);
                $this->createPostPurchase($transaction);
            }
            
            // Save the transaction after all updates
            $transaction->save();
            
            Log::info('Transaction updated with status and details', [
                'transaction_id' => $transaction->id,
                'status' => $transaction->status,
                'ccbill_subscription_id' => $transaction->ccbill_subscription_id ?? null
            ]);
            
            // Let PaymentService handle subscription creation and crediting receiver
            if ($this->paymentService) {
                Log::info('Delegating to PaymentService for subscription creation', [
                    'transaction_id' => $transaction->id,
                    'has_payment_service' => true
                ]);
                $this->paymentService->handleSuccessfulPayment($transaction);
            } else {
                Log::warning('PaymentService not set, falling back to direct credit', [
                    'transaction_id' => $transaction->id,
                    'has_payment_service' => false
                ]);
                // Fallback if PaymentService is not set
                $this->creditReceiverForTransaction($transaction);
            }
            
            DB::commit();
            
            Log::info('Test payment processed successfully', [
                'transaction_id' => $transaction->id,
                'final_status' => $transaction->status,
                'subscription_id' => $transaction->ccbill_subscription_id ?? null
            ]);
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error processing test payment', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
                'stack_trace' => $e->getTraceAsString()
            ]);
            
            // Mark the transaction as declined
            try {
                $transaction->status = Transaction::DECLINED_STATUS;
                $transaction->save();
            } catch (\Exception $innerException) {
                Log::error('Error updating transaction status after failed payment', [
                    'error' => $innerException->getMessage(),
                    'transaction_id' => $transaction->id,
                    'stack_trace' => $innerException->getTraceAsString()
                ]);
            }
            
            throw $e;
        }
    }

    /**
     * Create a post purchase record based on a transaction
     */
    private function createPostPurchase(Transaction $transaction)
    {
        // Extract post_id from additional_data
        $additionalData = $transaction->additional_data;
        $postId = null;
        
        if (is_array($additionalData) && isset($additionalData['post_id'])) {
            $postId = $additionalData['post_id'];
        } else if (isset($additionalData['tippable_id']) && isset($additionalData['tippable_type']) && $additionalData['tippable_type'] === 'post') {
            $postId = $additionalData['tippable_id'];
        }
        
        if (!$postId) {
            Log::error('Cannot create post purchase: No post_id found in transaction data', [
                'transaction_id' => $transaction->id,
                'additional_data' => $additionalData
            ]);
            throw new \Exception('No post_id found in transaction data');
        }
        
        // Check if the post exists
        $post = Post::find($postId);
        if (!$post) {
            Log::error('Cannot create post purchase: Post not found', [
                'transaction_id' => $transaction->id,
                'post_id' => $postId
            ]);
            throw new \Exception('Post not found');
        }
        
        // Check if purchase already exists
        $existingPurchase = Purchase::where('user_id', $transaction->sender_id)
            ->where('post_id', $postId)
            ->first();
            
        if ($existingPurchase) {
            Log::info('Post purchase already exists', [
                'transaction_id' => $transaction->id,
                'purchase_id' => $existingPurchase->id,
                'user_id' => $transaction->sender_id,
                'post_id' => $postId
            ]);
            return $existingPurchase;
        }
        
        // Create new purchase record
        $purchase = new Purchase();
        $purchase->user_id = $transaction->sender_id;
        $purchase->post_id = $postId;
        $purchase->transaction_id = $transaction->id;
        $purchase->amount = $transaction->amount;
        $purchase->purchased_at = now();
        $purchase->save();

        
        return $purchase;
    }

    /**
     * Create or update a subscription based on a transaction
     * This simulates what would happen when the webhook is received
     */
    private function createOrUpdateSubscription(Transaction $transaction)
    {
        Log::info('Starting createOrUpdateSubscription in TestPaymentService', [
            'transaction_id' => $transaction->id,
            'sender_id' => $transaction->sender_id,
            'tier_id' => $transaction->tier_id,
            'subscription_id' => $transaction->ccbill_subscription_id
        ]);

        if (!$transaction->tier_id) {
            Log::error('Cannot create subscription: No tier_id in transaction', [
                'transaction_id' => $transaction->id
            ]);
            throw new \Exception('No tier_id in transaction');
        }

        $duration = $this->getDurationFromTransactionType($transaction->type);
        $endDate = now()->addMonths($duration);
        
        Log::info('Calculated subscription duration and end date', [
            'transaction_id' => $transaction->id,
            'duration' => $duration,
            'end_date' => $endDate
        ]);
        
        try {
            // First check if there's an existing active subscription to update
            $subscription = Subscription::where('subscriber_id', $transaction->sender_id)
                ->where('tier_id', $transaction->tier_id)
                ->where('status', Subscription::ACTIVE_STATUS)
                ->first();
                
            Log::info('Checked for existing active subscription', [
                'transaction_id' => $transaction->id,
                'found_existing' => (bool)$subscription,
                'existing_subscription_id' => $subscription ? $subscription->id : null
            ]);

            if ($subscription) {
                Log::info('Updating existing subscription', [
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $transaction->id,
                    'current_end_date' => $subscription->end_date,
                    'new_end_date' => $endDate
                ]);

                // Create a new subscription for the new transaction
                $newSubscription = new Subscription();
                $newSubscription->subscriber_id = $transaction->sender_id;
                $newSubscription->creator_id = $transaction->receiver_id;
                $newSubscription->tier_id = $transaction->tier_id;
                $newSubscription->ccbill_subscription_id = $transaction->ccbill_subscription_id;
                $newSubscription->amount = $transaction->amount;
                $newSubscription->status = Subscription::ACTIVE_STATUS;
                $newSubscription->start_date = $subscription->end_date; // Start from the end of previous subscription
                $newSubscription->end_date = $endDate;
                $newSubscription->duration = $duration;
                $newSubscription->transaction_id = $transaction->id;
                $newSubscription->save();

                Log::info('Created new subscription record', [
                    'new_subscription_id' => $newSubscription->id,
                    'transaction_id' => $transaction->id,
                    'start_date' => $newSubscription->start_date,
                    'end_date' => $newSubscription->end_date
                ]);

                // Mark the old subscription as completed
                $subscription->status = Subscription::EXPIRED_STATUS;
                $subscription->save();

                Log::info('Marked old subscription as expired', [
                    'old_subscription_id' => $subscription->id,
                    'new_subscription_id' => $newSubscription->id,
                    'transaction_id' => $transaction->id
                ]);

                $subscription = $newSubscription;
            } else {
                Log::info('Creating new subscription (no existing active subscription)', [
                    'transaction_id' => $transaction->id,
                    'subscriber_id' => $transaction->sender_id,
                    'tier_id' => $transaction->tier_id
                ]);

                // Create a new subscription
                $subscription = new Subscription();
                $subscription->subscriber_id = $transaction->sender_id;
                $subscription->creator_id = $transaction->receiver_id;
                $subscription->tier_id = $transaction->tier_id;
                $subscription->ccbill_subscription_id = $transaction->ccbill_subscription_id;
                $subscription->amount = $transaction->amount;
                $subscription->status = Subscription::ACTIVE_STATUS;
                $subscription->start_date = now();
                $subscription->end_date = $endDate;
                $subscription->duration = $duration;
                $subscription->transaction_id = $transaction->id;
                $subscription->save();

                Log::info('Created first-time subscription', [
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $transaction->id,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date
                ]);
            }
            
            Log::info('Subscription created/updated successfully', [
                'subscription_id' => $subscription->id,
                'transaction_id' => $transaction->id,
                'status' => $subscription->status,
                'end_date' => $subscription->end_date,
                'ccbill_subscription_id' => $subscription->ccbill_subscription_id
            ]);
            
            return $subscription;
        } catch (\Exception $e) {
            Log::error('Error creating/updating subscription', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
                'subscriber_id' => $transaction->sender_id,
                'tier_id' => $transaction->tier_id,
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Credit the receiver's wallet for a successful transaction
     */
    private function creditReceiverForTransaction(Transaction $transaction)
    {
        Log::info("Transaction from TestPaymentService". json_encode($transaction));
        $receiver = User::find($transaction->receiver_id);
        if ($receiver) {
            // Get platform fee from settings
            $gatewaySettings = GatewaySetting::first();
            $platformFee = $transaction->amount * ($gatewaySettings->platform_fee_percentage / 100);
            $amountToCredit = $transaction->amount - $platformFee;
            
            // Load wallet service dynamically to avoid circular dependency
            $walletService = app(WalletService::class);
            
            // Pass the transaction to the wallet service
            $walletService->addFunds(
                $receiver, 
                $amountToCredit, 
                'available', 
                'Payment received', 
                $transaction
            );

            // Record platform fee
            $platformWalletService = app(PlatformWalletService::class);
            $feeType = $this->getFeeType($transaction);
            
            $platformWalletService->recordFee(
                $transaction,
                $platformFee,
                $transaction->amount,
                $gatewaySettings->platform_fee_percentage,
                $feeType,
                User::find($transaction->sender_id),
                $receiver
            );

            Log::info('Credited receiver and recorded platform fee', [
                'transaction_id' => $transaction->id,
                'receiver_id' => $receiver->id,
                'amount_credited' => $amountToCredit,
                'platform_fee' => $platformFee,
                'fee_type' => $feeType
            ]);
        }
    }

    private function getFeeType(Transaction $transaction): string
    {
        if (in_array($transaction->type, [
            Transaction::ONE_MONTH_SUBSCRIPTION,
            Transaction::TWO_MONTHS_SUBSCRIPTION,
            Transaction::THREE_MONTHS_SUBSCRIPTION,
            Transaction::SIX_MONTHS_SUBSCRIPTION,
            Transaction::YEARLY_SUBSCRIPTION,
            Transaction::SUBSCRIPTION_RENEWAL
        ])) {
            return 'subscription';
        }
        
        // Check if it's a tip
        if (isset($transaction->additional_data['context']) && $transaction->additional_data['context'] === 'message') {
            return 'tip';
        }
        
        // Check if it's a media purchase
        if ($transaction->type === Transaction::ONE_TIME_PURCHASE) {
            return 'media_purchase';
        }
        
        return 'other';
    }

    /**
     * Refund a test payment
     * 
     * @param Transaction $transaction The transaction to refund
     * @return bool Whether the refund was successful
     */
    public function refundPayment(Transaction $transaction)
    {
        Log::info('Processing test refund', [
            'transaction_id' => $transaction->id
        ]);
        
        try {
            DB::beginTransaction();
            
            // Find the sender to refund their money
            $sender = User::findOrFail($transaction->sender_id);
            
            // Load wallet service dynamically to avoid circular dependency
            $walletService = app(WalletService::class);
            $walletService->addFunds($sender, $transaction->amount, 'available');
            
            // Deduct from receiver if needed
            $this->deductMoneyForRefund($transaction);
            
            // If there's a subscription, update its status
            if ($transaction->subscription) {
                $transaction->subscription->status = Subscription::SUSPENDED_STATUS;
                $transaction->subscription->end_date = now();
                $transaction->subscription->save();
            }
            
            // If it's a post purchase, delete the purchase record
            if ($transaction->type === Transaction::ONE_TIME_PURCHASE) {
                $additionalData = $transaction->additional_data;
                $postId = null;
                
                if (is_array($additionalData) && isset($additionalData['post_id'])) {
                    $postId = $additionalData['post_id'];
                } else if (isset($additionalData['tippable_id']) && isset($additionalData['tippable_type']) && $additionalData['tippable_type'] === 'post') {
                    $postId = $additionalData['tippable_id'];
                }
            
                if ($postId) {
                    Purchase::where('user_id', $transaction->sender_id)
                        ->where('post_id', $postId)
                        ->delete();
                        
                    
                }
            }
            
            DB::commit();
            
            Log::info('Test refund processed successfully', [
                'transaction_id' => $transaction->id
            ]);
            
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error processing test refund', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);
            
            return false;
        }
    }

    /**
     * Deduct money for a refund from the receiver
     */
    private function deductMoneyForRefund(Transaction $transaction)
    {
        $receiver = User::find($transaction->receiver_id);
        if ($receiver) {
            $gatewaySettings = GatewaySetting::first();
            $amountToDeduct = $transaction->amount + $gatewaySettings->refund_fee;
            
            // Load wallet service dynamically to avoid circular dependency
            $walletService = app(WalletService::class);
            $walletService->subtractFunds($receiver, $amountToDeduct, 'available');
            
            Log::info('Deducted money for test refund', [
                'transaction_id' => $transaction->id,
                'receiver_id' => $receiver->id,
                'amount_deducted' => $amountToDeduct,
            ]);
        }
    }

    /**
     * Cancel a test subscription
     * 
     * @param string $subscriptionId The subscription ID to cancel
     * @return bool Whether the cancellation was successful
     */
    public function cancelSubscription($subscriptionId)
    {
        Log::info('Cancelling test subscription', [
            'subscription_id' => $subscriptionId
        ]);
        
        try {
            // Find the subscription by ID
            $subscription = Subscription::where('ccbill_subscription_id', $subscriptionId)->first();
            
            if ($subscription) {
                $subscription->status = Subscription::CANCELED_STATUS;
                $subscription->cancel_date = now();
                $subscription->save();
                
                Log::info('Test subscription cancelled successfully', [
                    'subscription_id' => $subscription->id,
                    'ccbill_subscription_id' => $subscriptionId
                ]);
                
                return true;
            } else {
                Log::warning('Subscription not found for cancellation', [
                    'ccbill_subscription_id' => $subscriptionId
                ]);
                
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error cancelling test subscription', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscriptionId
            ]);
            
            return false;
        }
    }

    /**
     * Check if a transaction type is a subscription type
     * 
     * @param string $type The transaction type
     * @return bool Whether the transaction type is a subscription type
     */
    private function isSubscriptionType($type)
    {
        return in_array($type, [
            Transaction::ONE_MONTH_SUBSCRIPTION,
            Transaction::THREE_MONTHS_SUBSCRIPTION,
            Transaction::SIX_MONTHS_SUBSCRIPTION,
            Transaction::YEARLY_SUBSCRIPTION
        ]);
    }

    /**
     * Get the subscription duration in months from the transaction type
     * 
     * @param string $type The transaction type
     * @return int The duration in months
     */
    private function getDurationFromTransactionType($type)
    {
        switch ($type) {
            case Transaction::ONE_MONTH_SUBSCRIPTION:
                return 1;
            case Transaction::THREE_MONTHS_SUBSCRIPTION:
                return 3;
            case Transaction::SIX_MONTHS_SUBSCRIPTION:
                return 6;
            case Transaction::YEARLY_SUBSCRIPTION:
                return 12;
            default:
                return 1; // Default to 1 month if type is not recognized
        }
    }

    /**
     * Set the PaymentService for this TestPaymentService
     * Used to avoid circular dependencies
     */
    private $paymentService;
    
    public function setPaymentService(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
}