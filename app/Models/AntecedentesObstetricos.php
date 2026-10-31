<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $anamnese_id
 * @property int|null $quantos_partos_teve
 * @property bool|null $foi_cesarea
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Anamnese $anamnese
 */
class AntecedentesObstetricos extends Model
{
    protected $table = 'antecedentes_obstetricos';

    protected $fillable = [
        'anamnese_id',
        'quantos_partos_teve',
        'foi_cesarea',
    ];

    protected function casts(): array
    {
        return [
            'quantos_partos_teve' => 'integer',
            'foi_cesarea' => 'boolean',
        ];
    }

    public function anamnese(): BelongsTo
    {
        return $this->belongsTo(Anamnese::class, 'anamnese_id');
    }
}
