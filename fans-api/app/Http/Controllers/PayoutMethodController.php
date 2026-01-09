<?php

namespace App\Http\Controllers;

use App\Models\PayoutMethod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PayoutMethodController extends Controller
{
    /**
     * Get all payout methods for the authenticated user
     */
    public function index(): JsonResponse
    {
        $payoutMethods = auth()->user()->payoutMethods;
        return response()->json([
            'success' => true,
            'data' => $payoutMethods
        ]);
    }

    /**
     * Store a new payout method
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'provider' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'nullable|string',
            'is_default' => 'boolean',
            'details' => 'nullable|array',
        ]);

        $user = auth()->user();

        // If this is the first method or is_default is true, make it default
        $isDefault = $request->is_default ?? !$user->payoutMethods()->exists();

        // If setting this as default, unset any existing default
        if ($isDefault) {
            $user->payoutMethods()->update(['is_default' => false]);
        }

        $payoutMethod = $user->payoutMethods()->create([
            'type' => $request->type,
            'provider' => $request->provider,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'is_default' => $isDefault,
            'details' => $request->details,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payout method added successfully',
            'data' => $payoutMethod
        ], 201);
    }

    /**
     * Update a payout method
     */
    public function update(Request $request, PayoutMethod $payoutMethod): JsonResponse
    {
        // Check if the payout method belongs to the authenticated user
        if ($payoutMethod->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'provider' => 'string',
            'account_number' => 'string',
            'account_name' => 'nullable|string',
            'is_default' => 'boolean',
            'details' => 'nullable|array',
        ]);

        // If setting this as default, unset any existing default
        if ($request->has('is_default') && $request->is_default) {
            auth()->user()->payoutMethods()->where('id', '!=', $payoutMethod->id)
                ->update(['is_default' => false]);
        }

        $payoutMethod->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Payout method updated successfully',
            'data' => $payoutMethod
        ]);
    }

    /**
     * Delete a payout method
     */
    public function destroy(PayoutMethod $payoutMethod): JsonResponse
    {
        // Check if the payout method belongs to the authenticated user
        if ($payoutMethod->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Check if there are any pending payout requests using this method
        $pendingRequests = $payoutMethod->payoutRequests()
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($pendingRequests) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete payout method with pending requests'
            ], 400);
        }

        $wasDefault = $payoutMethod->is_default;
        $payoutMethod->delete();

        // If this was the default method, set another one as default if available
        if ($wasDefault) {
            $newDefault = auth()->user()->payoutMethods()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payout method deleted successfully'
        ]);
    }

    /**
     * Set a payout method as default
     */
    public function setDefault(PayoutMethod $payoutMethod): JsonResponse
    {
        // Check if the payout method belongs to the authenticated user
        if ($payoutMethod->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Unset any existing default
        auth()->user()->payoutMethods()->update(['is_default' => false]);

        // Set this one as default
        $payoutMethod->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default payout method updated successfully',
            'data' => $payoutMethod
        ]);
    }
}