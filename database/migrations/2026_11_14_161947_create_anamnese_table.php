<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anamnese', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes', 'id_paciente');
            $table->foreignId('usuario_id')->constrained('users', 'id_usuario');
            $table->dateTime('data_atendimento');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['paciente_id', 'data_atendimento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anamnese');
    }
};
