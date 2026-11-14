<?php

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('allows a patient to log in with email and reports first login', function () {
    Paciente::query()->create([
        'email' => 'paciente@example.com',
        'senha' => 'senha-temporaria-123',
        'cpf' => '12345678901',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'paciente@example.com',
        'senha' => 'senha-temporaria-123',
    ]);

    $response->assertOk()
        ->assertJsonPath('first_login', true)
        ->assertJsonStructure(['token']);
});

it('keeps collaborator login by alias working', function () {
    User::query()->create([
        'alias' => 'SE-1234567',
        'email' => 'secretario@example.com',
        'senha' => Hash::make('senha-colaborador-123'),
        'cargo' => 'secretario',
        'ativo' => true,
    ]);

    $this->postJson('/api/login', [
        'alias' => 'SE-1234567',
        'senha' => 'senha-colaborador-123',
    ])->assertOk()
        ->assertJsonPath('first_login', true)
        ->assertJsonStructure(['token']);
});

it('rejects login when both alias and email are provided', function () {
    $this->postJson('/api/login', [
        'alias' => 'SE-1234567',
        'email' => 'secretario@example.com',
        'senha' => 'senha-colaborador-123',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['alias', 'email']);
});

it('requires patient first-login password changes before accessing protected routes', function () {
    Paciente::query()->create([
        'email' => 'paciente@example.com',
        'senha' => 'senha-temporaria-123',
        'cpf' => '12345678901',
    ]);
    $login = $this->postJson('/api/login', [
        'email' => 'paciente@example.com',
        'senha' => 'senha-temporaria-123',
    ])->assertOk();

    $this->withToken($login->json('token'))
        ->getJson('/api/pacientes')
        ->assertForbidden()
        ->assertJsonPath('first_login', true);
});

it('changes patient password, clears first login, and rotates tokens', function () {
    $paciente = Paciente::query()->create([
        'email' => 'paciente@example.com',
        'senha' => 'senha-temporaria-123',
        'cpf' => '12345678901',
    ]);
    $login = $this->postJson('/api/login', [
        'email' => 'paciente@example.com',
        'senha' => 'senha-temporaria-123',
    ])->assertOk();
    $tokenAnterior = $login->json('token');

    $response = $this->withToken($tokenAnterior)
        ->postJson('/api/pacientes/alterar-senha', [
            'senha_atual' => 'senha-temporaria-123',
            'nova_senha' => 'senha-nova-segura-123',
            'nova_senha_confirmation' => 'senha-nova-segura-123',
        ]);

    $response->assertOk()
        ->assertJsonPath('first_login', false)
        ->assertJsonStructure(['token']);

    $paciente->refresh();
    expect($paciente->first_login)->toBeFalse();
    expect(Hash::check('senha-nova-segura-123', $paciente->senha))->toBeTrue();
    expect($response->json('token'))->not->toBe($tokenAnterior);
    $this->assertDatabaseCount('personal_access_tokens', 1);

    $this->withToken($tokenAnterior)
        ->postJson('/api/pacientes/alterar-senha', [
            'senha_atual' => 'senha-temporaria-123',
            'nova_senha' => 'outra-senha-segura-123',
            'nova_senha_confirmation' => 'outra-senha-segura-123',
        ])
        ->assertUnauthorized();
});

it('rejects an incorrect current patient password without changing the account', function () {
    $paciente = Paciente::query()->create([
        'email' => 'paciente@example.com',
        'senha' => 'senha-temporaria-123',
        'cpf' => '12345678901',
    ]);
    $login = $this->postJson('/api/login', [
        'email' => 'paciente@example.com',
        'senha' => 'senha-temporaria-123',
    ])->assertOk();

    $this->withToken($login->json('token'))
        ->postJson('/api/pacientes/alterar-senha', [
            'senha_atual' => 'senha-incorreta',
            'nova_senha' => 'senha-nova-segura-123',
            'nova_senha_confirmation' => 'senha-nova-segura-123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['senha_atual']);

    $paciente->refresh();
    expect($paciente->first_login)->toBeTrue();
    expect(Hash::check('senha-temporaria-123', $paciente->senha))->toBeTrue();
});

it('requires a collaborator to change the temporary password before using protected routes', function () {
    User::query()->create([
        'alias' => 'AD-7654321',
        'email' => 'admin@example.com',
        'senha' => Hash::make('senha-colaborador-123'),
        'cargo' => 'admin',
        'ativo' => true,
    ]);
    $login = $this->postJson('/api/login', [
        'alias' => 'AD-7654321',
        'senha' => 'senha-colaborador-123',
    ])->assertOk()
        ->assertJsonPath('first_login', true);

    $tokenAnterior = $login->json('token');

    $this->withToken($tokenAnterior)
        ->getJson('/api/users')
        ->assertForbidden()
        ->assertJsonPath('first_login', true);

    $response = $this->withToken($tokenAnterior)
        ->postJson('/api/alterar-senha', [
            'senha_atual' => 'senha-colaborador-123',
            'nova_senha' => 'senha-nova-segura-123',
            'nova_senha_confirmation' => 'senha-nova-segura-123',
        ]);

    $response->assertOk()
        ->assertJsonPath('first_login', false)
        ->assertJsonStructure(['token']);

    expect($response->json('token'))->not->toBe($tokenAnterior);
    $this->assertDatabaseCount('personal_access_tokens', 1);
    $this->assertDatabaseHas('users', [
        'alias' => 'AD-7654321',
        'first_login' => false,
    ]);

    $this->withToken($response->json('token'))
        ->getJson('/api/users')
        ->assertOk();

    $this->withToken($tokenAnterior)
        ->getJson('/api/users')
        ->assertUnauthorized();
});

it('returns 401 when changing a patient password without authentication', function () {
    $this->postJson('/api/pacientes/alterar-senha', [
        'senha_atual' => 'senha-temporaria-123',
        'nova_senha' => 'senha-nova-segura-123',
        'nova_senha_confirmation' => 'senha-nova-segura-123',
    ])->assertUnauthorized();
});
