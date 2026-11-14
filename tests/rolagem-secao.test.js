import assert from 'node:assert/strict';
import test from 'node:test';
import { revelarSecao } from '../resources/js/rolagem-secao.js';

test('revela a seção sem movimentos desnecessários e respeita o espaço disponível', () => {
    const original = globalThis.window;
    const movimentos = [];
    globalThis.window = {
        innerHeight: 900,
        matchMedia: () => ({ matches: false }),
        scrollBy: (opcoes) => movimentos.push(opcoes),
    };
    const secao = (top, height) => ({ getBoundingClientRect: () => ({ top, height, bottom: top + height }) });
    try {
        revelarSecao(secao(100, 300), { offsetHeight: 80 });
        assert.deepEqual(movimentos, []);

        revelarSecao(secao(700, 400), { offsetHeight: 80 });
        assert.deepEqual(movimentos.pop(), { top: 296, behavior: 'smooth' });

        revelarSecao(secao(300, 1200), { offsetHeight: 80 });
        assert.deepEqual(movimentos.pop(), { top: 284, behavior: 'smooth' });

        revelarSecao(secao(-100, 300));
        assert.deepEqual(movimentos.pop(), { top: -116, behavior: 'smooth' });

        window.matchMedia = () => ({ matches: true });
        window.visualViewport = { height: 500, offsetTop: 20 };
        revelarSecao(secao(350, 200), { offsetHeight: 80 });
        assert.deepEqual(movimentos.pop(), { top: 126, behavior: 'instant' });

        revelarSecao(secao(36, 1200), { offsetHeight: 80 });
        assert.deepEqual(movimentos, []);
    } finally {
        globalThis.window = original;
    }
});
