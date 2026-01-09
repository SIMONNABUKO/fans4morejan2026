<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $stats = [
            'totalUsers' => User::count(),
            'activePosts' => Post::where('status', 'published')->count(),
            'totalComments' => Comment::count(),
            'revenue' => Transaction::where('status', 'completed')
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->sum('amount')
        ];

        return response()->json($stats);
    }

    public function recentActivity(): JsonResponse
    {
        $activity = collect();

        // Get recent user registrations
        $recentUsers = User::latest()
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'type' => 'user_registration',
                    'title' => 'New User Registration',
                    'description' => "{$user->name} joined the platform",
                    'timestamp' => $user->created_at->toISOString(),
                ];
            });
        $activity = $activity->concat($recentUsers);

        // Get recent posts
        $recentPosts = Post::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'type' => 'post_published',
                    'title' => 'New Post Published',
                    'description' => "{$post->user->name} published a new post",
                    'timestamp' => $post->created_at->toISOString(),
                ];
            });
        $activity = $activity->concat($recentPosts);

        // Get recent subscriptions
        $recentSubscriptions = Transaction::with('user')
            ->where('type', 'subscription')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => 'subscription',
                    'title' => 'New Subscription',
                    'description' => "{$transaction->user->name} started a new subscription",
                    'timestamp' => $transaction->created_at->toISOString(),
                ];
            });
        $activity = $activity->concat($recentSubscriptions);

        // Sort by timestamp and take the 10 most recent items
        $activity = $activity->sortByDesc('timestamp')->take(10)->values();

        return response()->json($activity);
    }
} 