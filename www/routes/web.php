<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\PriorityController;
use App\Http\Controllers\Admin\CounterController;
use App\Http\Controllers\Admin\TotemController;
use App\Http\Controllers\Admin\TvController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\WebAuthController;

Route::get('/', function () {
    return redirect('/docs/api');
});

Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->middleware('auth')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/metrics', [DashboardController::class, 'metrics'])->name('dashboard.metrics');
    Route::get('/dashboard/units/{unit}/monitor', [DashboardController::class, 'unitMonitor'])->name('dashboard.unitMonitor');

    Route::get('/credentials/print', [App\Http\Controllers\Admin\PrintCredentialController::class, 'index'])->name('credentials.index');
    Route::post('/credentials/print/batch', [App\Http\Controllers\Admin\PrintCredentialController::class, 'printBatch'])->name('credentials.printBatch');

    Route::match(['get', 'post'], 'users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::post('users/store', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::get('users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    Route::match(['get', 'post'], 'units', [UnitController::class, 'index'])->name('units.index');
    Route::post('units/store', [UnitController::class, 'store'])->name('units.store');
    Route::get('units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit');
    Route::put('units/{unit}', [UnitController::class, 'update'])->name('units.update');
    Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');

    Route::match(['get', 'post'], 'areas', [AreaController::class, 'index'])->name('areas.index');
    Route::post('areas/store', [AreaController::class, 'store'])->name('areas.store');
    Route::get('areas/{area}/edit', [AreaController::class, 'edit'])->name('areas.edit');
    Route::put('areas/{area}', [AreaController::class, 'update'])->name('areas.update');
    Route::delete('areas/{area}', [AreaController::class, 'destroy'])->name('areas.destroy');

    Route::match(['get', 'post'], 'services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('services/store', [ServiceController::class, 'store'])->name('services.store');
    Route::get('services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    Route::match(['get', 'post'], 'priorities', [PriorityController::class, 'index'])->name('priorities.index');
    Route::post('priorities/store', [PriorityController::class, 'store'])->name('priorities.store');
    Route::get('priorities/{priority}/edit', [PriorityController::class, 'edit'])->name('priorities.edit');
    Route::put('priorities/{priority}', [PriorityController::class, 'update'])->name('priorities.update');
    Route::delete('priorities/{priority}', [PriorityController::class, 'destroy'])->name('priorities.destroy');

    Route::match(['get', 'post'], 'counters', [CounterController::class, 'index'])->name('counters.index');
    Route::post('counters/store', [CounterController::class, 'store'])->name('counters.store');
    Route::get('counters/{counter}/edit', [CounterController::class, 'edit'])->name('counters.edit');
    Route::put('counters/{counter}', [CounterController::class, 'update'])->name('counters.update');
    Route::delete('counters/{counter}', [CounterController::class, 'destroy'])->name('counters.destroy');

    Route::match(['get', 'post'], 'totems', [TotemController::class, 'index'])->name('totems.index');
    Route::post('totems/store', [TotemController::class, 'store'])->name('totems.store');
    Route::get('totems/{totem}/edit', [TotemController::class, 'edit'])->name('totems.edit');
    Route::put('totems/{totem}', [TotemController::class, 'update'])->name('totems.update');
    Route::delete('totems/{totem}', [TotemController::class, 'destroy'])->name('totems.destroy');

    Route::match(['get', 'post'], 'tvs', [TvController::class, 'index'])->name('tvs.index');
    Route::post('tvs/store', [TvController::class, 'store'])->name('tvs.store');
    Route::get('tvs/{tv}/edit', [TvController::class, 'edit'])->name('tvs.edit');
    Route::put('tvs/{tv}', [TvController::class, 'update'])->name('tvs.update');
    Route::delete('tvs/{tv}', [TvController::class, 'destroy'])->name('tvs.destroy');
});
