<?php
// Ensure in routes/web.php inside auth middleware:

use App\Http\Controllers\PostController;

Route::middleware(['auth'])->group(function () {
    Route::get('/posts',[PostController::class,'index']);
    Route::get('/posts/{id}',[PostController::class,'show']);
});
