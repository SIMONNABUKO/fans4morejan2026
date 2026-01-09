<?php

namespace App\Http\Controllers;

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

    public function getBalance(): JsonResponse
    {
        $balance = $this->walletService->getWalletBalance(auth()->user());
        return response()->json($balance);
    }

    public function addFunds(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:total,pending,available',
            'description' => 'nullable|string',
            'transaction_type' => 'nullable|string',
        ]);

        $wallet = $this->walletService->addFunds(
            auth()->user(),
            $request->amount,
            $request->type,
            $request->description ?? 'Funds Added',
            $request->transaction_type ?? null
        );

        return response()->json($wallet);
    }

    public function subtractFunds(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:total,pending,available',
            'description' => 'nullable|string',
            'transaction_type' => 'nullable|string',
        ]);

        $wallet = $this->walletService->subtractFunds(
            auth()->user(),
            $request->amount,
            $request->type,
            $request->description ?? 'Funds Subtracted',
            $request->transaction_type ?? null
        );

        return response()->json($wallet);
    }

    public function movePendingToAvailable(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $wallet = $this->walletService->movePendingToAvailable(
            auth()->user(),
            $request->amount
        );

        return response()->json($wallet);
    }
}