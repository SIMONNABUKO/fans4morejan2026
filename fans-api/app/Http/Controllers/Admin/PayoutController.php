<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return response()->json($payouts);
    }

    public function show($id)
    {
        $payout = Payout::with('user')
            ->findOrFail($id);
        
        return response()->json($payout);
    }

    public function process($id)
    {
        $payout = Payout::findOrFail($id);
        
        if ($payout->status !== 'pending') {
            return response()->json(['message' => 'Payout is not in pending status'], 400);
        }
        
        DB::transaction(function () use ($payout) {
            // Update payout status
            $payout->status = 'processed';
            $payout->processed_at = now();
            $payout->save();
            
            // Update user's wallet balance
            $user = User::findOrFail($payout->user_id);
            $user->wallet_balance -= $payout->amount;
            $user->save();
        });
        
        return response()->json(['message' => 'Payout processed successfully']);
    }
} 