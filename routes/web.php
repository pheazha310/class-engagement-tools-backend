<?php

use Illuminate\Support\Facades\Route;

// Redirect root to backend login
Route::redirect('/', '/login');

Route::redirect('dashboard', '/admin/dashboard')->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
