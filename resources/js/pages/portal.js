import { api, desabilitarEnquanto, ErroValidacao, limparErros, mostrarErros, protegerPagina, sair, setToken } from '../api';

document.querySelectorAll('[data-acao="sair"]').forEach((botao) => {
    botao.addEventListener('click', () => {
        botao.disabled = true;
        sair();
    });
});

const usuario = await protegerPagina();

if (usuario) {
    const formulario = document.getElementById('troca-senha');
    const texto = document.getElementById('texto-portal');
    const mensagem = document.getElementById('mensagem-portal');

    document.getElementById('saudacao-com-nome').hidden = !usuario.nome;
    document.getElementById('saudacao-sem-nome').hidden = Boolean(usuario.nome);

    formulario.hidden = !usuario.first_login;
    texto.hidden = usuario.first_login;

    formulario.addEventListener('submit', (evento) => {
        evento.preventDefault();
        limparErros(formulario);
        mensagem.hidden = true;

        const corpo = Object.fromEntries(new FormData(formulario));
        const faltando = {};
        if (!corpo.senha_atual) faltando.senha_atual = ['Informe a senha atual.'];
        if (!corpo.nova_senha) faltando.nova_senha = ['Informe a nova senha.'];
        if (Object.keys(faltando).length > 0) {
            mostrarErros(formulario, faltando);
            return;
        }

        desabilitarEnquanto(document.getElementById('botao-trocar'), async () => {
            try {
                const resposta = await api('/pacientes/alterar-senha', { method: 'POST', body: corpo });
                setToken(resposta.token);
                formulario.reset();
                formulario.hidden = true;
                texto.hidden = false;
                document.getElementById('senha-alterada').hidden = false;
            } catch (erro) {
                if (erro instanceof ErroValidacao && mostrarErros(formulario, erro.erros)) return;
                mensagem.textContent = erro.message;
                mensagem.hidden = false;
            }
        });
    });
}
