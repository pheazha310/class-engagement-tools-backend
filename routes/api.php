<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\PollController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\SchoolRequestController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [RegistrationController::class, 'register']);

Route::get('countries', [CountryController::class, 'index']);
Route::get('provinces', [ProvinceController::class, 'index']);
Route::get('districts', [DistrictController::class, 'index']);
Route::get('schools', [SchoolController::class, 'index']);
Route::post('school-requests', [SchoolRequestController::class, 'store'])->middleware('throttle:10,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [AuthController::class, 'user']);

    Route::get('polls/active', [PollController::class, 'active'])->withoutMiddleware('auth:sanctum');
    Route::get('polls/{poll}/results', [PollController::class, 'results']);

    Route::apiResource('polls', PollController::class)->except(['show']);
    Route::get('polls/{poll}', [PollController::class, 'show']);

    Route::post('polls/{poll}/start', [PollController::class, 'start']);
    Route::post('polls/{poll}/end', [PollController::class, 'end']);

    Route::post('polls/{poll}/vote', [VoteController::class, 'vote']);

});
