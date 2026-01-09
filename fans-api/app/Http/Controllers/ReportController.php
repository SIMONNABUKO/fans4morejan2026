<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function store(StoreReportRequest $request): JsonResponse
    {
        Log::info('🔴 ReportController::store called', [
            'content_type' => $request->content_type,
            'content_id' => $request->content_id,
            'reason' => $request->reason,
            'user_id' => auth()->id()
        ]);
        
        // Validate that the content exists
        $contentResult = $this->reportService->getReportableModel(
            $request->content_type,
            $request->content_id
        );
        
        Log::info('🔴 Content result:', $contentResult);

        if (!$contentResult['success']) {
            Log::info('🔴 Content validation failed');
            return response()->json($contentResult, 422);
        }

        Log::info('🔴 About to create report');

        // Create the report
        $reportResult = $this->reportService->createReport(
            auth()->id(),
            $request->content_type,
            $request->content_id,
            $request->reason,
            $request->additional_info
        );

        Log::info('🔴 Report creation result:', $reportResult);

        if (!$reportResult['success']) {
            Log::info('🔴 Report creation failed');
            return response()->json($reportResult, 422);
        }

        Log::info('🔴 Returning success response');
        return response()->json($reportResult);
    }
}