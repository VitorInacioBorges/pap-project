<?php

it('abre cada tela sem autenticacao no servidor', function (string $url) {
    $this->get($url)->assertOk();
})->with(['/login', '/inicio', '/pacientes', '/configuracoes', '/paciente']);

it('usa o layout protegido nas telas internas', function (string $url) {
    $this->get($url)
        ->assertOk()
        ->assertSee('id="app-conteudo"', false)
        ->assertSee('id="carregando"', false);
})->with(['/inicio', '/pacientes', '/configuracoes', '/paciente']);

it('restringe a tela de pacientes a secretario e admin', function () {
    $this->get('/pacientes')->assertSee('data-cargos="secretario,admin"', false);
});

it('marca o portal como tela de paciente', function () {
    $this->get('/paciente')->assertSee('data-tipo="paciente"', false);
});
