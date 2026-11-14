@extends('layouts.app')

@section('title', 'Colaboradores · Projeto PAP')
@section('cargos', 'admin')

@push('scripts')
    @vite('resources/js/pages/colaboradores.js')
@endpush

@section('content')
    <h1 class="font-heading text-3xl font-bold md:text-4xl">Colaboradores</h1>
    <p class="mt-2 text-lg">Cadastre médicos e atendentes para acessar o Projeto PAP.</p>
    <form id="form-colaborador" class="mt-6 rounded-2xl bg-white p-5 md:p-7">
        <h2 class="font-heading text-xl font-bold">Novo colaborador</h2>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            @foreach (['nome' => 'Nome completo', 'email' => 'E-mail'] as $campo => $rotulo)
                <div>
                    <label for="colaborador-{{ $campo }}" class="block font-heading text-sm font-bold">{{ $rotulo }} <span class="text-perigo">*</span></label>
                    <input id="colaborador-{{ $campo }}" name="{{ $campo }}" type="{{ $campo === 'email' ? 'email' : 'text' }}" required maxlength="{{ $campo === 'email' ? 30 : 255 }}"
                           aria-describedby="erro-colaborador-{{ $campo }}" class="mt-1 min-h-11 w-full rounded-xl border-2 border-black/10 bg-campo px-3 focus:border-pap focus:outline-none aria-invalid:border-perigo">
                    <p id="erro-colaborador-{{ $campo }}" data-erro="{{ $campo }}" class="mt-1 text-sm text-perigo" hidden></p>
                </div>
            @endforeach
            <div>
                <label for="colaborador-cargo" class="block font-heading text-sm font-bold">Cargo <span class="text-perigo">*</span></label>
                <select id="colaborador-cargo" name="cargo" required aria-describedby="erro-colaborador-cargo" class="mt-1 min-h-11 w-full rounded-xl border-2 border-black/10 bg-campo px-3 focus:border-pap focus:outline-none aria-invalid:border-perigo">
                    <option value="">Selecione</option>
                    <option value="medico">Médico(a)</option>
                    <option value="secretario">Atendente</option>
                </select>
                <p id="erro-colaborador-cargo" data-erro="cargo" class="mt-1 text-sm text-perigo" hidden></p>
            </div>
        </div>
        <p class="mt-4 text-sm text-black/70">A identificação e a senha temporária são geradas automaticamente. A troca da senha é obrigatória no primeiro acesso.</p>
        <p id="erro-cadastro" role="alert" class="mt-4 text-perigo" hidden></p>
        <button id="salvar-colaborador" type="submit" class="mt-5 min-h-11 rounded-xl bg-pap px-5 font-heading font-bold text-white hover:bg-pap-escuro disabled:cursor-wait disabled:opacity-70 focus-visible:outline-2 focus-visible:outline-pap focus-visible:outline-offset-2">Cadastrar colaborador</button>
    </form>
    <section id="credenciais-colaborador" role="status" class="mt-5 rounded-2xl border-2 border-pap bg-white p-5" hidden>
        <h2 class="font-heading text-xl font-bold">Colaborador cadastrado</h2>
        <p class="mt-2">Repasse estas credenciais ao colaborador. A senha temporária só aparece nesta confirmação.</p>
        <p class="mt-3">Identificação: <strong id="novo-alias" class="select-all"></strong></p>
        <p class="mt-2">Senha temporária: <strong id="nova-senha" class="select-all"></strong></p>
        <p class="mt-2 text-sm text-black/70">O e-mail de boas-vindas também é colocado na fila de envio. No ambiente local, os e-mails são registrados no log.</p>
        <button type="button" id="ocultar-credenciais" class="mt-3 min-h-11 rounded-xl border-2 border-pap px-4 font-semibold text-pap-escuro focus-visible:outline-2 focus-visible:outline-pap">Ocultar credenciais</button>
    </section>
    <section class="mt-8">
        <h2 class="font-heading text-xl font-bold">Equipe cadastrada</h2>
        <p id="aviso-status-colaborador" role="status" class="mt-4 rounded-xl border border-pap/30 bg-white p-4 font-semibold text-pap-escuro" hidden></p>
        <p id="carregando-colaboradores" role="status" class="mt-4">Carregando colaboradores…</p>
        <div id="erro-lista-colaboradores" role="alert" class="mt-4" hidden>
            <p id="mensagem-lista-colaboradores" class="text-perigo"></p>
            <button id="recarregar-colaboradores" type="button" class="min-h-11 rounded-lg px-2 font-semibold text-pap-escuro underline focus-visible:outline-2 focus-visible:outline-pap">Tentar de novo</button>
        </div>
        <ul id="lista-colaboradores" class="mt-4 grid gap-3 sm:grid-cols-2"></ul>
        <template id="modelo-colaborador">
            <li class="min-w-0 rounded-2xl bg-white p-5">
                <h3 data-nome class="break-words font-heading font-bold"></h3>
                <p data-cargo class="mt-1"></p>
                <p data-email class="mt-2 break-words"></p>
                <p class="mt-1">Identificação: <span data-alias></span></p>
                <p data-status class="mt-2 text-sm"></p>
                <button type="button" data-alterar-status class="mt-4 min-h-11 rounded-xl border-2 px-4 text-sm font-semibold focus-visible:outline-2 focus-visible:outline-pap" hidden></button>
            </li>
        </template>
    </section>
    <dialog id="confirmar-status-colaborador" aria-labelledby="titulo-status-colaborador" class="m-auto w-[min(460px,calc(100%-2rem))] rounded-2xl bg-white p-6 text-tinta shadow-xl">
        <h2 id="titulo-status-colaborador" class="font-heading text-xl font-bold"></h2>
        <p id="nome-status-colaborador" class="mt-3 break-words font-semibold"></p>
        <p id="descricao-status-colaborador" class="mt-2 text-base leading-relaxed"></p>
        <p id="erro-status-colaborador" role="alert" class="mt-3 text-sm text-perigo" hidden></p>
        <div class="mt-6 flex flex-wrap justify-end gap-3">
            <button id="cancelar-status-colaborador" type="button" class="min-h-11 rounded-xl px-4 font-semibold hover:bg-campo disabled:opacity-70 focus-visible:outline-2 focus-visible:outline-pap">Cancelar</button>
            <button id="salvar-status-colaborador" type="button" class="min-h-11 rounded-xl bg-pap px-5 font-heading font-bold text-white hover:bg-pap-escuro disabled:cursor-wait disabled:opacity-70 focus-visible:outline-2 focus-visible:outline-pap"></button>
        </div>
    </dialog>
@endsection
