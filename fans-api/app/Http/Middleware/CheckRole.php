<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        Log::info('Checking user role', [
            'user_id' => $request->user()->id,
            'user_role' => $request->user()->role,
            'required_roles' => $roles
        ]);

        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            Log::warning('Unauthorized role access attempt', [
                'user_id' => $request->user()->id,
                'user_role' => $request->user()->role,
                'required_roles' => $roles
            ]);
            return response()->json(['message' => 'This action is unauthorized.'], 403);
        }

        return $next($request);
    }
} 