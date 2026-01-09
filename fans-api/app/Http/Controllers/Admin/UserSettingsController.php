<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class UserSettingsController extends Controller
{
    /**
     * Get all settings for a user
     */
    public function show($id): JsonResponse
    {
        $user = User::findOrFail($id);
        $settings = UserSetting::where('user_id', $user->id)->first();

        if (!$settings) {
            // Return default settings if none exist
            return response()->json([
                'account' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => '',
                    'language' => 'en',
                    'timezone' => 'UTC'
                ],
                'privacyAndSecurity' => [
                    'twoFactorAuth' => $user->two_factor_enabled ?? false,
                    'privateAccount' => false,
                    'showActivity' => true,
                    'pushNotifications' => false
                ],
                'emailNotifications' => [
                    'new_messages' => true,
                    'subscription_purchases' => true,
                    'subscription_renews' => true,
                    'media_purchases' => true,
                    'media_likes' => true,
                    'post_replies' => true,
                    'post_likes' => true,
                    'tips_received' => true,
                    'new_followers' => true
                ],
                'messaging' => [
                    'show_read_receipts' => false,
                    'require_tip_for_messages' => false,
                    'accept_messages_from_followed' => true
                ]
            ]);
        }

        return response()->json($settings->settings);
    }

    /**
     * Update settings for a specific category
     */
    public function update(Request $request, $id, $category): JsonResponse
    {
        $user = User::findOrFail($id);
        $settings = UserSetting::where('user_id', $user->id)->first();

        if (!$settings) {
            $settings = new UserSetting();
            $settings->user_id = $user->id;
            $settings->settings = [
                'account' => [
                    'username' => $user->username,
                    'email' => $user->email,
                    'phone' => '',
                    'language' => 'en',
                    'timezone' => 'UTC'
                ],
                'privacyAndSecurity' => [
                    'twoFactorAuth' => $user->two_factor_enabled ?? false,
                    'privateAccount' => false,
                    'showActivity' => true,
                    'pushNotifications' => false
                ],
                'emailNotifications' => [
                    'new_messages' => true,
                    'subscription_purchases' => true,
                    'subscription_renews' => true,
                    'media_purchases' => true,
                    'media_likes' => true,
                    'post_replies' => true,
                    'post_likes' => true,
                    'tips_received' => true,
                    'new_followers' => true
                ],
                'messaging' => [
                    'show_read_receipts' => false,
                    'require_tip_for_messages' => false,
                    'accept_messages_from_followed' => true
                ]
            ];
        }

        // Validate the category
        if (!in_array($category, ['account', 'privacyAndSecurity', 'emailNotifications', 'messaging'])) {
            return response()->json(['message' => 'Invalid settings category'], 422);
        }

        // Update the specific category
        $currentSettings = $settings->settings;
        $currentSettings[$category] = array_merge($currentSettings[$category] ?? [], $request->all());
        $settings->settings = $currentSettings;

        // Special handling for account settings that affect the user model
        if ($category === 'account') {
            if (isset($request->username) && $request->username !== $user->username) {
                $request->validate([
                    'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)]
                ]);
                $user->username = $request->username;
            }
            if (isset($request->email) && $request->email !== $user->email) {
                $request->validate([
                    'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)]
                ]);
                $user->email = $request->email;
            }
            $user->save();
        }

        // Special handling for privacy settings that affect the user model
        if ($category === 'privacyAndSecurity') {
            if (isset($request->twoFactorAuth) && $request->twoFactorAuth !== $user->two_factor_enabled) {
                $user->two_factor_enabled = $request->twoFactorAuth;
                $user->save();
            }
        }

        $settings->save();

        return response()->json($settings->settings);
    }
} 