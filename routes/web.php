<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/cc', function () {
    // Clear everything
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('config:cache');

    // ASCII/Visual style message
    $ascii = <<<ASCII
╔════════════════════════════════╗
║   ⚡ Laravel Epic Cache Clear ⚡  ║
╠════════════════════════════════╣
║  Config: Cleared               ║
║  Route: Cleared                ║
║  Cache: Cleared                ║
║  Views: Cleared                ║
╠════════════════════════════════╣
║   ✅ All caches cleared!       ║
╚════════════════════════════════╝
ASCII;

    return response()->json([
        'message' => "🚀 SYSTEM HACK INITIATED...",
        'output'  => $ascii
    ]);
});
