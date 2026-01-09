<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Transaction;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function users()
    {
        // Get user statistics
        $totalUsers = User::count();
        $activeUsers = User::where('last_login_at', '>=', now()->subDays(30))->count();
        $newUsers = User::where('created_at', '>=', now()->subDays(30))->count();
        
        // Get user roles distribution
        $userRoles = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();
        
        // Get user growth over time
        $userGrowth = User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return response()->json([
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'new_users' => $newUsers,
            'user_roles' => $userRoles,
            'user_growth' => $userGrowth
        ]);
    }

    public function content()
    {
        // Get content statistics
        $totalPosts = Post::count();
        $totalComments = Comment::count();
        $newPosts = Post::where('created_at', '>=', now()->subDays(30))->count();
        $newComments = Comment::where('created_at', '>=', now()->subDays(30))->count();
        
        // Get content growth over time
        $postGrowth = Post::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $commentGrowth = Comment::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return response()->json([
            'total_posts' => $totalPosts,
            'total_comments' => $totalComments,
            'new_posts' => $newPosts,
            'new_comments' => $newComments,
            'post_growth' => $postGrowth,
            'comment_growth' => $commentGrowth
        ]);
    }

    public function financial()
    {
        // Get financial statistics
        $totalTransactions = Transaction::count();
        $totalTransactionAmount = Transaction::sum('amount');
        $totalPayouts = Payout::count();
        $totalPayoutAmount = Payout::sum('amount');
        
        // Get recent transactions
        $recentTransactions = Transaction::with('user', 'creator')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Get recent payouts
        $recentPayouts = Payout::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Get transaction growth over time
        $transactionGrowth = Transaction::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'), DB::raw('sum(amount) as amount'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return response()->json([
            'total_transactions' => $totalTransactions,
            'total_transaction_amount' => $totalTransactionAmount,
            'total_payouts' => $totalPayouts,
            'total_payout_amount' => $totalPayoutAmount,
            'recent_transactions' => $recentTransactions,
            'recent_payouts' => $recentPayouts,
            'transaction_growth' => $transactionGrowth
        ]);
    }
} 