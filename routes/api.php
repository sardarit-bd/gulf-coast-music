<?php

use App\Http\Controllers\Admin\ProfileAcivitionController;
use App\Http\Controllers\Artist\ArtistController;
use App\Http\Controllers\Artist\ArtistPhotoController;
use App\Http\Controllers\Artist\ArtistSongController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Journalist\NewsPhotoController;
use App\Http\Controllers\Venue\VenueController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Journalist\JournalistController;
use App\Http\Controllers\Journalist\NewsController;
use App\Http\Controllers\Admin\PrintifyController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('me',            [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout',  [AuthController::class, 'logout']);

    //==================================================================================
    //===================================Admin Routes===================================
    //==================================================================================

    Route::get('user/pending',[ProfileAcivitionController::class,'pendingProfiles']);
    Route::get('user/activate/{id}',[ProfileAcivitionController::class,'activateProfile']);



    //===================================Artist Routes===================================
    Route::apiResource('artists', ArtistController::class);
    // updating artist
    Route::put('artist/user/{user_id}', [ArtistController::class, 'update']);
    Route::apiResource('photos', ArtistPhotoController::class);
    Route::apiResource('songs', ArtistSongController::class);

    //===================================Journalist Routes===================================
    Route::apiResource('journalists', JournalistController::class);
    Route::apiResource('news', NewsController::class);
    Route::prefix('news/{news}/photos')->group(function () {
        Route::get('/', [NewsPhotoController::class, 'index']);
        Route::post('/', [NewsPhotoController::class, 'store']);
        Route::patch('{photo}', [NewsPhotoController::class, 'update']);
        Route::delete('{photo}', [NewsPhotoController::class, 'destroy']);
    });

    //===================================Venue Routes===================================
    Route::apiResource('venues', VenueController::class);
    //===================================Event Routes===================================
    Route::apiResource('events', EventController::class);

    //==================================================================================
    //===================================Printify Routes================================
    //==================================================================================
    // Shop
Route::get('/printify/shop', [PrintifyController::class, 'getShop']);

// Products
Route::get('/printify/products', [PrintifyController::class, 'getProducts']);
Route::post('/printify/products', [PrintifyController::class, 'addProduct']);

// Orders
Route::get('/printify/orders', [PrintifyController::class, 'getOrders']);





});
