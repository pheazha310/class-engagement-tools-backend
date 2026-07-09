<?php

use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserController::class)->except('show');
        Route::resource('roles', RoleController::class)->except('show');
        Route::resource('locations', LocationController::class)->except('show');
    });
