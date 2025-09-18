<?php

use App\Http\Controllers\Artist\ArtistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Http\Request;

Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('me',            [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout',  [AuthController::class, 'logout']);


    //===================================Artist Routes===================================

    Route::prefix('artist')->name('artist.')->group(function () {
        Route::apiResource('profile', ArtistController::class);
        // Route::apiResource('photos', ArtistPhotoController::class);
        // Route::apiResource('songs', ArtistSongController::class);
        // Route::apiResource('genres', ArtistGenreController::class);
    });
});
