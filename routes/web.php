<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController; //vitor

// TEMP-FRONT-START: rotas provisórias só para abrir as telas no navegador.
// Ainda não há controllers web nem model de paciente: as rotas de envio
// apenas redirecionam. Substituir pelas rotas reais quando o backend existir.
// As rotas da API (login, usuários) continuam em routes/api.php.
Route::redirect('/', '/login');

Route::view('/login', 'auth.login')->name('login');
Route::post('/login', fn () => redirect()->route('dashboard'));
Route::post('/logout', fn () => redirect()->route('login'))->name('logout');

Route::view('/dashboard', 'dashboard.index')->name('dashboard');

Route::view('/profile', 'profile.edit')->name('profile.edit');
Route::put('/profile', fn () => redirect()->route('profile.edit')->with('success', 'Prévia: nada foi salvo.'))->name('profile.update');

//vitor
Route::get('/patients', [PatientController::class, 'index'])
    ->name('patients.index');

Route::get('/patients/create', [PatientController::class, 'create'])
    ->name('patients.create');

Route::post('/patients', [PatientController::class, 'store'])
    ->name('patients.store');
Route::post('/patients', fn () => redirect()->route('patients.index')->with('success', 'Prévia: nada foi salvo.'))->name('patients.store');
Route::get('/patients/{patient}/edit', fn () => redirect()->route('patients.index'))->name('patients.edit');
Route::patch('/patients/{patient}/toggle', fn () => redirect()->route('patients.index'))->name('patients.toggle');
// TEMP-FRONT-END
