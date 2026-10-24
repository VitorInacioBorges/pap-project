<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use HasApiTokens;
    protected $primaryKey = 'id_usuario';
    protected $fillable = ['alias','email', 'senha', 'cargo'];
    use SoftDeletes;
}
