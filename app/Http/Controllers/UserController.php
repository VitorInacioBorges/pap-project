<?php

namespace App\Http\Controllers;
use App\Mail\senhaMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Services\colaboradorService;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
   
    public function index()
    {
        return User::all();

    }

    public function criaColaborador(Request $request,  ColaboradorService $service)
    {
        $dados = $request->validate([
            'email' => 'required|email',
            'cargo' => 'required|string',
        ]);

        $usuario = $service->cria($dados);

        Mail::to($dados['email'])->send(new senhaMail($usuario["identificador"],$usuario['senhaTemporaria']));
        

        return response()->json($request->user(),201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id,  ColaboradorService $service)
    {
        $usuario = $service -> listarColaboradorPorId($id);
        
        if($usuario){
            return response()->json(["Sucesso: Colaborador encontrado com sucesso", $usuario], 200);
        }
        return response()->json(['erro' => 'id de usuario não encontrado'], 401); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id, colaboradorService $service)
    {
        $dados = $request->validate([
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($id, 'id_usuario')],
            'cargo' => ['sometimes', 'in:medico,enfermeiro,secretario,admin'],
            'senha' => ['sometimes', 'string', 'min:8'],
            'ativo' => ['sometimes', 'boolean']
        ]);

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
