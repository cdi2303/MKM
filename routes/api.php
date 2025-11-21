<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenerationController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/generate-titles', [GenerationController::class, 'generateTitles']);
    Route::post('/generate-content', [GenerationController::class, 'generateContent']);
    Route::post('/generate-image', [GenerationController::class, 'generateImage']);
    Route::post('/save-post', [GenerationController::class, 'savePost']);
    Route::post('/save-draft', [GenerationController::class, 'saveDraft']);
    Route::get('/posts/{id}/versions/{version}', [PostController::class, 'versionDetail']);
    Route::post('/posts/{id}/versions/{version}/restore', [PostController::class, 'restoreVersion']);
    Route::post('/analyze-seo', [GenerationController::class, 'analyzeSEO'])
    ->middleware('auth:sanctum');

});
