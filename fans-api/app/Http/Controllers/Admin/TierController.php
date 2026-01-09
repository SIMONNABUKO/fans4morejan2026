<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTierRequest;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TierController extends Controller
{
    protected $tierService;

    public function __construct(TierService $tierService)
    {
        $this->tierService = $tierService;
    }

    public function index(): JsonResponse
    {
        $tiers = Tier::with(['creator', 'activeSubscribers'])
            ->withCount(['subscribers', 'activeSubscribers'])
            ->orderBy('created_at', 'desc')
            ->paginate();

        // Calculate monthly revenue for each tier
        $tiers->getCollection()->transform(function ($tier) {
            // Debug tier data
            Log::info('Processing tier', [
                'tier_id' => $tier->id,
                'base_price' => $tier->base_price,
                'active_subscribers_count' => $tier->activeSubscribers->count()
            ]);

            // Add all pricing options
            $tier->pricing = [
                'base_price' => (float)$tier->base_price,
                'two_month_price' => (float)$tier->two_month_price,
                'three_month_price' => (float)$tier->three_month_price,
                'six_month_price' => (float)$tier->six_month_price,
                'two_month_discount' => (float)$tier->two_month_discount,
                'three_month_discount' => (float)$tier->three_month_discount,
                'six_month_discount' => (float)$tier->six_month_discount
            ];

            // Calculate monthly revenue from active subscriptions
            $monthlyRevenue = 0;

            foreach ($tier->activeSubscribers as $subscriber) {
                // Debug subscription data
                Log::info('Processing subscription', [
                    'tier_id' => $tier->id,
                    'subscriber_id' => $subscriber->id,
                    'pivot_data' => $subscriber->pivot->toArray()
                ]);

                $subscription = $subscriber->pivot;
                $duration = $subscription->duration ?? 1;
                
                // Use the subscription amount directly if available
                if ($subscription->amount > 0) {
                    $monthlyPrice = $subscription->amount / $duration;
                } else {
                    // Calculate based on tier pricing
                    switch ($duration) {
                        case 2:
                            $monthlyPrice = $tier->two_month_price ? ($tier->two_month_price / 2) : ($tier->base_price * (1 - $tier->two_month_discount / 100));
                            break;
                        case 3:
                            $monthlyPrice = $tier->three_month_price ? ($tier->three_month_price / 3) : ($tier->base_price * (1 - $tier->three_month_discount / 100));
                            break;
                        case 6:
                            $monthlyPrice = $tier->six_month_price ? ($tier->six_month_price / 6) : ($tier->base_price * (1 - $tier->six_month_discount / 100));
                            break;
                        default:
                            $monthlyPrice = $tier->base_price;
                    }
                }
                
                // Debug revenue calculation
                Log::info('Revenue calculation', [
                    'tier_id' => $tier->id,
                    'duration' => $duration,
                    'subscription_amount' => $subscription->amount,
                    'monthly_price' => $monthlyPrice
                ]);
                
                $monthlyRevenue += $monthlyPrice;
            }
            
            $tier->monthly_revenue = round($monthlyRevenue, 2);

            // Debug final revenue
            Log::info('Final tier revenue', [
                'tier_id' => $tier->id,
                'monthly_revenue' => $tier->monthly_revenue
            ]);

            return $tier;
        });

        $stats = [
            'total_tiers' => Tier::count(),
            'active_tiers' => Tier::where('is_enabled', true)->count(),
            'total_subscribers' => Tier::withCount('subscribers')->get()->sum('subscribers_count'),
            'active_subscribers' => Tier::withCount('activeSubscribers')->get()->sum('active_subscribers_count'),
            'monthly_revenue' => $tiers->sum('monthly_revenue')
        ];

        return response()->json([
            'success' => true,
            'data' => $tiers,
            'meta' => [
                'stats' => $stats
            ]
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total_tiers' => Tier::count(),
            'active_tiers' => Tier::where('is_enabled', true)->count(),
            'total_subscribers' => Tier::withCount('subscribers')->get()->sum('subscribers_count'),
            'active_subscribers' => Tier::withCount('activeSubscribers')->get()->sum('active_subscribers_count'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $tier = Tier::with(['subscribers', 'activeSubscribers'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $tier
        ]);
    }

    public function update(UpdateTierRequest $request, int $id): JsonResponse
    {
        $tier = Tier::findOrFail($id);
        $tier->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tier updated successfully',
            'data' => $tier
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $tier = Tier::findOrFail($id);
        
        if ($tier->subscribers()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tier with existing subscribers'
            ], 422);
        }

        $tier->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tier deleted successfully'
        ]);
    }
} 