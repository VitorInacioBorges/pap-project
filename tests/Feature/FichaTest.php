<?php

use App\Models\Anamnese;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('cadastra ficha vinculada a paciente e ao usuario autenticado', function () {
    $usuario = entrarComo('admin');
    $paciente = novaPaciente();
    $outra = novaPaciente('111.111.111-11', 'outra@example.com');

    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", [
        'data_atendimento' => '2026-11-14T13:30:00.000Z',
        'observacoes' => 'Retorno em 30 dias.',
        'paciente_id' => $outra->getKey(),
        'usuario_id' => 999,
    ])->assertCreated()
        ->assertJsonPath('paciente_id', $paciente->getKey())
        ->assertJsonPath('usuario_id', $usuario->getKey())
        ->assertJsonPath('data_atendimento', '2026-11-14T13:30:00.000000Z');

    $this->assertDatabaseHas('anamnese', [
        'paciente_id' => $paciente->getKey(),
        'usuario_id' => $usuario->getKey(),
        'data_atendimento' => '2026-11-14 13:30:00',
        'observacoes' => 'Retorno em 30 dias.',
    ]);
    $this->getJson('/api/pacientes')->assertJsonPath('data.0.fichas_count', 0)->assertJsonPath('data.1.fichas_count', 1);
});

it('lista somente fichas da paciente em ordem de atendimento e ignora excluidas', function () {
    $usuario = entrarComo('secretario');
    $paciente = novaPaciente();
    $outra = novaPaciente('111.111.111-11', 'outra@example.com');
    $antiga = Anamnese::factory()->for($paciente, 'paciente')->for($usuario, 'usuario')->create(['data_atendimento' => '2026-11-13 10:00:00']);
    $recente = Anamnese::factory()->for($paciente, 'paciente')->for($usuario, 'usuario')->create(['data_atendimento' => '2026-11-14 10:00:00']);
    Anamnese::factory()->for($outra, 'paciente')->for($usuario, 'usuario')->create();
    Anamnese::factory()->for($paciente, 'paciente')->for($usuario, 'usuario')->create()->delete();

    $this->getJson("/api/pacientes/{$paciente->getKey()}/fichas")
        ->assertOk()->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $recente->getKey())
        ->assertJsonPath('data.1.id', $antiga->getKey());
    $this->getJson("/api/pacientes/{$paciente->getKey()}")->assertJsonPath('fichas_count', 2);
});

it('mantem o responsavel no historico apos sua exclusao logica', function () {
    $usuario = entrarComo('admin');
    $paciente = novaPaciente();
    Anamnese::factory()->for($paciente, 'paciente')->for($usuario, 'usuario')->create();
    $usuario->delete();
    entrarComo('secretario');

    $this->getJson("/api/pacientes/{$paciente->getKey()}/fichas")
        ->assertOk()->assertJsonPath('data.0.usuario.alias', $usuario->alias);
});

it('exige autenticacao para consultar e criar fichas', function () {
    $paciente = novaPaciente();
    $this->getJson("/api/pacientes/{$paciente->getKey()}/fichas")->assertUnauthorized();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", [])->assertUnauthorized();
});

it('recusa cargos sem acesso a pacientes com 403', function (string $cargo) {
    entrarComo($cargo);
    $paciente = novaPaciente();
    $this->getJson("/api/pacientes/{$paciente->getKey()}/fichas")->assertForbidden();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", [])->assertForbidden();
})->with(['medico', 'enfermeiro']);

it('impede que paciente acesse fichas pela API de colaboradores', function () {
    $paciente = novaPaciente()->forceFill(['first_login' => false]);
    Sanctum::actingAs($paciente);
    $this->getJson("/api/pacientes/{$paciente->getKey()}/fichas")->assertForbidden();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", [])->assertForbidden();
});

it('recusa ficha antes da troca da senha inicial com 403', function () {
    $usuario = entrarComo('admin')->forceFill(['first_login' => true]);
    Sanctum::actingAs($usuario);
    $paciente = novaPaciente();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", [])->assertForbidden();
});

it('consulta historico de paciente oculta mas recusa novas fichas com 404', function () {
    $usuario = entrarComo('admin');
    $paciente = novaPaciente();
    Anamnese::factory()->for($paciente, 'paciente')->for($usuario, 'usuario')->create();
    $paciente->delete();

    $this->getJson("/api/pacientes/{$paciente->getKey()}/fichas")->assertOk()->assertJsonCount(1, 'data');
    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", [])->assertNotFound();
});

it('recusa acesso a fichas de paciente excluida com 404', function () {
    entrarComo('admin');
    $paciente = novaPaciente();
    $paciente->forceFill(['hard_deleted' => true])->save();
    $this->getJson("/api/pacientes/{$paciente->getKey()}/fichas")->assertNotFound();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", [])->assertNotFound();
});

it('valida data e observacoes antes de criar ficha', function (array $dados, string $campo) {
    entrarComo('admin');
    $paciente = novaPaciente();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", $dados)
        ->assertUnprocessable()->assertJsonValidationErrors([$campo]);
    $this->assertDatabaseCount('anamnese', 0);
})->with([
    'data obrigatoria' => [[], 'data_atendimento'],
    'data invalida' => [['data_atendimento' => 'ontem'], 'data_atendimento'],
    'observacoes textuais' => [['data_atendimento' => '2026-11-14T13:30:00.000Z', 'observacoes' => ['texto']], 'observacoes'],
    'limite de observacoes' => [['data_atendimento' => '2026-11-14T13:30:00.000Z', 'observacoes' => str_repeat('a', 10001)], 'observacoes'],
]);

it('aceita ficha sem observacoes', function () {
    entrarComo('secretario');
    $paciente = novaPaciente();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", ['data_atendimento' => '2026-11-14T13:30:00.000Z'])
        ->assertCreated()->assertJsonPath('observacoes', null);
    $this->assertDatabaseCount('anamnese', 1);
});

it('abre historico e cadastro com a paciente vinculada', function (string $caminho) {
    $this->get($caminho)->assertOk()->assertSee('data-paciente="123"', false);
})->with(['/pacientes/123/fichas', '/pacientes/123/fichas/criar']);
