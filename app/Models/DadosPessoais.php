<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $paciente_id
 * @property string $nome
 * @property int $idade
 * @property float|null $altura
 * @property float|null $ca
 * @property string|null $profissao
 * @property string|null $escolaridade
 * @property string|null $estado_civil
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Paciente $paciente
 */
class DadosPessoais extends Model
{
    protected $table = 'dados_pessoais';

    protected $fillable = [
        'paciente_id',
        'nome',
        'idade',
        'altura',
        'ca',
        'profissao',
        'escolaridade',
        'estado_civil',
    ];

    protected function casts(): array
    {
        return [
            'idade' => 'integer',
            'altura' => 'decimal:2',
            'ca' => 'decimal:2',
        ];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }
}
