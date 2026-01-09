<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function requestCode(Request $request)
    {
        $user = Auth::user();
        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        // Invalidate previous codes
        EmailVerificationCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $code = random_int(100000, 999999);
        $expiresAt = now()->addMinutes(10);

        $verification = EmailVerificationCode::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        // Send code via email
        app(EmailService::class)->sendEmailVerificationCode($user, $code);

        return response()->json(['message' => 'Verification code sent.']);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);
        $user = Auth::user();
        $code = $request->input('code');

        $verification = EmailVerificationCode::where('user_id', $user->id)
            ->where('code', $code)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return response()->json(['message' => 'Invalid or expired code.'], 422);
        }

        $verification->used_at = now();
        $verification->save();

        $user->email_verified_at = now();
        $user->save();

        return response()->json(['message' => 'Email verified successfully.']);
    }
} 