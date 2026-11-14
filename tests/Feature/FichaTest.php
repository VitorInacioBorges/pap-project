<?php

use App\Models\Anamnese;
use App\Support\CamposAnamnese;
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
        'dados_clinicos' => ['queixa_principal' => 'Consulta de rotina', 'historia_atual' => 'Sem queixas atuais.'],
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
    $usuario = entrarComo('medico');
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
    entrarComo('medico');

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
})->with(['secretario', 'enfermeiro']);

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
    entrarComo('medico');
    $paciente = novaPaciente();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", ['data_atendimento' => '2026-11-14T13:30:00.000Z', 'dados_clinicos' => ['queixa_principal' => 'Retorno', 'historia_atual' => 'Sem queixas.']])
        ->assertCreated()->assertJsonPath('observacoes', null);
    $this->assertDatabaseCount('anamnese', 1);
});

it('abre historico e cadastro com a paciente vinculada', function (string $caminho) {
    $this->get($caminho)->assertOk()->assertSee('data-paciente="123"', false);
})->with(['/pacientes/123/fichas', '/pacientes/123/fichas/criar']);

it('grava a anamnese estruturada e permite consultar todos os campos', function () {
    entrarComo('medico');
    $paciente = novaPaciente();
    $clinicos = [
        'queixa_principal' => 'Dor pélvica há uma semana',
        'historia_atual' => 'Relato da evolução dos sintomas.',
        'alergias' => 'Nega alergias conhecidas.',
        'medicamentos' => 'Medicação e posologia relatadas.',
        'peso' => 65.5, 'altura' => 1.65, 'saturacao' => 98,
        'gestacoes' => 0, 'ultima_menstruacao' => '2026-11-01',
        'conduta' => 'Orientações registradas pelo profissional.',
    ];
    $resposta = $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", [
        'data_atendimento' => '2026-11-14T13:30:00.000Z', 'dados_clinicos' => $clinicos,
    ])->assertCreated()->assertJsonPath('dados_clinicos', $clinicos);
    expect(Anamnese::findOrFail($resposta->json('id'))->dados_clinicos)->toBe($clinicos);
    $this->getJson("/api/pacientes/{$paciente->getKey()}/fichas")
        ->assertJsonPath('data.0.dados_clinicos', $clinicos);
});

it('valida campos clinicos antes de salvar com 422', function (array $clinicos, string $campo) {
    entrarComo('admin');
    $paciente = novaPaciente();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/fichas", [
        'data_atendimento' => '2026-11-14T13:30:00.000Z',
        'dados_clinicos' => $clinicos,
    ])->assertUnprocessable()->assertJsonValidationErrors([$campo]);
    $this->assertDatabaseCount('anamnese', 0);
})->with([
    'queixa obrigatoria' => [['historia_atual' => 'Relato'], 'dados_clinicos.queixa_principal'],
    'historia obrigatoria' => [['queixa_principal' => 'Consulta'], 'dados_clinicos.historia_atual'],
    'campo desconhecido' => [['injetado' => 'valor'], 'dados_clinicos'],
    'peso negativo' => [['peso' => -1], 'dados_clinicos.peso'],
    'saturacao acima de 100' => [['saturacao' => 101], 'dados_clinicos.saturacao'],
    'gestacoes fracionarias' => [['gestacoes' => 1.5], 'dados_clinicos.gestacoes'],
    'data invalida' => [['ultima_menstruacao' => '2026-02-30'], 'dados_clinicos.ultima_menstruacao'],
    'texto longo' => [['alergias' => str_repeat('a', 10001)], 'dados_clinicos.alergias'],
]);

it('medico consulta pacientes mas nao altera o cadastro administrativo', function () {
    entrarComo('medico');
    $paciente = novaPaciente();
    $this->getJson('/api/pacientes')->assertOk();
    $this->getJson("/api/pacientes/{$paciente->getKey()}")->assertOk();
    $this->postJson('/api/pacientes', [])->assertForbidden();
    $this->putJson("/api/pacientes/{$paciente->getKey()}", [])->assertForbidden();
    $this->deleteJson("/api/pacientes/{$paciente->getKey()}")->assertForbidden();
});

it('organiza a anamnese em categorias mantendo cada campo uma unica vez', function () {
    $resposta = $this->get('/pacientes/123/fichas/criar')->assertOk();
    preg_match_all('/data-clinico="([^"]+)"/', $resposta->getContent(), $camposRenderizados);
    $camposEsperados = array_keys(array_merge(...array_values(CamposAnamnese::SECOES)));
    expect($camposRenderizados[1])->toHaveCount(count($camposEsperados));
    expect(array_diff($camposEsperados, $camposRenderizados[1]))->toBeEmpty();
    expect(array_unique($camposRenderizados[1]))->toHaveCount(count($camposEsperados));
    $resposta->assertSee('Ciclo menstrual e climatério')
        ->assertSee('Histórico obstétrico')
        ->assertSee('Medidas corporais');
});
