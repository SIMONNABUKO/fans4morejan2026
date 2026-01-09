<?php

namespace App\Http\Controllers;

use App\Models\DMPermission;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class DMPermissionController extends Controller
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function index(Request $request)
    {
        $dmPermission = DMPermission::firstOrCreate(
            ['user_id' => auth()->id()]
        );

        $permissionSets = $dmPermission->permissionSets()
            ->with('permissions')
            ->get()
            ->map(function ($set) {
                return [
                    'permissions' => $set->permissions->map(function ($permission) {
                        return [
                            'type' => $permission->type,
                            'value' => $permission->value
                        ];
                    })
                ];
            });

        return response()->json([
            'permissionSets' => $permissionSets
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'required|array:type,value',
        ]);

        $dmPermission = DMPermission::firstOrCreate(
            ['user_id' => auth()->id()]
        );

        $this->permissionService->updatePermissions($dmPermission, $request->permissions);

        return response()->json([
            'message' => 'Permissions updated successfully'
        ]);
    }
}