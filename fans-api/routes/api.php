<?php

use App\Http\Controllers\EarningsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UserRegistrationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AutomatedMessageController;
use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CCBillWebhookController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CreatorApplicationController;
use App\Http\Controllers\DMPermissionController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\MediaAlbumController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayoutMethodController;
use App\Http\Controllers\PayoutRequestController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostTagController;
use App\Http\Controllers\SearchController;

use App\Http\Controllers\FeedFilterController;
use App\Http\Controllers\MessageFilterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TierController;
use App\Http\Controllers\TipController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletHistoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\TrackingLinkController;
use App\Http\Controllers\MassMessageController;
use App\Http\Controllers\MessageDraftController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\ScheduledMessageController;
use App\Http\Controllers\BlockedLocationController;
use App\Http\Controllers\UserLocationController;
use App\Http\Controllers\ManagementSessionController;
use Illuminate\Support\Facades\Broadcast;

// CORS test route
Route::get('/cors-test', function () {
    return response()->json(['message' => 'CORS is working!']);
});

// Terms test route
Route::get('/terms-test', function () {
    return response()->json([
        'message' => 'Terms API is accessible',
        'timestamp' => now(),
        'status' => 'working'
    ]);
});

// Privacy test route
Route::get('/privacy-test', function () {
    return response()->json([
        'message' => 'Privacy API is accessible',
        'timestamp' => now(),
        'status' => 'working'
    ]);
});

// Populate privacy policy data
Route::get('/privacy/populate', function () {
    try {
        // Check if privacy policy already exists
        $existingPolicy = \App\Models\PrivacyPolicy::first();
        if ($existingPolicy) {
            return response()->json([
                'message' => 'Privacy policy already exists',
                'id' => $existingPolicy->id,
                'status' => 'exists'
            ]);
        }

        $privacyPolicyContent = <<<HTML
<h1 class="text-3xl font-bold mb-6">Privacy Policy</h1>

<p class="mb-4 text-gray-600 dark:text-gray-300">Last updated: August 4, 2025</p>

<div class="space-y-6">
    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">1. Introduction</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            Welcome to Fans4More ("we," "our," or "us"). We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our platform and services.
        </p>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            By using Fans4More, you agree to the collection and use of information in accordance with this policy. If you do not agree with our policies and practices, please do not use our service.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">2. Information We Collect</h2>
        
        <h3 class="text-xl font-medium mb-3 text-gray-800 dark:text-gray-200">2.1 Personal Information</h3>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We may collect personal information that you provide directly to us, including:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Name, email address, and username</li>
            <li>Profile information and bio</li>
            <li>Payment and billing information</li>
            <li>Content you create, upload, or share</li>
            <li>Communications with other users</li>
            <li>Preferences and settings</li>
        </ul>

        <h3 class="text-xl font-medium mb-3 text-gray-800 dark:text-gray-200">2.2 Automatically Collected Information</h3>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We automatically collect certain information when you use our service:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Device information (IP address, browser type, operating system)</li>
            <li>Usage data (pages visited, time spent, interactions)</li>
            <li>Location data (if you grant permission)</li>
            <li>Cookies and similar tracking technologies</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">3. How We Use Your Information</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We use the collected information for various purposes:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Provide, maintain, and improve our services</li>
            <li>Process payments and transactions</li>
            <li>Send notifications and updates</li>
            <li>Personalize your experience</li>
            <li>Ensure platform security and prevent fraud</li>
            <li>Comply with legal obligations</li>
            <li>Analyze usage patterns and trends</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">4. Information Sharing and Disclosure</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We do not sell, trade, or rent your personal information to third parties. However, we may share your information in the following circumstances:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li><strong>With your consent:</strong> When you explicitly agree to share information</li>
            <li><strong>Service providers:</strong> Trusted third parties who assist in operating our platform</li>
            <li><strong>Legal requirements:</strong> When required by law or to protect our rights</li>
            <li><strong>Business transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
            <li><strong>Safety and security:</strong> To protect users and prevent fraud</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">5. Data Security</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We implement appropriate technical and organizational measures to protect your personal information:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Encryption of data in transit and at rest</li>
            <li>Regular security assessments and updates</li>
            <li>Access controls and authentication measures</li>
            <li>Secure payment processing</li>
            <li>Regular backups and disaster recovery</li>
        </ul>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            However, no method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">6. Your Rights and Choices</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            You have certain rights regarding your personal information:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li><strong>Access:</strong> Request a copy of your personal data</li>
            <li><strong>Correction:</strong> Update or correct inaccurate information</li>
            <li><strong>Deletion:</strong> Request deletion of your personal data</li>
            <li><strong>Portability:</strong> Request transfer of your data to another service</li>
            <li><strong>Objection:</strong> Object to certain processing activities</li>
            <li><strong>Withdrawal:</strong> Withdraw consent at any time</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">7. Cookies and Tracking Technologies</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We use cookies and similar technologies to enhance your experience:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li><strong>Essential cookies:</strong> Required for basic functionality</li>
            <li><strong>Analytics cookies:</strong> Help us understand usage patterns</li>
            <li><strong>Preference cookies:</strong> Remember your settings and choices</li>
            <li><strong>Marketing cookies:</strong> Provide relevant content and ads</li>
        </ul>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            You can control cookie settings through your browser preferences.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">8. Third-Party Services</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            Our platform may contain links to third-party services or integrate with external providers:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Payment processors (Stripe, PayPal)</li>
            <li>Analytics services (Google Analytics)</li>
            <li>Social media platforms</li>
            <li>Cloud storage providers</li>
        </ul>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            These third parties have their own privacy policies, and we are not responsible for their practices.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">9. Data Retention</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We retain your personal information for as long as necessary to:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Provide our services to you</li>
            <li>Comply with legal obligations</li>
            <li>Resolve disputes and enforce agreements</li>
            <li>Maintain security and prevent fraud</li>
        </ul>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            When you delete your account, we will delete or anonymize your personal data within 30 days, except where retention is required by law.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">10. International Data Transfers</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your data during international transfers.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">11. Children's Privacy</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            Our service is not intended for children under 18 years of age. We do not knowingly collect personal information from children under 18. If you believe we have collected information from a child under 18, please contact us immediately.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">12. Changes to This Privacy Policy</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We may update this Privacy Policy from time to time. We will notify you of any changes by:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Posting the new Privacy Policy on this page</li>
            <li>Sending you an email notification</li>
            <li>Displaying a notice on our platform</li>
        </ul>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            Your continued use of our service after any changes constitutes acceptance of the updated policy.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">13. Contact Us</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            If you have any questions about this Privacy Policy or our data practices, please contact us:
        </p>
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <p class="text-gray-700 dark:text-gray-300">
                <strong>Email:</strong> privacy@fans4more.com<br>
                <strong>Address:</strong> [Your Business Address]<br>
                <strong>Phone:</strong> [Your Contact Number]
            </p>
        </div>
    </section>
</div>
HTML;

        // Create the privacy policy
        $privacyPolicy = \App\Models\PrivacyPolicy::create([
            'content' => $privacyPolicyContent,
            'version' => '1.0',
            'is_active' => true,
            'effective_date' => now(),
        ]);

        return response()->json([
            'message' => 'Privacy policy created successfully',
            'id' => $privacyPolicy->id,
            'status' => 'created'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to create privacy policy: ' . $e->getMessage(),
            'status' => 'error'
        ], 500);
    }
});

// Public Terms and Conditions Route (no CORS middleware)
Route::get('/terms/latest', [App\Http\Controllers\TermsAndConditionController::class, 'getLatest']);

// Public Privacy Policy Route (no CORS middleware)
Route::get('/privacy/latest', [App\Http\Controllers\PrivacyPolicyController::class, 'show']);

// Apply CORS middleware to all routes
Route::middleware('cors')->group(function () {

    Broadcast::routes(['middleware' => ['auth:sanctum']]);
    
    // Public routes (no auth required)
    Route::get('/track/{slug}', [TrackingLinkController::class, 'track']);
    Route::post('/tracking-links/click', [TrackingLinkController::class, 'recordClick']);
    Route::get('/public/previews', [FeedController::class, 'getPublicPreviews']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
    Route::get('/users/search', [UserController::class, 'search'])->middleware('auth:sanctum');
Route::post('/users/update-location-testing', [UserController::class, 'updateLocationForTesting'])->middleware('auth:sanctum');
Route::get('/users/check-data', [UserController::class, 'checkUserData'])->middleware('auth:sanctum');
    Route::get('/users/dashboard', [UserController::class, 'getDashboard']);
    Route::get('/users/{username}', [UserController::class, 'getByUsername']);
    Route::middleware('auth:sanctum')->get('/users/{userId}/earnings', [UserController::class, 'getUserEarnings']);
    Route::post('/register', [UserRegistrationController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/password/forgot', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/password/reset', [PasswordResetController::class, 'reset']);
    Route::middleware('auth:sanctum')->get('/me', [UserController::class, 'me']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/statistics/earnings', [EarningsController::class, 'getStatistics']);
        Route::get('/statistics/analytics', [TransactionController::class, 'getAnalytics']);
        Route::get('/statistics/test', [TransactionController::class, 'testAnalytics']);
        Route::get('/statistics/reach', [TransactionController::class, 'getReachStatistics']);
        Route::get('/statistics/top-fans', [TransactionController::class, 'getTopFansStatistics']);

        // Post routes
        Route::prefix('posts')->group(function () {
            Route::get('/', [PostController::class, 'index']);
            Route::post('/', [PostController::class, 'store']);
            Route::get('/scheduled', [PostController::class, 'getScheduledPosts']);
            Route::get('/{post}', [PostController::class, 'show']);
            Route::put('/{post}', [PostController::class, 'update']);
            Route::delete('/{post}', [PostController::class, 'destroy']);

            // Like routes for posts
            Route::post('/{post}/like', [LikeController::class, 'likePost']);
            Route::delete('/{post}/like', [LikeController::class, 'unlikePost']);

            // Pin/Unpin routes for posts
            Route::post('/{post}/pin', [PostController::class, 'pinPost']);
            Route::delete('/{post}/pin', [PostController::class, 'unpinPost']);

            // Comment routes
            Route::get('/{post}/comments', [CommentController::class, 'index']);
            Route::post('/{post}/comments', [CommentController::class, 'store']);
            Route::put('/comments/{comment}', [CommentController::class, 'update']);
            Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
        });

        // Bookmark routes
        Route::prefix('bookmarks')->group(function () {
            Route::get('/', [BookmarkController::class, 'index']);
            Route::post('/posts/{post}', [BookmarkController::class, 'bookmarkPost']);
            Route::delete('/posts/{post}', [BookmarkController::class, 'unbookmarkPost']);
            Route::post('/media/{media}', [BookmarkController::class, 'bookmarkMedia']);
            Route::delete('/media/{media}', [BookmarkController::class, 'unbookmarkMedia']);
            Route::post('/move/{bookmark}', [BookmarkController::class, 'moveBookmark']);
        });

        // Bookmark Album routes
        Route::prefix('bookmark-albums')->group(function () {
            Route::get('/', [BookmarkController::class, 'getUserAlbums']);
            Route::get('/user/{user}', [BookmarkController::class, 'getAlbumsByUser']);
            Route::post('/', [BookmarkController::class, 'createAlbum']);
            Route::put('/{album}', [BookmarkController::class, 'updateAlbum']);
            Route::delete('/{album}', [BookmarkController::class, 'deleteAlbum']);
            Route::get('/{album}/bookmarks', [BookmarkController::class, 'getAlbumBookmarks']);
        });

        //dm permissions
        Route::get('/dm-permissions', [DMPermissionController::class, 'index']);
        Route::post('/dm-permissions', [DMPermissionController::class, 'store']);

        Route::get('/automated-messages', [AutomatedMessageController::class, 'index']);
        Route::post('/automated-messages', [AutomatedMessageController::class, 'store']);
        Route::get('/automated-messages/{message}', [AutomatedMessageController::class, 'show']);
        Route::post('/automated-messages/{message}', [AutomatedMessageController::class, 'update']);
        Route::delete('/automated-messages/{message}', [AutomatedMessageController::class, 'destroy']);
        Route::patch('/automated-messages/{message}/toggle', [AutomatedMessageController::class, 'toggle']);

        Route::prefix('messages')->group(function () {
            // Mass messaging routes (specific routes must come first)
            Route::post('/mass', [MassMessageController::class, 'sendMassMessage']);
            Route::post('/schedule', [MassMessageController::class, 'scheduleMessage']);
            Route::get('/campaigns', [MassMessageController::class, 'index']);
            Route::get('/campaigns/{id}/analytics', [MassMessageController::class, 'getAnalytics']);
            
            // Media upload for messages
            Route::post('/upload-media', [MassMessageController::class, 'uploadMedia']);
            
            // Message Drafts (must come before catch-all routes)
            Route::get('/drafts', [MessageDraftController::class, 'index']);
            Route::post('/drafts', [MessageDraftController::class, 'store']);
            Route::get('/drafts/recent', [MessageDraftController::class, 'recent']);
            Route::get('/drafts/{id}', [MessageDraftController::class, 'show']);
            Route::put('/drafts/{id}', [MessageDraftController::class, 'update']);
            Route::delete('/drafts/{id}', [MessageDraftController::class, 'destroy']);
            Route::post('/drafts/{id}/duplicate', [MessageDraftController::class, 'duplicate']);
            Route::get('/drafts/{id}/to-message', [MessageDraftController::class, 'toMessageData']);
            
            // Message Templates
            Route::get('/templates', [MessageTemplateController::class, 'index']);
            Route::post('/templates', [MessageTemplateController::class, 'store']);
            Route::get('/templates/popular', [MessageTemplateController::class, 'popular']);
            Route::get('/templates/my-templates', [MessageTemplateController::class, 'myTemplates']);
            Route::get('/templates/{id}', [MessageTemplateController::class, 'show']);
            Route::put('/templates/{id}', [MessageTemplateController::class, 'update']);
            Route::delete('/templates/{id}', [MessageTemplateController::class, 'destroy']);
            Route::post('/templates/{id}/duplicate', [MessageTemplateController::class, 'duplicate']);
            Route::get('/templates/{id}/to-message', [MessageTemplateController::class, 'toMessageData']);
            
            // Scheduled Messages
            Route::get('/scheduled', [ScheduledMessageController::class, 'index']);
            Route::get('/scheduled/upcoming', [ScheduledMessageController::class, 'upcoming']);
            Route::get('/scheduled/overdue', [ScheduledMessageController::class, 'overdue']);
            Route::get('/scheduled/statistics', [ScheduledMessageController::class, 'statistics']);
            Route::get('/scheduled/{id}', [ScheduledMessageController::class, 'show']);
            Route::put('/scheduled/{id}', [ScheduledMessageController::class, 'update']);
            Route::delete('/scheduled/{id}', [ScheduledMessageController::class, 'destroy']);
            Route::post('/scheduled/{id}/cancel', [ScheduledMessageController::class, 'cancel']);

            // Individual message routes (catch-all routes must come LAST)
            Route::get('/', [MessageController::class, 'getRecentConversations']);
            Route::get('/{userId}', [MessageController::class, 'getOrCreateConversation']);
            Route::post('/', [MessageController::class, 'sendMessage']);
            Route::post('/{receiverId}', [MessageController::class, 'sendMessage']);

            // WebSocket functionality
            Route::post('/{messageId}/read', [MessageController::class, 'markAsRead']);
            Route::post('/{messageId}/unlock', [MessageController::class, 'unlockMessage']);
        });
        // Media routes
        Route::prefix('media')->group(function () {
            // Direct media upload route for testing watermark functionality
            Route::post('/upload', [\App\Http\Controllers\MediaController::class, 'store']);
            // Like routes for media
            Route::post('/{media}/like', [LikeController::class, 'likeMedia']);
            Route::delete('/{media}/like', [LikeController::class, 'unlikeMedia']);
        });

        // Media preview routes
        Route::prefix('media-previews')->group(function () {
            Route::post('/{mediaPreview}/like', [LikeController::class, 'likeMediaPreview']);
            Route::post('/{mediaPreview}/unlike', [LikeController::class, 'unlikeMediaPreview']);
        });

        Route::apiResource('media-albums', MediaAlbumController::class);
        Route::post('media-albums/{mediaAlbum}/add-media', [MediaAlbumController::class, 'addMedia']);
        Route::post('media-albums/{mediaAlbum}/remove-media', [MediaAlbumController::class, 'removeMedia']);

        // Tier routes
        Route::prefix('tiers')->group(function () {
            Route::get('/', [TierController::class, 'index']);
            Route::post('/', [TierController::class, 'store']);
            Route::get('/{tier}', [TierController::class, 'show'])->where('tier', '[0-9]+');
            Route::put('/{tier}', [TierController::class, 'update'])->where('tier', '[0-9]+');
            Route::get('/{tier}/plans', [TierController::class, 'getAvailablePlans'])->where('tier', '[0-9]+');
            Route::post('/{tier}/subscribe', [TierController::class, 'subscribe'])->where('tier', '[0-9]+');
            Route::get('/{tier}/subscriber-count', [TierController::class, 'subscriberCount'])->where('tier', '[0-9]+');
            Route::post('/{tier}/enable', [TierController::class, 'enable'])->where('tier', '[0-9]+');
            Route::post('/{tier}/disable', [TierController::class, 'disable'])->where('tier', '[0-9]+');
            Route::get('/user/{userId}', [TierController::class, 'getUserActiveTiers']);
            Route::delete('/{tier}', [TierController::class, 'destroy'])->where('tier', '[0-9]+');
        });

        // Feed routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/feed', [FeedController::class, 'index']);
            Route::get('/feed/new-posts', [FeedController::class, 'getNewPosts']);
            Route::get('/feed/previews', [FeedController::class, 'getPreviews']);
            Route::get('/feed/image-previews', [FeedController::class, 'getImagePreviews']);
            Route::get('/feed/all-image-previews', [FeedController::class, 'getAllImagePreviews']);
        });

        // Feed Filter routes
        Route::prefix('user/feed-filters')->group(function () {
            Route::get('/', [FeedFilterController::class, 'index']);
            Route::put('/', [FeedFilterController::class, 'update']);
            Route::get('/filtered-feed', [FeedFilterController::class, 'getFilteredFeed']);
        });

        // Message Filter routes
        Route::prefix('user/message-filters')->group(function () {
            Route::get('/', [MessageFilterController::class, 'index']);
            Route::put('/', [MessageFilterController::class, 'update']);
            Route::get('/filtered-conversations', [MessageFilterController::class, 'getFilteredConversations']);
        });

        // Search routes
        Route::prefix('search')->group(function () {
            Route::get('/hashtags', [SearchController::class, 'searchByHashtag']);
            Route::get('/posts', [SearchController::class, 'searchPosts']);
            Route::get('/hashtags/search', [SearchController::class, 'searchHashtags']);
            Route::get('/hashtags/popular', [SearchController::class, 'getPopularHashtags']);
            Route::get('/hashtags/trending', [SearchController::class, 'getTrendingHashtags']);
            Route::get('/hashtags/suggestions', [SearchController::class, 'getHashtagSuggestions']);
        });

        // Follow routes
        Route::post('/users/{user}/follow', [FollowController::class, 'follow']);
        Route::post('/users/{user}/unfollow', [FollowController::class, 'unfollow']);
        Route::get('/users/{user}/followers', [FollowController::class, 'followers']);
        Route::get('/users/{user}/following', [FollowController::class, 'following']);
        Route::post('/user/profile', [UserController::class, 'update']);
        // User Settings Routes
        Route::get('/user/settings', [UserSettingController::class, 'index']);
        Route::get('/user/settings/{category}', [UserSettingController::class, 'show']);
        Route::put('/user/settings/{category}', [UserSettingController::class, 'update']);
        Route::get('/users/{id}/settings/messaging', [UserSettingController::class, 'getUserMessagingSettings']);
        Route::get('/users/{id}/followers/{followerId}', [UserSettingController::class, 'checkFollowerStatus']);
        Route::get('/users/{id}/mutual-follow/{otherUserId}', [UserSettingController::class, 'checkMutualFollowStatus']);

        // Blocked Locations routes
        Route::prefix('blocked-locations')->group(function () {
            Route::get('/', [BlockedLocationController::class, 'getBlockedLocations']);
            Route::post('/', [BlockedLocationController::class, 'blockLocation']);
            Route::delete('/{countryCode}', [BlockedLocationController::class, 'unblockLocation']);
            Route::get('/countries/available', [BlockedLocationController::class, 'getAvailableCountries']);
            Route::get('/countries/all', [BlockedLocationController::class, 'getCountries']);
            Route::get('/search', [BlockedLocationController::class, 'searchLocations']);
        });

        // Management Sessions routes
        Route::prefix('management-sessions')->group(function () {
            Route::get('/', [ManagementSessionController::class, 'index']);
            Route::post('/', [ManagementSessionController::class, 'store']);
            Route::get('/{id}', [ManagementSessionController::class, 'show']);
            Route::delete('/{id}', [ManagementSessionController::class, 'destroy']);
        });

        // Claim management session (public route - no auth required)
        Route::post('/management-access/{token}', [ManagementSessionController::class, 'claim']);

        // User Location routes
        Route::prefix('user/location')->group(function () {
            Route::post('/update-from-coordinates', [UserLocationController::class, 'updateFromCoordinates']);
            Route::post('/update-from-ip', [UserLocationController::class, 'updateFromIp']);
            Route::post('/update-manually', [UserLocationController::class, 'updateManually']);
            Route::get('/current', [UserLocationController::class, 'getCurrentLocation']);
        });

        // Bookmarks

        Route::put('/user/display-name', [UserController::class, 'updateDisplayName']);
        Route::put('/user/email', [UserController::class, 'updateEmail']);
        Route::put('/user/password', [UserController::class, 'updatePassword']);
        Route::post('/user/toggle-2fa', [UserController::class, 'toggle2FA']);
        Route::delete('/user', [UserController::class, 'destroy']);

        // Wallet routes
        Route::get('/wallet/balance', [WalletController::class, 'getBalance']);
        Route::post('/wallet/add-funds', [WalletController::class, 'addFunds']);
        Route::post('/wallet/subtract-funds', [WalletController::class, 'subtractFunds']);
        Route::post('/wallet/move-pending-to-available', [WalletController::class, 'movePendingToAvailable']);

        // Wallet History
        Route::get('/wallet/history', [WalletHistoryController::class, 'index']);
        Route::get('/wallet/history/{walletHistory}', [WalletHistoryController::class, 'show']);

        // Payout Methods
        Route::get('/payout-methods', [PayoutMethodController::class, 'index']);
        Route::post('/payout-methods', [PayoutMethodController::class, 'store']);
        Route::put('/payout-methods/{payoutMethod}', [PayoutMethodController::class,  'store']);
        Route::put('/payout-methods/{payoutMethod}', [PayoutMethodController::class, 'update']);
        Route::delete('/payout-methods/{payoutMethod}', [PayoutMethodController::class, 'destroy']);
        Route::post('/payout-methods/{payoutMethod}/set-default', [PayoutMethodController::class, 'setDefault']);

        // Payout Requests
        Route::get('/payout-requests', [PayoutRequestController::class, 'index']);
        Route::post('/payout-requests', [PayoutRequestController::class, 'store']);
        Route::get('/payout-requests/{payoutRequest}', [PayoutRequestController::class, 'show']);
        Route::post('/payout-requests/{payoutRequest}/cancel', [PayoutRequestController::class, 'cancel']);

        // Transaction routes
        Route::post('/tip', [TransactionController::class, 'processTip']);
        Route::post('/transactions/{id}/link', [TransactionController::class, 'linkTipToTippable']);
        Route::post('/purchase', [TransactionController::class, 'processOneTimePurchase']);
        Route::post('/purchase-message', [TransactionController::class, 'processMessagePurchase']);
        Route::post('/subscribe', [TransactionController::class, 'processTierSubscription']);
        Route::get('/earnings/statistics', [EarningsController::class, 'getStatistics']);
        Route::get('/supporters/top', [TransactionController::class, 'getTopSupporters']);
        Route::get('/supporters/{id}/details', [TransactionController::class, 'getSupporterDetails']);
        // Subscription routes
        Route::get('/subscriptions', [SubscriptionController::class, 'index']);
        Route::post('/subscriptions/{id}/renew', [SubscriptionController::class, 'renew']);
        Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel']);

        // Creator subscription management routes
        Route::get('/creator/subscribers', [SubscriptionController::class, 'getSubscribers']);
        Route::get('/creator/subscribers/counts', [SubscriptionController::class, 'getSubscriberCounts']);
        Route::post('/creator/subscribers/{subscriber}/vip', [SubscriptionController::class, 'toggleVipStatus']);
        Route::post('/creator/subscribers/{subscriber}/mute', [SubscriptionController::class, 'toggleMuteStatus']);
        Route::post('/creator/subscribers/{subscriber}/block', [SubscriptionController::class, 'blockSubscriber']);
        Route::post('/creator/subscribers/{subscriber}/unblock', [SubscriptionController::class, 'unblockSubscriber']);
        Route::get('/creator/subscribers/{subscriber}/earnings', [SubscriptionController::class, 'getSubscriberEarnings']);

        // List routes
        Route::prefix('lists')->group(function () {
            Route::get('/', [ListController::class, 'index']);
            Route::post('/', [ListController::class, 'store']);
            Route::get('/{list}', [ListController::class, 'show']); // New route for fetching list details
            Route::get('/{list}/members', [ListController::class, 'members']);
            Route::post('/{list}/members/{user}', [ListController::class, 'addMember']);
            Route::delete('/{list}/members/{user}', [ListController::class, 'removeMember']);
            Route::delete('/{listId}', [ListController::class, 'destroy']);
        });

        Route::post('/reports', [ReportController::class, 'store']);

        // Creator Applications
        Route::prefix('creator-applications')->group(function () {
            Route::post('/', [CreatorApplicationController::class, 'store']);
            Route::get('/user', [CreatorApplicationController::class, 'getCurrentUserApplication']);
            Route::get('/user/{userId}', [CreatorApplicationController::class, 'getUserApplication'])
                ->where('userId', '[0-9]+')
                ->middleware('admin');
            Route::put('/{id}', [CreatorApplicationController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [CreatorApplicationController::class, 'destroy'])->where('id', '[0-9]+');
        });

        // CCBill webhook route
        Route::post('/payment/ccbill', [CCBillWebhookController::class, 'handleWebhook']);

        // Tag management
        Route::get('/tags/pending', [PostTagController::class, 'getPendingRequests']);
        Route::post('/tags/respond', [PostTagController::class, 'respondToTagRequest']);
        Route::get('/posts/{post}/tags', [PostTagController::class, 'getPostTags']);
        Route::get('/tags/history/users', [PostTagController::class, 'getPreviouslyTaggedUsers']);

        // Referral System Routes
        Route::prefix('referrals')->group(function () {
            Route::post('/generate-link', [ReferralController::class, 'generateLink']);
            Route::post('/generate-creator-code', [ReferralController::class, 'generateCreatorCode']);
            Route::put('/update-code', [ReferralController::class, 'updateCode']);
            Route::get('/statistics', [ReferralController::class, 'getStatistics']);
            Route::get('/earnings', [ReferralController::class, 'getEarningsHistory']);
            Route::get('/earnings-breakdown', [ReferralController::class, 'getEarningsBreakdown']);
            Route::get('/referred-users', [ReferralController::class, 'getReferredUsers']);
            Route::post('/validate-code', [ReferralController::class, 'validateReferralCode']);
        });

        // User search for taggingbb
        Route::get('/users/search', [PostTagController::class, 'searchUsers']);

        Route::prefix('notifications')->group(function () {
            // Get all notifications for the authenticated user
            Route::get('/', [NotificationController::class, 'index']);

            // Mark notification as read
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);

            // Mark all notifications as read
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);

            // Get unread notification count
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        });

        Route::post('/email/request-code', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'requestCode']);
        Route::post('/email/verify-code', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verifyCode']);

        // Tracking Links
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/tracking-links', [TrackingLinkController::class, 'index']);
            Route::post('/tracking-links', [TrackingLinkController::class, 'store']);
            Route::get('/tracking-links/{id}', [TrackingLinkController::class, 'show']);
            Route::put('/tracking-links/{id}', [TrackingLinkController::class, 'update']);
            Route::delete('/tracking-links/{id}', [TrackingLinkController::class, 'destroy']);
            Route::get('/tracking-links/{id}/stats', [TrackingLinkController::class, 'getStats']);
            Route::get('/tracking-links/{id}/events', [TrackingLinkController::class, 'getEvents']);
            Route::get('/tracking-links/{id}/actions', [TrackingLinkController::class, 'getActions']);
            Route::post('/tracking-links/{id}/actions', [TrackingLinkController::class, 'trackAction']);
        });

        // Subscription Discounts for Packages (Tiers)
        Route::get('tiers/{tier}/discounts', [\App\Http\Controllers\SubscriptionDiscountController::class, 'index']);
        Route::post('tiers/{tier}/discounts', [\App\Http\Controllers\SubscriptionDiscountController::class, 'store']);
        Route::get('tiers/{tier}/discounts/{discount}', [\App\Http\Controllers\SubscriptionDiscountController::class, 'show']);

        // Terms and Conditions Routes
        Route::middleware(['auth:sanctum', 'admin'])->group(function () {
            Route::get('/terms', [App\Http\Controllers\TermsAndConditionController::class, 'index']);
            Route::get('/terms/{id}', [App\Http\Controllers\TermsAndConditionController::class, 'show']);
            Route::post('/terms', [App\Http\Controllers\TermsAndConditionController::class, 'store']);
            Route::put('/terms/{id}', [App\Http\Controllers\TermsAndConditionController::class, 'update']);
            Route::delete('/terms/{id}', [App\Http\Controllers\TermsAndConditionController::class, 'destroy']);
            Route::post('/terms/{id}/publish', [App\Http\Controllers\TermsAndConditionController::class, 'publish']);
        });

    });
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->middleware(['auth:sanctum', 'admin']);
    Route::post('/renew-token', [AdminAuthController::class, 'renewToken'])->middleware(['auth:sanctum', 'admin']);
});

// Admin Routes
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    // User Management
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index']);
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show']);
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update']);
    Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy']);

    // Media routes
    Route::get('/media', [App\Http\Controllers\Admin\MediaController::class, 'index']);
    Route::get('/media/{id}', [App\Http\Controllers\Admin\MediaController::class, 'show']);
    Route::patch('/media/{id}/status', [App\Http\Controllers\Admin\MediaController::class, 'updateStatus']);
    Route::delete('/media/{id}', [App\Http\Controllers\Admin\MediaController::class, 'destroy']);
    Route::get('/media/stats', [App\Http\Controllers\Admin\MediaController::class, 'stats']);
});
