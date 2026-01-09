<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\GatewaySetting;
use App\Models\Subscription;
use App\Models\User;
use App\Models\PlatformWallet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\PlatformWalletService;
use App\Services\ReferralService;

class PaymentService
{
    private $ccbillService;
    private $testPaymentService;
    private $gatewaySettings;
    private $walletService;
    private $platformWalletService;
    private $referralService;

    public function __construct(
        CCBillService $ccbillService,
        WalletService $walletService,
        TestPaymentService $testPaymentService,
        PlatformWalletService $platformWalletService,
        ReferralService $referralService
    ) {
        $this->ccbillService = $ccbillService;
        $this->testPaymentService = $testPaymentService;
        $this->walletService = $walletService;
        $this->platformWalletService = $platformWalletService;
        $this->referralService = $referralService;
        $this->gatewaySettings = GatewaySetting::first();
        // Ensure TestPaymentService always has a reference to PaymentService
        $this->testPaymentService->setPaymentService($this);
    }

    public function processPayment(
        User $user,
        float $amount,
        string $type,
        ?int $tierId = null,
        ?int $receiverId = null,
        array $additionalData = [],
        ?string $purchasableType = null,
        ?int $purchasableId = null
    ) {
        try {
            Log::info('Starting payment processing', [
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => $type,
                'tier_id' => $tierId,
                'receiver_id' => $receiverId,
                'additional_data' => $additionalData,
                'purchasable_type' => $purchasableType,
                'purchasable_id' => $purchasableId
            ]);

            // Create transaction record
            $transaction = Transaction::create([
                'sender_id' => $user->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'type' => $type,
                'status' => 'pending',
                'tier_id' => $tierId,
                'receiver_id' => $receiverId,
                'payment_method' => $additionalData['payment_method'] ?? 'wallet',
                'purchasable_type' => $purchasableType,
                'purchasable_id' => $purchasableId,
                'additional_data' => array_merge($additionalData, ['amount' => $amount])
            ]);

            Log::info('Transaction record created', [
                'transaction_id' => $transaction->id,
                'status' => $transaction->status
            ]);

            // If payment method is wallet, process it immediately
            if (($additionalData['payment_method'] ?? 'wallet') === 'wallet') {
                Log::info('Processing wallet payment', [
                    'transaction_id' => $transaction->id
                ]);

                $result = $this->processWalletPayment($transaction->id);
                
                Log::info('Wallet payment processed', [
                    'transaction_id' => $transaction->id,
                    'result' => $result
                ]);

                return $result;
            }

            // For CCBill payments
            Log::info('Processing CCBill payment', [
                'transaction_id' => $transaction->id
            ]);

            $result = $this->processExternalPayment(
                $user,
                $amount,
                $type,
                $tierId,
                $receiverId,
                $additionalData,
                $purchasableType,
                $purchasableId
            );
            
            Log::info('CCBill payment processed', [
                'transaction_id' => $transaction->id,
                'result' => $result
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Error in payment processing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'transaction_id' => $transaction->id ?? null
            ]);
            throw $e;
        }
    }
    
    /**
     * Process payment through external payment provider or test service
     */
    private function processExternalPayment(
        $user, 
        $amount, 
        $type, 
        $tierId = null, 
        $receiverId = null, 
        $additionalData = [],
        $purchasableType = null,
        $purchasableId = null
    ) {
        // Prepare additional data with payment method
        $paymentMethod = $this->gatewaySettings->test_mode ? 'test' : 'ccbill';
        
        $newAdditionalData = $additionalData;
        $newAdditionalData['payment_method'] = $paymentMethod;
        
        $transactionData = [
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'amount' => $amount,
            'type' => $type,
            'status' => Transaction::PENDING_STATUS,
            'tier_id' => $tierId,
            'additional_data' => $newAdditionalData
        ];
        
        // Add polymorphic relationship data if provided
        if ($purchasableType && $purchasableId) {
            $transactionData['purchasable_type'] = $purchasableType;
            $transactionData['purchasable_id'] = $purchasableId;
        }
        
        // Now that we have payment_method column, use it directly
        if (Schema::hasColumn('transactions', 'payment_method')) {
            $transactionData['payment_method'] = $paymentMethod;
        }
        
        $transaction = Transaction::create($transactionData);

        Log::info('Initiating external payment process', [
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => $type,
            'tierId' => $tierId,
            'receiverId' => $receiverId,
            'test_mode' => $this->gatewaySettings->test_mode
        ]);

        try {
            // If in test mode, process the payment directly
            if ($this->gatewaySettings->test_mode) {
                // Process the test payment (assuming success by default)
                $success = $this->testPaymentService->processPayment($transaction, true);
                
                if ($success) {
                    return [
                        'success' => true,
                        'payment_method' => 'test',
                        'transaction_id' => $transaction->id,
                        'redirect_required' => false,
                        'message' => 'Test payment processed successfully'
                    ];
                } else {
                    throw new \Exception("Test payment processing failed");
                }
            } else {
                // Use the real payment gateway
                switch ($this->gatewaySettings->active_gateway) {
                    case 'ccbill':
                        $paymentUrl = $this->ccbillService->generatePaymentUrl($transaction);
                        return [
                            'success' => true,
                            'payment_method' => 'ccbill',
                            'payment_url' => $paymentUrl,
                            'transaction_id' => $transaction->id,
                            'redirect_required' => true,
                            'message' => 'Please complete payment on CCBill'
                        ];
                    // Add cases for other payment gateways here
                    default:
                        throw new \Exception("Unsupported payment gateway");
                }
            }
        } catch (\Exception $e) {
            Log::error('Error processing payment', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);
            $transaction->status = Transaction::DECLINED_STATUS;
            $transaction->save();
            throw $e;
        }
    }
    
    /**
     * Process a wallet payment - this mimics the CCBill webhook flow
     */
    public function processWalletPayment($transactionId)
    {
        // First, log the transaction ID for debugging
       
        
        // Use a database transaction to ensure consistency
        DB::beginTransaction();
        
        try {
            $transaction = Transaction::findOrFail($transactionId);
            
           
            
            // Check if this is a wallet payment by looking at additional_data
            $additionalData = $transaction->additional_data;
            $isWalletPayment = false;
            
            // Check if payment_method column exists and is set to wallet
            if (Schema::hasColumn('transactions', 'payment_method') && $transaction->payment_method === 'wallet') {
                $isWalletPayment = true;
            }
            // Otherwise check in additional_data
            else if (is_array($additionalData) && isset($additionalData['payment_method']) && $additionalData['payment_method'] === 'wallet') {
                $isWalletPayment = true;
            }
            
            if (!$isWalletPayment) {
               
                
                // Try to update the additional_data to include payment_method
                $newAdditionalData = is_array($additionalData) ? $additionalData : [];
                $newAdditionalData['payment_method'] = 'wallet';
                $transaction->additional_data = $newAdditionalData;
                $transaction->save();
                
                // If payment_method column exists, set it directly
                if (Schema::hasColumn('transactions', 'payment_method')) {
                    $transaction->payment_method = 'wallet';
                }
                
                $transaction->save();
                
                // Reload the transaction to verify the fix
                $transaction = Transaction::findOrFail($transactionId);
                $additionalData = $transaction->additional_data;
                
                // Check again if it's a wallet payment
                if ((Schema::hasColumn('transactions', 'payment_method') && $transaction->payment_method === 'wallet') || 
                    (is_array($additionalData) && isset($additionalData['payment_method']) && $additionalData['payment_method'] === 'wallet')) {
                    $isWalletPayment = true;
                  
                } else {
                    
                    DB::rollBack();
                    throw new \Exception("Could not set transaction payment method to wallet");
                }
            }
            
            if ($transaction->status !== Transaction::PENDING_STATUS) {
                Log::error('Transaction is not in pending status', [
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status
                ]);
                DB::rollBack();
                throw new \Exception("Transaction is not in pending status");
            }
            
            $sender = User::findOrFail($transaction->sender_id);
            
            // Double-check wallet balance
            $walletBalance = $sender->wallet ? $sender->wallet->total_balance : 0;
            if ($walletBalance < $transaction->amount) {
                $transaction->status = Transaction::DECLINED_STATUS;
                $transaction->save();
                
                
                
                DB::commit();
                throw new \Exception("Insufficient wallet balance");
            }
            
            // Deduct from wallet's available_for_payout
            $this->walletService->subtractFunds($sender, $transaction->amount, 'total');
            
            // Mark transaction as approved
            $transaction->status = Transaction::APPROVED_STATUS;
            $transaction->save();
            
            // Handle successful payment (this includes subscription creation and crediting receiver)
            $this->handleSuccessfulPayment($transaction);

            Log::info('Transaction from PaymentService'. json_encode($transaction));
            
            DB::commit();
            
            Log::info('Wallet payment processed successfully', [
                'transaction_id' => $transaction->id,
                'user_id' => $sender->id,
                'amount' => $transaction->amount
            ]);
            
            return [
                'success' => true,
                'transaction_id' => $transaction->id,
                'message' => 'Payment processed successfully',
                'payment_method' => 'wallet',
                'redirect_required' => false
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
           
            
            // Try to update the transaction status if we have a transaction object
            try {
                $transaction = Transaction::find($transactionId);
                if ($transaction) {
                    $transaction->status = Transaction::DECLINED_STATUS;
                    $transaction->save();
                }
            } catch (\Exception $innerException) {
                Log::error('Failed to update transaction status after error', [
                    'error' => $innerException->getMessage(),
                    'transaction_id' => $transactionId
                ]);
            }
            
            throw $e;
        }
    }

    private function isSubscriptionType($type)
    {
        return in_array($type, [
            Transaction::ONE_MONTH_SUBSCRIPTION,
            Transaction::THREE_MONTHS_SUBSCRIPTION,
            Transaction::SIX_MONTHS_SUBSCRIPTION,
            Transaction::YEARLY_SUBSCRIPTION
        ]);
    }

    public function handleSuccessfulPayment(Transaction $transaction)
    {
        Log::info('Starting successful payment handling', [
            'transaction_id' => $transaction->id,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'additional_data' => $transaction->additional_data
        ]);

        $transaction->status = Transaction::APPROVED_STATUS;
        $transaction->save();

        if ($transaction->isSubscription()) {
            Log::info('Creating/updating subscription', [
                'transaction_id' => $transaction->id,
                'type' => $transaction->type
            ]);
            $this->createOrUpdateSubscription($transaction);
            // Eager load the subscription relationship
            $transaction->load('subscription');
        }

        $this->creditReceiverForTransaction($transaction);

        // Record tracking link action if present
        if (isset($transaction->additional_data['tracking_link_id'])) {
            $trackingLinkId = $transaction->additional_data['tracking_link_id'];
            Log::info('Processing tracking link action', [
                'transaction_id' => $transaction->id,
                'tracking_link_id' => $trackingLinkId
            ]);

            $trackingLink = \App\Models\TrackingLink::find($trackingLinkId);
            
            if ($trackingLink) {
                $actionType = $transaction->type;
                $actionData = [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount
                ];
                
                if ($transaction->isSubscription()) {
                    $actionData['subscription_id'] = $transaction->subscription->id;
                } else if ($transaction->type === Transaction::TIP) {
                    $actionData['tip_id'] = $transaction->purchasable_id;
                }
                
                Log::info('Recording tracking link action', [
                    'tracking_link_id' => $trackingLinkId,
                    'action_type' => $actionType,
                    'action_data' => $actionData
                ]);

                app(\App\Services\TrackingLinkActionService::class)->trackAction(
                    $trackingLink,
                    $actionType,
                    $transaction->sender_id,
                    $actionData
                );

                Log::info('Tracking link action recorded successfully', [
                    'tracking_link_id' => $trackingLinkId,
                    'action_type' => $actionType
                ]);
            } else {
                Log::warning('Tracking link not found', [
                    'tracking_link_id' => $trackingLinkId,
                    'transaction_id' => $transaction->id
                ]);
            }
        }

        // Defensive: Only call if subscription exists
        if ($transaction->subscription) {
            Log::info('Calculating referral earnings', [
                'transaction_id' => $transaction->id,
                'subscription_id' => $transaction->subscription->id
            ]);
            $this->referralService->calculateEarnings($transaction);
        } else {
            Log::warning('No subscription found for referral calculation', [
                'transaction_id' => $transaction->id
            ]);
        }

        Log::info('Payment processing completed successfully', [
            'transaction_id' => $transaction->id,
            'status' => $transaction->status
        ]);
    }

    public function handleFailedPayment(Transaction $transaction)
    {
        $transaction->status = Transaction::DECLINED_STATUS;
        $transaction->save();

        Log::info('Payment failed', ['transaction_id' => $transaction->id]);
    }

    public function refundPayment(Transaction $transaction)
    {
        if ($transaction->status !== Transaction::APPROVED_STATUS) {
            throw new \Exception("Only approved transactions can be refunded.");
        }

        try {
            // Get payment method from additional_data or payment_method column
            $paymentMethod = 'ccbill'; // Default
            
            if (Schema::hasColumn('transactions', 'payment_method') && $transaction->payment_method) {
                $paymentMethod = $transaction->payment_method;
            } else {
                $additionalData = $transaction->additional_data;
                if (is_array($additionalData) && isset($additionalData['payment_method'])) {
                    $paymentMethod = $additionalData['payment_method'];
                }
            }
            
            // Handle refund differently based on payment method
            if ($paymentMethod === 'wallet') {
                // For wallet payments, just credit back the sender and deduct from receiver
                $sender = User::findOrFail($transaction->sender_id);
                $this->walletService->addFunds($sender, $transaction->amount, 'available');
                $this->deductMoneyForRefund($transaction);
            } else if ($paymentMethod === 'test') {
                // For test payments, use the test payment service
                $refundSuccess = $this->testPaymentService->refundPayment($transaction);
                if (!$refundSuccess) {
                    throw new \Exception("Test refund failed");
                }
            } else {
                // For external payments, process through the gateway
                switch ($this->gatewaySettings->active_gateway) {
                    case 'ccbill':
                        $refundSuccess = $this->ccbillService->refundPayment($transaction);
                        if (!$refundSuccess) {
                            throw new \Exception("CCBill refund failed");
                        }
                        break;
                    // Add cases for other payment gateways here
                    default:
                        throw new \Exception("Unsupported payment gateway for refunds");
                }
                $this->deductMoneyForRefund($transaction);
            }
            
            $transaction->status = Transaction::REFUNDED_STATUS;
            $transaction->save();

            if ($transaction->subscription) {
                $transaction->subscription->status = Subscription::SUSPENDED_STATUS;
                $transaction->subscription->end_date = now();
                $transaction->subscription->save();
            }

            Log::info('Payment refunded successfully', ['transaction_id' => $transaction->id]);
        } catch (\Exception $e) {
            Log::error('Error refunding payment', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);
            throw $e;
        }
    }

    public function cancelSubscription(Subscription $subscription)
    {
        try {
            // Get the payment method from the original transaction
            $paymentMethod = 'ccbill'; // Default
            
            if ($subscription->transaction) {
                if (Schema::hasColumn('transactions', 'payment_method') && $subscription->transaction->payment_method) {
                    $paymentMethod = $subscription->transaction->payment_method;
                } else {
                    $additionalData = $subscription->transaction->additional_data;
                    if (is_array($additionalData) && isset($additionalData['payment_method'])) {
                        $paymentMethod = $additionalData['payment_method'];
                    }
                }
            }
            
            // Handle cancellation differently based on payment method
            if ($paymentMethod === 'test') {
                // For test subscriptions, use the test payment service
                $this->testPaymentService->cancelSubscription($subscription->ccbill_subscription_id);
            } else {
                // For external subscriptions, process through the gateway
                switch ($this->gatewaySettings->active_gateway) {
                    case 'ccbill':
                        $this->ccbillService->cancelSubscription($subscription->ccbill_subscription_id);
                        break;
                    // Add cases for other payment gateways here
                    default:
                        throw new \Exception("Unsupported payment gateway for subscription cancellation");
                }
            }

            $subscription->status = Subscription::CANCELED_STATUS;
            $subscription->cancel_date = now();
            $subscription->save();

            Log::info('Subscription cancelled successfully', ['subscription_id' => $subscription->id]);
        } catch (\Exception $e) {
            Log::error('Error cancelling subscription', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
            ]);
            throw $e;
        }
    }

    private function createOrUpdateSubscription(Transaction $transaction)
    {
        Log::info('Starting createOrUpdateSubscription in PaymentService', [
            'transaction_id' => $transaction->id,
            'sender_id' => $transaction->sender_id,
            'receiver_id' => $transaction->receiver_id,
            'tier_id' => $transaction->tier_id,
            'ccbill_subscription_id' => $transaction->ccbill_subscription_id,
            'amount' => $transaction->amount,
            'status' => Subscription::ACTIVE_STATUS,
            'start_date' => now(),
            'end_date' => $this->calculateSubscriptionEndDate($transaction),
            'duration' => $this->getDurationFromTransactionType($transaction->type),
            'transaction_id' => $transaction->id,
        ]);

        if (!$transaction->tier_id) {
            Log::error('Cannot create subscription: No tier_id in transaction', [
                'transaction_id' => $transaction->id
            ]);
            throw new \Exception('No tier_id in transaction');
        }

        $where = [
            'subscriber_id' => $transaction->sender_id,
            'tier_id' => $transaction->tier_id
        ];
        $attributes = [
            'creator_id' => $transaction->receiver_id,
            'ccbill_subscription_id' => $transaction->ccbill_subscription_id,
            'amount' => $transaction->amount,
            'status' => Subscription::ACTIVE_STATUS,
            'start_date' => now(),
            'end_date' => $this->calculateSubscriptionEndDate($transaction),
            'duration' => $this->getDurationFromTransactionType($transaction->type),
            'transaction_id' => $transaction->id,
        ];
        Log::info('Attempting Subscription::updateOrCreate', [
            'where' => $where,
            'attributes' => $attributes
        ]);

        try {
            $subscription = Subscription::updateOrCreate($where, $attributes);
            Log::info('Subscription updateOrCreate result', [
                'subscription' => $subscription->toArray()
            ]);
            // Immediately check if the subscription exists in the DB
            $dbSubscription = Subscription::find($subscription->id);
            Log::info('Immediate DB check for subscription', [
                'subscription_id' => $subscription->id,
                'db_subscription_exists' => $dbSubscription !== null,
                'db_subscription' => $dbSubscription ? $dbSubscription->toArray() : null,
                'database' => DB::connection()->getDatabaseName()
            ]);
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
                'attributes' => $attributes,
                'where' => $where,
                'stack_trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function creditReceiverForTransaction(Transaction $transaction)
    {
        $receiver = User::find($transaction->receiver_id);
        if ($receiver) {
            // Calculate platform fee
            $platformFee = $transaction->amount * ($this->gatewaySettings->platform_fee_percentage / 100);
            $amountToCredit = $transaction->amount - $platformFee;
            
            // Credit receiver's wallet
            $this->walletService->addFunds(
                $receiver, 
                $amountToCredit, 
                'available', 
                'Payment received', 
                $transaction
            );

            // Record platform fee using the new service
            $this->platformWalletService->recordFee(
                $transaction,
                $platformFee,
                $transaction->amount,
                $this->gatewaySettings->platform_fee_percentage,
                $this->getFeeType($transaction),
                User::find($transaction->sender_id),
                $receiver
            );

            Log::info('Credited receiver and platform for transaction', [
                'transaction_id' => $transaction->id,
                'receiver_id' => $receiver->id,
                'amount_credited' => $amountToCredit,
                'platform_fee' => $platformFee
            ]);
        }
    }

    private function getFeeType(Transaction $transaction): string
    {
        if ($this->isSubscriptionType($transaction->type)) {
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

    private function deductMoneyForRefund(Transaction $transaction)
    {
        $receiver = User::find($transaction->receiver_id);
        if ($receiver) {
            // Calculate the original platform fee that was taken
            $originalPlatformFee = $transaction->amount * ($this->gatewaySettings->platform_fee_percentage / 100);
            
            // The total amount to deduct from receiver includes the refund fee
            $amountToDeduct = $transaction->amount + $this->gatewaySettings->refund_fee;
            
            // Pass the transaction to the wallet service
            try {
                $this->walletService->subtractFunds(
                    $receiver, 
                    $amountToDeduct, 
                    'available', 
                    'Refund processed', 
                    $transaction
                );

                // Record platform fee refund
                $this->platformWalletService->recordFee(
                    $transaction,
                    -$originalPlatformFee, // Negative amount for refund
                    $transaction->amount,
                    $this->gatewaySettings->platform_fee_percentage,
                    'refund_' . $this->getFeeType($transaction),
                    User::find($transaction->sender_id),
                    $receiver
                );
                
                Log::info('Deducted money for refund', [
                    'transaction_id' => $transaction->id,
                    'receiver_id' => $receiver->id,
                    'amount_deducted' => $amountToDeduct,
                    'platform_fee_refunded' => $originalPlatformFee
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to deduct money for refund', [
                    'error' => $e->getMessage(),
                    'transaction_id' => $transaction->id,
                    'receiver_id' => $receiver->id,
                    'amount_to_deduct' => $amountToDeduct,
                    'platform_fee_to_refund' => $originalPlatformFee
                ]);
                
                throw $e;
            }
        }
    }

    private function calculateSubscriptionEndDate(Transaction $transaction)
    {
        $duration = $this->getDurationFromTransactionType($transaction->type);
        return now()->addMonths($duration);
    }

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
}