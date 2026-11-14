# Boas práticas

Este documento reúne as regras que a equipe segue ao mexer no Projeto PAP.
Cada regra vem de um padrão que o código já usa ou de um erro que já aconteceu.
Para entender as camadas citadas aqui, leia a [arquitetura](arquitetura.md).

## API e backend

### Coloque a regra de negócio no service

O controller valida a entrada e devolve a resposta. O que o sistema faz com os
dados fica no service, em `app/Services/`. Por exemplo, `PacienteController`
valida o CPF, e `PacienteService::criar()` grava a paciente e os dados pessoais
na mesma transação.

Quando uma operação grava em mais de uma tabela, use `DB::transaction()`. Assim,
se uma parte falhar, nada fica gravado pela metade.

### Valide tudo na API, mesmo o que a tela já valida

A tela confere campos obrigatórios só para responder mais rápido. Quem garante
os dados é a API, porque qualquer pessoa pode chamá-la sem passar pela tela.

- Valide com `$request->validate([...])` no controller.
- Para listas fixas, use `Rule::in(...)` com as constantes de
  `App\Support\OpcoesPaciente`. Não repita a lista no PHP.
- Ao criar um campo novo, acrescente o nome dele em português em
  `lang/pt_BR/validation.php`, na chave `attributes`. Sem isso, a mensagem
  mostra o nome técnico do campo.

### Use closure em `Rule::unique` com valor booleano

`Rule::unique('pacientes', 'cpf')->where('hard_deleted', false)` não funciona
como parece: a regra transforma `false` no texto `'false'`, e no SQLite a
consulta nunca encontra a linha. Passe a condição numa closure:

```php
Rule::unique('pacientes', 'email')
    ->where(fn ($query) => $query->where('hard_deleted', false));
```

### Escreva SQL que funcione no PostgreSQL e no SQLite

O sistema roda em PostgreSQL, e os testes rodam em SQLite. Prefira o query
builder do Eloquent. Quando precisar de SQL à mão, use funções que existem nos
dois bancos, como `lower()`, `replace()` e `CASE WHEN`. Use também condições
booleanas sem comparar com número, como `WHERE NOT hard_deleted`.

Depois de mudar uma consulta, rode-a também no PostgreSQL local, por exemplo
pelo `php artisan tinker`. Os testes não passam por ele.

### Crie uma migration nova em vez de editar uma antiga

Uma migration que já rodou na máquina de alguém não roda de novo. Para mudar
uma tabela, crie outra migration com `php artisan make:migration`. Escreva
sempre o `down()`, para a mudança poder ser desfeita.

Quando a mudança afetar dados que já existem, inclua a conversão na migration.
Um exemplo é `2026_11_15_000003_normaliza_cpf_dos_pacientes`.

### Trate os estados da paciente pelos nomes certos

- **Ocultar** usa a exclusão suave do Laravel (`delete()` com `SoftDeletes`) e
  pode ser desfeita.
- **Excluir** marca `hard_deleted = true`. Nenhuma rota devolve uma paciente
  excluída, porque `Paciente::resolveRouteBindingQuery()` a filtra.

Ao escrever uma consulta nova de pacientes, filtre `hard_deleted = false` de
propósito. Não conte com o fato de toda paciente excluída também estar oculta.

### Apague os tokens quando alguém perder o acesso

Ao ocultar, excluir ou desativar alguém, apague os tokens dessa pessoa com
`$usuario->tokens()->delete()`. Sem isso, quem já estava logado continua com
acesso até o token ser usado de novo.

## Telas e JavaScript

### Chame a API sempre pelo `api()`

Use `api(caminho, { method, body })`, de `resources/js/api.js`. Não use `fetch`
direto. O `api()` envia o token, manda de volta para o login quando a sessão
cai e transforma os erros 422 em `ErroValidacao`, com os erros por campo.

### Comece toda tela com `protegerPagina()`

O módulo de cada tela começa com:

```js
const usuario = await protegerPagina();

if (usuario) {
    // código da tela
}
```

Declare quem pode ver a tela no `<body>` da view, com `data-tipo` e
`data-cargos`. Nunca mostre dados antes de `protegerPagina()` devolver o
usuário.

### Nunca use `innerHTML` com dados da API

Coloque texto vindo da API com `textContent`, `value` ou atributos. Um nome de
paciente como `<img src=x onerror=alert(1)>` precisa aparecer escrito, e não ser
executado. Para montar listas, clone um `<template>` e preencha os campos com
`textContent`, como faz `montarLinha()` em `pacientes.js`.

### Evite envio duplicado

Envolva o envio de formulário em `desabilitarEnquanto(botao, async () => ...)`.
O botão fica desabilitado antes da requisição sair, então um duplo clique gera
uma só chamada.

### Mostre cada erro embaixo do seu campo

Cada campo tem um parágrafo `data-erro="<nome do campo na API>"` com um `id`.
O campo aponta para esse parágrafo com `aria-describedby`. Depois, chame
`limparErros(formulario)` antes de enviar e `mostrarErros(formulario, erros)`
quando a API responder 422.

### Registre cada módulo novo no Vite

Toda tela nova ganha um arquivo em `resources/js/pages/`. Esse arquivo entra na
lista `input` do `vite.config.js` e é carregado na view com
`@vite('resources/js/pages/<tela>.js')`, dentro de `@push('scripts')`.

### Use os tokens de cor e mantenha a tela acessível

- Use as classes dos tokens (`bg-pap`, `text-tinta`, `border-rosa`). Não escreva
  cores soltas como `bg-[#009A94]`. Uma cor nova entra no `@theme` do
  `resources/css/app.css`.
- Todo campo tem um `<label>` visível.
- Todo botão e link tem foco visível e pelo menos 44px de altura.
- Botões e links respondem ao passar o mouse e ao clicar.
- Escreva os textos da tela em português do Brasil, com os mesmos termos do
  design: "Paciente", "Atendente", "Ocultar", "Excluir".

## Testes

- Escreva o teste antes do código e rode-o para vê-lo falhar. Um teste que
  passa antes da mudança não prova nada.
- Para cada rota nova, teste o caminho que funciona, o 401 sem token, o 403 sem
  o cargo e o 422 de validação.
- Use as funções de apoio de `tests/Pest.php`, como `entrarComo('secretario')`
  e `novaPaciente()`. Funções novas de apoio também vão para lá, porque o Pest
  não aceita a mesma função declarada em dois arquivos.
- Rode a suíte inteira antes de cada commit: `php artisan test --compact`.
- Mudanças em telas precisam de um teste no navegador, pelo caminho principal e
  pelos erros. Os testes do Pest não executam o JavaScript.

## Decisões e documentação

Quando uma especificação deixar uma escolha para quem implementa, marque a
escolha no código, no ponto onde ela foi aplicada:

```php
// DECISAO: <o que precisa ser decidido> — escolhido: <o que foi feito>; alternativas: <outras opções>
```

No Blade, use `{{-- DECISAO: … --}}`, e no CSS, `/* DECISAO: … */`. Para
encontrar todas, execute `grep -rn "DECISAO:" app resources`.

Ao mudar a forma de rodar o projeto, atualize a [execução](execucao.md). Ao
mudar rotas, tabelas ou camadas, atualize a [arquitetura](arquitetura.md).

## Git e pull requests

- Crie uma branch por tarefa a partir da `develop`, com o nome
  `feature/<assunto>` ou `fix/<assunto>`.
- Escreva as mensagens de commit em português, com o tipo na frente: `feat:`,
  `fix:`, `docs:`, `chore:`.
- Abra o pull request para a `develop`. Na descrição, diga o que mudou para
  quem usa a API, o que rodar depois do merge e como você testou.
- Nunca versione o `.env`. Ele guarda as senhas do banco e do e-mail. Uma
  configuração nova vai para o `.env.example`, com valor de exemplo.

## Problemas conhecidos

Estes pontos já existem no código e ainda precisam de correção. Não repita os
mesmos padrões em código novo.

- `GET /api/users/{id}` não exige cargo: qualquer pessoa logada lê os dados de
  um colaborador.
- Os tokens do Sanctum nunca expiram no servidor (`sanctum.expiration` vazio).
- `colaboradorService` e `PacienteService` geram alias e códigos com `fake()`,
  que depende de um pacote só de desenvolvimento. Em código de produção, use
  `Str::random()`.
- `colaboradorService::atualizarColaborador()` falha quando o `PUT` não envia
  `cargo`.
- O `DatabaseSeeder` usa colunas que não existem em `users`.
- As migrations não criam a tabela `cache`. Por isso, o `.env` local usa
  `CACHE_STORE=file`.
