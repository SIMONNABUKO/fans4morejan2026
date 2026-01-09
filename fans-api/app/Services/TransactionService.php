<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletHistory;
use App\Models\Purchase;
use App\Models\ReferralEarning;
use App\Models\Subscription;
use App\Models\Tip;
use App\Models\Post;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    // Transaction types (copied from original Transaction model)
    const TIP = 'tip';
    const ONE_TIME_PURCHASE = 'one_time_purchase';
    const ONE_MONTH_SUBSCRIPTION = 'one_month_subscription';
    const TWO_MONTHS_SUBSCRIPTION = 'two_months_subscription';
    const THREE_MONTHS_SUBSCRIPTION = 'three_months_subscription';
    const SIX_MONTHS_SUBSCRIPTION = 'six_months_subscription';
    const YEARLY_SUBSCRIPTION = 'yearly_subscription';
    const SUBSCRIPTION_RENEWAL = 'subscription_renewal';
    
    // Transaction statuses (copied from original Transaction model)
    const PENDING_STATUS = 'pending';
    const APPROVED_STATUS = 'completed'; // Maps to WalletHistory::STATUS_COMPLETED
    const FAILED_STATUS = 'failed';

    public function createTransaction($sender, $receiver, $amount, $type, $tierId = null)
    {
        // Create sender's wallet history (debit)
        $senderHistory = WalletHistory::create([
            'user_id' => $sender->id,
            'wallet_id' => $sender->wallet->id,
            'amount' => $amount,
            'balance_type' => WalletHistory::BALANCE_AVAILABLE,
            'transaction_type' => WalletHistory::TYPE_DEBIT,
            'payment_type' => $type,
            'description' => "Payment to " . $receiver->username,
            'status' => WalletHistory::STATUS_PENDING,
            'reference_id' => uniqid('txn_'),
        ]);
        
        // Create receiver's wallet history (credit)
        $receiverHistory = WalletHistory::create([
            'user_id' => $receiver->id,
            'wallet_id' => $receiver->wallet->id,
            'amount' => $amount,
            'balance_type' => WalletHistory::BALANCE_PENDING, // Pending until approved
            'transaction_type' => WalletHistory::TYPE_CREDIT,
            'payment_type' => $type,
            'description' => "Payment from " . $sender->username,
            'status' => WalletHistory::STATUS_PENDING,
            'reference_id' => $senderHistory->reference_id, // Use same reference ID to link the transactions
            'transactionable_type' => $tierId ? 'App\\Models\\Tier' : null,
            'transactionable_id' => $tierId,
        ]);
        
        // Return an object that mimics the structure of the original Transaction model
        return (object) [
            'id' => $senderHistory->id,
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $amount,
            'type' => $type,
            'status' => self::PENDING_STATUS,
            'tier_id' => $tierId,
            'reference_id' => $senderHistory->reference_id,
            'created_at' => $senderHistory->created_at,
            'updated_at' => $senderHistory->updated_at
        ];
    }

    public function updateTransactionStatus($transaction, $status)
    {
        // Map Transaction status to WalletHistory status
        $statusMap = [
            self::PENDING_STATUS => WalletHistory::STATUS_PENDING,
            self::APPROVED_STATUS => WalletHistory::STATUS_COMPLETED,
            self::FAILED_STATUS => WalletHistory::STATUS_FAILED,
        ];
        
        $mappedStatus = $statusMap[$status] ?? $status;
        
        // Find all wallet histories with the same reference_id
        // If $transaction is a WalletHistory object, use its reference_id
        // If $transaction is a Transaction-like object, use its reference_id property
        $referenceId = $transaction->reference_id ?? $transaction->id;
        
        $walletHistories = WalletHistory::where('reference_id', $referenceId)->get();
        
        foreach ($walletHistories as $history) {
            $history->status = $mappedStatus;
            
            // If approved and it's a credit, move from pending to available balance
            if ($mappedStatus === WalletHistory::STATUS_COMPLETED && $history->transaction_type === WalletHistory::TYPE_CREDIT) {
                $history->balance_type = WalletHistory::BALANCE_AVAILABLE;
            }
            
            $history->save();
        }
        
        // Update the transaction object to maintain compatibility
        if (is_object($transaction)) {
            $transaction->status = $status;
        }
    }

    public function getTransactionsByUser(User $user)
    {
        // Get all wallet histories for the user
        $walletHistories = WalletHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Transform wallet histories to match the structure of Transaction objects
        $transactions = [];
        
        foreach ($walletHistories as $history) {
            // For each unique reference_id, create one transaction-like object
            // Skip if we've already processed this reference_id
            if (isset($transactions[$history->reference_id])) {
                continue;
            }
            
            // Find the paired transaction (if this is a credit, find the debit and vice versa)
            $pairedHistory = WalletHistory::where('reference_id', $history->reference_id)
                ->where('id', '!=', $history->id)
                ->first();
            
            // Determine sender_id and receiver_id based on transaction_type
            $senderId = $history->transaction_type === WalletHistory::TYPE_DEBIT ? 
                $history->user_id : 
                ($pairedHistory ? $pairedHistory->user_id : null);
                
            $receiverId = $history->transaction_type === WalletHistory::TYPE_CREDIT ? 
                $history->user_id : 
                ($pairedHistory ? $pairedHistory->user_id : null);
            
            // Map WalletHistory status to Transaction status
            $statusMap = [
                WalletHistory::STATUS_PENDING => self::PENDING_STATUS,
                WalletHistory::STATUS_COMPLETED => self::APPROVED_STATUS,
                WalletHistory::STATUS_FAILED => self::FAILED_STATUS,
            ];
            
            $transactions[$history->reference_id] = (object) [
                'id' => $history->id,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'amount' => $history->amount,
                'type' => $history->payment_type,
                'status' => $statusMap[$history->status] ?? $history->status,
                'tier_id' => $history->transactionable_type === 'App\\Models\\Tier' ? $history->transactionable_id : null,
                'reference_id' => $history->reference_id,
                'created_at' => $history->created_at,
                'updated_at' => $history->updated_at
            ];
        }
        
        // Convert associative array to indexed array
        return array_values($transactions);
    }

    /**
     * Get earnings statistics for a user
     * 
     * @param User $user The user to get statistics for
     * @param string $period The period to get statistics for (30days, month, year)
     * @param string|null $startDate Optional start date (Y-m-d)
     * @param string|null $endDate Optional end date (Y-m-d)
     * @return array
     */
    public function getEarningsStatistics(User $user, string $period = '30days', ?string $startDate = null, ?string $endDate = null)
    {
        // Set default dates based on period if not provided
        if (!$startDate || !$endDate) {
            switch ($period) {
                case '30days':
                    $startDate = Carbon::now()->subDays(30)->startOfDay()->format('Y-m-d');
                    $endDate = Carbon::now()->endOfDay()->format('Y-m-d');
                    break;
                case 'month':
                    $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                    break;
                case 'year':
                    $startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                    $endDate = Carbon::now()->endOfYear()->format('Y-m-d');
                    break;
                default:
                    $startDate = Carbon::now()->subDays(30)->startOfDay()->format('Y-m-d');
                    $endDate = Carbon::now()->endOfDay()->format('Y-m-d');
            }
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Get daily earnings grouped by type (tips, subscriptions, purchases)
        $dailyEarnings = $this->getDailyEarnings($user, $start, $end);

        // Get daily referral earnings (paid only)
        $referralEarnings = $user->referralEarnings()
            ->where('referral_earnings.status', 'paid')
            ->whereBetween('referral_earnings.created_at', [$start->startOfDay(), $end->endOfDay()])
            ->get();
        $referralByDate = [];
        foreach ($referralEarnings as $earning) {
            $dateKey = Carbon::parse($earning->created_at)->format('Y-m-d');
            if (!isset($referralByDate[$dateKey])) {
                $referralByDate[$dateKey] = 0;
            }
            $referralByDate[$dateKey] += $earning->amount;
        }

        // Build chart data (gross/net per day)
        $chart = [];
        $grossTotal = 0;
        $netTotal = 0;
        $tipsTotal = 0;
        $subsTotal = 0;
        $purchasesTotal = 0;
        $referralsTotal = 0;
        $platformFeePercent = 15; // TODO: get from settings if needed
        foreach ($dailyEarnings as $day) {
            $dateKey = Carbon::parse($day['date'])->format('Y-m-d');
            $tips = $day['tips'] ?? 0;
            $subs = $day['subscriptions'] ?? 0;
            $purchases = $day['media'] ?? 0;
            $referrals = $referralByDate[$dateKey] ?? 0;
            $gross = $tips + $subs + $purchases + $referrals;
            $net = ($tips + $subs + $purchases) * (1 - $platformFeePercent / 100) + $referrals; // referrals are net
            $chart[] = [
                'date' => $day['date'],
                'gross' => round($gross, 2),
                'net' => round($net, 2),
                'tips' => round($tips, 2),
                'subscriptions' => round($subs, 2),
                'purchases' => round($purchases, 2),
                'referrals' => round($referrals, 2),
            ];
            $grossTotal += $gross;
            $netTotal += $net;
            $tipsTotal += $tips;
            $subsTotal += $subs;
            $purchasesTotal += $purchases;
            $referralsTotal += $referrals;
        }

        // Breakdown by source
        $breakdown = [
            [
                'id' => 'tips',
                'name' => 'Tips',
                'gross' => round($tipsTotal, 2),
                'net' => round($tipsTotal * (1 - $platformFeePercent / 100), 2),
            ],
            [
                'id' => 'subscriptions',
                'name' => 'Subscriptions',
                'gross' => round($subsTotal, 2),
                'net' => round($subsTotal * (1 - $platformFeePercent / 100), 2),
            ],
            [
                'id' => 'purchases',
                'name' => 'Purchases',
                'gross' => round($purchasesTotal, 2),
                'net' => round($purchasesTotal * (1 - $platformFeePercent / 100), 2),
            ],
            [
                'id' => 'referrals',
                'name' => 'Referrals',
                'gross' => round($referralsTotal, 2),
                'net' => round($referralsTotal, 2), // referrals are net
            ],
        ];

        // Top source (by gross)
        $topSource = collect($breakdown)->sortByDesc('gross')->first();

        // Conversion rate (net/gross)
        $conversionRate = $grossTotal > 0 ? round(($netTotal / $grossTotal) * 100, 1) : 0;

        // Monthly summary (net earnings for current month)
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $monthlyNet = 0;
        // Sum net for current month
        foreach ($chart as $row) {
            $rowDate = Carbon::parse($row['date']);
            if ($rowDate->between($currentMonthStart, $currentMonthEnd)) {
                $monthlyNet += $row['net'];
            }
        }

        return [
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'chart' => $chart,
            'total' => [
                'gross' => round($grossTotal, 2),
                'net' => round($netTotal, 2),
            ],
            'breakdown' => $breakdown,
            'monthly_summary' => [
                'month' => $currentMonthStart->format('F, Y'),
                'net' => round($monthlyNet, 2),
            ],
            'top_source' => $topSource,
            'conversion_rate' => $conversionRate,
        ];
    }

    /**
     * Get monthly earnings statistics for a user for the past year
     * 
     * @param User $user The user to get statistics for
     * @return array
     */
    public function getMonthlyEarningsStatistics(User $user)
    {
        $months = [];
        $startDate = Carbon::now()->subMonths(11)->startOfMonth();
        
        // Get data for the last 12 months
        for ($i = 0; $i < 12; $i++) {
            $currentMonth = $startDate->copy()->addMonths($i);
            $monthStart = $currentMonth->copy()->startOfMonth();
            $monthEnd = $currentMonth->copy()->endOfMonth();
            
            $monthlyStats = $this->getEarningsStatistics(
                $user, 
                'month', 
                $monthStart->format('Y-m-d'), 
                $monthEnd->format('Y-m-d')
            );
            
            $months[] = [
                'name' => $currentMonth->format('M Y'),
                'amount' => $monthlyStats['total'],
                'expanded' => false,
                'badge' => $this->calculateTopPercentage($user, $monthStart, $monthEnd),
            ];
        }
        
        return $months;
    }

    /**
     * Get daily earnings for a user within a date range
     * 
     * @param User $user The user to get earnings for
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return array
     */
    private function getDailyEarnings(User $user, Carbon $startDate, Carbon $endDate)
    {
        // Get only CREDIT transactions where the user is the receiver
        $walletHistories = WalletHistory::where('user_id', $user->id)
            ->where('transaction_type', WalletHistory::TYPE_CREDIT)
            ->where('status', WalletHistory::STATUS_COMPLETED) // Equivalent to APPROVED_STATUS
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->get();

        // Initialize the result array with all dates in the range
        $result = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $result[$dateKey] = [
                'date' => $currentDate->format('M j, Y'),
                'tips' => 0,
                'subscriptions' => 0,
                'media' => 0
            ];
            $currentDate->addDay();
        }

        // Categorize transactions and sum amounts by date
        foreach ($walletHistories as $history) {
            $dateKey = Carbon::parse($history->created_at)->format('Y-m-d');
            
            if (!isset($result[$dateKey])) {
                continue; // Skip if date is not in our range (shouldn't happen but just in case)
            }

            // Use payment_type instead of type, but keep the same logic
            if ($history->payment_type === self::TIP) {
                $result[$dateKey]['tips'] += $history->amount;
            } elseif ($this->isSubscriptionType($history->payment_type)) {
                $result[$dateKey]['subscriptions'] += $history->amount;
            } elseif ($history->payment_type === self::ONE_TIME_PURCHASE) {
                $result[$dateKey]['media'] += $history->amount;
            }
        }

        // Convert to indexed array and round values
        $indexedResult = [];
        foreach ($result as $date => $data) {
            $data['tips'] = round($data['tips'], 2);
            $data['subscriptions'] = round($data['subscriptions'], 2);
            $data['media'] = round($data['media'], 2);
            $indexedResult[] = $data;
        }

        return $indexedResult;
    }

    /**
     * Check if a transaction type is a subscription type
     * 
     * @param string|null $type Transaction type
     * @return bool
     */
    private function isSubscriptionType(?string $type): bool
    {
        // If type is null, it's not a subscription
        if ($type === null) {
            return false;
        }
        
        return in_array($type, [
            self::ONE_MONTH_SUBSCRIPTION,
            self::TWO_MONTHS_SUBSCRIPTION,
            self::THREE_MONTHS_SUBSCRIPTION,
            self::SIX_MONTHS_SUBSCRIPTION,
            self::YEARLY_SUBSCRIPTION,
            self::SUBSCRIPTION_RENEWAL
        ]);
    }

    /**
     * Calculate top percentage badge for a user (mock implementation)
     * In a real app, this would compare to other creators' earnings
     * 
     * @param User $user The user
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return string|null
     */
    private function calculateTopPercentage(User $user, Carbon $startDate, Carbon $endDate): ?string
    {
        // Match the original logic: only count CREDIT transactions that are COMPLETED
        $userEarnings = WalletHistory::where('user_id', $user->id)
            ->where('transaction_type', WalletHistory::TYPE_CREDIT)
            ->where('status', WalletHistory::STATUS_COMPLETED)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');
        
        if ($userEarnings > 1000) {
            return '2.30%';
        } elseif ($userEarnings > 500) {
            return '4.90%';
        } elseif ($userEarnings > 100) {
            return '14.92%';
        }
        
        return null;
    }

    public function getAnalyticsData(array $filters)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return [
                    'table_data' => [],
                    'chart_data' => [],
                    'summary' => ['total' => 0, 'growth' => '0%'],
                ];
            }

            $contentType = $filters['content_type'] ?? 'posts';
            $period = $this->parsePeriod($filters['period'] ?? 'last30');
            $metric = $filters['metric'] ?? 'purchases';

            Log::info('Analytics request', [
                'user_id' => $user->id,
                'content_type' => $contentType,
                'period' => $period,
                'metric' => $metric,
                'filters' => $filters
            ]);

            if ($contentType === 'posts') {
                return $this->getPostAnalytics($user, $period, $metric);
            }

            if ($contentType === 'messages') {
                return $this->getMessageAnalytics($user, $period, $metric);
            }

            return [
                'table_data' => [],
                'chart_data' => [],
                'summary' => ['total' => 0, 'growth' => '0%'],
            ];
        } catch (\Exception $e) {
            Log::error('Error in getAnalyticsData', [
                'error' => $e->getMessage(),
                'filters' => $filters,
                'user_id' => Auth::id()
            ]);

            return [
                'table_data' => [],
                'chart_data' => [],
                'summary' => ['total' => 0, 'growth' => '0%'],
            ];
        }
    }

    protected function getMessageAnalytics($user, $period, $metric)
    {
        try {
            // For messages, we are only concerned with purchases and tips
            if (!in_array($metric, ['purchases', 'tips'])) {
                Log::info('Message analytics: metric not supported', ['metric' => $metric]);
                return [
                    'table_data' => [],
                    'chart_data' => [],
                    'summary' => ['total' => 0, 'growth' => '0%'],
                ];
            }
            
            if ($metric === 'tips') {
                // For tips, we need to query tips directly since they might not be linked to specific messages
                $tipsQuery = Tip::where('recipient_id', $user->id)
                    ->where('tippable_type', 'message')
                    ->whereBetween('created_at', [$period['start'], $period['end']]);

                // Get tips that are either linked to specific messages or general message tips
                $tips = (clone $tipsQuery)
                    ->orderBy('created_at', 'desc')
                    ->get();

                Log::info('Message tips query results', [
                    'total_tips' => $tips->count(),
                    'linked_tips' => $tips->whereNotNull('tippable_id')->count(),
                    'unlinked_tips' => $tips->whereNull('tippable_id')->count(),
                    'total_amount' => $tips->sum('amount')
                ]);

                // Create table data from tips
                $tableData = $tips->map(function ($tip) {
                    return [
                        'id' => $tip->id,
                        'date' => $tip->created_at->format('M d, Y'),
                        'time' => $tip->created_at->format('h:i A'),
                        'viewers' => null, // Not applicable
                        'tips' => $tip->amount,
                        'price' => 0, // Not applicable for tips
                        'purchases' => 0, // Not applicable for tips
                        'likes' => null, // Not applicable
                        'comments' => null, // Not applicable
                    ];
                });

                // Generate chart data for tips
                $chartData = $this->generateTipsChartData($tipsQuery, $period);
                
                // Summary Data
                $total = $tips->sum('amount');
                $summary = [
                    'total' => $total,
                    'growth' => '0%', // Placeholder
                ];

                return [
                    'table_data' => $tableData,
                    'chart_data' => $chartData,
                    'summary' => $summary,
                ];
            } else {
                // For purchases, use the original message query
                $query = Message::where(function($q) use ($user) {
                    $q->where('messages.receiver_id', $user->id)  // Messages received by creator
                      ->orWhere('messages.sender_id', $user->id); // Messages sent by creator
                })
                ->whereBetween('messages.created_at', [$period['start'], $period['end']]);

                // Log the query for debugging
                Log::info('Message purchases query', [
                    'user_id' => $user->id,
                    'period_start' => $period['start'],
                    'period_end' => $period['end'],
                    'metric' => $metric,
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings()
                ]);

                // Detailed Analytics Table Data
                $messages = (clone $query)
                    ->withSum('purchases as purchases_total', 'price')
                    ->orderBy('created_at', 'desc')
                    ->get();

                Log::info('Message purchases results', [
                    'total_messages' => $messages->count(),
                    'messages_with_purchases' => $messages->where('purchases_total', '>', 0)->count(),
                    'total_purchases' => $messages->sum('purchases_total')
                ]);

                $tableData = $messages->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'date' => $message->created_at->format('M d, Y'),
                        'time' => $message->created_at->format('h:i A'),
                        'viewers' => null, // Not applicable
                        'tips' => 0, // Not applicable for purchases
                        'price' => $message->price ?? 0,
                        'purchases' => $message->purchases_total ?? 0,
                        'likes' => null, // Not applicable
                        'comments' => null, // Not applicable
                    ];
                });

                // Chart Data
                $chartData = $this->generateChartData((clone $query), $period, $metric, Message::class);
                
                // Summary Data
                $total = $chartData->sum('total');
                $summary = [
                    'total' => $total,
                    'growth' => '0%', // Placeholder
                ];

                return [
                    'table_data' => $tableData,
                    'chart_data' => $chartData,
                    'summary' => $summary,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error in getMessageAnalytics', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'period' => $period,
                'metric' => $metric
            ]);

            return [
                'table_data' => [],
                'chart_data' => [],
                'summary' => ['total' => 0, 'growth' => '0%'],
            ];
        }
    }

    protected function getPostAnalytics($user, $period, $metric)
    {
        try {
            Log::info('Starting getPostAnalytics', [
                'user_id' => $user->id,
                'period_start' => $period['start'],
                'period_end' => $period['end'],
                'metric' => $metric
            ]);

            if ($metric === 'purchases') {
                // For purchases, use the same data source as overview (WalletHistory)
                $purchasesQuery = WalletHistory::where('user_id', $user->id)
                    ->where('transaction_type', WalletHistory::TYPE_CREDIT)
                    ->where('status', WalletHistory::STATUS_COMPLETED)
                    ->where('payment_type', self::ONE_TIME_PURCHASE)
                    ->whereBetween('created_at', [$period['start'], $period['end']]);

                Log::info('Post purchases query (WalletHistory)', [
                    'sql' => $purchasesQuery->toSql(),
                    'bindings' => $purchasesQuery->getBindings(),
                    'user_id' => $user->id
                ]);

                $purchases = (clone $purchasesQuery)
                    ->orderBy('created_at', 'desc')
                    ->get();

                Log::info('Post purchases results (WalletHistory)', [
                    'total_purchases' => $purchases->count(),
                    'total_amount' => $purchases->sum('amount')
                ]);

                // Create table data from purchases
                $tableData = $purchases->map(function ($purchase) {
                    return [
                        'id' => $purchase->id,
                        'date' => $purchase->created_at->format('M d, Y'),
                        'time' => $purchase->created_at->format('h:i A'),
                        'viewers' => null, // Not applicable
                        'tips' => 0, // Not applicable for purchases
                        'price' => 0, // Not applicable for purchases
                        'purchases' => $purchase->amount,
                        'likes' => null, // Not applicable
                        'comments' => null, // Not applicable
                    ];
                });

                // Generate chart data for purchases
                $chartData = $this->generatePurchasesChartData($purchasesQuery, $period);
                
                // Summary Data
                $total = $purchases->sum('amount');
                $summary = [
                    'total' => $total,
                    'growth' => '0%', // Placeholder
                ];

                Log::info('Post purchases summary (WalletHistory)', [
                    'summary_total' => $total,
                    'table_data_count' => $tableData->count()
                ]);

                return [
                    'table_data' => $tableData,
                    'chart_data' => $chartData,
                    'summary' => $summary,
                ];
            } else {
                // For other metrics (tips, views, likes, comments), use the original post query
                $query = Post::where('posts.user_id', $user->id)
                    ->whereBetween('posts.created_at', [$period['start'], $period['end']]);

                // Log the base query
                Log::info('Post analytics base query', [
                    'sql' => $query->toSql(),
                    'bindings' => $query->getBindings(),
                    'user_id' => $user->id
                ]);

                // Check total posts in the period
                $totalPostsInPeriod = (clone $query)->count();
                Log::info('Total posts in period', [
                    'total_posts' => $totalPostsInPeriod,
                    'period_start' => $period['start'],
                    'period_end' => $period['end']
                ]);

                // Detailed Analytics Table Data
                $posts = (clone $query)
                    ->withCount(['likes', 'comments'])
                    ->withSum('purchases as purchases_total', 'price')
                    ->withSum('tips as tips_total', 'amount')
                    ->with('stats') // For views
                    ->with('permissionSets') // For price
                    ->orderBy('created_at', 'desc')
                    ->get();

                Log::info('Post analytics results', [
                    'total_posts' => $posts->count(),
                    'posts_with_purchases' => $posts->where('purchases_total', '>', 0)->count(),
                    'posts_with_tips' => $posts->where('tips_total', '>', 0)->count(),
                    'total_purchases_amount' => $posts->sum('purchases_total'),
                    'total_tips_amount' => $posts->sum('tips_total'),
                    'posts_with_stats' => $posts->whereNotNull('stats')->count(),
                    'posts_with_permission_sets' => $posts->where('permissionSets')->count()
                ]);

                // Log individual post details for debugging
                foreach ($posts->take(5) as $post) {
                    Log::info('Post details', [
                        'post_id' => $post->id,
                        'created_at' => $post->created_at,
                        'purchases_total' => $post->purchases_total,
                        'tips_total' => $post->tips_total,
                        'likes_count' => $post->likes_count,
                        'comments_count' => $post->comments_count,
                        'has_stats' => $post->stats ? 'yes' : 'no',
                        'has_permission_sets' => $post->permissionSets->count()
                    ]);
                }

                $tableData = $posts->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'date' => $post->created_at->format('M d, Y'),
                        'time' => $post->created_at->format('h:i A'),
                        'viewers' => $post->stats?->views_count ?? 0,
                        'tips' => $post->tips_total ?? 0,
                        'price' => $post->permissionSets->first()?->price ?? 0, // Get price from permission sets
                        'purchases' => $post->purchases_total ?? 0,
                        'likes' => $post->likes_count ?? 0,
                        'comments' => $post->comments_count ?? 0,
                    ];
                });

                // Chart Data
                $chartData = $this->generateChartData((clone $query), $period, $metric, Post::class);

                Log::info('Post chart data', [
                    'chart_data_count' => $chartData->count(),
                    'chart_data_total' => $chartData->sum('total'),
                    'chart_data_sample' => $chartData->take(3)->toArray()
                ]);

                // Summary Data
                $total = $chartData->sum('total');
                $summary = [
                    'total' => $total,
                    'growth' => '0%', // Placeholder
                ];

                Log::info('Post analytics summary', [
                    'summary_total' => $total,
                    'table_data_count' => $tableData->count()
                ]);

                return [
                    'table_data' => $tableData,
                    'chart_data' => $chartData,
                    'summary' => $summary,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error in getPostAnalytics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
                'period' => $period,
                'metric' => $metric
            ]);

            return [
                'table_data' => [],
                'chart_data' => [],
                'summary' => ['total' => 0, 'growth' => '0%'],
            ];
        }
    }

    protected function generateChartData($query, $period, $metric, $modelClass)
    {
        try {
            $data = collect();
            $queryClone = clone $query;
            $tableName = (new $modelClass)->getTable();

            switch ($metric) {
                case 'purchases':
                    $data = $queryClone->join('purchases', "{$tableName}.id", '=', 'purchases.purchasable_id')
                        ->where('purchases.purchasable_type', $modelClass)
                        ->select(DB::raw("DATE({$tableName}.created_at) as date"), DB::raw('SUM(purchases.price) as total'))
                        ->groupBy(DB::raw("DATE({$tableName}.created_at)"))
                        ->orderBy('date')
                        ->get();
                    break;
                case 'tips':
                    $data = $queryClone->join('tips', "{$tableName}.id", '=', 'tips.tippable_id')
                        ->where('tips.tippable_type', $modelClass)
                        ->select(DB::raw("DATE({$tableName}.created_at) as date"), DB::raw('SUM(tips.amount) as total'))
                        ->groupBy(DB::raw("DATE({$tableName}.created_at)"))
                        ->orderBy('date')
                        ->get();
                    break;
                case 'views':
                    if ($modelClass === Post::class) {
                        $data = $queryClone->join('stats', "{$tableName}.id", '=', 'stats.statable_id')
                            ->where('stats.statable_type', $modelClass)
                            ->select(DB::raw("DATE({$tableName}.created_at) as date"), DB::raw('SUM(stats.views_count) as total'))
                            ->groupBy(DB::raw("DATE({$tableName}.created_at)"))
                            ->orderBy('date')
                            ->get();
                    }
                    break;
                case 'likes':
                    if ($modelClass === Post::class) {
                        $data = $queryClone->join('likes', "{$tableName}.id", '=', 'likes.likeable_id')
                            ->where('likes.likeable_type', $modelClass)
                            ->select(DB::raw("DATE({$tableName}.created_at) as date"), DB::raw('COUNT(likes.id) as total'))
                            ->groupBy(DB::raw("DATE({$tableName}.created_at)"))
                            ->orderBy('date')
                            ->get();
                    }
                    break;
                case 'comments':
                    if ($modelClass === Post::class) {
                        $data = $queryClone->join('comments', "{$tableName}.id", '=', 'comments.post_id')
                            ->select(DB::raw("DATE({$tableName}.created_at) as date"), DB::raw('COUNT(comments.id) as total'))
                            ->groupBy(DB::raw("DATE({$tableName}.created_at)"))
                            ->orderBy('date')
                            ->get();
                    }
                    break;
            }

            // Ensure all dates in the period have data points (fill with 0 if no data)
            $allDates = collect();
            $startDate = $period['start']->copy();
            $endDate = $period['end']->copy();
            
            while ($startDate <= $endDate) {
                $allDates->push($startDate->format('Y-m-d'));
                $startDate->addDay();
            }

            // Create a map of existing data
            $dataMap = $data->keyBy('date');
            
            // Fill missing dates with 0 values
            $filledData = $allDates->map(function ($date) use ($dataMap) {
                return [
                    'date' => $date,
                    'total' => $dataMap->get($date)?->total ?? 0
                ];
            });

            return $filledData;
        } catch (\Exception $e) {
            Log::error('Error in generateChartData', [
                'error' => $e->getMessage(),
                'metric' => $metric,
                'modelClass' => $modelClass
            ]);

            return collect();
        }
    }

    protected function generateTipsChartData($query, $period)
    {
        try {
            $data = (clone $query)
                ->select(DB::raw("DATE(created_at) as date"), DB::raw('SUM(amount) as total'))
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderBy('date')
                ->get();

            // Ensure all dates in the period have data points (fill with 0 if no data)
            $allDates = collect();
            $startDate = $period['start']->copy();
            $endDate = $period['end']->copy();
            
            while ($startDate <= $endDate) {
                $allDates->push($startDate->format('Y-m-d'));
                $startDate->addDay();
            }

            // Create a map of existing data
            $dataMap = $data->keyBy('date');
            
            // Fill missing dates with 0 values
            $filledData = $allDates->map(function ($date) use ($dataMap) {
                return [
                    'date' => $date,
                    'total' => $dataMap->get($date)?->total ?? 0
                ];
            });

            return $filledData;
        } catch (\Exception $e) {
            Log::error('Error in generateTipsChartData', [
                'error' => $e->getMessage()
            ]);

            return collect();
        }
    }

    protected function generatePurchasesChartData($query, $period)
    {
        try {
            $data = (clone $query)
                ->select(DB::raw("DATE(created_at) as date"), DB::raw('SUM(amount) as total'))
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderBy('date')
                ->get();

            // Ensure all dates in the period have data points (fill with 0 if no data)
            $allDates = collect();
            $startDate = $period['start']->copy();
            $endDate = $period['end']->copy();
            
            while ($startDate <= $endDate) {
                $allDates->push($startDate->format('Y-m-d'));
                $startDate->addDay();
            }

            // Create a map of existing data
            $dataMap = $data->keyBy('date');
            
            // Fill missing dates with 0 values
            $filledData = $allDates->map(function ($date) use ($dataMap) {
                return [
                    'date' => $date,
                    'total' => $dataMap->get($date)?->total ?? 0
                ];
            });

            return $filledData;
        } catch (\Exception $e) {
            Log::error('Error in generatePurchasesChartData', [
                'error' => $e->getMessage()
            ]);

            return collect();
        }
    }

    protected function parsePeriod($period)
    {
        $end = Carbon::now();
        $start = match ($period) {
            'last90' => $end->copy()->subDays(90),
            'last180' => $end->copy()->subMonths(6),
            default => $end->copy()->subDays(30),
        };

        return ['start' => $start->startOfDay(), 'end' => $end->endOfDay()];
    }
}