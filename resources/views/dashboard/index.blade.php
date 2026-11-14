@extends('layouts.app')

@section('title', 'Início · Projeto PAP')

@push('scripts')
    @vite('resources/js/pages/inicio.js')
@endpush

@section('content')
    <div class="flex items-center gap-4 md:gap-5">
        <span class="relative h-12 w-12 shrink-0 overflow-hidden rounded-full bg-pap md:h-14 md:w-14" aria-hidden="true">
            <img src="{{ asset('images/user-icon.svg') }}" alt="" class="absolute inset-x-0 bottom-0 mx-auto h-[85%] w-[85%] object-contain object-bottom">
        </span>

        <div class="min-w-0">
            <h1 class="font-heading text-2xl font-bold leading-tight md:text-4xl">
                Olá, <span data-cargo-rotulo></span> <span data-usuario-nome></span>!
            </h1>
            <p class="mt-1 text-base md:text-lg">Escolha o que você quer fazer.</p>
        </div>
    </div>

    <div class="mt-10 grid gap-4 sm:grid-cols-[repeat(auto-fill,minmax(270px,280px))] md:mt-14">
        <a href="{{ route('pacientes') }}" data-so-cargos="secretario,admin" hidden
           class="group flex min-h-32 flex-col rounded-2xl border-2 border-black/15 bg-white p-4 text-tinta transition duration-200 hover:-translate-y-0.5 hover:border-pap hover:bg-pap-cartao hover:text-white hover:shadow-md active:translate-y-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-pap focus-visible:ring-offset-2">
            <span class="h-9 w-9 bg-pap transition group-hover:bg-white" style="mask: url('{{ asset('images/patients-icon.svg') }}') center / contain no-repeat; -webkit-mask: url('{{ asset('images/patients-icon.svg') }}') center / contain no-repeat;" aria-hidden="true"></span>
            <span class="mt-3 font-heading text-lg font-bold leading-tight">Pacientes</span>
            <span class="mt-1 text-base leading-snug">Cadastro e listagem de pacientes.</span>
        </a>

        <a href="{{ route('configuracoes') }}"
           class="group flex min-h-32 flex-col rounded-2xl border-2 border-black/15 bg-white p-4 text-tinta transition duration-200 hover:-translate-y-0.5 hover:border-pap hover:bg-pap-cartao hover:text-white hover:shadow-md active:translate-y-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-pap focus-visible:ring-offset-2">
            <span class="h-9 w-9 bg-pap transition group-hover:bg-white" style="mask: url('{{ asset('images/settings-icon.svg') }}') center / contain no-repeat; -webkit-mask: url('{{ asset('images/settings-icon.svg') }}') center / contain no-repeat;" aria-hidden="true"></span>
            <span class="mt-3 font-heading text-lg font-bold leading-tight">Configurações da conta</span>
            <span class="mt-1 text-base leading-snug">Seus dados de acesso.</span>
        </a>
    </div>
@endsection
