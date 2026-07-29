<?php

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

// ─── Admin SPA entry point ───
// This renders the Inertia admin dashboard shell, which bootstraps
// the admin Vue Router for all child pages (users, roles, schools, etc.)
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('dashboard/{any?}', function () {
        // Fetch initial users data on the server to avoid unauthenticated API calls
        // from the Vue SPA (the Inertia page load is properly session-authenticated).
        $usersQuery = User::with('roles')
            ->withCount(['polls', 'votes'])
            ->orderBy('created_at', 'desc');

        $usersPaginator = $usersQuery->paginate(15);

        $usersData = collect($usersPaginator->items())->map(function ($user) {
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

        // Compute real dashboard stats from the database
        $totalUsers = User::count();
        $students = User::where('role', 'student')->count();
        $teachers = User::where('role', 'teacher')->count();
        $admins = User::where('role', 'admin')->count();

        $currentUser = request()->user();
        $initials = '';
        if ($currentUser) {
            $parts = explode(' ', trim($currentUser->name));
            foreach ($parts as $part) {
                if (! empty($part)) {
                    $initials .= strtoupper($part[0]);
                }
            }
            $initials = substr($initials, 0, 2);
        }

        // Fetch roles data from the database
        $roles = Role::withCount('users')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'users_count' => $role->users_count,
                    'permissions' => $role->permissions->pluck('name'),
                    'is_protected' => in_array($role->name, ['admin', 'super-admin']),
                ];
            });

        return inertia('admin/Dashboard', [
            'serverUsers' => [
                'data' => $usersData->toArray(),
                'current_page' => $usersPaginator->currentPage(),
                'last_page' => $usersPaginator->lastPage(),
                'from' => $usersPaginator->firstItem(),
                'to' => $usersPaginator->lastItem(),
                'total' => $usersPaginator->total(),
                'links' => $usersPaginator->linkCollection()->toArray(),
            ],
            'serverStats' => [
                'stats' => [
                    ['id' => 'total-users', 'title' => 'Total Users', 'value' => $totalUsers, 'description' => 'Registered platform users', 'growth' => 0, 'growthLabel' => 'Total', 'accent' => 'primary', 'icon' => 'users'],
                    ['id' => 'students', 'title' => 'Students', 'value' => $students, 'description' => 'Student accounts', 'growth' => 0, 'growthLabel' => '', 'accent' => 'success', 'icon' => 'graduation-cap'],
                    ['id' => 'teachers', 'title' => 'Teachers', 'value' => $teachers, 'description' => 'Teacher accounts', 'growth' => 0, 'growthLabel' => '', 'accent' => 'warning', 'icon' => 'chalkboard-teacher'],
                    ['id' => 'admins', 'title' => 'Admins', 'value' => $admins, 'description' => 'Admin accounts', 'growth' => 0, 'growthLabel' => '', 'accent' => 'info', 'icon' => 'shield'],
                ],
                'currentUser' => [
                    'name' => $currentUser?->name ?? 'Admin',
                    'email' => $currentUser?->email ?? '',
                    'role' => $currentUser?->role ?? 'Administrator',
                    'initials' => $initials ?: 'A',
                    'avatar' => null,
                ],
            ],
            'serverRoles' => $roles->toArray(),
        ]);
    })->where('any', '.*')->name('admin.dashboard');
});

// ─── Admin data API routes ───
// These are used by the admin Vue SPA to fetch/manage data.
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('api/admin')->group(function () {
    // Users
    Route::get('users', [AdminUserController::class, 'index']);
    Route::post('users', [AdminUserController::class, 'store']);
    Route::get('users/{user}', [AdminUserController::class, 'show']);
    Route::put('users/{user}', [AdminUserController::class, 'update']);
    Route::delete('users/{user}', [AdminUserController::class, 'destroy']);

    // Dashboard
    Route::get('dashboard', [AdminDashboardController::class, 'index']);

    // Roles
    Route::get('roles', [AdminRoleController::class, 'index']);
    Route::post('roles', [AdminRoleController::class, 'store']);
    Route::get('roles/{role}', [AdminRoleController::class, 'show']);
    Route::put('roles/{role}', [AdminRoleController::class, 'update']);
    Route::delete('roles/{role}', [AdminRoleController::class, 'destroy']);
    Route::get('roles/permissions/all', [AdminRoleController::class, 'permissions']);
});
