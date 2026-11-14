@extends('layouts.app')

@section('title', ($cadastro ? 'Criar ficha' : 'Fichas da paciente').' · Projeto PAP')
@section('cargos', 'medico,admin')

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
        <details id="identificacao-adicional" class="group mt-4 rounded-2xl border border-pap/15 bg-white" hidden>
            <summary class="flex min-h-20 cursor-pointer list-none items-center justify-between gap-3 rounded-2xl px-4 py-4 transition hover:bg-pap-fundo/50 focus-visible:outline-2 focus-visible:outline-pap md:px-6 [&::-webkit-details-marker]:hidden">
                <span class="flex min-w-0 items-center gap-3">
                    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-pap-fundo text-pap-escuro" aria-hidden="true">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4" /><path d="M4 21v-2a8 8 0 0 1 16 0v2" /></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block font-heading text-base font-bold text-tinta">Dados da paciente</span>
                        <span class="mt-0.5 block text-sm text-black/60">Identificação e contato</span>
                    </span>
                </span>
                <span class="flex shrink-0 items-center gap-2 text-sm font-semibold text-pap-escuro">
                    <span class="hidden sm:inline"><span class="group-open:hidden">Ver dados</span><span class="hidden group-open:inline">Ocultar dados</span></span>
                    <svg class="h-4 w-4 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                </span>
            </summary>
            <dl class="mx-4 grid gap-x-6 gap-y-5 border-t border-black/10 py-5 text-base sm:grid-cols-2 md:mx-6 lg:grid-cols-3">
                @foreach (['nascimento' => 'Nascimento', 'telefone' => 'Telefone', 'email' => 'E-mail', 'profissao' => 'Profissão', 'estado_civil' => 'Estado civil', 'escolaridade' => 'Escolaridade', 'endereco' => 'Endereço'] as $campo => $rotulo)
                    <div class="min-w-0 {{ $campo === 'endereco' ? 'sm:col-span-2 lg:col-span-3' : '' }}">
                        <dt class="text-sm text-black/60">{{ $rotulo }}</dt>
                        <dd data-identificacao="{{ $campo }}" class="mt-1 break-words font-semibold text-tinta"></dd>
                    </div>
                @endforeach
            </dl>
        </details>

        <div id="erro-fichas" role="alert" class="mt-5 rounded-xl border-2 border-perigo bg-white p-4" hidden>
            <p id="erro-fichas-texto"></p>
            <button type="button" id="tentar-fichas" class="mt-2 min-h-11 rounded-lg px-3 font-semibold text-pap-escuro underline focus-visible:outline-2 focus-visible:outline-pap">Tentar de novo</button>
        </div>

        @if ($cadastro)
            <form id="form-ficha" class="mt-5 w-full min-w-0 rounded-2xl bg-white p-5 md:p-7" hidden>
                <div class="grid items-center gap-4 border-b border-black/10 pb-5 sm:grid-cols-2">
                    <div>
                        <p class="text-sm text-black/60">Responsável pelo atendimento</p>
                        <p class="mt-1 font-semibold" data-usuario-nome></p>
                    </div>
                    <div>
                        <label for="data-atendimento" class="block font-heading text-sm font-bold">Data e horário do atendimento <span class="text-perigo">*</span></label>
                        <input id="data-atendimento" name="data_atendimento" type="datetime-local" required aria-describedby="erro-data-atendimento"
                               class="mt-1 min-h-11 w-full rounded-xl border-2 border-black/10 bg-campo px-3 focus:border-pap focus:outline-none aria-invalid:border-perigo">
                        <p id="erro-data-atendimento" data-erro="data_atendimento" class="mt-1 text-sm text-perigo" hidden></p>
                    </div>
                </div>
                <p class="my-4 text-sm text-black/60">Preencha conforme a entrevista e o exame. Registre “não informado”, “nega” ou “não realizado” quando pertinente; campos vazios não significam ausência de sintomas.</p>
                @php
                    $iconesSecoes = [
                        'Motivo do atendimento' => 'M9 5H6a2 2 0 0 0-2 2v13h16V7a2 2 0 0 0-2-2h-3M9 3h6v4H9zM8 12h8M8 16h5',
                        'Antecedentes e cuidados de saúde' => 'M3 11a9 9 0 1 1 2 7M3 5v6h6M12 7v5l3 2',
                        'Hábitos e contexto de vida' => 'M20 3C9 3 3 7 3 13a7 7 0 0 0 7 7c6 0 10-6 10-17ZM4 20 15 9',
                        'História ginecológica e obstétrica' => 'M17 8a5 5 0 1 1-10 0 5 5 0 0 1 10 0ZM12 13v9M8 18h8',
                        'Revisão de sistemas' => 'M9 5h11M9 12h11M9 19h11M3 4l1 1 2-2M3 11l1 1 2-2M3 18l1 1 2-2',
                        'Sinais vitais e exame físico' => 'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8ZM3 12h4l2-4 4 8 2-4h6',
                        'Avaliação e plano' => 'M9 5H5v16h14V5h-4M9 3h6v4H9zM8 14l3 3 5-6',
                    ];
                @endphp
                @foreach (\App\Support\CamposAnamnese::SECOES as $titulo => $campos)
                    <details data-secao-ficha class="group border-t border-black/10" @if ($loop->first) open @endif>
                        <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-3 rounded-lg py-4 font-heading text-base font-bold text-pap-escuro focus-visible:outline-2 focus-visible:outline-pap [&::-webkit-details-marker]:hidden">
                            <span class="flex min-w-0 items-center gap-3">
                                <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $iconesSecoes[$titulo] }}" /></svg>
                                <span>{{ $titulo }}</span>
                            </span>
                            <svg class="h-4 w-4 shrink-0 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                        </summary>
                        <div class="grid gap-5 pb-5">
                            @foreach (\App\Support\CamposAnamnese::CATEGORIAS[$titulo] as $categoria => $nomes)
                                @php
                                    $compactos = count(array_filter($nomes, fn ($nome) => in_array($campos[$nome][1], ['number', 'decimal'], true))) >= 3;
                                @endphp
                                <fieldset class="ficha-categoria min-w-0 rounded-xl border border-black/10 bg-campo/30">
                                    @if (count(\App\Support\CamposAnamnese::CATEGORIAS[$titulo]) > 1)
                                        <legend class="px-2 font-heading text-sm font-bold text-pap-escuro">{{ $categoria }}</legend>
                                    @endif
                                    <div class="ficha-categoria-campos {{ $compactos ? 'ficha-categoria-campos--compactos' : '' }}">
                                        @foreach ($nomes as $nome)
                                            @php
                                                [$rotulo, $tipo] = $campos[$nome];
                                                $obrigatorio = in_array($nome, ['queixa_principal', 'historia_atual'], true);
                                                $largura = $obrigatorio || ($tipo === 'textarea' && ($compactos || ($loop->last && count($nomes) % 2 === 1)))
                                                    ? 'ficha-campo--inteiro'
                                                    : '';
                                                if ($compactos && count($nomes) === 5 && $loop->index >= 3) {
                                                    $largura = 'ficha-campo--meia';
                                                }
                                            @endphp
                                            <div class="ficha-campo min-w-0 {{ $largura }}">
                                                <label for="clinico-{{ $nome }}" class="ficha-rotulo flex items-end gap-1 font-heading text-sm font-bold leading-snug">{{ $rotulo }} @if ($obrigatorio)<span class="text-perigo">*</span>@endif</label>
                                                @if ($tipo === 'textarea')
                                                    <textarea id="clinico-{{ $nome }}" data-clinico="{{ $nome }}" rows="3" maxlength="10000" @required($obrigatorio)
                                                              aria-describedby="erro-clinico-{{ $nome }}" class="mt-2 block w-full resize-y rounded-xl border-2 border-black/10 bg-white p-3 focus:border-pap focus:outline-none aria-invalid:border-perigo"></textarea>
                                                @else
                                                    <input id="clinico-{{ $nome }}" data-clinico="{{ $nome }}" type="{{ $tipo === 'decimal' ? 'number' : $tipo }}"
                                                           @if (in_array($tipo, ['number', 'decimal'])) min="0" step="{{ $tipo === 'decimal' ? 'any' : '1' }}" @else maxlength="10000" @endif
                                                           @required($obrigatorio) aria-describedby="erro-clinico-{{ $nome }}"
                                                           class="mt-2 block h-11 w-full rounded-xl border-2 border-black/10 bg-white px-3 focus:border-pap focus:outline-none aria-invalid:border-perigo">
                                                @endif
                                                <p id="erro-clinico-{{ $nome }}" data-erro="dados_clinicos.{{ $nome }}" class="mt-1 text-sm text-perigo" hidden></p>
                                            </div>
                                        @endforeach
                                    </div>
                                </fieldset>
                            @endforeach
                        </div>
                    </details>
                @endforeach
                <details data-secao-ficha class="group border-t border-black/10">
                    <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-3 rounded-lg py-4 font-heading text-base font-bold text-pap-escuro focus-visible:outline-2 focus-visible:outline-pap [&::-webkit-details-marker]:hidden">
                        <span class="flex min-w-0 items-center gap-3">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 3 5 5-12 12H4v-5L16 3ZM13 6l5 5M4 20h16" /></svg>
                            <span>Observações adicionais</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 transition-transform group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
                    </summary>
                    <div class="pb-5">
                        <label for="observacoes" class="block font-heading text-sm font-bold">Observações</label>
                        <textarea id="observacoes" name="observacoes" rows="2" maxlength="10000" aria-describedby="erro-observacoes"
                                  class="mt-1 w-full rounded-xl border-2 border-black/10 bg-campo p-3 focus:border-pap focus:outline-none aria-invalid:border-perigo"></textarea>
                        <p id="erro-observacoes" data-erro="observacoes" class="mt-1 text-sm text-perigo" hidden></p>
                    </div>
                </details>
                <p id="erro-salvar-ficha" role="alert" class="mt-4 text-perigo" hidden></p>
                <div id="acoes-ficha" class="sticky bottom-0 z-10 -mx-5 mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-black/10 bg-white px-5 py-4 md:-mx-7 md:px-7">
                    <button id="abrir-preferencias-ficha" type="button" aria-haspopup="dialog" aria-controls="preferencias-ficha"
                            class="inline-flex min-h-11 items-center gap-2 rounded-xl px-3 font-semibold text-pap-escuro hover:bg-pap-fundo focus-visible:outline-2 focus-visible:outline-pap">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 17h16" /><circle cx="9" cy="7" r="3" fill="white" /><circle cx="15" cy="17" r="3" fill="white" /></svg>
                        Preferências
                    </button>
                    <div class="ml-auto flex gap-3">
                        <a href="{{ route('fichas.index', ['paciente' => request()->route('paciente')]) }}" class="inline-flex min-h-11 items-center rounded-xl border-2 border-pap px-5 font-heading font-bold text-pap-escuro hover:bg-pap-fundo focus-visible:outline-2 focus-visible:outline-pap">Cancelar</a>
                        <button id="salvar-ficha" type="submit" class="min-h-11 rounded-xl bg-pap px-5 font-heading font-bold text-white hover:bg-pap-escuro disabled:cursor-wait disabled:opacity-70 focus-visible:outline-2 focus-visible:outline-pap focus-visible:outline-offset-2">Salvar ficha</button>
                    </div>
                </div>
            </form>
            <dialog id="preferencias-ficha" aria-labelledby="titulo-preferencias-ficha" class="m-auto w-[min(440px,calc(100%-2rem))] rounded-2xl border border-black/10 bg-white p-6 text-tinta shadow-xl">
                <h2 id="titulo-preferencias-ficha" class="font-heading text-xl font-bold">Preferências da ficha</h2>
                <label for="preferencia-secao-unica" class="mt-5 flex min-h-11 cursor-pointer items-start gap-3">
                    <input id="preferencia-secao-unica" type="checkbox" checked aria-describedby="dica-secao-unica" class="mt-1 h-5 w-5 shrink-0 accent-pap">
                    <span>
                        <span class="block font-semibold">Fechar a seção anterior</span>
                        <span id="dica-secao-unica" class="mt-1 block text-sm text-black/65">Ao abrir uma seção, as outras são recolhidas. Desative para manter várias abertas.</span>
                    </span>
                </label>
                <p class="mt-4 text-sm text-black/60">Sua escolha é salva neste navegador para sua conta.</p>
                <div class="mt-5 flex justify-end">
                    <button id="fechar-preferencias-ficha" type="button" class="min-h-11 rounded-xl bg-pap px-5 font-heading font-bold text-white hover:bg-pap-escuro focus-visible:outline-2 focus-visible:outline-pap">Concluir</button>
                </div>
            </dialog>
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
                    <details class="mt-4">
                        <summary class="min-h-11 cursor-pointer rounded-lg py-3 font-heading font-bold text-pap-escuro focus-visible:outline-2 focus-visible:outline-pap">Ver anamnese completa</summary>
                        <dl data-dados-clinicos class="mt-3 grid gap-4">
                            @foreach (\App\Support\CamposAnamnese::SECOES as $titulo => $campos)
                                @foreach ($campos as $nome => [$rotulo, $tipo])
                                    <div data-item-clinico="{{ $nome }}">
                                        <dt class="font-semibold">{{ $rotulo }}</dt>
                                        <dd class="whitespace-pre-wrap break-words" data-valor-clinico="{{ $nome }}"></dd>
                                    </div>
                                @endforeach
                            @endforeach
                        </dl>
                        <p data-legado hidden>Esta ficha foi cadastrada no modelo anterior, sem os campos clínicos estruturados.</p>
                    </details>
                    <p data-observacoes class="mt-4 whitespace-pre-wrap break-words"></p>
                </article>
            </template>
        @endif
    </section>
@endsection
