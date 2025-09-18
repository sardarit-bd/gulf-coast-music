<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Illuminate\Http\Client\Request;

// Route::post('register', [AuthController::class, 'register']);
Route::post('register', function(Request $req) {
    return response()->json(['message' => 'This is a placeholder for the register route.']);
});
Route::post('login',    [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('me',            [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout',  [AuthController::class, 'logout']);
});


Route::get('check', function () {
    $r = User::all();
    return response()->json(['users' => $r]);
});
