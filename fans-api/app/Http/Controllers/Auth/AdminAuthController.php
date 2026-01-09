<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\LoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AdminAuthController extends Controller
{
    public function __construct(
        private LoginService $loginService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $loginData = $this->loginService->login($request);

        if ($loginData['user']->role !== 'admin') { 
            return response()->json([
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        return response()->json($loginData);
    }

    public function logout(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        // Delete the current token
        if ($user) {
            $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        }

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function renewToken(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Delete the current token
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();

        // Create a new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
            'message' => 'Token renewed successfully'
        ]);
    }
} 