<?php

namespace App\Services;

use App\Models\Tier;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TierService
{
    public function createTier(User $creator, array $data): Tier
    {
        $data['user_id'] = $creator->id;
        $tier = Tier::create($data);
        return $tier->load('creator:id,name,email, avatar,cover_photo');
    }

    public function updateTier(Tier $tier, array $data): Tier
    {
        $tier->update($data);
        return $tier->fresh()->load('creator:id,name,email,avatar,cover_photo');
    }

    public function deleteTier(Tier $tier): bool
    {
        try {
            DB::beginTransaction();

            Log::info('Attempting to delete tier', [
                'tier_id' => $tier->id,
                'tier_data' => $tier->toArray()
            ]);

            // Check if tier has any subscribers
            if ($tier->subscribers()->exists()) {
                Log::warning('Cannot delete tier - has subscribers', [
                    'tier_id' => $tier->id,
                    'subscribers_count' => $tier->subscribers()->count()
                ]);
                DB::rollBack();
                throw new \Exception('Cannot delete tier with existing subscribers');
            }

            // Delete using the model to trigger events
            $result = $tier->delete();
            
            Log::info('Tier deletion result', [
                'tier_id' => $tier->id,
                'result' => $result,
                'verification' => Tier::where('id', $tier->id)->exists()
            ]);
            
            DB::commit();
            
            return (bool) $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete tier', [
                'tier_id' => $tier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getCreatorTiers(User $creator): Collection
    {
        Log::info('Fetching creator tiers', [
            'creator_id' => $creator->id
        ]);

        $tiers = $creator->tiers()
            ->with('creator:id,name,email,avatar,cover_photo')
            ->orderBy('base_price')
            ->get();

        Log::info('Retrieved creator tiers', [
            'creator_id' => $creator->id,
            'tiers_count' => $tiers->count(),
            'tier_ids' => $tiers->pluck('id')
        ]);

        return $tiers;
    }

    public function getTier(int $id): ?Tier
    {
        return Tier::with('creator:id,name,email,avatar,cover_photo')->find($id);
    }

    public function getUserActiveTiers(User $user): Collection
    {
        return $user->tiers()
            ->where('is_enabled', true)
            ->orderBy('base_price')
            ->get();
    }

    public function subscriberCount(Tier $tier): int
    {
        return $tier->subscribers()->count();
    }

    public function canSubscribe(Tier $tier): bool
    {
        if (!$tier->subscriptions_enabled || !$tier->is_enabled) {
            return false;
        }

        if (!$tier->max_subscribers_enabled) {
            return true;
        }

        return $this->subscriberCount($tier) < $tier->max_subscribers;
    }

    public function enableTier(Tier $tier): Tier
    {
        Log::info('Attempting to enable tier in service', [
            'tier_id' => $tier->id,
            'current_status' => $tier->is_enabled,
            'tier_data' => $tier->toArray()
        ]);

        try {
            DB::beginTransaction();
            
            // Update only the specific fields we want to change
            $tier->update([
                'is_enabled' => true,
                'subscriptions_enabled' => true
            ]);
            
            DB::commit();
            
            Log::info('Successfully enabled tier in service', [
                'tier_id' => $tier->id,
                'new_status' => $tier->is_enabled,
                'updated_tier_data' => $tier->fresh()->toArray()
            ]);
            
            return $tier->fresh()->load('creator:id,name,email,avatar,cover_photo');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to enable tier in service', [
                'tier_id' => $tier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Failed to enable tier: ' . $e->getMessage());
        }
    }

    public function getPriceForDuration(Tier $tier, int $duration): float
    {
        if (!$tier->base_price) {
            throw new \Exception('Base price is required for tier');
        }

        switch ($duration) {
            case 1:
                return (float) $tier->base_price;
            case 2:
                return (float) ($tier->two_month_price ?? ($tier->base_price * 2 * (1 - ($tier->two_month_discount ?? 0) / 100)));
            case 3:
                return (float) ($tier->three_month_price ?? ($tier->base_price * 3 * (1 - ($tier->three_month_discount ?? 0) / 100)));
            case 6:
                return (float) ($tier->six_month_price ?? ($tier->base_price * 6 * (1 - ($tier->six_month_discount ?? 0) / 100)));
            case 12:
                return (float) ($tier->yearly_price ?? ($tier->base_price * 12 * (1 - ($tier->yearly_discount ?? 0) / 100)));
            default:
                throw new \Exception('Invalid subscription duration');
        }
    }

    public function disableTier(Tier $tier): Tier
    {
        Log::info('Attempting to disable tier in service', [
            'tier_id' => $tier->id,
            'current_status' => $tier->is_enabled,
            'active_subscribers' => $tier->activeSubscribers()->count(),
            'tier_data' => $tier->toArray()
        ]);

        try {
            DB::beginTransaction();
            
            $tier->is_enabled = false;
            $tier->subscriptions_enabled = false; // Also disable subscriptions
            $tier->save();
            
            DB::commit();
            
            Log::info('Successfully disabled tier in service', [
                'tier_id' => $tier->id,
                'new_status' => $tier->is_enabled,
                'updated_tier_data' => $tier->fresh()->toArray()
            ]);
            
            return $tier->fresh()->load('creator:id,name,email,avatar,cover_photo');
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to disable tier in service', [
                'tier_id' => $tier->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception('Failed to disable tier: ' . $e->getMessage());
        }
    }
}
