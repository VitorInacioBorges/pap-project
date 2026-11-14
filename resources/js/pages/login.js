import {
    api,
    carregarUsuario,
    desabilitarEnquanto,
    ErroApi,
    ErroValidacao,
    getToken,
    limparErros,
    limparSessao,
    mostrarErros,
    paginaInicial,
    setToken,
} from '../api';

const formulario = document.getElementById('painel-login');
const campoLogin = document.getElementById('campo-login');
const campoSenha = document.getElementById('campo-senha');
const rotuloLogin = document.getElementById('rotulo-login');
const mensagem = document.getElementById('mensagem-login');
const botaoEntrar = document.getElementById('botao-entrar');
const ajuda = document.getElementById('ajuda-paciente');
const botaoAjuda = document.getElementById('botao-ajuda');
const textoAjuda = document.getElementById('texto-ajuda');
const abas = [...document.querySelectorAll('[role="tab"]')];

const PERFIS = {
    paciente: {
        tema: 'rosa',
        rotulo: 'E-mail',
        tipo: 'email',
        placeholder: 'seu-email@exemplo.com',
        campoApi: 'email',
    },
    colaborador: {
        tema: 'pap',
        rotulo: 'Identificação',
        tipo: 'text',
        placeholder: 'MD-xxxxxxxx',
        campoApi: 'alias',
    },
};

let perfilAtual = 'paciente';

function mostrarMensagem(texto) {
    mensagem.textContent = texto;
    mensagem.hidden = !texto;
}

function escolherAba(aba, focar = false) {
    perfilAtual = aba.dataset.aba;
    const perfil = PERFIS[perfilAtual];

    abas.forEach((outra) => {
        const ativa = outra === aba;
        outra.setAttribute('aria-selected', ativa ? 'true' : 'false');
        outra.tabIndex = ativa ? 0 : -1;
    });

    document.body.dataset.tema = perfil.tema;
    formulario.setAttribute('aria-labelledby', aba.id);
    rotuloLogin.textContent = perfil.rotulo;
    campoLogin.type = perfil.tipo;
    campoLogin.placeholder = perfil.placeholder;
    campoLogin.value = '';
    ajuda.hidden = perfilAtual !== 'paciente';
    limparErros(formulario);
    mostrarMensagem('');

    if (focar) aba.focus();
}

abas.forEach((aba, indice) => {
    aba.addEventListener('click', () => escolherAba(aba));
    aba.addEventListener('keydown', (evento) => {
        if (evento.key !== 'ArrowRight' && evento.key !== 'ArrowLeft') return;
        evento.preventDefault();
        const passo = evento.key === 'ArrowRight' ? 1 : -1;
        escolherAba(abas[(indice + passo + abas.length) % abas.length], true);
    });
});

botaoAjuda.addEventListener('click', () => {
    const aberto = textoAjuda.hidden;
    textoAjuda.hidden = !aberto;
    botaoAjuda.setAttribute('aria-expanded', aberto ? 'true' : 'false');
});

formulario.addEventListener('submit', (evento) => {
    evento.preventDefault();
    limparErros(formulario);
    mostrarMensagem('');

    const login = campoLogin.value.trim();
    const senha = campoSenha.value;
    const faltando = {};
    if (!login) faltando.login = [`Informe ${PERFIS[perfilAtual].rotulo === 'E-mail' ? 'o e-mail' : 'a identificação'}.`];
    if (!senha) faltando.senha = ['Informe a senha.'];
    if (Object.keys(faltando).length > 0) {
        mostrarErros(formulario, faltando);
        return;
    }

    desabilitarEnquanto(botaoEntrar, async () => {
        try {
            const resposta = await api('/login', {
                method: 'POST',
                body: { [PERFIS[perfilAtual].campoApi]: login, senha },
            });
            setToken(resposta.token);
            const usuario = await carregarUsuario();
            window.location.replace(paginaInicial(usuario));
            await new Promise(() => {});
        } catch (erro) {
            if (erro instanceof ErroValidacao) {
                const erros = { ...erro.erros };
                erros.login = erros.email ?? erros.alias;
                if (!erros.login) delete erros.login;
                if (!mostrarErros(formulario, erros)) mostrarMensagem(erro.message);
            } else if (erro instanceof ErroApi && erro.status === 401) {
                mostrarMensagem('Credenciais inválidas.');
            } else if (erro instanceof ErroApi && erro.status === 429) {
                mostrarMensagem('Muitas tentativas. Aguarde um minuto e tente de novo.');
            } else if (erro instanceof ErroApi && erro.status === 403) {
                limparSessao();
                mostrarMensagem('Seu acesso está desativado. Procure a administração do ambulatório.');
            } else {
                mostrarMensagem(erro.message);
            }
            campoSenha.value = '';
        }
    });
});

if (new URLSearchParams(window.location.search).get('motivo') === 'inativo') {
    mostrarMensagem('Seu acesso está desativado. Procure a administração do ambulatório.');
}

if (getToken()) {
    carregarUsuario()
        .then((usuario) => window.location.replace(paginaInicial(usuario)))
        .catch(() => limparSessao());
}
