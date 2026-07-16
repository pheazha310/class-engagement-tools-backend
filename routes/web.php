<?php

use Illuminate\Support\Facades\Route;

Route::inertia('login', 'auth/Login')->name('login');

Route::redirect('/', '/login');

Route::redirect('dashboard', '/admin/dashboard')->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
