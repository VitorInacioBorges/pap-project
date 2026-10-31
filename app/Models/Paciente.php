<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $cpf
 * @property bool|null $hard_deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read DadosPessoais|null $dadosPessoais
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Anamnese> $anamneses
 */
class Paciente extends Model
{
    use SoftDeletes;

    protected $table = 'paciente';

    protected $fillable = [
        'cpf',
        'hard_deleted',
    ];

    protected function casts(): array
    {
        return [
            'hard_deleted' => 'boolean',
        ];
    }

    public function dadosPessoais(): HasOne
    {
        return $this->hasOne(DadosPessoais::class, 'paciente_id');
    }

    public function anamneses(): HasMany
    {
        return $this->hasMany(Anamnese::class, 'paciente_id');
    }
}
