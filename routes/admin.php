<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('dashboard/{any?}', function () {
        return inertia('admin/AdminDashboardShell');
    })->where('any', '.*')->name('admin.dashboard');
});
