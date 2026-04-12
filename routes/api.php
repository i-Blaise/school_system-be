<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\RegisterSchoolController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SchoolProfileController;
use App\Http\Controllers\Api\StudentImportController;
use App\Http\Controllers\Api\TeacherImportController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\Auth\LoginLookupController;
use App\Http\Middleware\ResolveSchool;
use App\Http\Controllers\Api\TeacherProfileController;
use App\Http\Controllers\Api\AttendanceKioskController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AttendanceReportController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TeacherController;

Route::post('/schools/register', RegisterSchoolController::class);
Route::post('/auth/lookup', LoginLookupController::class);

Route::middleware([ResolveSchool::class])->prefix('schools/{school_slug}')->group(function () {
    Route::post('/login', LoginController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', DashboardController::class);

    Route::post('/logout', LogoutController::class);
    Route::get('/me', UserController::class);
    Route::post('/me/profile', UserProfileController::class);

    Route::get('/school/profile', [SchoolProfileController::class, 'show']);
    Route::put('/school/profile', [SchoolProfileController::class, 'update']);
    Route::post('/students/import', StudentImportController::class);
    Route::post('/teachers/import', TeacherImportController::class);
    
    // Teachers (CRUD + drafts)
    Route::post('/teachers', [TeacherController::class, 'store']);
    Route::get('/teachers', [TeacherController::class, 'index']);
    Route::get('/teachers/{id}', [TeacherController::class, 'show']);

    // Profiles (legacy)
    Route::get('/profiles/teachers', [TeacherProfileController::class, 'index']);
    
    // Attendance
    Route::get('/attendance/kiosk/token', [AttendanceKioskController::class, 'generateToken']);
    Route::post('/attendance/clock', [AttendanceController::class, 'clock']);
    Route::post('/attendance/admin/manual-clock', [AttendanceController::class, 'adminClock']);
    Route::get('/attendance/teachers/graphs', [AttendanceReportController::class, 'teacherGraphs']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/mark-read/{id?}', [NotificationController::class, 'markAsRead']);
});
