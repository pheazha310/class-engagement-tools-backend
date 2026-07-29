<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * List all roles with user counts.
     */
    public function index(): JsonResponse
    {
        $roles = Role::withCount('users')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'users_count' => $role->users_count,
                    'permissions' => $role->permissions->pluck('name'),
                    'is_protected' => in_array($role->name, ['admin', 'super-admin']),
                    'created_at' => $role->created_at?->toISOString(),
                ];
            });

        return response()->json(['data' => $roles]);
    }

    /**
     * Show a single role with its permissions.
     */
    public function show(string $id): JsonResponse
    {
        $role = Role::withCount('users')->with('permissions')->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users_count,
                'permissions' => $role->permissions->pluck('name'),
                'is_protected' => in_array($role->name, ['admin', 'super-admin']),
                'created_at' => $role->created_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Create a new role.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return response()->json([
            'message' => 'Role created successfully.',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
                'users_count' => 0,
                'is_protected' => false,
            ],
        ], 201);
    }

    /**
     * Update an existing role.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, ['admin', 'super-admin'])) {
            return response()->json(['message' => 'Protected roles cannot be modified.'], 422);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:roles,name,'.$id],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        if (isset($validated['name'])) {
            $role->name = $validated['name'];
            $role->save();
        }

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        $role->load('permissions');

        return response()->json([
            'message' => 'Role updated successfully.',
            'data' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ],
        ]);
    }

    /**
     * Delete a role.
     */
    public function destroy(string $id): JsonResponse
    {
        $role = Role::findOrFail($id);

        if (in_array($role->name, ['admin', 'super-admin'])) {
            return response()->json(['message' => 'Protected roles cannot be deleted.'], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Role deleted successfully.']);
    }

    /**
     * List all available permissions.
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::all()->pluck('name');

        return response()->json(['data' => $permissions]);
    }
}
