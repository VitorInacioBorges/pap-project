<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum', 'ativo')->group(function(){
    Route::get('/users', [UserController::class, 'index'])->middleware('cargo:admin');
    Route::post('/users', [UserController::class, 'criaColaborador'])->middleware('cargo:admin');
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('cargo:admin');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('cargo:admin');
});




