<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    private const string GUARD = 'web';

    public function index(): JsonResponse
    {
        $roles = Role::with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => \DB::table('model_has_roles')
                    ->where('role_id', $role->id)
                    ->where('model_type', \App\Models\User::class)
                    ->count(),
                'permissions' => $role->permissions->pluck('name'),
                'is_protected' => in_array($role->name, ['admin', 'teacher', 'student']),
            ])
            ->values();

        return response()->json(['data' => $roles]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name,guard_name,'.self::GUARD],
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => self::GUARD]);

        if (! empty($validated['permissions'])) {
            $this->syncPermissions($role, $validated['permissions']);
        }

        $role->load('permissions:id,name');

        return response()->json([
            'message' => 'Role created.',
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => 0,
                'permissions' => $role->permissions->pluck('name'),
                'is_protected' => false,
            ],
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        $role->load('permissions:id,name');

        return response()->json([
            'id' => $role->id,
            'name' => $role->name,
            'users_count' => \DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', \App\Models\User::class)
                ->count(),
            'permissions' => $role->permissions->pluck('name'),
            'is_protected' => in_array($role->name, ['admin', 'teacher', 'student']),
        ]);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        if (in_array($role->name, ['admin', 'teacher', 'student'])) {
            return response()->json([
                'message' => 'Built-in roles cannot be modified.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name,guard_name,'.self::GUARD],
        ]);

        $role->update(['name' => $validated['name']]);

        $this->syncPermissions($role, $validated['permissions'] ?? []);

        $role->load('permissions:id,name');

        return response()->json([
            'message' => 'Role updated.',
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => \DB::table('model_has_roles')
                    ->where('role_id', $role->id)
                    ->where('model_type', \App\Models\User::class)
                    ->count(),
                'permissions' => $role->permissions->pluck('name'),
                'is_protected' => false,
            ],
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        if (in_array($role->name, ['admin', 'teacher', 'student'])) {
            return response()->json([
                'message' => 'Built-in roles cannot be deleted.',
            ], 403);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted.',
        ]);
    }

    public function permissions(): JsonResponse
    {
        return response()->json(
            Permission::where('guard_name', self::GUARD)->orderBy('name')->pluck('name')
        );
    }

    private function syncPermissions(Role $role, array $permissionNames): void
    {
        $permissions = Permission::whereIn('name', $permissionNames)
            ->where('guard_name', self::GUARD)
            ->get();

        $role->syncPermissions($permissions);
    }
}
