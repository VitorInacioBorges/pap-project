<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class colaboradorService
{
    /** @param array{nome: string, email: string, cargo: string} $dados */
    public function cria(array $dados): array
    {
        do {
            $identificador = $this->geraIdentificador($dados['cargo']);
        } while (User::withTrashed()->where('alias', $identificador)->exists());
        $senhaTemporaria = Str::password(12);
        $user = User::create([
            ...$dados,
            'alias' => $identificador,
            'senha' => Hash::make($senhaTemporaria),
        ]);

        return [
            ...$user->refresh()->toArray(),
            'senhaTemporaria' => $senhaTemporaria,
        ];

    }

    private function geraIdentificador(string $cargo): string
    {

        $string = Str::random(8);

        return match ($cargo) {
            'medico' => 'MD-'.$string,
            'enfermeiro' => 'EN-'.$string,
            'secretario' => 'SE-'.$string,
            'admin' => 'AD-'.$string,
        };
    }

    public function listarColaboradorPorId(string $id)
    {

        $User = User::where('id_usuario', $id)->first();

        return $User;

    }

    public function atualizarColaborador(string $id, array $dados): User
    {
        return DB::transaction(function () use ($id, $dados): User {
            $user = User::query()->lockForUpdate()->findOrFail($id);

            if (isset($dados['senha'])) {
                $dados['senha'] = Hash::make($dados['senha']);
            }

            if (isset($dados['cargo']) && $dados['cargo'] !== $user->cargo) {
                $dados['alias'] = $this->geraIdentificador($dados['cargo']);
            }

            $user->update($dados);
            if (array_key_exists('ativo', $dados) && (! $user->ativo || $user->wasChanged('ativo'))) {
                $user->tokens()->delete();
            }

            return $user;
        });
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
