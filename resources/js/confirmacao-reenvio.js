export function limparConfirmacaoReenvio(botao, aviso) {
    aviso.hidden = true;
    botao.textContent = 'Reenviar senha temporária';
    botao.setAttribute('aria-expanded', 'false');
}

export function confirmarReenvio(botao, aviso) {
    if (aviso.hidden) {
        aviso.hidden = false;
        botao.textContent = 'Confirmar reenvio';
        botao.setAttribute('aria-expanded', 'true');
        return false;
    }
    limparConfirmacaoReenvio(botao, aviso);
    return true;
}
