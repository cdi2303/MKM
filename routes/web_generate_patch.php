<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GenerationController;

Route::middleware('auth')->group(function () {
    Route::get('/generate', [GenerationController::class, 'generatePage']);
});
