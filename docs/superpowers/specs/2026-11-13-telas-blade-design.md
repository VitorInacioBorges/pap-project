# Telas Blade do Projeto PAP ligadas à API

Data: 13 de novembro de 2026. Branch: `develop`.

## 1. Intenção

O Projeto PAP (Ambulatório de Saúde UEPG, Prevenção e Educação na Atenção à
Saúde da Mulher) tem uma API Laravel de colaboradores e pacientes, mas só tem
telas provisórias, que não salvam nada. Este trabalho faz as telas em Blade
seguindo o design do Figma e as liga à API. Onde a API não entrega o que a tela
precisa, a API é ajustada.

As telas usam a API de verdade, pelo navegador, com o token que o login já
devolve. Assim, os ajustes na API também servem para outros clientes no futuro,
como um app ou o portal da paciente.

## 2. Objetivos, não objetivos e anti-objetivos

**Objetivos**

- Login com duas abas: **Sou Paciente** (e-mail e senha, tema rosa) e
  **Sou do ProjetoPAP** (alias e senha, tema verde-água).
- Início, menu lateral e configurações de conta do colaborador.
- Lista de pacientes com busca, filtros e ações (editar, ocultar, excluir), e a
  janela de nova paciente e de edição.
- Versão de computador e de celular de cada tela, como no design.
- Ajustes na API para cada tela funcionar, com testes de feature.

**Não objetivos** (ficam para depois)

- Telas de anamnese e as tabelas `anamnese`, `local_anamnese`,
  `preparo_pre_exame`, `antecedentes_obstetricos`, `historico_ginecologico` e
  `historico_oncologico` do PDF.
- Portal da paciente depois do login. A paciente entra e vê uma página simples
  de "em construção" (seção 8).
- Cadastro de colaborador pela própria pessoa ("Faça seu cadastro") e a
  aprovação pelo admin. O link "Cadastre-se" não aparece.
- Tela de gestão de colaboradores pelo admin.
- Alinhar a tabela `users` ao PDF (`cpf`, `area_academico`).

**Anti-objetivos** (falha, mesmo que funcione)

- Tela que mostra dados falsos ou que diz "salvo" sem a API ter salvo.
- Tela que mostra dados de paciente a quem não está logado ou não tem o cargo
  certo, mesmo que por um instante antes de redirecionar.
- Quebrar os testes que já existem da API. Um teste pode ter os dados
  atualizados só quando o contrato mudou de propósito neste documento. O
  comportamento que ele testa continua valendo.

## 3. Restrições

- Laravel 13, PHP 8.3+, PostgreSQL, Tailwind 4 e Vite, que já estão no projeto.
  Nenhum framework de frontend novo (React, Vue, Alpine): JavaScript puro em
  módulos do Vite.
- Autenticação por token do Sanctum, como a API já faz. Rotas protegidas
  passam por `clear_sanctum`, `auth:sanctum`, `ativo` e `primeiro_login`.
- Rotas de pacientes exigem `cargo:secretario` hoje.
- O design só existe como capturas de tela, sem medidas exatas. As cores e o
  tamanho dos textos são lidos das imagens e dos Blade atuais, que já usam
  `#009A94` (verde-água), `#E6F4F4` (fundo) e Montserrat nos títulos.
- Os ícones e logos já estão em `public/images/`.

## 4. Abordagens

| | Como funciona | Ganha | Perde |
|---|---|---|---|
| **A. API com token (escolhida)** | O Blade monta o HTML. O JavaScript chama `/api/...` com o token do login, guardado no navegador. | Usa a API como ela já é. Os ajustes servem a qualquer cliente. | JavaScript em cada tela. O token fica no navegador. A tela precisa esperar a API antes de mostrar dados. |
| B. API com sessão (modo SPA do Sanctum) | O JavaScript chama `/api/...` com cookie de sessão. | Sem token guardado no navegador. | Briga com o login atual, que gera tokens, com o `clear_sanctum` e com os dois tipos de usuário. Refaz a autenticação. |
| C. Blade tradicional | Formulários enviam para rotas web, que usam os Services. | Menos JavaScript, erros automáticos, testes simples. | As telas não usam a API. Os ajustes na API viram trabalho à parte. |

O usuário escolheu **A**. É o único caminho que cumpre o pedido, que é usar a
API e ajustá-la, sem refazer a autenticação.

## 5. Decisões já tomadas

Tomadas pelo usuário:

- O login do colaborador continua pelo **alias** (`MD-…`, `EN-…`, `SE-…`,
  `AD-…`). A aba **Sou do ProjetoPAP** pede "Identificação" no lugar de CPF.
- O cadastro pela própria pessoa fica de fora.
- Abordagem A.

Propostas do assistente, apresentadas sem objeção do usuário. A aprovação deste
documento confirma cada uma:

- **Login da paciente por e-mail**, como a API já faz. A aba **Sou Paciente**
  pede "E-mail" no lugar de CPF.
- **Nome do colaborador:** nova coluna `nome` em `users`, que pode ficar vazia.
  Sem nome, o cabeçalho mostra o alias.
- **Rótulos de cargo:** `secretario` vira "Atendente", `medico` vira
  "Médico(a)", `enfermeiro` vira "Enfermeiro(a)" e `admin` vira
  "Administrador(a)". O banco não muda.
- **Ocultar:** exclusão suave (`deleted_at`), que já existe. A paciente aparece
  como **Inativa** e pode ser reativada.
- **Excluir:** nova coluna `hard_deleted` em `pacientes`, como no PDF. A
  paciente some de todas as listas e não pode mais entrar. O registro fica no
  banco.
- **Configurações:** a tela ganha o campo "Senha atual", que a API exige.
- **Lista:** a API ganha busca, ordenação e filtro de status.
- **Nova paciente e edição:** um único Salvar grava a paciente e os dados
  pessoais juntos.

## 6. Mudanças na API

Todas as rotas abaixo ficam em `routes/api.php`, dentro do grupo
`clear_sanctum`, e respondem em JSON.

### 6.1 Sessão do usuário

| Rota | Middlewares | O que faz |
|---|---|---|
| `GET /api/me` | `auth:sanctum`, `ativo` (sem `primeiro_login`) | Devolve quem está logado: `tipo` (`colaborador` ou `paciente`), `nome`, `email`, `first_login` e, para colaborador, `cargo` e `alias`. Para paciente, `nome` vem de `dados_pessoais`. |
| `PATCH /api/me` | `auth:sanctum`, `ativo`, `primeiro_login` | O colaborador atualiza o próprio `nome` (obrigatório, até 255 caracteres). Cargo e alias não mudam por aqui. Paciente recebe 403. |
| `POST /api/logout` | `auth:sanctum` | Apaga o token atual. Responde 204. |

`GET /api/me` fica fora de `primeiro_login` porque a tela precisa saber quem
está logado para mandá-lo trocar a senha.

### 6.2 Colaboradores

- Migration: `users.nome`, `string`, `nullable`.
- `nome` entra no `$fillable` de `User`.
- `POST /api/users` aceita `nome` opcional.
- `AuthService::autenticar` recusa colaborador com `ativo = false`, com a mesma
  resposta de credenciais inválidas (401). Sem isso, um inativo recebe token e
  fica preso entre telas que respondem 403. Decisão do assistente, a partir da
  revisão.

### 6.3 Pacientes

- Migration: `pacientes.hard_deleted`, `boolean`, padrão `false`.
- A mesma migration troca os índices únicos de `pacientes.cpf` e
  `pacientes.email` por índices parciais do PostgreSQL
  (`WHERE hard_deleted = false`). Assim, uma paciente excluída pode ser
  cadastrada de novo, e o registro antigo fica no banco como histórico. As
  regras `unique:pacientes,cpf` e `unique:pacientes,email` do `store` e do
  `update` passam a ignorar as excluídas. Uma paciente apenas ocultada continua
  bloqueando o CPF e o e-mail, porque pode ser reativada. Decisão do usuário.
- Os testes rodam em SQLite em memória, que também aceita índice parcial. A
  migration usa `DB::statement` com SQL que funciona nos dois bancos.
- O login da paciente (`AuthService::autenticar`) recusa paciente com
  `hard_deleted = true` ou ocultada (`deleted_at` preenchido).
- As rotas de pacientes passam a aceitar `cargo:secretario,admin`.

`GET /api/pacientes` ganha parâmetros:

| Parâmetro | Valores | Padrão | Efeito |
|---|---|---|---|
| `busca` | texto | vazio | Filtra por nome (em `dados_pessoais`, sem diferenciar maiúsculas) ou por CPF. Na comparação do CPF, pontos e traço são ignorados dos dois lados. |
| `ordem` | `recentes`, `antigos`, `alfabetica` | `recentes` | Ordena por `created_at` (decrescente ou crescente) ou por nome. |
| `status[]` | `ativos`, `inativos` | `ativos` | `ativos`: `deleted_at` vazio. `inativos`: ocultadas. Os dois juntos: todas. |
| `page` | número | 1 | Paginação que já existe, 15 por página. |

Pacientes com `hard_deleted = true` nunca aparecem. Cada item traz
`id_paciente`, `cpf`, `email`, `ativo` (calculado: `deleted_at` vazio) e
`dados_pessoais` com `nome` e `data_nascimento`.

`POST /api/pacientes` e `PUT /api/pacientes/{paciente}` aceitam um objeto
`dados_pessoais` com os campos de `DadosPessoaisController::rules()`. A paciente
e os dados pessoais são gravados na mesma transação. Se um falhar, nada é
gravado. As rotas `/pacientes/{paciente}/dados-pessoais` continuam existindo.

`dados_pessoais` continua opcional na API, para não quebrar quem já cria a
paciente sem ele. A janela da tela sempre envia esse objeto. Na edição, a API
cria os dados pessoais se a paciente ainda não tem e atualiza se já tem. Na
lista, uma paciente sem nome aparece com o e-mail no lugar do nome. Na ordem
alfabética, ela vai para o fim.

Novas rotas:

| Rota | O que faz |
|---|---|
| `DELETE /api/pacientes/{paciente}` | Já existe: oculta (exclusão suave). Também apaga os tokens da paciente. |
| `POST /api/pacientes/{paciente}/reativar` | Desfaz a ocultação. O vínculo da rota precisa achar pacientes ocultadas (`withTrashed`). |
| `POST /api/pacientes/{paciente}/excluir` | Marca `hard_deleted = true`, oculta e apaga os tokens. Responde 204. |

`GET /api/pacientes/{paciente}` precisa achar pacientes ocultadas, para a edição
de uma inativa funcionar. Pacientes com `hard_deleted` respondem 404 em todas as
rotas.

### 6.4 Testes da API

Testes de feature em Pest para cada rota nova ou alterada: o caminho que
funciona, o 401 sem token, o 403 sem o cargo e o 422 de validação. Os testes que
já existem continuam passando.

## 7. Rotas web

`routes/web.php` deixa de ter rotas provisórias. Cada tela vira um
`Route::view`, sem autenticação no servidor, porque quem protege os dados é a
API:

| Rota | View | Quem acessa |
|---|---|---|
| `/` | redireciona para `/login` | todos |
| `/login` | `auth.login` | todos |
| `/inicio` | `dashboard.index` | colaborador |
| `/pacientes` | `patients.index` | secretário ou admin |
| `/configuracoes` | `profile.edit` | colaborador |
| `/paciente` | `portal.index` | paciente |

As views `patients.create` e a rota `/patients/create` somem. Nova paciente vira
uma janela dentro de `/pacientes`, como no design.

## 8. Frontend

### 8.1 Estrutura

- `resources/js/api.js`: guarda e lê o token, monta cada chamada com
  `Accept: application/json` e `Authorization: Bearer …`, e trata as respostas
  comuns:
  - **401:** apaga o token e manda para `/login`.
  - **403 com `first_login: true`:** manda o colaborador para
    `/configuracoes?primeiro-acesso=1` e a paciente para `/paciente`.
  - **403 de cargo:** mostra "Você não tem permissão para esta tela" e um link
    para o Início.
  - **422:** devolve os erros por campo para a tela.
- Um módulo por tela em `resources/js/pages/`, carregado pelo `@vite` da view.
- `layouts/app.blade.php`: deixa de usar `auth()->user()`. Ao abrir, chama
  `GET /api/me` e preenche nome e cargo no menu lateral. O `<main>` fica escondido
  (com um indicador de carregamento) até `/api/me` responder. Assim, nenhuma tela
  mostra conteúdo a quem não está logado.
- **Sair:** chama `POST /api/logout`, apaga o token e vai para `/login`.

### 8.2 Telas

**Login (`/login`)**

- Abas como no design. A aba ativa muda as cores: rosa para paciente,
  verde-água para colaborador. Ao abrir, a aba **Sou Paciente** fica ativa, como
  no design.
- Paciente: campos "E-mail" e "Senha", e o link "Não sei qual é meu login".
- Colaborador: campos "Identificação" (placeholder `MD-xxxxxxxx`) e "Senha".
- Envia para `POST /api/login` com `email` ou `alias`. Mostra "Credenciais
  inválidas." no 401 e uma mensagem de espera no 429 (6 tentativas por minuto).
- Depois do login:
  - colaborador com `first_login`: vai para `/configuracoes?primeiro-acesso=1`;
  - colaborador: vai para `/inicio`;
  - paciente: vai para `/paciente`.
- Quem já tem token e abre `/login` é mandado para a sua página inicial.

**Início (`/inicio`)**

- "Olá, {cargo} {nome}!" (sem nome, mostra o alias), com o subtítulo "Escolha o
  que você quer fazer."
- Cartões **Pacientes** (só para secretário e admin) e **Configurações da conta**.

**Menu lateral**

- Abre pelo ícone de menu. Mostra foto genérica, nome, cargo, botão de sair e os
  itens Início, Pacientes (só para secretário e admin) e Configurações.
- O item da página atual fica destacado.
- No celular, ocupa a tela e tem o botão de fechar (X) embaixo, como no design.
- Fecha com Esc e com clique fora. O foco volta para o botão de menu.

**Configurações (`/configuracoes`)**

- Campos:
  - Nome completo: editável, salvo por `PATCH /api/me`.
  - Cargo: rótulo da seção 5, só leitura.
  - Identificação: alias, só leitura.
  - Senha atual, Nova senha e Confirmar nova senha.
- **Salvar Alterações:**
  - Salva o nome se ele mudou.
  - Troca a senha (`POST /api/alterar-senha`) se a nova senha foi preenchida.
    Nesse caso, guarda o token novo que a API devolve.
  - Se os dois mudaram, salva o nome primeiro.
- Com `?primeiro-acesso=1`, mostra um aviso no topo: "Troque a senha
  temporária para continuar." O menu leva de volta a esta tela até a troca. Na
  troca de primeiro acesso, o nome é salvo depois da senha, porque
  `PATCH /api/me` passa por `primeiro_login`.

**Pacientes (`/pacientes`)**

- Título, subtítulo e botão **+ Nova Paciente**.
- Busca por nome ou CPF e **Filtros** (Ordenar por: Mais recentes, Mais
  antigos, Ordem alfabética; Status: Ativos, Inativos), como no design.
- Tabela: Nome, CPF, Nascimento (dd/mm/aaaa), Status (Ativo verde, Inativo
  vermelho) e Ações (menu "…").
- No celular, a tabela vira linhas com Informações (nome, CPF, nascimento),
  Status e Ações, como no design.
- Menu "…":
  - **Editar:** abre a janela preenchida.
  - **Ocultar:** só para ativas. Vira **Reativar** para inativas.
  - **Excluir:** em vermelho. Pede confirmação antes.
- Estados: carregando, lista vazia ("Nenhuma paciente encontrada.") e erro da
  API com o botão "Tentar de novo".

**Janela Nova paciente e Editar paciente**

- Seções e campos como no design:
  - **Informações pessoais:** nome\*, CPF\*, data de nascimento\*, telefone,
    profissão, estado civil e escolaridade.
  - **Informações corporais:** peso, altura, pressão arterial e CA.
  - **Informações para login:** e-mail\* e senha\*. Na edição, a senha é
    opcional e só muda se for preenchida.
- **Cancelar** fecha sem salvar. **Salvar** envia tudo de uma vez (seção 6.3) e
  mostra cada erro da API embaixo do seu campo.
- Ao salvar, fecha a janela, recarrega a lista e mostra uma confirmação curta.
- Usa `<dialog>`: o foco fica dentro da janela e Esc fecha.

**Portal da paciente (`/paciente`)**

- Logos, "Olá, {nome}!", o texto "Em breve você verá seus atendimentos aqui." e
  o botão Sair. Não mostra dados clínicos.
- Toda paciente nasce com `first_login = true`. Nesse caso, a página mostra no
  lugar do texto um formulário curto com senha atual, nova senha e confirmação.
  O formulário usa a rota que já existe, `POST /api/pacientes/alterar-senha`,
  no visual rosa do login. O design não tem essa tela. Decisão do assistente, a
  partir da revisão: sem ela, nenhuma paciente conseguiria sair do primeiro
  acesso.
- Colaborador que abre `/paciente` vai para `/inicio`. Paciente que abre uma
  tela de colaborador vai para `/paciente`.

### 8.3 Visual

- Seguir as capturas do Figma. Cores do design:
  - verde-água `#009A94`;
  - fundo `#E6F4F4`;
  - cartão ativo `#76C9C6` (aproximado);
  - texto `#1B1B18`;
  - rosa do login da paciente `#FF2BA0` e fundo `#F7E1EE` (aproximados).
- As cores viram tokens no `@theme` do `resources/css/app.css`. As classes com
  cor fixa (`bg-[#009A94]`) passam a usar os tokens.
- Títulos em Montserrat, que hoje é citada no CSS mas não é carregada. O corpo
  do texto fica na seção 11.
- Regras gerais do taste-skill que valem aqui:
  - todo botão e link responde ao passar o mouse e ao clicar;
  - foco visível;
  - alvos de toque com pelo menos 44px;
  - um estilo de botão primário e um secundário;
  - `label` visível em cada campo;
  - contraste de texto legível;
  - nada aparece com animação ao rolar a página.

  As partes do taste-skill para sites de divulgação (hero, fotos, uma seção por
  tela, o `gate.mjs`) não se aplicam a um sistema interno.

### 8.4 Verificação

- `php artisan test --compact` passando.
- Teste no navegador (Playwright) do caminho principal: login de colaborador,
  troca de senha no primeiro acesso, criação de paciente, busca, ocultar,
  reativar, editar e excluir. Depois, login de paciente. Em 1440px e 390px.
- Depois do teste no navegador, apagar os navegadores baixados, os pacotes e as
  pastas temporárias criadas só para ele.

## 9. Problemas conhecidos fora do escopo

- `colaboradorService::atualizarColaborador` chama
  `geraIdentificador($dados['cargo'])` mesmo sem `cargo`, o que dá erro em
  `PUT /api/users/{id}` sem esse campo. Nenhuma tela deste trabalho usa essa
  rota.
- `DatabaseSeeder` usa colunas que não existem em `users`.
- As migrations não criam a tabela `cache` (o `.env` local usa
  `CACHE_STORE=file`).

## 10. Regra para decisões deixadas para quem implementa

Quando quem implementa fizer uma escolha listada na seção 11, a escolha é
marcada no código, no ponto onde foi aplicada, com um comentário neste formato
(na sintaxe de comentário da linguagem):

```
// DECISAO: <o que precisa ser decidido> — escolhido: <o que foi feito>; alternativas: <outras opções>
```

No Blade, use `{{-- DECISAO: … --}}`. O usuário encontra todas com
`grep -rn "DECISAO:"`.

## 11. Decisões deixadas para quem implementa

### 11.1 Onde guardar o token no navegador

- **O que precisa ser decidido:** se o token do login fica no `localStorage` ou
  no `sessionStorage`.
- **Por que ficou aberto:** não muda as telas nem a API, só quanto tempo a
  pessoa fica logada.
- **Opções:**
  - `localStorage`. Ganha: a pessoa continua logada em outras abas e depois de
    fechar o navegador. Perde: num computador compartilhado da clínica, quem
    esquecer de sair deixa a conta aberta.
  - `sessionStorage`. Ganha: fechar a aba já desloga. Perde: cada aba nova pede
    login de novo.
- **Sugestão:** `sessionStorage`. Os computadores do ambulatório são
  compartilhados, e os dados são de saúde.
- **Como o usuário confere:** `DECISAO:` em `resources/js/api.js`. Na tela,
  abrir `/inicio` numa aba nova mostra se pede login de novo.

### 11.2 Fonte do texto corrido

- **O que precisa ser decidido:** a fonte dos textos que não são títulos.
- **Por que ficou aberto:** as capturas não dizem o nome da fonte. Pelo
  desenho, parece Hind, e o CSS atual usa Instrument Sans.
- **Opções:**
  - Hind. Ganha: fica mais próximo do Figma. Perde: é uma fonte a mais para
    carregar.
  - Instrument Sans, que já está no CSS. Ganha: nada a carregar. Perde: pode
    ficar diferente do Figma.
- **Sugestão:** Hind, servida pelo próprio projeto (pacote `@fontsource`), sem
  depender do Google Fonts.
- **Como o usuário confere:** `DECISAO:` em `resources/css/app.css`. Comparar os
  subtítulos com o Figma.

### 11.3 Paginação da lista de pacientes

- **O que precisa ser decidido:** como mostrar mais de 15 pacientes.
- **Por que ficou aberto:** o design só mostra duas linhas e não tem paginação.
- **Opções:**
  - Botão "Carregar mais" no fim da tabela. Ganha: simples e cabe no design.
    Perde: não dá para pular para uma página.
  - Paginação numerada. Ganha: navegação direta. Perde: é um componente que o
    design não tem.
- **Sugestão:** "Carregar mais", porque é mais simples e o design não tem
  paginação.
- **Como o usuário confere:** `DECISAO:` em `resources/js/pages/pacientes.js`.
  Com mais de 15 pacientes, o botão aparece.

### 11.4 Quando a busca dispara

- **O que precisa ser decidido:** se a busca roda enquanto a pessoa digita ou só
  ao apertar Enter.
- **Por que ficou aberto:** o design só mostra o campo.
- **Opções:**
  - Enquanto digita, com espera de 300 ms. Ganha: resposta imediata. Perde: mais
    chamadas à API.
  - Só no Enter ou na lupa. Ganha: menos chamadas. Perde: a pessoa precisa
    apertar algo.
- **Sugestão:** enquanto digita, com espera de 300 ms.
- **Como o usuário confere:** `DECISAO:` em `resources/js/pages/pacientes.js`.

### 11.5 Listas de estado civil e escolaridade

- **O que precisa ser decidido:** as opções dos dois campos de seleção.
- **Por que ficou aberto:** o design só mostra o primeiro item de cada um
  ("Solteira", "Não escolarizada").
- **Opções:**
  - Lista fixa no frontend. Ganha: simples. Perde: a API aceita qualquer texto.
  - Lista fixa no frontend e validada na API (`in:`). Ganha: dados limpos.
    Perde: mudar a lista exige mexer nos dois lugares.
- **Sugestão:** validar na API e repetir a lista no frontend.
  - Estado civil: Solteira, Casada, União estável, Divorciada, Viúva.
  - Escolaridade: Não escolarizada, Fundamental incompleto, Fundamental
    completo, Médio incompleto, Médio completo, Superior incompleto, Superior
    completo, Pós-graduação.
- **Como o usuário confere:** `DECISAO:` na regra de validação e no Blade da
  janela.

### 11.6 Formato do CPF

- **O que precisa ser decidido:** se o CPF é gravado com ou sem pontos e traço.
- **Por que ficou aberto:** a API aceita até 14 caracteres, o que inclui a
  máscara, mas não exige formato nenhum.
- **Opções:**
  - Gravar com máscara (`000.000.000-00`). Ganha: igual ao que se vê na tela.
    Perde: a busca precisa ignorar a pontuação.
  - Gravar só os números. Ganha: busca e unicidade simples. Perde: os CPFs que
    já existem precisam ser convertidos, e a tela formata ao mostrar.
- **Sugestão:** gravar com máscara. A tela aplica a máscara ao digitar. A API
  aceita os dois formatos (11 dígitos ou `000.000.000-00`) e grava sempre com
  máscara, para o mesmo CPF não entrar duas vezes em formatos diferentes. Os
  testes que já enviam só dígitos continuam válidos. Os testes que conferem o
  valor gravado são atualizados para o formato com máscara.
- **Como o usuário confere:** `DECISAO:` na regra de validação do CPF.

### 11.7 O link "Não sei qual é meu login"

- **O que precisa ser decidido:** o que acontece ao clicar no link.
- **Por que ficou aberto:** o design não mostra o destino.
- **Opções:**
  - Mostrar um texto ali mesmo: "Seu login é o e-mail informado no seu cadastro.
    Em caso de dúvida, procure a recepção do ambulatório." Ganha: não precisa de
    tela nova. Perde: não resolve sozinho.
  - Uma tela de recuperação por e-mail. Ganha: a pessoa resolve sozinha. Perde:
    exige rota nova na API e envio de e-mail, fora deste trabalho.
- **Sugestão:** o texto ali mesmo.
- **Como o usuário confere:** `DECISAO:` em `resources/views/auth/login.blade.php`.
