<?php

namespace App\Http\Controllers;

use App\Models\ManagementSession;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class ManagementSessionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sessions = ManagementSession::where('owner_id', Auth::id())
            ->with(['claimedBy:id,name,username,avatar'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'expires_in_days' => 'nullable|integer|min:1|max:365'
        ]);

        $token = Str::random(64);
        $expiresAt = null;

        if ($request->has('expires_in_days')) {
            $expiresAt = now()->addDays($request->expires_in_days);
        }

        $session = ManagementSession::create([
            'name' => $request->name,
            'token' => $token,
            'owner_id' => Auth::id(),
            'expires_at' => $expiresAt,
        ]);

        // Get frontend URL from config or env
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'));
        
        return response()->json([
            'success' => true,
            'data' => $session,
            'link' => "{$frontendUrl}/management-access/{$token}"
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $session = ManagementSession::where('owner_id', Auth::id())
            ->with(['claimedBy:id,name,username,avatar'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $session
        ]);
    }

    /**
     * Claim a management session via token
     */
    public function claim(Request $request, string $token)
    {
        $session = ManagementSession::where('token', $token)->firstOrFail();

        // Check if session is valid
        if (!$session->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'This session link has expired or has already been used.'
            ], 400);
        }

        // Capture device information
        $userAgent = $request->header('User-Agent', 'Unknown');
        $deviceInfo = $this->parseUserAgent($userAgent);
        
        // Mark session as used with device information
        $session->update([
            'used_at' => now(),
            'device_name' => $deviceInfo['device_name'],
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'ip_address' => $request->ip(),
            'location' => $deviceInfo['location'] ?? null,
        ]);

        // Create authentication token for the owner
        $owner = $session->owner;
        $authToken = $owner->createToken('management-session')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'You have successfully claimed this management session.',
            'token' => $authToken,
            'user' => new UserResource($owner)
        ]);
    }

    /**
     * Parse user agent to extract device information
     */
    private function parseUserAgent(string $userAgent): array
    {
        $deviceInfo = [
            'device_name' => 'Unknown Device',
            'device_type' => 'Unknown',
            'browser' => 'Unknown',
            'platform' => 'Unknown',
        ];

        // Detect device type
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            $deviceInfo['device_type'] = 'Mobile';
            if (preg_match('/iPhone/', $userAgent)) {
                $deviceInfo['device_name'] = 'iPhone';
                $deviceInfo['platform'] = 'iOS';
            } elseif (preg_match('/iPad/', $userAgent)) {
                $deviceInfo['device_name'] = 'iPad';
                $deviceInfo['platform'] = 'iOS';
            } elseif (preg_match('/Android/', $userAgent)) {
                $deviceInfo['device_name'] = 'Android Phone';
                $deviceInfo['platform'] = 'Android';
            }
        } else {
            $deviceInfo['device_type'] = 'Desktop';
            if (preg_match('/Windows/', $userAgent)) {
                $deviceInfo['platform'] = 'Windows';
                $deviceInfo['device_name'] = 'Windows PC';
            } elseif (preg_match('/Mac/', $userAgent)) {
                $deviceInfo['platform'] = 'macOS';
                $deviceInfo['device_name'] = 'Mac';
            } elseif (preg_match('/Linux/', $userAgent)) {
                $deviceInfo['platform'] = 'Linux';
                $deviceInfo['device_name'] = 'Linux PC';
            }
        }

        // Detect browser
        if (preg_match('/Chrome/', $userAgent)) {
            $deviceInfo['browser'] = 'Chrome';
        } elseif (preg_match('/Firefox/', $userAgent)) {
            $deviceInfo['browser'] = 'Firefox';
        } elseif (preg_match('/Safari/', $userAgent) && !preg_match('/Chrome/', $userAgent)) {
            $deviceInfo['browser'] = 'Safari';
        } elseif (preg_match('/Edge/', $userAgent)) {
            $deviceInfo['browser'] = 'Edge';
        } elseif (preg_match('/Opera/', $userAgent)) {
            $deviceInfo['browser'] = 'Opera';
        }

        return $deviceInfo;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $session = ManagementSession::where('owner_id', Auth::id())->findOrFail($id);
        
        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Management session deleted successfully.'
        ]);
    }
}
