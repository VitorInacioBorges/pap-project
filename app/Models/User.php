<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class User extends Model
{
    use HasApiTokens;
    protected $primaryKey = 'id_usuario';
    protected $fillable = ['identificador','email', 'senha', 'cargo'];
}
