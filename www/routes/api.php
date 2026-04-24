<?php

use App\Http\Controllers\Api\TotemController;
use App\Http\Controllers\Api\TvController;
use App\Http\Controllers\Api\SseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendantController;

Route::prefix('v1')->group(function () {
    
    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Attendant Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        
        Route::get('/counters', [AttendantController::class, 'counters']);
        Route::get('/counters/{counter}/state', [AttendantController::class, 'state']);
        Route::post('/counters/{counter}/call-next', [AttendantController::class, 'callNext']);
        
        Route::post('/queue/{ticket}/recall', [AttendantController::class, 'recall']);
        Route::post('/queue/{ticket}/start', [AttendantController::class, 'start']);
        Route::post('/queue/{ticket}/finish', [AttendantController::class, 'finish']);
        Route::post('/queue/{ticket}/absent', [AttendantController::class, 'absent']);
    });

    // Device Protected Routes
    Route::middleware('device_auth')->group(function () {
        // Totem Endpoints
        Route::get('/totem/config', [TotemController::class, 'config']);
        Route::post('/totem/ticket', [TotemController::class, 'ticket']);

        // TV Endpoints
        Route::get('/tv/config', [TvController::class, 'config']);
        Route::get('/tv/stream', [SseController::class, 'stream']);
    });
});
