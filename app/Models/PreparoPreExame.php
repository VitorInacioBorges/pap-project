<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $anamnese_id
 * @property string|null $medicamentos_tomados
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Anamnese $anamnese
 */
class PreparoPreExame extends Model
{
    protected $table = 'preparo_pre_exame';

    protected $fillable = [
        'anamnese_id',
        'medicamentos_tomados',
    ];

    public function anamnese(): BelongsTo
    {
        return $this->belongsTo(Anamnese::class, 'anamnese_id');
    }
}
