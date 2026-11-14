<?php

use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
    $paciente = novaPaciente(cpf: '123.456.789-09', nome: 'Maria Aparecida Silva');

    $this->postJson("/api/pacientes/{$paciente->id_paciente}/excluir", ['confirmacao_nome' => 'Maria Aparecida Silva'])->assertNoContent();

    $this->assertDatabaseHas('pacientes', [
        'id_paciente' => $paciente->id_paciente,
        'hard_deleted' => true,
    ]);
    $this->getJson("/api/pacientes/{$paciente->id_paciente}")->assertNotFound();
    $this->postJson("/api/pacientes/{$paciente->id_paciente}/reativar")->assertNotFound();
});

it('libera cpf e email de paciente excluida para novo cadastro', function () {
    entrarComo('secretario');
    $paciente = novaPaciente(cpf: '123.456.789-09', nome: 'Maria Aparecida Silva');

    $this->postJson("/api/pacientes/{$paciente->id_paciente}/excluir", ['confirmacao_nome' => 'Maria Aparecida Silva'])->assertNoContent();

    $this->postJson('/api/pacientes', [
        'email' => 'paciente@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '123.456.789-09',
    ])->assertCreated();
});

it('bloqueia cpf de paciente apenas oculta', function () {
    entrarComo('secretario');
    novaPaciente(cpf: '123.456.789-09')->delete();

    $this->postJson('/api/pacientes', [
        'email' => 'outra@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '123.456.789-09',
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

it('recusa exclusao sem o nome completo correto com 422', function (mixed $nome) {
    entrarComo('secretario');
    $paciente = novaPaciente(cpf: '123.456.789-09', nome: 'Maria Aparecida Silva');
    $paciente->createToken('portal');

    $this->postJson("/api/pacientes/{$paciente->id_paciente}/excluir", ['confirmacao_nome' => $nome])
        ->assertUnprocessable()->assertJsonValidationErrors(['confirmacao_nome']);

    $this->assertDatabaseHas('pacientes', ['id_paciente' => $paciente->id_paciente, 'hard_deleted' => false, 'deleted_at' => null]);
    $this->assertDatabaseCount('personal_access_tokens', 1);
})->with([null, '', 'Maria', 'Maria Silva', 'Outra Pessoa', [['Maria Aparecida Silva']]]);

it('impede exclusao de paciente sem nome cadastrado com 422', function () {
    entrarComo('secretario');
    $paciente = novaPaciente();
    $this->postJson("/api/pacientes/{$paciente->id_paciente}/excluir", ['confirmacao_nome' => $paciente->email])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['confirmacao_nome'])
        ->assertJsonPath('errors.confirmacao_nome.0', 'Cadastre o nome completo da paciente antes de excluí-la.');
    $this->assertDatabaseHas('pacientes', ['id_paciente' => $paciente->id_paciente, 'hard_deleted' => false]);
});

it('permite excluir paciente oculta confirmando o nome completo', function () {
    entrarComo('admin');
    $paciente = novaPaciente(cpf: '123.456.789-09', nome: 'Maria Aparecida Silva');
    $paciente->delete();
    $this->postJson("/api/pacientes/{$paciente->id_paciente}/excluir", ['confirmacao_nome' => 'Maria Aparecida Silva'])
        ->assertNoContent();
    $this->assertDatabaseHas('pacientes', ['id_paciente' => $paciente->id_paciente, 'hard_deleted' => true]);
});
