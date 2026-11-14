export function formatarTelefone(valor) {
    let digitos = valor.replace(/\D/g, '');
    if (digitos.length > 11 && digitos.startsWith('55')) digitos = digitos.slice(2);
    digitos = digitos.slice(0, 11);
    if (!digitos) return '';
    if (digitos.length <= 2) return `(${digitos}`;
    const numero = digitos.slice(2);
    const corte = digitos.length === 11 ? 5 : 4;
    return `(${digitos.slice(0, 2)}) ${numero.slice(0, corte)}${numero.length > corte ? `-${numero.slice(corte)}` : ''}`;
}

export function mascararTelefone(campo) {
    const antes = campo.value;
    const posicao = campo.selectionStart ?? antes.length;
    let quantidade = antes.slice(0, posicao).replace(/\D/g, '').length;
    if (antes.replace(/\D/g, '').length > 11 && antes.replace(/\D/g, '').startsWith('55')) {
        quantidade = Math.max(0, quantidade - 2);
    }
    campo.value = formatarTelefone(antes);
    let cursor = 0;
    while (cursor < campo.value.length && quantidade > 0) {
        if (/\d/.test(campo.value[cursor])) quantidade--;
        cursor++;
    }
    campo.setSelectionRange(cursor, cursor);
}
