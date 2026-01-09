<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['sender', 'receiver', 'tier', 'subscription'])
            ->when($request->type, function ($q, $type) {
                return $q->where('type', $type);
            })
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
            ->when($request->search, function ($q, $search) {
                return $q->where(function ($query) use ($search) {
                    $query->where('id', 'like', "%{$search}%")
                        ->orWhereHas('sender', function ($q) use ($search) {
                            $q->where('username', 'like', "%{$search}%");
                        })
                        ->orWhereHas('receiver', function ($q) use ($search) {
                            $q->where('username', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->startDate, function ($q, $date) {
                return $q->whereDate('created_at', '>=', $date);
            })
            ->when($request->endDate, function ($q, $date) {
                return $q->whereDate('created_at', '<=', $date);
            });

        // Clone query for stats to avoid pagination affecting the counts
        $statsQuery = clone $query;

        // Get statistics
        $stats = [
            'total_transactions' => $statsQuery->count(),
            'total_amount' => $statsQuery->sum('amount'),
            'completed_transactions' => $statsQuery->where('status', Transaction::APPROVED_STATUS)->count(),
            'pending_transactions' => $statsQuery->where('status', Transaction::PENDING_STATUS)->count()
        ];

        // Get paginated results
        $transactions = $query->latest()
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'data' => $transactions,
            'meta' => [
                'stats' => $stats
            ]
        ]);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['sender', 'receiver', 'tier', 'subscription'])
            ->findOrFail($id);

        return response()->json($transaction);
    }

    public function refund($id)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($id);

            if ($transaction->status !== Transaction::APPROVED_STATUS || $transaction->refunded) {
                throw new \Exception('Transaction cannot be refunded');
            }

            // Process refund logic here
            // 1. Return funds to sender's wallet
            $transaction->sender->wallet->increment('balance', $transaction->amount);
            
            // 2. Deduct funds from receiver's wallet
            $transaction->receiver->wallet->decrement('balance', $transaction->amount);
            
            // 3. Mark transaction as refunded
            $transaction->update([
                'status' => Transaction::REFUNDED_STATUS,
                'refunded_at' => now()
            ]);

            DB::commit();
            return response()->json(['message' => 'Transaction refunded successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
} 