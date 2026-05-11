<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\PostController;
use App\Http\Controllers\V1\CommentController;
use App\Http\Controllers\V1\Auth\AuthController;

Route::prefix('v1')->middleware('throttle:5,1')->group(function () {
    // Auth routes (public)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Protected routes (require Bearer token)
    Route::middleware('auth:sanctum')->group(function () {
        // User routes - semua bisa lihat profil sendiri
        Route::get('/user', function (\Illuminate\Http\Request $request) {
            return $request->user();
        });
        Route::put('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Hanya admin yang bisa delete user dan lihat semua user
        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [AuthController::class, 'index']);
            Route::delete('/user', [AuthController::class, 'destroy']);
        });

        // Posts and Comments GET routes - Admin dan Member
        Route::middleware('role:admin,member')->group(function () {
            Route::get('/posts', [PostController::class, 'index']);
            Route::get('/posts/{id}', [PostController::class, 'show']);
            Route::get('/posts/{id}/comments', [CommentController::class, 'index']);
            Route::get('/comments/{id}', [CommentController::class, 'show']);
        });

        // Posts and Comments modification routes - hanya Admin
        Route::middleware('role:admin')->group(function () {
            Route::post('/posts', [PostController::class, 'store']);
            Route::put('/posts/{id}', [PostController::class, 'update']);
            Route::delete('/posts/{id}', [PostController::class, 'destroy']);
            
            Route::post('/posts/{id}/comments', [CommentController::class, 'store']);
            Route::put('/comments/{id}', [CommentController::class, 'update']);
            Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
        });
    });
});