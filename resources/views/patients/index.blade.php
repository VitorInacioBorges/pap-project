@extends('layouts.app')

@section('title', 'Pacientes · Projeto PAP')
@section('cargos', 'secretario,admin')

@push('scripts')
    @vite('resources/js/pages/pacientes.js')
@endpush

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-heading text-3xl font-bold leading-tight md:text-4xl">Pacientes</h1>
            <p class="mt-1 text-base md:text-lg">Cadastro, edição e ocultação de pacientes.</p>
        </div>

        <button type="button" id="botao-nova"
                class="inline-flex min-h-12 items-center gap-2 rounded-xl bg-pap px-4 font-heading text-base font-bold text-white transition hover:-translate-y-0.5 hover:bg-pap-escuro hover:shadow-md active:translate-y-0 active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-pap focus-visible:ring-offset-2 md:text-lg">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" aria-hidden="true">
                <path d="M12 4v16M4 12h16" />
            </svg>
            Nova Paciente
        </button>
    </div>

    <div class="mt-8 flex gap-3 md:mt-10">
        <div class="relative flex-1">
            <label for="campo-busca" class="sr-only">Pesquisar por nome ou CPF</label>
            <input id="campo-busca" type="search" autocomplete="off" placeholder="Pesquise por nome ou CPF..."
                   class="h-12 w-full rounded-xl border-2 border-pap bg-white pl-4 pr-11 text-base font-semibold placeholder:font-semibold placeholder:text-black/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-pap/40">
            <svg class="pointer-events-none absolute right-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-black/35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
            </svg>
        </div>

        <div class="relative">
            <button type="button" id="botao-filtros" aria-expanded="false" aria-controls="painel-filtros"
                    class="inline-flex h-12 items-center gap-6 rounded-xl border-2 border-pap bg-white px-4 font-heading text-base font-bold text-pap transition hover:bg-pap-fundo active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-pap/40 md:min-w-40 md:justify-between">
                Filtros
                <svg class="h-3.5 w-3.5" viewBox="0 0 12 10" fill="currentColor" aria-hidden="true"><path d="M6 10 0 0h12z" /></svg>
            </button>

            <div id="painel-filtros" role="dialog" aria-label="Filtros da lista" hidden
                 class="absolute right-0 top-full z-30 mt-2 w-56 rounded-2xl bg-white p-4 shadow-lg ring-1 ring-black/5">
                <fieldset>
                    <legend class="font-heading text-sm font-bold">Ordenar por:</legend>
                    @foreach (['recentes' => 'Mais recentes', 'antigos' => 'Mais antigos', 'alfabetica' => 'Ordem Alfabética'] as $valor => $rotulo)
                        <label class="mt-1 flex min-h-10 cursor-pointer items-center gap-2.5 text-base">
                            <input type="radio" name="ordem" value="{{ $valor }}" @checked($valor === 'recentes')
                                   class="h-5 w-5 accent-pap">
                            {{ $rotulo }}
                        </label>
                    @endforeach
                </fieldset>
                <fieldset class="mt-3">
                    <legend class="font-heading text-sm font-bold">Status:</legend>
                    @foreach (['ativos' => 'Ativos', 'inativos' => 'Inativos'] as $valor => $rotulo)
                        <label class="mt-1 flex min-h-10 cursor-pointer items-center gap-2.5 text-base">
                            <input type="checkbox" name="status" value="{{ $valor }}" @checked($valor === 'ativos')
                                   class="h-5 w-5 accent-pap">
                            {{ $rotulo }}
                        </label>
                    @endforeach
                </fieldset>
            </div>
        </div>
    </div>

    <p id="aviso-lista" role="status" class="mt-4 rounded-xl bg-white px-4 py-3 font-semibold text-pap-escuro ring-2 ring-pap" hidden></p>

    <div class="mt-6 overflow-hidden rounded-2xl bg-white">
        <table class="w-full table-fixed border-collapse">
            <caption class="sr-only">Lista de pacientes</caption>
            <thead class="bg-pap font-heading text-base text-white md:text-lg">
                <tr>
                    <th scope="col" class="h-12 px-3 font-bold md:hidden">Informações</th>
                    <th scope="col" class="hidden h-12 w-[32%] px-3 font-bold md:table-cell">Nome</th>
                    <th scope="col" class="hidden h-12 w-[24%] px-3 font-bold md:table-cell">CPF</th>
                    <th scope="col" class="hidden h-12 w-[16%] px-3 font-bold md:table-cell">Nascimento</th>
                    <th scope="col" class="h-12 w-24 px-2 font-bold md:w-[14%]">Status</th>
                    <th scope="col" class="h-12 w-20 px-2 font-bold md:w-[14%]">Ações</th>
                </tr>
            </thead>
            <tbody id="corpo-tabela" class="text-center text-base"></tbody>
        </table>

        <div id="lista-carregando" role="status" class="flex items-center justify-center gap-3 px-4 py-8 text-black/60">
            <span class="h-5 w-5 animate-spin rounded-full border-2 border-pap border-t-transparent" aria-hidden="true"></span>
            Carregando pacientes…
        </div>
        <p id="lista-vazia" class="px-4 py-8 text-center text-black/60" hidden>Nenhuma paciente encontrada.</p>
        <div id="lista-erro" role="alert" class="flex flex-col items-center gap-3 px-4 py-8 text-center" hidden>
            <p id="lista-erro-texto" class="font-semibold text-perigo"></p>
            <button type="button" id="botao-tentar"
                    class="min-h-11 rounded-xl border-2 border-pap px-4 font-heading font-bold text-pap transition hover:bg-pap-fundo active:scale-[0.98]">
                Tentar de novo
            </button>
        </div>
    </div>

    {{-- DECISAO: paginação — escolhido: botão Carregar mais; alternativas: paginação numerada --}}
    <div class="mt-4 flex justify-center">
        <button type="button" id="botao-mais" hidden
                class="min-h-11 rounded-xl border-2 border-pap bg-white px-5 font-heading font-bold text-pap transition hover:bg-pap-fundo active:scale-[0.98] disabled:cursor-wait disabled:opacity-70">
            Carregar mais
        </button>
    </div>

    <template id="linha-paciente">
        <tr class="border-b-2 border-campo last:border-b-0">
            <td class="px-3 py-3 md:hidden">
                <span class="block font-semibold" data-campo="nome"></span>
                <span class="block" data-campo="cpf"></span>
                <span class="block" data-campo="nascimento"></span>
                @include('patients._fichas')
            </td>
            <td class="hidden px-3 py-4 md:table-cell">
                <span class="block truncate" data-campo="nome"></span>
                @include('patients._fichas')
            </td>
            <td class="hidden px-3 py-4 md:table-cell" data-campo="cpf"></td>
            <td class="hidden px-3 py-4 md:table-cell" data-campo="nascimento"></td>
            <td class="px-2 py-3">
                <span data-campo="status" class="inline-flex rounded-full border px-3 py-0.5 text-sm md:text-base"></span>
            </td>
            <td class="px-2 py-3">
                <button type="button" data-acao="menu" aria-haspopup="menu" aria-expanded="false"
                        class="inline-flex h-11 w-14 items-center justify-center rounded-full transition hover:bg-pap-fundo active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-pap">
                    <span class="inline-flex h-7 w-11 items-center justify-center rounded-full border-2 border-pap/60 text-pap">
                        <svg class="h-4 w-4" viewBox="0 0 24 6" fill="currentColor" aria-hidden="true">
                            <circle cx="3" cy="3" r="3" /><circle cx="12" cy="3" r="3" /><circle cx="21" cy="3" r="3" />
                        </svg>
                    </span>
                </button>
            </td>
        </tr>
    </template>

    <div id="menu-acoes" role="menu" aria-label="Ações da paciente" hidden
         class="fixed z-40 flex w-36 flex-col items-center gap-1 rounded-2xl bg-white p-2 shadow-lg ring-1 ring-black/5">
        <button type="button" role="menuitem" data-item="editar"
                class="min-h-10 w-full rounded-lg px-3 text-base transition hover:bg-pap-fundo focus:bg-pap-fundo focus:outline-none">Editar</button>
        <button type="button" role="menuitem" data-item="alternar"
                class="min-h-10 w-full rounded-lg px-3 text-base transition hover:bg-pap-fundo focus:bg-pap-fundo focus:outline-none">Ocultar</button>
        <button type="button" role="menuitem" data-item="excluir"
                class="min-h-10 rounded-full border border-perigo px-3 text-base text-perigo transition hover:bg-perigo hover:text-white focus:bg-perigo focus:text-white focus:outline-none">Excluir</button>
    </div>

    <dialog id="confirmar-exclusao" aria-labelledby="titulo-exclusao"
            class="m-auto w-[min(440px,calc(100%-2rem))] rounded-2xl bg-white p-6 text-tinta">
        <h2 id="titulo-exclusao" class="font-heading text-xl font-bold">Excluir paciente?</h2>
        <p class="mt-3 text-base leading-snug">
            <span id="nome-exclusao" class="font-semibold"></span> some de todas as listas e não pode mais entrar no sistema.
        </p>
        <label for="confirmacao-nome" class="mt-4 block font-heading text-sm font-bold">Digite o nome completo da paciente para confirmar</label>
        <input id="confirmacao-nome" type="text" autocomplete="off" aria-describedby="erro-confirmacao-nome"
               class="mt-2 min-h-11 w-full rounded-xl border-2 border-black/10 bg-campo px-3 focus:border-pap focus:outline-none aria-invalid:border-perigo">
        <p id="erro-confirmacao-nome" role="alert" class="mt-2 text-sm text-perigo" hidden></p>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" data-fechar
                    class="min-h-11 rounded-xl px-4 font-heading font-bold text-tinta transition hover:bg-campo active:scale-[0.98]">Cancelar</button>
            <button type="button" id="botao-confirmar-exclusao" disabled
                    class="min-h-11 rounded-xl bg-perigo px-4 font-heading font-bold text-white transition hover:brightness-90 active:scale-[0.98] disabled:opacity-70">Excluir</button>
        </div>
    </dialog>

    @include('patients._formulario')
@endsection
