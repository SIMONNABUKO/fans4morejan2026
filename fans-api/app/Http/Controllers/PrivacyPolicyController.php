<?php

namespace App\Http\Controllers;

use App\Models\PrivacyPolicy;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PrivacyPolicyController extends Controller
{
    /**
     * Get the current privacy policy
     */
    public function show(): JsonResponse
    {
        $privacyPolicy = PrivacyPolicy::active()->latest()->first();

        if (!$privacyPolicy) {
            return response()->json([
                'message' => 'Privacy policy not found'
            ], 404);
        }

        return response()->json([
            'privacy_policy' => [
                'id' => $privacyPolicy->id,
                'content' => $privacyPolicy->content,
                'version' => $privacyPolicy->version,
                'effective_date' => $privacyPolicy->effective_date?->toISOString(),
                'created_at' => $privacyPolicy->created_at->toISOString(),
                'updated_at' => $privacyPolicy->updated_at->toISOString(),
            ]
        ]);
    }
}
