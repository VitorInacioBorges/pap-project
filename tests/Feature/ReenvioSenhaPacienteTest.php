<?php

use App\Mail\SenhaTemporariaPaciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('gera senha temporaria revoga acessos e exige troca no proximo login', function (string $cargo) {
    Mail::fake();
    entrarComo($cargo);
    $paciente = novaPaciente();
    $paciente->update(['first_login' => false]);
    $paciente->createToken('portal');
    $senhaAntiga = $paciente->senha;

    $this->postJson("/api/pacientes/{$paciente->getKey()}/reenviar-senha", ['email' => 'outro@example.com', 'senha' => 'senha-injetada'])
        ->assertOk()->assertJsonMissingPath('senha')->assertJsonMissingPath('senhaTemporaria');
    $paciente->refresh();
    expect($paciente->first_login)->toBeTrue();
    expect($paciente->senha)->not->toBe($senhaAntiga);
    $this->assertDatabaseCount('personal_access_tokens', 0);

    $senha = null;
    Mail::assertQueued(SenhaTemporariaPaciente::class, function ($mail) use ($paciente, &$senha) {
        $senha = $mail->senhaTemporaria;

        return $mail->hasTo($paciente->email) && $mail->email === $paciente->email && Hash::check($senha, $paciente->senha);
    });
    Mail::assertQueuedCount(1);
    expect(strlen($senha))->toBeGreaterThanOrEqual(12);
    $this->postJson('/api/login', ['email' => $paciente->email, 'senha' => 'senha-segura-123'])->assertUnauthorized();
    $this->postJson('/api/login', ['email' => $paciente->email, 'senha' => $senha])
        ->assertOk()->assertJsonPath('first_login', true);
    Sanctum::actingAs($paciente);
    $this->getJson('/api/pacientes')->assertForbidden()->assertJsonPath('first_login', true);
    $this->postJson('/api/pacientes/alterar-senha', [
        'senha_atual' => $senha,
        'nova_senha' => 'nova-senha-escolhida-123',
        'nova_senha_confirmation' => 'nova-senha-escolhida-123',
    ])->assertOk()->assertJsonPath('first_login', false);
    expect($paciente->refresh()->first_login)->toBeFalse();
    expect(Hash::check('nova-senha-escolhida-123', $paciente->senha))->toBeTrue();
})->with(['admin', 'secretario']);

it('recusa reenvio por cargo sem permissao com 403', function (string $cargo) {
    Mail::fake();
    entrarComo($cargo);
    $paciente = novaPaciente();
    $senha = $paciente->senha;
    $this->postJson("/api/pacientes/{$paciente->getKey()}/reenviar-senha")->assertForbidden();
    expect($paciente->refresh()->senha)->toBe($senha);
    Mail::assertNothingQueued();
})->with(['medico', 'enfermeiro']);

it('nao permite paciente redefinir senha de outra paciente com 403', function () {
    Mail::fake();
    $paciente = novaPaciente();
    $outra = novaPaciente('111.111.111-11', 'outra@example.com');
    Sanctum::actingAs($paciente->forceFill(['first_login' => false]));
    $this->postJson("/api/pacientes/{$outra->getKey()}/reenviar-senha")->assertForbidden();
    Mail::assertNothingQueued();
});

it('exige autenticacao para reenviar senha com 401', function () {
    $paciente = novaPaciente();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/reenviar-senha")->assertUnauthorized();
});

it('nao reenvia senha para paciente oculta ou excluida com 404', function (bool $excluida) {
    Mail::fake();
    entrarComo('admin');
    $paciente = novaPaciente();
    $paciente->forceFill(['hard_deleted' => $excluida])->save();
    $paciente->delete();
    $this->postJson("/api/pacientes/{$paciente->getKey()}/reenviar-senha")->assertNotFound();
    Mail::assertNothingQueued();
})->with([false, true]);

it('preserva senha e acessos quando falha ao enfileirar o email', function () {
    entrarComo('admin');
    $paciente = novaPaciente();
    $paciente->update(['first_login' => false]);
    $paciente->createToken('portal');
    $senha = $paciente->senha;
    Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('Fila indisponível'));
    $this->postJson("/api/pacientes/{$paciente->getKey()}/reenviar-senha")->assertServerError();
    expect($paciente->refresh()->senha)->toBe($senha);
    expect($paciente->first_login)->toBeFalse();
    $this->assertDatabaseCount('personal_access_tokens', 1);
});

it('limita reenvios repetidos com 429', function () {
    Mail::fake();
    entrarComo('admin');
    $paciente = novaPaciente();
    for ($tentativa = 0; $tentativa < 3; $tentativa++) {
        $this->postJson("/api/pacientes/{$paciente->getKey()}/reenviar-senha")->assertOk();
    }
    $this->postJson("/api/pacientes/{$paciente->getKey()}/reenviar-senha")->assertTooManyRequests();
    Mail::assertQueuedCount(3);
});

it('email explica o acesso e escapa os caracteres da senha', function () {
    $mail = new SenhaTemporariaPaciente('paciente@example.com', 'Senha<123>&');
    expect($mail->render())->toContain('paciente@example.com', 'Senha&lt;123&gt;&amp;', 'precisará escolher uma nova senha');
});
