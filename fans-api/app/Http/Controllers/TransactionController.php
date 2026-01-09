<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Post;
use App\Models\Media;
use App\Models\Message;
use App\Models\Purchase;
use App\Models\Tier;
use App\Models\Subscription;
use App\Services\PaymentService;
use App\Services\WalletService;
use App\Services\CCBillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Tip;
use App\Models\TrackingLinkEvent;
use App\Services\TransactionService;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransactionController extends Controller
{
    protected $paymentService;
    protected $walletService;
    protected $ccbillService;
    protected $transactionService;

    public function __construct(
        PaymentService $paymentService,
        WalletService $walletService,
        CCBillService $ccbillService,
        TransactionService $transactionService
    ) {
        $this->paymentService = $paymentService;
        $this->walletService = $walletService;
        $this->ccbillService = $ccbillService;
        $this->transactionService = $transactionService;
    }


    /**
     * Process a tip transaction
     */
    public function processTip(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'receiver_id' => 'required|exists:users,id',
                'context' => 'nullable|string',
                'tippable_type' => 'nullable|string|in:post,message,profile',
                'tippable_id' => 'nullable|integer',
                'currency' => 'nullable|string|in:USD',
                'payment_method' => 'nullable|string|in:wallet,ccbill'
            ]);

            $sender = Auth::user();
            if (!$sender) {
                throw new \Exception('User not authenticated');
            }

            $receiver = User::findOrFail($validatedData['receiver_id']);

            // Use a database transaction to ensure consistency
            DB::beginTransaction();

            try {
                // For message tips, we allow null tippable_id initially
                $isMessageTip = isset($validatedData['context']) && $validatedData['context'] === 'message';
                
                // Create the tip record first
                $tip = Tip::create([
                    'sender_id' => $sender->id,
                    'recipient_id' => $receiver->id,
                    'amount' => $validatedData['amount'],
                    'tippable_type' => $isMessageTip ? 'message' : ($validatedData['tippable_type'] ?? null),
                    'tippable_id' => $isMessageTip ? null : ($validatedData['tippable_id'] ?? null),
                ]);

                // Prepare additional data
                $additionalData = [
                    'context' => $validatedData['context'] ?? 'general',
                    'payment_method' => $validatedData['payment_method'] ?? 'wallet'
                ];

                // Get tracking link ID from session if available
                if ($trackingLinkId = session('tracking_link_id')) {
                    Log::info('Adding tracking link ID to additional data for tip', [
                        'tracking_link_id' => $trackingLinkId
                    ]);
                    $additionalData['tracking_link_id'] = $trackingLinkId;
                }

                // For message tips, force wallet payment
                if ($isMessageTip) {
                    $additionalData['payment_method'] = 'wallet';
                    Log::info('Forcing wallet payment for message tip', [
                        'user_id' => $sender->id,
                        'context' => $validatedData['context']
                    ]);
                }

                // Process the payment
                $result = $this->paymentService->processPayment(
                    $sender,
                    $validatedData['amount'],
                    Transaction::TIP,
                    null,
                    $receiver->id,
                    $additionalData,
                    Tip::class,
                    $tip->id
                );

                // Log the payment result for debugging
                Log::info('Payment processing result', [
                    'result' => $result,
                    'payment_method' => $result['payment_method'] ?? 'unknown'
                ]);

                // Check if the payment processing failed
                if (isset($result['success']) && $result['success'] === false) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => $result['message'] ?? 'Payment processing failed',
                        'error' => $result['error'] ?? 'payment_error',
                        'data' => $result['data'] ?? []
                    ], 400);
                }

                // If payment method is wallet, the payment has already been processed by processPayment
                if (isset($result['payment_method']) && $result['payment_method'] === 'wallet') {
                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Tip sent successfully using wallet balance',
                        'data' => [
                            'transaction_id' => $result['transaction_id'],
                            'tip_id' => $tip->id,
                            'amount' => $validatedData['amount'],
                            'currency' => $validatedData['currency'] ?? 'USD',
                            'payment_method' => 'wallet',
                            'redirect_required' => false,
                            'transaction_status' => 'completed'
                        ]
                    ], 200);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Tip transaction initiated',
                    'data' => [
                        'transaction_id' => $result['transaction_id'],
                        'tip_id' => $tip->id,
                        'amount' => $validatedData['amount'],
                        'currency' => $validatedData['currency'] ?? 'USD',
                        'payment_method' => $result['payment_method'],
                        'redirect_url' => $result['redirect_url'] ?? null,
                        'redirect_required' => true,
                        'transaction_status' => 'pending'
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error processing tip', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing tip: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a wallet payment for a pending transaction
     */
    public function processWalletPayment(Request $request, $transactionId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            $transaction = Transaction::findOrFail($transactionId);

            // Verify that the user is the sender of the transaction
            if ($transaction->sender_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                    'error' => 'You are not authorized to process this transaction'
                ], 403);
            }

            // Verify that the transaction is pending and uses wallet payment method
            if ($transaction->status !== Transaction::PENDING_STATUS) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid transaction status',
                    'error' => 'This transaction is not in a pending state'
                ], 400);
            }

            if ($transaction->payment_method !== 'wallet') {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid payment method',
                    'error' => 'This transaction is not configured for wallet payment'
                ], 400);
            }

            // Process the wallet payment
            $result = $this->paymentService->processWalletPayment($transactionId);

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => [
                    'transaction_id' => $transactionId,
                    'status' => Transaction::APPROVED_STATUS
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error processing wallet payment', [
                'exception' => $e,
                'user_id' => Auth::id(),
                'transaction_id' => $transactionId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing error',
                'error' => 'Unable to process payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top supporters for the authenticated user
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopSupporters(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validatedData = $request->validate([
                'period' => 'nullable|in:all,year,month,week',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100'
            ]);

            $period = $validatedData['period'] ?? 'all';
            $perPage = $validatedData['per_page'] ?? 10;
            $page = $validatedData['page'] ?? 1;

            // Build the query to get top supporters
            $query = Transaction::query()
                ->where('receiver_id', $user->id)
                ->where('status', Transaction::APPROVED_STATUS)
                ->select([
                    'sender_id',
                    DB::raw('SUM(amount) as total_amount'),
                    DB::raw('COUNT(*) as transaction_count')
                ])
                ->groupBy('sender_id');

            // Apply time period filter - IMPORTANT: Only apply date filters if period is not 'all'
            // or if specific dates are provided and period is not 'all'
            if ($period !== 'all') {
                // Apply predefined period filters
                $dateFilter = now();

                switch ($period) {
                    case 'year':
                        $dateFilter = $dateFilter->startOfYear();
                        break;
                    case 'month':
                        $dateFilter = $dateFilter->startOfMonth();
                        break;
                    case 'week':
                        $dateFilter = $dateFilter->startOfWeek();
                        break;
                }

                $query->where('created_at', '>=', $dateFilter);

                // Apply custom date range if provided
                if (isset($validatedData['start_date']) && isset($validatedData['end_date'])) {
                    $query->whereBetween('created_at', [
                        $validatedData['start_date'],
                        $validatedData['end_date']
                    ]);
                }
            }
            // For 'all' period, don't apply any date filters to get ALL data

            // Order by total amount
            $query->orderBy('total_amount', 'desc');

            // Get paginated results
            $supporters = $query->paginate($perPage, ['*'], 'page', $page);

            // Load user details for each supporter
            $supportersData = $supporters->getCollection()->map(function ($item) use ($user) {
                $supporter = User::find($item->sender_id);

                // Skip if this is the current user (shouldn't happen, but just in case)
                if ($supporter->id === $user->id) {
                    return null;
                }

                // Get transaction breakdown by type
                $transactions = Transaction::where('receiver_id', $user->id)
                    ->where('sender_id', $supporter->id)
                    ->where('status', Transaction::APPROVED_STATUS)
                    ->get();

                // Calculate breakdown by transaction type
                $tips = $transactions->where('type', Transaction::TIP)->sum('amount');
                $subscriptions = $transactions->whereIn('type', [
                    Transaction::ONE_MONTH_SUBSCRIPTION,
                    Transaction::THREE_MONTHS_SUBSCRIPTION,
                    Transaction::SIX_MONTHS_SUBSCRIPTION,
                    Transaction::YEARLY_SUBSCRIPTION
                ])->sum('amount');
                $media = $transactions->where('type', Transaction::ONE_TIME_PURCHASE)->sum('amount');

                // Calculate net income as the sum of all income sources
                $netIncome = $tips + $subscriptions + $media;

                // Ensure we're not returning boolean values for names
                $displayName = $supporter->display_name;
                if (is_bool($displayName)) {
                    $displayName = $supporter->username ?? 'Unknown User';
                }

                $name = $supporter->name;
                if (is_bool($name)) {
                    $name = $supporter->username ?? 'Unknown User';
                }

                return [
                    'id' => $supporter->id,
                    'name' => $displayName ?? $name ?? $supporter->username ?? 'Unknown User',
                    'username' => $supporter->username,
                    'handle' => $supporter->handle ?? '@' . $supporter->username,
                    'avatar' => $supporter->avatar,
                    'amount' => $item->total_amount,
                    'transaction_count' => $item->transaction_count,
                    'gross_income' => $item->total_amount,
                    'net_income' => $netIncome,
                    'tips' => $tips,
                    'subscriptions' => $subscriptions,
                    'media' => $media,
                    'stats' => [
                        ['name' => 'Tips', 'value' => $tips],
                        ['name' => 'Subscriptions', 'value' => $subscriptions],
                        ['name' => 'Media', 'value' => $media]
                    ]
                ];
            })
                ->filter() // Remove any null values (current user)
                ->values(); // Re-index the array

            // Replace the collection in the paginator
            $supporters->setCollection($supportersData);

            return response()->json([
                'success' => true,
                'data' => $supporters
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching top supporters', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching top supporters: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed statistics for a specific supporter
     * 
     * @param int $id The supporter user ID
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupporterDetails($id, Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validatedData = $request->validate([
                'period' => 'nullable|in:all,year,month,week',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date'
            ]);

            $period = $validatedData['period'] ?? 'all';

            // Verify the supporter exists and has transactions with the authenticated user
            $supporter = User::findOrFail($id);

            // Build query for transactions between these users
            $query = Transaction::query()
                ->where('receiver_id', $user->id)
                ->where('sender_id', $supporter->id)
                ->where('status', Transaction::APPROVED_STATUS);

            // Apply date filters if provided and period is not 'all'
            if ($period !== 'all') {
                if (isset($validatedData['start_date']) && isset($validatedData['end_date'])) {
                    $query->whereBetween('created_at', [
                        $validatedData['start_date'],
                        $validatedData['end_date']
                    ]);
                } else {
                    // Apply predefined period filters
                    $dateFilter = now();

                    switch ($period) {
                        case 'year':
                            $dateFilter = $dateFilter->startOfYear();
                            break;
                        case 'month':
                            $dateFilter = $dateFilter->startOfMonth();
                            break;
                        case 'week':
                            $dateFilter = $dateFilter->startOfWeek();
                            break;
                    }

                    $query->where('created_at', '>=', $dateFilter);
                }
            }
            // For 'all' period, don't apply any date filters to get ALL data

            // Get transactions and group by type
            $transactions = $query->get();

            // Calculate totals by transaction type
            $tips = $transactions->where('type', Transaction::TIP)->sum('amount');
            $subscriptions = $transactions->whereIn('type', [
                Transaction::ONE_MONTH_SUBSCRIPTION,
                Transaction::THREE_MONTHS_SUBSCRIPTION,
                Transaction::SIX_MONTHS_SUBSCRIPTION,
                Transaction::YEARLY_SUBSCRIPTION
            ])->sum('amount');
            $mediaPurchases = $transactions->where('type', Transaction::ONE_TIME_PURCHASE)->sum('amount');

            // Generate daily data for the chart
            $dailyData = [];

            // For "all" period, get data from the first transaction to now
            if ($period === 'all') {
                $firstTransaction = $transactions->sortBy('created_at')->first();
                $startDate = $firstTransaction ? new \DateTime($firstTransaction->created_at) : (new \DateTime())->modify('-30 days');
            } else if (isset($validatedData['start_date'])) {
                $startDate = new \DateTime($validatedData['start_date']);
            } else {
                // Default to last 30 days if no specific period
                $startDate = (new \DateTime())->modify('-30 days');
            }

            $endDate = isset($validatedData['end_date']) ? new \DateTime($validatedData['end_date']) : new \DateTime();

            $interval = new \DateInterval('P1D');
            $dateRange = new \DatePeriod($startDate, $interval, $endDate);

            foreach ($dateRange as $date) {
                $dateString = $date->format('Y-m-d');
                $dayTransactions = $transactions->filter(function ($transaction) use ($dateString) {
                    return $transaction->created_at->format('Y-m-d') === $dateString;
                });

                $dailyTips = $dayTransactions->where('type', Transaction::TIP)->sum('amount');
                $dailySubscriptions = $dayTransactions->whereIn('type', [
                    Transaction::ONE_MONTH_SUBSCRIPTION,
                    Transaction::THREE_MONTHS_SUBSCRIPTION,
                    Transaction::SIX_MONTHS_SUBSCRIPTION,
                    Transaction::YEARLY_SUBSCRIPTION
                ])->sum('amount');
                $dailyMedia = $dayTransactions->where('type', Transaction::ONE_TIME_PURCHASE)->sum('amount');

                $dailyData[] = [
                    'date' => $date->format('M j, Y'),
                    'tips' => $dailyTips,
                    'subscriptions' => $dailySubscriptions,
                    'media' => $dailyMedia
                ];
            }

            // Calculate net income as sum of all income sources
            $netIncome = $tips + $subscriptions + $mediaPurchases;

            $detailedData = [
                'tips' => $tips,
                'subscriptions' => $subscriptions,
                'media' => $mediaPurchases,
                'gross_income' => $tips + $subscriptions + $mediaPurchases,
                'net_income' => $netIncome,
                'daily_data' => $dailyData,
                'stats' => [
                    ['name' => 'Tips', 'value' => $tips],
                    ['name' => 'Subscriptions', 'value' => $subscriptions],
                    ['name' => 'Media', 'value' => $mediaPurchases]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $detailedData
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching supporter details', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'supporter_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching supporter details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a one-time purchase transaction (for posts)
     */
    public function processOneTimePurchase(Request $request)
    {
        try {
            Log::info('Processing one-time purchase request', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            $validatedData = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'receiver_id' => 'required|exists:users,id',
                'purchasable_type' => 'required|string|in:post,media',
                'purchasable_id' => 'required|integer',
                'currency' => 'nullable|string|in:USD',
                'payment_method' => 'nullable|string|in:wallet,ccbill'
            ]);

            Log::info('Purchase request validated', [
                'validated_data' => $validatedData
            ]);

            $sender = Auth::user();
            if (!$sender) {
                Log::error('Purchase failed - User not authenticated');
                throw new \Exception('User not authenticated');
            }

            $receiver = User::findOrFail($validatedData['receiver_id']);

            // Use a database transaction to ensure consistency
            DB::beginTransaction();

            try {
                Log::info('Creating purchase record', [
                    'purchasable_type' => $validatedData['purchasable_type'],
                    'purchasable_id' => $validatedData['purchasable_id'],
                    'buyer_id' => $sender->id,
                    'seller_id' => $receiver->id,
                    'amount' => $validatedData['amount']
                ]);

                // Create the purchase record
                $purchasableType = $validatedData['purchasable_type'] === 'post' ? \App\Models\Post::class : ($validatedData['purchasable_type'] === 'media' ? \App\Models\Media::class : $validatedData['purchasable_type']);
                $purchase = Purchase::create([
                    'user_id' => $sender->id,
                    'purchasable_type' => $purchasableType,
                    'purchasable_id' => $validatedData['purchasable_id'],
                    'price' => $validatedData['amount']
                ]);

                Log::info('Purchase record created', [
                    'purchase_id' => $purchase->id
                ]);

                // Prepare additional data
                $additionalData = [
                    'payment_method' => $validatedData['payment_method'] ?? 'wallet',
                    'purchasable_type' => $purchasableType,
                    'purchasable_id' => $validatedData['purchasable_id']
                ];

                // Get tracking link ID from session if available
                if ($trackingLinkId = session('tracking_link_id')) {
                    Log::info('Adding tracking link ID to additional data', [
                        'tracking_link_id' => $trackingLinkId
                    ]);
                    $additionalData['tracking_link_id'] = $trackingLinkId;
                }

                Log::info('Processing payment', [
                    'amount' => $validatedData['amount'],
                    'additional_data' => $additionalData
                ]);

                $result = $this->paymentService->processPayment(
                    $sender,
                    $validatedData['amount'],
                    Transaction::ONE_TIME_PURCHASE,
                    null,
                    $validatedData['receiver_id'],
                    $additionalData,
                    $purchasableType,
                    $validatedData['purchasable_id']
                );

                Log::info('Payment processed', [
                    'result' => $result
                ]);

                // Update purchase with transaction ID
                $purchase->transaction_id = $result['transaction_id'];
                $purchase->save();

                Log::info('Purchase updated with transaction ID', [
                    'purchase_id' => $purchase->id,
                    'transaction_id' => $result['transaction_id']
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Purchase processed successfully',
                    'data' => [
                        'transaction_id' => $result['transaction_id'],
                        'amount' => $validatedData['amount'],
                        'currency' => 'USD',
                        'purchasable_type' => $validatedData['purchasable_type'],
                        'purchasable_id' => $validatedData['purchasable_id']
                    ]
                ], 200);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error processing purchase', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error processing purchase', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error processing purchase',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process a message purchase transaction
     */
    public function processMessagePurchase(Request $request)
    {
        try {
            // Log the incoming request data for debugging
            Log::info('Processing message purchase request', [
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);

            $validatedData = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'receiver_id' => 'required|exists:users,id',
                'message_id' => 'required|exists:messages,id',
                'payment_method' => 'nullable|string|in:wallet,ccbill,test'
            ]);

            $sender = Auth::user();
            if (!$sender) {
                throw new \Exception('User not authenticated');
            }

            $receiver = User::findOrFail($validatedData['receiver_id']);
            $message = Message::findOrFail($validatedData['message_id']);

            // Verify that the message belongs to the receiver
            if ($message->sender_id != $receiver->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message does not belong to the specified receiver',
                    'error' => 'invalid_message_owner'
                ], 400);
            }

            // Check if the user has already purchased this message
            $existingPurchase = Purchase::where('user_id', $sender->id)
                ->where('purchasable_id', $validatedData['message_id'])
                ->where('purchasable_type', Message::class)
                ->exists();

            if ($existingPurchase) {
                return response()->json([
                    'success' => true,
                    'message' => 'You have already purchased this message',
                    'data' => [
                        'message_id' => $validatedData['message_id'],
                        'already_purchased' => true
                    ]
                ], 200);
            }

            // Prepare additional data
            $additionalData = [
                'message_id' => $validatedData['message_id'],
                'tippable_id' => $validatedData['message_id'],
                'tippable_type' => 'message'
            ];

            // Get tracking link ID from session if available
            if ($trackingLinkId = session('tracking_link_id')) {
                Log::info('Adding tracking link ID to additional data for message purchase', [
                    'tracking_link_id' => $trackingLinkId
                ]);
                $additionalData['tracking_link_id'] = $trackingLinkId;
            }

            // Force test payment method if specified
            if (isset($validatedData['payment_method']) && $validatedData['payment_method'] === 'test') {
                $additionalData['payment_method'] = 'test';
                Log::info('Using test payment method', ['user_id' => $sender->id, 'message_id' => $validatedData['message_id']]);
            } else if (isset($validatedData['payment_method'])) {
                $additionalData['payment_method'] = $validatedData['payment_method'];
            }

            // Log the wallet balance for debugging
            $walletBalance = $sender->wallet ? $sender->wallet->available_for_payout : 0;
            Log::info('User wallet balance before processing message purchase', [
                'user_id' => $sender->id,
                'wallet_balance' => $walletBalance,
                'amount' => $validatedData['amount'],
                'message_id' => $validatedData['message_id']
            ]);

            // Use a database transaction to ensure consistency
            DB::beginTransaction();

            try {
                // Process the payment
                $result = $this->paymentService->processPayment(
                    $sender,
                    $validatedData['amount'],
                    Transaction::ONE_TIME_PURCHASE,
                    null,
                    $receiver->id,
                    $additionalData
                );

                // Log the payment result for debugging
                Log::info('Message purchase payment processing result', [
                    'result' => $result,
                    'payment_method' => $result['payment_method'] ?? 'unknown'
                ]);

                // Check if the payment processing failed
                if (isset($result['success']) && $result['success'] === false) {
                    DB::rollBack();

                    // Return the error from the payment service
                    return response()->json([
                        'success' => false,
                        'message' => $result['message'] ?? 'Payment processing failed',
                        'error' => $result['error'] ?? 'payment_error',
                        'data' => $result['data'] ?? []
                    ], 400);
                }

                // If payment method is wallet, the payment has already been processed by processPayment
                // FIX: Check if payment_method exists in result or additionalData before accessing it
                $paymentMethod = $result['payment_method'] ?? ($additionalData['payment_method'] ?? null);

                if ($paymentMethod === 'wallet' || $paymentMethod === 'test') {
                    try {
                        Log::info('Message purchase payment already processed', [
                            'transaction_id' => $result['transaction_id'],
                            'payment_method' => $paymentMethod
                        ]);

                        // For test payments, we don't need to do anything extra
                        if ($paymentMethod === 'test') {
                            Log::info('Test payment completed', [
                                'transaction_id' => $result['transaction_id']
                            ]);
                        }

                        // Create the purchase record using the new Purchase model
                        $purchase = Purchase::create([
                            'user_id' => $sender->id,
                            'purchasable_id' => $message->id,
                            'purchasable_type' => Message::class,
                            'permission_set_id' => null, // Messages typically don't have permission sets
                            'price' => $validatedData['amount'],
                            'transaction_id' => $result['transaction_id']
                        ]);

                        Log::info('Message purchase record created', [
                            'purchase_id' => $purchase->id,
                            'transaction_id' => $result['transaction_id'],
                            'user_id' => $sender->id,
                            'message_id' => $message->id
                        ]);

                        DB::commit();

                        return response()->json([
                            'success' => true,
                            'message' => 'Message purchased successfully',
                            'data' => [
                                'transaction_id' => $result['transaction_id'],
                                'message_id' => $validatedData['message_id'],
                                'amount' => $validatedData['amount'],
                                'currency' => 'USD',
                                'payment_method' => $paymentMethod,
                                'redirect_required' => false,
                                'transaction_status' => 'completed'
                            ]
                        ], 200);
                    } catch (\Exception $e) {
                        // If payment fails, log the error and return a specific error message
                        DB::rollBack();

                        Log::error('Error processing message purchase payment', [
                            'error' => $e->getMessage(),
                            'transaction_id' => $result['transaction_id'],
                            'user_id' => $sender->id
                        ]);

                        // Check if it's an insufficient balance error
                        if (strpos($e->getMessage(), 'Insufficient wallet balance') !== false) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Insufficient wallet balance. Please top up your account first.',
                                'error' => 'insufficient_balance',
                                'data' => [
                                    'transaction_id' => $result['transaction_id'],
                                    'wallet_balance' => $walletBalance,
                                    'required_amount' => $validatedData['amount']
                                ]
                            ], 400);
                        }

                        // For other errors
                        return response()->json([
                            'success' => false,
                            'message' => 'Error processing payment: ' . $e->getMessage(),
                            'error' => 'payment_error'
                        ], 500);
                    }
                }

                // If external payment is needed (CCBill) - this part remains unchanged
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Message purchase initiated successfully',
                    'data' => [
                        'payment_url' => $result['payment_url'],
                        'transaction_id' => $result['transaction_id'],
                        'message_id' => $validatedData['message_id'],
                        'amount' => $validatedData['amount'],
                        'currency' => 'USD',
                        'payment_method' => $paymentMethod ?? 'external',
                        'redirect_required' => true,
                        'transaction_status' => 'pending'
                    ]
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Error processing message purchase', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'message_id' => $request->message_id,
                'amount' => $request->amount
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing error',
                'error' => 'Unable to initiate payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle CCBill webhook for payment notifications
     */
    public function handleCCBillWebhook(Request $request)
    {
        try {
            // Validate the webhook request
            $validatedData = $request->validate([
                'transactionId' => 'required|string',
                'subscriptionId' => 'nullable|string',
                'status' => 'required|string',
                'eventType' => 'required|string'
            ]);

            Log::info('CCBill webhook received', $validatedData);

            // Verify the webhook signature if CCBill provides one
            if (!$this->ccbillService->verifyWebhookSignature($request)) {
                Log::warning('Invalid CCBill webhook signature', $validatedData);
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
            }

            // Find the transaction by CCBill transaction ID
            $transaction = Transaction::where('ccbill_transaction_id', $validatedData['transactionId'])->first();
            if (!$transaction) {
                Log::warning('Transaction not found for CCBill webhook', $validatedData);
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            // Process based on event type
            switch ($validatedData['eventType']) {
                case 'payment.success':
                    // Update transaction status and handle successful payment
                    $this->paymentService->handleSuccessfulPayment($transaction);

                    // If it's a subscription, store the subscription ID
                    if ($validatedData['subscriptionId'] && $transaction->isSubscription()) {
                        $transaction->ccbill_subscription_id = $validatedData['subscriptionId'];
                        $transaction->save();
                    }

                    // If it's a one-time purchase, create the purchase record
                    if ($transaction->type === Transaction::ONE_TIME_PURCHASE) {
                        $additionalData = $transaction->additional_data;

                        if (isset($additionalData['post_id'])) {
                            // Create post purchase
                            $post = Post::find($additionalData['post_id']);
                            if ($post) {
                                $permissionSet = $post->permissionSets()->first();

                                Purchase::create([
                                    'user_id' => $transaction->sender_id,
                                    'purchasable_id' => $post->id,
                                    'purchasable_type' => Post::class,
                                    'permission_set_id' => $permissionSet ? $permissionSet->id : null,
                                    'price' => $transaction->amount,
                                    'transaction_id' => $transaction->id
                                ]);
                            }
                        } else if (isset($additionalData['message_id'])) {
                            // Create message purchase
                            $message = Message::find($additionalData['message_id']);
                            if ($message) {
                                Purchase::create([
                                    'user_id' => $transaction->sender_id,
                                    'purchasable_id' => $message->id,
                                    'purchasable_type' => Message::class,
                                    'permission_set_id' => null,
                                    'price' => $transaction->amount,
                                    'transaction_id' => $transaction->id
                                ]);
                            }
                        }
                    }

                    break;

                case 'payment.failed':
                    // Handle failed payment
                    $this->paymentService->handleFailedPayment($transaction);
                    break;

                case 'subscription.cancelled':
                    // Handle subscription cancellation
                    if ($validatedData['subscriptionId']) {
                        $subscription = Subscription::where('ccbill_subscription_id', $validatedData['subscriptionId'])->first();
                        if ($subscription) {
                            $subscription->status = Subscription::CANCELED_STATUS;
                            $subscription->cancel_date = now();
                            $subscription->save();
                        }
                    }
                    break;

                case 'subscription.renewed':
                    // Handle subscription renewal
                    if ($validatedData['subscriptionId']) {
                        $subscription = Subscription::where('ccbill_subscription_id', $validatedData['subscriptionId'])->first();
                        if ($subscription) {
                            // Create a new transaction for the renewal
                            $newTransaction = Transaction::create([
                                'sender_id' => $subscription->user_id,
                                'receiver_id' => $subscription->tier->user_id,
                                'amount' => $subscription->amount,
                                'type' => $this->getSubscriptionTransactionType($subscription->duration),
                                'status' => Transaction::APPROVED_STATUS,
                                'tier_id' => $subscription->tier_id,
                                'ccbill_transaction_id' => $validatedData['transactionId'],
                                'ccbill_subscription_id' => $validatedData['subscriptionId'],
                                'payment_method' => 'ccbill'
                            ]);

                            // Update subscription end date
                            $subscription->end_date = $subscription->end_date->addMonths($subscription->duration);
                            $subscription->save();

                            // Credit the creator
                            $this->paymentService->creditReceiverForTransaction($newTransaction);
                        }
                    }
                    break;

                default:
                    Log::info('Unhandled CCBill webhook event type', $validatedData);
                    break;
            }

            return response()->json(['success' => true, 'message' => 'Webhook processed successfully']);
        } catch (\Exception $e) {
            Log::error('Error processing CCBill webhook', [
                'exception' => $e,
                'payload' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Link a tip transaction to a tippable entity (message, post, etc.)
     * 
     * @param int $id The transaction ID
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function linkTipToTippable($id, Request $request)
    {
        try {
            $validatedData = $request->validate([
                'message_id' => 'nullable|integer|exists:messages,id',
                'post_id' => 'nullable|integer|exists:posts,id',
                'tippable_id' => 'nullable|integer',
                'tippable_type' => 'nullable|string|in:message,post,media',
            ]);

            $transaction = Transaction::findOrFail($id);

            // Check if user is authorized to link this transaction
            if ($transaction->sender_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to link this transaction'
                ], 403);
            }

            // Update the additional_data to include the tippable entity
            $additionalData = $transaction->additional_data;
            if (!is_array($additionalData)) {
                $additionalData = [];
            }

            // Get the tippable ID and type
            $tippableId = null;
            $tippableType = null;

            // Handle message_id specifically
            if (isset($validatedData['message_id'])) {
                $tippableId = $validatedData['message_id'];
                $tippableType = 'message';
            }
            // Handle post_id specifically
            else if (isset($validatedData['post_id'])) {
                $tippableId = $validatedData['post_id'];
                $tippableType = 'post';
            }
            // Handle generic tippable entity
            else if (isset($validatedData['tippable_id']) && isset($validatedData['tippable_type'])) {
                $tippableId = $validatedData['tippable_id'];
                $tippableType = $validatedData['tippable_type'];
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No tippable entity provided'
                ], 400);
            }

            // Validate that the tippable entity exists and belongs to the receiver
            $isValid = false;
            $errorMessage = 'Invalid tippable item';

            if ($tippableType === 'message') {
                // Check if the message exists and belongs to the transaction receiver
                $message = \App\Models\Message::find($tippableId);

                if ($message) {
                    // Log the message details for debugging
                    Log::info('Validating message for tip linking', [
                        'message_id' => $message->id,
                        'message_sender_id' => $message->sender_id,
                        'message_receiver_id' => $message->receiver_id,
                        'transaction_receiver_id' => $transaction->receiver_id
                    ]);

                    // Check if either the sender or receiver of the message matches the transaction receiver
                    if (
                        $message->sender_id === $transaction->receiver_id ||
                        $message->receiver_id === $transaction->receiver_id
                    ) {
                        $isValid = true;
                    } else {
                        $errorMessage = 'The message does not belong to the transaction receiver';
                    }
                } else {
                    $errorMessage = 'The specified message does not exist';
                }
            } else if ($tippableType === 'post') {
                // Similar validation for posts
                $post = \App\Models\Post::find($tippableId);

                if ($post && $post->user_id === $transaction->receiver_id) {
                    $isValid = true;
                } else {
                    $errorMessage = 'The post does not exist or does not belong to the transaction receiver';
                }
            } else if ($tippableType === 'media') {
                // Similar validation for media
                $media = \App\Models\Media::find($tippableId);

                if ($media && $media->user_id === $transaction->receiver_id) {
                    $isValid = true;
                } else {
                    $errorMessage = 'The media does not exist or does not belong to the transaction receiver';
                }
            }

            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid tippable item',
                    'error' => $errorMessage
                ], 400);
            }

            // If we get here, the tippable entity is valid
            // Update the additional_data
            $additionalData['tippable_id'] = $tippableId;
            $additionalData['tippable_type'] = $tippableType;

            // Also store in specific fields for backward compatibility
            if ($tippableType === 'message') {
                $additionalData['message_id'] = $tippableId;
            } else if ($tippableType === 'post') {
                $additionalData['post_id'] = $tippableId;
            }

            Log::info('Linking transaction to tippable entity', [
                'transaction_id' => $id,
                'tippable_id' => $tippableId,
                'tippable_type' => $tippableType,
                'user_id' => Auth::id()
            ]);

            $transaction->additional_data = $additionalData;
            $transaction->save();

            // --- NEW: Update the related Tip record as well ---
            // Try to find the tip by sender, receiver, and transaction id (if available in additional_data)
            $tip = Tip::where('sender_id', $transaction->sender_id)
                ->where('recipient_id', $transaction->receiver_id)
                ->where(function($q) use ($transaction) {
                    if (isset($transaction->additional_data['tip_id'])) {
                        $q->where('id', $transaction->additional_data['tip_id']);
                    }
                })
                ->orderByDesc('id')
                ->first();

            if ($tip) {
                Log::info('Updating Tip tippable_id and tippable_type', [
                    'tip_id' => $tip->id,
                    'old_tippable_id' => $tip->tippable_id,
                    'old_tippable_type' => $tip->tippable_type,
                    'new_tippable_id' => $tippableId,
                    'new_tippable_type' => $tippableType
                ]);
                $tip->tippable_id = $tippableId;
                $tip->tippable_type = $tippableType;
                $tip->save();
            } else {
                Log::warning('No matching Tip found to update for transaction', [
                    'transaction_id' => $transaction->id,
                    'sender_id' => $transaction->sender_id,
                    'receiver_id' => $transaction->receiver_id
                ]);
            }
            // --- END NEW ---

            return response()->json([
                'success' => true,
                'message' => 'Transaction linked successfully',
                'data' => [
                    'transaction_id' => $id,
                    'tippable_id' => $tippableId,
                    'tippable_type' => $tippableType,
                    'amount' => $transaction->amount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to link transaction to tippable entity', [
                'transaction_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to link transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's wallet balance
     */
    public function getWalletBalance()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Make sure user has a wallet
        if (!$user->wallet) {
            $this->walletService->createWallet($user);
        }

        // Get wallet balance using the existing service
        $balances = $this->walletService->getWalletBalance($user);

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $balances['available_for_payout'],
                'total_balance' => $balances['total_balance'],
                'pending_balance' => $balances['pending_balance'],
                'available_for_payout' => $balances['available_for_payout'],
                'currency' => 'USD'
            ]
        ]);
    }

    /**
     * Get transaction history for the authenticated user
     */
    public function getTransactionHistory(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $validatedData = $request->validate([
            'type' => 'nullable|in:all,sent,received',
            'status' => 'nullable|in:all,pending,approved,declined,refunded',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        $type = $validatedData['type'] ?? 'all';
        $status = $validatedData['status'] ?? 'all';
        $perPage = $validatedData['per_page'] ?? 15;

        $query = Transaction::query();

        // Filter by type
        if ($type === 'sent') {
            $query->where('sender_id', $user->id);
        } elseif ($type === 'received') {
            $query->where('receiver_id', $user->id);
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            });
        }

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Order by latest first
        $query->orderBy('created_at', 'desc');

        // Eager load relationships
        $query->with(['sender', 'receiver', 'tier']);

        // Paginate results
        $transactions = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Get transaction details
     */
    public function getTransactionDetails($id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $transaction = Transaction::with(['sender', 'receiver', 'tier'])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $transaction
        ]);
    }

    /**
     * Request a refund for a transaction
     */
    public function requestRefund(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $transaction = Transaction::where('sender_id', $user->id)->findOrFail($id);

        // Check if transaction is eligible for refund
        if ($transaction->status !== Transaction::APPROVED_STATUS) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction is not eligible for refund',
                'error' => 'Only approved transactions can be refunded'
            ], 400);
        }

        // Check if refund period has expired (e.g., 30 days)
        $refundPeriodDays = 30;
        if ($transaction->created_at->addDays($refundPeriodDays) < now()) {
            return response()->json([
                'success' => false,
                'message' => 'Refund period has expired',
                'error' => "Refunds must be requested within {$refundPeriodDays} days of purchase"
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Process the refund
            $this->paymentService->refundPayment($transaction);

            // Create a refund request record if you have a separate table for that
            // RefundRequest::create([...]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Refund processed successfully',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'status' => Transaction::REFUNDED_STATUS
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error processing refund', [
                'exception' => $e,
                'user_id' => $user->id,
                'transaction_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing refund',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a subscription
     */
    public function cancelSubscription(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $subscription = Subscription::where('user_id', $user->id)->findOrFail($id);

        if ($subscription->status !== Subscription::ACTIVE_STATUS) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is not active',
                'error' => 'Only active subscriptions can be cancelled'
            ], 400);
        }

        try {
            // Cancel the subscription
            $this->paymentService->cancelSubscription($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Subscription cancelled successfully',
                'data' => [
                    'subscription_id' => $subscription->id,
                    'status' => Subscription::CANCELED_STATUS
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling subscription', [
                'exception' => $e,
                'user_id' => $user->id,
                'subscription_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error cancelling subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tippable item by type and ID
     */
    private function getTippableItem($type, $id, $receiverId)
    {
        switch ($type) {
            case 'post':
                return Post::where('id', $id)->where('user_id', $receiverId)->first();
            case 'media':
                return Media::where('id', $id)->where('user_id', $receiverId)->first();
            case 'message':
                return Message::where('id', $id)->where('sender_id', $receiverId)->first();
            default:
                return null;
        }
    }

    /**
     * Get subscription transaction type based on duration
     */
    private function getSubscriptionTransactionType($duration)
    {
        switch ($duration) {
            case 1:
                return Transaction::ONE_MONTH_SUBSCRIPTION;
            case 3:
                return Transaction::THREE_MONTHS_SUBSCRIPTION;
            case 6:
                return Transaction::SIX_MONTHS_SUBSCRIPTION;
            case 12:
                return Transaction::YEARLY_SUBSCRIPTION;
            default:
                return Transaction::ONE_MONTH_SUBSCRIPTION;
        }
    }

    /**
     * Calculate subscription amount based on tier price and duration
     */
    private function calculateSubscriptionAmount($basePrice, $duration)
    {
        // Apply discounts for longer subscriptions
        switch ($duration) {
            case 3:
                return $basePrice * 3 * 0.9; // 10% discount for 3 months
            case 6:
                return $basePrice * 6 * 0.85; // 15% discount for 6 months
            case 12:
                return $basePrice * 12 * 0.8; // 20% discount for 12 months
            default:
                return $basePrice * $duration;
        }
    }

    public function getAnalytics(Request $request)
    {
        Log::info('Analytics endpoint called', [
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        $validator = Validator::make($request->all(), [
            'content_type' => 'required|in:posts,messages',
            'period' => 'required|in:last30,last90,last180',
            'metric' => 'required|in:purchases,tips,views,likes,comments'
        ]);

        if ($validator->fails()) {
            Log::error('Analytics validation failed', [
                'errors' => $validator->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json($validator->errors(), 422);
        }

        $data = $this->transactionService->getAnalyticsData($request->all());

        Log::info('Analytics data returned', [
            'data_structure' => array_keys($data),
            'table_data_count' => count($data['table_data'] ?? []),
            'chart_data_count' => count($data['chart_data'] ?? []),
            'summary' => $data['summary'] ?? null
        ]);

        return response()->json($data);
    }

    public function testAnalytics()
    {
        Log::info('Test analytics endpoint called');
        
        $testData = [
            'table_data' => [
                [
                    'id' => 1,
                    'date' => 'Jan 15, 2025',
                    'time' => '10:30 AM',
                    'viewers' => 100,
                    'tips' => 25.50,
                    'price' => 5.00,
                    'purchases' => 15.00,
                    'likes' => 25,
                    'comments' => 5
                ]
            ],
            'chart_data' => [
                ['date' => '2025-01-15', 'total' => 25.50],
                ['date' => '2025-01-16', 'total' => 30.00],
                ['date' => '2025-01-17', 'total' => 15.75]
            ],
            'summary' => [
                'total' => 71.25,
                'growth' => '12.5%'
            ]
        ];

        return response()->json($testData);
    }

    /**
     * Get reach statistics for the authenticated user
     */
    public function getReachStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validatedData = $request->validate([
                'period' => 'nullable|string|in:last30,last90,last180',
                'start_date' => 'nullable|date_format:Y-m-d',
                'end_date' => 'nullable|date_format:Y-m-d'
            ]);

            $period = $validatedData['period'] ?? 'last90';
            $startDate = $validatedData['start_date'] ?? null;
            $endDate = $validatedData['end_date'] ?? null;

            // Parse period to get date range
            $dateRange = $this->parsePeriod($period);
            if ($startDate && $endDate) {
                $dateRange = [
                    'start' => Carbon::parse($startDate)->startOfDay(),
                    'end' => Carbon::parse($endDate)->endOfDay()
                ];
            }

            // Get profile visitors data
            $profileVisitors = $this->getProfileVisitorsData($user, $dateRange);
            
            // Get view duration data
            $viewDuration = $this->getViewDurationData($user, $dateRange);
            
            // Get top countries data
            $topCountries = $this->getTopCountriesData($user, $dateRange);
            
            // Get reach summary
            $reachSummary = $this->getReachSummary($user, $dateRange);

            return response()->json([
                'success' => true,
                'data' => [
                    'profile_visitors' => $profileVisitors,
                    'view_duration' => $viewDuration,
                    'top_countries' => $topCountries,
                    'reach_summary' => $reachSummary,
                    'period' => [
                        'start' => $dateRange['start']->format('Y-m-d'),
                        'end' => $dateRange['end']->format('Y-m-d')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching reach statistics', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch reach statistics'
            ], 500);
        }
    }

    /**
     * Get top fans statistics for the authenticated user
     */
    public function getTopFansStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validatedData = $request->validate([
                'period' => 'nullable|string|in:last30,last90,last180',
                'filter' => 'nullable|string|in:all,newest,spend',
                'start_date' => 'nullable|date_format:Y-m-d',
                'end_date' => 'nullable|date_format:Y-m-d'
            ]);

            $period = $validatedData['period'] ?? 'last90';
            $filter = $validatedData['filter'] ?? 'all';
            $startDate = $validatedData['start_date'] ?? null;
            $endDate = $validatedData['end_date'] ?? null;

            // Parse period to get date range
            $dateRange = $this->parsePeriod($period);
            if ($startDate && $endDate) {
                $dateRange = [
                    'start' => Carbon::parse($startDate)->startOfDay(),
                    'end' => Carbon::parse($endDate)->endOfDay()
                ];
            }

            // Get top fans data
            $topFans = $this->getTopFansData($user, $dateRange, $filter);
            
            // Get fans summary
            $fansSummary = $this->getFansSummary($user, $dateRange);
            
            // Get fans chart data
            $fansChartData = $this->getFansChartData($user, $dateRange);

            return response()->json([
                'success' => true,
                'data' => [
                    'top_fans' => $topFans,
                    'fans_summary' => $fansSummary,
                    'fans_chart_data' => $fansChartData,
                    'period' => [
                        'start' => $dateRange['start']->format('Y-m-d'),
                        'end' => $dateRange['end']->format('Y-m-d')
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching top fans statistics', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch top fans statistics'
            ], 500);
        }
    }

    /**
     * Get profile visitors data
     */
    private function getProfileVisitorsData($user, $dateRange)
    {
        // Get profile views from post stats
        $posts = Post::where('user_id', $user->id)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->with('stats')
            ->get();

        $totalVisitors = $posts->sum(function($post) {
            return $post->stats ? $post->stats->total_views : 0;
        });

        // For now, use total_views as unique_views since we don't have unique tracking
        $uniqueVisitors = $totalVisitors;
        $returningVisitors = 0; // We don't have returning visitor tracking yet

        return [
            [
                'count' => $totalVisitors,
                'label' => 'Total Visitors'
            ],
            [
                'count' => $uniqueVisitors,
                'label' => 'Unique Visitors'
            ],
            [
                'count' => $returningVisitors,
                'label' => 'Returning Visitors'
            ]
        ];
    }

    /**
     * Get view duration data
     */
    private function getViewDurationData($user, $dateRange)
    {
        // Calculate average view duration from post stats
        $posts = Post::where('user_id', $user->id)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->with('stats')
            ->get();

        // Since we don't have actual view duration data, use placeholder values
        $avgDuration = 300; // 5 minutes in seconds
        $maxDuration = 1800; // 30 minutes in seconds
        $postCount = $posts->count();

        return [
            [
                'days' => floor($avgDuration / 86400),
                'time' => gmdate('H:i:s', $avgDuration % 86400),
                'label' => 'Average Duration'
            ],
            [
                'days' => floor($maxDuration / 86400),
                'time' => gmdate('H:i:s', $maxDuration % 86400),
                'label' => 'Max Duration'
            ],
            [
                'days' => $postCount,
                'time' => 'posts',
                'label' => 'Content Pieces'
            ]
        ];
    }

    /**
     * Get top countries data
     */
    private function getTopCountriesData($user, $dateRange)
    {
        // Get country data from post stats or user visits
        // This is a placeholder - you'll need to implement actual country tracking
        $countries = [
            ['name' => 'United States', 'flag' => '🇺🇸', 'guests' => 1250, 'users' => 890, 'total' => 2140],
            ['name' => 'United Kingdom', 'flag' => '🇬🇧', 'guests' => 450, 'users' => 320, 'total' => 770],
            ['name' => 'Canada', 'flag' => '🇨🇦', 'guests' => 380, 'users' => 280, 'total' => 660],
            ['name' => 'Australia', 'flag' => '🇦🇺', 'guests' => 290, 'users' => 210, 'total' => 500],
            ['name' => 'Germany', 'flag' => '🇩🇪', 'guests' => 220, 'users' => 180, 'total' => 400]
        ];

        return $countries;
    }

    /**
     * Get reach summary data
     */
    private function getReachSummary($user, $dateRange)
    {
        $posts = Post::where('user_id', $user->id)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->with('stats')
            ->get();

        $totalReach = $posts->sum(function($post) {
            return $post->stats ? $post->stats->total_views : 0;
        });

        // Calculate engagement rate (users vs guests) - using likes as engagement metric
        $totalEngagement = $posts->sum(function($post) {
            return $post->stats ? $post->stats->total_likes : 0;
        });

        $engagementRate = $totalReach > 0 ? ($totalEngagement / $totalReach) * 100 : 0;

        return [
            'total_reach' => $totalReach,
            'engagement_rate' => round($engagementRate, 1)
        ];
    }

    /**
     * Get top fans data
     */
    private function getTopFansData($user, $dateRange, $filter)
    {
        // Get top supporters based on transactions
        $query = Transaction::where('receiver_id', $user->id)
            ->where('status', Transaction::APPROVED_STATUS)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->with('sender');

        // Apply filter
        switch ($filter) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'spend':
                $query->orderBy('amount', 'desc');
                break;
            default: // 'all'
                $query->orderBy('amount', 'desc');
                break;
        }

        $transactions = $query->limit(10)->get();

        // Group by sender and calculate totals
        $fansData = $transactions->groupBy('sender_id')->map(function ($userTransactions, $senderId) {
            $sender = $userTransactions->first()->sender;
            $totalSpent = $userTransactions->sum('amount');
            $lastTransaction = $userTransactions->sortByDesc('created_at')->first();

            return [
                'id' => $senderId,
                'name' => $sender->name ?? 'Unknown User',
                'username' => $sender->username ?? 'unknown',
                'avatar' => $sender->avatar,
                'price' => '$' . number_format($totalSpent, 2),
                'duration' => $lastTransaction ? $lastTransaction->created_at->diffForHumans() : null,
                'color' => 'bg-primary-light dark:bg-primary-dark',
                'initials' => strtoupper(substr($sender->name ?? 'Unknown User', 0, 2))
            ];
        })->values();

        return $fansData;
    }

    /**
     * Get fans summary data
     */
    private function getFansSummary($user, $dateRange)
    {
        // Get active fans count
        $activeFans = Transaction::where('receiver_id', $user->id)
            ->where('status', Transaction::APPROVED_STATUS)
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->distinct('sender_id')
            ->count('sender_id');

        // Calculate retention rate (placeholder)
        $retentionRate = 89.2; // This would need actual calculation based on user behavior

        return [
            'active_fans' => $activeFans,
            'retention_rate' => $retentionRate
        ];
    }

    /**
     * Get fans chart data
     */
    private function getFansChartData($user, $dateRange)
    {
        // Generate chart data for the last 7 days
        $chartData = [];
        $currentDate = $dateRange['start']->copy();

        for ($i = 0; $i < 7; $i++) {
            $dayStart = $currentDate->copy()->addDays($i)->startOfDay();
            $dayEnd = $currentDate->copy()->addDays($i)->endOfDay();

            $dailyRevenue = Transaction::where('receiver_id', $user->id)
                ->where('status', Transaction::APPROVED_STATUS)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->sum('amount');

            $dailySubscribers = Transaction::where('receiver_id', $user->id)
                ->where('status', Transaction::APPROVED_STATUS)
                ->whereIn('type', [
                    Transaction::ONE_MONTH_SUBSCRIPTION,
                    Transaction::THREE_MONTHS_SUBSCRIPTION,
                    Transaction::SIX_MONTHS_SUBSCRIPTION,
                    Transaction::YEARLY_SUBSCRIPTION
                ])
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->count();

            $chartData[] = [
                'date' => $dayStart->format('Y-m-d'),
                'revenue' => $dailyRevenue,
                'subscribers' => $dailySubscribers
            ];
        }

        return $chartData;
    }

    /**
     * Parse period string to get start and end dates.
     *
     * @param string $period
     * @return array
     */
    private function parsePeriod($period)
    {
        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();

        switch ($period) {
            case 'last30':
                $startDate = now()->subDays(30)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last90':
                $startDate = now()->subDays(90)->startOfDay();
                $endDate = now()->endOfDay();
                break;
            case 'last180':
                $startDate = now()->subDays(180)->startOfDay();
                $endDate = now()->endOfDay();
                break;
        }

        return ['start' => $startDate, 'end' => $endDate];
    }
}
