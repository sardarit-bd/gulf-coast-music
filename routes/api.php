<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Models\User;

Route::post('register', [AuthController::class, 'register']);
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
