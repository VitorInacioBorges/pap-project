# Arquitetura do projeto

Este documento explica como o Projeto PAP está organizado: as camadas, o
caminho de uma requisição, a autenticação, o banco de dados e onde fica cada
coisa no código. Para preparar a máquina, leia a [execução](execucao.md). Para
as regras de código, leia as [boas práticas](boas-praticas.md).

## Visão geral

O Projeto PAP é o sistema do Ambulatório de Saúde UEPG (Prevenção e Educação
na Atenção à Saúde da Mulher). Hoje ele cobre o login da equipe e das
pacientes, a conta do colaborador e o cadastro de pacientes.

O sistema tem duas partes que rodam no mesmo projeto Laravel:

- **API JSON**, em `/api`. Ela guarda todas as regras de negócio e protege os
  dados com tokens do Laravel Sanctum.
- **Telas em Blade**, nas rotas web. O servidor só entrega o HTML. O
  JavaScript de cada tela busca e envia os dados pela API, com o token que o
  login devolve.

```text
Navegador
  │  1. GET /pacientes ──────────────► routes/web.php → Route::view → Blade (HTML vazio)
  │  2. JS da tela: GET /api/me ─────► routes/api.php → middlewares → MeController
  │  3. JS da tela: GET /api/pacientes ► middlewares → PacienteController → PacienteService → Paciente (Eloquent) → PostgreSQL
  ▼
Tela preenchida com os dados da API
```

As rotas web não autenticam ninguém. Quem decide o que cada pessoa pode ver é a
API. Por isso, uma tela aberta sem token mostra só "Carregando…" e vai para o
login, sem nenhum dado.

## Camadas e pastas

| Camada | Onde fica | O que faz |
|---|---|---|
| Rotas web | `routes/web.php` | Um `Route::view` por tela. Sem autenticação. |
| Views | `resources/views/` | HTML das telas. `layouts/app.blade.php` é o layout dos colaboradores. |
| Cliente da API | `resources/js/api.js` | Guarda o token, chama a API, trata erros e protege as telas. |
| JavaScript das telas | `resources/js/pages/*.js` | Um módulo por tela, carregado com `@vite` na própria view. |
| Menu e logout | `resources/js/layout.js` | Abre e fecha o menu lateral e liga os botões de sair. |
| Estilos | `resources/css/app.css` | Tailwind 4, com as cores e fontes como tokens no `@theme`. |
| Rotas da API | `routes/api.php` | Todas as rotas JSON, com o prefixo `/api`. |
| Middlewares | `app/Http/Middleware/` | Barram quem não pode passar, antes do controller. |
| Controllers | `app/Http/Controllers/` | Validam a entrada e devolvem JSON. |
| Services | `app/Services/` | Regras de negócio e transações. |
| Models | `app/Models/` | Tabelas do banco, pelo Eloquent. |
| Apoio | `app/Support/OpcoesPaciente.php` | Listas fixas e formatação de CPF. |
| Traduções | `lang/pt_BR/validation.php` | Mensagens de validação em português. |
| Testes | `tests/Feature/` | Testes Pest de cada rota da API e das telas. |

## Autenticação e sessão

O login funciona igual para as telas e para qualquer outro cliente da API:

1. A tela envia `POST /api/login` com `alias` e `senha` (colaborador) ou
   `email` e `senha` (paciente). A rota aceita 6 tentativas por minuto.
2. A API devolve um token do Sanctum e o valor de `first_login`.
3. O `api.js` guarda o token no `sessionStorage` do navegador e o envia em
   todas as chamadas, no cabeçalho `Authorization: Bearer …`.
4. Cada tela chama `protegerPagina()`, que busca `GET /api/me` e decide:
   - sem token ou com token inválido (401): vai para `/login`;
   - tipo de usuário errado: vai para a página inicial de quem está logado;
   - colaborador no primeiro acesso: vai para `/configuracoes`;
   - cargo sem permissão: mostra "Você não tem permissão para esta tela.";
   - tudo certo: mostra o conteúdo.
5. **Sair** chama `POST /api/logout`, que apaga o token no servidor, e limpa o
   `sessionStorage`.

Como o token fica no `sessionStorage`, fechar a aba encerra a sessão no
navegador. Essa escolha está marcada com `DECISAO:` no `api.js`.

### Primeiro acesso

Todo usuário novo nasce com `first_login = true`. Enquanto a senha não muda, o
middleware `primeiro_login` responde 403 nas rotas protegidas. Só três rotas
ficam liberadas: `GET /api/me`, a troca de senha e o logout. A troca de senha
apaga os tokens antigos e devolve um token novo.

### Middlewares

Os apelidos são registrados em `bootstrap/app.php`.

| Apelido | Classe | O que barra |
|---|---|---|
| `clear_sanctum` | `ResetSanctumGuard` | Esquece o usuário em memória quando o token da requisição já foi apagado. |
| `auth:sanctum` | Sanctum | Requisição sem token válido (401). |
| `ativo` | `ValidaSeUsuarioAtivo` | Colaborador com `ativo = false` (403). |
| `primeiro_login` | `ExigeTrocaSenhaPrimeiroAcesso` | Quem ainda não trocou a senha temporária (403 com `first_login: true`). |
| `cargo:…` | `ValidaCargos` | Cargo fora da lista, por exemplo `cargo:secretario,admin` (403). |

## Usuários e cargos

Existem dois tipos de usuário, em tabelas separadas:

| Tipo | Tabela e model | Login | Telas |
|---|---|---|---|
| Colaborador | `users`, `App\Models\User` | `alias`, por exemplo `SE-aB3dE9fG` | `/inicio`, `/pacientes`, `/configuracoes` |
| Paciente | `pacientes`, `App\Models\Paciente` | `email` | `/paciente` |

O cargo do colaborador fica na coluna `cargo`. As telas mostram um rótulo:

| Valor no banco | Na tela | Acessa pacientes |
|---|---|---|
| `secretario` | Atendente | Sim |
| `admin` | Administrador(a) | Sim, e também gerencia colaboradores |
| `medico` | Médico(a) | Não |
| `enfermeiro` | Enfermeiro(a) | Não |

O admin cria colaboradores por `POST /api/users`. O `colaboradorService` gera o
alias pelo cargo (`MD-`, `EN-`, `SE-`, `AD-`) e uma senha temporária, que vão
por e-mail.

## Banco de dados

O banco é PostgreSQL. Os testes usam SQLite em memória, então todo SQL escrito
à mão precisa funcionar nos dois.

| Tabela | O que guarda |
|---|---|
| `users` | Colaboradores: `nome`, `alias`, `email`, `senha`, `cargo`, `ativo`, `first_login`, `deleted_at`. |
| `pacientes` | Login da paciente: `email`, `senha`, `cpf`, `codigo_paciente`, `first_login`, `hard_deleted`, `deleted_at`. |
| `dados_pessoais` | Dados da paciente, um registro por paciente: nome, nascimento, peso, altura, PA, CA, profissão, escolaridade, estado civil, telefone e endereço. |
| `personal_access_tokens` | Tokens do Sanctum. |
| `jobs` | Fila de e-mails. |
| `sessions` | Sessões do Laravel. As telas não dependem delas. |

### Estados da paciente

| Estado | Como fica no banco | O que acontece |
|---|---|---|
| Ativa | `deleted_at` vazio e `hard_deleted = false` | Aparece na lista e pode entrar no portal. |
| Oculta (inativa) | `deleted_at` preenchido | Aparece só com o filtro **Inativos**, não entra no portal e pode ser reativada. |
| Excluída | `hard_deleted = true` e `deleted_at` preenchido | Some de todas as rotas (404). O registro fica no banco como histórico. |

O CPF é gravado sempre como `000.000.000-00`. CPF e e-mail são únicos entre as
pacientes não excluídas, por índices únicos parciais
(`WHERE NOT hard_deleted`). Assim, uma paciente excluída pode ser cadastrada
de novo.

### O que ainda falta do banco planejado

O diagrama do banco prevê a anamnese e as tabelas ligadas a ela:
`anamnese`, `local_anamnese`, `preparo_pre_exame`, `antecedentes_obstetricos`,
`historico_ginecologico` e `historico_oncologico`. Por enquanto existe só o
model `App\Models\Anamnese`, sem migration nem rotas.

O diagrama também prevê `cpf` e `area_academico` em `usuario`. O projeto manteve
o login de colaborador por alias e ainda não tem essas colunas.

## Rotas da API

Todas ficam em `routes/api.php`, respondem JSON e passam por `clear_sanctum`.

| Método e caminho | Quem pode | O que faz |
|---|---|---|
| `POST /api/login` | Todos | Login de colaborador ou paciente. |
| `POST /api/alterar-senha` | Logado | Troca a senha e devolve um token novo. |
| `POST /api/pacientes/alterar-senha` | Logado | Mesmo que a anterior, usada pelo portal. |
| `GET /api/me` | Logado, mesmo no primeiro acesso | Dados de quem está logado. |
| `POST /api/logout` | Logado | Apaga o token atual. |
| `PATCH /api/me` | Colaborador | Muda o próprio nome. |
| `GET`, `POST /api/users` | Admin | Lista e cria colaboradores. |
| `GET /api/users/{id}` | Logado | Mostra um colaborador. |
| `PUT`, `DELETE /api/users/{id}` | Admin | Altera e desativa colaborador. |
| `GET /api/pacientes` | Atendente e admin | Lista com `busca`, `ordem` (`recentes`, `antigos`, `alfabetica`), `status[]` (`ativos`, `inativos`) e `page`. |
| `POST /api/pacientes` | Atendente e admin | Cria a paciente e, se vierem, os `dados_pessoais`, numa transação. |
| `GET`, `PUT /api/pacientes/{id}` | Atendente e admin | Mostra e altera, inclusive uma paciente oculta. |
| `DELETE /api/pacientes/{id}` | Atendente e admin | Oculta a paciente e apaga os tokens dela. |
| `POST /api/pacientes/{id}/reativar` | Atendente e admin | Desfaz a ocultação. |
| `POST /api/pacientes/{id}/excluir` | Atendente e admin | Exclui: marca `hard_deleted`, oculta e apaga os tokens. |
| `/api/pacientes/{id}/dados-pessoais` | Atendente e admin | `GET`, `POST`, `PUT`/`PATCH` e `DELETE` dos dados pessoais, em separado. |

## Frontend

Cada tela segue o mesmo formato:

1. A view Blade monta o HTML com o conteúdo escondido (`#app-conteudo`) e um
   indicador de carregamento (`#carregando`).
2. A view declara quem pode vê-la, no `<body>`: `data-tipo` (`colaborador` ou
   `paciente`) e, se precisar, `data-cargos`, por exemplo
   `secretario,admin`.
3. O módulo da tela, em `resources/js/pages/`, chama `await protegerPagina()`
   e só continua se receber o usuário.
4. Toda chamada à API passa por `api()`, que trata 401, 403 do primeiro acesso
   e 422 de validação. Os erros de validação aparecem embaixo de cada campo, nos
   elementos `data-erro="<campo>"`.

As cores e as fontes ficam no `@theme` do `resources/css/app.css`. Por exemplo,
`bg-pap` é o verde-água `#009A94` e `bg-rosa` é o rosa do login da paciente. Os
títulos usam Montserrat e o texto usa Hind, carregadas pela diretiva `@fonts`.

## Testes

| Arquivo | O que cobre |
|---|---|
| `AuthControllerTest.php` | Login, primeiro acesso e troca de senha. |
| `MeControllerTest.php` | `/me`, `/logout`, colaborador inativo e mensagens em português. |
| `PacienteControllerTest.php` | Cadastro, listagem e exclusão básicos. |
| `PacienteEstadoTest.php` | Ocultar, reativar, excluir e unicidade de CPF e e-mail. |
| `PacienteListagemTest.php` | Busca, ordenação e filtro de status. |
| `PacienteCadastroTest.php` | Cadastro com dados pessoais, CPF e listas fixas. |
| `DadosPessoaisControllerTest.php` | Rotas próprias dos dados pessoais. |
| `TelasWebTest.php` | Cada tela abre e declara quem pode vê-la. |

As funções de apoio `entrarComo()` e `novaPaciente()` ficam em
`tests/Pest.php`.

## Onde estão as decisões

- O design e o plano das telas estão em `docs/superpowers/specs/` e
  `docs/superpowers/plans/`.
- As escolhas feitas na implementação estão marcadas no código. Para listá-las,
  execute `grep -rn "DECISAO:" app resources`.
- O `GUIA.md`, na raiz, explica linha a linha os primeiros arquivos da API para
  quem está começando em PHP. Ele foi escrito antes das telas e do PostgreSQL.
