<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::all();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $dados = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|max:14|unique:patients,cpf',
            'telefone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);

        Patient::create($dados);

        return redirect()
            ->route('patients.index')
            ->with('success', 'Paciente cadastrado com sucesso!');
    }
}
