<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pacientes')
            ->whereRaw('length(cpf) = 11')
            ->orderBy('id_paciente')
            ->each(function (object $paciente) {
                if (preg_match('/^\d{11}$/', $paciente->cpf) !== 1) {
                    return;
                }

                $formatado = substr($paciente->cpf, 0, 3).'.'.substr($paciente->cpf, 3, 3).'.'
                    .substr($paciente->cpf, 6, 3).'-'.substr($paciente->cpf, 9, 2);

                $conflito = DB::table('pacientes')
                    ->where('cpf', $formatado)
                    ->where('hard_deleted', false)
                    ->exists();

                // Com conflito, o CPF antigo fica como está para alguém resolver à mão.
                if (! $conflito) {
                    DB::table('pacientes')->where('id_paciente', $paciente->id_paciente)->update(['cpf' => $formatado]);
                }
            });
    }

    public function down(): void
    {
        //
    }
};
