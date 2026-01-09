<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegistrationRequest;
use App\Services\UserRegistrationService;
use App\Services\TrackingLinkActionService;
use App\Models\TrackingLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserRegistrationController extends Controller
{
    public function __construct(
        private UserRegistrationService $registrationService,
        private TrackingLinkActionService $trackingLinkActionService
    ) {}

    public function register(UserRegistrationRequest $request): JsonResponse
    {
        Log::info('Registration request received', [
            'request_data' => $request->all(),
            'validated_data' => $request->validated(),
            'has_referral_code' => $request->has('referral_code'),
            'referral_code' => $request->input('referral_code'),
            'has_tracking_link_id' => $request->has('tracking_link_id'),
            'tracking_link_id' => $request->input('tracking_link_id')
        ]);

        try {
            $user = $this->registrationService->register($request);
            
            Log::info('Registration successful', [
                'user_id' => $user->id,
                'email' => $user->email,
                'username' => $user->username
            ]);

            // Track the signup action if tracking link ID is provided
            if ($request->has('tracking_link_id')) {
                $trackingLink = TrackingLink::find($request->input('tracking_link_id'));
                
                if ($trackingLink) {
                    Log::info('Tracking signup action', [
                        'user_id' => $user->id,
                        'tracking_link_id' => $trackingLink->id
                    ]);

                    $this->trackingLinkActionService->trackAction(
                        $trackingLink,
                        'signup',
                        $user->id,
                        ['method' => 'register'],
                        $request
                    );
                } else {
                    Log::warning('Tracking link not found', [
                        'tracking_link_id' => $request->input('tracking_link_id')
                    ]);
                }
            }

            return response()->json([
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            throw $e;
        }
    }
}