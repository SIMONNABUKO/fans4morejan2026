<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EarningsController extends Controller
{
    protected $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Get earnings statistics for the authenticated user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validatedData = $request->validate([
                'period' => 'nullable|string|in:30days,month,year',
                'start_date' => 'nullable|date_format:Y-m-d',
                'end_date' => 'nullable|date_format:Y-m-d'
            ]);

            $period = $validatedData['period'] ?? '30days';
            $startDate = $validatedData['start_date'] ?? null;
            $endDate = $validatedData['end_date'] ?? null;

            // Get statistics from the service
            $statistics = $this->transactionService->getEarningsStatistics(
                $user,
                $period,
                $startDate,
                $endDate
            );

            // Get monthly statistics
            $monthlyStats = $this->transactionService->getMonthlyEarningsStatistics($user);

            return response()->json([
                'success' => true,
                'data' => [
                    'current_period' => $statistics,
                    'monthly_stats' => $monthlyStats
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving earnings statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}