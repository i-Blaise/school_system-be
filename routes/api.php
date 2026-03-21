<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\RegisterSchoolController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\ResolveSchool;

Route::post('/schools/register', RegisterSchoolController::class);

Route::middleware([ResolveSchool::class])->prefix('schools/{school_slug}')->group(function () {
    Route::post('/login', LoginController::class);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', LogoutController::class);
    Route::get('/me', UserController::class);
});
