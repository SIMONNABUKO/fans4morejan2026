<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get all wallets with pagination and filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Wallet::with('user')
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('user', function ($uq) use ($request) {
                    $uq->where('name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->when($request->min_balance, function ($q) use ($request) {
                $q->where('available_balance', '>=', $request->min_balance);
            })
            ->when($request->max_balance, function ($q) use ($request) {
                $q->where('available_balance', '<=', $request->max_balance);
            })
            ->when($request->has_pending, function ($q) use ($request) {
                if ($request->has_pending) {
                    $q->where('pending_balance', '>', 0);
                }
            })
            ->when($request->sort_by, function ($q) use ($request) {
                $direction = $request->sort_direction ?? 'desc';
                $q->orderBy($request->sort_by, $direction);
            }, function ($q) {
                $q->orderBy('total_balance', 'desc');
            });

        $wallets = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $wallets->items(),
            'total' => $wallets->total(),
            'per_page' => $wallets->perPage(),
            'current_page' => $wallets->currentPage(),
            'last_page' => $wallets->lastPage(),
        ]);
    }

    /**
     * Get a specific wallet with its history
     */
    public function show($userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $wallet = $user->wallet()->with('history')->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found'], 404);
        }

        return response()->json([
            'wallet' => $wallet,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Get wallet history with filters
     */
    public function history(Request $request, $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        
        $query = WalletHistory::where('wallet_id', $user->wallet->id)
            ->when($request->type, function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->min_amount, function ($q) use ($request) {
                $q->where('amount', '>=', $request->min_amount);
            })
            ->when($request->max_amount, function ($q) use ($request) {
                $q->where('amount', '<=', $request->max_amount);
            })
            ->when($request->date_from, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->orderBy('created_at', 'desc');

        $history = $query->paginate($request->per_page ?? 20);

        return response()->json([
            'data' => $history->items(),
            'total' => $history->total(),
            'per_page' => $history->perPage(),
            'current_page' => $history->currentPage(),
            'last_page' => $history->lastPage(),
        ]);
    }

    /**
     * Update wallet balances
     */
    public function update(Request $request, $userId): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:add,subtract,move_to_available',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required_unless:action,move_to_available|in:total,pending,available',
            'description' => 'nullable|string',
        ]);

        $user = User::findOrFail($userId);

        try {
            switch ($request->action) {
                case 'add':
                    $wallet = $this->walletService->addFunds(
                        $user,
                        $request->amount,
                        $request->type,
                        $request->description ?? 'Admin: Funds Added',
                        'admin_adjustment'
                    );
                    break;

                case 'subtract':
                    $wallet = $this->walletService->subtractFunds(
                        $user,
                        $request->amount,
                        $request->type,
                        $request->description ?? 'Admin: Funds Subtracted',
                        'admin_adjustment'
                    );
                    break;

                case 'move_to_available':
                    $wallet = $this->walletService->movePendingToAvailable(
                        $user,
                        $request->amount
                    );
                    break;
            }

            return response()->json($wallet);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Get wallet statistics
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'total_balance' => Wallet::sum('total_balance'),
            'available_balance' => Wallet::sum('available_balance'),
            'pending_balance' => Wallet::sum('pending_balance'),
            'total_wallets' => Wallet::count(),
            'wallets_with_pending' => Wallet::where('pending_balance', '>', 0)->count(),
            'recent_transactions' => WalletHistory::with('wallet.user')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
        ];

        return response()->json($stats);
    }
} 