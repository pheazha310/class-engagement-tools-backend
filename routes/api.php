<?php

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Api\Admin\SchoolController as AdminSchoolController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\GameSessionController;
use App\Http\Controllers\Api\LocationSchoolController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuizQuestionController;
use App\Http\Controllers\Api\QuizRankingController;
use App\Http\Controllers\Api\QuizReportController;
use App\Http\Controllers\Api\QuizSubmitController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\SchoolRequestController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\WheelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [RegistrationController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('countries', [CountryController::class, 'index']);
Route::get('provinces', [ProvinceController::class, 'index']);
Route::get('districts', [DistrictController::class, 'index']);
Route::get('location-schools', [LocationSchoolController::class, 'index']);
Route::post('school-requests', [SchoolRequestController::class, 'store'])->middleware('throttle:10,1');
Route::post('game-sessions', [GameSessionController::class, 'store']);
Route::post('game-sessions/generate-questions', [GameSessionController::class, 'generateQuestions']);
Route::get('game-sessions/join/{joinCode}', [GameSessionController::class, 'showByJoinCode'])->name('game-sessions.join');
Route::post('game-sessions/{gameSession}/validate-answer', [GameSessionController::class, 'validateAnswer'])->name('game-sessions.validate-answer');
Route::get('game-sessions/{gameSession}/leaderboard', [GameSessionController::class, 'leaderboard'])->name('game-sessions.leaderboard');
Route::get('game-sessions', [GameSessionController::class, 'index'])->middleware('auth:sanctum');

// Quiz routes — public (no auth required)
Route::get('quizzes', [QuizController::class, 'index']);
Route::post('quizzes', [QuizController::class, 'store']);
Route::get('quizzes/{quiz}', [QuizController::class, 'show']);
Route::put('quizzes/{quiz}', [QuizController::class, 'update']);
Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy']);
Route::post('quizzes/{quiz}/duplicate', [QuizController::class, 'duplicate']);

// Question routes — public (no auth required)
Route::get('quizzes/{quiz}/questions', [QuizQuestionController::class, 'index']);
Route::post('quizzes/{quiz}/questions', [QuizQuestionController::class, 'store']);
Route::get('questions/{question}', [QuizQuestionController::class, 'show']);
Route::put('questions/{question}', [QuizQuestionController::class, 'update']);
Route::delete('questions/{question}', [QuizQuestionController::class, 'destroy']);

// Quiz submission (auto-grading) — public (no auth required)
Route::post('quizzes/{quiz}/submit', QuizSubmitController::class);

// Ranking routes — public (no auth required)
Route::get('quizzes/{quiz}/rankings', [QuizRankingController::class, 'index']);

// Report routes — public (no auth required)
Route::get('quizzes/{quiz}/report/pdf', [QuizReportController::class, 'exportPdf']);
Route::get('quizzes/{quiz}/report/excel', [QuizReportController::class, 'exportExcel']);

// Public poll routes (no auth required)
Route::get('polls/active', [PollController::class, 'activePolls']);
Route::get('polls/public/{token}', [PollController::class, 'showByToken']);
Route::get('polls/public/{token}/results', [PollController::class, 'publicResults']);
Route::post('polls/public/{token}/vote', [VoteController::class, 'vote']);

// Poll routes — authenticated (teacher only)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);

    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::post('profile/image', [ProfileController::class, 'uploadImage']);

    Route::get('polls', [PollController::class, 'index']);
    Route::post('polls', [PollController::class, 'store']);
    Route::get('polls/{poll}', [PollController::class, 'show']);
    Route::put('polls/{poll}', [PollController::class, 'update']);
    Route::delete('polls/{poll}', [PollController::class, 'destroy']);
    Route::post('polls/{poll}/start', [PollController::class, 'start']);
    Route::post('polls/{poll}/end', [PollController::class, 'end']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('users', [AdminUserController::class, 'index']);
        Route::post('users', [AdminUserController::class, 'store']);
        Route::get('users/{user}', [AdminUserController::class, 'show']);
        Route::put('users/{user}', [AdminUserController::class, 'update']);
        Route::delete('users/{user}', [AdminUserController::class, 'destroy']);
        Route::get('dashboard', [AdminDashboardController::class, 'index']);

        Route::get('schools', [AdminSchoolController::class, 'index']);
        Route::post('schools', [AdminSchoolController::class, 'store']);
        Route::get('schools/{school}', [AdminSchoolController::class, 'show']);
        Route::put('schools/{school}', [AdminSchoolController::class, 'update']);
        Route::delete('schools/{school}', [AdminSchoolController::class, 'destroy']);
        Route::get('schools/lookup/data', [AdminSchoolController::class, 'lookupData']);

        Route::get('roles', [AdminRoleController::class, 'index']);
        Route::post('roles', [AdminRoleController::class, 'store']);
        Route::get('roles/{role}', [AdminRoleController::class, 'show']);
        Route::put('roles/{role}', [AdminRoleController::class, 'update']);
        Route::delete('roles/{role}', [AdminRoleController::class, 'destroy']);
        Route::get('roles/permissions/all', [AdminRoleController::class, 'permissions']);
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
