<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AuthController;

// Auth routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (require Bearer token)
Route::middleware('auth:sanctum')->group(function () {
	Route::get('/user', function (\Illuminate\Http\Request $request) {
		return $request->user();
	});
	Route::delete('/user', [AuthController::class, 'destroy']);

	Route::post('/logout', [AuthController::class, 'logout']);

	// Posts routes
	Route::get('/posts', [PostController::class, 'index']);
	Route::get('/posts/{id}', [PostController::class, 'show']);
	Route::post('/posts', [PostController::class, 'store']);
	Route::put('/posts/{id}', [PostController::class, 'update']);
	Route::delete('/posts/{id}', [PostController::class, 'destroy']);

	// Comments routes
	Route::get('/posts/{id}/comments', [CommentController::class, 'index']);
	Route::post('/posts/{id}/comments', [CommentController::class, 'store']);
	Route::get('/comments/{id}', [CommentController::class, 'show']);
	Route::put('/comments/{id}', [CommentController::class, 'update']);
	Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});