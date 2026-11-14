<?php

namespace App\Services;

use App\Models\Paciente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PacienteService
{
    /**
     * @param  list<string>  $status
     */
    public function listar(?string $busca = null, string $ordem = 'recentes', array $status = ['ativos']): LengthAwarePaginator
    {
        $query = Paciente::query()
            ->select('pacientes.*')
            ->with('dadosPessoais')
            ->where('pacientes.hard_deleted', false);

        $ativos = in_array('ativos', $status, true);
        $inativos = in_array('inativos', $status, true);

        if ($ativos && $inativos) {
            $query->withTrashed();
        } elseif ($inativos) {
            $query->onlyTrashed();
        }

        $busca = trim((string) $busca);

        if ($busca !== '') {
            $digitos = preg_replace('/\D/', '', $busca);

            $query->where(function ($q) use ($busca, $digitos) {
                $q->whereHas('dadosPessoais', function ($dados) use ($busca) {
                    $dados->whereRaw('lower(nome) like ?', ['%'.mb_strtolower($busca).'%']);
                });

                if ($digitos !== '') {
                    $q->orWhereRaw("replace(replace(pacientes.cpf, '.', ''), '-', '') like ?", ['%'.$digitos.'%']);
                }
            });
        }

        match ($ordem) {
            'antigos' => $query->orderBy('pacientes.created_at')->orderBy('pacientes.id_paciente'),
            'alfabetica' => $query
                ->leftJoin('dados_pessoais', 'dados_pessoais.id_paciente', '=', 'pacientes.id_paciente')
                ->orderByRaw('CASE WHEN dados_pessoais.nome IS NULL THEN 1 ELSE 0 END')
                ->orderByRaw('lower(dados_pessoais.nome)')
                ->orderBy('pacientes.id_paciente'),
            default => $query->orderByDesc('pacientes.created_at')->orderByDesc('pacientes.id_paciente'),
        };

        return $query->paginate(15);
    }

    public function buscar(Paciente $paciente): Paciente
    {
        return $paciente->load('dadosPessoais');
    }

    /**
     * @param  array{email: string, senha: string, cpf: string}  $dados
     * @param  array<string, mixed>|null  $dadosPessoais
     */
    public function criar(array $dados, ?array $dadosPessoais = null): Paciente
    {
        return DB::transaction(function () use ($dados, $dadosPessoais) {
            do {
                $codigoPaciente = 'PA-'.fake()->regexify('[A-Za-z0-9]{8}');
            } while (Paciente::withTrashed()->where('codigo_paciente', $codigoPaciente)->exists());

            $paciente = new Paciente($dados);
            $paciente->codigo_paciente = $codigoPaciente;
            $paciente->save();

            if ($dadosPessoais !== null) {
                $paciente->dadosPessoais()->create($dadosPessoais);
            }

            return $paciente->load('dadosPessoais');
        });
    }

    /**
     * @param  array<string, mixed>  $dados
     * @param  array<string, mixed>|null  $dadosPessoais
     */
    public function atualizar(Paciente $paciente, array $dados, ?array $dadosPessoais = null): Paciente
    {
        return DB::transaction(function () use ($paciente, $dados, $dadosPessoais) {
            $paciente->update($dados);

            if ($dadosPessoais !== null) {
                $paciente->dadosPessoais()->updateOrCreate([], $dadosPessoais);
            }

            return $paciente->load('dadosPessoais');
        });
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
