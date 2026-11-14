<?php

namespace App\Services;

use App\Models\Paciente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PacienteService
{
    public function listar(): LengthAwarePaginator
    {
        return Paciente::query()
            ->with('dadosPessoais')
            ->orderByDesc('created_at')
            ->orderByDesc('id_paciente')
            ->paginate();
    }

    public function buscar(Paciente $paciente): Paciente
    {
        return $paciente->load('dadosPessoais');
    }

    /**
     * @param  array{email: string, senha: string, cpf: string}  $dados
     */
    public function criar(array $dados): Paciente
    {
        do {
            $codigoPaciente = 'PA-'.fake()->regexify('[A-Za-z0-9]{8}');
        } while (Paciente::query()->where('codigo_paciente', $codigoPaciente)->exists());

        $paciente = new Paciente($dados);
        $paciente->codigo_paciente = $codigoPaciente;
        $paciente->save();

        return $paciente;
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(Paciente $paciente, array $dados): Paciente
    {
        $paciente->update($dados);

        return $paciente;
    }

    public function excluir(Paciente $paciente): void
    {
        DB::transaction(function () use ($paciente) {
            $paciente->tokens()->delete();
            $paciente->delete();
        });
    }

    public function reativar(Paciente $paciente): Paciente
    {
        $paciente->restore();

        return $paciente->load('dadosPessoais');
    }

    public function excluirDefinitivo(Paciente $paciente): void
    {
        DB::transaction(function () use ($paciente) {
            $paciente->tokens()->delete();
            $paciente->forceFill(['hard_deleted' => true])->save();

            if (! $paciente->trashed()) {
                $paciente->delete();
            }
        });
    }
}
