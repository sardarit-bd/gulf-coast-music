<?php

use App\Http\Controllers\Artist\ArtistController;
use App\Http\Controllers\Artist\ArtistPhotoController;
use App\Http\Controllers\Artist\ArtistGenreController;
use App\Http\Controllers\Artist\ArtistSongController;
use App\Http\Controllers\Journalist\NewsPhotoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Journalist\JournalistController;
use App\Http\Controllers\Journalist\NewsController;
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

    //===================================Journalist Routes===================================
    Route::apiResource('journalists', JournalistController::class);
    Route::apiResource('news', NewsController::class);
    Route::prefix('news/{news}')->group(function () {
    Route::get('photos', [NewsPhotoController::class, 'index']);   // list photos for a news
    Route::post('photos', [NewsPhotoController::class, 'store']);  // upload photos
    });
    Route::patch('photos/{photo}', [NewsPhotoController::class, 'update']); // update alt text
    Route::delete('photos/{photo}', [NewsPhotoController::class, 'destroy']); // delete photo

});
