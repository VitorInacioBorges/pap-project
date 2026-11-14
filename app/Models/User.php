<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $primaryKey = 'id_usuario';

    protected $fillable = ['nome', 'alias', 'email', 'senha', 'cargo', 'ativo', 'first_login'];

    protected $hidden = ['senha'];

    use SoftDeletes;

    protected $casts = [
        'ativo' => 'boolean',
        'first_login' => 'boolean',
        'senha' => 'hashed',
    ];
}
