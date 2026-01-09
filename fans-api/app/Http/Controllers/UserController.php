<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Services\CoverPhotoService;
use App\Services\ContentVisibilityService;
use App\Services\PermissionService;
use App\Services\MediaStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Referral;
use App\Models\ReferralCodeHistory;
use App\Services\ReferralCodeService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $coverPhotoService;
    protected $referralCodeService;
    protected $contentVisibilityService;
    protected $permissionService;

    public function __construct(
        CoverPhotoService $coverPhotoService,
        ReferralCodeService $referralCodeService,
        ContentVisibilityService $contentVisibilityService,
        PermissionService $permissionService
    ) {
        $this->coverPhotoService = $coverPhotoService;
        $this->referralCodeService = $referralCodeService;
        $this->contentVisibilityService = $contentVisibilityService;
        $this->permissionService = $permissionService;
    }

    public function me(): JsonResponse
    {
        $user = Auth::user()->load([
            'posts' => function ($query) {
                $query->with('media.previews');
                $query->with('user');
            },
            'media'
        ]);
        return response()->json(new UserResource($user));
    }


    public function search(Request $request): JsonResponse
    {
        $query = $request->get('query', '');
        $limit = $request->get('limit', 10);
        $user = $request->user();

        Log::info('🔍 UserController::search called', [
            'query' => $query,
            'limit' => $limit,
            'user_authenticated' => Auth::check(),
            'user_id' => $user ? $user->id : 'NULL',
            'user_country_code' => $user ? $user->country_code : 'NULL',
            'user_region_name' => $user ? $user->region_name : 'NULL',
            'user_city_name' => $user ? $user->city_name : 'NULL'
        ]);

        $usersQuery = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('username', 'LIKE', "%{$query}%")
            ->orWhere('handle', 'LIKE', "%{$query}%")
            ->orWhere('bio', 'LIKE', "%{$query}%");

        // Apply location-based filtering using user's stored location data
        $usersQuery = $this->contentVisibilityService->filterContentByUserLocation(
            $usersQuery, 
            $user
        );

        $users = $usersQuery->limit($limit)->get();

        Log::info('🔍 Search results after filtering', [
            'query' => $query,
            'results_count' => $users->count(),
            'user_ids' => $users->pluck('id')->toArray()
        ]);

        return response()->json(UserResource::collection($users));
    }

    /**
     * Temporary method to update user location for testing geoblocking
     */
    public function updateLocationForTesting(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Update user location to Nairobi for testing
        $user->update([
            'country_code' => 'KE',
            'country_name' => 'Kenya',
            'region_name' => 'Nairobi County',
            'city_name' => 'Nairobi',
            'location_updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Location updated for testing',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Temporary method to check user data for debugging
     */
    public function checkUserData(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'user_id' => $user->id,
            'username' => $user->username,
            'country_code' => $user->country_code,
            'country_name' => $user->country_name,
            'region_name' => $user->region_name,
            'city_name' => $user->city_name,
            'location_updated_at' => $user->location_updated_at,
            'database' => config('database.connections.mysql.database'),
            'host' => config('database.connections.mysql.host'),
        ]);
    }

    public function getDashboard(): JsonResponse
    {
        $user = Auth::user();
        
        // Get basic user dashboard data
        $dashboardData = [
            'user' => new UserResource($user),
            'stats' => [
                'total_posts' => $user->posts()->count(),
                'total_media' => $user->media()->count(),
                'total_followers' => $user->followers()->count(),
                'total_following' => $user->following()->count(),
            ]
        ];

        return response()->json($dashboardData);
    }
    public function show($id): JsonResponse
    {
        $user = User::with(['posts', 'media'])->findOrFail($id);

        // Generate cover photo if it doesn't exist
        if (!$user->cover_photo) {
            $user->cover_photo = $this->coverPhotoService->generateAndStoreCoverPhoto($user);
            $user->save();
        }

        return response()->json(new UserResource($user));
    }

    public function update(Request $request): JsonResponse
    {
        $user = Auth::user();
        $cacheKey = "user_update_{$user->id}";
        
        Log::info('User update request received', [
            'user_id' => $user->id,
            'request_id' => $request->header('X-Request-ID', uniqid()),
            'has_referral_code' => $request->has('referral_code'),
            'current_code' => $user->referral_code,
            'new_code' => $request->referral_code ?? 'not provided'
        ]);

        // Check for duplicate request
        if (Cache::has($cacheKey)) {
            Log::warning('Duplicate user update request detected', [
                'user_id' => $user->id,
                'cache_key' => $cacheKey
            ]);
            return response()->json(['message' => 'Request already in progress'], 429);
        }

        // Set cache lock for 30 seconds
        Cache::put($cacheKey, true, 30);

        try {
            // Validate request data
            $validatedData = $request->validate([
                'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
                'display_name' => 'sometimes|string|max:255',
                'bio' => 'sometimes|string|max:1000',
                'bio_color' => 'sometimes|string|max:7',
                'bio_font' => 'sometimes|string|max:255',
                'location' => 'sometimes|string|max:255',
                'website' => 'sometimes|url|max:255',
                'referral_code' => 'sometimes|string|max:255|unique:users,referral_code,' . $user->id,
                'cover_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
                'profile_photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle file uploads first (outside any database operations)
            if ($request->hasFile('cover_photo')) {
                try {
                    Log::info('Processing cover photo upload', ['user_id' => $user->id]);
                    
                    $coverPhotoPath = app(MediaStorageService::class)
                        ->storeUploadedFile('cover_photos', $request->file('cover_photo'));
                    $validatedData['cover_photo'] = $coverPhotoPath;
                    
                    // Delete old cover photo after successful upload
                    if ($user->cover_photo) {
                        app(MediaStorageService::class)->delete($user->getRawOriginal('cover_photo'));
                    }
                    
                    Log::info('Cover photo uploaded successfully', [
                        'user_id' => $user->id,
                        'cover_photo_path' => $coverPhotoPath
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error uploading cover photo', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                    Cache::forget($cacheKey);
                    return response()->json(['message' => 'Error uploading cover photo'], 500);
                }
            }

            if ($request->hasFile('profile_photo')) {
                try {
                    Log::info('Processing profile photo upload', ['user_id' => $user->id]);
                    
                    $profilePhotoPath = app(MediaStorageService::class)
                        ->storeUploadedFile('profile_photos', $request->file('profile_photo'));
                    $validatedData['profile_photo'] = $profilePhotoPath;
                    
                    // Delete old profile photo after successful upload
                    if ($user->profile_photo) {
                        app(MediaStorageService::class)->delete($user->getRawOriginal('profile_photo'));
                    }
                    
                    Log::info('Profile photo uploaded successfully', [
                        'user_id' => $user->id,
                        'profile_photo_path' => $profilePhotoPath
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error uploading profile photo', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                    Cache::forget($cacheKey);
                    return response()->json(['message' => 'Error uploading profile photo'], 500);
                }
            }

            // Handle referral code update separately
            if (isset($validatedData['referral_code'])) {
                try {
                    // Record the history before updating
                    ReferralCodeHistory::create([
                        'user_id' => $user->id,
                        'old_code' => $user->referral_code,
                        'new_code' => $validatedData['referral_code'],
                        'changed_at' => now()
                    ]);

                    Log::info('Referral code updated successfully', [
                        'user_id' => $user->id,
                        'new_code' => $validatedData['referral_code']
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error updating referral code', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                    Cache::forget($cacheKey);
                    return response()->json(['message' => 'Error updating referral code'], 500);
                }
            }

            // Database update with retry logic
            $maxRetries = 3;
            $retryCount = 0;
            $lastException = null;

            while ($retryCount < $maxRetries) {
                try {
                    // Use a fresh user instance to avoid any cached state
                    $freshUser = User::find($user->id);
                    if (!$freshUser) {
                        throw new \Exception('User not found');
                    }

                    // Update user attributes
                    foreach ($validatedData as $key => $value) {
                        $freshUser->$key = $value;
                    }
                    
                    $freshUser->save();
                    
                    Log::info('User updated successfully', [
                        'user_id' => $freshUser->id,
                        'updated_fields' => array_keys($validatedData),
                        'retry_count' => $retryCount
                    ]);
                    
                    // Reload relationships and return response
                    $freshUser->refresh();
                    $freshUser->load(['posts', 'media']);
                    
                    Cache::forget($cacheKey);
                    return response()->json(new UserResource($freshUser));
                    
                } catch (\Exception $e) {
                    $lastException = $e;
                    $retryCount++;
                    
                    Log::warning('Database update attempt failed', [
                        'user_id' => $user->id,
                        'retry_count' => $retryCount,
                        'max_retries' => $maxRetries,
                        'error' => $e->getMessage()
                    ]);
                    
                    // If it's a lock timeout and we have retries left, wait and try again
                    if (strpos($e->getMessage(), 'Lock wait timeout exceeded') !== false && $retryCount < $maxRetries) {
                        // Wait for a short time before retrying (exponential backoff)
                        $waitTime = pow(2, $retryCount) * 1000000; // microseconds
                        usleep($waitTime);
                        continue;
                    }
                    
                    // If it's not a lock timeout or we're out of retries, break
                    break;
                }
            }

            // If we get here, all retries failed
            Log::error('All database update attempts failed', [
                'user_id' => $user->id,
                'retry_count' => $retryCount,
                'final_error' => $lastException ? $lastException->getMessage() : 'Unknown error',
                'trace' => $lastException ? $lastException->getTraceAsString() : ''
            ]);
            
            Cache::forget($cacheKey);
            return response()->json(['message' => 'Error updating user profile after multiple attempts'], 500);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Cache::forget($cacheKey);
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Unexpected error during user update', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Cache::forget($cacheKey);
            return response()->json(['message' => 'An unexpected error occurred'], 500);
        }
    }

    public function getByUsername($username): JsonResponse
    {
        Log::info('👤 UserController::getByUsername called', [
            'username' => $username,
            'user_authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'request_user' => request()->user() ? request()->user()->id : 'NULL',
            'bearer_token_present' => request()->bearerToken() ? 'YES' : 'NO'
        ]);

        $user = User::with(['posts.user', 'posts.media', 'posts.media.previews', 'posts.stats', 'posts.permissionSets.permissions'])
            ->where('username', $username)
            ->orWhere('handle', $username)
            ->first();

        if (!$user) {
            Log::warning('❌ User not found by username/handle', ['searched_for' => $username]);
            return response()->json(['error' => 'user_not_found'], 404);
        }

        Log::info('✅ User found by username/handle', [
            'searched_for' => $username,
            'found_user_id' => $user->id,
            'found_username' => $user->username,
            'found_handle' => $user->handle
        ]);

        // Check if viewer is in a blocked location
        $authUser = null;
        if (Auth::check()) {
            $authUser = Auth::user();
            Log::info('🔐 User authenticated via Auth::check()', ['user_id' => $authUser->id]);
        } else {
            // Try to authenticate using the token if present
            $token = request()->bearerToken();
            if ($token) {
                // Use Sanctum's token authentication
                $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
                if ($personalAccessToken) {
                    $authenticatedUser = $personalAccessToken->tokenable;
                    if ($authenticatedUser) {
                        $authUser = $authenticatedUser;
                        Log::info('🔐 User authenticated via token', ['user_id' => $authUser->id]);
                    } else {
                        Log::info('❌ Token found but no user associated', ['token_id' => $personalAccessToken->id]);
                    }
                } else {
                    Log::info('❌ Token authentication failed', ['token_present' => 'YES', 'token_length' => strlen($token)]);
                }
            } else {
                Log::info('❌ No bearer token present');
            }
        }

        // Set Auth facade for UserResource to access authenticated user
        if ($authUser) {
            Auth::setUser($authUser);
        }

        // Check geoblocking if viewer is authenticated
        if ($authUser) {
            $userLocationService = app(\App\Services\UserLocationService::class);
            if ($userLocationService->isUserInBlockedLocation($authUser, $user)) {
                Log::info('🚫 User blocked by location', [
                    'viewer_id' => $authUser->id,
                    'target_user_id' => $user->id,
                    'viewer_location' => $authUser->country_code . ', ' . $authUser->region_name . ', ' . $authUser->city_name
                ]);
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'This profile is not available in your location',
                    'code' => 'LOCATION_BLOCKED'
                ], 403);
            }
        }

        // Always check permissions, even for unauthenticated users
        foreach ($user->posts as $post) {
            // Check permission first
            $userHasPermission = $authUser ? $this->permissionService->checkPermission($post, $authUser) : false;

            // Get required permissions (always get them, even for unauthenticated users)
            if ($authUser) {
                $requiredPermissions = $this->permissionService->getRequiredPermissions($post, $authUser);
            } else {
                // For unauthenticated users, create a simple array of required permissions without checking if they're met
                $requiredPermissions = $this->getRequiredPermissionsForUnauthenticatedUser($post);
            }

            // Set the properties
            $post->user_has_permission = $userHasPermission;
            $post->required_permissions = $requiredPermissions;
        }

        return response()->json(new UserResource($user));
    }

    /**
     * Get required permissions for unauthenticated users
     * This creates a simple array of permissions without checking if they're met
     */
    private function getRequiredPermissionsForUnauthenticatedUser($post): array
    {
        $requiredPermissions = [];

        // If the content has no media or no permission sets, no permissions are required
        if ($post->media->isEmpty() || $post->permissionSets->isEmpty()) {
            return $requiredPermissions;
        }

        // Group permissions by permission set
        foreach ($post->permissionSets as $permissionSet) {
            $permissionGroup = [];

            foreach ($permissionSet->permissions as $permission) {
                // For unauthenticated users, all permissions are not met
                $permissionGroup[] = [
                    'type' => $permission->type,
                    'value' => $permission->value,
                    'is_met' => false
                ];
            }

            // Only add the group if it has permissions
            if (!empty($permissionGroup)) {
                $requiredPermissions[] = $permissionGroup;
            }
        }

        return $requiredPermissions;
    }

    public function updateDisplayName(Request $request): JsonResponse
    {
        $request->validate([
            'display_name' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $user->display_name = $request->display_name;
        $user->save();

        return response()->json(['message' => 'Display name updated successfully', 'display_name' => $user->display_name]);
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore(Auth::id())],
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->email = $request->email;
        $user->email_verified_at = null; // Require re-verification
        $user->save();

        // TODO: Send email verification

        return response()->json(['message' => 'Email updated successfully. Please verify your new email address.']);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function toggle2FA(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->has_2fa = !$user->has_2fa;
        $user->save();

        $message = $user->has_2fa ? '2FA enabled successfully' : '2FA disabled successfully';
        return response()->json(['message' => $message, 'has_2fa' => $user->has_2fa]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        // Perform any necessary cleanup before deleting the user
        // For example, you might want to delete associated data or perform soft delete

        // $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Get total earnings from a specific user (subscriptions, purchases, tips)
     */
    public function getUserEarnings($userId): JsonResponse
    {
        $creator = Auth::user();
        $viewedUser = User::findOrFail($userId);

        try {
            // Get all subscriptions from this user
            // Sum from transactions table instead since subscription.amount might be NULL
            $subscriptions = DB::table('transactions')
                ->where('sender_id', $viewedUser->id)
                ->where('receiver_id', $creator->id)
                ->where('status', 'approved')
                ->whereIn('type', [
                    'one_month_subscription',
                    'three_months_subscription',
                    'six_months_subscription',
                    'yearly_subscription'
                ])
                ->sum('amount');

            // Get all purchases (posts/media) from this user
            $purchases = DB::table('transactions')
                ->where('sender_id', $viewedUser->id)
                ->where('receiver_id', $creator->id)
                ->where('status', 'approved')
                ->whereIn('type', [
                    'media_purchase',
                    'post_purchase',
                    'video_purchase',
                    'album_purchase'
                ])
                ->sum('amount');

            // Get all tips from this user
            $tips = DB::table('transactions')
                ->where('sender_id', $viewedUser->id)
                ->where('receiver_id', $creator->id)
                ->where('status', 'approved')
                ->where('type', 'tip')
                ->sum('amount');

            // Get total spent by this user on our content
            $totalSpent = $subscriptions + $purchases + $tips;

            // Get subscription details - get amounts from transactions by matching subscription criteria
            $subscriptionDetails = DB::table('subscriptions')
                ->join('tiers', 'subscriptions.tier_id', '=', 'tiers.id')
                ->where('subscriptions.subscriber_id', $viewedUser->id)
                ->where('subscriptions.creator_id', $creator->id)
                ->where('subscriptions.status', 'completed')
                ->select(
                    'subscriptions.*', 
                    'tiers.title as tier_name', 
                    'tiers.base_price as tier_price'
                )
                ->get();
            
            // For each subscription, get the matching transaction amount
            foreach ($subscriptionDetails as $subscription) {
                $transaction = DB::table('transactions')
                    ->where('sender_id', $viewedUser->id)
                    ->where('receiver_id', $creator->id)
                    ->where('tier_id', $subscription->tier_id)
                    ->whereIn('type', [
                        'one_month_subscription',
                        'three_months_subscription',
                        'six_months_subscription',
                        'yearly_subscription'
                    ])
                    ->where('status', 'approved')
                    ->where('created_at', '>=', $subscription->start_date)
                    ->where('created_at', '<=', $subscription->end_date)
                    ->first();
                
                $subscription->actual_amount = $transaction ? $transaction->amount : ($subscription->amount ?? 0);
            }

            // Get purchase details
            $purchaseDetails = DB::table('transactions')
                ->leftJoin('posts', function($join) {
                    $join->on('transactions.purchasable_id', '=', 'posts.id')
                         ->where('transactions.purchasable_type', '=', 'App\\Models\\Post');
                })
                ->leftJoin('media', function($join) {
                    $join->on('transactions.purchasable_id', '=', 'media.id')
                         ->where('transactions.purchasable_type', '=', 'App\\Models\\Media');
                })
                ->where('transactions.sender_id', $viewedUser->id)
                ->where('transactions.receiver_id', $creator->id)
                ->where('transactions.status', 'approved')
                ->whereIn('transactions.type', [
                    'media_purchase',
                    'post_purchase',
                    'video_purchase',
                    'album_purchase'
                ])
                ->select(
                    'transactions.*',
                    'posts.id as post_id',
                    'media.id as media_id',
                    DB::raw('COALESCE(posts.content, media.type) as content_type')
                )
                ->orderBy('transactions.created_at', 'desc')
                ->get();

            // Get tip details
            $tipDetails = DB::table('transactions')
                ->where('sender_id', $viewedUser->id)
                ->where('receiver_id', $creator->id)
                ->where('status', 'approved')
                ->where('type', 'tip')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $viewedUser->id,
                        'username' => $viewedUser->username,
                        'name' => $viewedUser->name,
                        'avatar' => $viewedUser->avatar
                    ],
                    'totals' => [
                        'subscriptions' => round($subscriptions, 2),
                        'purchases' => round($purchases, 2),
                        'tips' => round($tips, 2),
                        'total_spent' => round($totalSpent, 2)
                    ],
                    'breakdown' => [
                        'subscriptions' => $subscriptionDetails,
                        'purchases' => $purchaseDetails,
                        'tips' => $tipDetails
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting user earnings', [
                'creator_id' => $creator->id,
                'viewed_user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get user earnings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
