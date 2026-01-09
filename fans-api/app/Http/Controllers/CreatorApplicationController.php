<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatorApplicationRequest;
use App\Services\CreatorApplicationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreatorApplicationController extends Controller
{
    protected $service;

    public function __construct(CreatorApplicationService $service)
    {
        $this->service = $service;
    }

    public function store(CreatorApplicationRequest $request)
    {
        $application = $this->service->createOrUpdateApplication($request->validated(), Auth::id());
        return response()->json($application, 201);
    }

    public function getCurrentUserApplication(Request $request)
    {
        $application = $this->service->getApplicationByUserId(Auth::id());
        return $application ? response()->json($application) : response()->json(null, 404);
    }

    public function getUserApplication($userId)
    {
        // Add authorization check here to ensure only admins can access this
        $application = $this->service->getApplicationByUserId($userId);
        return $application ? response()->json($application) : response()->json(null, 404);
    }
}
