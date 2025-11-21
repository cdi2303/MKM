<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostPublishController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('welcome');
});

// 인증 필요
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
    // 프로젝트 CRUD
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/create', [ProjectController::class, 'create']);
    Route::post('/projects/store', [ProjectController::class, 'store']);

    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::get('/projects/{id}/edit', [ProjectController::class, 'edit']);
    Route::post('/projects/{id}/update', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}/delete', [ProjectController::class, 'destroy']);

    // 프로젝트 통계
    Route::get('/projects/{id}/stats', [ProjectController::class, 'stats']);

    // generate 페이지
    Route::get('/generate', [GenerationController::class, 'generatePage'])->name('generate');

    // posts
    Route::get('/posts', [PostController::class, 'index']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::get('/posts/{id}/versions', [PostController::class, 'versions']);

    // drafts
    Route::get('/drafts', [PostController::class, 'drafts']);
    Route::get('/drafts/{id}', [PostController::class, 'editDraft']);

    // 발행 시스템
    Route::post('/posts/{id}/publish/wordpress', [PostPublishController::class, 'publishWordpress']);
    Route::post('/posts/{id}/publish/tistory',   [PostPublishController::class, 'publishTistory']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
