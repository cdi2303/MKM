<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PostController;

Route::get('/', function () {
    return view('welcome');
});

// 인증 필요
Route::middleware(['auth'])->group(function () {

    // 프로젝트 CRUD
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/create', [ProjectController::class, 'create']);
    Route::post('/projects/store', [ProjectController::class, 'store']);

    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::get('/projects/{id}/edit', [ProjectController::class, 'edit']);
    Route::post('/projects/{id}/update', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}/delete', [ProjectController::class, 'destroy']);
    Route::get('/projects/{id}/stats', [ProjectController::class, 'stats']);


    // generate 페이지
    Route::get('/generate', [GenerationController::class, 'generatePage']);


    // posts 시스템
    Route::get('/posts', [App\Http\Controllers\PostController::class, 'index']);
    Route::get('/posts/{id}', [App\Http\Controllers\PostController::class, 'show']);
    Route::get('/posts/{id}/versions', [PostController::class, 'versions']);

    Route::get('/drafts', [PostController::class, 'drafts']);
    Route::get('/drafts/{id}', [PostController::class, 'editDraft']);

    Route::post('/posts/{id}/publish/wordpress', [PostPublishController::class, 'publishWordpress']);
    Route::post('/posts/{id}/publish/tistory',   [PostPublishController::class, 'publishTistory']);
});

require __DIR__.'/auth.php';
