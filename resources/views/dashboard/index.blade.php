@extends('layouts.app')

@section('title', 'Início · Projeto PAP')

@section('content')
    @php
        $usuario = auth()->user();

        $cargos = [
            'medico'     => 'Médico',
            'enfermeiro' => 'Enfermeiro',
            'secretario' => 'Atendente',
            'admin'      => 'Administrador',
        ];

        $nomeUsuario  = $usuario?->nome ?? $usuario?->identificador;
        $cargoUsuario = $cargos[$usuario?->cargo] ?? $usuario?->cargo;
    @endphp

    {{-- Saudação --}}
    <div class="flex items-center gap-4 md:gap-8">
        <img src="{{ asset('images/user-icon.svg') }}" alt="" class="h-12 w-12 shrink-0 md:h-16 md:w-16">

        <div class="min-w-0">
            <h1 class="font-heading text-2xl font-bold leading-tight text-[#1B1B18] md:text-[40px]">
                Olá, {{ trim($cargoUsuario.' '.$nomeUsuario) }}!
            </h1>
            <p class="mt-1 text-lg text-[#1B1B18] md:text-xl">Escolha o que você quer fazer.</p>
        </div>
    </div>

    {{-- Atalhos --}}
    <div class="mt-10 flex flex-wrap gap-5 md:mt-[72px]">
        <a href="{{ route('patients.index') }}"
           class="flex min-h-[168px] w-full flex-col rounded-2xl border-2 border-[#009A94] bg-[#5ECBC6] p-4 text-[#FFFEFE] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#009A94] focus-visible:ring-offset-2 sm:w-[363px]">
            <img src="{{ asset('images/patients-icon.svg') }}" alt="" class="h-12 w-12">
            <h2 class="font-heading mt-4 text-[22px] font-bold leading-tight">Pacientes</h2>
            <p class="mt-3 text-xl leading-tight">Cadastro e listagem de pacientes.</p>
        </a>

        <a href="{{ route('profile.edit') }}"
           class="flex min-h-[168px] w-full flex-col rounded-2xl border-2 border-[#C4C4C4] bg-white p-4 text-[#1B1B18] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#009A94] focus-visible:ring-offset-2 sm:w-[363px]">
            <img src="{{ asset('images/settings-icon.svg') }}" alt="" class="h-12 w-12">
            <h2 class="font-heading mt-4 text-[22px] font-bold leading-tight">Configurações da conta</h2>
            <p class="mt-3 text-xl leading-tight">Seus dados de acesso.</p>
        </a>
    </div>
@endsection
