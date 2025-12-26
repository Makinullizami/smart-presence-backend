<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/classes', [ClassController::class, 'index']);
    Route::post('/classes', [ClassController::class, 'store']);

    Route::get('/schedules/{class_id}', [ScheduleController::class, 'showByClass']);

    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendance/history/{user_id}', [AttendanceController::class, 'history']);

    Route::post('/face/register', [FaceController::class, 'register']);
    Route::post('/face/verify', [FaceController::class, 'verify']);

    Route::get('/report/class/{class_id}', [ReportController::class, 'classReport']);

    Route::get('/notifications/{user_id}', [NotificationController::class, 'getUserNotifications']);

    Route::get('/settings', [SettingController::class, 'index']);
});
