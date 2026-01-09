<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FollowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Log;

class FollowController extends Controller
{
    protected $followService;

    public function __construct(FollowService $followService)
    {
        $this->followService = $followService;
    }

    public function follow(Request $request, $user): JsonResponse
    {
        $followedUser = \App\Models\User::find($user);
        Log::info('FollowController@follow', [
            'route_user_param' => $user,
            'resolved_user_id' => $followedUser ? $followedUser->id : null,
            'authenticated_user_id' => $request->user()->id ?? null,
        ]);
        if (!$followedUser) {
            Log::warning('FollowController@follow: User not found', [
                'route_user_param' => $user
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        }
        try {
            $result = $this->followService->follow($request->user(), $followedUser);
            return response()->json(['success' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function unfollow($user): JsonResponse
    {
        $followedUser = \App\Models\User::find($user);
        Log::info('FollowController@unfollow', [
            'route_user_param' => $user,
            'resolved_user_id' => $followedUser ? $followedUser->id : null,
            'authenticated_user_id' => request()->user()->id ?? null,
        ]);
        
        if (!$followedUser) {
            Log::warning('FollowController@unfollow: User not found', [
                'route_user_param' => $user
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        }
        
        try {
            $result = $this->followService->unfollow(request()->user(), $followedUser);
            return response()->json(['success' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function followers(): JsonResponse
    {
        $user = request()->user();
        $followers = $this->followService->getFollowers($user);
        $followers->setCollection($followers->getCollection()->map(function ($user) {
            return new UserResource($user);
        }));
        return response()->json($followers);
    }

    public function following(): JsonResponse
    {
        $user = request()->user();
        Log::info("User ID: " . $user->id);
        $following = $this->followService->getFollowing($user);

        // Debug the query and results
        Log::info('Following query', [
            'user_id' => $user->id,
            'sql' => $user->following()->toSql(),
            'count' => $following->count(),
            'data' => $following->items()
        ]);

        return response()->json(UserResource::collection($following));
    }
}
