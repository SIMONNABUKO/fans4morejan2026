<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SettingCategory;
use Illuminate\Http\Request;
use App\Http\Resources\UserSettingResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class UserSettingController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        // Get all settings grouped by category
        $settings = [
            'account' => $this->getCategorySettings($user, 'account'),
            'privacyAndSecurity' => $this->getCategorySettings($user, 'privacyAndSecurity'),
            'emailNotifications' => $this->getCategorySettings($user, 'emailNotifications'),
            'messaging' => $this->getCategorySettings($user, 'messaging'),
            'creator' => $this->getCategorySettings($user, 'creator'),
        ];

        return response()->json($settings);
    }

    public function show(Request $request, $category)
    {
        $user = $request->user();
        $settings = $this->getCategorySettings($user, $category);

        return response()->json($settings);
    }

    public function update(Request $request, $category)
    {
        $user = $request->user();

        Log::debug('UserSettingController@update called', [
            'user_id' => $user->id,
            'category' => $category,
            'request_data' => $request->all()
        ]);

        // Define validation rules based on category
        $rules = $this->getValidationRules($category);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            Log::debug('UserSettingController@update validation failed', [
                'user_id' => $user->id,
                'errors' => $validator->errors()
            ]);
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        Log::debug('UserSettingController@update validated data', [
            'user_id' => $user->id,
            'validated_data' => $validatedData
        ]);

        // Update settings for the category
        foreach ($validatedData as $key => $value) {
            Log::debug('UserSettingController@update calling setSetting', [
                'user_id' => $user->id,
                'category' => $category,
                'key' => $key,
                'value' => $value
            ]);
            $user->setSetting($category, $key, $value);
        }

        return response()->json($this->getCategorySettings($user, $category));
    }

    protected function getValidationRules($category)
    {
        switch ($category) {
            case 'account':
                return [
                    'username' => 'sometimes|string|max:255',
                    'email' => 'sometimes|email|max:255',
                    'phone' => 'sometimes|nullable|string|max:20',
                    'language' => 'sometimes|string|in:en,es,fr',
                    'timezone' => 'sometimes|string|timezone',
                ];

            case 'privacyAndSecurity':
                return [
                    'twoFactorAuth' => 'sometimes|boolean',
                    'privateAccount' => 'sometimes|boolean',
                    'showActivity' => 'sometimes|boolean',
                    'pushNotifications' => 'sometimes|boolean',
                    'sensitiveContentLevel' => 'sometimes|integer|min:0|max:3',
                    'messageBlurLevel' => 'sometimes|integer|min:0|max:3',
                    'appear_in_suggestions' => 'sometimes|boolean',
                    'enable_preview_discovery' => 'sometimes|boolean',
                ];

            case 'emailNotifications':
                return [
                    'new_messages' => 'sometimes|boolean',
                    'subscription_purchases' => 'sometimes|boolean',
                    'subscription_renews' => 'sometimes|boolean',
                    'media_purchases' => 'sometimes|boolean',
                    'media_likes' => 'sometimes|boolean',
                    'post_replies' => 'sometimes|boolean',
                    'post_likes' => 'sometimes|boolean',
                    'tips_received' => 'sometimes|boolean',
                    'new_followers' => 'sometimes|boolean',
                ];
            case 'messaging':
                return [
                    'show_read_receipts' => 'sometimes|boolean',
                    'require_tip_for_messages' => 'sometimes|boolean',
                    'accept_messages_from_followed' => 'sometimes|boolean',
                ];
            case 'creator':
                return [
                    'follow_button_enabled' => 'sometimes|boolean',
                    'require_payment_method' => 'sometimes|boolean',
                ];
            default:
                return [];
        }
    }

    /**
     * Get messaging settings for a specific user
     */
    public function getUserMessagingSettings(Request $request, $userId)
    {
        // Find the user
        $user = User::findOrFail($userId);

        // Get messaging settings with default values
        $settings = [
            'show_read_receipts' => $user->getSetting('messaging', 'show_read_receipts', false),
            'require_tip_for_messages' => $user->getSetting('messaging', 'require_tip_for_messages', false),
            'accept_messages_from_followed' => $user->getSetting('messaging', 'accept_messages_from_followed', true),
        ];

        return response()->json($settings);
    }

    /**
     * Check if a user is following another user
     */
    public function checkFollowerStatus(Request $request, $userId, $followerId)
    {
        // Find both users
        $user = User::findOrFail($userId);
        $follower = User::findOrFail($followerId);

        // Check if follower is following user
        $isFollowing = $user->followers()->where('follower_id', $follower->id)->exists();

        return response()->json([
            'is_following' => $isFollowing
        ]);
    }

    /**
     * Check mutual follow status between two users
     */
    public function checkMutualFollowStatus(Request $request, $userId, $otherUserId)
    {
        // Find both users
        $user = User::findOrFail($userId);
        $otherUser = User::findOrFail($otherUserId);

        // Check if user is following other user
        $userFollowsOther = $user->following()->where('followed_id', $otherUser->id)->exists();
        
        // Check if other user is following user
        $otherFollowsUser = $otherUser->following()->where('followed_id', $user->id)->exists();

        return response()->json([
            'user_follows_other' => $userFollowsOther,
            'other_follows_user' => $otherFollowsUser,
            'mutual_follow' => $userFollowsOther && $otherFollowsUser
        ]);
    }

    protected function getCategorySettings(User $user, $category)
    {
        $defaultSettings = $this->getDefaultSettings($category);
        $userSettings = [];

        foreach ($defaultSettings as $key => $defaultValue) {
            $userSettings[$key] = $user->getSetting($category, $key, $defaultValue);
        }

        return $userSettings;
    }

    protected function getDefaultSettings($category)
    {
        switch ($category) {
            case 'account':
                return [
                    'username' => '',
                    'email' => '',
                    'phone' => '',
                    'language' => 'en',
                    'timezone' => 'UTC',
                ];

            case 'privacyAndSecurity':
                return [
                    'twoFactorAuth' => false,
                    'privateAccount' => false,
                    'showActivity' => true,
                    'pushNotifications' => false,
                    'sensitiveContentLevel' => 0,
                    'messageBlurLevel' => 0,
                    'appear_in_suggestions' => true,
                    'enable_preview_discovery' => true,
                ];

            case 'emailNotifications':
                return [
                    'new_messages' => true,
                    'subscription_purchases' => true,
                    'subscription_renews' => true,
                    'media_purchases' => true,
                    'media_likes' => true,
                    'post_replies' => true,
                    'post_likes' => true,
                    'tips_received' => true,
                    'new_followers' => true,
                ];

            case 'messaging':
                return [
                    'show_read_receipts' => false,
                    'require_tip_for_messages' => false,
                    'accept_messages_from_followed' => true,
                ];

            case 'creator':
                return [
                    'follow_button_enabled' => true,
                    'require_payment_method' => false,
                ];

            default:
                return [];
        }
    }

    /**
     * Admin endpoint to view a user's settings
     */
    public function adminViewUserSettings(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        // Get all settings grouped by category
        $settings = [
            'account' => $this->getCategorySettings($user, 'account'),
            'privacyAndSecurity' => $this->getCategorySettings($user, 'privacyAndSecurity'),
            'emailNotifications' => $this->getCategorySettings($user, 'emailNotifications'),
            'messaging' => $this->getCategorySettings($user, 'messaging'),
            'creator' => $this->getCategorySettings($user, 'creator'),
        ];

        return response()->json($settings);
    }

    /**
     * Admin endpoint to update a user's settings
     */
    public function adminUpdateUserSettings(Request $request, $userId, $category)
    {
        $user = User::findOrFail($userId);

        // Define validation rules based on category
        $rules = $this->getValidationRules($category);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // Update settings for the category
        foreach ($validatedData as $key => $value) {
            $user->setSetting($category, $key, $value);
        }

        return response()->json($this->getCategorySettings($user, $category));
    }
}
