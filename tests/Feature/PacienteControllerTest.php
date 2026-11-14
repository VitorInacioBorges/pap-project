<?php

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('cria um paciente para um usuario secretario e nao retorna a senha', function () {
    $secretario = User::query()->create([
        'alias' => 'SE-1234567',
        'email' => 'secretario@example.com',
        'cargo' => 'secretario',
        'ativo' => true,
    ]);

    Sanctum::actingAs($secretario->refresh()->forceFill(['first_login' => false]));

    $response = $this->postJson('/api/pacientes', [
        'email' => 'paciente@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '12345678901',
    ]);

    $response->assertCreated()
        ->assertJsonMissingPath('senha')
        ->assertJsonPath('email', 'paciente@example.com');

    $codigoPaciente = $response->json('codigo_paciente');
    expect($codigoPaciente)->toMatch('/^PA-[A-Za-z0-9]{8}$/');

    $this->assertDatabaseHas('pacientes', [
        'email' => 'paciente@example.com',
        'cpf' => '123.456.789-01',
        'codigo_paciente' => $codigoPaciente,
    ]);
});

it('retorna 401 ao tentar criar paciente sem autenticacao', function () {
    $this->postJson('/api/pacientes', [])
        ->assertUnauthorized();
});

it('retorna 403 ao tentar criar paciente sem cargo secretario', function () {
    $medico = User::query()->create([
        'alias' => 'ME-1234567',
        'email' => 'medico@example.com',
        'cargo' => 'medico',
        'ativo' => true,
    ]);

    Sanctum::actingAs($medico->refresh()->forceFill(['first_login' => false]));

    $this->postJson('/api/pacientes', [])
        ->assertForbidden();
});

it('retorna erros de validacao para os campos obrigatorios', function () {
    $secretario = User::query()->create([
        'alias' => 'SE-7654321',
        'email' => 'outro@example.com',
        'cargo' => 'secretario',
        'ativo' => true,
    ]);

    Sanctum::actingAs($secretario->refresh()->forceFill(['first_login' => false]));

    $this->postJson('/api/pacientes', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'senha', 'cpf']);
});

it('lista e consulta pacientes com seus dados pessoais', function () {
    $secretario = User::query()->create([
        'alias' => 'SE-8877665',
        'email' => 'secretario-lista@example.com',
        'cargo' => 'secretario',
        'ativo' => true,
    ]);
    $paciente = Paciente::query()->create([
        'email' => 'paciente-lista@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '98765432100',
    ]);
    $paciente->dadosPessoais()->create([
        'nome' => 'Joao da Silva',
        'data_nascimento' => '1985-03-20',
    ]);

    Sanctum::actingAs($secretario->refresh()->forceFill(['first_login' => false]));

    $this->getJson('/api/pacientes')
        ->assertOk()
        ->assertJsonPath('data.0.email', 'paciente-lista@example.com');

    $this->getJson("/api/pacientes/{$paciente->getKey()}")
        ->assertOk()
        ->assertJsonPath('dados_pessoais.nome', 'Joao da Silva')
        ->assertJsonMissingPath('senha');
});

it('atualiza e exclui logicamente um paciente', function () {
    $secretario = User::query()->create([
        'alias' => 'SE-6677889',
        'email' => 'secretario-altera@example.com',
        'cargo' => 'secretario',
        'ativo' => true,
    ]);
    $paciente = Paciente::query()->create([
        'email' => 'paciente-antigo@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '99887766554',
    ]);

    Sanctum::actingAs($secretario->refresh()->forceFill(['first_login' => false]));

    $this->putJson("/api/pacientes/{$paciente->getKey()}", [
        'email' => 'paciente-novo@example.com',
    ])->assertOk()
        ->assertJsonPath('email', 'paciente-novo@example.com')
        ->assertJsonMissingPath('senha');

    $this->assertDatabaseHas('pacientes', [
        'id_paciente' => $paciente->getKey(),
        'email' => 'paciente-novo@example.com',
    ]);

    $this->deleteJson("/api/pacientes/{$paciente->getKey()}")
        ->assertNoContent();

    $this->assertSoftDeleted('pacientes', ['id_paciente' => $paciente->getKey()]);
});
