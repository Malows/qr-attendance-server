<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Users\Roles\AssignPermissionsRequest;
use App\Http\Requests\Users\Roles\AssignRolesRequest;
use App\Http\Requests\Users\Roles\RevokePermissionsRequest;
use App\Http\Requests\Users\Roles\RevokeRolesRequest;
use App\Http\Requests\Users\Roles\SyncRolesRequest;
use App\Http\Requests\Users\Roles\ViewRolesRequest;

class RoleController extends Controller
{
    /**
     * Display a listing of all roles.
     */
    public function index(ViewRolesRequest $request)
    {
        $roles = Role::with('permissions')->get();

        return response()->json($roles);
    }

    /**
     * Display a listing of all permissions.
     */
    public function permissions(ViewRolesRequest $request)
    {
        $permissions = Permission::all();

        return response()->json($permissions);
    }

    /**
     * Assign a role to a user.
     */
    public function assignRole(AssignRolesRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->assignRole($validated['role']);

        return response()->json([
            'message' => 'Role assigned successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
    }

    /**
     * Remove a role from a user.
     */
    public function removeRole(RevokeRolesRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->removeRole($validated['role']);

        return response()->json([
            'message' => 'Role removed successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
    }

    /**
     * Sync roles for a user (replace all roles).
     */
    public function syncRoles(SyncRolesRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->syncRoles($validated['roles']);

        return response()->json([
            'message' => 'Roles synced successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
    }

    /**
     * Give a specific permission to a user.
     */
    public function givePermission(AssignPermissionsRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->givePermissionTo($validated['permission']);

        return response()->json([
            'message' => 'Permission granted successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
    }

    /**
     * Revoke a specific permission from a user.
     */
    public function revokePermission(RevokePermissionsRequest $request, User $user)
    {
        $validated = $request->validated();

        $user->revokePermissionTo($validated['permission']);

        return response()->json([
            'message' => 'Permission revoked successfully',
            'user' => $user->load('roles', 'permissions'),
        ]);
    }
}
