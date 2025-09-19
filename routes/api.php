<?php

use App\Http\Controllers\Artist\ArtistController;
use App\Http\Controllers\Artist\ArtistPhotoController;
use App\Http\Controllers\ArtistGenreController;
use App\Http\Controllers\ArtistSongController;
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
    Route::apiResource('artists', ArtistController::class);
    Route::apiResource('photos', ArtistPhotoController::class);
    Route::apiResource('songs', ArtistSongController::class);
    Route::apiResource('genres', ArtistGenreController::class);

});
