<?php

use Illuminate\Support\Facades\Route;

// As telas não autenticam no servidor: cada uma busca os dados na API com o token
// do navegador (resources/js/api.js), e é a API que protege os dados.
Route::redirect('/', '/login');

Route::view('/login', 'auth.login')->name('login');
Route::view('/inicio', 'dashboard.index')->name('inicio');
Route::view('/pacientes', 'patients.index')->name('pacientes');
Route::view('/pacientes/{paciente}/fichas/criar', 'patients.fichas', ['cadastro' => true])->name('fichas.create');
Route::view('/pacientes/{paciente}/fichas', 'patients.fichas', ['cadastro' => false])->name('fichas.index');
Route::view('/configuracoes', 'profile.edit')->name('configuracoes');
Route::view('/paciente', 'portal.index')->name('paciente');
