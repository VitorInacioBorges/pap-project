<?php

namespace App\Http\Controllers;

use App\Mail\senhaMail;
use App\Models\User;
use App\Services\colaboradorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return User::all();

    }

    public function criaColaborador(Request $request, colaboradorService $service): JsonResponse
    {
        $dados = $request->validate([
            'email' => ['required', 'email', 'max:30', Rule::unique('users', 'email')],
            'cargo' => ['required', Rule::in(['medico', 'secretario'])],
            'nome' => ['required', 'string', 'max:255'],
        ]);

        $usuario = DB::transaction(function () use ($service, $dados) {
            $usuario = $service->cria($dados);
            Mail::to($dados['email'])->queue(new senhaMail($usuario['alias'], $usuario['senhaTemporaria']));

            return $usuario;
        });

        return response()->json($usuario, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, colaboradorService $service)
    {
        $usuario = $service->listarColaboradorPorId($id);

        if ($usuario) {
            return response()->json(['Sucesso: Colaborador encontrado com sucesso', $usuario], 200);
        }

        return response()->json(['erro' => 'id de usuario não encontrado'], 401);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, colaboradorService $service): JsonResponse
    {
        $dados = $request->validate([
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($id, 'id_usuario')],
            'cargo' => ['sometimes', 'in:medico,enfermeiro,secretario,admin'],
            'senha' => ['sometimes', 'string', 'min:8'],
            'ativo' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('ativo', $dados) && ! $dados['ativo'] && (int) $request->user()->getKey() === (int) $id) {
            return response()->json(['message' => 'Você não pode desativar a própria conta.'], 403);
        }

        $user = $service->atualizarColaborador($id, $dados);

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id, colaboradorService $service)
    {
        if ((string) $request->user()->getKey() === $id) {
            return response()->json(['erro' => 'Você não pode excluir a própria conta'], 403);
        }

        $service->removerColaborador($id);

        return response()->noContent();
    }
}
