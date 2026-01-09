<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\User;
use App\Models\ReferralCodeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReferralController extends Controller
{
    /**
     * Generate a referral link for the authenticated user.
     */
    public function generateLink(Request $request)
    {
        $user = Auth::user();
        $referralCode = $user->generateReferralCode();
        
        // Generate both user and creator referral links
        $userReferralLink = config('app.frontend_url') . '/auth?ref=' . $referralCode;
        $creatorReferralLink = config('app.frontend_url') . '/auth?ref=' . $referralCode;

        // Log the generated links
        Log::info('Generated referral links', [
            'userReferralLink' => $userReferralLink,
            'creatorReferralLink' => $creatorReferralLink,
            'referralCode' => $referralCode,
            'user_id' => $user->id,
        ]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $referralCode,
                'referral_links' => [
                    'user' => $userReferralLink,
                    'creator' => $creatorReferralLink
                ]
            ]
        ]);
    }

    /**
     * Generate a creator-specific referral code.
     */
    public function generateCreatorCode(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role !== 'creator' && $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Only creators can generate creator referral codes'
            ], 403);
        }

        $referralCode = $user->generateReferralCode();
        
        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $referralCode
            ]
        ]);
    }

    /**
     * Update the user's referral code.
     */
    public function updateCode(Request $request)
    {
        Log::info('Referral code update request received', [
            'user_id' => Auth::id(),
            'new_code' => $request->code,
            'type' => $request->type
        ]);

        $validator = Validator::make($request->all(), [
            'code' => 'required|string|min:3|max:20|unique:users,referral_code,' . Auth::id(),
            'type' => 'required|in:user,creator'
        ]);

        if ($validator->fails()) {
            Log::warning('Referral code update validation failed', [
                'user_id' => Auth::id(),
                'errors' => $validator->errors()->toArray()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Check if user has permission to update creator code
        if ($request->type === 'creator' && $user->role !== 'creator' && $user->role !== 'admin') {
            Log::warning('Unauthorized creator code update attempt', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Only creators can update creator referral codes'
            ], 403);
        }

        try {
            // Record the history before updating
            $history = ReferralCodeHistory::create([
                'user_id' => $user->id,
                'old_code' => $user->referral_code,
                'new_code' => $request->code,
                'changed_at' => now()
            ]);

            Log::info('Referral code history created', [
                'history_id' => $history->id,
                'user_id' => $user->id,
                'old_code' => $history->old_code,
                'new_code' => $history->new_code
            ]);

            // Update the user's referral code
            $user->update(['referral_code' => $request->code]);

            Log::info('User referral code updated', [
                'user_id' => $user->id,
                'new_code' => $request->code
            ]);

            // Generate new referral links
            $userReferralLink = config('app.frontend_url') . '/auth?ref=' . $request->code;
            $creatorReferralLink = config('app.frontend_url') . '/auth?ref=' . $request->code;
            
            return response()->json([
                'success' => true,
                'data' => [
                    'referral_code' => $request->code,
                    'referral_links' => [
                        'user' => $userReferralLink,
                        'creator' => $creatorReferralLink
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating referral code', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update referral code'
            ], 500);
        }
    }

    /**
     * Get the authenticated user's referral statistics.
     */
    public function getStatistics()
    {
        $user = Auth::user();
        
        $totalReferrals = $user->referrals()->count();
        $activeReferrals = $user->referrals()->active()->count();
        $totalEarnings = $user->referralEarnings()->sum('amount');
        $pendingEarnings = $user->referralEarnings()->where('referral_earnings.status', 'pending')->sum('amount');
        $paidEarnings = $user->referralEarnings()->where('referral_earnings.status', 'paid')->sum('amount');
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_referrals' => $totalReferrals,
                'active_referrals' => $activeReferrals,
                'total_earnings' => $totalEarnings,
                'pending_earnings' => $pendingEarnings,
                'paid_earnings' => $paidEarnings
            ]
        ]);
    }

    /**
     * Get the authenticated user's referral earnings history.
     */
    public function getEarningsHistory(Request $request)
    {
        $user = Auth::user();
        
        $earnings = $user->referralEarnings()
            ->with(['referral.referred', 'transaction'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $earnings
        ]);
    }

    /**
     * Get detailed earnings breakdown for the authenticated user.
     */
    public function getEarningsBreakdown(Request $request)
    {
        $user = Auth::user();
        
        $earnings = $user->referralEarnings()
            ->with(['referral.referred', 'transaction'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $totalEarnings = $earnings->sum('amount');
        
        return response()->json([
            'success' => true,
            'data' => [
                'data' => $earnings,
                'total_earnings' => $totalEarnings,
                'total_transactions' => $earnings->count()
            ]
        ]);
    }

    /**
     * Get the list of users referred by the authenticated user.
     */
    public function getReferredUsers(Request $request)
    {
        $user = Auth::user();
        
        $referredUsers = $user->referrals()
            ->with(['referred'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'success' => true,
            'data' => $referredUsers
        ]);
    }

    /**
     * Validate a referral code.
     */
    public function validateReferralCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'referral_code' => 'required|string|exists:users,referral_code'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid referral code'
            ], 422);
        }

        $referrer = User::where('referral_code', $request->referral_code)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'referrer' => [
                    'id' => $referrer->id,
                    'name' => $referrer->name,
                    'username' => $referrer->username
                ]
            ]
        ]);
    }
} 