<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasApiTokens;

    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'identificador',
        'email',
        'senha',
        'cargo',
    ];

    public function anamneses(): HasMany
    {
        return $this->hasMany(Anamnese::class, 'usuario_id', 'id_usuario');
    }
}
