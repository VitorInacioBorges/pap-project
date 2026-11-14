<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request, AuthService $service): JsonResponse
    {
        $dados = $request->validate([
            'alias' => ['nullable', 'required_without:email', 'string'],
            'email' => ['nullable', 'required_without:alias', 'email'],
            'senha' => ['required', 'string'],
        ]);

        if (isset($dados['alias']) === isset($dados['email'])) {
            throw ValidationException::withMessages([
                'alias' => 'Informe alias ou e-mail, mas não ambos.',
                'email' => 'Informe alias ou e-mail, mas não ambos.',
            ]);
        }

        $resposta = $service->autenticar(
            $dados['alias'] ?? null,
            $dados['email'] ?? null,
            $dados['senha'],
        );

        if ($resposta === null) {
            return response()->json(['erro' => 'Credenciais inválidas.'], 401);
        }

        return response()->json($resposta);
    }

    public function alterarSenha(Request $request, AuthService $service): JsonResponse
    {
        $validated = $request->validate([
            'senha_atual' => ['required', 'string'],
            'nova_senha' => ['required', 'string', 'min:8', 'confirmed', 'different:senha_atual'],
        ]);

        $usuario = $request->user();
        abort_unless($usuario instanceof User || $usuario instanceof Paciente, 403);

        $token = $service->alterarSenha(
            $usuario,
            $validated['senha_atual'],
            $validated['nova_senha'],
        );

        return response()->json([
            'message' => 'Senha alterada com sucesso.',
            'first_login' => false,
            'token' => $token,
        ]);
    }
}
