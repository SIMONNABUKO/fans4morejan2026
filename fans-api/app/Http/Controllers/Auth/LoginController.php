<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\LoginService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        private LoginService $loginService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $loginData = $this->loginService->login($request);

        return response()->json($loginData);
    }
}