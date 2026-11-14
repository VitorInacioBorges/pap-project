export function lerSecaoUnica(chave) {
    try {
        return window.localStorage.getItem(chave) !== 'false';
    } catch {
        return true;
    }
}

export function salvarSecaoUnica(chave, ativa) {
    try {
        window.localStorage.setItem(chave, String(ativa));
    } catch {
        // A preferência continua válida nesta página quando o armazenamento está indisponível.
    }
}

export function fecharOutrasSecoes(secaoAberta, secoes, ativa) {
    if (!ativa || !secaoAberta?.open) return;
    for (const secao of secoes) {
        if (secao !== secaoAberta) secao.open = false;
    }
}
