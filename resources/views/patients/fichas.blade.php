@extends('layouts.app')

@section('title', ($cadastro ? 'Criar ficha' : 'Fichas da paciente').' · Projeto PAP')
@section('cargos', 'secretario,admin')

@push('scripts')
    @vite('resources/js/pages/fichas.js')
@endpush

@section('content')
    <section id="pagina-fichas" data-paciente="{{ request()->route('paciente') }}"
             data-lista="{{ route('fichas.index', ['paciente' => request()->route('paciente')], false) }}"
             data-cadastro="{{ $cadastro ? 'true' : 'false' }}">
        <a href="{{ route('pacientes') }}" class="inline-flex min-h-11 items-center rounded-lg font-semibold text-pap-escuro underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-pap">Voltar para pacientes</a>
        <div class="mt-3 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="font-heading text-3xl font-bold md:text-4xl">{{ $cadastro ? 'Criar ficha' : 'Fichas da paciente' }}</h1>
                <p id="identificacao-paciente" class="mt-2 text-lg" role="status">Carregando paciente…</p>
            </div>
            @if (! $cadastro)
                <a id="criar-ficha" href="{{ route('fichas.create', ['paciente' => request()->route('paciente')]) }}" hidden
                   class="inline-flex min-h-12 items-center rounded-xl bg-pap px-5 font-heading font-bold text-white hover:bg-pap-escuro focus-visible:outline-2 focus-visible:outline-pap focus-visible:outline-offset-2">+ Criar ficha</a>
            @endif
        </div>

        <div id="erro-fichas" role="alert" class="mt-5 rounded-xl border-2 border-perigo bg-white p-4" hidden>
            <p id="erro-fichas-texto"></p>
            <button type="button" id="tentar-fichas" class="mt-2 min-h-11 rounded-lg px-3 font-semibold text-pap-escuro underline focus-visible:outline-2 focus-visible:outline-pap">Tentar de novo</button>
        </div>

        @if ($cadastro)
            <form id="form-ficha" class="mt-6 max-w-3xl rounded-2xl bg-white p-5 md:p-7" hidden>
                <p class="text-sm text-black/70">A ficha será vinculada à paciente acima. O responsável é registrado com sua conta.</p>
                <p class="mt-3 font-semibold">Responsável: <span data-usuario-nome></span></p>
                <div class="mt-5">
                    <label for="data-atendimento" class="block font-heading text-sm font-bold">Data e horário do atendimento <span class="text-perigo">*</span></label>
                    <input id="data-atendimento" name="data_atendimento" type="datetime-local" required aria-describedby="erro-data-atendimento"
                           class="mt-1 min-h-11 w-full rounded-xl border-2 border-black/10 bg-campo px-3 focus:border-pap focus:outline-none aria-invalid:border-perigo">
                    <p id="erro-data-atendimento" data-erro="data_atendimento" class="mt-1 text-sm text-perigo" hidden></p>
                </div>
                <div class="mt-5">
                    <label for="observacoes" class="block font-heading text-sm font-bold">Observações</label>
                    <textarea id="observacoes" name="observacoes" rows="7" maxlength="10000" aria-describedby="erro-observacoes"
                              class="mt-1 w-full rounded-xl border-2 border-black/10 bg-campo p-3 focus:border-pap focus:outline-none aria-invalid:border-perigo"></textarea>
                    <p id="erro-observacoes" data-erro="observacoes" class="mt-1 text-sm text-perigo" hidden></p>
                </div>
                <p id="erro-salvar-ficha" role="alert" class="mt-4 text-perigo" hidden></p>
                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <a href="{{ route('fichas.index', ['paciente' => request()->route('paciente')]) }}" class="inline-flex min-h-11 items-center rounded-xl border-2 border-pap px-5 font-heading font-bold text-pap-escuro hover:bg-pap-fundo focus-visible:outline-2 focus-visible:outline-pap">Cancelar</a>
                    <button id="salvar-ficha" type="submit" class="min-h-11 rounded-xl bg-pap px-5 font-heading font-bold text-white hover:bg-pap-escuro disabled:cursor-wait disabled:opacity-70 focus-visible:outline-2 focus-visible:outline-pap focus-visible:outline-offset-2">Salvar ficha</button>
                </div>
            </form>
        @else
            <p id="ficha-salva" role="status" class="mt-5 rounded-xl border-2 border-pap bg-white p-4 font-semibold" hidden>Ficha cadastrada com sucesso.</p>
            <p id="fichas-vazias" class="mt-6 rounded-2xl bg-white p-6 text-black/70" hidden>Nenhuma ficha cadastrada para esta paciente.</p>
            <div id="lista-fichas" class="mt-6 grid gap-4"></div>
            <button id="mais-fichas" type="button" class="mt-4 min-h-11 rounded-xl border-2 border-pap bg-white px-5 font-heading font-bold text-pap-escuro disabled:opacity-70 focus-visible:outline-2 focus-visible:outline-pap" hidden>Carregar mais</button>
            <template id="modelo-ficha">
                <article class="rounded-2xl bg-white p-5 md:p-6">
                    <h2 data-numero class="font-heading text-lg font-bold"></h2>
                    <p class="mt-2"><time data-data></time></p>
                    <p class="mt-1">Responsável: <span data-responsavel></span></p>
                    <p data-observacoes class="mt-4 whitespace-pre-wrap break-words"></p>
                </article>
            </template>
        @endif
    </section>
@endsection
