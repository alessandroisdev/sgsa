<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TotemController;
use App\Http\Controllers\Api\TvController;
use App\Http\Controllers\Api\SseController;

Route::middleware('device_auth')->prefix('v1')->group(function () {
    // Totem Endpoints
    Route::get('/totem/config', [TotemController::class, 'config']);
    Route::post('/totem/ticket', [TotemController::class, 'ticket']);

    // TV Endpoints
    Route::get('/tv/config', [TvController::class, 'config']);
    Route::get('/tv/stream', [SseController::class, 'stream']);
});
