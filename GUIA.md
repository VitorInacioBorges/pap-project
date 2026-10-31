# Guia de leitura do projeto PAP

Este guia explica como ler e entender este projeto do zero, pensado para quem está começando em PHP e Laravel.

Os arquivos principais estão **comentados linha a linha**. Leia este guia primeiro e depois abra os arquivos na ordem da seção 2.

## Sumário

1. [O que é o projeto](#1-o-que-é-o-projeto)
2. [Ordem de leitura](#2-ordem-de-leitura)
3. [Arquivos comentados e o que cada um explica](#3-arquivos-comentados-e-o-que-cada-um-explica)
4. [Conceitos de PHP que aparecem no projeto](#4-conceitos-de-php-que-aparecem-no-projeto)
5. [O caminho de uma requisição](#5-o-caminho-de-uma-requisição)
6. [Como rodar e testar](#6-como-rodar-e-testar)
7. [Problemas conhecidos](#7-problemas-conhecidos)
8. [Como continuar estudando](#8-como-continuar-estudando)

## 1. O que é o projeto

É uma **API REST em Laravel 13** (PHP 8.3), sem telas. Pelos cargos (médico, enfermeiro, secretário, admin), é um sistema de clínica ou hospital. O que já existe:

- **Login** com identificador e senha, que devolve um token (Laravel Sanctum).
- **Cadastro de colaborador**, feito por um admin. O sistema gera um identificador (ex.: `MD-aB3dE9fG`) e uma senha temporária, e manda os dois por e-mail.
- **Listagem de usuários**, só para admin.

Quase todos os arquivos do repositório são do esqueleto padrão do Laravel. O código escrito pela equipe está em cerca de 10 arquivos, listados abaixo.

## 2. Ordem de leitura

Siga o caminho que uma requisição percorre, de fora para dentro:

| # | Arquivo | O que entender nele |
|---|---|---|
| 1 | [composer.json](composer.json) | As dependências: `laravel/framework`, `laravel/sanctum` (tokens) e `pestphp/pest` (testes). |
| 2 | [bootstrap/app.php](bootstrap/app.php) | Onde a aplicação é montada: arquivos de rotas, apelido `cargo` do middleware e erros em JSON nas rotas `api/*`. **Comece por aqui: explica os conceitos de PHP usados no projeto todo.** |
| 3 | [routes/api.php](routes/api.php) | **O mapa da API.** Todas as rotas ganham o prefixo `/api`. |
| 4 | [app/Http/Middleware/ValidaCargos.php](app/Http/Middleware/ValidaCargos.php) | Controle de permissão por cargo (`cargo:admin`). |
| 5 | [app/Http/Controllers/AuthController.php](app/Http/Controllers/AuthController.php) | Login: valida os dados, confere a senha com `Hash::check` e cria o token. |
| 6 | [app/Http/Controllers/UserController.php](app/Http/Controllers/UserController.php) | Listar e criar colaboradores. |
| 7 | [app/Services/colaboradorService.php](app/Services/colaboradorService.php) | Regra de negócio: gera o identificador (`MD-`, `EN-`, `SE-`, `AD-`) e a senha temporária. |
| 8 | [app/Models/User.php](app/Models/User.php) | O model Eloquent: chave primária `id_usuario` e campos preenchíveis. |
| 9 | [database/migrations/2026_09_28_191232_create_users_table.php](database/migrations/2026_09_28_191232_create_users_table.php) | A estrutura real da tabela `users`. |
| 10 | [app/Mail/senhaMail.php](app/Mail/senhaMail.php) e [resources/views/email/boasVindas.blade.php](resources/views/email/boasVindas.blade.php) | O e-mail de boas-vindas, que vai para a fila, e o template dele. |
| 11 | [.env.example](.env.example) | A configuração: SQLite, fila no banco e e-mail gravado no log. |

Pode ignorar por enquanto: `config/*` (padrão do Laravel), `vendor/`, `storage/`, `public/` e `.claude/`.

## 3. Arquivos comentados e o que cada um explica

Os arquivos abaixo receberam comentários explicativos em português. **Nenhuma linha de código mudou de comportamento**: só foram acrescentados comentários.

| Arquivo | O que os comentários explicam |
|---|---|
| [bootstrap/app.php](bootstrap/app.php) | Bloco no topo com os conceitos de PHP: `use`, `::`, `->`, encadeamento, argumentos nomeados, `__DIR__`. Também: por que se passa uma função como parâmetro (closure), o que `alias()` faz, arrays associativos, `::class` e arrow function `fn () =>`. |
| [routes/api.php](routes/api.php) | Facade `Route::`, o formato `[Classe::class, 'metodo']`, `middleware()->group()`, parâmetros de middleware (`cargo:admin`) e a ordem em que os middlewares rodam. |
| [app/Http/Middleware/ValidaCargos.php](app/Http/Middleware/ValidaCargos.php) | O que é um middleware (a "fila de porteiros"), `namespace`, `$next`, o variádico `...$cargos`, `in_array` e a resposta 403. |
| [app/Http/Controllers/AuthController.php](app/Http/Controllers/AuthController.php) | Herança (`extends`), injeção de dependência, `validate()`, consulta com Eloquent (`where()->first()`), hash de senha e criação de token. |
| [app/Http/Controllers/UserController.php](app/Http/Controllers/UserController.php) | Injeção do service, `User::all()`, o envio de e-mail lido "de dentro para fora" e a fila. |
| [app/Services/colaboradorService.php](app/Services/colaboradorService.php) | O que é um service, `$this`, `public` × `private`, o spread `...$dados`, `User::create`, `Hash::make` e a expressão `match`. |
| [app/Models/User.php](app/Models/User.php) | O que é um model e como o Eloquent traduz para SQL, trait (`use` dentro da classe), `$primaryKey` e `$fillable`. |
| [database/migrations/2026_09_28_191232_create_users_table.php](database/migrations/2026_09_28_191232_create_users_table.php) | O que é uma migration, os comandos `migrate`, a classe anônima, `up()`/`down()` e o que cada coluna faz. |
| [app/Mail/senhaMail.php](app/Mail/senhaMail.php) | Mailable, `implements ShouldQueue` (interface), traits de fila, construtor com promoção de propriedades, e como o array `with:` vira variáveis na view. |
| [resources/views/email/boasVindas.blade.php](resources/views/email/boasVindas.blade.php) | O que é Blade, comentário `{{-- --}}` (não vai no e-mail) e como `{{ $variavel }}` imprime e escapa valores. |
| [.env.example](.env.example) | Para que serve o `.env`, e as configurações de banco (SQLite), fila (`database`) e e-mail (`log`). |

O [composer.json](composer.json) não foi comentado porque o formato JSON não aceita comentários.

Nos pontos em que o código tem problemas conhecidos (seção 7), há um comentário começando com `ATENÇÃO:`. Para listar todos:

```sh
grep -rn "ATENÇÃO" app routes database
```

## 4. Conceitos de PHP que aparecem no projeto

Resumo rápido. A explicação completa, com exemplos, está nos comentários do [bootstrap/app.php](bootstrap/app.php).

| Sintaxe | O que é | Exemplo no projeto |
|---|---|---|
| `use A\B\Classe;` (no topo) | Importa uma classe; dá um apelido curto. Não executa nada. | `use App\Models\User;` |
| `use Trait;` (dentro da classe) | "Cola" os métodos de um trait na classe. | `use HasApiTokens;` |
| `namespace A\B;` | O "endereço" da classe. O Composer usa isso para achar o arquivo. | `namespace App\Http\Middleware;` |
| `Classe::metodo()` | Chama um método estático, da classe e não de um objeto. | `User::where(...)` |
| `$objeto->metodo()` | A seta: acessa um método ou propriedade de um objeto (como o `.` do JavaScript). | `$request->user()` |
| `a()->b()->c()` | Encadeamento: cada método devolve um objeto e o próximo é chamado nele. | `Application::configure()->withRouting()->create()` |
| `nome: valor` | Argumento nomeado (PHP 8+). | `new Envelope(subject: '...')` |
| `function (Tipo $x) { ... }` | Closure: função anônima passada como parâmetro para o Laravel chamar depois. | `->withMiddleware(function (Middleware $middleware) {...})` |
| `fn ($x) => expressão` | Arrow function: closure curta que já devolve o resultado. | `fn (Request $request) => $request->is('api/*')` |
| `['chave' => valor]` | Array associativo (como um dicionário ou objeto JSON). | `['token' => $token]` |
| `...$array` | Spread: espalha os itens de um array. Nos parâmetros, junta os argumentos num array. | `...$dados`, `...$cargos` |
| `Classe::class` | O nome completo da classe como texto. | `UserController::class` |
| `match ($x) { ... }` | Como um `switch`, mas devolve um valor e compara com `===`. | `geraIdentificador()` |
| `extends` / `implements` | Herança de classe / assinatura de um contrato (interface). | `class senhaMail extends Mailable implements ShouldQueue` |
| `.` | Concatena strings. | `'MD-' . $string` |

## 5. O caminho de uma requisição

```
POST /api/users  (header: Authorization: Bearer <token>)
   │
   ├─ auth:sanctum      → descobre quem é o usuário pelo token (tabela personal_access_tokens)
   ├─ cargo:admin       → ValidaCargos: se o cargo não for admin, devolve 403
   ├─ UserController@criaColaborador
   │     ├─ valida email e cargo
   │     ├─ ColaboradorService::cria()  (o Laravel injeta o service automaticamente)
   │     │     ├─ gera o identificador "MD-xxxxxxxx"
   │     │     ├─ gera a senha temporária e grava só o hash dela
   │     │     └─ User::create(...)
   │     └─ Mail::to(...)->send(senhaMail)  → entra na tabela jobs (fila)
   └─ resposta 201
                 ... depois, `php artisan queue:work` processa a fila e "envia" o e-mail
```

O login (`POST /api/login`) é mais curto: não passa por middleware, e o `AuthController` valida os dados, confere a senha e devolve `{ "token": "..." }`.

Conceitos do Laravel para estudar na [documentação oficial](https://laravel.com/docs): **Routing**, **Middleware**, **Controllers**, **Validation**, **Service Container** (injeção de dependência), **Eloquent**, **Migrations**, **Sanctum**, **Mail** e **Queues**.

## 6. Como rodar e testar

```sh
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan serve          # API em http://localhost:8000
php artisan queue:work     # em outro terminal, para os e-mails saírem da fila
php artisan route:list     # lista todas as rotas
php artisan tinker         # console para mexer nos models
```

Como `MAIL_MAILER=log`, o e-mail não é enviado de verdade: o conteúdo aparece em `storage/logs/laravel.log`.

## 7. Problemas conhecidos

Estes problemas foram encontrados lendo o código, sem executá-lo. Alguns impedem o fluxo de funcionar:

1. **A coluna `identificador` não existe no banco.** O commit 96234e2 renomeou a coluna para `alias` na migration. O model, o service e o login continuam usando `identificador`, então o cadastro e o login quebram. É preciso escolher um nome só.
2. **Não há como criar o primeiro admin.** O cadastro exige um admin logado. O [DatabaseSeeder](database/seeders/DatabaseSeeder.php) e a [UserFactory](database/factories/UserFactory.php) ainda são os padrões, com `name` e `password` (colunas que não existem). O model também não usa `HasFactory`, então `User::factory()` falha.
3. **[routes/web.php](routes/web.php) está quebrado.** Usa `UserController` sem `use` e aponta para um método `store` que não existe. Parece sobra: as rotas certas estão em `api.php`.
4. **O service usa `fake()` (Faker) para gerar senha e identificador.** O Faker é dependência só de desenvolvimento (`require-dev`), então não roda em produção, e não serve para gerar segredos. O adequado é `Str::random()` ou `Str::password()`.
5. **O cargo é validado só como `string`.** Um cargo inexistente faz o `match` sem `default` lançar `UnhandledMatchError` (erro 500). A validação deveria usar `Rule::in(['medico','enfermeiro','secretario','admin'])`.
6. **`criaColaborador` devolve o usuário errado.** Retorna `$request->user()`, que é o admin logado, e não o colaborador criado.
7. **`User` estende `Model` em vez de `Authenticatable`**, que é a classe base normal para usuários que fazem login. Pode funcionar com o Sanctum, mas é fora do padrão.
8. **O `email` tem só 30 caracteres** na migration, e muitos e-mails reais passam disso.
9. **`GET /api/users` devolve o hash da senha**, porque o model não esconde a coluna `senha`.
10. **O [README.md](README.md) ficou com marcadores de conflito de merge** (`<<<<<<<`, `=======`, `>>>>>>>`).
11. **Os testes são só os exemplos padrão.** Os fluxos de login e cadastro não têm testes.

## 8. Como continuar estudando

- Releia o caminho da seção 5 com a documentação do Laravel aberta em cada conceito.
- Depois de corrigir o problema 1 e criar um admin pelo `php artisan tinker`, chame os endpoints com Postman ou Insomnia: primeiro `POST /api/login`, depois `POST /api/users` com o token no header `Authorization: Bearer <token>`.
- Use `git log -p` para ver como o projeto evoluiu, commit a commit.
