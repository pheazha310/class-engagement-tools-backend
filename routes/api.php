<?php

use App\Http\Controllers\Api\WheelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
