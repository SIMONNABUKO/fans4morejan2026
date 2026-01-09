<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\CreatorApplicationController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\UserSettingController;
use App\Http\Controllers\Admin\WalletController;
use App\Http\Controllers\Admin\PlatformWalletController;
use App\Http\Controllers\Admin\TierController;
use App\Http\Controllers\Admin\MessageController;

Route::prefix('admin')->middleware(['auth:sanctum', 'admin', 'throttle:admin', 'cors'])->group(function () {
    // Overview/Stats
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/activity', [DashboardController::class, 'recentActivity']);

    // User Management
    Route::get('/users/list', [UserController::class, 'index']);
    Route::get('/users/show/{id}', [UserController::class, 'show'])->where('id', '[0-9]+');
    Route::post('/users/create', [UserController::class, 'store']);
    Route::put('/users/update/{id}', [UserController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/users/delete/{id}', [UserController::class, 'destroy'])->where('id', '[0-9]+');

    // User Settings
    Route::get('/users/{id}/settings', [UserSettingController::class, 'adminViewUserSettings'])->where('id', '[0-9]+');
    Route::put('/users/{id}/settings/{category}', [UserSettingController::class, 'adminUpdateUserSettings'])
        ->where('id', '[0-9]+')
        ->where('category', 'account|privacyAndSecurity|emailNotifications|messaging');

    // Content Management
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/stats', [PostController::class, 'stats']);
        Route::get('/{id}', [PostController::class, 'show'])->where('id', '[0-9]+');
        Route::put('/{id}', [PostController::class, 'update'])->where('id', '[0-9]+');
        Route::delete('/{id}', [PostController::class, 'destroy'])->where('id', '[0-9]+');
    });

    Route::get('/comments', [CommentController::class, 'index']);
    Route::get('/comments/{id}', [CommentController::class, 'show'])->where('id', '[0-9]+');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->where('id', '[0-9]+');

    // Creator Applications
    Route::prefix('creator-applications')->group(function () {
        Route::get('/', [CreatorApplicationController::class, 'index']);
        Route::put('/{id}/status', [CreatorApplicationController::class, 'updateStatus'])->where('id', '[0-9]+');
        Route::get('/{id}/history', [CreatorApplicationController::class, 'history'])->where('id', '[0-9]+');
    });

    // Financial Management
    Route::prefix('platform-wallet')->group(function () {
        Route::get('/balance', [PlatformWalletController::class, 'getBalance']);
        Route::get('/history', [PlatformWalletController::class, 'getHistory']);
        Route::get('/stats', [PlatformWalletController::class, 'getStats']);
        Route::post('/withdraw', [PlatformWalletController::class, 'withdraw']);
    });

    Route::prefix('wallets')->group(function () {
        Route::get('/', [WalletController::class, 'index']);
        Route::get('/stats', [WalletController::class, 'stats']);
        Route::get('/{userId}', [WalletController::class, 'show'])->where('id', '[0-9]+');
        Route::get('/{userId}/history', [WalletController::class, 'history'])->where('id', '[0-9]+');
        Route::put('/{userId}', [WalletController::class, 'update'])->where('id', '[0-9]+');
    });

    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::post('/{id}/refund', [TransactionController::class, 'refund']);
    });

    Route::get('/payouts', [PayoutController::class, 'index']);
    Route::get('/payouts/{id}', [PayoutController::class, 'show'])->where('id', '[0-9]+');
    Route::post('/payouts/{id}/process', [PayoutController::class, 'process'])->where('id', '[0-9]+');

    // Analytics
    Route::get('/analytics/users', [AnalyticsController::class, 'users']);
    Route::get('/analytics/content', [AnalyticsController::class, 'content']);
    Route::get('/analytics/financial', [AnalyticsController::class, 'financial']);

    // Tier Management Routes
    Route::prefix('tiers')->group(function () {
        Route::get('/', [TierController::class, 'index']);
        Route::get('/stats', [TierController::class, 'stats']);
        Route::get('/{id}', [TierController::class, 'show']);
        Route::put('/{id}', [TierController::class, 'update']);
        Route::delete('/{id}', [TierController::class, 'destroy']);
    });

    // Message Monitoring
    Route::prefix('messages')->group(function () {
        Route::get('/', [MessageController::class, 'index']);
        Route::get('/stats', [MessageController::class, 'getStats']);
        Route::get('/{id}', [MessageController::class, 'show']);
        Route::post('/{id}/review', [MessageController::class, 'review']);
        Route::delete('/{id}', [MessageController::class, 'destroy']);
    });
}); 