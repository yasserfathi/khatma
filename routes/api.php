<?php

use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\TilawaKhatmaAssignmentController;
use App\Http\Controllers\Api\ZikrKhatmaAssignmentController;
use App\Http\Controllers\Api\ZikrGroupParticipantController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KhatmaController;
use App\Http\Controllers\Api\GroupReadingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('groups', GroupController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('khatmas', KhatmaController::class);
    Route::apiResource('khatma-assignments', TilawaKhatmaAssignmentController::class);
    Route::apiResource('tilawa-khatma-assignments', TilawaKhatmaAssignmentController::class);
    Route::apiResource('zikr-khatma-assignments', ZikrKhatmaAssignmentController::class);
    Route::post('zikr-group-participants/bulk', [ZikrGroupParticipantController::class, 'bulkUpdate']);
    Route::apiResource('zikr-group-participants', ZikrGroupParticipantController::class);
    Route::apiResource('group-readings', GroupReadingController::class);
});
