<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/import-news', NewsController::class );
Route::get('/prompt', [App\Http\Controllers\ApiNewsController::class, 'getPrompt']);
Route::post('/saveArticle', [App\Http\Controllers\ApiNewsController::class, 'saveArticle']);
Route::get('/uploadImage', [App\Http\Controllers\ApiNewsController::class, 'uploadImage']);

