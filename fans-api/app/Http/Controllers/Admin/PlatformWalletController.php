<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformWallet;
use App\Models\PlatformWalletHistory;
use Illuminate\Http\Request;

class PlatformWalletController extends Controller
{
    public function getBalance()
    {
        $wallet = PlatformWallet::firstOrCreate(['id' => 1]);
        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $wallet->balance
            ]
        ]);
    }

    public function getHistory(Request $request)
    {
        $history = PlatformWalletHistory::with(['transaction', 'sender', 'receiver'])
            ->when($request->fee_type, function($query, $feeType) {
                $query->where('fee_type', $feeType);
            })
            ->when($request->transaction_type, function($query, $type) {
                $query->where('transaction_type', $type);
            })
            ->when($request->date_from, function($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function($query, $date) {
                $query->whereDate('created_at', '<=', $date);
            })
            ->when($request->min_amount, function($query, $amount) {
                $query->where('amount', '>=', $amount);
            })
            ->when($request->max_amount, function($query, $amount) {
                $query->where('amount', '<=', $amount);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $history->items(),
            'meta' => [
                'current_page' => $history->currentPage(),
                'last_page' => $history->lastPage(),
                'per_page' => $history->perPage(),
                'total' => $history->total(),
                'filters' => [
                    'fee_types' => PlatformWalletHistory::distinct('fee_type')->pluck('fee_type'),
                    'transaction_types' => ['CREDIT', 'DEBIT']
                ]
            ]
        ]);
    }

    public function getStats()
    {
        $wallet = PlatformWallet::firstOrCreate(['id' => 1]);
        
        // Get total earnings (sum of all CREDIT transactions)
        $totalEarnings = PlatformWalletHistory::where('transaction_type', 'CREDIT')
            ->sum('amount');

        // Get today's earnings
        $todayEarnings = PlatformWalletHistory::where('transaction_type', 'CREDIT')
            ->whereDate('created_at', today())
            ->sum('amount');

        // Get this month's earnings
        $monthEarnings = PlatformWalletHistory::where('transaction_type', 'CREDIT')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'current_balance' => $wallet->balance,
                'total_earnings' => $totalEarnings,
                'today_earnings' => $todayEarnings,
                'month_earnings' => $monthEarnings
            ]
        ]);
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string'
        ]);

        $wallet = PlatformWallet::firstOrCreate(['id' => 1]);

        try {
            $wallet->subtractFunds(
                $request->amount,
                null,
                $request->description
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => 'Funds withdrawn successfully',
                    'new_balance' => $wallet->balance
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
} 