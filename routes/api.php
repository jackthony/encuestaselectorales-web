<?php

use App\Http\Controllers\Api\SurveyRoundController;
use App\Http\Controllers\Api\TerritoryController;
use App\Http\Controllers\Api\VoteController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::get('/health', static fn (): JsonResponse => response()->json([
    'status' => 'ok',
]));

Route::get('/territories/search', [TerritoryController::class, 'search']);
Route::get('/survey-rounds', [SurveyRoundController::class, 'index']);
Route::get('/territories/{territory}/survey-round', [SurveyRoundController::class, 'territory']);

Route::post('/votes', [VoteController::class, 'store'])->middleware('throttle:votes');
Route::post('/votar.php', [VoteController::class, 'legacy'])->middleware('throttle:votes');
