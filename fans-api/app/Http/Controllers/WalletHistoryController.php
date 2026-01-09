<?php

namespace App\Http\Controllers;

use App\Models\WalletHistory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WalletHistoryController extends Controller
{
    /**
     * Get wallet history for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'transaction_type' => 'nullable|in:credit,debit,transfer',
            'status' => 'nullable|in:pending,completed,failed',
        ]);

        $perPage = $request->per_page ?? 15;
        
        $query = auth()->user()->walletHistories()
            ->orderBy('created_at', 'desc');
            
        // Apply filters
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        if ($request->has('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $walletHistory = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $walletHistory
        ]);
    }

    /**
     * Get a specific wallet history entry
     */
    public function show(WalletHistory $walletHistory): JsonResponse
    {
        // Check if the wallet history belongs to the authenticated user
        if ($walletHistory->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Load the related transaction if available
        if ($walletHistory->transactionable_id && $walletHistory->transactionable_type) {
            $walletHistory->load('transactionable');
        }

        return response()->json([
            'success' => true,
            'data' => $walletHistory
        ]);
    }
}