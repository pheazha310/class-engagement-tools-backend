<?php

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Api\Admin\SchoolController as AdminSchoolController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

// ─── Admin SPA entry point ───
// This renders the Inertia admin dashboard shell, which bootstraps
// the admin Vue Router for all child pages (users, roles, schools, etc.)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('dashboard/{any?}', function () {
        // Return the Dashboard Inertia page, which receives server-side props
        // and bootstraps the admin Vue Router for all child pages.
        return inertia('admin/Dashboard', [
            // Optional: pass server-side dashboard data to avoid an extra API call
            // 'stats' => [...],
            // 'userRegistrationData' => [...],
            // 'platformActivityData' => [...],
            // 'recentActivities' => [...],
            // 'notifications' => [...],
            // 'currentUser' => [...],
        ]);
    })->where('any', '.*')->name('admin.dashboard');
});

// ─── Admin data API routes ───
// These are used by the admin Vue SPA to fetch/manage data.
// They use 'auth' (web session guard) instead of 'auth:sanctum' because
// the admin login uses a Blade form with standard session auth.
// Being in routes/admin.php means they get the 'web' middleware group
// natively (no Sanctum EnsureFrontendRequestsAreStateful interference).
Route::middleware(['auth', 'role:admin'])->prefix('api/admin')->group(function () {
    // Users
    Route::get('users', [AdminUserController::class, 'index']);
    Route::post('users', [AdminUserController::class, 'store']);
    Route::get('users/{user}', [AdminUserController::class, 'show']);
    Route::put('users/{user}', [AdminUserController::class, 'update']);
    Route::delete('users/{user}', [AdminUserController::class, 'destroy']);

    // Dashboard
    Route::get('dashboard', [AdminDashboardController::class, 'index']);

    // Schools
    Route::get('schools', [AdminSchoolController::class, 'index']);
    Route::post('schools', [AdminSchoolController::class, 'store']);
    Route::get('schools/{school}', [AdminSchoolController::class, 'show']);
    Route::put('schools/{school}', [AdminSchoolController::class, 'update']);
    Route::delete('schools/{school}', [AdminSchoolController::class, 'destroy']);
    Route::get('schools/lookup/data', [AdminSchoolController::class, 'lookupData']);

    // Roles
    Route::get('roles', [AdminRoleController::class, 'index']);
    Route::post('roles', [AdminRoleController::class, 'store']);
    Route::get('roles/{role}', [AdminRoleController::class, 'show']);
    Route::put('roles/{role}', [AdminRoleController::class, 'update']);
    Route::delete('roles/{role}', [AdminRoleController::class, 'destroy']);
    Route::get('roles/permissions/all', [AdminRoleController::class, 'permissions']);
});
