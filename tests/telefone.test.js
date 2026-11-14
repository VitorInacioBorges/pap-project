import assert from 'node:assert/strict';
import test from 'node:test';
import { formatarTelefone, mascararTelefone } from '../resources/js/telefone.js';

test('formata telefone fixo, celular, colagem com DDI e valores parciais', () => {
    for (const [entrada, esperado] of [
        ['', ''], ['abc', ''], ['4', '(4'], ['42', '(42'], ['429', '(42) 9'],
        ['4232221234', '(42) 3222-1234'], ['42999991234', '(42) 99999-1234'],
        ['+55 (42) 99999-1234', '(42) 99999-1234'], ['(42) 3222-1234', '(42) 3222-1234'],
        ['429999912345', '(42) 99999-1234'],
    ]) assert.equal(formatarTelefone(entrada), esperado);
});

test('mantém o cursor junto ao dígito editado no meio do telefone', () => {
    const campo = {
        value: '(42) 989999-1234', selectionStart: 7,
        setSelectionRange(inicio, fim) { this.cursor = [inicio, fim]; },
    };
    mascararTelefone(campo);
    assert.equal(campo.value, '(42) 98999-9123');
    assert.deepEqual(campo.cursor, [7, 7]);
});
