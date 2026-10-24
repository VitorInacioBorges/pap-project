<?php

use Illuminate\Support\Facades\Route;

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);

// TEMP-FRONT-START (remover antes do commit)
Route::view('/dashboard', 'dashboard.index')->name('dashboard');
Route::get('/patients', fn () => 'em construção')->name('patients.index');
Route::get('/profile', fn () => 'em construção')->name('profile.edit');
Route::post('/logout', fn () => redirect()->route('dashboard'))->name('logout');
// TEMP-FRONT-END