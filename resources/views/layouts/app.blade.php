<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Projeto PAP'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#E6F4F4] text-[#1B1B18] antialiased">
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

    {{-- HEADER --}}
    <header class="bg-[#009A94]">
        <div class="mx-auto grid h-20 w-full max-w-[1600px] grid-cols-[1fr_auto_1fr] items-center px-6 md:h-[114px] lg:px-10">

            {{-- Esquerda: abre o menu --}}
            <div class="justify-self-start">
                <button type="button"
                        id="menu-toggle"
                        class="-m-2 rounded-md p-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E6F4F4]"
                        aria-controls="menu-lateral"
                        aria-expanded="false"
                        aria-label="Abrir menu">
                    <img src="{{ asset('images/menu-icon.svg') }}" alt="" class="h-7 w-auto">
                </button>
            </div>

            {{-- Centro: logo + título --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-2 rounded-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E6F4F4]">
                <img src="{{ asset('images/pap-logo.svg') }}" alt="" class="h-9 w-auto md:h-[51px]">
                <img src="{{ asset('images/pap-title.svg') }}" alt="Projeto PAP" class="h-auto w-[120px] md:w-[189px]">
            </a>

            {{-- Direita: sair --}}
            <form method="POST" action="{{ route('logout') }}" class="justify-self-end">
                @csrf
                <button type="submit"
                        class="-m-2 rounded-md p-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E6F4F4]"
                        aria-label="Sair">
                    <img src="{{ asset('images/exit-icon.svg') }}" alt="" class="h-7 w-auto">
                </button>
            </form>
        </div>
    </header>

    {{-- OVERLAY DO MENU --}}
    <div id="menu-overlay" class="fixed inset-0 z-40 hidden bg-black/30"></div>

    {{-- MENU LATERAL (drawer) --}}
    <aside id="menu-lateral"
           inert
           aria-label="Menu principal"
           class="fixed inset-y-0 left-0 z-50 flex w-[393px] max-w-full -translate-x-full flex-col bg-[#7BBDC1] transition-transform duration-200">

        <div class="flex h-32 shrink-0 items-center justify-between gap-4 bg-[#009A94] px-8">
            <div class="flex min-w-0 items-center gap-4">
                <img src="{{ asset('images/user-icon.svg') }}" alt="" class="h-16 w-16 shrink-0">
                <div class="min-w-0 text-[#FFFEFE]">
                    <p class="font-heading truncate text-xl font-bold leading-tight">{{ $nomeUsuario }}</p>
                    <p class="truncate text-lg font-light leading-tight">{{ $cargoUsuario }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="-m-2 rounded-md p-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E6F4F4]"
                        aria-label="Sair">
                    <img src="{{ asset('images/exit-icon.svg') }}" alt="" class="h-7 w-auto">
                </button>
            </form>
        </div>

        <nav class="flex flex-col items-start px-8 pt-8" aria-label="Navegação principal">
            <a href="{{ route('dashboard') }}"
               @if (request()->routeIs('dashboard')) aria-current="page" @endif
               class="font-heading rounded-2xl px-4 py-[18px] text-[22px] font-bold leading-[25px] text-[#FFFEFE] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E6F4F4]
                      {{ request()->routeIs('dashboard') ? 'bg-[#009A94]' : 'hover:bg-[rgba(0,154,148,0.25)]' }}">
                Início
            </a>

            <a href="{{ route('patients.index') }}"
               @if (request()->routeIs('patients.*')) aria-current="page" @endif
               class="font-heading rounded-2xl px-4 py-[18px] text-[22px] font-bold leading-[25px] text-[#FFFEFE] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E6F4F4]
                      {{ request()->routeIs('patients.*') ? 'bg-[#009A94]' : 'hover:bg-[rgba(0,154,148,0.25)]' }}">
                Pacientes
            </a>

            <a href="{{ route('profile.edit') }}"
               @if (request()->routeIs('profile.*')) aria-current="page" @endif
               class="font-heading rounded-2xl px-4 py-[18px] text-[22px] font-bold leading-[25px] text-[#FFFEFE] transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#E6F4F4]
                      {{ request()->routeIs('profile.*') ? 'bg-[#009A94]' : 'hover:bg-[rgba(0,154,148,0.25)]' }}">
                Configurações
            </a>
        </nav>
    </aside>

    {{-- CONTEÚDO --}}
    <main class="mx-auto w-full max-w-[1600px] px-6 py-10 md:pt-16 lg:px-10">
        @if (session('success'))
            <div role="status" class="mb-8 rounded-2xl border border-[#009A94] bg-white px-5 py-4 text-[#009A94]">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div role="alert" class="mb-8 rounded-2xl border border-red-600 bg-white px-5 py-4 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        (function () {
            var toggle  = document.getElementById('menu-toggle');
            var menu    = document.getElementById('menu-lateral');
            var overlay = document.getElementById('menu-overlay');

            function setOpen(open) {
                menu.classList.toggle('-translate-x-full', !open);
                overlay.classList.toggle('hidden', !open);
                menu.toggleAttribute('inert', !open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

                if (open) {
                    var primeiro = menu.querySelector('a, button');
                    if (primeiro) { primeiro.focus(); }
                } else {
                    toggle.focus();
                }
            }

            toggle.addEventListener('click', function () {
                setOpen(menu.hasAttribute('inert'));
            });
            overlay.addEventListener('click', function () { setOpen(false); });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !menu.hasAttribute('inert')) { setOpen(false); }
            });
        })();
    </script>
</body>
</html>
