import { api, desabilitarEnquanto, ErroValidacao, limparErros, mostrarErros, protegerPagina } from '../api';

const usuario = await protegerPagina();

if (usuario) {
    const $ = (id) => document.getElementById(id);

    const corpoTabela = $('corpo-tabela');
    const modeloLinha = $('linha-paciente');
    const carregando = $('lista-carregando');
    const vazia = $('lista-vazia');
    const erroLista = $('lista-erro');
    const botaoMais = $('botao-mais');
    const aviso = $('aviso-lista');
    const menuAcoes = $('menu-acoes');
    const painelFiltros = $('painel-filtros');
    const botaoFiltros = $('botao-filtros');
    const janela = $('janela-paciente');
    const formulario = $('form-paciente');
    const confirmar = $('confirmar-exclusao');

    const estado = { busca: '', ordem: 'recentes', status: ['ativos'], pagina: 1, ultimaPagina: 1, itens: [] };
    let requisicao = 0;
    let pacienteDoMenu = null;
    let botaoDoMenu = null;
    let emEdicao = null;
    let temporizadorAviso;

    const nomeDe = (p) => p.dados_pessoais?.nome || p.email;

    const formatarData = (iso) => {
        if (!iso) return '—';
        const [ano, mes, dia] = String(iso).slice(0, 10).split('-');
        return `${dia}/${mes}/${ano}`;
    };

    const avisar = (texto) => {
        aviso.textContent = texto;
        aviso.hidden = false;
        clearTimeout(temporizadorAviso);
        temporizadorAviso = setTimeout(() => {
            aviso.hidden = true;
        }, 4000);
    };

    // ---------- Lista ----------

    const montarLinha = (paciente) => {
        const linha = modeloLinha.content.firstElementChild.cloneNode(true);
        linha.querySelectorAll('[data-campo="nome"]').forEach((el) => {
            el.textContent = nomeDe(paciente);
            el.title = nomeDe(paciente);
        });
        linha.querySelectorAll('[data-campo="cpf"]').forEach((el) => {
            el.textContent = paciente.cpf;
        });
        linha.querySelectorAll('[data-campo="nascimento"]').forEach((el) => {
            el.textContent = formatarData(paciente.dados_pessoais?.data_nascimento);
        });
        const status = linha.querySelector('[data-campo="status"]');
        status.textContent = paciente.ativo ? 'Ativo' : 'Inativo';
        status.classList.add(...(paciente.ativo ? ['border-ativo', 'text-ativo'] : ['border-perigo', 'text-perigo']));
        const botao = linha.querySelector('[data-acao="menu"]');
        botao.setAttribute('aria-label', `Ações de ${nomeDe(paciente)}`);
        botao.addEventListener('click', () => abrirMenu(paciente, botao));
        return linha;
    };

    const desenhar = () => {
        corpoTabela.replaceChildren(...estado.itens.map(montarLinha));
        vazia.hidden = estado.itens.length > 0;
        botaoMais.hidden = estado.pagina >= estado.ultimaPagina;
    };

    const carregar = async ({ acrescentar = false } = {}) => {
        const minha = ++requisicao;
        const parametros = new URLSearchParams({ ordem: estado.ordem, page: String(estado.pagina) });
        if (estado.busca) parametros.set('busca', estado.busca);
        estado.status.forEach((s) => parametros.append('status[]', s));

        erroLista.hidden = true;
        if (!acrescentar) {
            carregando.hidden = false;
            vazia.hidden = true;
        }

        try {
            const resposta = await api(`/pacientes?${parametros}`);
            if (minha !== requisicao) return;
            estado.itens = acrescentar ? [...estado.itens, ...resposta.data] : resposta.data;
            estado.ultimaPagina = resposta.last_page;
            desenhar();
        } catch (erro) {
            if (minha !== requisicao) return;
            $('lista-erro-texto').textContent = erro.message;
            erroLista.hidden = false;
            if (!acrescentar) corpoTabela.replaceChildren();
            botaoMais.hidden = true;
        } finally {
            if (minha === requisicao) carregando.hidden = true;
        }
    };

    const recarregar = () => {
        estado.pagina = 1;
        return carregar();
    };

    // DECISAO: quando a busca dispara — escolhido: ao digitar com espera de 300 ms; alternativas: só no Enter ou na lupa
    let esperaBusca;
    $('campo-busca').addEventListener('input', (evento) => {
        clearTimeout(esperaBusca);
        esperaBusca = setTimeout(() => {
            estado.busca = evento.target.value.trim();
            recarregar();
        }, 300);
    });

    botaoMais.addEventListener('click', () =>
        desabilitarEnquanto(botaoMais, async () => {
            estado.pagina += 1;
            await carregar({ acrescentar: true });
        }),
    );

    $('botao-tentar').addEventListener('click', () => carregar());

    // ---------- Filtros ----------

    const abrirFiltros = (aberto) => {
        painelFiltros.hidden = !aberto;
        botaoFiltros.setAttribute('aria-expanded', aberto ? 'true' : 'false');
        if (aberto) painelFiltros.querySelector('input:checked')?.focus();
    };

    botaoFiltros.addEventListener('click', () => abrirFiltros(painelFiltros.hidden));

    painelFiltros.addEventListener('change', (evento) => {
        const alvo = evento.target;
        if (alvo.name === 'ordem') estado.ordem = alvo.value;
        if (alvo.name === 'status') {
            const marcados = [...painelFiltros.querySelectorAll('input[name="status"]:checked')].map((el) => el.value);
            if (marcados.length === 0) {
                alvo.checked = true;
                return;
            }
            estado.status = marcados;
        }
        recarregar();
    });

    // ---------- Menu de ações ----------

    const fecharMenu = (devolverFoco = true) => {
        if (menuAcoes.hidden) return;
        menuAcoes.hidden = true;
        botaoDoMenu?.setAttribute('aria-expanded', 'false');
        if (devolverFoco) botaoDoMenu?.focus();
        pacienteDoMenu = null;
    };

    function abrirMenu(paciente, botao) {
        if (!menuAcoes.hidden && pacienteDoMenu === paciente) {
            fecharMenu();
            return;
        }
        fecharMenu(false);
        pacienteDoMenu = paciente;
        botaoDoMenu = botao;
        menuAcoes.querySelector('[data-item="alternar"]').textContent = paciente.ativo ? 'Ocultar' : 'Reativar';
        menuAcoes.hidden = false;
        const caixa = botao.getBoundingClientRect();
        const largura = menuAcoes.offsetWidth;
        const altura = menuAcoes.offsetHeight;
        const topo = caixa.bottom + 6 + altura > window.innerHeight ? caixa.top - altura - 6 : caixa.bottom + 6;
        menuAcoes.style.top = `${Math.max(8, topo)}px`;
        menuAcoes.style.left = `${Math.min(window.innerWidth - largura - 8, caixa.right - largura)}px`;
        botao.setAttribute('aria-expanded', 'true');
        menuAcoes.querySelector('[role="menuitem"]').focus();
    }

    menuAcoes.addEventListener('keydown', (evento) => {
        const itens = [...menuAcoes.querySelectorAll('[role="menuitem"]')];
        const atual = itens.indexOf(document.activeElement);
        if (evento.key === 'ArrowDown' || evento.key === 'ArrowUp') {
            evento.preventDefault();
            const passo = evento.key === 'ArrowDown' ? 1 : -1;
            itens[(atual + passo + itens.length) % itens.length].focus();
        } else if (evento.key === 'Tab') {
            fecharMenu(false);
        }
    });

    menuAcoes.addEventListener('click', (evento) => {
        const item = evento.target.closest('[data-item]')?.dataset.item;
        const paciente = pacienteDoMenu;
        if (!item || !paciente) return;
        fecharMenu(false);
        if (item === 'editar') abrirJanela(paciente);
        if (item === 'alternar') alternarStatus(paciente);
        if (item === 'excluir') pedirExclusao(paciente);
    });

    document.addEventListener('click', (evento) => {
        if (!menuAcoes.hidden && !menuAcoes.contains(evento.target) && !evento.target.closest('[data-acao="menu"]')) {
            fecharMenu(false);
        }
        if (!painelFiltros.hidden && !painelFiltros.contains(evento.target) && !botaoFiltros.contains(evento.target)) {
            abrirFiltros(false);
        }
    });

    document.addEventListener('keydown', (evento) => {
        if (evento.key !== 'Escape') return;
        if (!menuAcoes.hidden) fecharMenu();
        if (!painelFiltros.hidden) {
            abrirFiltros(false);
            botaoFiltros.focus();
        }
    });

    window.addEventListener('resize', () => fecharMenu(false));
    window.addEventListener('scroll', () => fecharMenu(false), { passive: true });

    const alternarStatus = async (paciente) => {
        try {
            if (paciente.ativo) {
                await api(`/pacientes/${paciente.id_paciente}`, { method: 'DELETE' });
                avisar(`${nomeDe(paciente)} foi ocultada.`);
            } else {
                await api(`/pacientes/${paciente.id_paciente}/reativar`, { method: 'POST' });
                avisar(`${nomeDe(paciente)} foi reativada.`);
            }
            await recarregar();
        } catch (erro) {
            avisar(erro.message);
        }
    };

    const pedirExclusao = (paciente) => {
        $('nome-exclusao').textContent = nomeDe(paciente);
        confirmar.dataset.id = paciente.id_paciente;
        confirmar.dataset.nome = nomeDe(paciente);
        confirmar.showModal();
    };

    $('botao-confirmar-exclusao').addEventListener('click', (evento) =>
        desabilitarEnquanto(evento.currentTarget, async () => {
            try {
                await api(`/pacientes/${confirmar.dataset.id}/excluir`, { method: 'POST' });
                confirmar.close();
                avisar(`${confirmar.dataset.nome} foi excluída.`);
                await recarregar();
            } catch (erro) {
                confirmar.close();
                avisar(erro.message);
            }
        }),
    );

    // ---------- Janela de cadastro e edição ----------

    const campoCpf = $('campo-cpf');
    campoCpf.addEventListener('input', () => {
        const d = campoCpf.value.replace(/\D/g, '').slice(0, 11);
        const partes = [d.slice(0, 3), d.slice(3, 6), d.slice(6, 9)].filter(Boolean).join('.');
        campoCpf.value = d.length > 9 ? `${partes}-${d.slice(9)}` : partes;
    });

    const camposDoFormulario = () => [...formulario.querySelectorAll('input[name], select[name]')];

    function abrirJanela(paciente = null) {
        emEdicao = paciente;
        formulario.reset();
        limparErros(formulario);
        $('mensagem-janela').hidden = true;
        $('titulo-janela').textContent = paciente ? 'Editar Paciente' : 'Nova Paciente';
        $('senha-obrigatoria').hidden = Boolean(paciente);
        const campoSenha = $('campo-senha');
        campoSenha.required = !paciente;
        campoSenha.placeholder = paciente ? 'Deixe em branco para manter' : '••••••••';

        if (paciente) {
            const dados = paciente.dados_pessoais ?? {};
            for (const campo of camposDoFormulario()) {
                const [grupo, chave] = campo.name.split('.');
                let valor = chave ? dados[chave] : paciente[grupo];
                if (campo.name === 'senha') valor = '';
                if (campo.type === 'date' && valor) valor = String(valor).slice(0, 10);
                if (['dados_pessoais.peso', 'dados_pessoais.altura', 'dados_pessoais.CA'].includes(campo.name) && valor != null) {
                    valor = String(Number(valor)).replace('.', ',');
                }
                campo.value = valor ?? '';
            }
        }

        janela.showModal();
        formulario.querySelector('input')?.focus();
    }

    $('botao-nova').addEventListener('click', () => abrirJanela());

    document.querySelectorAll('dialog [data-fechar]').forEach((botao) => {
        botao.addEventListener('click', () => botao.closest('dialog').close());
    });

    const NUMERICOS = ['peso', 'altura', 'CA'];

    const montarCorpo = () => {
        const corpo = { dados_pessoais: {} };
        for (const campo of camposDoFormulario()) {
            const [grupo, chave] = campo.name.split('.');
            let valor = campo.value.trim();
            if (chave) {
                if (NUMERICOS.includes(chave) && valor) valor = valor.replace(',', '.');
                corpo.dados_pessoais[chave] = valor === '' ? null : valor;
            } else if (grupo === 'senha') {
                if (campo.value !== '') corpo.senha = campo.value;
            } else {
                corpo[grupo] = valor;
            }
        }
        return corpo;
    };

    formulario.addEventListener('submit', (evento) => {
        evento.preventDefault();
        limparErros(formulario);
        $('mensagem-janela').hidden = true;

        const corpo = montarCorpo();
        const faltando = {};
        if (!corpo.dados_pessoais.nome) faltando['dados_pessoais.nome'] = ['Informe o nome completo.'];
        if (!corpo.cpf) faltando.cpf = ['Informe o CPF.'];
        if (!corpo.dados_pessoais.data_nascimento) faltando['dados_pessoais.data_nascimento'] = ['Informe a data de nascimento.'];
        if (!corpo.email) faltando.email = ['Informe o e-mail.'];
        if (!emEdicao && !corpo.senha) faltando.senha = ['Defina uma senha para o portal.'];
        if (Object.keys(faltando).length > 0) {
            mostrarErros(formulario, faltando);
            return;
        }

        desabilitarEnquanto($('botao-salvar-paciente'), async () => {
            try {
                if (emEdicao) {
                    await api(`/pacientes/${emEdicao.id_paciente}`, { method: 'PUT', body: corpo });
                } else {
                    await api('/pacientes', { method: 'POST', body: corpo });
                }
                janela.close();
                avisar('Paciente salva.');
                await recarregar();
            } catch (erro) {
                if (erro instanceof ErroValidacao) {
                    if (!mostrarErros(formulario, erro.erros)) {
                        $('mensagem-janela').textContent = erro.message;
                        $('mensagem-janela').hidden = false;
                    }
                } else {
                    $('mensagem-janela').textContent = erro.message;
                    $('mensagem-janela').hidden = false;
                }
            }
        });
    });

    carregar();
}
