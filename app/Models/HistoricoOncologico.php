<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $anamnese_id
 * @property bool|null $historico_cancer
 * @property bool|null $fez_quimioterapia
 * @property bool|null $fez_radioterapia
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Anamnese $anamnese
 */
class HistoricoOncologico extends Model
{
    protected $table = 'historico_oncologico';

    protected $fillable = [
        'anamnese_id',
        'historico_cancer',
        'fez_quimioterapia',
        'fez_radioterapia',
    ];

    protected function casts(): array
    {
        return [
            'historico_cancer' => 'boolean',
            'fez_quimioterapia' => 'boolean',
            'fez_radioterapia' => 'boolean',
        ];
    }

    public function anamnese(): BelongsTo
    {
        return $this->belongsTo(Anamnese::class, 'anamnese_id');
    }
}
