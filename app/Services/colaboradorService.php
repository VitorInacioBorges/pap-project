<?php

namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class colaboradorService
{
    public function cria(array $dados){
        $identificador = $this->geraIdentificador($dados['cargo']);
        $senhaTemporaria = fake() -> regexify(('[A-Za-z0-9(|#$%&*]{12}'));
        $user = User::create([
            ...$dados, 
            'alias' => $identificador, 
            'senha'=>Hash::make($senhaTemporaria),
            ]);


        return [
            ...$dados,
            'alias'=>$identificador,
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

    public function listarColaboradorPorId(String $id){

        $User = User::where('id_usuario', $id)->first();
        return $User;

    }

    public function atualizarColaborador(string $id, array $dados){
        $user = User::findOrFail($id);
        $identificador = $this->geraIdentificador($dados['cargo']);

        if(isset($dados['senha'])){
            $dados['senha'] = Hash::make($dados['senha']);
        }

        if (isset($dados['cargo']) && $dados['cargo'] !== $user->cargo) {
        $dados['alias'] = $this->geraIdentificador($dados['cargo']);
        }

        $user -> update($dados);

        return $user;
    }

    public function removerColaborador(string $id): void
{
    $user = User::findOrFail($id);

    DB::transaction(function () use ($user) {
        $user->tokens()->delete();
        $user->forceFill(['ativo' => false])->save();
        $user->delete();
    });
}

}