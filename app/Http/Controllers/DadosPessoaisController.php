<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Services\DadosPessoaisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DadosPessoaisController extends Controller
{
    public function show(Paciente $paciente, DadosPessoaisService $service): JsonResponse
    {
        return response()->json($service->buscarPorPaciente($paciente));
    }

    public function store(
        Request $request,
        Paciente $paciente,
        DadosPessoaisService $service
    ): JsonResponse {
        $validated = $request->validate($this->rules());
        $dadosPessoais = $service->criar($paciente, $validated);

        if ($dadosPessoais === null) {
            return response()->json([
                'message' => 'Este paciente já possui dados pessoais cadastrados.',
            ], 409);
        }

        return response()->json($dadosPessoais, 201);
    }

    public function update(
        Request $request,
        Paciente $paciente,
        DadosPessoaisService $service
    ): JsonResponse {
        $validated = $request->validate($this->rules(true));

        return response()->json($service->atualizar($paciente, $validated));
    }

    public function destroy(Paciente $paciente, DadosPessoaisService $service): Response
    {
        $service->excluir($paciente);

        return response()->noContent();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(bool $sometimes = false): array
    {
        $required = $sometimes ? 'sometimes' : 'required';

        return [
            'nome' => [$required, 'string', 'max:255'],
            'data_nascimento' => [$required, 'date'],
            'idade' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:150'],
            'peso' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'altura' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'PA' => ['sometimes', 'nullable', 'string', 'max:20'],
            'CA' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'profissao' => ['sometimes', 'nullable', 'string', 'max:255'],
            'escolaridade' => ['sometimes', 'nullable', 'string', 'max:255'],
            'estado_civil' => ['sometimes', 'nullable', 'string', 'max:255'],
            'telefone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'endereco' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
