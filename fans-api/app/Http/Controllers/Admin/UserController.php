<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        
        Log::info('Admin Users List - Request Parameters:', [
            'per_page' => $perPage,
            'search' => $request->input('search'),
            'role' => $request->input('role'),
            'status' => $request->input('status')
        ]);

        $users = User::latest()
            ->paginate($perPage);

        Log::info('Admin Users List - Database Results:', [
            'total_users' => $users->total(),
            'current_page' => $users->currentPage(),
            'per_page' => $users->perPage(),
            'last_page' => $users->lastPage(),
            'users' => $users->items()
        ]);

        return response()->json([
            'data' => $users->items(),
            'total' => $users->total(),
            'current_page' => $users->currentPage(),
            'per_page' => $users->perPage(),
            'last_page' => $users->lastPage(),
        ]);
    }

    public function show($id): JsonResponse
    {
        Log::info('Admin User Details - Request:', ['user_id' => $id]);

        $user = User::withCount(['posts', 'followers', 'following', 'media'])->findOrFail((int) $id);
        
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'avatar' => $user->avatar,
            'role' => $user->role,
            'status' => $user->status,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'two_factor_enabled' => $user->two_factor_enabled,
            'email_verified_at' => $user->email_verified_at,
            'posts_count' => $user->posts_count,
            'followers_count' => $user->followers_count,
            'following_count' => $user->following_count,
            'media_count' => $user->media_count
        ];

        Log::info('Admin User Details - Response:', ['user_data' => $userData]);

        return response()->json(['data' => $userData]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        Log::info('Admin User Update - Request:', [
            'user_id' => $id,
            'update_data' => $request->all()
        ]);

        $user = User::findOrFail((int) $id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => ['sometimes', 'string', Rule::in(['user', 'creator', 'admin'])],
            'status' => ['sometimes', 'string', Rule::in(['active', 'inactive', 'suspended'])],
        ]);

        $user->update($validated);

        Log::info('Admin User Update - Response:', [
            'user_id' => $id,
            'updated_data' => $validated,
            'user' => $user->toArray()
        ]);

        return response()->json($user);
    }

    public function store(Request $request): JsonResponse
    {
        Log::info('Admin User Create - Request:', [
            'create_data' => array_diff_key($request->all(), ['password' => '']) // Log everything except password
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => ['required', 'string', Rule::in(['user', 'creator', 'admin'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'active';

        $user = User::create($validated);

        Log::info('Admin User Create - Response:', [
            'user_id' => $user->id,
            'user' => array_diff_key($user->toArray(), ['password' => '']) // Log everything except password
        ]);

        return response()->json($user, 201);
    }

    public function destroy($id): JsonResponse
    {
        Log::info('Admin User Delete - Request:', ['user_id' => $id]);

        $user = User::findOrFail((int) $id);
        $user->delete();

        Log::info('Admin User Delete - Response:', [
            'user_id' => $id,
            'status' => 'deleted'
        ]);

        return response()->json(null, 204);
    }
} 