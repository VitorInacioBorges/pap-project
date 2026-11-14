<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FF2BA0">
    <title>Portal da paciente · Projeto PAP</title>
    <link rel="icon" href="{{ asset('images/pap-logo.svg') }}" type="image/svg+xml">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/pages/portal.js'])
</head>
@php
    $campo = 'mt-1 h-12 w-full rounded-xl border-2 border-black/10 bg-campo px-3 text-base transition placeholder:text-black/40 focus:border-rosa focus:bg-white focus:outline-none aria-invalid:border-perigo';
    $rotulo = 'block font-heading text-sm font-bold';
    $erro = 'mt-1 text-sm text-perigo';
@endphp
<body data-tipo="paciente" data-tema="rosa" class="flex min-h-screen items-center justify-center bg-rosa-fundo p-4 font-sans text-tinta antialiased md:p-10">
    <main class="w-full max-w-[680px] rounded-3xl bg-white px-5 py-8 shadow-sm sm:px-10 md:py-12">
        <div class="flex items-center justify-center gap-8">
            <img src="{{ asset('images/pap-fullogo.svg') }}" alt="Projeto PAP" class="h-14 w-auto md:h-[72px]">
            <img src="{{ asset('images/uepg.svg') }}" alt="UEPG" class="h-11 w-auto md:h-[52px]">
        </div>

        <div id="carregando" role="status" class="mt-10 flex items-center justify-center gap-3 text-lg text-tinta/70">
            <span class="h-5 w-5 animate-spin rounded-full border-2 border-rosa border-t-transparent" aria-hidden="true"></span>
            Carregando…
        </div>

        <div id="erro-carregar" role="alert" class="mt-8 rounded-2xl border-2 border-perigo bg-white p-6 text-center" hidden>
            <p class="font-heading text-xl font-bold">Não foi possível carregar seus dados.</p>
            <p class="mt-2" data-erro-texto></p>
            <button type="button"
                    class="mt-4 min-h-11 rounded-xl border-2 border-rosa px-4 font-heading font-bold text-rosa-escuro transition hover:bg-rosa-claro active:scale-[0.98]">
                Tentar de novo
            </button>
        </div>

        <div id="app-conteudo" class="mt-8 text-center" hidden>
            <h1 class="font-heading text-2xl font-bold md:text-3xl">
                <span id="saudacao-com-nome" hidden>Olá, <span data-usuario-nome></span>!</span>
                <span id="saudacao-sem-nome">Olá!</span>
            </h1>

            <p id="texto-portal" class="mx-auto mt-3 max-w-md text-lg">Em breve você verá seus atendimentos aqui.</p>
            <p id="senha-alterada" role="status" class="mx-auto mt-4 max-w-md rounded-lg bg-rosa-claro px-3 py-2 font-semibold text-rosa-escuro" hidden>
                Senha alterada.
            </p>

            <form id="troca-senha" novalidate class="mx-auto mt-6 max-w-[500px] space-y-4 rounded-xl border-2 border-rosa px-5 py-6 text-left sm:px-6" hidden>
                <div>
                    <h2 class="font-heading text-lg font-bold">Crie sua senha</h2>
                    <p class="mt-1 text-base">Este é seu primeiro acesso. Troque a senha recebida na recepção por uma só sua.</p>
                </div>

                <div>
                    <label for="campo-senha-atual" class="{{ $rotulo }}">Senha atual<span class="text-perigo" aria-hidden="true">*</span></label>
                    <input id="campo-senha-atual" name="senha_atual" type="password" autocomplete="current-password"
                           aria-describedby="erro-senha-atual" class="{{ $campo }}">
                    <p id="erro-senha-atual" data-erro="senha_atual" class="{{ $erro }}" hidden></p>
                </div>
                <div>
                    <label for="campo-nova-senha" class="{{ $rotulo }}">Nova senha<span class="text-perigo" aria-hidden="true">*</span></label>
                    <input id="campo-nova-senha" name="nova_senha" type="password" autocomplete="new-password" minlength="8"
                           aria-describedby="erro-nova-senha dica-nova-senha" class="{{ $campo }}">
                    <p id="dica-nova-senha" class="mt-1 text-sm text-black/60">Pelo menos 8 caracteres.</p>
                    <p id="erro-nova-senha" data-erro="nova_senha" class="{{ $erro }}" hidden></p>
                </div>
                <div>
                    <label for="campo-confirmacao" class="{{ $rotulo }}">Confirmar nova senha<span class="text-perigo" aria-hidden="true">*</span></label>
                    <input id="campo-confirmacao" name="nova_senha_confirmation" type="password" autocomplete="new-password"
                           aria-describedby="erro-confirmacao" class="{{ $campo }}">
                    <p id="erro-confirmacao" data-erro="nova_senha_confirmation" class="{{ $erro }}" hidden></p>
                </div>

                <p id="mensagem-portal" role="alert" class="rounded-lg bg-perigo/10 px-3 py-2 text-sm font-semibold text-perigo" hidden></p>

                <button type="submit" id="botao-trocar"
                        class="h-12 w-full rounded-xl bg-rosa font-heading text-lg font-bold text-white transition hover:bg-rosa-escuro active:scale-[0.99] disabled:cursor-wait disabled:opacity-70 focus:outline-none focus-visible:ring-2 focus-visible:ring-tinta focus-visible:ring-offset-2">
                    Salvar nova senha
                </button>
            </form>

            <button type="button" data-acao="sair"
                    class="mt-8 inline-flex min-h-11 items-center gap-2 rounded-xl border-2 border-rosa px-5 font-heading font-bold text-rosa-escuro transition hover:bg-rosa hover:text-white active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-rosa focus-visible:ring-offset-2">
                Sair
            </button>
        </div>
    </main>
</body>
</html>
