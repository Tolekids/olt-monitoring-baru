<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DeviceController;
use App\Http\Controllers\Api\V1\CliSessionController;
use App\Http\Controllers\Api\V1\SyslogController;
use App\Http\Controllers\Api\V1\SyslogStreamController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->middleware('permission:dashboard.view');
        Route::get('/dashboard/traffic', [DashboardController::class, 'traffic'])->middleware('permission:dashboard.view');
        Route::apiResource('devices', DeviceController::class)->middleware('permission:devices.view');
        Route::post('/devices/{device}/test-connection', [DeviceController::class, 'testConnection'])->middleware('permission:devices.manage');
        Route::get('/syslog-events', [SyslogController::class, 'index'])->middleware('permission:syslog.view');
        Route::get('/syslog-events/stream', SyslogStreamController::class)->middleware('permission:syslog.view');
        Route::post('/devices/{device}/cli-sessions', [CliSessionController::class, 'store'])->middleware('permission:cli.open');
        Route::post('/cli-sessions/{session}/commands', [CliSessionController::class, 'execute'])->middleware('permission:cli.open');
        Route::post('/cli-sessions/{session}/close', [CliSessionController::class, 'close'])->middleware('permission:cli.open');
    });
});
