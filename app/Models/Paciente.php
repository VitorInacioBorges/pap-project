<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Paciente extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'pacientes';

    protected $primaryKey = 'id_paciente';

    protected $fillable = ['email', 'senha', 'cpf', 'first_login'];

    protected $hidden = ['senha'];

    protected $casts = [
        'senha' => 'hashed',
        'first_login' => 'boolean',
        'hard_deleted' => 'boolean',
    ];

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->where($this->qualifyColumn('hard_deleted'), false);
    }

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    public function dadosPessoais(): HasOne
    {
        return $this->hasOne(DadosPessoais::class, 'id_paciente', 'id_paciente');
    }
}
