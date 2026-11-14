<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#009A94">

    <title>@yield('title', 'Projeto PAP')</title>
    <link rel="icon" href="{{ asset('images/pap-logo.svg') }}" type="image/svg+xml">

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('scripts')
</head>
<body class="min-h-screen bg-pap-fundo font-sans text-tinta antialiased"
      data-tipo="@yield('tipo', 'colaborador')"
      data-cargos="@yield('cargos', '')">
    <a href="#app-conteudo"
       class="sr-only z-[60] rounded-lg bg-white px-4 py-3 font-semibold text-pap focus:not-sr-only focus:fixed focus:left-4 focus:top-4">
        Pular para o conteúdo
    </a>

    <header class="bg-pap">
        <div class="mx-auto grid h-20 w-full max-w-[1600px] grid-cols-[1fr_auto_1fr] items-center px-4 md:h-[100px] lg:px-10">
            <div class="justify-self-start">
                <button type="button"
                        id="menu-toggle"
                        class="grid h-12 w-12 place-items-center rounded-xl transition hover:bg-white/15 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        aria-controls="menu-lateral"
                        aria-expanded="false"
                        aria-label="Abrir menu">
                    <img src="{{ asset('images/menu-icon.svg') }}" alt="" class="h-7 w-auto">
                </button>
            </div>

            <a href="{{ route('inicio') }}"
               class="flex items-center gap-2 rounded-xl px-2 py-1 transition hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                <img src="{{ asset('images/pap-logo.svg') }}" alt="" class="h-10 w-auto md:h-[46px]">
                <img src="{{ asset('images/pap-title.svg') }}" alt="Projeto PAP" class="hidden h-auto w-[170px] sm:block">
                <span class="sr-only sm:hidden">Projeto PAP — Início</span>
            </a>

            <div class="justify-self-end">
                <button type="button"
                        data-acao="sair"
                        class="grid h-12 w-12 place-items-center rounded-xl transition hover:bg-white/15 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        aria-label="Sair">
                    <img src="{{ asset('images/exit-icon.svg') }}" alt="" class="h-7 w-auto">
                </button>
            </div>
        </div>
    </header>

    <div id="menu-overlay" class="fixed inset-0 z-40 bg-black/30" hidden></div>

    <aside id="menu-lateral"
           inert
           aria-label="Menu principal"
           class="fixed inset-y-0 left-0 z-50 flex w-full -translate-x-full flex-col bg-pap-menu transition-transform duration-200 sm:w-[360px]">
        <div class="flex h-28 shrink-0 items-center justify-between gap-4 bg-pap px-6">
            <div class="flex min-w-0 items-center gap-4">
                <span class="relative h-14 w-14 shrink-0 overflow-hidden rounded-full bg-pap-fundo" aria-hidden="true">
                    <span class="absolute inset-x-0 bottom-0 mx-auto h-[85%] w-[85%] bg-pap-menu" style="mask: url('{{ asset('images/user-icon.svg') }}') center bottom / contain no-repeat; -webkit-mask: url('{{ asset('images/user-icon.svg') }}') center bottom / contain no-repeat;"></span>
                </span>
                <div class="min-w-0 text-white">
                    <p class="truncate font-heading text-xl font-bold leading-tight" data-usuario-nome></p>
                    <p class="truncate text-lg leading-tight" data-usuario-cargo></p>
                </div>
            </div>

            <button type="button"
                    data-acao="sair"
                    class="grid h-12 w-12 shrink-0 place-items-center rounded-xl transition hover:bg-white/15 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                    aria-label="Sair">
                <img src="{{ asset('images/exit-icon.svg') }}" alt="" class="h-7 w-auto">
            </button>
        </div>

        <nav class="flex flex-1 flex-col items-start gap-1 px-6 pt-6" aria-label="Navegação principal">
            @php
                $itens = [
                    ['rota' => 'inicio', 'rotulo' => 'Início', 'cargos' => null],
                    ['rota' => 'pacientes', 'rotulo' => 'Pacientes', 'cargos' => 'secretario,admin'],
                    ['rota' => 'configuracoes', 'rotulo' => 'Configurações', 'cargos' => null],
                ];
            @endphp

            @foreach ($itens as $item)
                @php $ativo = request()->routeIs($item['rota']); @endphp
                <a href="{{ route($item['rota']) }}"
                   @if ($item['cargos']) data-so-cargos="{{ $item['cargos'] }}" hidden @endif
                   @if ($ativo) aria-current="page" @endif
                   class="rounded-2xl px-4 py-3 font-heading text-xl font-bold text-white transition active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-white
                          {{ $ativo ? 'bg-pap' : 'hover:bg-pap/40' }}">
                    {{ $item['rotulo'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex justify-center pb-10 sm:hidden">
            <button type="button"
                    id="menu-fechar"
                    class="grid h-14 w-14 place-items-center rounded-full bg-white text-pap shadow transition active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-pap"
                    aria-label="Fechar menu">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>
    </aside>

    <main class="mx-auto w-full max-w-[1100px] px-4 py-8 md:px-6 md:py-12">
        <div id="carregando" role="status" class="flex items-center gap-3 py-16 text-lg text-tinta/70">
            <span class="h-5 w-5 animate-spin rounded-full border-2 border-pap border-t-transparent" aria-hidden="true"></span>
            Carregando…
        </div>

        <div id="sem-permissao" class="rounded-2xl border-2 border-perigo bg-white p-6" hidden>
            <p class="font-heading text-xl font-bold">Você não tem permissão para esta tela.</p>
            <a href="{{ route('inicio') }}"
               class="mt-4 inline-flex min-h-11 items-center rounded-xl px-1 font-semibold text-pap underline underline-offset-4 hover:text-pap-escuro">
                Voltar ao início
            </a>
        </div>

        <div id="app-conteudo" tabindex="-1" hidden>
            @yield('content')
        </div>
    </main>
</body>
</html>
