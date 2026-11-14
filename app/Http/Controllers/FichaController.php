<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FichaController extends Controller
{
    public function index(Paciente $paciente): JsonResponse
    {
        return response()->json($paciente->fichas()
            ->with('usuario:id_usuario,nome,alias')
            ->orderByDesc('data_atendimento')
            ->orderByDesc('id')
            ->paginate(15));
    }

    public function store(Request $request, Paciente $paciente): JsonResponse
    {
        $dados = $request->validate([
            'data_atendimento' => ['required', 'date_format:Y-m-d\TH:i:s.v\Z'],
            'observacoes' => ['nullable', 'string', 'max:10000'],
        ]);

        $ficha = $paciente->fichas()->create([
            ...$dados,
            'data_atendimento' => $request->date('data_atendimento')->utc(),
            'usuario_id' => $request->user()->getKey(),
        ]);

        return response()->json($ficha->load('usuario:id_usuario,nome,alias'), 201);
    }
}
