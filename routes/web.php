<?php

use App\Http\Controllers\StudentDashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('login', 'auth/Login')->name('login');

Route::redirect('/', '/login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (Auth::user()->isStudent()) {
            return redirect()->route('student.dashboard');
        }

        return Inertia::render('Dashboard');
    })->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('student/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
