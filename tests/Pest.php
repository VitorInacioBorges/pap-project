<?php

use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

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

function novaPaciente(string $cpf = '123.456.789-01', string $email = 'paciente@example.com', ?string $nome = null, ?string $criadoEm = null): Paciente
{
    $paciente = Paciente::query()->create([
        'email' => $email,
        'senha' => 'senha-segura-123',
        'cpf' => $cpf,
    ]);

    if ($criadoEm !== null) {
        $paciente->forceFill(['created_at' => $criadoEm])->save();
    }

    if ($nome !== null) {
        $paciente->dadosPessoais()->create(['nome' => $nome, 'data_nascimento' => '1990-01-01']);
    }

    return $paciente->refresh();
}
