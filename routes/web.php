<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/cc', function () {
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear'); // optional, clears compiled views
    Artisan::call('config:cache'); // optional, rebuild config cache

    return response()->json([
        'message' => 'All caches cleared successfully!'
    ]);
});
