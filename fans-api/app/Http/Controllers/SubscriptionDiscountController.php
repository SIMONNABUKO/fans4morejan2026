<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionDiscount;
use App\Models\Tier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SubscriptionDiscountController extends Controller
{
    use AuthorizesRequests;

    // List all discounts for a creator's package (tier)
    public function index($tierId)
    {
        $tier = Tier::findOrFail($tierId);
        $this->authorize('view', $tier);
        $discounts = $tier->discounts()->latest()->get();
        return response()->json(['success' => true, 'data' => $discounts]);
    }

    // Create a new discount for a package (tier)
    public function store(Request $request, $tierId)
    {
        $tier = Tier::findOrFail($tierId);
        $this->authorize('update', $tier);

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|max:255',
            'max_uses' => 'nullable|integer|min:1',
            'discounted_price' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|integer|min:1|max:100',
            'amount_off' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'duration_days' => 'nullable|integer|min:1',
            'exclude_previous_claimers' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $discount = $tier->discounts()->create([
            'label' => $request->label,
            'max_uses' => $request->max_uses,
            'discounted_price' => $request->discounted_price,
            'discount_percent' => $request->discount_percent,
            'amount_off' => $request->amount_off,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration_days' => $request->duration_days,
            'exclude_previous_claimers' => $request->exclude_previous_claimers ?? false,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'data' => $discount]);
    }

    // Show a single discount
    public function show($tierId, $discountId)
    {
        $tier = Tier::findOrFail($tierId);
        $this->authorize('view', $tier);
        $discount = $tier->discounts()->findOrFail($discountId);
        return response()->json(['success' => true, 'data' => $discount]);
    }
} 