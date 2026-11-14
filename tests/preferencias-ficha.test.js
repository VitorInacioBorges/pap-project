import assert from 'node:assert/strict';
import test from 'node:test';
import { fecharOutrasSecoes, lerSecaoUnica, salvarSecaoUnica } from '../resources/js/preferencias-ficha.js';

test('começa ativa, persiste a escolha por conta e tolera armazenamento bloqueado', () => {
    const original = globalThis.window;
    const valores = new Map();
    globalThis.window = { localStorage: {
        getItem: (chave) => valores.get(chave) ?? null,
        setItem: (chave, valor) => valores.set(chave, valor),
    } };
    try {
        assert.equal(lerSecaoUnica('medico-1'), true);
        salvarSecaoUnica('medico-1', false);
        assert.equal(lerSecaoUnica('medico-1'), false);
        assert.equal(lerSecaoUnica('medico-2'), true);
        salvarSecaoUnica('medico-1', true);
        assert.equal(lerSecaoUnica('medico-1'), true);

        Object.defineProperty(window, 'localStorage', { get() { throw new Error('Bloqueado'); } });
        assert.equal(lerSecaoUnica('medico-1'), true);
        assert.doesNotThrow(() => salvarSecaoUnica('medico-1', false));
    } finally {
        globalThis.window = original;
    }
});

test('recolhe as demais seções sem apagar valores e permite manter várias abertas', () => {
    const anterior = { open: true, valor: 'Informação já preenchida' };
    const atual = { open: true };
    const observacoes = { open: true };
    const secoes = [anterior, atual, observacoes];
    fecharOutrasSecoes(atual, secoes, false);
    assert.deepEqual(secoes.map((secao) => secao.open), [true, true, true]);
    fecharOutrasSecoes(atual, secoes, true);
    assert.deepEqual(secoes.map((secao) => secao.open), [false, true, false]);
    assert.equal(anterior.valor, 'Informação já preenchida');
    atual.open = false;
    anterior.open = true;
    fecharOutrasSecoes(atual, secoes, true);
    assert.equal(anterior.open, true);
});
