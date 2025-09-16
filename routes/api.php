<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CourseReviewController;
use App\Http\Controllers\Api\FavoriteCourseController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuestController;
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
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/user-preferences', [UserCategoryPreferencesController::class, 'store']);
    
    // Home routes
    Route::get('/home', [HomeController::class, 'index']);
    Route::get('/home/search', [HomeController::class, 'search']);
    Route::get('/home/category/{categoryId?}', [HomeController::class, 'getCoursesByCategory']);

    // Course routes
    Route::get('/courses', [CourseController::class, 'index']);
    Route::get('/courses/search', [CourseController::class, 'search']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);

    Route::post('/courses/review', [CourseReviewController::class, 'store']);
    Route::post('/courses/enroll', [CourseController::class, 'enroll']);
    Route::post('/courses/start-lesson', [CourseController::class, 'startLesson']);
        Route::post('/lessons/set-active', [CourseController::class, 'setActiveLesson']);
    Route::post('/courses/complete-lesson', [CourseController::class, 'completeLesson']);
    Route::post('/courses/complete-quiz', [CourseController::class, 'completeQuiz']);
    Route::post('/courses/complete', [CourseController::class, 'completeCourse']);
    Route::get('/courses/leaderboard-quiz/{id}', [CourseController::class, 'leaderboardQuiz']);

    Route::post('/favorite-courses', [FavoriteCourseController::class, 'store']);
    Route::get('/favorite-courses', [FavoriteCourseController::class, 'index']);
    Route::delete('/favorites-courses', [FavoriteCourseController::class, 'destroy']);


    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/profile/enrolled-courses', [ProfileController::class, 'enrolledCourses']);

    Route::post('/quests/finish', [QuestController::class, 'finish']);
    Route::get('/quests/daily', [QuestController::class, 'daily']);
    Route::get('/quests/weekly', [QuestController::class, 'weekly']);
});

// Public routes
Route::get('/categories', [CategoryController::class, 'index']);