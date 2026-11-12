<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Paciente extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'pacientes';
    protected $primaryKey = 'id_paciente';

    protected $fillable = ['email', 'senha', 'cpf'];

    protected $hidden = ['senha'];

    protected $casts = [
        'senha' => 'hashed',
    ];

    public function getAuthPassword()
    {
        return $this->senha;
    }
}