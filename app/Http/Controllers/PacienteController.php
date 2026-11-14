<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Services\PacienteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use Symfony\Component\HttpFoundation\Response;

class PacienteController extends Controller
{
    public function index(Request $request, PacienteService $service): JsonResponse
    {
        $filtros = $request->validate([
            'busca' => ['nullable', 'string', 'max:100'],
            'ordem' => ['nullable', 'in:recentes,antigos,alfabetica'],
            'status' => ['nullable', 'array'],
            'status.*' => ['in:ativos,inativos'],
        ]);

        return response()->json($service->listar(
            $filtros['busca'] ?? null,
            $filtros['ordem'] ?? 'recentes',
            $filtros['status'] ?? ['ativos'],
        ));
    }

    public function show(Paciente $paciente, PacienteService $service): JsonResponse
    {
        return response()->json($service->buscar($paciente));
    }

    public function store(Request $request, PacienteService $service): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', $this->unico('email')],
            'senha' => ['required', 'string', 'min:8', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', $this->unico('cpf')],
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
                $this->unico('email')->ignore($paciente->getKey(), $paciente->getKeyName()),
            ],
            'senha' => ['sometimes', 'required', 'string', 'min:8', 'max:255'],
            'cpf' => [
                'sometimes',
                'required',
                'string',
                'max:14',
                $this->unico('cpf')->ignore($paciente->getKey(), $paciente->getKeyName()),
            ],
        ]);

        return response()->json($service->atualizar($paciente, $validated));
    }

    public function destroy(Paciente $paciente, PacienteService $service): Response
    {
        $service->excluir($paciente);

        return response()->noContent();
    }

    public function reativar(Paciente $paciente, PacienteService $service): JsonResponse
    {
        return response()->json($service->reativar($paciente));
    }

    public function excluir(Paciente $paciente, PacienteService $service): Response
    {
        $service->excluirDefinitivo($paciente);

        return response()->noContent();
    }

    private function unico(string $coluna): Unique
    {
        return Rule::unique('pacientes', $coluna)
            ->where(fn ($query) => $query->where('hard_deleted', false));
    }
}
