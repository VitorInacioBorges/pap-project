const CHAVE_TOKEN = 'pap.token';
const CHAVE_USUARIO = 'pap.usuario';

// DECISAO: onde guardar o token — escolhido: sessionStorage (fechar a aba desloga; computadores do ambulatório são compartilhados); alternativas: localStorage
function armazenamento() {
    try {
        return window.sessionStorage;
    } catch {
        return null;
    }
}

const ROTULOS_CARGO = {
    secretario: 'Atendente',
    medico: 'Médico(a)',
    enfermeiro: 'Enfermeiro(a)',
    admin: 'Administrador(a)',
};

export class ErroValidacao extends Error {
    constructor(erros, mensagem) {
        super(mensagem || 'Confira os campos destacados.');
        this.erros = erros || {};
    }
}

export class ErroApi extends Error {
    constructor(status, mensagem) {
        super(mensagem);
        this.status = status;
        this.mensagem = mensagem;
    }
}

const nunca = () => new Promise(() => {});

export function getToken() {
    return armazenamento()?.getItem(CHAVE_TOKEN) ?? null;
}

export function setToken(token) {
    armazenamento()?.setItem(CHAVE_TOKEN, token);
}

export function limparSessao() {
    armazenamento()?.removeItem(CHAVE_TOKEN);
    armazenamento()?.removeItem(CHAVE_USUARIO);
}

function usuarioEmCache() {
    try {
        return JSON.parse(armazenamento()?.getItem(CHAVE_USUARIO) ?? 'null');
    } catch {
        return null;
    }
}

function irPara(destino) {
    const atual = window.location.pathname + window.location.search;
    if (atual !== destino) {
        window.location.replace(destino);
        return true;
    }
    return false;
}

function mensagemDe(dados, status) {
    if (dados && typeof dados === 'object') {
        if (typeof dados.message === 'string') return dados.message;
        if (typeof dados.erro === 'string') return dados.erro;
        if (typeof dados.error === 'string') return dados.error;
        if (Array.isArray(dados) && typeof dados[0] === 'string') return dados[0];
    }
    return `Algo deu errado (código ${status}). Tente de novo.`;
}

export async function api(caminho, { method = 'GET', body } = {}) {
    const headers = { Accept: 'application/json' };
    if (body !== undefined) headers['Content-Type'] = 'application/json';

    const token = getToken();
    if (token) headers.Authorization = `Bearer ${token}`;

    let resposta;
    try {
        resposta = await fetch(`/api${caminho}`, {
            method,
            headers,
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });
    } catch {
        throw new ErroApi(0, 'Não foi possível falar com o servidor. Verifique a conexão e tente de novo.');
    }

    if (resposta.status === 204) return null;

    const dados = await resposta.json().catch(() => null);

    if (resposta.ok) return dados;

    if (resposta.status === 401 && caminho !== '/login') {
        limparSessao();
        window.location.replace('/login');
        return nunca();
    }

    if (resposta.status === 403 && dados?.first_login) {
        const usuario = { ...(usuarioEmCache() ?? { tipo: 'colaborador' }), first_login: true };
        if (irPara(paginaInicial(usuario))) return nunca();
    }

    if (resposta.status === 422) throw new ErroValidacao(dados?.errors, dados?.message);

    throw new ErroApi(resposta.status, mensagemDe(dados, resposta.status));
}

export async function carregarUsuario() {
    const usuario = await api('/me');
    armazenamento()?.setItem(CHAVE_USUARIO, JSON.stringify(usuario));
    return usuario;
}

export function paginaInicial(usuario) {
    if (usuario.tipo === 'paciente') return '/paciente';
    if (usuario.first_login) return '/configuracoes?primeiro-acesso=1';
    return '/inicio';
}

export function rotuloCargo(cargo) {
    return ROTULOS_CARGO[cargo] ?? cargo ?? '';
}

export function nomeExibido(usuario) {
    return usuario.nome || usuario.alias || usuario.email || '';
}

export async function sair() {
    try {
        await api('/logout', { method: 'POST' });
    } catch {
        // O token pode já ter expirado; a sessão local é encerrada de qualquer forma.
    }
    limparSessao();
    window.location.replace('/login');
}

function preencherUsuario(usuario) {
    document.querySelectorAll('[data-usuario-nome]').forEach((el) => {
        el.textContent = nomeExibido(usuario);
    });
    document.querySelectorAll('[data-usuario-cargo]').forEach((el) => {
        el.textContent = rotuloCargo(usuario.cargo);
    });
    document.querySelectorAll('[data-so-cargos]').forEach((el) => {
        const permitidos = el.dataset.soCargos.split(',');
        el.hidden = !permitidos.includes(usuario.cargo);
    });
}

export async function protegerPagina() {
    const tipo = document.body.dataset.tipo || 'colaborador';
    const cargos = (document.body.dataset.cargos || '').split(',').filter(Boolean);

    if (!getToken()) {
        window.location.replace('/login');
        return nunca();
    }

    const usuario = await carregarUsuario();

    if (usuario.tipo !== tipo) {
        window.location.replace(paginaInicial(usuario));
        return nunca();
    }

    if (usuario.tipo === 'colaborador' && usuario.first_login && window.location.pathname !== '/configuracoes') {
        window.location.replace('/configuracoes?primeiro-acesso=1');
        return nunca();
    }

    preencherUsuario(usuario);
    document.getElementById('carregando')?.setAttribute('hidden', '');

    if (cargos.length > 0 && !cargos.includes(usuario.cargo)) {
        document.getElementById('sem-permissao')?.removeAttribute('hidden');
        return null;
    }

    document.getElementById('app-conteudo')?.removeAttribute('hidden');
    return usuario;
}

export async function desabilitarEnquanto(botao, acao) {
    if (botao.disabled) return undefined;
    botao.disabled = true;
    botao.setAttribute('aria-busy', 'true');
    try {
        return await acao();
    } finally {
        botao.disabled = false;
        botao.removeAttribute('aria-busy');
    }
}

export function limparErros(raiz) {
    raiz.querySelectorAll('[data-erro]').forEach((el) => {
        el.textContent = '';
        el.hidden = true;
    });
    raiz.querySelectorAll('[aria-invalid]').forEach((el) => el.removeAttribute('aria-invalid'));
}

export function mostrarErros(raiz, erros) {
    let primeiro = null;
    for (const [campo, mensagens] of Object.entries(erros)) {
        const alvo = raiz.querySelector(`[data-erro="${CSS.escape(campo)}"]`);
        if (!alvo) continue;
        alvo.textContent = Array.isArray(mensagens) ? mensagens[0] : String(mensagens);
        alvo.hidden = false;
        const campoEl = alvo.id ? raiz.querySelector(`[aria-describedby~="${CSS.escape(alvo.id)}"]`) : null;
        if (campoEl) {
            campoEl.setAttribute('aria-invalid', 'true');
            primeiro ??= campoEl;
        }
    }
    primeiro?.focus();
    return primeiro !== null;
}
