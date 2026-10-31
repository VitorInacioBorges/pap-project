<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $anamnese_id
 * @property string|null $local_coleta
 * @property string|null $observacoes_local
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Anamnese $anamnese
 */
class LocalAnamnese extends Model
{
    protected $table = 'local_anamnese';

    protected $fillable = [
        'anamnese_id',
        'local_coleta',
        'observacoes_local',
    ];

    public function anamnese(): BelongsTo
    {
        return $this->belongsTo(Anamnese::class, 'anamnese_id');
    }
}
