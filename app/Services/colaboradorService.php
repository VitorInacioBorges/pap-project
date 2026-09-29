<?php

namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class colaboradorService
{
    public function cria(array $dados){
        $identificador = $this->geraIdentificador($dados['cargo']);
        $senhaTemporaria = fake() -> regexify(('[A-Za-z0-9(|#$%&*]{12}'));
        $user = User::create([
            ...$dados, 
            'identificador' => $identificador, 
            'senha'=>Hash::make($senhaTemporaria),
            ]);


        return [
            ...$dados,
            'identificador'=>$identificador,
            'senhaTemporaria'=>$senhaTemporaria
            ];

    }

    private function geraIdentificador(string $cargo){
        

        $string = fake()->regexify('[A-Za-z0-9]{8}');
        return match ($cargo) {
            'medico' => 'MD-' . $string,
            'enfermeiro' => 'EN-' . $string,
            'secretario' => 'SE-' . $string,
            'admin' => 'AD-'.$string,
            };

       

        
    }
}