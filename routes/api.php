<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/users', [UserController::class, 'index'])->middleware('cargo:admin');
    Route::post('/users', [UserController::class, 'criaColaborador'])->middleware('cargo:admin');
});




