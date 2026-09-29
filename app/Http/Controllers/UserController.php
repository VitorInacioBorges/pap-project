<?php

namespace App\Http\Controllers;
use App\Mail\senhaMail;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Services\colaboradorService;

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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
