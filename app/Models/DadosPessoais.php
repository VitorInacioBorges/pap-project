<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DadosPessoais extends Model
{
    protected $table = 'dados_pessoais';

    protected $primaryKey = 'id_dados_pessoais';

    protected $fillable = [
        'id_paciente',
        'nome',
        'data_nascimento',
        'idade',
        'peso',
        'altura',
        'PA',
        'CA',
        'profissao',
        'escolaridade',
        'estado_civil',
        'telefone',
        'endereco',
    ];

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'id_paciente');
    }
}
