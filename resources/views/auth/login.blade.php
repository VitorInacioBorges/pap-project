<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#009A94">
    <meta name="description" content="Acesso ao sistema do Ambulatório de Saúde UEPG — Prevenção e Educação na Atenção à Saúde da Mulher.">
    <title>Login · Projeto PAP</title>
    <link rel="icon" href="{{ asset('images/pap-logo.svg') }}" type="image/svg+xml">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/pages/login.js'])
</head>
<body data-tema="rosa" class="flex min-h-screen items-center justify-center bg-(--fundo-tema) p-4 font-sans text-tinta antialiased transition-colors md:p-10">
    <main class="w-full max-w-[680px] rounded-3xl bg-white px-5 py-8 shadow-sm sm:px-10 md:py-12">
        <div class="flex items-center justify-center gap-8">
            <img src="{{ asset('images/pap-fullogo.svg') }}" alt="Projeto PAP" class="h-14 w-auto md:h-[72px]">
            <img src="{{ asset('images/uepg.svg') }}" alt="UEPG" class="h-11 w-auto md:h-[52px]">
        </div>

        <h1 class="mt-8 text-center font-heading text-2xl font-bold md:text-3xl">Faça seu Login</h1>
        <p class="mx-auto mt-2 max-w-md text-center text-base leading-snug">
            Ambulatório de Saúde UEPG — Prevenção e Educação na Atenção à Saúde da Mulher
        </p>

        <div class="mx-auto mt-8 w-full max-w-[500px]">
            <div role="tablist" aria-label="Tipo de acesso" class="grid grid-cols-2 overflow-hidden rounded-t-xl">
                <button type="button" role="tab" id="aba-paciente" data-aba="paciente"
                        aria-selected="true" aria-controls="painel-login"
                        class="flex min-h-12 items-center justify-center gap-2 px-2 py-2 font-heading text-base font-bold leading-tight transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-tinta aria-selected:bg-(--destaque) aria-selected:text-white aria-[selected=false]:bg-(--destaque-claro) aria-[selected=false]:text-(--destaque-escuro) aria-[selected=false]:hover:brightness-95 sm:text-lg">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="12" cy="8" r="4" /><path d="M4 21a8 8 0 0 1 16 0" stroke-linecap="round" />
                    </svg>
                    Sou Paciente
                </button>
                <button type="button" role="tab" id="aba-colaborador" data-aba="colaborador"
                        aria-selected="false" aria-controls="painel-login" tabindex="-1"
                        class="flex min-h-12 items-center justify-center gap-2 px-2 py-2 font-heading text-base font-bold leading-tight transition focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-tinta aria-selected:bg-(--destaque) aria-selected:text-white aria-[selected=false]:bg-(--destaque-claro) aria-[selected=false]:text-(--destaque-escuro) aria-[selected=false]:hover:brightness-95 sm:text-lg">
                    <span class="h-5 w-6 shrink-0 bg-current" style="mask: url('{{ asset('images/pap-logo.svg') }}') center / contain no-repeat; -webkit-mask: url('{{ asset('images/pap-logo.svg') }}') center / contain no-repeat;" aria-hidden="true"></span>
                    Sou do ProjetoPAP
                </button>
            </div>

            <form id="painel-login" role="tabpanel" aria-labelledby="aba-paciente" novalidate
                  class="space-y-4 rounded-b-xl border-2 border-(--destaque) px-5 py-6 sm:px-6">
                <div>
                    <label for="campo-login" class="block font-heading text-sm font-bold">
                        <span id="rotulo-login">E-mail</span><span class="text-perigo" aria-hidden="true">*</span>
                    </label>
                    <input id="campo-login" name="login" type="email" required autocomplete="username"
                           placeholder="seu-email@exemplo.com" aria-describedby="erro-login"
                           class="mt-1 h-12 w-full rounded-xl border-2 border-black/10 bg-campo px-3 text-base transition placeholder:text-black/40 focus:border-(--destaque) focus:bg-white focus:outline-none aria-invalid:border-perigo">
                    <p id="erro-login" data-erro="login" class="mt-1 text-sm text-perigo" hidden></p>
                </div>

                <div>
                    <label for="campo-senha" class="block font-heading text-sm font-bold">
                        Senha<span class="text-perigo" aria-hidden="true">*</span>
                    </label>
                    <input id="campo-senha" name="senha" type="password" required autocomplete="current-password"
                           placeholder="••••••••" aria-describedby="erro-senha"
                           class="mt-1 h-12 w-full rounded-xl border-2 border-black/10 bg-campo px-3 text-base transition placeholder:text-black/40 focus:border-(--destaque) focus:bg-white focus:outline-none aria-invalid:border-perigo">
                    <p id="erro-senha" data-erro="senha" class="mt-1 text-sm text-perigo" hidden></p>
                </div>

                <p id="mensagem-login" role="alert" class="rounded-lg bg-perigo/10 px-3 py-2 text-sm font-semibold text-perigo" hidden></p>

                <button type="submit" id="botao-entrar"
                        class="h-12 w-full rounded-xl bg-(--destaque) font-heading text-lg font-bold text-white transition hover:bg-(--destaque-escuro) active:scale-[0.99] disabled:cursor-wait disabled:opacity-70 focus:outline-none focus-visible:ring-2 focus-visible:ring-tinta focus-visible:ring-offset-2">
                    Entrar
                </button>

                {{-- DECISAO: link "Não sei qual é meu login" — escolhido: texto de ajuda na própria tela; alternativas: tela de recuperação por e-mail --}}
                <div id="ajuda-paciente" class="text-center">
                    <button type="button" id="botao-ajuda" aria-expanded="false" aria-controls="texto-ajuda"
                            class="inline-flex min-h-11 items-center gap-1.5 rounded-lg px-2 text-sm font-semibold text-black/60 transition hover:text-tinta focus:outline-none focus-visible:ring-2 focus-visible:ring-(--destaque)">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" /><path d="M9.5 9a2.5 2.5 0 0 1 4.9.8c0 1.7-2.4 2.2-2.4 3.7M12 17h.01" stroke-linecap="round" />
                        </svg>
                        Não sei qual é meu login
                    </button>
                    <p id="texto-ajuda" class="mx-auto mt-2 max-w-sm text-sm leading-snug" hidden>
                        Seu login é o e-mail informado no seu cadastro. Em caso de dúvida, procure a recepção do ambulatório.
                    </p>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
