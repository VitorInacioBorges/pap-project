<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dados_pessoais', function (Blueprint $table) {
            $table->id('id_dados_pessoais');
            $table->foreignId('id_paciente')
                ->unique()
                ->constrained('pacientes', 'id_paciente')
                ->cascadeOnDelete();
            $table->string('nome');
            $table->date('data_nascimento');
            $table->unsignedSmallInteger('idade')->nullable();
            $table->decimal('peso', 8, 2)->nullable();
            $table->decimal('altura', 5, 2)->nullable();
            $table->string('PA', 20)->nullable();
            $table->decimal('CA', 8, 2)->nullable();
            $table->string('profissao')->nullable();
            $table->string('escolaridade')->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('telefone', 30)->nullable();
            $table->text('endereco')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dados_pessoais');
    }
};
