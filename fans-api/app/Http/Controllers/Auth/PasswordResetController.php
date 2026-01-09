<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Send password reset link to user's email.
     * Returns a generic success message to prevent email enumeration.
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $user = User::where('email', $request->email)->first();

            if ($user) {
                $token = Str::random(64);

                \DB::table('password_reset_tokens')->updateOrInsert(
                    ['email' => $user->email],
                    [
                        'token' => Hash::make($token),
                        'created_at' => now(),
                    ]
                );

                $resetUrl = config('app.frontend_url') . '/auth?token=' . $token . '&email=' . urlencode($user->email);
                $this->emailService->sendPasswordResetEmail($user, $resetUrl);

                Log::info('Password reset link sent', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            } else {
                Log::info('Password reset requested for non-existent email', [
                    'email' => $request->email,
                ]);
            }

            return response()->json([
                'message' => 'If that email address exists in our system, we will send a password reset link.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset link', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to send password reset link. Please try again later.',
            ], 500);
        }
    }

    /**
     * Reset user password using token.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.exists' => 'We could not find a user with that email address.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        try {
            $resetRecord = \DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'message' => 'Invalid or expired reset token.',
                ], 422);
            }

            $createdAt = \Carbon\Carbon::parse($resetRecord->created_at);
            if ($createdAt->addMinutes(60)->isPast()) {
                \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

                return response()->json([
                    'message' => 'This password reset token has expired. Please request a new one.',
                ], 422);
            }

            if (!Hash::check($request->token, $resetRecord->token)) {
                return response()->json([
                    'message' => 'Invalid reset token.',
                ], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'message' => 'User not found.',
                ], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            Log::info('Password reset successful', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return response()->json([
                'message' => 'Password has been reset successfully. You can now login with your new password.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to reset password', [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to reset password. Please try again later.',
            ], 500);
        }
    }
}
