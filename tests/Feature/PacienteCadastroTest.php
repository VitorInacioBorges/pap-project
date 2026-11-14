<?php

use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(fn () => entrarComo('secretario'));

function dadosNovaPaciente(array $extra = []): array
{
    return array_replace_recursive([
        'email' => 'marlene@example.com',
        'senha' => 'senha-segura-123',
        'cpf' => '123.456.789-01',
        'dados_pessoais' => [
            'nome' => 'Marlene Souza',
            'data_nascimento' => '1989-07-06',
            'estado_civil' => 'Casada',
            'escolaridade' => 'Médio completo',
        ],
    ], $extra);
}

it('cria paciente com dados pessoais numa so requisicao', function () {
    $this->postJson('/api/pacientes', dadosNovaPaciente())
        ->assertCreated()
        ->assertJsonPath('dados_pessoais.nome', 'Marlene Souza');

    $paciente = Paciente::query()->where('email', 'marlene@example.com')->firstOrFail();
    $this->assertDatabaseHas('dados_pessoais', [
        'id_paciente' => $paciente->id_paciente,
        'nome' => 'Marlene Souza',
        'estado_civil' => 'Casada',
    ]);
});

it('nao grava nada se os dados pessoais forem invalidos', function () {
    $dados = dadosNovaPaciente();
    unset($dados['dados_pessoais']['data_nascimento']);

    $this->postJson('/api/pacientes', $dados)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['dados_pessoais.data_nascimento']);

    expect(Paciente::withTrashed()->count())->toBe(0);
});

it('grava o cpf sempre com mascara', function () {
    $this->postJson('/api/pacientes', dadosNovaPaciente(['cpf' => '12345678901']))
        ->assertCreated()
        ->assertJsonPath('cpf', '123.456.789-01');

    $this->assertDatabaseHas('pacientes', ['cpf' => '123.456.789-01']);
});

it('recusa cpf em formato invalido', function () {
    $this->postJson('/api/pacientes', dadosNovaPaciente(['cpf' => '123.456']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cpf']);
});

it('recusa o mesmo cpf em outro formato', function () {
    novaPaciente('123.456.789-01', 'outra@example.com');

    $this->postJson('/api/pacientes', dadosNovaPaciente(['cpf' => '12345678901']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cpf']);
});

it('recusa estado civil fora da lista', function () {
    $this->postJson('/api/pacientes', dadosNovaPaciente(['dados_pessoais' => ['estado_civil' => 'Noiva']]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['dados_pessoais.estado_civil']);
});

it('edita a paciente criando os dados pessoais que faltavam', function () {
    $paciente = novaPaciente();

    $this->putJson("/api/pacientes/{$paciente->id_paciente}", [
        'dados_pessoais' => ['nome' => 'Nome Novo', 'data_nascimento' => '1990-01-01'],
    ])->assertOk()
        ->assertJsonPath('dados_pessoais.nome', 'Nome Novo');

    $this->assertDatabaseHas('dados_pessoais', [
        'id_paciente' => $paciente->id_paciente,
        'nome' => 'Nome Novo',
    ]);
});

it('edita a paciente sem trocar a senha quando ela nao vem', function () {
    $paciente = novaPaciente();

    $this->putJson("/api/pacientes/{$paciente->id_paciente}", [
        'email' => 'novo@example.com',
        'dados_pessoais' => ['nome' => 'Nome', 'data_nascimento' => '1990-01-01'],
    ])->assertOk();

    expect(Hash::check('senha-segura-123', $paciente->refresh()->senha))->toBeTrue();
});
