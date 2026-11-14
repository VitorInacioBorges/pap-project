<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\PersonalAccessToken;

class MeController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return response()->json($this->formatar($request->user()));
    }

    public function update(Request $request): JsonResponse
    {
        $usuario = $request->user();
        abort_unless($usuario instanceof User, 403);

        $dados = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
        ]);

        $usuario->forceFill(['nome' => $dados['nome']])->save();

        return response()->json($this->formatar($usuario));
    }

    public function logout(Request $request): Response
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->noContent();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatar(User|Paciente $usuario): array
    {
        if ($usuario instanceof Paciente) {
            return [
                'tipo' => 'paciente',
                'nome' => $usuario->dadosPessoais?->nome,
                'email' => $usuario->email,
                'first_login' => (bool) $usuario->first_login,
            ];
        }

        return [
            'tipo' => 'colaborador',
            'nome' => $usuario->nome,
            'email' => $usuario->email,
            'cargo' => $usuario->cargo,
            'alias' => $usuario->alias,
            'first_login' => (bool) $usuario->first_login,
        ];
    }
}
