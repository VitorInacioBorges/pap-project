<?php

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('cria dados pessoais vinculados ao paciente da rota', function () {
    $secretario = User::query()->create([
        'alias' => 'SE-1122334',
        'email' => 'secretario@example.com',
        'cargo' => 'secretario',
        'ativo' => true,
    ]);
    $paciente = Paciente::query()->create([
        'email' => 'paciente@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '12345678901',
    ]);

    Sanctum::actingAs($secretario->refresh()->forceFill(['first_login' => false]));

    $response = $this->postJson("/api/pacientes/{$paciente->getKey()}/dados-pessoais", [
        'id_paciente' => 999,
        'nome' => 'Maria da Silva',
        'data_nascimento' => '1990-05-10',
        'telefone' => '11999999999',
    ]);

    $response->assertCreated()
        ->assertJsonPath('id_paciente', $paciente->getKey())
        ->assertJsonPath('nome', 'Maria da Silva');

    $this->assertDatabaseHas('dados_pessoais', [
        'id_paciente' => $paciente->getKey(),
        'nome' => 'Maria da Silva',
    ]);
});

it('retorna 409 quando o paciente ja possui dados pessoais', function () {
    $secretario = User::query()->create([
        'alias' => 'SE-2233445',
        'email' => 'secretario@example.com',
        'cargo' => 'secretario',
        'ativo' => true,
    ]);
    $paciente = Paciente::query()->create([
        'email' => 'paciente@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '12345678901',
    ]);
    $paciente->dadosPessoais()->create([
        'nome' => 'Maria da Silva',
        'data_nascimento' => '1990-05-10',
    ]);

    Sanctum::actingAs($secretario->refresh()->forceFill(['first_login' => false]));

    $this->postJson("/api/pacientes/{$paciente->getKey()}/dados-pessoais", [
        'nome' => 'Maria da Silva',
        'data_nascimento' => '1990-05-10',
    ])->assertConflict();
});

it('retorna 403 ao criar dados pessoais sem cargo secretario', function () {
    $medico = User::query()->create([
        'alias' => 'ME-1122334',
        'email' => 'medico@example.com',
        'cargo' => 'medico',
        'ativo' => true,
    ]);
    $paciente = Paciente::query()->create([
        'email' => 'paciente@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '12345678901',
    ]);

    Sanctum::actingAs($medico->refresh()->forceFill(['first_login' => false]));

    $this->postJson("/api/pacientes/{$paciente->getKey()}/dados-pessoais", [])
        ->assertForbidden();
});

it('retorna erros de validacao para nome e data de nascimento ausentes', function () {
    $secretario = User::query()->create([
        'alias' => 'SE-3344556',
        'email' => 'secretario@example.com',
        'cargo' => 'secretario',
        'ativo' => true,
    ]);
    $paciente = Paciente::query()->create([
        'email' => 'paciente@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '12345678901',
    ]);

    Sanctum::actingAs($secretario->refresh()->forceFill(['first_login' => false]));

    $this->postJson("/api/pacientes/{$paciente->getKey()}/dados-pessoais", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['nome', 'data_nascimento']);
});

it('consulta dados pessoais pelo paciente vinculado', function () {
    $secretario = User::query()->create([
        'alias' => 'SE-4455667',
        'email' => 'consulta@example.com',
        'cargo' => 'secretario',
        'ativo' => true,
    ]);
    $paciente = Paciente::query()->create([
        'email' => 'paciente-consulta@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '11223344556',
    ]);
    $paciente->dadosPessoais()->create([
        'nome' => 'Ana Souza',
        'data_nascimento' => '1992-11-02',
    ]);

    Sanctum::actingAs($secretario->refresh()->forceFill(['first_login' => false]));

    $this->getJson("/api/pacientes/{$paciente->getKey()}/dados-pessoais")
        ->assertOk()
        ->assertJsonPath('nome', 'Ana Souza')
        ->assertJsonPath('id_paciente', $paciente->getKey());
});

it('atualiza e exclui dados pessoais do paciente', function () {
    $secretario = User::query()->create([
        'alias' => 'SE-5566778',
        'email' => 'altera-dados@example.com',
        'cargo' => 'secretario',
        'ativo' => true,
    ]);
    $paciente = Paciente::query()->create([
        'email' => 'paciente-altera-dados@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '22334455667',
    ]);
    $paciente->dadosPessoais()->create([
        'nome' => 'Ana Souza',
        'data_nascimento' => '1992-11-02',
    ]);

    Sanctum::actingAs($secretario->refresh()->forceFill(['first_login' => false]));

    $this->patchJson("/api/pacientes/{$paciente->getKey()}/dados-pessoais", [
        'nome' => 'Ana Oliveira',
    ])->assertOk()
        ->assertJsonPath('nome', 'Ana Oliveira')
        ->assertJsonPath('id_paciente', $paciente->getKey());

    $this->assertDatabaseHas('dados_pessoais', [
        'id_paciente' => $paciente->getKey(),
        'nome' => 'Ana Oliveira',
    ]);

    $this->deleteJson("/api/pacientes/{$paciente->getKey()}/dados-pessoais")
        ->assertNoContent();

    $this->assertDatabaseMissing('dados_pessoais', [
        'id_paciente' => $paciente->getKey(),
    ]);
});
