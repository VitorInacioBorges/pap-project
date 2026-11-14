import {
    api,
    desabilitarEnquanto,
    ErroValidacao,
    limparErros,
    mostrarErros,
    protegerPagina,
    rotuloCargo,
    setToken,
} from '../api';

const usuario = await protegerPagina();

if (usuario) {
    const formulario = document.getElementById('form-configuracoes');
    const campoNome = document.getElementById('campo-nome');
    const senhaAtual = document.getElementById('campo-senha-atual');
    const novaSenha = document.getElementById('campo-nova-senha');
    const confirmacao = document.getElementById('campo-confirmacao');
    const mensagem = document.getElementById('mensagem-configuracoes');
    const botao = document.getElementById('botao-salvar');

    let nomeSalvo = usuario.nome ?? '';
    let primeiroAcesso = usuario.first_login;

    campoNome.value = nomeSalvo;
    document.getElementById('campo-cargo').value = rotuloCargo(usuario.cargo);
    document.getElementById('campo-identificacao').value = usuario.alias ?? '';
    document.getElementById('aviso-primeiro-acesso').hidden = !primeiroAcesso;
    document.getElementById('dica-nova-senha').textContent = primeiroAcesso
        ? 'Obrigatória no primeiro acesso. Pelo menos 8 caracteres.'
        : 'Opcional. Deixe em branco para manter a senha atual.';

    const mostrarMensagem = (texto, sucesso) => {
        mensagem.textContent = texto;
        mensagem.hidden = !texto;
        mensagem.className = `mt-5 rounded-lg px-3 py-2 text-sm font-semibold ${sucesso ? 'bg-pap/10 text-pap-escuro' : 'bg-perigo/10 text-perigo'}`;
    };

    const salvarNome = async (nome, senha) => {
        const atualizado = await api('/me', { method: 'PATCH', body: { nome, senha_atual: senha } });
        nomeSalvo = atualizado.nome ?? '';
        document.querySelectorAll('[data-usuario-nome]').forEach((el) => {
            el.textContent = atualizado.nome || atualizado.alias;
        });
    };

    const trocarSenha = async () => {
        const resposta = await api('/alterar-senha', {
            method: 'POST',
            body: {
                senha_atual: senhaAtual.value,
                nova_senha: novaSenha.value,
                nova_senha_confirmation: confirmacao.value,
            },
        });
        setToken(resposta.token);
        senhaAtual.value = '';
        novaSenha.value = '';
        confirmacao.value = '';
    };

    formulario.addEventListener('submit', (evento) => {
        evento.preventDefault();
        limparErros(formulario);
        mostrarMensagem('');

        const nome = campoNome.value.trim();
        const querTrocarSenha = novaSenha.value !== '' || confirmacao.value !== '';
        const nomeMudou = nome !== nomeSalvo;

        const faltando = {};
        if (nomeMudou && nome === '') faltando.nome = ['Informe o nome completo.'];
        if (primeiroAcesso && novaSenha.value === '') faltando.nova_senha = ['Defina uma nova senha para continuar.'];
        if ((nomeMudou || querTrocarSenha || primeiroAcesso) && senhaAtual.value === '') faltando.senha_atual = ['Informe a senha atual para confirmar as alterações.'];
        if (Object.keys(faltando).length > 0) {
            mostrarErros(formulario, faltando);
            return;
        }

        if (!nomeMudou && !querTrocarSenha) {
            mostrarMensagem('Nada para salvar.', true);
            return;
        }

        desabilitarEnquanto(botao, async () => {
            try {
                // No primeiro acesso, PATCH /me fica bloqueado até a senha ser trocada.
                if (primeiroAcesso) {
                    const senhaAposTroca = novaSenha.value;
                    await trocarSenha();
                    primeiroAcesso = false;
                    if (nomeMudou) await salvarNome(nome, senhaAposTroca);
                    window.location.replace('/inicio');
                    return;
                }

                if (querTrocarSenha) {
                    const senhaAposTroca = novaSenha.value;
                    await trocarSenha();
                    if (nomeMudou) await salvarNome(nome, senhaAposTroca);
                } else if (nomeMudou) {
                    await salvarNome(nome, senhaAtual.value);
                    senhaAtual.value = '';
                }
                mostrarMensagem('Alterações salvas.', true);
            } catch (erro) {
                if (erro instanceof ErroValidacao) {
                    if (!mostrarErros(formulario, erro.erros)) mostrarMensagem(erro.message, false);
                } else {
                    mostrarMensagem(erro.message, false);
                }
            }
        });
    });
}
