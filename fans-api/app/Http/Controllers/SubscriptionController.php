<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Models\Tier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\TrackingLinkEvent;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Get all subscriptions for the authenticated user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get all subscriptions where the authenticated user is the subscriber
        // Include both creator and tier relationships
        $subscriptions = $user->subscriptions()
            ->with(['creator', 'tier'])
            ->get();
        
        $formattedSubscriptions = $subscriptions->map(function ($subscription) use ($user) {
            // Format the end date in a readable format
            $endDate = Carbon::parse($subscription->end_date)->format('l, M j, Y');
            
            // Check if the creator is being followed by the user
            $isFollowing = $user->isFollowing($subscription->creator);
            
            // Determine subscription status based on dates and stored status
            $status = $this->determineSubscriptionStatus($subscription);
            
            // Get tier information
            $tierName = $subscription->tier ? $subscription->tier->title : 'Subscription';
            $tierColor = $subscription->tier ? $subscription->tier->color_code : null;
            $tierBenefits = $subscription->tier ? $subscription->tier->subscription_benefits : [];
            
            // Format billing cycle based on duration
            $billingCycle = $this->formatBillingCycle($subscription->duration);
            
            return [
                'id' => $subscription->id,
                'creator' => [
                    'id' => $subscription->creator->id,
                    'name' => $subscription->creator->name,
                    'username' => '@' . $subscription->creator->username,
                    'avatar' => $subscription->creator->avatar ?? '/placeholder.svg',
                    'verified' => false, // Default to false since this field doesn't exist
                    'blocked' => false, // We'll implement this later if needed
                    'following' => $isFollowing
                ],
                'tier' => $tierName,
                'tierColor' => $tierColor,
                'tierBenefits' => $tierBenefits,
                'status' => $status,
                'billingCycle' => $billingCycle,
                'totalMonths' => $subscription->duration, // Use duration as total months subscribed
                'price' => $subscription->amount,
                'expiredDate' => $endDate
            ];
        });

        // Group subscriptions by status for the tab counts
        $activeCount = $formattedSubscriptions->where('status', 'completed')->count();
        $expiredCount = $formattedSubscriptions->where('status', 'expired')->count();
        $totalCount = $formattedSubscriptions->count();

        return response()->json([
            'success' => true,
            'data' => $formattedSubscriptions,
            'counts' => [
                'active' => $activeCount,
                'expired' => $expiredCount,
                'all' => $totalCount
            ]
        ]);
    }

    /**
     * Get all subscribers for the authenticated creator
     */
    public function getSubscribers(Request $request)
    {
        $creator = Auth::user();
        
        // Validate that the user is a creator
        if ($creator->role !== 'creator') {
            return response()->json([
                'success' => false,
                'message' => 'Only creators can access subscriber information'
            ], 403);
        }
        
        // Get query parameters for filtering and pagination
        $status = $request->query('status', 'all'); // active, expired, all
        $search = $request->query('search', '');
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 8);
        
        // Base query - get all subscriptions where the authenticated user is the creator
        $query = Subscription::where('creator_id', $creator->id)
            ->with(['subscriber', 'tier']);
        
        // Apply status filter
        if ($status === 'active') {
            $query->where('status', Subscription::ACTIVE_STATUS)
                ->where('end_date', '>=', Carbon::now());
        } elseif ($status === 'expired') {
            $query->where(function($q) {
                $q->where('status', Subscription::EXPIRED_STATUS)
                  ->orWhere('end_date', '<', Carbon::now());
            });
        }
        
        // Apply search filter if provided
        if ($search) {
            $query->whereHas('subscriber', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }
        
        // Get total count for pagination
        $totalCount = $query->count();
        
        // Apply pagination
        $subscriptions = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        
        // Format the subscribers data
        $formattedSubscribers = $subscriptions->map(function ($subscription) {
            // Format the end date in a readable format
            $endDate = Carbon::parse($subscription->end_date)->format('l, M j, Y');
            
            // Determine if auto-renew is on
            $renewStatus = $subscription->status === Subscription::ACTIVE_STATUS ? 'On' : 'Off';
            
            // Get tier information
            $tierName = $subscription->tier ? $subscription->tier->title : 'Subscription';
            $tierEmoji = $subscription->tier ? $subscription->tier->emoji : null;
            
            // Format billing cycle
            $billingCycle = $this->formatBillingCycle($subscription->duration);
            
            // Truncate username for display if needed
            $handle = '@' . $subscription->subscriber->username;
            if (strlen($handle) > 15) {
                $handle = substr($handle, 0, 12) . '...';
            }
            
            // Check if subscriber has VIP status (you may need to adjust this based on your model)
            $isVip = $subscription->is_vip ?? false;
            
            // Check if subscriber is muted (you may need to adjust this based on your model)
            $isMuted = $subscription->is_muted ?? false;
            
            return [
                'id' => $subscription->id,
                'subscriberId' => $subscription->subscriber->id,
                'username' => $subscription->subscriber->name,
                'handle' => $handle,
                'avatar' => $subscription->subscriber->avatar ?? '/placeholder.svg',
                'tier' => $tierName,
                'emoji' => $tierEmoji,
                'renew' => $renewStatus,
                'billingCycle' => $billingCycle,
                'expiredAt' => $endDate,
                'price' => $subscription->amount,
                'isVip' => $isVip,
                'isMuted' => $isMuted
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedSubscribers,
            'pagination' => [
                'total' => $totalCount,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => ceil($totalCount / $perPage)
            ]
        ]);
    }

    /**
     * Get subscriber counts for each status
     */
    public function getSubscriberCounts(Request $request)
    {
        $creator = Auth::user();
        
        // Validate that the user is a creator
        if ($creator->role !== 'creator') {
            return response()->json([
                'success' => false,
                'message' => 'Only creators can access subscriber information'
            ], 403);
        }
        
        // Get active subscribers count
        $activeCount = Subscription::where('creator_id', $creator->id)
            ->where('status', Subscription::ACTIVE_STATUS)
            ->where('end_date', '>=', Carbon::now())
            ->count();
        
        // Get expired subscribers count
        $expiredCount = Subscription::where('creator_id', $creator->id)
            ->where(function($query) {
                $query->where('status', Subscription::EXPIRED_STATUS)
                      ->orWhere('end_date', '<', Carbon::now());
            })
            ->count();
        
        // Get total subscribers count
        $totalCount = Subscription::where('creator_id', $creator->id)->count();
        
        return response()->json([
            'success' => true,
            'counts' => [
                'active' => $activeCount,
                'expired' => $expiredCount,
                'all' => $totalCount
            ]
        ]);
    }

    /**
     * Toggle VIP status for a subscriber
     */
    public function toggleVipStatus(Request $request, $subscriberId)
    {
        $creator = Auth::user();
        
        // Find the subscription
        $subscription = Subscription::where('creator_id', $creator->id)
            ->where('subscriber_id', $subscriberId)
            ->first();
        
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found'
            ], 404);
        }
        
        // Toggle VIP status
        $subscription->is_vip = !($subscription->is_vip ?? false);
        $subscription->save();
        
        return response()->json([
            'success' => true,
            'message' => $subscription->is_vip ? 'Subscriber marked as VIP' : 'Subscriber removed from VIP',
            'isVip' => $subscription->is_vip
        ]);
    }

    /**
     * Toggle mute status for a subscriber
     */
    public function toggleMuteStatus(Request $request, $subscriberId)
    {
        $creator = Auth::user();
        $subscriberToMute = User::findOrFail($subscriberId);
        
        // Use ListService to manage muted accounts list
        $listService = app(\App\Services\ListService::class);
        
        Log::info('🔇 TOGGLING MUTE STATUS', [
            'creator_id' => $creator->id,
            'subscriber_id' => $subscriberId,
            'subscriber_name' => $subscriberToMute->name
        ]);
        
        try {
            // Check if user is already in muted accounts list
            $mutedMembers = $listService->getListMembers($creator, 5); // Muted Accounts is ID 5
            $isCurrentlyMuted = $mutedMembers->contains('id', $subscriberId);
            
            if ($isCurrentlyMuted) {
                // Remove from muted accounts list
                $listService->removeFromList($creator, 'Muted Accounts', $subscriberToMute);
                $message = 'Subscriber unmuted and removed from muted accounts list';
                $isMuted = false;
            } else {
                // Add to muted accounts list
                $listService->addToList($creator, 'Muted Accounts', $subscriberToMute);
                $message = 'Subscriber muted and added to muted accounts list';
                $isMuted = true;
            }
            
            Log::info('🔇 MUTE STATUS TOGGLED', [
                'creator_id' => $creator->id,
                'subscriber_id' => $subscriberId,
                'is_muted' => $isMuted,
                'action' => $isCurrentlyMuted ? 'unmuted' : 'muted'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'isMuted' => $isMuted
            ]);
        } catch (\Exception $e) {
            Log::error('🔇 FAILED TO TOGGLE MUTE STATUS', [
                'creator_id' => $creator->id,
                'subscriber_id' => $subscriberId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle mute status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Block a subscriber
     */
    public function blockSubscriber(Request $request, $subscriberId)
    {
        $creator = Auth::user();
        $subscriberToBlock = User::findOrFail($subscriberId);
        
        // Use ListService to add to blocked accounts list
        $listService = app(\App\Services\ListService::class);
        
        Log::info('🚫 BLOCKING USER', [
            'creator_id' => $creator->id,
            'subscriber_id' => $subscriberId,
            'subscriber_name' => $subscriberToBlock->name
        ]);
        
        try {
            // Add user to "Blocked Accounts" list using ListService
            $listService->addToList($creator, 'Blocked Accounts', $subscriberToBlock);
            
            Log::info('🚫 USER SUCCESSFULLY BLOCKED', [
                'creator_id' => $creator->id,
                'subscriber_id' => $subscriberId,
                'added_to_list' => 'Blocked Accounts'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'User has been blocked and added to your blocked accounts list'
            ]);
        } catch (\Exception $e) {
            Log::error('🚫 FAILED TO BLOCK USER', [
                'creator_id' => $creator->id,
                'subscriber_id' => $subscriberId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to block user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unblock a subscriber
     */
    public function unblockSubscriber(Request $request, $subscriberId)
    {
        $creator = Auth::user();
        $subscriberToUnblock = User::findOrFail($subscriberId);
        
        // Use ListService to remove from blocked accounts list
        $listService = app(\App\Services\ListService::class);
        
        Log::info('✅ UNBLOCKING USER', [
            'creator_id' => $creator->id,
            'subscriber_id' => $subscriberId,
            'subscriber_name' => $subscriberToUnblock->name
        ]);
        
        try {
            // Remove user from "Blocked Accounts" list using ListService
            $result = $listService->removeFromList($creator, 'Blocked Accounts', $subscriberToUnblock);
            
            Log::info('✅ USER SUCCESSFULLY UNBLOCKED', [
                'creator_id' => $creator->id,
                'subscriber_id' => $subscriberId,
                'removed_from_list' => 'Blocked Accounts',
                'result' => $result
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'User has been unblocked and removed from your blocked accounts list'
            ]);
        } catch (\Exception $e) {
            Log::error('✅ FAILED TO UNBLOCK USER', [
                'creator_id' => $creator->id,
                'subscriber_id' => $subscriberId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to unblock user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get earnings from a specific subscriber
     */
    public function getSubscriberEarnings(Request $request, $subscriberId)
    {
        $creator = Auth::user();
        
        // Get all transactions from this subscriber to the creator
        // This is a placeholder - you'll need to adjust based on your actual transaction model
        $earnings = DB::table('transactions')
            ->where('sender_id', $subscriberId)
            ->where('receiver_id', $creator->id)
            ->sum('amount');
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_earnings' => $earnings,
                'currency' => 'USD'
            ]
        ]);
    }

    /**
     * Renew a subscription
     */
    public function renew(Request $request, Subscription $subscription)
    {
        $user = Auth::user();
        
        // Check if the user owns this subscription
        if ($subscription->subscriber_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to renew this subscription'
            ], 403);
        }
        
        // Calculate new dates based on the subscription duration
        $startDate = Carbon::now();
        $endDate = $startDate->copy()->addMonths($subscription->duration);
        
        // Create a new subscription or update the existing one
        $subscription->status = Subscription::PENDING_STATUS;
        $subscription->start_date = $startDate;
        $subscription->end_date = $endDate;
        $subscription->save();
        
        // Here you would typically handle payment processing
        // For now, we'll just simulate a successful payment
        $subscription->status = Subscription::ACTIVE_STATUS;
        $subscription->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription renewed successfully',
            'data' => $this->formatSubscription($subscription, $user)
        ]);
    }

    /**
     * Cancel a subscription
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        $user = Auth::user();
        
        // Check if the user owns this subscription
        if ($subscription->subscriber_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to cancel this subscription'
            ], 403);
        }
        
        // Cancel the subscription
        $subscription->status = Subscription::CANCELED_STATUS;
        $subscription->cancel_date = Carbon::now();
        $subscription->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Subscription cancelled successfully',
            'data' => $this->formatSubscription($subscription, $user)
        ]);
    }

    /**
     * Format a subscription for API response
     */
    private function formatSubscription(Subscription $subscription, User $user)
    {
        // Load the creator and tier if not already loaded
        if (!$subscription->relationLoaded('creator') || !$subscription->relationLoaded('tier')) {
            $subscription->load(['creator', 'tier']);
        }
        
        // Format the end date in a readable format
        $endDate = Carbon::parse($subscription->end_date)->format('l, M j, Y');
        
        // Check if the creator is being followed by the user
        $isFollowing = $user->isFollowing($subscription->creator);
        
        // Determine subscription status based on dates and stored status
        $status = $this->determineSubscriptionStatus($subscription);
        
        // Get tier information
        $tierName = $subscription->tier ? $subscription->tier->title : 'Subscription';
        $tierColor = $subscription->tier ? $subscription->tier->color_code : null;
        $tierBenefits = $subscription->tier ? $subscription->tier->subscription_benefits : [];
        
        // Format billing cycle based on duration
        $billingCycle = $this->formatBillingCycle($subscription->duration);
        
        return [
            'id' => $subscription->id,
            'creator' => [
                'id' => $subscription->creator->id,
                'name' => $subscription->creator->name,
                'username' => '@' . $subscription->creator->username,
                'avatar' => $subscription->creator->avatar ?? '/placeholder.svg',
                'verified' => false, // Default to false since this field doesn't exist
                'blocked' => false, // We'll implement this later if needed
                'following' => $isFollowing
            ],
            'tier' => $tierName,
            'tierColor' => $tierColor,
            'tierBenefits' => $tierBenefits,
            'status' => $status,
            'billingCycle' => $billingCycle,
            'totalMonths' => $subscription->duration, // Use duration as total months subscribed
            'price' => $subscription->amount,
            'expiredDate' => $endDate
        ];
    }

    /**
     * Format billing cycle based on duration
     */
    private function formatBillingCycle($duration)
    {
        switch ($duration) {
            case 1:
                return 'Monthly';
            case 2:
                return '2 Months';
            case 3:
                return '3 Months';
            case 6:
                return '6 Months';
            case 12:
                return 'Annual';
            default:
                return $duration . ' ' . ($duration == 1 ? 'month' : 'months');
        }
    }

    /**
     * Determine the actual status of a subscription based on dates and stored status
     */
    private function determineSubscriptionStatus($subscription)
    {
        $now = Carbon::now();
        $endDate = Carbon::parse($subscription->end_date);
        
        // If the subscription has explicitly been canceled or suspended
        if (in_array($subscription->status, [
            Subscription::CANCELED_STATUS, 
            Subscription::SUSPENDED_STATUS,
            Subscription::FAILED_STATUS
        ])) {
            return $subscription->status;
        }
        
        // If the end date has passed, mark as expired
        if ($endDate->isPast()) {
            return Subscription::EXPIRED_STATUS;
        }
        
        // If the subscription is pending and the start date is in the future
        if ($subscription->status === Subscription::PENDING_STATUS && Carbon::parse($subscription->start_date)->isFuture()) {
            return Subscription::PENDING_STATUS;
        }
        
        // Otherwise, it's active
        return Subscription::ACTIVE_STATUS;
    }

    public function subscribe(Request $request, Tier $tier)
    {
        $user = Auth::user();
        
        // Validate the request
        $request->validate([
            'duration' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
        ]);

        // Create the subscription
        $subscription = Subscription::create([
            'creator_id' => $tier->user_id,
            'subscriber_id' => $user->id,
            'tier_id' => $tier->id,
            'status' => Subscription::PENDING_STATUS,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonths($request->duration),
            'duration' => $request->duration,
            'amount' => $request->amount,
        ]);

        // If there's a tracking link in the session, record the subscription event
        if ($trackingLinkId = session('tracking_link_id')) {
            TrackingLinkEvent::create([
                'tracking_link_id' => $trackingLinkId,
                'event_type' => 'subscription',
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription created successfully',
            'data' => $this->formatSubscription($subscription, $user)
        ], 201);
    }
}

