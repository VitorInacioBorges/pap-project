<?php

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function criarSecretario(array $atributos = []): User
{
    return User::query()->create(array_merge([
        'alias' => 'SE-1234567',
        'email' => 'secretario@example.com',
        'senha' => 'senha-colaborador-123',
        'cargo' => 'secretario',
        'ativo' => true,
    ], $atributos))->refresh();
}

it('devolve o colaborador logado em /me', function () {
    $secretario = criarSecretario(['nome' => 'Fulana de Tal']);

    Sanctum::actingAs($secretario);

    $this->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('tipo', 'colaborador')
        ->assertJsonPath('nome', 'Fulana de Tal')
        ->assertJsonPath('cargo', 'secretario')
        ->assertJsonPath('alias', 'SE-1234567')
        ->assertJsonPath('first_login', true)
        ->assertJsonMissingPath('senha');
});

it('devolve a paciente logada em /me com o nome dos dados pessoais', function () {
    $paciente = Paciente::query()->create([
        'email' => 'paciente@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '12345678901',
    ]);
    $paciente->dadosPessoais()->create([
        'nome' => 'Maria Souza',
        'data_nascimento' => '1990-05-10',
    ]);

    Sanctum::actingAs($paciente->refresh());

    $this->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('tipo', 'paciente')
        ->assertJsonPath('nome', 'Maria Souza')
        ->assertJsonPath('email', 'paciente@example.com')
        ->assertJsonMissingPath('cargo');
});

it('atualiza o nome do colaborador', function () {
    $secretario = criarSecretario();

    Sanctum::actingAs($secretario->forceFill(['first_login' => false]));

    $this->patchJson('/api/me', ['nome' => 'Fulana Atualizada'])
        ->assertOk()
        ->assertJsonPath('nome', 'Fulana Atualizada');

    $this->assertDatabaseHas('users', [
        'id_usuario' => $secretario->getKey(),
        'nome' => 'Fulana Atualizada',
    ]);
});

it('exige nome ao atualizar /me', function () {
    Sanctum::actingAs(criarSecretario()->forceFill(['first_login' => false]));

    $this->patchJson('/api/me', ['nome' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['nome']);
});

it('recusa PATCH /me para paciente', function () {
    $paciente = Paciente::query()->create([
        'email' => 'paciente@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '12345678901',
    ]);

    Sanctum::actingAs($paciente->refresh()->forceFill(['first_login' => false]));

    $this->patchJson('/api/me', ['nome' => 'Outra'])
        ->assertForbidden();
});

it('encerra a sessao em /logout', function () {
    criarSecretario();

    $token = $this->postJson('/api/login', [
        'alias' => 'SE-1234567',
        'senha' => 'senha-colaborador-123',
    ])->json('token');

    $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout')
        ->assertNoContent();

    expect(DB::table('personal_access_tokens')->count())->toBe(0);
});

it('recusa login de colaborador inativo', function () {
    criarSecretario(['ativo' => false]);

    $this->postJson('/api/login', [
        'alias' => 'SE-1234567',
        'senha' => 'senha-colaborador-123',
    ])->assertUnauthorized();
});

it('exige autenticacao em /me', function () {
    $this->getJson('/api/me')->assertUnauthorized();
});
