import assert from 'node:assert/strict';
import test from 'node:test';
import { confirmarReenvio, limparConfirmacaoReenvio } from '../resources/js/confirmacao-reenvio.js';

test('primeiro clique só avisa e apenas o segundo autoriza o reenvio', () => {
    const atributos = {};
    const botao = { setAttribute: (nome, valor) => { atributos[nome] = valor; } };
    const aviso = { hidden: true };
    let envios = 0;
    const clicar = () => { if (confirmarReenvio(botao, aviso)) envios++; };

    clicar();
    assert.equal(envios, 0);
    assert.equal(aviso.hidden, false);
    assert.equal(botao.textContent, 'Confirmar reenvio');
    assert.equal(atributos['aria-expanded'], 'true');
    clicar();
    assert.equal(envios, 1);
    assert.equal(aviso.hidden, true);
    assert.equal(botao.textContent, 'Reenviar senha temporária');

    clicar();
    limparConfirmacaoReenvio(botao, aviso);
    clicar();
    assert.equal(envios, 1);
    assert.equal(aviso.hidden, false);
});
