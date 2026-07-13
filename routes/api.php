<?php

use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\GameSessionController;
use App\Http\Controllers\Api\LocationSchoolController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\SchoolRequestController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\WheelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [RegistrationController::class, 'register']);

Route::get('countries', [CountryController::class, 'index']);
Route::get('provinces', [ProvinceController::class, 'index']);
Route::get('districts', [DistrictController::class, 'index']);
Route::get('schools', [SchoolController::class, 'index']);
Route::get('location-schools', [LocationSchoolController::class, 'index']);
Route::post('school-requests', [SchoolRequestController::class, 'store'])->middleware('throttle:10,1');
Route::post('game-sessions', [GameSessionController::class, 'store']);
Route::get('game-sessions/join/{joinCode}', [GameSessionController::class, 'showByJoinCode'])->name('game-sessions.join');
Route::get('game-sessions', [GameSessionController::class, 'index'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);

    Route::get('polls/active', [PollController::class, 'active'])->withoutMiddleware('auth:sanctum');
    Route::get('polls/{poll}/results', [PollController::class, 'results']);

    Route::apiResource('polls', PollController::class)->except(['show']);
    Route::get('polls/{poll}', [PollController::class, 'show']);

    Route::post('polls/{poll}/start', [PollController::class, 'start']);
    Route::post('polls/{poll}/end', [PollController::class, 'end']);

    Route::post('polls/{poll}/vote', [VoteController::class, 'vote']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('users', [AdminUserController::class, 'index']);
        Route::post('users', [AdminUserController::class, 'store']);
        Route::get('users/{user}', [AdminUserController::class, 'show']);
        Route::put('users/{user}', [AdminUserController::class, 'update']);
        Route::delete('users/{user}', [AdminUserController::class, 'destroy']);
        Route::get('roles', [AdminUserController::class, 'roles']);
    });
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (! Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $request->session()->regenerate();

    return response()->json(['message' => 'Logged in']);
})->middleware('web');

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json(['message' => 'Logged out']);
})->middleware('web');

Route::post('/wheels/{wheel}/share-token', [WheelController::class, 'generateShareToken']);
Route::get('/wheels/shared/{shareToken}', [WheelController::class, 'showShared'])->name('wheels.shared');

Route::middleware('auth')->group(function () {
    Route::get('/wheels', [WheelController::class, 'index']);
    Route::post('/wheels', [WheelController::class, 'store']);
    Route::get('/wheels/{wheel}', [WheelController::class, 'show']);
    Route::put('/wheels/{wheel}', [WheelController::class, 'update']);
    Route::delete('/wheels/{wheel}', [WheelController::class, 'destroy']);
    Route::post('/wheels/{wheel}/participants', [WheelController::class, 'storeParticipant']);
    Route::post('/wheels/{wheel}/participants/import', [WheelController::class, 'importParticipants']);
    Route::delete('/wheels/{wheel}/participants/{participant}', [WheelController::class, 'destroyParticipant']);
});

Route::post('/wheel/spin', [WheelController::class, 'spin']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
