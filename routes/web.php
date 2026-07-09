<?php

use Illuminate\Support\Facades\Route;

Route::inertia('login', 'auth/Login')->name('login');

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
