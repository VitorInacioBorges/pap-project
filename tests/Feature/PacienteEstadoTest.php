<?php

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

function entrarComo(string $cargo): User
{
    $usuario = User::query()->create([
        'alias' => strtoupper(substr($cargo, 0, 2)).'-1234567',
        'email' => "{$cargo}@example.com",
        'cargo' => $cargo,
        'ativo' => true,
    ])->refresh()->forceFill(['first_login' => false]);

    Sanctum::actingAs($usuario);

    return $usuario;
}

function novaPaciente(string $cpf = '123.456.789-01', string $email = 'paciente@example.com'): Paciente
{
    return Paciente::query()->create([
        'email' => $email,
        'senha' => 'senha-segura-123',
        'cpf' => $cpf,
    ])->refresh();
}

it('permite que admin gerencie pacientes', function () {
    entrarComo('admin');

    $this->getJson('/api/pacientes')->assertOk();
});

it('oculta e reativa uma paciente', function () {
    entrarComo('secretario');
    $paciente = novaPaciente();

    $this->deleteJson("/api/pacientes/{$paciente->id_paciente}")->assertNoContent();
    expect(Paciente::withTrashed()->find($paciente->id_paciente)->deleted_at)->not->toBeNull();

    $this->postJson("/api/pacientes/{$paciente->id_paciente}/reativar")->assertOk();
    expect(Paciente::query()->find($paciente->id_paciente)->deleted_at)->toBeNull();
});

it('consulta uma paciente oculta', function () {
    entrarComo('secretario');
    $paciente = novaPaciente();
    $paciente->delete();

    $this->getJson("/api/pacientes/{$paciente->id_paciente}")
        ->assertOk()
        ->assertJsonPath('email', 'paciente@example.com');
});

it('exclui definitivamente uma paciente', function () {
    entrarComo('secretario');
    $paciente = novaPaciente();

    $this->postJson("/api/pacientes/{$paciente->id_paciente}/excluir")->assertNoContent();

    $this->assertDatabaseHas('pacientes', [
        'id_paciente' => $paciente->id_paciente,
        'hard_deleted' => true,
    ]);
    $this->getJson("/api/pacientes/{$paciente->id_paciente}")->assertNotFound();
    $this->postJson("/api/pacientes/{$paciente->id_paciente}/reativar")->assertNotFound();
});

it('libera cpf e email de paciente excluida para novo cadastro', function () {
    entrarComo('secretario');
    $paciente = novaPaciente();

    $this->postJson("/api/pacientes/{$paciente->id_paciente}/excluir")->assertNoContent();

    $this->postJson('/api/pacientes', [
        'email' => 'paciente@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '123.456.789-01',
    ])->assertCreated();
});

it('bloqueia cpf de paciente apenas oculta', function () {
    entrarComo('secretario');
    novaPaciente()->delete();

    $this->postJson('/api/pacientes', [
        'email' => 'outra@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '123.456.789-01',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['cpf']);
});

it('recusa login de paciente oculta', function () {
    novaPaciente()->delete();

    $this->postJson('/api/login', [
        'email' => 'paciente@example.com',
        'senha' => 'senha-segura-123',
    ])->assertUnauthorized();
});
