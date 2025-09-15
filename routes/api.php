<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

Route::post('register', [AuthController::class, 'register']);
Route::post('login',    [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('me',            [AuthController::class, 'me']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('logout',  [AuthController::class, 'logout']);
});


Route::get('check', function (\Illuminate\Http\Request $r) {
    try {
        $p = JWTAuth::parseToken()->getPayload();
        return ['ok'=>true,'sub'=>$p->get('sub'),'nbf'=>$p->get('nbf'),'exp'=>$p->get('exp')];
    } catch (\Throwable $e) {
        return ['ok'=>false,'err'=>$e->getMessage()];
    }
});
