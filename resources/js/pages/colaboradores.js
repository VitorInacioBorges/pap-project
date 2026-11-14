import { api, desabilitarEnquanto, ErroValidacao, limparErros, mostrarErros, protegerPagina, rotuloCargo } from '../api';

const usuario = await protegerPagina();

if (usuario) {
    const $ = (id) => document.getElementById(id);
    const formulario = $('form-colaborador');
    const dialogoStatus = $('confirmar-status-colaborador');
    let colaboradorSelecionado = null;
    const ocultarCredenciais = () => {
        $('credenciais-colaborador').hidden = true;
        $('novo-alias').textContent = '';
        $('nova-senha').textContent = '';
    };
    $('ocultar-credenciais').addEventListener('click', ocultarCredenciais);

    const carregar = async () => {
        $('carregando-colaboradores').hidden = false;
        $('erro-lista-colaboradores').hidden = true;
        try {
            const colaboradores = await api('/users');
            $('lista-colaboradores').replaceChildren(...colaboradores.map((colaborador) => {
                const item = $('modelo-colaborador').content.firstElementChild.cloneNode(true);
                for (const campo of ['nome', 'email', 'alias']) item.querySelector(`[data-${campo}]`).textContent = colaborador[campo] || 'Não informado';
                item.querySelector('[data-cargo]').textContent = rotuloCargo(colaborador.cargo);
                item.querySelector('[data-status]').textContent = !colaborador.ativo ? 'Inativo' : colaborador.first_login ? 'Aguardando primeiro acesso' : 'Ativo';
                const botaoStatus = item.querySelector('[data-alterar-status]');
                if (colaborador.alias !== usuario.alias) {
                    botaoStatus.hidden = false;
                    botaoStatus.textContent = colaborador.ativo ? 'Desativar colaborador' : 'Reativar colaborador';
                    botaoStatus.classList.add(...(colaborador.ativo ? ['border-perigo', 'text-perigo', 'hover:bg-perigo/10'] : ['border-pap', 'text-pap-escuro', 'hover:bg-pap-fundo']));
                    botaoStatus.addEventListener('click', () => {
                        colaboradorSelecionado = colaborador;
                        $('titulo-status-colaborador').textContent = colaborador.ativo ? 'Desativar colaborador?' : 'Reativar colaborador?';
                        $('nome-status-colaborador').textContent = colaborador.nome || colaborador.alias;
                        $('descricao-status-colaborador').textContent = colaborador.ativo
                            ? 'O acesso será bloqueado e as sessões atuais serão encerradas. Todas as fichas e registros continuarão vinculados a este colaborador.'
                            : 'O colaborador poderá entrar novamente com suas credenciais. As sessões antigas continuarão encerradas.';
                        $('salvar-status-colaborador').textContent = colaborador.ativo ? 'Confirmar desativação' : 'Confirmar reativação';
                        $('erro-status-colaborador').hidden = true;
                        dialogoStatus.showModal();
                    });
                }
                return item;
            }));
        } catch (erro) {
            $('mensagem-lista-colaboradores').textContent = erro.message;
            $('erro-lista-colaboradores').hidden = false;
        } finally {
            $('carregando-colaboradores').hidden = true;
        }
    };
    $('recarregar-colaboradores').addEventListener('click', (evento) => desabilitarEnquanto(evento.currentTarget, carregar));
    $('cancelar-status-colaborador').addEventListener('click', () => dialogoStatus.close());
    dialogoStatus.addEventListener('cancel', (evento) => {
        if ($('salvar-status-colaborador').disabled) evento.preventDefault();
    });
    $('salvar-status-colaborador').addEventListener('click', (evento) => {
        const colaborador = colaboradorSelecionado;
        if (!colaborador) return;
        desabilitarEnquanto(evento.currentTarget, async () => {
            $('cancelar-status-colaborador').disabled = true;
            $('erro-status-colaborador').hidden = true;
            try {
                await api(`/users/${colaborador.id_usuario}`, { method: 'PUT', body: { ativo: !colaborador.ativo } });
                dialogoStatus.close();
                $('aviso-status-colaborador').textContent = `${colaborador.nome || colaborador.alias}: acesso ${colaborador.ativo ? 'desativado' : 'reativado'}. Os registros foram preservados.`;
                $('aviso-status-colaborador').hidden = false;
                await carregar();
            } catch (erro) {
                $('erro-status-colaborador').textContent = erro.message;
                $('erro-status-colaborador').hidden = false;
            } finally {
                $('cancelar-status-colaborador').disabled = false;
            }
        });
    });

    formulario.addEventListener('submit', (evento) => {
        evento.preventDefault();
        if (!formulario.reportValidity()) return;
        desabilitarEnquanto($('salvar-colaborador'), async () => {
            limparErros(formulario);
            ocultarCredenciais();
            $('erro-cadastro').hidden = true;
            try {
                const novo = await api('/users', {
                    method: 'POST',
                    body: Object.fromEntries(new FormData(formulario).entries()),
                });
                formulario.reset();
                $('novo-alias').textContent = novo.alias;
                $('nova-senha').textContent = novo.senhaTemporaria;
                $('credenciais-colaborador').hidden = false;
                await carregar();
            } catch (erro) {
                if (!(erro instanceof ErroValidacao) || !mostrarErros(formulario, erro.erros)) {
                    $('erro-cadastro').textContent = erro.message;
                    $('erro-cadastro').hidden = false;
                }
            }
        });
    });

    carregar();
}
