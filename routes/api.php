<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LecturerStatsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SettingController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Profile routes
    Route::put('/user', [ProfileController::class, 'update']);
    Route::put('/user/password', [ProfileController::class, 'changePassword']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/classes', [ClassController::class, 'index']);
    Route::post('/classes', [ClassController::class, 'store']);

    // Lecturer routes
    Route::get('/lecturer/classes', [ClassController::class, 'getLecturerClasses']);
    Route::post('/lecturer/classes', [ClassController::class, 'store']);
    Route::delete('/lecturer/classes/{id}', [ClassController::class, 'destroy']);

    // Session management
    Route::post('/classes/{id}/sessions/start', [ClassController::class, 'startSession']);
    Route::post('/classes/{id}/sessions/stop', [ClassController::class, 'stopSession']);

    // Material routes
    Route::get('/classes/{classId}/materials', [MaterialController::class, 'index']);
    Route::post('/classes/{classId}/materials', [MaterialController::class, 'store']);
    Route::delete('/materials/{id}', [MaterialController::class, 'destroy']);

    // Assignment routes
    Route::get('/classes/{classId}/assignments', [AssignmentController::class, 'index']);
    Route::post('/classes/{classId}/assignments', [AssignmentController::class, 'store']);
    Route::delete('/assignments/{id}', [AssignmentController::class, 'destroy']);

    // Student routes
    Route::get('/student/classes', [ClassController::class, 'getStudentClasses']);
    Route::post('/student/classes/join', [ClassController::class, 'joinClass']);
    Route::get('/student/classes/{id}', [ClassController::class, 'getStudentClassDetail']);
    Route::get('/classes/{id}/students', [ClassController::class, 'getClassStudents']);
    Route::get('/classes/{id}/active-session', [ClassController::class, 'getActiveSession']);
    Route::get('/session/{sessionId}/attendances', [AttendanceController::class, 'getSessionAttendees']); // Added

    Route::get('/schedules/{class_id}', [ScheduleController::class, 'showByClass']);

    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);
    Route::get('/attendance/today', [AttendanceController::class, 'today']);
    Route::get('/attendance/statistics', [AttendanceController::class, 'statistics']);

    Route::post('/face/register', [FaceController::class, 'register']);
    Route::post('/face/verify', [FaceController::class, 'verify']);

    // Report & Analytics Routes
    Route::get('/lecturer/reports/summary', [ReportController::class, 'summary']);
    Route::get('/lecturer/reports/class/{id}', [ReportController::class, 'classReport']);
    Route::get('/lecturer/reports/export', [ReportController::class, 'export']);

    // Statistics Routes
    Route::get('/lecturer/stats/trends', [LecturerStatsController::class, 'trends']);
    Route::get('/lecturer/stats/comparison', [LecturerStatsController::class, 'comparison']);
    Route::get('/lecturer/stats/distribution', [LecturerStatsController::class, 'distribution']);
    Route::get('/lecturer/stats/methods', [LecturerStatsController::class, 'methods']);
    Route::get('/lecturer/stats/punctuality', [LecturerStatsController::class, 'punctuality']);

    Route::get('/notifications', [NotificationController::class, 'getUserNotifications']);

    Route::get('/settings', [SettingController::class, 'index']);
});
