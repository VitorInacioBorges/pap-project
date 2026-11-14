<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->boolean('hard_deleted')->default(false);
        });

        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropUnique(['cpf']);
            $table->dropUnique(['email']);
        });

        // Índice parcial: CPF e e-mail de paciente excluída ficam livres para um novo cadastro.
        DB::statement('CREATE UNIQUE INDEX pacientes_cpf_ativo_unique ON pacientes (cpf) WHERE NOT hard_deleted');
        DB::statement('CREATE UNIQUE INDEX pacientes_email_ativo_unique ON pacientes (email) WHERE NOT hard_deleted');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX pacientes_cpf_ativo_unique');
        DB::statement('DROP INDEX pacientes_email_ativo_unique');

        Schema::table('pacientes', function (Blueprint $table) {
            $table->unique('cpf');
            $table->unique('email');
            $table->dropColumn('hard_deleted');
        });
    }
};
