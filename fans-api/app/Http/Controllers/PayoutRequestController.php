<?php

namespace App\Http\Controllers;

use App\Models\PayoutRequest;
use App\Models\Transaction;
use App\Models\WalletHistory;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PayoutRequestController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Get all payout requests for the authenticated user
     */
    public function index(): JsonResponse
    {
        $payoutRequests = auth()->user()->payoutRequests()
            ->with('payoutMethod')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payoutRequests
        ]);
    }

    /**
     * Request a new payout
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payout_method_id' => 'required|exists:payout_methods,id',
        ]);

        $user = auth()->user();
        $payoutMethod = $user->payoutMethods()->findOrFail($request->payout_method_id);
        
        // Check if user has enough available balance
        if ($user->wallet->available_for_payout < $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient available balance for payout'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            // Create the payout request
            $payoutRequest = new PayoutRequest();
            $payoutRequest->user_id = $user->id;
            $payoutRequest->payout_method_id = $payoutMethod->id;
            $payoutRequest->amount = $request->amount;
            $payoutRequest->status = PayoutRequest::STATUS_PENDING;
            $payoutRequest->reference_id = $payoutRequest->generateReferenceId();
            $payoutRequest->save();

            // Create a transaction record for the payout
            $transaction = new Transaction();
            $transaction->sender_id = $user->id;
            $transaction->receiver_id = null; // System transaction
            $transaction->amount = $request->amount;
            $transaction->type = 'payout_request';
            $transaction->status = Transaction::PENDING_STATUS;
            $transaction->save();

            // Subtract from available balance
            $this->walletService->subtractFunds(
                $user, 
                $request->amount, 
                'available', 
                'Payout Request', 
                $transaction
            );
            
            // Also subtract from total balance
            $this->walletService->subtractFunds(
                $user, 
                $request->amount, 
                'total', 
                'Payout Request', 
                $transaction
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payout request submitted successfully',
                'data' => $payoutRequest
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payout request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific payout request
     */
    public function show(PayoutRequest $payoutRequest): JsonResponse
    {
        // Check if the payout request belongs to the authenticated user
        if ($payoutRequest->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $payoutRequest->load('payoutMethod');

        return response()->json([
            'success' => true,
            'data' => $payoutRequest
        ]);
    }

    /**
     * Cancel a pending payout request
     */
    public function cancel(PayoutRequest $payoutRequest): JsonResponse
    {
        // Check if the payout request belongs to the authenticated user
        if ($payoutRequest->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if the payout request is still pending
        if (!$payoutRequest->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending payout requests can be cancelled'
            ], 400);
        }

        DB::beginTransaction();
        
        try {
            $user = auth()->user();
            $amount = $payoutRequest->amount;

            // Update the payout request status
            $payoutRequest->status = PayoutRequest::STATUS_CANCELLED;
            $payoutRequest->notes = 'Cancelled by user';
            $payoutRequest->save();

            // Create a transaction record for the cancelled payout
            $transaction = new Transaction();
            $transaction->sender_id = null; // System transaction
            $transaction->receiver_id = $user->id;
            $transaction->amount = $amount;
            $transaction->type = 'payout_cancelled';
            $transaction->status = Transaction::APPROVED_STATUS;
            $transaction->save();

            // Return the funds to the user's wallet
            $this->walletService->addFunds(
                $user, 
                $amount, 
                'available', 
                'Payout Request Cancelled', 
                $transaction
            );
            
            $this->walletService->addFunds(
                $user, 
                $amount, 
                'total', 
                'Payout Request Cancelled', 
                $transaction
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payout request cancelled successfully',
                'data' => $payoutRequest
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel payout request: ' . $e->getMessage()
            ], 500);
        }
    }
}