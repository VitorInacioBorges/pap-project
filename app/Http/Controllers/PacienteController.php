<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Services\PacienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class PacienteController extends Controller
{
    public function index(PacienteService $service): JsonResponse
    {
        return response()->json($service->listar());
    }

    public function show(Paciente $paciente, PacienteService $service): JsonResponse
    {
        return response()->json($service->buscar($paciente));
    }

    public function store(Request $request, PacienteService $service): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:pacientes,email'],
            'senha' => ['required', 'string', 'min:8', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', 'unique:pacientes,cpf'],
        ]);

        $paciente = $service->criar($validated);

        return response()->json($paciente, 201);
    }

    public function update(Request $request, Paciente $paciente, PacienteService $service): JsonResponse
    {
        $validated = $request->validate([
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('pacientes', 'email')->ignore($paciente->getKey(), $paciente->getKeyName()),
            ],
            'senha' => ['sometimes', 'required', 'string', 'min:8', 'max:255'],
            'cpf' => [
                'sometimes',
                'required',
                'string',
                'max:14',
                Rule::unique('pacientes', 'cpf')->ignore($paciente->getKey(), $paciente->getKeyName()),
            ],
        ]);

        return response()->json($service->atualizar($paciente, $validated));
    }

    public function destroy(Paciente $paciente, PacienteService $service): Response
    {
        $service->excluir($paciente);

        return response()->noContent();
    }
}
