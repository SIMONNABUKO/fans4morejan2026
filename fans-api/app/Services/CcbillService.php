<?php

namespace App\Services;

use App\Models\CcbillConfig;
use App\Models\GatewaySetting;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class CCBillService
{
    private $ccbillConfig;
    private $gatewaySettings;

    public function __construct()
    {
        $this->ccbillConfig = CcbillConfig::first();
        $this->gatewaySettings = GatewaySetting::first();
    }

    public function generatePaymentUrl($transaction)
    {
        Log::info('Generating CCBill payment URL', [
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'type' => $transaction->type
        ]);

        try {
            $token = $this->generateUniqueTransactionToken($transaction);
            $user = User::findOrFail($transaction->sender_id);
            $amount = $transaction->amount;
            $isSubscription = $this->isSubscriptionPayment($transaction->type);

            $formData = [
                'clientAccnum' => $this->ccbillConfig->ccbill_account_number,
                'clientSubacc' => $isSubscription ? $this->ccbillConfig->ccbill_subaccount_number_recurring : $this->ccbillConfig->ccbill_subaccount_number_one_time,
                'currencyCode' => $this->getCurrencyCode('USD'),
                'formDigest' => $this->generateFormDigest($amount, $isSubscription, $transaction),
                'initialPrice' => $amount,
                'initialPeriod' => $this->getInitialPeriod($transaction),
                'token' => $token,
                'customer_fname' => $user->username ?? 'Unknown',
                'customer_lname' => $user->name ?? 'User',
                'email' => $user->email ?? '',
                'address1' => '',
                'city' => '',
                'state' => '',
                'zipcode' => '',
                'country' => '',
            ];

            if ($isSubscription) {
                $formData = array_merge($formData, [
                    'recurringPrice' => $amount,
                    'recurringPeriod' => $this->getRecurringPeriod($transaction),
                    'numRebills' => 99,
                ]);
            }

            $flexformId = $this->ccbillConfig->ccbill_flex_form_id;
            $url = "https://api.ccbill.com/wap-frontflex/flexforms/{$flexformId}?" . http_build_query($formData);

            Log::info('Generated CCBill payment URL', [
                'transaction_id' => $transaction->id,
                'url' => $url
            ]);

            return $url;
        } catch (\Exception $e) {
            Log::error('Error generating CCBill payment URL', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function processWebhook($request)
    {
        $eventType = $request->get('eventType');
        $eventBody = json_decode($request->getContent(), true);

        Log::info('CCBill webhook received', ['eventType' => $eventType, 'eventBody' => $eventBody]);

        switch ($eventType) {
            case 'NewSaleSuccess':
            case 'NewSaleFailure':
                $this->handleNewSale($eventBody, $eventType === 'NewSaleSuccess');
                break;
            case 'Refund':
                $this->handleRefund($eventBody);
                break;
            case 'RenewalSuccess':
            case 'Renewal Failure':
            case 'Cancellation':
            case 'Expiration':
                $this->handleSubscriptionEvent($eventBody, $eventType);
                break;
        }
    }

    public function cancelSubscription($ccbillSubscriptionId)
    {
        $client = new Client();
        $response = $client->get('https://datalink.ccbill.com/utils/subscriptionManagement.cgi', [
            'query' => [
                'clientAccnum' => $this->ccbillConfig->ccbill_account_number,
                'clientSubacc' => $this->ccbillConfig->ccbill_subaccount_number_recurring,
                'username' => $this->ccbillConfig->ccbill_datalink_username,
                'password' => $this->ccbillConfig->ccbill_datalink_password,
                'subscriptionId' => $ccbillSubscriptionId,
                'action' => 'cancelSubscription',
            ],
        ]);

        $result = $response->getBody()->getContents();
        $success = strpos($result, 'results,1') !== false;

        Log::info('CCBill subscription cancellation attempt', [
            'subscriptionId' => $ccbillSubscriptionId,
            'success' => $success,
        ]);

        return $success;
    }

    public function refundPayment(Transaction $transaction)
    {
        Log::info('Initiating CCBill refund', ['transaction_id' => $transaction->id]);

        $client = new Client();
        $response = $client->get('https://datalink.ccbill.com/utils/refundTransaction.cgi', [
            'query' => [
                'clientAccnum' => $this->ccbillConfig->ccbill_account_number,
                'clientSubacc' => $this->ccbillConfig->ccbill_subaccount_number_one_time,
                'username' => $this->ccbillConfig->ccbill_datalink_username,
                'password' => $this->ccbillConfig->ccbill_datalink_password,
                'transactionId' => $transaction->ccbill_transaction_id,
                'amount' => $transaction->amount,
            ],
        ]);

        $result = $response->getBody()->getContents();
        $success = strpos($result, 'results,1') !== false;

        if ($success) {
            Log::info('CCBill refund successful', ['transaction_id' => $transaction->id]);
        } else {
            Log::error('CCBill refund failed', ['transaction_id' => $transaction->id, 'response' => $result]);
            throw new \Exception('CCBill refund failed: ' . $result);
        }

        return $success;
    }

    private function generateUniqueTransactionToken($transaction)
    {
        do {
            $token = Uuid::uuid4()->getHex();
        } while (Transaction::where('ccbill_payment_token', $token)->exists());

        $transaction->ccbill_payment_token = $token;
        $transaction->save();

        return $token;
    }

    private function isSubscriptionPayment($transactionType)
    {
        return in_array($transactionType, [
            Transaction::ONE_MONTH_SUBSCRIPTION,
            Transaction::TWO_MONTHS_SUBSCRIPTION,
            Transaction::THREE_MONTHS_SUBSCRIPTION,
            Transaction::SIX_MONTHS_SUBSCRIPTION,
            Transaction::YEARLY_SUBSCRIPTION,
        ]);
    }

    private function getCurrencyCode($currency)
    {
        $codes = [
            'USD' => '840',
            'EUR' => '978',
            'GBP' => '826',
            'JPY' => '392',
            'CAD' => '124',
            'AUD' => '036',
        ];

        return $codes[$currency] ?? '840'; // Default to USD
    }

    private function generateFormDigest($amount, $isSubscription, $transaction = null)
    {
        $initialPeriod = $this->getInitialPeriod($transaction);
        $currencyCode = $this->getCurrencyCode('USD');

        if ($isSubscription) {
            $recurringPeriod = $this->getRecurringPeriod($transaction);
            $numRebills = 99;
            return md5("{$amount}{$initialPeriod}{$amount}{$recurringPeriod}{$numRebills}{$currencyCode}{$this->ccbillConfig->ccbill_salt_key}");
        } else {
            return md5("{$amount}{$initialPeriod}{$currencyCode}{$this->ccbillConfig->ccbill_salt_key}");
        }
    }

    private function getInitialPeriod($transaction = null)
    {
        if (!$transaction) {
            return 30; // Default to 30 days if no transaction is provided
        }

        switch ($transaction->type) {
            case Transaction::ONE_MONTH_SUBSCRIPTION:
                return 30;
            case Transaction::TWO_MONTHS_SUBSCRIPTION:
                return 60;
            case Transaction::THREE_MONTHS_SUBSCRIPTION:
                return 90;
            case Transaction::SIX_MONTHS_SUBSCRIPTION:
                return 180;
            case Transaction::YEARLY_SUBSCRIPTION:
                return 365;
            default:
                return 30; // Default to 30 days for other transaction types
        }
    }

    private function getRecurringPeriod($transaction = null)
    {
        return $this->getInitialPeriod($transaction);
    }

    private function handleNewSale($eventBody, $success)
    {
        $transaction = Transaction::where('ccbill_payment_token', $eventBody['X-token'])->first();

        if ($transaction) {
            $transaction->ccbill_transaction_id = $eventBody['transactionId'] ?? null;
            $transaction->ccbill_subscription_id = $eventBody['subscriptionId'] ?? null;
            $transaction->status = $success ? Transaction::APPROVED_STATUS : Transaction::DECLINED_STATUS;
            $transaction->save();

            if ($success && $this->isSubscriptionPayment($transaction->type)) {
                $this->createOrUpdateSubscription($transaction, $eventBody);
            }

            if ($success) {
                $this->creditReceiverForTransaction($transaction);
            }
        }
    }

    private function handleRefund($eventBody)
    {
        $transaction = Transaction::where('ccbill_transaction_id', $eventBody['transactionId'])->first();

        if ($transaction && $transaction->status === Transaction::APPROVED_STATUS) {
            $this->deductMoneyForRefund($transaction);
            $transaction->status = Transaction::REFUNDED_STATUS;
            $transaction->save();

            if ($transaction->subscription) {
                $transaction->subscription->status = Subscription::SUSPENDED_STATUS;
                $transaction->subscription->end_date = now();
                $transaction->subscription->save();
            }
        }
    }

    private function handleSubscriptionEvent($eventBody, $eventType)
    {
        $subscription = Subscription::where('ccbill_subscription_id', $eventBody['subscriptionId'])->first();

        if ($subscription) {
            switch ($eventType) {
                case 'RenewalSuccess':
                    $this->handleRenewalSuccess($subscription, $eventBody);
                    break;
                case 'Renewal Failure':
                    $subscription->status = Subscription::SUSPENDED_STATUS;
                    break;
                case 'Cancellation':
                    $subscription->status = Subscription::CANCELED_STATUS;
                    $subscription->cancel_date = now();
                    break;
                case 'Expiration':
                    $subscription->status = Subscription::EXPIRED_STATUS;
                    break;
            }

            $subscription->save();
        }
    }

    private function createOrUpdateSubscription($transaction, $eventBody)
    {
        $subscription = Subscription::updateOrCreate(
            ['transaction_id' => $transaction->id],
            [
                'ccbill_subscription_id' => $eventBody['subscriptionId'],
                'subscriber_id' => $transaction->sender_id,
                'creator_id' => $transaction->receiver_id,
                'tier_id' => $transaction->tier_id,
                'amount' => $transaction->amount,
                'status' => Subscription::ACTIVE_STATUS,
                'start_date' => now(),
                'end_date' => now()->addDays($this->getInitialPeriod($transaction)),
                'duration' => $transaction->duration,
            ]
        );

        // You might want to trigger a notification here
        // NotificationService::createNewSubscriptionNotification($subscription);
    }

    private function handleRenewalSuccess($subscription, $eventBody)
    {
        $transaction = $this->createRenewalTransaction($subscription);
        $subscription->end_date = new \DateTime($eventBody['nextRenewalDate']);
        $subscription->status = Subscription::ACTIVE_STATUS;
        $this->creditReceiverForTransaction($transaction);
    }

    private function createRenewalTransaction($subscription)
    {
        return Transaction::create([
            'sender_id' => $subscription->user_id,
            'receiver_id' => $subscription->tier->user_id,
            'type' => Transaction::SUBSCRIPTION_RENEWAL,
            'status' => Transaction::APPROVED_STATUS,
            'amount' => $subscription->amount,
            'ccbill_subscription_id' => $subscription->ccbill_subscription_id,
        ]);
    }

    private function creditReceiverForTransaction($transaction)
    {
        $receiver = User::find($transaction->receiver_id);
        if ($receiver) {
            $platformFee = $transaction->amount * ($this->gatewaySettings->platform_fee_percentage / 100);
            $amountToCredit = $transaction->amount - $platformFee;
            $receiver->wallet->addToAvailableBalance($amountToCredit);
        }
    }

    private function deductMoneyForRefund($transaction)
    {
        $receiver = User::find($transaction->receiver_id);
        if ($receiver && $receiver->wallet) {
            $receiver->wallet->available_for_payout -= $transaction->amount;
            $receiver->wallet->save();
        }
    }

    /**
     * Verify webhook signature from CCBill
     * 
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function verifyWebhookSignature($request)
    {
        // For now, return true as CCBill signature verification is not implemented
        // In production, you should implement proper signature verification
        Log::info('CCBill webhook signature verification skipped - not implemented');
        return true;
    }
}
