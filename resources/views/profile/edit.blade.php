@extends('layouts.app')

@section('title', 'Configurações da conta · Projeto PAP')

@push('scripts')
    @vite('resources/js/pages/configuracoes.js')
@endpush

@php
    $campo = 'mt-1 h-12 w-full rounded-xl border-2 border-pap/35 bg-white px-3 text-base transition placeholder:text-black/40 focus:border-pap focus:outline-none aria-invalid:border-perigo';
    $campoSenha = 'mt-1 h-12 w-full rounded-xl border-2 border-black/10 bg-campo px-3 text-base transition placeholder:text-black/40 focus:border-pap focus:bg-white focus:outline-none aria-invalid:border-perigo';
    $campoLeitura = 'mt-1 h-12 w-full cursor-default rounded-xl border-2 border-pap/35 bg-white px-3 text-base font-semibold text-pap focus:border-pap focus:outline-none';
    $rotulo = 'block font-heading text-sm font-bold';
    $erro = 'mt-1 text-sm text-perigo';
@endphp

@section('content')
    <h1 class="font-heading text-3xl font-bold leading-tight md:text-4xl">Configurações de conta</h1>
    <p class="mt-1 text-base md:text-lg">Seus dados de acesso ao sistema.</p>

    <div id="aviso-primeiro-acesso" role="status" class="mt-6 rounded-2xl border-2 border-pap bg-white px-5 py-4 font-semibold text-pap-escuro" hidden>
        Troque a senha temporária para continuar.
    </div>

    <form id="form-configuracoes" novalidate class="mt-8 rounded-2xl border-2 border-pap bg-white p-5 md:mt-10 md:p-6">
        <div class="grid gap-x-3 gap-y-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="campo-nome" class="{{ $rotulo }}">Nome Completo</label>
                <input id="campo-nome" name="nome" type="text" autocomplete="name" maxlength="255"
                       aria-describedby="erro-nome" class="{{ $campo }}">
                <p id="erro-nome" data-erro="nome" class="{{ $erro }}" hidden></p>
            </div>

            <div>
                <label for="campo-cargo" class="{{ $rotulo }}">Cargo</label>
                <input id="campo-cargo" type="text" readonly class="{{ $campoLeitura }}">
            </div>

            <div>
                <label for="campo-identificacao" class="{{ $rotulo }}">Identificação</label>
                <input id="campo-identificacao" type="text" readonly class="{{ $campoLeitura }}">
            </div>

            <div class="md:col-span-2">
                <label for="campo-senha-atual" class="{{ $rotulo }}">Senha Atual</label>
                <input id="campo-senha-atual" name="senha_atual" type="password" autocomplete="current-password" required
                       placeholder="••••••••" aria-describedby="erro-senha-atual dica-senha-atual" class="{{ $campoSenha }}">
                <p id="dica-senha-atual" class="mt-1 text-sm text-black/60">Informe sua senha atual para confirmar as alterações.</p>
                <p id="erro-senha-atual" data-erro="senha_atual" class="{{ $erro }}" hidden></p>
            </div>

            <div>
                <label for="campo-nova-senha" class="{{ $rotulo }}">Nova Senha</label>
                <input id="campo-nova-senha" name="nova_senha" type="password" autocomplete="new-password" minlength="8"
                       placeholder="••••••••" aria-describedby="erro-nova-senha dica-nova-senha" class="{{ $campoSenha }}">
                <p id="dica-nova-senha" class="mt-1 text-sm text-black/60">Opcional. Deixe em branco para manter a senha atual.</p>
                <p id="erro-nova-senha" data-erro="nova_senha" class="{{ $erro }}" hidden></p>
            </div>

            <div>
                <label for="campo-confirmacao" class="{{ $rotulo }}">Confirmar Nova Senha</label>
                <input id="campo-confirmacao" name="nova_senha_confirmation" type="password" autocomplete="new-password"
                       placeholder="••••••••" aria-describedby="erro-confirmacao" class="{{ $campoSenha }}">
                <p id="erro-confirmacao" data-erro="nova_senha_confirmation" class="{{ $erro }}" hidden></p>
            </div>
        </div>

        <p id="mensagem-configuracoes" role="status" class="mt-5 rounded-lg px-3 py-2 text-sm font-semibold" hidden></p>

        <button type="submit" id="botao-salvar"
                class="mt-5 h-12 w-full rounded-xl bg-pap font-heading text-lg font-bold text-white transition hover:bg-pap-escuro active:scale-[0.99] disabled:cursor-wait disabled:opacity-70 focus:outline-none focus-visible:ring-2 focus-visible:ring-tinta focus-visible:ring-offset-2">
            Salvar Alterações
        </button>
    </form>
@endsection
