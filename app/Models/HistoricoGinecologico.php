<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $anamnese_id
 * @property string|null $uso_anticoncepcional
 * @property \Illuminate\Support\Carbon|null $ultimo_dia_menstruacao
 * @property bool|null $fez_histerectomia
 * @property bool|null $histerectomia_total
 * @property string|null $alteracoes_reativas
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Anamnese $anamnese
 */
class HistoricoGinecologico extends Model
{
    protected $table = 'historico_ginecologico';

    protected $fillable = [
        'anamnese_id',
        'uso_anticoncepcional',
        'ultimo_dia_menstruacao',
        'fez_histerectomia',
        'histerectomia_total',
        'alteracoes_reativas',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_dia_menstruacao' => 'date',
            'fez_histerectomia' => 'boolean',
            'histerectomia_total' => 'boolean',
        ];
    }

    public function anamnese(): BelongsTo
    {
        return $this->belongsTo(Anamnese::class, 'anamnese_id');
    }
}
