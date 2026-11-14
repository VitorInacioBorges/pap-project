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
        'cpf' => '123.456.789-09',
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
    $this->postJson('/api/pacientes', dadosNovaPaciente(['cpf' => '12345678909']))
        ->assertCreated()
        ->assertJsonPath('cpf', '123.456.789-09');

    $this->assertDatabaseHas('pacientes', ['cpf' => '123.456.789-09']);
});

it('recusa cpf em formato invalido', function () {
    $this->postJson('/api/pacientes', dadosNovaPaciente(['cpf' => '123.456']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cpf']);
});

it('recusa o mesmo cpf em outro formato', function () {
    novaPaciente('123.456.789-09', 'outra@example.com');

    $this->postJson('/api/pacientes', dadosNovaPaciente(['cpf' => '12345678909']))
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

it('recusa cpf ja gravado sem mascara por cadastro antigo', function () {
    novaPaciente('12345678909', 'antiga@example.com');

    $this->postJson('/api/pacientes', dadosNovaPaciente(['cpf' => '123.456.789-09']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cpf']);
});

it('recusa cpf com digitos verificadores invalidos sem gravar paciente', function (mixed $cpf) {
    $this->postJson('/api/pacientes', dadosNovaPaciente(['cpf' => $cpf]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cpf']);

    $this->assertDatabaseCount('pacientes', 0);
    $this->assertDatabaseCount('dados_pessoais', 0);
})->with([
    'primeiro verificador errado' => '529.982.247-15',
    'segundo verificador errado' => '529.982.247-26',
    'cpf antigo ficticio' => '123.456.789-01',
    'curto' => '123456789',
    'longo' => '529982247250',
    'letras' => '5299822472a',
    'array' => [['52998224725']],
    ...array_map(fn (int $digito) => str_repeat((string) $digito, 11), range(0, 9)),
]);

it('aceita cpf com os dois verificadores corretos', function (string $cpf, string $formatado) {
    $this->postJson('/api/pacientes', dadosNovaPaciente(['cpf' => $cpf]))
        ->assertCreated()->assertJsonPath('cpf', $formatado);
    $this->assertDatabaseHas('pacientes', ['cpf' => $formatado]);
})->with([
    ['52998224725', '529.982.247-25'],
    ['111.444.777-35', '111.444.777-35'],
    ['01234567890', '012.345.678-90'],
    ['12345678909', '123.456.789-09'],
]);

it('recusa alterar cpf para invalido e preserva os dados existentes', function () {
    $paciente = novaPaciente(cpf: '529.982.247-25');
    $this->putJson("/api/pacientes/{$paciente->id_paciente}", ['cpf' => '52998224726'])
        ->assertUnprocessable()
        ->assertJsonPath('errors.cpf.0', 'CPF inválido. Confira os números informados.');
    $this->assertDatabaseHas('pacientes', ['id_paciente' => $paciente->id_paciente, 'cpf' => '529.982.247-25']);
});

it('permite corrigir o cpf e reenviar o mesmo cpf valido na edicao', function () {
    $paciente = novaPaciente();
    $this->putJson("/api/pacientes/{$paciente->id_paciente}", ['cpf' => '52998224725'])
        ->assertOk()->assertJsonPath('cpf', '529.982.247-25');
    $this->putJson("/api/pacientes/{$paciente->id_paciente}", ['cpf' => '529.982.247-25'])
        ->assertOk();
    $this->assertDatabaseHas('pacientes', ['id_paciente' => $paciente->id_paciente, 'cpf' => '529.982.247-25']);
});
