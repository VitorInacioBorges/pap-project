<?php

namespace App\Services;

use App\Models\DadosPessoais;
use App\Models\Paciente;

class DadosPessoaisService
{
    public function buscarPorPaciente(Paciente $paciente): DadosPessoais
    {
        return $paciente->dadosPessoais()->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function criar(Paciente $paciente, array $dados): ?DadosPessoais
    {
        if ($paciente->dadosPessoais()->exists()) {
            return null;
        }

        return $paciente->dadosPessoais()->create($dados);
    }

    /**
     * @param  array<string, mixed>  $dados
     */
    public function atualizar(Paciente $paciente, array $dados): DadosPessoais
    {
        $dadosPessoais = $this->buscarPorPaciente($paciente);
        $dadosPessoais->update($dados);

        return $dadosPessoais;
    }

    public function excluir(Paciente $paciente): void
    {
        $this->buscarPorPaciente($paciente)->delete();
    }
}
