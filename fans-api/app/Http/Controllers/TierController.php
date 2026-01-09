<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTierRequest;
use App\Http\Requests\UpdateTierRequest;
use App\Models\Tier;
use App\Models\User;
use App\Services\PaymentService;
use App\Services\TierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TierController extends Controller
{
    protected $tierService;
    protected $paymentService;

    public function __construct(TierService $tierService, PaymentService $paymentService)
    {
        $this->tierService = $tierService;
        $this->paymentService = $paymentService;
    }

    public function index(): JsonResponse
    {
        $tiers = $this->tierService->getCreatorTiers(Auth::user());
        return response()->json([
            'success' => true,
            'data' => $tiers
        ]);
    }
    
    public function getUserActiveTiers($userId): JsonResponse
    {
        try {
            $user = User::findOrFail($userId);
            $activeTiers = $this->tierService->getUserActiveTiers($user);

            return response()->json([
                'success' => true,
                'data' => $activeTiers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch active tiers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function store(CreateTierRequest $request): JsonResponse
    {
        try {
            $tier = $this->tierService->createTier(Auth::user(), $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tier created successfully',
                'data' => $tier
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create tier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Tier $tier): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $tier
        ]);
    }

    public function update(UpdateTierRequest $request, Tier $tier): JsonResponse
    {
        try {
            $updatedTier = $this->tierService->updateTier($tier, $request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Tier updated successfully',
                'data' => $updatedTier
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tier',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Tier $tier): JsonResponse
    {
        try {
            // Log the incoming request details
            Log::info('Delete tier request received', [
                'tier_id' => $tier->id ?? null,
                'auth_user_id' => Auth::id(),
                'request_path' => request()->path(),
                'request_method' => request()->method(),
                'tier_exists' => $tier->exists,
                'tier_data' => $tier->toArray()
            ]);

            // Ensure tier exists in database
            if (!$tier->exists) {
                Log::warning('Attempt to delete non-existent tier', [
                    'tier_id' => $tier->id,
                    'auth_user_id' => Auth::id()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Tier not found'
                ], 404);
            }

            // Verify tier belongs to authenticated user
            if ($tier->user_id !== Auth::id()) {
                Log::warning('Unauthorized tier deletion attempt', [
                    'tier_id' => $tier->id,
                    'tier_user_id' => $tier->user_id,
                    'auth_user_id' => Auth::id()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to delete this tier'
                ], 403);
            }

            // Explicitly load the tier to ensure we have fresh data
            $tier = Tier::findOrFail($tier->id);

            $result = $this->tierService->deleteTier($tier);
            
            Log::info('Tier deletion completed in controller', [
                'tier_id' => $tier->id,
                'auth_user_id' => Auth::id(),
                'result' => $result
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tier deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete tier in controller', [
                'tier_id' => $tier->id ?? null,
                'auth_user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $statusCode = $e->getMessage() === 'Cannot delete tier with existing subscribers' ? 422 : 500;
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $statusCode);
        }
    }

    public function getAvailablePlans(Tier $tier): JsonResponse
    {
        $plans = $this->tierService->getAvailablePlans($tier);
        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    public function enable(Request $request, $tierId): JsonResponse
    {
        try {
            $authUserId = (string) Auth::id();
            
            // Log the incoming request data
            Log::info('Enable tier request data:', [
                'tierId' => $tierId,
                'auth_user_id' => $authUserId,
                'request_data' => $request->all()
            ]);
            
            // Try to find the tier
            $tier = Tier::find($tierId);
            
            if (!$tier) {
                Log::warning('Tier not found', [
                    'tier_id' => $tierId,
                    'auth_user_id' => $authUserId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Tier not found'
                ], 404);
            }

            // Check if the user owns this tier
            if ($tier->user_id !== Auth::id()) {
                Log::warning('Unauthorized attempt to enable tier', [
                    'tier_id' => $tier->id,
                    'auth_user_id' => $authUserId,
                    'tier_user_id' => $tier->user_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to enable this tier'
                ], 403);
            }

            try {
                $enabledTier = $this->tierService->enableTier($tier);
                
                Log::info('Successfully enabled tier in controller', [
                    'tier_id' => $tier->id,
                    'user_id' => $authUserId,
                    'enabled_tier_data' => $enabledTier->toArray()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Tier enabled successfully',
                    'data' => $enabledTier
                ]);
            } catch (\Exception $e) {
                Log::error('Error in tierService.enableTier', [
                    'tier_id' => $tier->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to enable tier in controller', [
                'tier_id' => $tierId ?? null,
                'user_id' => Auth::id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to enable tier: ' . $e->getMessage()
            ], 500);
        }
    }

    public function disable(Request $request, $tierId): JsonResponse
    {
        try {
            // Log at the very start of the method
            Log::error('DEBUG: Disable tier endpoint hit', [
                'tierId' => $tierId,
                'auth_check' => Auth::check(),
                'auth_id' => Auth::id(),
                'request_method' => $request->method(),
                'request_path' => $request->path(),
                'request_all' => $request->all(),
                'request_headers' => $request->headers->all()
            ]);

            if (!Auth::check()) {
                Log::error('User not authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $authUserId = (string) Auth::id();
            
            // Try to find the tier
            $tier = Tier::find($tierId);
            
            if (!$tier) {
                Log::warning('Tier not found', [
                    'tier_id' => $tierId,
                    'auth_user_id' => $authUserId
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Tier not found'
                ], 404);
            }

            $tierUserId = (string) $tier->user_id;

            Log::info('Attempting to disable tier in controller', [
                'tier_id' => $tier->id,
                'auth_user_id' => $authUserId,
                'tier_user_id' => $tierUserId,
                'auth_user' => Auth::user(),
                'tier_data' => $tier->toArray()
            ]);

            // Check if the user owns this tier
            if ($tier->user_id !== Auth::id()) {
                Log::warning('Unauthorized attempt to disable tier', [
                    'tier_id' => $tier->id,
                    'auth_user_id' => $authUserId,
                    'tier_user_id' => $tierUserId,
                    'comparison_result' => [
                        'tier_user_id' => $tier->user_id,
                        'auth_id' => Auth::id(),
                        'values_match' => $tier->user_id === Auth::id()
                    ]
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to disable this tier'
                ], 403);
            }

            try {
                $disabledTier = $this->tierService->disableTier($tier);
                
                Log::info('Successfully disabled tier in controller', [
                    'tier_id' => $tier->id,
                    'user_id' => $authUserId,
                    'disabled_tier_data' => $disabledTier->toArray()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Tier disabled successfully',
                    'data' => $disabledTier
                ]);
            } catch (\Exception $e) {
                Log::error('Error in tierService.disableTier', [
                    'tier_id' => $tier->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to disable tier in controller', [
                'tier_id' => $tierId ?? null,
                'user_id' => Auth::id() ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disable tier: ' . $e->getMessage()
            ], 500);
        }
    }

    public function subscriberCount(Tier $tier): JsonResponse
    {
        $count = $this->tierService->subscriberCount($tier);
        return response()->json([
            'success' => true,
            'data' => ['count' => $count]
        ]);
    }

    public function subscribe(Request $request, $tierId)
    {
        Log::info('Subscription request received', [
            'tier_id' => $tierId,
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        try {
            $validatedData = $request->validate([
                'duration' => 'required|integer|in:1,3,6,12',
                'tracking_link_id' => 'nullable|exists:tracking_links,id'
            ]);

            Log::info('Subscription request validated', [
                'tier_id' => $tierId,
                'duration' => $validatedData['duration'],
                'tracking_link_id' => $validatedData['tracking_link_id'] ?? null
            ]);

            $tier = Tier::findOrFail($tierId);
            $user = Auth::user();

            // Store tracking link ID in session if provided
            if (isset($validatedData['tracking_link_id'])) {
                Log::info('Storing tracking link ID in session', [
                    'tracking_link_id' => $validatedData['tracking_link_id'],
                    'session_id' => session()->getId()
                ]);
                session(['tracking_link_id' => $validatedData['tracking_link_id']]);
            }

            // Calculate amount based on duration
            $amount = $tier->price * $validatedData['duration'];
            $transactionType = $this->getTransactionType($validatedData['duration']);

            Log::info('Processing payment for subscription', [
                'tier_id' => $tier->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'duration' => $validatedData['duration'],
                'transaction_type' => $transactionType,
                'tracking_link_id' => $validatedData['tracking_link_id'] ?? null,
                'session_tracking_link_id' => session('tracking_link_id')
            ]);

            $paymentResult = $this->paymentService->processPayment(
                $user,
                $amount,
                $transactionType,
                $tier->id,
                $tier->user_id
            );

            Log::info('Payment processing result', [
                'tier_id' => $tier->id,
                'user_id' => $user->id,
                'payment_result' => $paymentResult,
                'redirect_required' => $paymentResult['redirect_required'] ?? null,
                'tracking_link_id' => session('tracking_link_id')
            ]);

            // Check if we're in test mode and payment was processed directly
            if (isset($paymentResult['redirect_required']) && $paymentResult['redirect_required'] === false) {
                Log::info('Subscription processed successfully (direct payment)', [
                    'tier_id' => $tier->id,
                    'user_id' => $user->id,
                    'transaction_id' => $paymentResult['transaction_id'],
                    'tracking_link_id' => session('tracking_link_id')
                ]);

                // Clear tracking link from session after successful payment
                if (session('tracking_link_id')) {
                    Log::info('Clearing tracking link from session after successful payment', [
                        'tracking_link_id' => session('tracking_link_id')
                    ]);
                    session()->forget('tracking_link_id');
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Subscription processed successfully',
                    'data' => [
                        'transaction_id' => $paymentResult['transaction_id'],
                        'amount' => $amount,
                        'currency' => 'USD',
                        'tier_id' => $tier->id,
                        'duration' => $validatedData['duration']
                    ]
                ], 200);
            }
            // Normal flow with payment URL
            Log::info('Subscription processing initiated (redirect required)', [
                'tier_id' => $tier->id,
                'user_id' => $user->id,
                'transaction_id' => $paymentResult['transaction_id'],
                'payment_url' => $paymentResult['payment_url']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription processing initiated successfully',
                'data' => [
                    'payment_url' => $paymentResult['payment_url'],
                    'transaction_id' => $paymentResult['transaction_id'],
                    'amount' => $amount,
                    'currency' => 'USD',
                    'tier_id' => $tier->id,
                    'duration' => $validatedData['duration']
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error processing subscription', [
                'exception' => $e,
                'user_id' => Auth::id(),
                'tier_id' => $tierId,
                'duration' => $request->duration,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Subscription processing error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getTransactionType(int $duration): string
    {
        switch ($duration) {
            case 1:
                return 'one_month_subscription';
            case 2:
                return 'two_months_subscription';
            case 3:
                return 'three_months_subscription';
            case 6:
                return 'six_months_subscription';
            case 12:
                return 'yearly_subscription';
            default:
                throw new \Exception('Invalid subscription duration');
        }
    }
}