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
    
    // Profiles
    Route::get('/profiles/teachers', [TeacherProfileController::class, 'index']);
});
