<?php

namespace App\Services;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @return array{token: string, first_login: bool}|null
     */
    public function autenticar(?string $alias, ?string $email, string $senha): ?array
    {
        $usuario = $alias !== null
            ? User::query()->where('alias', $alias)->first()
            : Paciente::query()->where('email', $email)->first();

        if (! $usuario || ! is_string($usuario->senha) || ! Hash::check($senha, $usuario->senha)) {
            return null;
        }

        if ($usuario instanceof User && ! $usuario->ativo) {
            return null;
        }

        return [
            'token' => $usuario->createToken('token')->plainTextToken,
            'first_login' => (bool) $usuario->first_login,
        ];
    }

    public function alterarSenha(
        User|Paciente $usuario,
        string $senhaAtual,
        string $novaSenha
    ): string {
        return DB::transaction(function () use ($usuario, $senhaAtual, $novaSenha): string {
            $tipoUsuario = $usuario instanceof Paciente ? Paciente::class : User::class;
            $usuario = $tipoUsuario::query()
                ->lockForUpdate()
                ->findOrFail($usuario->getKey());

            if (! is_string($usuario->senha) || ! Hash::check($senhaAtual, $usuario->senha)) {
                throw ValidationException::withMessages([
                    'senha_atual' => 'A senha atual informada está incorreta.',
                ]);
            }

            $usuario->forceFill([
                'senha' => $usuario instanceof User ? Hash::make($novaSenha) : $novaSenha,
                'first_login' => false,
            ])->save();

            $usuario->tokens()->delete();

            if (method_exists($usuario, 'currentAccessToken') && $usuario->currentAccessToken()) {
                $usuario->currentAccessToken()->delete();
            }

            Auth::guard('sanctum')->forgetUser();

            return $usuario->createToken('usuario')->plainTextToken;
        });
    }
}
