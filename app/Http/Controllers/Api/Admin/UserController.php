<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * List all users with optional search and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->with('roles')
            ->withCount(['polls', 'votes']);

        // Search by name or email
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDir);

        $perPage = min((int) $request->get('per_page', 15), 100);
        $users = $query->paginate($perPage);

        // Transform users to include role names and school info
        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'role' => $user->role,
                'roles' => $user->getRoleNames()->toArray(),
                'profile_image' => $user->profile_image,
                'profile_image_url' => $user->profile_image
                    ? asset('storage/'.$user->profile_image)
                    : null,
                'polls_count' => (int) ($user->polls_count ?? 0),
                'votes_count' => (int) ($user->votes_count ?? 0),
                'created_at' => $user->created_at?->toISOString(),
                'updated_at' => $user->updated_at?->toISOString(),
            ];
        });

        return response()->json($users);
    }

    /**
     * Show a single user with their roles.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with('roles')->findOrFail($id);

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'role' => $user->role,
                'roles' => $user->getRoleNames()->toArray(),
                'profile_image' => $user->profile_image,
                'profile_image_url' => $user->profile_image
                    ? asset('storage/'.$user->profile_image)
                    : null,
                'created_at' => $user->created_at?->toISOString(),
                'updated_at' => $user->updated_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['sometimes', 'string', 'in:admin,teacher,student'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        // Determine the primary role: use 'roles' array first, then fall back to 'role'
        $roleNames = $request->input('roles', []);
        $primaryRole = $validated['role'] ?? ($roleNames[0] ?? 'student');

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $primaryRole,
            'email_verified_at' => now(),
        ]);

        // Assign Spatie roles
        if (! empty($roleNames)) {
            $user->syncRoles($roleNames);
        } else {
            $user->assignRole($primaryRole);
        }

        $user->load('roles');

        return response()->json([
            'message' => 'User created successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'roles' => $user->getRoleNames()->toArray(),
                'created_at' => $user->created_at?->toISOString(),
            ],
        ], 201);
    }

    /**
     * Update an existing user.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,'.$id],
            'password' => ['sometimes', 'string', 'min:8', 'nullable'],
            'role' => ['sometimes', 'string', 'in:admin,teacher,student'],
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password']) && $validated['password']) {
            $user->password = Hash::make($validated['password']);
        }

        // Handle roles from frontend (array) or single role (string)
        if ($request->has('roles') && is_array($request->input('roles'))) {
            $roleNames = $request->input('roles');
            $user->syncRoles($roleNames);
            $user->role = $roleNames[0] ?? 'student';
        } elseif (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
            $user->role = $validated['role'];
        }

        $user->save();

        $user->load('roles');

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'roles' => $user->getRoleNames()->toArray(),
                'updated_at' => $user->updated_at?->toISOString(),
            ],
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            // Prevent deleting yourself
            if ($request->user() && (int) $request->user()->id === (int) $user->id) {
                return response()->json(['message' => 'You cannot delete your own account.'], 422);
            }

            $user->delete();

            return response()->json(['message' => 'User deleted successfully.']);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'User not found.'], 404);
        }
    }
}
