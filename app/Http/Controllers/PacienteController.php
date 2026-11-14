<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Services\PacienteService;
use App\Support\OpcoesPaciente;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        $this->normalizarCpf($request);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', $this->unico('email')],
            'senha' => ['required', 'string', 'min:8', 'max:255'],
            'cpf' => ['bail', 'required', 'string', $this->validarCpf()],
            ...$this->regrasDadosPessoais($request, false),
        ]);

        $dadosPessoais = Arr::pull($validated, 'dados_pessoais');

        return response()->json($service->criar($validated, $dadosPessoais), 201);
    }

    public function update(Request $request, Paciente $paciente, PacienteService $service): JsonResponse
    {
        $this->normalizarCpf($request);

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
                'bail',
                'required',
                'string',
                $this->validarCpf($paciente),
            ],
            ...$this->regrasDadosPessoais($request, $paciente->dadosPessoais()->exists()),
        ]);

        $dadosPessoais = Arr::pull($validated, 'dados_pessoais');

        return response()->json($service->atualizar($paciente, $validated, $dadosPessoais));
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

    public function excluir(Request $request, Paciente $paciente, PacienteService $service): Response
    {
        $nome = trim((string) $paciente->dadosPessoais?->nome);
        $request->validate([
            'confirmacao_nome' => ['required', 'string', Rule::in($nome === '' ? [] : [$nome])],
        ], [
            'confirmacao_nome.required' => 'Digite o nome completo da paciente para confirmar a exclusão.',
            'confirmacao_nome.in' => $nome === ''
                ? 'Cadastre o nome completo da paciente antes de excluí-la.'
                : 'O nome informado não corresponde ao nome completo da paciente.',
        ]);

        $service->excluirDefinitivo($paciente);

        return response()->noContent();
    }

    private function normalizarCpf(Request $request): void
    {
        if (is_string($request->input('cpf'))) {
            $request->merge(['cpf' => OpcoesPaciente::formatarCpf($request->input('cpf'))]);
        }
    }

    /**
     * Valida dados_pessoais só quando vierem, para manter o cadastro sem eles funcionando.
     *
     * @return array<string, array<int, mixed>>
     */
    private function regrasDadosPessoais(Request $request, bool $parcial): array
    {
        if (! $request->has('dados_pessoais')) {
            return [];
        }

        $regras = ['dados_pessoais' => ['array']];

        foreach (DadosPessoaisController::rules($parcial) as $campo => $regra) {
            $regras["dados_pessoais.{$campo}"] = $regra;
        }

        return $regras;
    }

    /**
     * Compara só os dígitos, porque cadastros antigos podem ter o CPF sem máscara.
     */
    private function validarCpf(?Paciente $ignorar = null): Closure
    {
        return function (string $atributo, mixed $valor, Closure $falhar) use ($ignorar) {
            if (! OpcoesPaciente::cpfValido($valor)) {
                $falhar('CPF inválido. Confira os números informados.');

                return;
            }

            $digitos = preg_replace('/\D/', '', (string) $valor);

            $emUso = Paciente::withTrashed()
                ->where('hard_deleted', false)
                ->whereRaw("replace(replace(cpf, '.', ''), '-', '') = ?", [$digitos])
                ->when($ignorar, fn ($query) => $query->whereKeyNot($ignorar->getKey()))
                ->exists();

            if ($emUso) {
                $falhar('Este CPF já está em uso.');
            }
        };
    }

    private function unico(string $coluna): Unique
    {
        return Rule::unique('pacientes', $coluna)
            ->where(fn ($query) => $query->where('hard_deleted', false));
    }
}
