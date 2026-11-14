@php
    $campo = 'mt-1 h-11 w-full rounded-xl border-2 border-black/10 bg-campo px-3 text-base transition placeholder:text-black/35 focus:border-pap focus:bg-white focus:outline-none aria-invalid:border-perigo';
    $rotulo = 'block font-heading text-sm font-bold';
    $erro = 'mt-1 text-sm text-perigo';
    $secao = 'font-heading text-base font-bold text-pap md:text-lg';
    $obrigatorio = '<span class="text-perigo" aria-hidden="true">*</span>';

    $campos = [
        'pessoais' => [
            ['dados_pessoais.nome', 'Nome Completo', 'text', true, 'md:col-span-2', ['autocomplete' => 'off', 'maxlength' => 255]],
            ['cpf', 'CPF', 'text', true, '', ['inputmode' => 'numeric', 'placeholder' => '000.000.000-00', 'maxlength' => 14]],
            ['dados_pessoais.data_nascimento', 'Data de Nascimento', 'date', true, '', []],
            ['dados_pessoais.telefone', 'Telefone', 'tel', false, '', ['inputmode' => 'tel', 'placeholder' => '(42) 99999-9999', 'maxlength' => 30]],
            ['dados_pessoais.profissao', 'Profissão', 'text', false, '', ['maxlength' => 255]],
        ],
    ];

    $idDe = fn (string $nome) => 'campo-'.str_replace(['.', '_'], '-', strtolower($nome));
@endphp

<dialog id="janela-paciente" aria-labelledby="titulo-janela"
        class="m-auto max-h-[calc(100dvh-2rem)] w-[min(900px,calc(100%-2rem))] overflow-y-auto rounded-2xl border-2 border-pap bg-white p-0 text-tinta">
    <form id="form-paciente" novalidate class="p-5 md:p-7">
        <h2 id="titulo-janela" class="text-center font-heading text-2xl font-bold md:text-3xl">Nova Paciente</h2>

        @foreach (['pessoais' => 'Informações Pessoais'] as $grupo => $titulo)
            <fieldset class="mt-5">
                <legend class="{{ $secao }}">{{ $titulo }}</legend>
                <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-3">
                    @foreach ($campos[$grupo] as [$nome, $texto, $tipo, $req, $classe, $attrs])
                        @php $id = $idDe($nome); @endphp
                        <div class="col-span-2 md:col-span-1 {{ $classe }}">
                            <label for="{{ $id }}" class="{{ $rotulo }}">{{ $texto }}{!! $req ? $obrigatorio : '' !!}</label>
                            <input id="{{ $id }}" name="{{ $nome }}" type="{{ $tipo }}" @if ($req) required @endif
                                   aria-describedby="erro-{{ $id }}" class="{{ $campo }}"
                                   @foreach ($attrs as $attr => $valor) {{ $attr }}="{{ $valor }}" @endforeach>
                            <p id="erro-{{ $id }}" data-erro="{{ $nome }}" class="{{ $erro }}" hidden></p>
                        </div>
                    @endforeach

                    @if ($grupo === 'pessoais')
                        @foreach ([['dados_pessoais.estado_civil', 'Estado Civil', \App\Support\OpcoesPaciente::ESTADOS_CIVIS], ['dados_pessoais.escolaridade', 'Escolaridade', \App\Support\OpcoesPaciente::ESCOLARIDADES]] as [$nome, $texto, $opcoes])
                            @php $id = $idDe($nome); @endphp
                            <div class="col-span-2 md:col-span-1">
                                <label for="{{ $id }}" class="{{ $rotulo }}">{{ $texto }}</label>
                                <select id="{{ $id }}" name="{{ $nome }}" aria-describedby="erro-{{ $id }}" class="{{ $campo }}">
                                    <option value="">Selecione</option>
                                    @foreach ($opcoes as $opcao)
                                        <option value="{{ $opcao }}">{{ $opcao }}</option>
                                    @endforeach
                                </select>
                                <p id="erro-{{ $id }}" data-erro="{{ $nome }}" class="{{ $erro }}" hidden></p>
                            </div>
                        @endforeach
                    @endif
                </div>
            </fieldset>
        @endforeach

        <fieldset class="mt-5">
            <legend class="{{ $secao }}">Informações para Login</legend>
            <div class="mt-2 grid grid-cols-2 gap-x-3 gap-y-3">
                <div class="col-span-2 md:col-span-1">
                    <label for="campo-email" class="{{ $rotulo }}">E-mail (login do portal da paciente){!! $obrigatorio !!}</label>
                    <input id="campo-email" name="email" type="email" required autocomplete="off" maxlength="255"
                           aria-describedby="erro-campo-email" class="{{ $campo }}">
                    <p id="erro-campo-email" data-erro="email" class="{{ $erro }}" hidden></p>
                </div>
                <div class="col-span-2 md:col-span-1">
                    <div id="senha-manual">
                    <label for="campo-senha" class="{{ $rotulo }}">Senha<span id="senha-obrigatoria">{!! $obrigatorio !!}</span></label>
                    <input id="campo-senha" name="senha" type="password" autocomplete="new-password" minlength="8"
                           placeholder="••••••••" aria-describedby="erro-campo-senha dica-campo-senha" class="{{ $campo }}">
                    <p id="dica-campo-senha" class="mt-1 text-sm text-black/60">Pelo menos 8 caracteres.</p>
                    <p id="erro-campo-senha" data-erro="senha" class="{{ $erro }}" hidden></p>
                    </div>
                    <div id="reenvio-senha" hidden>
                        <p class="{{ $rotulo }}">Recuperar acesso</p>
                        <button id="botao-reenviar-senha" type="button" aria-describedby="dica-reenvio-senha" aria-controls="aviso-reenvio-senha" aria-expanded="false"
                                class="mt-1 inline-flex min-h-11 items-center rounded-xl border-2 border-pap px-4 font-heading text-sm font-bold text-pap-escuro hover:bg-pap-fundo disabled:cursor-wait disabled:opacity-70 focus-visible:outline-2 focus-visible:outline-pap">Reenviar senha temporária</button>
                        <div id="aviso-reenvio-senha" role="status" class="mt-2 rounded-xl border border-pap/20 bg-pap-fundo p-3" hidden>
                            <p id="dica-reenvio-senha" class="text-sm text-black/70"></p>
                            <p class="mt-1 text-sm text-black/70">Salve alterações de e-mail antes de reenviar. A paciente precisará trocar a senha ao entrar.</p>
                            <p class="mt-2 text-sm font-semibold">Clique novamente em “Confirmar reenvio” para continuar.</p>
                            <button id="cancelar-reenvio-senha" type="button" class="mt-1 min-h-11 rounded-lg px-2 text-sm font-semibold text-pap-escuro underline focus-visible:outline-2 focus-visible:outline-pap">Cancelar reenvio</button>
                        </div>
                        <p id="sucesso-reenvio-senha" role="status" class="mt-2 text-sm font-semibold text-pap-escuro" hidden></p>
                    </div>
                </div>
            </div>
        </fieldset>

        <p id="mensagem-janela" role="alert" class="mt-4 rounded-lg bg-perigo/10 px-3 py-2 text-sm font-semibold text-perigo" hidden></p>

        <div class="mt-6 grid grid-cols-2 gap-3 md:flex md:justify-end">
            <button type="button" data-fechar
                    class="min-h-11 rounded-xl border-2 border-perigo bg-white px-5 font-heading text-base font-bold text-perigo transition hover:bg-perigo hover:text-white active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-perigo focus-visible:ring-offset-2">
                Cancelar
            </button>
            <button type="submit" id="botao-salvar-paciente"
                    class="min-h-11 rounded-xl bg-pap px-6 font-heading text-base font-bold text-white transition hover:bg-pap-escuro active:scale-[0.98] disabled:cursor-wait disabled:opacity-70 focus:outline-none focus-visible:ring-2 focus-visible:ring-pap focus-visible:ring-offset-2">
                Salvar
            </button>
        </div>
    </form>
</dialog>
