<?php

namespace App\Http\Controllers;

use App\Services\CCBillService;
use Illuminate\Http\Request;

class CCBillWebhookController extends Controller
{
    protected $ccbillService;

    public function __construct(CCBillService $ccbillService)
    {
        $this->ccbillService = $ccbillService;
    }

    public function handleWebhook(Request $request)
    {
        $this->ccbillService->processWebhook($request);
        return response()->json(['message' => 'Webhook processed successfully']);
    }
}

