<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Classroom\ClassroomQuizController;
use App\Http\Controllers\Api\Classroom\ClassroomRankingController;
use App\Http\Controllers\Api\Classroom\ClassroomSubmissionController;
use App\Http\Controllers\Api\GameHistoryController;
use App\Http\Controllers\Api\GameSessionController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\QuizQuestionController;
use App\Http\Controllers\Api\QuizRankingController;
use App\Http\Controllers\Api\QuizReportController;
use App\Http\Controllers\Api\QuizSubmitController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\SoundController;
use App\Http\Controllers\Api\TeacherDashboardController;
use App\Http\Controllers\Api\VoteController;
use App\Http\Controllers\Api\WheelController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('debug/session', function (Request $r) {
    return response()->json([
        'has_session' => $r->hasSession(),
        'session_id' => $r->hasSession() ? $r->session()->getId() : null,
        'user' => $r->user() ? ['id' => $r->user()->id, 'name' => $r->user()->name, 'role' => $r->user()->role] : null,
        'cookies' => $r->cookies->all(),
        'headers' => [
            'cookie' => $r->header('Cookie'),
            'authorization' => $r->header('Authorization'),
        ],
    ]);
})->middleware('web');

Route::post('auth/register', [RegistrationController::class, 'register'])->middleware('web');
Route::post('login', [AuthController::class, 'login'])->middleware('web');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
});

Route::post('game-sessions', [GameSessionController::class, 'store']);
Route::post('game-sessions/generate-questions', [GameSessionController::class, 'generateQuestions']);
Route::get('game-sessions/join/{joinCode}', [GameSessionController::class, 'showByJoinCode'])->name('game-sessions.join');
Route::post('game-sessions/{gameSession}/validate-answer', [GameSessionController::class, 'validateAnswer'])->name('game-sessions.validate-answer');
Route::get('game-sessions/{gameSession}/leaderboard', [GameSessionController::class, 'leaderboard'])->name('game-sessions.leaderboard');
Route::post('game-sessions/{gameSession}/end', [GameSessionController::class, 'end'])->name('game-sessions.end');
Route::get('game-sessions', [GameSessionController::class, 'index'])->middleware(['web']);

Route::get('quizzes', [QuizController::class, 'index']);
Route::get('quizzes/{quiz}', [QuizController::class, 'show']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('quizzes', [QuizController::class, 'store']);
    Route::put('quizzes/{quiz}', [QuizController::class, 'update']);
    Route::delete('quizzes/{quiz}', [QuizController::class, 'destroy']);
    Route::post('quizzes/{quiz}/duplicate', [QuizController::class, 'duplicate']);

    Route::post('quizzes/{quiz}/questions', [QuizQuestionController::class, 'store']);
    Route::put('questions/{question}', [QuizQuestionController::class, 'update']);
    Route::delete('questions/{question}', [QuizQuestionController::class, 'destroy']);

    Route::get('game-histories', [GameHistoryController::class, 'index']);
    Route::get('game-histories/{id}', [GameHistoryController::class, 'show']);
    Route::get('game-histories/{id}/export/{format}', [GameHistoryController::class, 'export']);

    Route::post('sounds/{sound}/play', [SoundController::class, 'play']);
    Route::get('sounds/history', [SoundController::class, 'history']);

    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
    Route::post('profile/image', [ProfileController::class, 'uploadImage']);

    Route::get('teacher/dashboard-stats', [TeacherDashboardController::class, 'dashboardStats']);
    Route::get('teacher/recent-activities', [TeacherDashboardController::class, 'recentActivities']);
    Route::get('teacher/top-quizzes', [TeacherDashboardController::class, 'topQuizzes']);
    Route::get('teacher/class-configurations', [TeacherDashboardController::class, 'classConfigurations']);
    Route::post('teacher/class-configurations', [TeacherDashboardController::class, 'storeClassConfiguration']);
    Route::put('teacher/class-configurations/{id}', [TeacherDashboardController::class, 'updateClassConfiguration']);
    Route::delete('teacher/class-configurations/{id}', [TeacherDashboardController::class, 'destroyClassConfiguration']);
    Route::get('teacher/students', [TeacherDashboardController::class, 'students']);

    Route::get('polls', [PollController::class, 'index']);
    Route::get('polls/stats', [PollController::class, 'dashboardStats']);
    Route::get('polls/dashboard/stats', [PollController::class, 'dashboardStats']);
    Route::post('polls', [PollController::class, 'store']);
    Route::get('polls/{poll}', [PollController::class, 'show']);
    Route::put('polls/{poll}', [PollController::class, 'update']);
    Route::delete('polls/{poll}', [PollController::class, 'destroy']);
    Route::post('polls/{poll}/start', [PollController::class, 'start']);
    Route::post('polls/{poll}/end', [PollController::class, 'end']);

    Route::get('/wheels', [WheelController::class, 'index']);
    Route::post('/wheels', [WheelController::class, 'store']);
    Route::get('/wheels/{wheel}', [WheelController::class, 'show']);
    Route::put('/wheels/{wheel}', [WheelController::class, 'update']);
    Route::delete('/wheels/{wheel}', [WheelController::class, 'destroy']);
    Route::post('/wheels/{wheel}/participants', [WheelController::class, 'storeParticipant']);
    Route::post('/wheels/{wheel}/participants/import', [WheelController::class, 'importParticipants']);
    Route::delete('/wheels/{wheel}/participants/{participant}', [WheelController::class, 'destroyParticipant']);
    Route::post('/wheels/{wheel}/share-token', [WheelController::class, 'generateShareToken']);
});

Route::get('quizzes/{quiz}/questions', [QuizQuestionController::class, 'index']);

// Public quiz reporting and submissions.
Route::post('quizzes/{quiz}/submit', QuizSubmitController::class);
Route::get('quizzes/{quiz}/rankings', [QuizRankingController::class, 'index']);
Route::get('quizzes/{quiz}/report/pdf', [QuizReportController::class, 'exportPdf']);
Route::get('quizzes/{quiz}/report/excel', [QuizReportController::class, 'exportExcel']);

Route::prefix('v1/classroom')->group(function () {
    Route::get('quizzes', [ClassroomQuizController::class, 'index']);
    Route::post('quizzes', [ClassroomQuizController::class, 'store']);
    Route::get('quizzes/{quiz}', [ClassroomQuizController::class, 'show']);
    Route::put('quizzes/{quiz}', [ClassroomQuizController::class, 'update']);
    Route::delete('quizzes/{quiz}', [ClassroomQuizController::class, 'destroy']);

    Route::post('submissions', [ClassroomSubmissionController::class, 'store']);
    Route::get('submissions', [ClassroomSubmissionController::class, 'index']);
    Route::get('submissions/check', [ClassroomSubmissionController::class, 'check']);

    Route::get('rankings/{quiz}', [ClassroomRankingController::class, 'index']);
});

Route::get('polls/active', [PollController::class, 'activePolls']);
Route::get('polls/public/{token}', [PollController::class, 'showByToken']);
Route::get('polls/public/{token}/results', [PollController::class, 'publicResults']);
Route::get('polls/{poll}/vote/check', [VoteController::class, 'checkVote']);
Route::post('polls/public/{token}/vote', [VoteController::class, 'vote']);
Route::get('polls/{poll}/results', [PollController::class, 'results']);
Route::get('polls/{poll}/qr', [PollController::class, 'qrCode']);
Route::post('polls/{poll}/vote', [VoteController::class, 'voteByPollId']);

Route::get('sounds', [SoundController::class, 'index']);

Route::get('/wheels/shared/{shareToken}', [WheelController::class, 'showShared'])->name('wheels.shared');
Route::post('/wheel/spin', [WheelController::class, 'spin']);
