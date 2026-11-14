<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => entrarComo('secretario'));

it('lista so ativas por padrao e marca o status', function () {
    novaPaciente('111.111.111-11', 'a@example.com', 'Ativa');
    novaPaciente('222.222.222-22', 'b@example.com', 'Oculta')->delete();

    $this->getJson('/api/pacientes')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.dados_pessoais.nome', 'Ativa')
        ->assertJsonPath('data.0.ativo', true);
});

it('filtra inativas e todas pelo status', function () {
    novaPaciente('111.111.111-11', 'a@example.com', 'Ativa');
    novaPaciente('222.222.222-22', 'b@example.com', 'Oculta')->delete();

    $this->getJson('/api/pacientes?status[]=inativos')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.dados_pessoais.nome', 'Oculta')
        ->assertJsonPath('data.0.ativo', false);

    $this->getJson('/api/pacientes?status[]=ativos&status[]=inativos')
        ->assertJsonCount(2, 'data');
});

it('nunca lista pacientes excluidas', function () {
    $paciente = novaPaciente('111.111.111-11', 'a@example.com', 'Excluida');
    $paciente->forceFill(['hard_deleted' => true])->save();
    $paciente->delete();

    $this->getJson('/api/pacientes?status[]=ativos&status[]=inativos')
        ->assertJsonCount(0, 'data');
});

it('busca por nome sem diferenciar maiusculas', function () {
    novaPaciente('111.111.111-11', 'a@example.com', 'Marlene Aparecida');
    novaPaciente('222.222.222-22', 'b@example.com', 'Rosane Moreira');

    $this->getJson('/api/pacientes?busca=marlene')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.dados_pessoais.nome', 'Marlene Aparecida');
});

it('busca cpf com ou sem pontuacao', function () {
    novaPaciente('123.456.789-01', 'a@example.com', 'Marlene');
    novaPaciente('987.654.321-00', 'b@example.com', 'Rosane');

    $this->getJson('/api/pacientes?busca=12345678901')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.cpf', '123.456.789-01');

    $this->getJson('/api/pacientes?busca=123.456.789-01')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.cpf', '123.456.789-01');
});

it('ordena por nome com sem nome no fim', function () {
    novaPaciente('333.333.333-33', 'a@example.com', 'Ana');
    novaPaciente('111.111.111-11', 'semnome@example.com');
    novaPaciente('222.222.222-22', 'b@example.com', 'Bruna');

    $resposta = $this->getJson('/api/pacientes?ordem=alfabetica')->assertOk();

    expect(collect($resposta->json('data'))->pluck('email')->all())
        ->toBe(['a@example.com', 'b@example.com', 'semnome@example.com']);
});

it('ordena por mais antigos', function () {
    novaPaciente('111.111.111-11', 'nova@example.com', 'Nova', '2026-11-10 10:00:00');
    novaPaciente('222.222.222-22', 'antiga@example.com', 'Antiga', '2026-01-10 10:00:00');

    $this->getJson('/api/pacientes?ordem=antigos')
        ->assertJsonPath('data.0.email', 'antiga@example.com');

    $this->getJson('/api/pacientes')
        ->assertJsonPath('data.0.email', 'nova@example.com');
});

it('valida o parametro ordem', function () {
    $this->getJson('/api/pacientes?ordem=xpto')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['ordem']);
});
