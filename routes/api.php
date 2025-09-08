<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserCategoryPreferencesController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('resend-verification', [AuthController::class, 'resendVerification']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::get('me', [AuthController::class, 'me']);

        // Home routes
        Route::get('/home', [HomeController::class, 'index']);
        Route::get('/home/category/{categoryId}', [HomeController::class, 'getCoursesByCategory']);
        Route::get('/home/enrolled-courses', [HomeController::class, 'getEnrolledCourses']);
    });
});

// Public routes
Route::post('/user-preferences', action: [UserCategoryPreferencesController::class, 'store']);
Route::get('/categories', [CategoryController::class, 'index']);