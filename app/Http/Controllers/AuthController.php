<?php

namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $dados = $request->validate([
            'identificador' => 'required|string',
            'senha' => 'required|string'
        ]);


        $user = User::where('identificador', $dados['identificador'])->first();

        if(!$user || !Hash::check($dados['senha'], $user->senha)){
            return response()->json(['erro' => 'credenciais invalidads'], 401); 
        }

        $token = $user -> createToken('token')->plainTextToken;

        return response()->json(['token' => $token]);
    }
}
