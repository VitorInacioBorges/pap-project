<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DadosPessoaisController;
use App\Http\Controllers\FichaController;
use App\Http\Controllers\MeController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('clear_sanctum')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
    Route::post('/alterar-senha', [AuthController::class, 'alterarSenha'])
        ->middleware(['auth:sanctum', 'ativo']);
    Route::post('/pacientes/alterar-senha', [AuthController::class, 'alterarSenha'])
        ->middleware(['auth:sanctum', 'ativo']);

    Route::get('/me', [MeController::class, 'show'])->middleware(['auth:sanctum', 'ativo']);
    Route::post('/logout', [MeController::class, 'logout'])->middleware('auth:sanctum');

    Route::middleware(['auth:sanctum', 'ativo', 'primeiro_login'])->group(function () {
        Route::patch('/me', [MeController::class, 'update']);

        Route::get('/users', [UserController::class, 'index'])->middleware('cargo:admin');
        Route::post('/users', [UserController::class, 'criaColaborador'])->middleware('cargo:admin');
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update'])->middleware('cargo:admin');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('cargo:admin');

        Route::middleware('cargo:medico,admin')->group(function () {
            Route::get('/pacientes/{paciente}/fichas', [FichaController::class, 'index'])->withTrashed();
            Route::post('/pacientes/{paciente}/fichas', [FichaController::class, 'store']);
        });

        Route::get('/pacientes', [PacienteController::class, 'index'])->middleware('cargo:secretario,medico,admin');
        Route::get('/pacientes/{paciente}', [PacienteController::class, 'show'])->middleware('cargo:secretario,medico,admin')->withTrashed();

        Route::middleware('cargo:secretario,admin')->group(function () {
            Route::post('/pacientes/{paciente}/reenviar-senha', [PacienteController::class, 'reenviarSenha'])->middleware('throttle:3,1');
            Route::apiResource('pacientes', PacienteController::class)
                ->except(['index', 'show'])
                ->parameters(['pacientes' => 'paciente'])
                ->withTrashed(['show', 'update']);

            Route::post('/pacientes/{paciente}/reativar', [PacienteController::class, 'reativar'])->withTrashed();
            Route::post('/pacientes/{paciente}/excluir', [PacienteController::class, 'excluir'])->withTrashed();

            Route::get('/pacientes/{paciente}/dados-pessoais', [DadosPessoaisController::class, 'show']);
            Route::post('/pacientes/{paciente}/dados-pessoais', [DadosPessoaisController::class, 'store']);
            Route::match(['put', 'patch'], '/pacientes/{paciente}/dados-pessoais', [DadosPessoaisController::class, 'update']);
            Route::delete('/pacientes/{paciente}/dados-pessoais', [DadosPessoaisController::class, 'destroy']);
        });
    });
});
