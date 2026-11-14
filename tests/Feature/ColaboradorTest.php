<?php

use App\Mail\senhaMail;
use App\Models\Anamnese;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('admin cadastra colaborador com credenciais temporarias e primeiro acesso', function (string $cargo, string $prefixo) {
    Mail::fake();
    $admin = entrarComo('admin');
    $resposta = $this->postJson('/api/users', ['nome' => 'Nova Colaboradora', 'email' => 'nova@pap.local', 'cargo' => $cargo, 'first_login' => false, 'ativo' => false])
        ->assertCreated()->assertJsonPath('cargo', $cargo)->assertJsonPath('first_login', true)->assertJsonMissingPath('senha');
    $novo = User::where('email', 'nova@pap.local')->firstOrFail();
    expect($novo->getKey())->not->toBe($admin->getKey());
    expect($novo->alias)->toStartWith($prefixo);
    expect(Hash::check($resposta->json('senhaTemporaria'), $novo->senha))->toBeTrue();
    expect($novo->ativo)->toBeTrue();
    $this->getJson('/api/users')->assertOk()->assertJsonMissingPath('0.senha')->assertJsonMissingPath('1.senhaTemporaria');
    Mail::assertQueued(senhaMail::class, fn ($mail) => $mail->hasTo('nova@pap.local') && $mail->identificador === $novo->alias && $mail->senhaTemporaria === $resposta->json('senhaTemporaria'));
    Mail::assertQueuedCount(1);

    $login = $this->postJson('/api/login', ['alias' => $novo->alias, 'senha' => $resposta->json('senhaTemporaria')])
        ->assertOk()->assertJsonPath('first_login', true);
    Sanctum::actingAs($novo);
    $this->getJson('/api/pacientes')->assertForbidden()->assertJsonPath('first_login', true);
})->with([['medico', 'MD-'], ['secretario', 'SE-']]);

it('recusa cadastro de colaborador para outro cargo com 403', function (string $cargo) {
    Mail::fake();
    entrarComo($cargo);
    $this->postJson('/api/users', ['nome' => 'Pessoa', 'email' => 'nova@pap.local', 'cargo' => 'medico'])->assertForbidden();
    $this->assertDatabaseMissing('users', ['email' => 'nova@pap.local']);
    Mail::assertNothingQueued();
})->with(['medico', 'secretario', 'enfermeiro']);

it('exige autenticacao para cadastrar colaborador com 401', function () {
    $this->postJson('/api/users', [])->assertUnauthorized();
});

it('valida nome email e cargo do novo colaborador com 422', function (array $dados, string $campo) {
    Mail::fake();
    entrarComo('admin');
    $this->postJson('/api/users', $dados)->assertUnprocessable()->assertJsonValidationErrors([$campo]);
    $this->assertDatabaseCount('users', 1);
    Mail::assertNothingQueued();
})->with([
    'nome obrigatorio' => [['email' => 'nova@pap.local', 'cargo' => 'medico'], 'nome'],
    'email invalido' => [['nome' => 'Pessoa', 'email' => 'invalido', 'cargo' => 'medico'], 'email'],
    'email repetido' => [['nome' => 'Pessoa', 'email' => 'admin@example.com', 'cargo' => 'medico'], 'email'],
    'cargo desconhecido' => [['nome' => 'Pessoa', 'email' => 'nova@pap.local', 'cargo' => 'superuser'], 'cargo'],
    'novo admin proibido' => [['nome' => 'Pessoa', 'email' => 'nova@pap.local', 'cargo' => 'admin'], 'cargo'],
    'email longo' => [['nome' => 'Pessoa', 'email' => str_repeat('a', 25).'@pap.local', 'cargo' => 'medico'], 'email'],
]);

it('mostra a tela de colaboradores com acesso restrito ao admin', function () {
    $this->get('/colaboradores')->assertOk()->assertSee('data-cargos="admin"', false)->assertSee('Novo colaborador');
});

it('nao deixa conta parcial quando nao consegue enfileirar as credenciais', function () {
    entrarComo('admin');
    Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('Fila indisponível'));
    $this->postJson('/api/users', ['nome' => 'Pessoa', 'email' => 'nova@pap.local', 'cargo' => 'medico'])
        ->assertServerError();
    $this->assertDatabaseMissing('users', ['email' => 'nova@pap.local']);
});

it('desativa colaborador revoga acessos e preserva autoria e historico', function () {
    $medico = entrarComo('medico');
    $medico->forceFill(['nome' => 'Dra. Maria', 'senha' => 'senha-medica-123', 'first_login' => false])->save();
    $dadosAntes = $medico->only(['nome', 'alias', 'email', 'cargo', 'senha', 'first_login']);
    $token = $medico->createToken('sessao-antiga')->plainTextToken;
    $paciente = novaPaciente();
    $ficha = Anamnese::factory()->for($paciente, 'paciente')->for($medico, 'usuario')->create();
    entrarComo('admin');

    $this->putJson("/api/users/{$medico->getKey()}", ['ativo' => false])
        ->assertOk()->assertJsonPath('ativo', false);
    expect($medico->refresh()->only(array_keys($dadosAntes)))->toBe($dadosAntes);
    expect($medico->deleted_at)->toBeNull();
    $this->assertDatabaseCount('personal_access_tokens', 0);
    $this->assertDatabaseHas('anamnese', ['id' => $ficha->getKey(), 'usuario_id' => $medico->getKey()]);
    $this->getJson('/api/users')->assertJsonFragment(['id_usuario' => $medico->getKey(), 'ativo' => false]);
    $this->getJson("/api/pacientes/{$paciente->getKey()}/fichas")
        ->assertOk()->assertJsonPath('data.0.usuario.nome', 'Dra. Maria');
    $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    $this->postJson('/api/login', ['alias' => $medico->alias, 'senha' => 'senha-medica-123'])->assertUnauthorized();
});

it('reativa colaborador com as mesmas credenciais sem restaurar sessoes antigas', function () {
    $medico = entrarComo('medico');
    $medico->forceFill(['ativo' => false, 'senha' => 'senha-medica-123', 'first_login' => false])->save();
    $token = $medico->createToken('sessao-antiga')->plainTextToken;
    $senha = $medico->senha;
    $alias = $medico->alias;
    entrarComo('admin');
    $this->putJson("/api/users/{$medico->getKey()}", ['ativo' => true])->assertOk()->assertJsonPath('ativo', true);
    expect($medico->refresh()->senha)->toBe($senha);
    expect($medico->alias)->toBe($alias);
    $this->withToken($token)->getJson('/api/me')->assertUnauthorized();
    $this->postJson('/api/login', ['alias' => $alias, 'senha' => 'senha-medica-123'])->assertOk();
});

it('impede admin de desativar a propria conta com 403', function (bool $zeros) {
    $admin = entrarComo('admin');
    $id = ($zeros ? '00' : '').$admin->getKey();
    $this->putJson("/api/users/{$id}", ['ativo' => false])
        ->assertForbidden()->assertJsonPath('message', 'Você não pode desativar a própria conta.');
    expect($admin->refresh()->ativo)->toBeTrue();
})->with([false, true]);

it('recusa alteracao de status por outros cargos com 403', function (string $cargo) {
    $alvo = entrarComo('admin');
    entrarComo($cargo);
    $this->putJson("/api/users/{$alvo->getKey()}", ['ativo' => false])->assertForbidden();
    expect($alvo->refresh()->ativo)->toBeTrue();
})->with(['medico', 'secretario', 'enfermeiro']);

it('exige autenticacao para alterar status de colaborador com 401', function () {
    $this->putJson('/api/users/1', ['ativo' => false])->assertUnauthorized();
});

it('valida status de colaborador e recusa usuario inexistente', function () {
    $admin = entrarComo('admin');
    $this->putJson("/api/users/{$admin->getKey()}", ['ativo' => 'invalido'])->assertUnprocessable()->assertJsonValidationErrors(['ativo']);
    $this->putJson('/api/users/99999', ['ativo' => false])->assertNotFound();
    expect($admin->refresh()->ativo)->toBeTrue();
});
