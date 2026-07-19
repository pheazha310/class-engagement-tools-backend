<?php

use Illuminate\Support\Facades\Route;

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
