<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenerationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostPublishController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectSeoController;
use App\Http\Controllers\ProjectSeoPdfController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectClusterController;
use App\Http\Controllers\PostQualityController;

// 루트(홈)
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| AUTH PROTECTED WEB ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Projects (CRUD + Stats/SEO/Cluster/Quality/Report)
    Route::get('/projects', [ProjectController::class, 'index'])
        ->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])
        ->name('projects.create');
    Route::post('/projects/store', [ProjectController::class, 'store'])
        ->name('projects.store');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])
        ->name('projects.show');
    Route::get('/projects/{id}/edit', [ProjectController::class, 'edit'])
        ->name('projects.edit');
    Route::post('/projects/{id}/update', [ProjectController::class, 'update'])
        ->name('projects.update');
    Route::delete('/projects/{id}/delete', [ProjectController::class, 'destroy'])
        ->name('projects.destroy');
    Route::get('/projects/{id}/stats', [ProjectController::class, 'stats'])
        ->name('projects.stats');
    Route::get('/projects/{id}/seo', [ProjectSeoController::class, 'index'])
        ->name('projects.seo');
    Route::get('/projects/{id}/seo/pdf', [ProjectSeoPdfController::class, 'generate'])
        ->name('projects.seo.pdf');
    Route::get('/projects/{id}/cluster', [ProjectClusterController::class, 'view'])
        ->name('projects.cluster');
    Route::post('/projects/{id}/cluster/generate', [ProjectClusterController::class, 'generate'])
        ->name('projects.cluster.generate');
    Route::get('/projects/{id}/quality', [ProjectQualityController::class, 'index'])
        ->name('projects.quality');
    Route::get('/projects/{id}/report', [ProjectReportController::class, 'index'])
        ->name('projects.report');

    /*
    |------------------------------
    | AI Generate Routes
    |------------------------------
    */
    Route::get('/generate', [GenerationController::class, 'generatePage'])
        ->name('generate.page');

    Route::post('/generate/titles', [GenerationController::class, 'generateTitles'])
        ->name('generate.titles');

    Route::post('/generate/content', [GenerationController::class, 'generateContent'])
        ->name('generate.content');

    Route::post('/generate/analyze', [GenerationController::class, 'analyzeSEO'])
        ->name('generate.analyze');

    Route::post('/generate/tags', [GenerationController::class, 'generateTags'])
        ->name('generate.tags');

    Route::post('/generate/thumbnail', [GenerationController::class, 'generateThumbnail'])
        ->name('generate.thumbnail');

    Route::post('/generate/internal-links', [GenerationController::class, 'recommendInternalLinks'])
        ->name('generate.internalLinks');

    Route::post('/generate/save-draft', [GenerationController::class, 'saveDraft'])
        ->name('generate.saveDraft');

    Route::post('/generate/save-post', [GenerationController::class, 'savePost'])
        ->name('generate.savePost');

    Route::post('/generate/upgrade', [GenerationController::class, 'upgradeContent'])
        ->name('generate.upgrade');

    Route::post('/generate/quality-check', [GenerationController::class, 'qualityCheck'])
        ->name('generate.qualityCheck');

    /*
    |------------------------------
    | Blog Posts Routes
    |------------------------------
    */
    Route::resource('posts', PostController::class)->except(['store']);

    Route::get('/posts', [PostController::class, 'index'])
        ->name('posts.index');

    Route::get('/posts/{id}', [PostController::class, 'show'])
        ->name('posts.show');

    Route::get('/posts/{id}/versions', [PostController::class, 'versions'])
        ->name('posts.versions');

    Route::get('/posts/{id}/versions/{version}', [PostController::class, 'versionDetail'])
        ->name('posts.versionDetail');

    Route::post('/posts/{id}/versions/{version}/restore', [PostController::class, 'restoreVersion'])
        ->name('posts.versionRestore');

    Route::get('/posts/{id}/health', [PostController::class, 'health'])
        ->name('posts.health');

    Route::post('/posts/{id}/health/fix', [PostController::class, 'fixHealth'])
        ->name('posts.health.fix');

    Route::post('/posts/{id}/generate-title-tests', [GenerationController::class, 'generateTitleCandidates'])
        ->name('posts.generateTitleTests');

    Route::delete('/posts/{id}', [PostController::class, 'destroy'])
        ->name('posts.destroy');

    Route::get('/posts/{id}/edit', [PostController::class, 'edit'])
        ->name('posts.edit');

    Route::put('/posts/{id}', [PostController::class, 'update'])
        ->name('posts.update');

    /*
    |------------------------------
    | Publish to WordPress
    |------------------------------
    */
    Route::post('/posts/{id}/publish/wordpress', [PostPublishController::class, 'publishWordpress'])
        ->name('posts.publish.wp');

    /*
    |------------------------------
    | Profile
    |------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |------------------------------
    | Debug: Local LLM Test
    |------------------------------
    */
    Route::get('/ai/test', function(App\Services\CodexMaxService $ai) {
        return $ai->chat("안녕");
    })->name('ai.test');

});

// Laravel Breeze Auth
require __DIR__.'/auth.php';
