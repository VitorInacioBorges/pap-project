# Telas Blade do Projeto PAP — Plano de implementação

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Telas Blade do design (login, início, menu, configurações, pacientes e portal da paciente) ligadas à API por token, com os ajustes de API que elas exigem.

**Architecture:** Cada tela é um `Route::view` sem autenticação no servidor. Um módulo JS por tela chama `/api/...` através de `resources/js/api.js`, que guarda o token, trata 401, 403 e 422 e protege as telas. A API ganha `/me`, `/logout`, filtros na lista, estados de paciente (ocultar, reativar, excluir) e cadastro com dados pessoais numa transação.

**Tech Stack:** Laravel 13, PHP 8.3+, Sanctum, Pest, PostgreSQL (SQLite em memória nos testes), Blade, Tailwind 4, Vite 8 e JavaScript puro em módulos ES.

**Spec:** `docs/superpowers/specs/2026-11-13-telas-blade-design.md`

## Global Constraints

- Nenhum framework de frontend novo (React, Vue, Alpine): JS puro, um módulo por tela em `resources/js/pages/`, cada um declarado em `input` do `vite.config.js`.
- Todo texto vindo da API entra no DOM por `textContent` ou por atributos, nunca por `innerHTML`.
- Rotas novas de API ficam dentro do grupo `clear_sanctum` em `routes/api.php` e respondem JSON.
- As rotas de pacientes usam `cargo:secretario,admin`.
- Cores (tokens no `@theme` de `resources/css/app.css`): `--color-pap` `#009A94`, `--color-pap-fundo` `#E6F4F4`, `--color-pap-cartao` `#76C9C6`, `--color-pap-menu` `#7BBDC1`, `--color-tinta` `#1B1B18`, `--color-rosa` `#FF2BA0`, `--color-rosa-fundo` `#F7E1EE`.
- Rótulos de cargo: `secretario` → "Atendente", `medico` → "Médico(a)", `enfermeiro` → "Enfermeiro(a)", `admin` → "Administrador(a)".
- Estado civil: Solteira, Casada, União estável, Divorciada, Viúva. Escolaridade: Não escolarizada, Fundamental incompleto, Fundamental completo, Médio incompleto, Médio completo, Superior incompleto, Superior completo, Pós-graduação.
- Cada escolha da seção 11 do spec é marcada no ponto onde foi aplicada com `// DECISAO: <o que> — escolhido: <o quê>; alternativas: <outras>` (`{{-- DECISAO: … --}}` no Blade, `/* DECISAO: … */` no CSS).
- Alvos clicáveis com pelo menos 44px de altura, foco visível, `label` visível em cada campo e resposta visual ao passar o mouse e ao clicar.
- Os testes que já existem continuam passando. Um teste só tem os dados atualizados onde o contrato mudou de propósito (CPF gravado com máscara).

## Review Focus

1. **Nome com HTML** (`<img src=x onerror=alert(1)>`) cadastrado como paciente: a lista, a janela de edição e o menu mostram o texto literal e não executam nada. Teste na Task 8 (passo de navegador).
2. **Token inválido no meio do uso** (logout em outra aba ou token apagado no banco): a próxima chamada recebe 401, o token local é apagado e a tela vai para `/login`, sem tela quebrada. Teste na Task 5 (`TelasWebTest`, com a checagem no navegador da Task 10).
3. **Duplo clique em Salvar** na janela de paciente ou no login: só uma requisição sai, porque o botão fica desabilitado enquanto envia. Passo de navegador na Task 8.
4. **CPF buscado em outro formato** (`12345678901` contra `123.456.789-01` gravado): a busca encontra nos dois formatos. Teste na Task 3.
5. **Colaborador sem o cargo** (médico) abrindo `/pacientes` ou paciente abrindo `/inicio`: nenhum dado aparece. O médico vê a mensagem de permissão, e a paciente vai para `/paciente`. Passo de navegador na Task 10.

---

### Task 1: API de sessão — `nome`, `/me`, `/logout` e login de inativo

**Files:**
- Create: `database/migrations/2026_11_15_000001_add_nome_to_users_table.php`
- Create: `app/Http/Controllers/MeController.php`
- Modify: `app/Models/User.php` (`$fillable` ganha `nome`)
- Modify: `app/Services/AuthService.php` (`autenticar` recusa `User` com `ativo = false`)
- Modify: `app/Http/Controllers/UserController.php` (`criaColaborador` aceita `nome` opcional: `['sometimes','nullable','string','max:255']`)
- Modify: `app/Services/colaboradorService.php` (`cria` grava `nome` quando vier)
- Modify: `routes/api.php`
- Test: `tests/Feature/MeControllerTest.php`

**Interfaces:**
- Produces:
  - `GET /api/me` (middlewares `auth:sanctum`, `ativo`):
    - colaborador: `{tipo:'colaborador', nome:string|null, email, cargo, alias, first_login:bool}`;
    - paciente: `{tipo:'paciente', nome:string|null (de dados_pessoais), email, first_login:bool}`.
  - `PATCH /api/me` (`auth:sanctum`, `ativo`, `primeiro_login`): body `{nome}`, obrigatório, string, até 255. Devolve o mesmo formato do GET. Paciente recebe 403.
  - `POST /api/logout` (`auth:sanctum`): apaga o token atual e devolve 204.
  - `MeController::show(Request): JsonResponse`, `MeController::update(Request): JsonResponse`, `MeController::logout(Request): Response`.

- [ ] **Step 1: Write the failing tests** in `tests/Feature/MeControllerTest.php` (`uses(RefreshDatabase::class)`; cria usuários como em `PacienteControllerTest`):
  - `it('devolve o colaborador logado em /me')`: um secretário com `nome` 'Fulana de Tal' e `first_login` true, via `Sanctum::actingAs`. `getJson('/api/me')` dá 200 com `tipo` 'colaborador', `nome` 'Fulana de Tal', `cargo` 'secretario', `alias` e `first_login` true. Precisa funcionar mesmo com `first_login` true.
  - `it('devolve a paciente logada em /me com o nome dos dados pessoais')`: paciente com `dados_pessoais.nome` 'Maria Souza'. Espera `tipo` 'paciente' e `nome` 'Maria Souza'.
  - `it('atualiza o nome do colaborador')`: `patchJson('/api/me', ['nome' => 'Fulana Atualizada'])` dá 200 e `nome` 'Fulana Atualizada', e o banco tem a mudança.
  - `it('exige nome ao atualizar /me')`: `patchJson('/api/me', ['nome' => ''])` dá 422 com erro em `nome`.
  - `it('recusa PATCH /me para paciente')`: paciente com `first_login` false dá 403.
  - `it('encerra a sessao em /logout')`: faz login real por `postJson('/api/login', ['alias'=>…, 'senha'=>…])`, chama `postJson('/api/logout')` com `Authorization: Bearer <token>` e espera 204. Depois, `personal_access_tokens` fica com 0 linhas.
  - `it('recusa login de colaborador inativo')`: usuário `ativo` false e senha conhecida. `postJson('/api/login', …)` dá 401.
  - `it('exige autenticacao em /me')`: `getJson('/api/me')` sem token dá 401.

- [ ] **Step 2: Run** `php artisan test --compact tests/Feature/MeControllerTest.php`. Expected: FAIL (rotas não existem).

- [ ] **Step 3: Implement.**
  - Migration: `string('nome')->nullable()->after('id_usuario')`.
  - `MeController`: monta a resposta num método privado `formatar(User|Paciente $u): array`.
  - `logout`: apaga `currentAccessToken()` só quando ele for `Laravel\Sanctum\PersonalAccessToken` (o `Sanctum::actingAs` dos testes usa `TransientToken`).
  - Rotas:
    - `GET /me` e `POST /logout` fora do grupo `primeiro_login`;
    - `PATCH /me` dentro dele.

- [ ] **Step 4: Run** `php artisan test --compact`. Expected: tudo PASS, os testes antigos inclusive.

- [ ] **Step 5: Commit** `feat: adiciona /me, /logout e nome do colaborador na API`.

### Task 2: API — estados da paciente (ocultar, reativar, excluir) e unicidade

**Files:**
- Create: `database/migrations/2026_11_15_000002_add_hard_deleted_to_pacientes_table.php`
- Modify: `app/Models/Paciente.php`
- Modify: `app/Services/PacienteService.php`
- Modify: `app/Http/Controllers/PacienteController.php`
- Modify: `routes/api.php`
- Test: `tests/Feature/PacienteEstadoTest.php`

**Interfaces:**
- Produces:
  - `Paciente::resolveRouteBindingQuery()` sempre filtra `hard_deleted = false`. Uma paciente excluída dá 404 em toda rota.
  - `show`, `update`, `reativar` e `excluir` acham pacientes ocultadas (`withTrashed`).
  - `POST /api/pacientes/{paciente}/reativar` → 200 com a paciente. `POST /api/pacientes/{paciente}/excluir` → 204.
  - `DELETE /api/pacientes/{paciente}` oculta e apaga os tokens da paciente.
  - `PacienteService::reativar(Paciente): Paciente`, `PacienteService::excluirDefinitivo(Paciente): void`. `excluir` passa a apagar os tokens.
  - Todas as rotas de pacientes e de dados pessoais usam `cargo:secretario,admin`.

- [ ] **Step 1: Write the failing tests** in `tests/Feature/PacienteEstadoTest.php`:
  - `it('permite que admin gerencie pacientes')`: admin faz `getJson('/api/pacientes')` e recebe 200.
  - `it('oculta e reativa uma paciente')`: `DELETE` dá 204 e `deleted_at` fica preenchido. `postJson(".../reativar")` dá 200 e `deleted_at` volta a null.
  - `it('consulta uma paciente oculta')`: depois do `DELETE`, `getJson("/api/pacientes/{id}")` dá 200.
  - `it('exclui definitivamente uma paciente')`: `postJson(".../excluir")` dá 204. O banco tem `hard_deleted` true. `getJson("/api/pacientes/{id}")` dá 404, e `postJson(".../reativar")` também.
  - `it('libera cpf e email de paciente excluida para novo cadastro')`: exclui a paciente com `cpf` '123.456.789-01' e o e-mail dela. `postJson('/api/pacientes', …)` com os mesmos dados dá 201.
  - `it('bloqueia cpf de paciente apenas oculta')`: oculta a paciente e cadastra o mesmo CPF. Dá 422 com erro em `cpf`.
  - `it('recusa login de paciente oculta')`: oculta a paciente. `postJson('/api/login', ['email'=>…, 'senha'=>…])` dá 401.

- [ ] **Step 2: Run** `php artisan test --compact tests/Feature/PacienteEstadoTest.php`. Expected: FAIL.

- [ ] **Step 3: Implement.**
  - Migration `up`:
    - `boolean('hard_deleted')->default(false)`;
    - `dropUnique(['cpf'])` e `dropUnique(['email'])`;
    - `DB::statement('CREATE UNIQUE INDEX pacientes_cpf_ativo_unique ON pacientes (cpf) WHERE NOT hard_deleted')` e o mesmo para `email`. `WHERE NOT hard_deleted` funciona em PostgreSQL e SQLite.
  - Migration `down`: desfaz na ordem inversa.
  - Validação de unicidade em `store` e `update`: `Rule::unique('pacientes', 'cpf')->where('hard_deleted', false)`, com `->ignore($paciente->id_paciente, 'id_paciente')` no update. O mesmo para `email`.
  - Rotas:
    - `apiResource(...)->withTrashed(['show','update'])`;
    - rotas `reativar` e `excluir` com `->withTrashed()`.

- [ ] **Step 4: Run** `php artisan test --compact`. Expected: tudo PASS.

- [ ] **Step 5: Commit** `feat: adiciona ocultar, reativar e excluir paciente na API`.

### Task 3: API — busca, ordenação e filtro de status na lista

**Files:**
- Modify: `app/Services/PacienteService.php` (`listar`)
- Modify: `app/Http/Controllers/PacienteController.php` (`index` valida e repassa os filtros)
- Modify: `app/Models/Paciente.php` (atributo calculado `ativo` em `$appends`)
- Test: `tests/Feature/PacienteListagemTest.php`

**Interfaces:**
- Consumes: `hard_deleted` (Task 2).
- Produces:
  - `PacienteService::listar(?string $busca, string $ordem = 'recentes', array $status = ['ativos']): LengthAwarePaginator`. Pagina com 15 itens e carrega `dadosPessoais`.
  - Validação do `index`:
    - `busca`: `nullable|string|max:100`;
    - `ordem`: `in:recentes,antigos,alfabetica`;
    - `status`: `array`, com `status.*` em `ativos,inativos`.
  - Cada item tem `ativo:bool`.

- [ ] **Step 1: Write the failing tests** in `tests/Feature/PacienteListagemTest.php`. Use um helper local `criarPaciente(string $nome, string $cpf, ?string $criadoEm = null)`, que cria a paciente e os dados pessoais.
  - `it('lista so ativas por padrao e marca o status')`: duas pacientes, uma delas oculta. `getJson('/api/pacientes')` traz 1 item, com `data.0.ativo` true.
  - `it('filtra inativas e todas pelo status')`: `?status[]=inativos` traz 1 item com `ativo` false. `?status[]=ativos&status[]=inativos` traz 2 itens.
  - `it('nunca lista pacientes excluidas')`: uma excluída (`hard_deleted` true e oculta). `?status[]=ativos&status[]=inativos` traz 0 itens.
  - `it('busca por nome sem diferenciar maiusculas')`: 'Marlene Aparecida' e 'Rosane Moreira'. `?busca=marlene` traz só Marlene.
  - `it('busca cpf com ou sem pontuacao')`: CPF gravado '123.456.789-01'. `?busca=12345678901` e `?busca=123.456.789-01` acham a paciente.
  - `it('ordena por nome com sem nome no fim')`: 'Bruna', 'Ana' e uma paciente sem dados pessoais. `?ordem=alfabetica` traz Ana, Bruna e, por fim, a sem nome.
  - `it('ordena por mais antigos')`: criadas em datas diferentes. `?ordem=antigos` traz a mais antiga primeiro.
  - `it('valida o parametro ordem')`: `?ordem=xpto` dá 422.

- [ ] **Step 2: Run** `php artisan test --compact tests/Feature/PacienteListagemTest.php`. Expected: FAIL.

- [ ] **Step 3: Implement.**
  - Sempre filtra `where('pacientes.hard_deleted', false)`.
  - Status: `ativos` usa o escopo padrão, `inativos` usa `onlyTrashed()` e os dois juntos usam `withTrashed()`.
  - Busca por nome: `whereHas('dadosPessoais', lower(nome) like ?)` com o termo em `mb_strtolower`.
  - Busca por CPF: só quando o termo tem dígitos. Compara `replace(replace(cpf,'.',''),'-','') like %dígitos%`.
  - Ordem alfabética: `leftJoin` com `dados_pessoais`, `select pacientes.*` e `orderByRaw('CASE WHEN dados_pessoais.nome IS NULL THEN 1 ELSE 0 END, lower(dados_pessoais.nome)')`.

- [ ] **Step 4: Run** `php artisan test --compact`. Expected: tudo PASS.

- [ ] **Step 5: Commit** `feat: adiciona busca, ordenacao e filtro de status na lista de pacientes`.

### Task 4: API — cadastro e edição com dados pessoais, CPF e listas fixas

**Files:**
- Create: `app/Support/OpcoesPaciente.php`
- Modify: `app/Http/Controllers/PacienteController.php` (`store`, `update`)
- Modify: `app/Http/Controllers/DadosPessoaisController.php` (`rules()` passa a ser `public static`, com `in:` nas listas)
- Modify: `app/Services/PacienteService.php` (`criar`, `atualizar`)
- Modify: `tests/Feature/PacienteControllerTest.php` (valor gravado do CPF passa a ser `'123.456.789-01'`)
- Test: `tests/Feature/PacienteCadastroTest.php`

**Interfaces:**
- Consumes: regras de unicidade da Task 2.
- Produces:
  - `OpcoesPaciente::ESTADOS_CIVIS` e `OpcoesPaciente::ESCOLARIDADES`, como `list<string>` com os valores das Global Constraints.
  - `OpcoesPaciente::formatarCpf(string): string`: 11 dígitos viram `000.000.000-00`.
  - `DadosPessoaisController::rules(bool $sometimes = false): array` é `public static`.
  - `store` e `update` aceitam `dados_pessoais` opcional, validado com as `rules()` prefixadas por `dados_pessoais.`.
  - CPF aceito no formato `/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/` e normalizado antes da regra `unique` (em `$request->merge`).
  - `PacienteService::criar(array $dados, ?array $dadosPessoais = null): Paciente` e `PacienteService::atualizar(Paciente, array $dados, ?array $dadosPessoais = null): Paciente`. Os dois rodam em `DB::transaction` e devolvem a paciente com `dadosPessoais` carregado. Na edição, os dados pessoais são criados ou atualizados (`updateOrCreate`).

- [ ] **Step 1: Write the failing tests** in `tests/Feature/PacienteCadastroTest.php`:
  - `it('cria paciente com dados pessoais numa so requisicao')`: secretário faz `postJson('/api/pacientes', [... 'dados_pessoais' => ['nome'=>'Marlene Souza','data_nascimento'=>'1989-07-06','estado_civil'=>'Casada','escolaridade'=>'Médio completo']])`. Dá 201 e `dados_pessoais.nome` 'Marlene Souza'. O banco tem as duas linhas.
  - `it('nao grava nada se os dados pessoais forem invalidos')`: `dados_pessoais.data_nascimento` ausente dá 422 com erro em `dados_pessoais.data_nascimento`. `pacientes` fica com 0 linhas.
  - `it('grava o cpf sempre com mascara')`: envia `'12345678901'`. O banco tem `'123.456.789-01'`.
  - `it('recusa cpf em formato invalido')`: `'123.456'` dá 422 em `cpf`.
  - `it('recusa o mesmo cpf em outro formato')`: com '123.456.789-01' já cadastrado, enviar '12345678901' dá 422 em `cpf`.
  - `it('recusa estado civil fora da lista')`: `'Noiva'` dá 422 em `dados_pessoais.estado_civil`.
  - `it('edita a paciente criando os dados pessoais que faltavam')`: paciente sem dados pessoais. `putJson` com `dados_pessoais.nome` e `data_nascimento` dá 200, e o banco tem a linha.
  - `it('edita a paciente sem trocar a senha quando ela nao vem')`: `putJson` sem `senha`. O `Hash::check` com a senha antiga continua true.

- [ ] **Step 2: Run** `php artisan test --compact tests/Feature/PacienteCadastroTest.php`. Expected: FAIL.

- [ ] **Step 3: Implement** conforme as Interfaces.
  - Marque a escolha do CPF com `// DECISAO: formato do CPF gravado — escolhido: sempre com máscara, aceitando 11 dígitos ou mascarado; alternativas: gravar só dígitos`.
  - Marque as listas com `// DECISAO: opções de estado civil e escolaridade — escolhido: lista fixa validada com in: na API e repetida no Blade; alternativas: lista só no frontend`.
  - Em `PacienteControllerTest`, troque o `cpf` esperado no `assertDatabaseHas` para `'123.456.789-01'`.

- [ ] **Step 4: Run** `php artisan test --compact`. Expected: tudo PASS.

- [ ] **Step 5: Commit** `feat: cadastra paciente com dados pessoais e normaliza CPF`.

### Task 5: Base do frontend — tokens, fonte, `api.js`, rotas web e layout

**Files:**
- Modify: `resources/css/app.css` (tokens das Global Constraints, `--font-sans` Hind)
- Modify: `vite.config.js` (fonte `bunny('Hind', {weights:[400,500,600]})`, mais `bunny('Montserrat', {weights:[600,700]})`, e cada `resources/js/pages/*.js` em `input`)
- Create: `resources/js/api.js`
- Create: `resources/js/layout.js`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `routes/web.php`
- Delete: `resources/views/patients/create.blade.php`
- Test: `tests/Feature/TelasWebTest.php`

**Interfaces:**
- Produces (exports de `resources/js/api.js`):
  - `getToken(): string|null`, `setToken(token: string): void` e `limparSessao(): void`. Guardam em `sessionStorage`, nas chaves `pap.token` e `pap.usuario`.
  - `api(caminho: string, { method = 'GET', body } = {}): Promise<any>`. Prefixa `/api`, manda `Accept` e `Content-Type: application/json` e `Authorization` quando há token, e devolve o JSON (`null` em 204). Erros:
    - **401:** chama `limparSessao()`, vai para `/login` e devolve uma promise que nunca resolve.
    - **403 com `first_login`:** vai para `paginaInicial({...usuarioEmCache, first_login: true})`.
    - **422:** lança `ErroValidacao`, com `.erros` (objeto campo → lista de mensagens).
    - **Outro status ≥ 400:** lança `ErroApi`, com `.status` e `.mensagem`.
  - `class ErroValidacao extends Error { erros }` e `class ErroApi extends Error { status; mensagem }`.
  - `carregarUsuario(): Promise<Usuario>`: `GET /me`, guardado em `pap.usuario`.
  - `paginaInicial(usuario): string`:
    - colaborador com `first_login`: `/configuracoes?primeiro-acesso=1`;
    - colaborador: `/inicio`;
    - paciente: `/paciente`.
  - `rotuloCargo(cargo: string): string`, com os rótulos das Global Constraints.
  - `sair(): Promise<void>`: `POST /logout`, ignora o erro, chama `limparSessao()` e vai para `/login`.
  - `protegerPagina(): Promise<Usuario|null>`: lê `data-tipo` e `data-cargos` do `<body>`.
    - Sem token: vai para `/login`.
    - Tipo diferente: vai para `paginaInicial(usuario)`.
    - Cargo fora da lista: mostra o bloco `#sem-permissao` e devolve `null`.
    - Tudo certo: revela o `#app-conteudo`, tirando `hidden` e escondendo `#carregando`, e devolve o usuário.
  - `desabilitarEnquanto(botao: HTMLButtonElement, promessa: Promise): Promise`: desabilita o botão durante a promessa.
  - `layout.js` (importado pelo `app.js`): abre e fecha o menu, como o script atual do layout. Também preenche `[data-usuario-nome]` e `[data-usuario-cargo]` por `textContent` (sem nome, mostra o alias). Esconde `[data-so-cargos]` cujo cargo não confere e liga `[data-acao="sair"]` a `sair()`.
- Rotas web:
  - `/` → redirect para `/login`;
  - `/login` → `auth.login` (`login`);
  - `/inicio` → `dashboard.index` (`inicio`);
  - `/pacientes` → `patients.index` (`pacientes`);
  - `/configuracoes` → `profile.edit` (`configuracoes`);
  - `/paciente` → `portal.index` (`paciente`).
- Layout `layouts/app.blade.php`:
  - sem `auth()` e sem `<form>` de logout;
  - `<body data-tipo="@yield('tipo','colaborador')" data-cargos="@yield('cargos','')">`;
  - `#carregando` visível;
  - `<div id="app-conteudo" hidden>@yield('content')</div>`;
  - `#sem-permissao` com o texto "Você não tem permissão para esta tela." e um link "Voltar ao início" para `/inicio`;
  - `@vite(['resources/css/app.css','resources/js/app.js', ...$paginaJs])` via `@stack('vite')`.
  - Item de menu ativo por `request()->routeIs(...)`. "Pacientes" leva `data-so-cargos="secretario,admin"`.

- [ ] **Step 1: Write the failing test** `tests/Feature/TelasWebTest.php`:
  - `it('abre cada tela sem autenticacao no servidor')`: para `['/login','/inicio','/pacientes','/configuracoes','/paciente']`, `get($url)` dá 200.
  - Nas telas com layout, a resposta tem `id="app-conteudo"`. A de `/pacientes` tem `data-cargos="secretario,admin"`.

- [ ] **Step 2: Run** `php artisan test --compact tests/Feature/TelasWebTest.php`. Expected: FAIL (rotas novas não existem).

- [ ] **Step 3: Implement** conforme as Interfaces.
  - Crie `resources/views/portal/index.blade.php` e os `resources/js/pages/*.js` vazios, para o build passar. As Tasks 6 a 9 os preenchem.
  - Marque o armazenamento com `// DECISAO: onde guardar o token — escolhido: sessionStorage (fechar a aba desloga; computadores do ambulatório são compartilhados); alternativas: localStorage`.
  - Marque a fonte com `/* DECISAO: fonte do texto corrido — escolhido: Hind via bunny no vite.config.js; alternativas: Instrument Sans já configurada */`.

- [ ] **Step 4: Run** `php artisan test --compact` e `npm run build`. Expected: testes PASS e build sem erro.

- [ ] **Step 5: Commit** `feat: base do frontend com cliente da API, rotas web e layout`.

### Task 6: Tela de login

**Files:**
- Modify: `resources/views/auth/login.blade.php` (reescrita, sem layout)
- Create: `resources/js/pages/login.js`

**Interfaces:**
- Consumes: `api`, `setToken`, `getToken`, `carregarUsuario`, `paginaInicial`, `ErroValidacao`, `ErroApi`, `desabilitarEnquanto` (Task 5).

- [ ] **Step 1: Build the view** como na captura do login:
  - logos `pap-fullogo.svg` e `uepg.svg`;
  - "Faça seu Login" e o subtítulo do ambulatório;
  - abas com `role="tablist"` e `aria-selected`: **Sou Paciente**, que vem ativa, e **Sou do ProjetoPAP**.
  - A aba muda o tema do cartão (`data-tema="rosa|pap"` no `<main>`), o rótulo e o tipo do campo:
    - paciente: "E-mail", `type=email`;
    - colaborador: "Identificação", `type=text`, placeholder `MD-xxxxxxxx`.
  - Fundo da página: rosa (`rosa-fundo`) na aba paciente e `pap-fundo` na aba colaborador.
  - Campos "Senha" e botão "Entrar".
  - Só na aba paciente: o botão "Não sei qual é meu login", que mostra e esconde o texto do spec 11.7. Marque com `{{-- DECISAO: link "Não sei qual é meu login" — escolhido: texto de ajuda na própria tela; alternativas: tela de recuperação por e-mail --}}`.
  - Sem link "Cadastre-se".
  - Uma área `role="alert"` para as mensagens.

- [ ] **Step 2: Implement `login.js`.**
  - Se já há token, chama `carregarUsuario()` e redireciona para `paginaInicial`.
  - No envio, faz `POST /login` com `{email, senha}` ou `{alias, senha}`, conforme a aba. Em seguida, `setToken`, `carregarUsuario()` e redireciona.
  - Erros:
    - 401: "Credenciais inválidas.";
    - 429: "Muitas tentativas. Aguarde um minuto e tente de novo.";
    - 422: a mensagem embaixo do campo.
  - O botão fica desabilitado durante o envio.

- [ ] **Step 3: Verify** com `npm run build` e `php artisan serve`. Faça o login do admin pelo navegador (Task 10 automatiza): vai para `/configuracoes?primeiro-acesso=1` quando `first_login` é true.

- [ ] **Step 4: Commit** `feat: tela de login ligada a API`.

### Task 7: Início e configurações (com primeiro acesso)

**Files:**
- Modify: `resources/views/dashboard/index.blade.php`
- Modify: `resources/views/profile/edit.blade.php`
- Create: `resources/js/pages/inicio.js`
- Create: `resources/js/pages/configuracoes.js`

**Interfaces:**
- Consumes: `protegerPagina`, `api`, `setToken`, `rotuloCargo`, `ErroValidacao`, `desabilitarEnquanto` (Task 5); `PATCH /api/me` (Task 1); `POST /api/alterar-senha` (já existe: `senha_atual`, `nova_senha`, `nova_senha_confirmation`, e devolve `token`).

- [ ] **Step 1: Início** como na captura:
  - ícone `user-icon.svg`;
  - "Olá, <span data-cargo-rotulo></span> <span data-usuario-nome></span>!" e "Escolha o que você quer fazer.";
  - cartão **Pacientes** (`data-so-cargos="secretario,admin"`, link `/pacientes`, ícone `patients-icon.svg`) e cartão **Configurações da conta** (link `/configuracoes`, ícone `settings-icon.svg`);
  - cartões com elevação de 2px e mudança de fundo para `pap-cartao` ao passar o mouse.
  - `inicio.js`: `await protegerPagina()` e preenche o rótulo do cargo.

- [ ] **Step 2: Configurações** como na captura:
  - "Nome Completo", editável;
  - "Cargo" e "Identificação", `readonly`;
  - "Senha atual", "Nova Senha" e "Confirmar Nova Senha";
  - "Salvar Alterações";
  - um aviso `#aviso-primeiro-acesso` ("Troque a senha temporária para continuar."), visível com `?primeiro-acesso=1` ou quando `usuario.first_login`.
  - A versão de celular empilha os campos, como na captura.

- [ ] **Step 3: Implement `configuracoes.js`.**
  - Preenche os campos a partir do usuário.
  - **Salvar, sem primeiro acesso:**
    - se o nome mudou, `PATCH /me`;
    - depois, se a nova senha foi preenchida, `POST /alterar-senha`, seguido de `setToken(resposta.token)` e da limpeza dos campos de senha.
  - **Salvar, no primeiro acesso:** a senha é obrigatória e vai primeiro. Depois vem o `PATCH /me` do nome, se ele mudou. No fim, redireciona para `/inicio`.
  - Erros 422 aparecem embaixo de cada campo (`senha_atual`, `nova_senha`, `nome`). Sucesso mostra "Alterações salvas." numa área `role="status"`.

- [ ] **Step 4: Commit** `feat: telas de inicio e configuracoes ligadas a API`.

### Task 8: Lista de pacientes e janela de nova paciente e edição

**Files:**
- Modify: `resources/views/patients/index.blade.php` (reescrita)
- Create: `resources/views/patients/_formulario.blade.php`
- Create: `resources/js/pages/pacientes.js`

**Interfaces:**
- Consumes:
  - de `api.js` (Task 5): `protegerPagina`, `api`, `ErroValidacao`, `ErroApi`, `desabilitarEnquanto`;
  - da API: `GET /api/pacientes?busca&ordem&status[]&page` (Task 3), `POST` e `PUT /api/pacientes` com `dados_pessoais` (Task 4), `DELETE`, `POST …/reativar` e `POST …/excluir` (Task 2).
  - `@section('cargos','secretario,admin')`.

- [ ] **Step 1: Build the view** como nas capturas:
  - "Pacientes", "Cadastro, edição e ocultação de pacientes." e o botão "+ Nova Paciente";
  - busca com lupa (`type=search`, rótulo visível só para leitor de tela);
  - botão "Filtros", que abre um painel `role="dialog"` com o rádio "Ordenar por" e as caixas "Status", marcando "Ativos" por padrão.
  - Tabela:
    - colunas Nome, CPF, Nascimento, Status e Ações;
    - linhas vindas de um `<template id="linha-paciente">`;
    - no celular, uma coluna "Informações" empilhada.
  - Selo de status: Ativo em contorno verde e Inativo em contorno vermelho.
  - Menu "…" (`aria-haspopup="menu"`): Editar, Ocultar ou Reativar, e Excluir em vermelho.
  - Estados: `#lista-carregando`, `#lista-vazia` ("Nenhuma paciente encontrada.") e `#lista-erro` com o botão "Tentar de novo".
  - Botão "Carregar mais", marcado com `{{-- DECISAO: paginação — escolhido: botão Carregar mais; alternativas: paginação numerada --}}`.
  - `<dialog id="confirmar-exclusao">`: "Excluir {nome}? Ela some de todas as listas e não pode mais entrar no sistema.", com Cancelar e Excluir.

- [ ] **Step 2: Build `_formulario.blade.php`** dentro de `<dialog id="janela-paciente">`:
  - título "Nova Paciente" ou "Editar Paciente";
  - seções e campos do spec 8.2, com `name` iguais aos da API (`dados_pessoais[nome]` etc. no HTML, convertidos para objeto aninhado no JS);
  - selects de estado civil e escolaridade com as listas das Global Constraints e a primeira opção vazia ("Selecione");
  - máscara de CPF ao digitar;
  - `<p data-erro="<campo>">` embaixo de cada campo;
  - Cancelar (contorno vermelho) e Salvar.

- [ ] **Step 3: Implement `pacientes.js`.**
  - Estado: `{busca, ordem, status, pagina, itens}`.
  - Busca com espera de 300 ms, marcada com `// DECISAO: quando a busca dispara — escolhido: ao digitar com espera de 300 ms; alternativas: só no Enter ou na lupa`. Mudança de filtro recarrega da página 1.
  - Linhas montadas com `textContent`. Data em dd/mm/aaaa a partir de `data_nascimento` (`YYYY-MM-DD`), sem `new Date()` para não deslocar o fuso. Sem nome, mostra o e-mail.
  - Ações: Editar abre a janela preenchida pelo `GET /api/pacientes/{id}`. Ocultar, Reativar e Excluir (depois da confirmação) recarregam a lista e mostram uma mensagem curta em `role="status"`.
  - Salvar: monta `{email, cpf, senha?, dados_pessoais:{…}}` sem campos vazios e envia `POST` ou `PUT`. O 422 mostra cada mensagem no `data-erro` do campo (`dados_pessoais.nome` → `dados_pessoais[nome]`). O botão fica desabilitado durante o envio. No sucesso, fecha a janela, recarrega e mostra "Paciente salva.".
  - Fecha menus e painéis com Esc e com clique fora.

- [ ] **Step 4: Commit** `feat: lista de pacientes com filtros e janela de cadastro e edicao`.

### Task 9: Portal da paciente

**Files:**
- Modify: `resources/views/portal/index.blade.php`
- Create: `resources/js/pages/portal.js`

**Interfaces:**
- Consumes: `protegerPagina`, `api`, `setToken`, `sair`, `ErroValidacao`, `desabilitarEnquanto` (Task 5); `POST /api/pacientes/alterar-senha` (já existe).

- [ ] **Step 1: Build the view.**
  - Página sem o layout de colaborador, no visual rosa do login: `<body data-tipo="paciente">`, logos, "Olá, <span data-usuario-nome></span>!" (sem nome, só "Olá!") e "Em breve você verá seus atendimentos aqui.".
  - Botão "Sair" (`data-acao="sair"`).
  - Formulário `#troca-senha` (senha atual, nova senha e confirmação), escondido por padrão.
  - Os mesmos `#carregando` e `#app-conteudo` do layout.

- [ ] **Step 2: Implement `portal.js`.**
  - `protegerPagina()`.
  - Se `first_login`, mostra o formulário no lugar do texto.
  - No envio, `POST /pacientes/alterar-senha`, seguido de `setToken(resposta.token)`. Depois, esconde o formulário e mostra "Senha alterada." e o texto.

- [ ] **Step 3: Commit** `feat: portal da paciente com troca de senha do primeiro acesso`.

### Task 10: Verificação no navegador e documentação

**Files:**
- Modify: `COMO-RODAR.md` (seção "Primeiro acesso" passa a apontar para a tela `/login`, e não mais só para o `curl`)
- Scratchpad: script Playwright fora do projeto, apagado no fim.

- [ ] **Step 1:** Rode `php artisan test --compact` e `npm run build`. Expected: PASS e build sem erro.
- [ ] **Step 2:** Com `php artisan serve` numa porta livre, banco de teste local `pap_project` e Playwright num diretório do scratchpad, percorra em 1440×900 e 390×844:
  - login do admin, primeiro acesso, troca de senha e chegada ao `/inicio`;
  - criar paciente com nome `<img src=x onerror=alert(1)>`: o texto aparece literal e nenhum `dialog` dispara;
  - buscar por CPF sem pontuação;
  - ocultar, filtrar inativas, reativar, editar e excluir;
  - duplo clique em Salvar gera um só cadastro;
  - logout;
  - login da paciente e troca de senha no portal;
  - paciente abrindo `/inicio` vai para `/paciente`;
  - médico abrindo `/pacientes` vê a mensagem de permissão;
  - token apagado no banco: a próxima ação volta ao `/login`.

  Tire capturas para comparar com o design.
- [ ] **Step 3:** Apague as pacientes e os usuários criados no teste do banco local. Recrie o admin com `first_login` true e a senha `admin12345`, como na documentação. Apague `~/.cache/ms-playwright`, os pacotes e a pasta do script.
- [ ] **Step 4:** Atualize `COMO-RODAR.md`.
- [ ] **Step 5: Commit** `docs: atualiza guia de execucao para as telas`.
