<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExamSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Auth
Route::middleware('auth:sanctum')->group(function() {
    Route::get('me', [AuthController::class, 'currentUser']);
    Route::get('bootstrap', [AuthController::class, 'bootstrap']);
});
Route::get('logout', [AuthController::class, 'logout']);

// Exam-session
Route::middleware('auth:sanctum')->group(function() {
    Route::middleware('proctor-role')->group(function() {
        Route::post('exam-session', [ExamSessionController::class, 'create']);
    });
});
