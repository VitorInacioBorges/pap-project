{{--
    Tela: Configurações da conta (route 'profile.edit')

    Premissas (confirmar com o backend):
    - Envio: PUT para route('profile.update').
    - Somente a senha é editável: campos 'senha' e 'senha_confirmation'
      (a regra 'confirmed' do Laravel devolve o erro no campo 'senha').
    - Nome, cargo e identificação são apenas exibidos (sem atributo name, não são enviados).
    - A view usa auth()->user() com 'nome', 'cargo' e 'identificador'
      ('nome' ainda não existe na tabela users).
--}}
@extends('layouts.app')

@section('title', 'Configurações da conta · Projeto PAP')

@section('content')
    @php
        $usuario = auth()->user();

        $cargos = [
            'medico'     => 'Médico',
            'enfermeiro' => 'Enfermeiro',
            'secretario' => 'Atendente',
            'admin'      => 'Administrador',
        ];

        $nomeUsuario   = $usuario?->nome;
        $cargoUsuario  = $cargos[$usuario?->cargo] ?? $usuario?->cargo;
        $identificacao = $usuario?->identificador;

        $campoLeitura = 'block h-[57px] w-full rounded-2xl border border-[#5ECBC6] bg-white px-4 text-xl font-semibold text-[#5ECBC6] focus:outline-none';
        $campoSenha   = 'block h-[58px] w-full rounded-2xl border bg-[#F2F2F2] px-4 text-xl text-[#1B1B18] placeholder:text-[#B3B3B3] focus:border-[#009A94] focus:outline-none focus:ring-1 focus:ring-[#009A94]';
        $rotulo       = 'font-heading mb-3 block text-xl font-bold leading-6 text-[#1B1B18]';
    @endphp

    <div>
        <h1 class="font-heading text-2xl font-bold leading-tight text-[#1B1B18] md:text-[40px]">Configurações de conta</h1>
        <p class="mt-1 text-lg text-[#1B1B18] md:text-xl">Seus dados de acesso ao sistema.</p>
    </div>

    <form method="POST"
          action="{{ route('profile.update') }}"
          class="mt-10 rounded-[20px] border-2 border-[#009A94] bg-white p-6 md:mt-[72px] md:p-8"
          novalidate>
        @csrf
        @method('PUT')

        <div class="grid gap-x-4 gap-y-[18px] md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="nome" class="{{ $rotulo }}">Nome Completo</label>
                <input type="text" id="nome" value="{{ $nomeUsuario }}" readonly class="{{ $campoLeitura }}">
            </div>

            <div>
                <label for="cargo" class="{{ $rotulo }}">Cargo</label>
                <input type="text" id="cargo" value="{{ $cargoUsuario }}" readonly class="{{ $campoLeitura }}">
            </div>

            <div>
                <label for="identificacao" class="{{ $rotulo }}">Identificação</label>
                <input type="text" id="identificacao" value="{{ $identificacao }}" readonly class="{{ $campoLeitura }}">
            </div>

            <div>
                <label for="senha" class="{{ $rotulo }}">Nova Senha</label>
                <input type="password"
                       id="senha"
                       name="senha"
                       placeholder="********"
                       autocomplete="new-password"
                       @error('senha') aria-invalid="true" aria-describedby="senha-erro" @enderror
                       class="{{ $campoSenha }} @error('senha') border-red-600 @else border-[#C4C4C4] @enderror">
                @error('senha')
                    <p id="senha-erro" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="senha_confirmation" class="{{ $rotulo }}">Confirmar Nova Senha</label>
                <input type="password"
                       id="senha_confirmation"
                       name="senha_confirmation"
                       placeholder="********"
                       autocomplete="new-password"
                       @error('senha_confirmation') aria-invalid="true" aria-describedby="senha-confirmation-erro" @enderror
                       class="{{ $campoSenha }} @error('senha_confirmation') border-red-600 @else border-[#C4C4C4] @enderror">
                @error('senha_confirmation')
                    <p id="senha-confirmation-erro" class="mt-2 text-sm font-semibold text-red-700">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <button type="submit"
                class="font-heading mt-8 h-[61px] w-full rounded-[20px] bg-[#009A94] text-[22px] font-bold text-[#FFFEFE] transition hover:brightness-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#009A94] focus-visible:ring-offset-2">
            Salvar Alterações
        </button>
    </form>
@endsection
