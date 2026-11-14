<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DadosPessoaisController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('clear_sanctum')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/alterar-senha', [AuthController::class, 'alterarSenha'])
        ->middleware(['auth:sanctum', 'ativo']);
    Route::post('/pacientes/alterar-senha', [AuthController::class, 'alterarSenha'])
        ->middleware(['auth:sanctum', 'ativo']);

    Route::middleware(['auth:sanctum', 'ativo', 'primeiro_login'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->middleware('cargo:admin');
        Route::post('/users', [UserController::class, 'criaColaborador'])->middleware('cargo:admin');
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update'])->middleware('cargo:admin');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('cargo:admin');

        Route::apiResource('pacientes', PacienteController::class)
            ->parameters(['pacientes' => 'paciente'])
            ->middleware('cargo:secretario');

        Route::get('/pacientes/{paciente}/dados-pessoais', [DadosPessoaisController::class, 'show'])
            ->middleware('cargo:secretario');
        Route::post('/pacientes/{paciente}/dados-pessoais', [DadosPessoaisController::class, 'store'])
            ->middleware('cargo:secretario');
        Route::match(['put', 'patch'], '/pacientes/{paciente}/dados-pessoais', [DadosPessoaisController::class, 'update'])
            ->middleware('cargo:secretario');
        Route::delete('/pacientes/{paciente}/dados-pessoais', [DadosPessoaisController::class, 'destroy'])
            ->middleware('cargo:secretario');
    });
});
