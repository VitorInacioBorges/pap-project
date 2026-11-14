# Execução do projeto

Este guia mostra como preparar, iniciar e atualizar o Projeto PAP numa máquina
de desenvolvimento. O projeto é uma aplicação Laravel 13 com telas em Blade,
uma API JSON em `/api`, banco PostgreSQL e frontend compilado pelo Vite.

Para entender como as partes se ligam, leia a
[arquitetura](arquitetura.md). Para as regras de código da equipe, leia as
[boas práticas](boas-praticas.md).

## Requisitos

Antes de começar, instale:

- PHP 8.3 ou superior, com as extensões `pdo_pgsql` e `pgsql`.
- Composer 2.
- Node.js `^20.19.0` ou `>=22.12.0`, com npm.
- PostgreSQL, com o serviço ativo.
- Git, caso ainda precise clonar o repositório.

Para conferir as versões e as extensões, execute:

```bash
php -v
php -m | grep -i pgsql
composer -V
node -v
psql --version
```

## Instalação inicial

Siga os passos abaixo na raiz do projeto.

### 1. Instale as dependências e crie o `.env`

1. Crie o `.env` a partir do exemplo, caso ainda não exista:

   ```bash
   cp .env.example .env
   ```

2. Instale as dependências do PHP e gere a chave da aplicação:

   ```bash
   composer install
   php artisan key:generate
   ```

### 2. Descubra a porta do PostgreSQL

O PostgreSQL nem sempre usa a porta padrão `5432`. Em instalações com mais de
uma versão, por exemplo, o serviço pode estar em `5433`. Para ver a porta do
seu servidor no Ubuntu ou no WSL, execute:

```bash
pg_lsclusters
```

Use o valor da coluna `Port` nos próximos passos. Se o comando não existir,
confira o parâmetro `port` no arquivo `postgresql.conf`.

### 3. Crie o usuário e o banco

Crie um usuário e um banco exclusivos para o projeto. Troque `5433` pela porta
do passo anterior e `troque_esta_senha` por uma senha sua:

```bash
sudo -u postgres psql -p 5433 <<'SQL'
CREATE ROLE pap_user WITH LOGIN PASSWORD 'troque_esta_senha';
CREATE DATABASE pap_project OWNER pap_user;
SQL
```

O comando pede a senha do seu usuário no Linux, porque roda como o usuário
`postgres`.

### 4. Configure o banco, o cache e o idioma no `.env`

No `.env`, substitua as linhas `DB_*` por estas, com a sua porta e a sua senha:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=pap_project
DB_USERNAME=pap_user
DB_PASSWORD=troque_esta_senha
```

No mesmo arquivo, mude o cache para arquivo e o idioma para português:

```dotenv
CACHE_STORE=file
APP_LOCALE=pt_BR
```

As migrations do projeto não criam a tabela `cache`. Com
`CACHE_STORE=database`, a rota de login falha, porque o limitador de
tentativas guarda os contadores no cache.

Para confirmar que a aplicação acessa o banco, execute:

```bash
php artisan db:show
```

### 5. Crie as tabelas

Execute as migrations:

```bash
php artisan migrate
```

### 6. Crie o primeiro administrador

O projeto ainda não tem um seeder para o primeiro usuário. Não use
`php artisan db:seed`: o `DatabaseSeeder` atual usa as colunas `name` e
`password`, que não existem na tabela `users`.

Crie o administrador pelo Tinker:

```bash
php artisan tinker --execute="App\Models\User::create(['nome' => 'Administrador', 'alias' => 'admin', 'email' => 'admin@pap.local', 'senha' => 'admin12345', 'cargo' => 'admin']);"
```

O modelo `User` aplica o hash na senha ao salvar. O campo `alias` aceita até
11 caracteres, e o campo `email` aceita até 30. Os cargos válidos são
`admin`, `medico`, `enfermeiro` e `secretario`. Só `secretario` (mostrado como
"Atendente") e `admin` acessam a tela de pacientes.

### 7. Compile o frontend

Instale as dependências do Node e gere os arquivos do frontend:

```bash
npm ci
npm run build
```

## Iniciar em desenvolvimento

Para subir tudo de uma vez, execute na raiz do projeto:

```bash
composer run dev
```

O comando abre quatro processos num só terminal:

| Processo | Comando | Para que serve |
|---|---|---|
| `server` | `php artisan serve` | Serve as telas e a API em <http://127.0.0.1:8000>. |
| `queue` | `php artisan queue:listen` | Envia os e-mails da fila, como a senha temporária de colaborador. |
| `logs` | `php artisan pail` | Mostra o `storage/logs/laravel.log` em tempo real. |
| `vite` | `npm run dev` | Recompila CSS e JavaScript a cada mudança. |

A rota `/` leva para a tela de login.

Opcional: para subir só o necessário, abra um terminal para cada comando:

```bash
php artisan serve
npm run dev
```

Sem `npm run dev`, as telas usam os arquivos gerados por `npm run build`. Sem
a fila, os e-mails ficam parados na tabela `jobs` até alguém rodar
`php artisan queue:work`.

## Telas

| Endereço | Tela | Quem acessa |
|---|---|---|
| `/login` | Login, com as abas **Sou Paciente** e **Sou do ProjetoPAP** | Todos |
| `/inicio` | Início, com os atalhos | Colaboradores |
| `/pacientes` | Lista, cadastro e edição de pacientes | Atendente (`secretario`) e admin |
| `/configuracoes` | Nome, senha e primeiro acesso do colaborador | Colaboradores |
| `/paciente` | Portal da paciente | Pacientes |

## Primeiro acesso pelas telas

Todo usuário novo começa com a senha temporária e precisa trocá-la antes de
usar o sistema. Com o servidor rodando, siga estes passos no navegador:

1. Abra <http://127.0.0.1:8000/login>.
2. Escolha a aba **Sou do ProjetoPAP**, informe a identificação `admin` e a
   senha `admin12345`, e clique em **Entrar**.
3. Na tela **Configurações de conta**, preencha o nome, a senha atual e a nova
   senha duas vezes, e clique em **Salvar Alterações**. A nova senha precisa
   ter pelo menos 8 caracteres e ser diferente da atual.
4. Depois da troca, a tela **Início** abre com os atalhos para **Pacientes** e
   **Configurações da conta**.

Pacientes entram pela aba **Sou Paciente**, com o e-mail e a senha definidos no
cadastro. No primeiro acesso, o portal pede a troca da senha.

As telas guardam o token de acesso só na aba do navegador. Fechar a aba encerra
a sessão.

### Acesso direto pela API

Opcional: para testar a API sem as telas, use `curl`. Envie sempre o cabeçalho
`Accept: application/json`, porque sem ele o Laravel pode responder com
redirecionamento em vez de JSON.

1. Faça login. Colaboradores usam `alias`, e pacientes usam `email`. A rota
   aceita um dos dois, nunca os dois juntos, e permite 6 tentativas por minuto:

   ```bash
   curl -X POST http://127.0.0.1:8000/api/login \
     -H 'Accept: application/json' -H 'Content-Type: application/json' \
     -d '{"alias": "admin", "senha": "admin12345"}'
   ```

2. Troque a senha do primeiro acesso com o token recebido. A resposta traz um
   token novo, e os antigos deixam de valer:

   ```bash
   curl -X POST http://127.0.0.1:8000/api/alterar-senha \
     -H 'Accept: application/json' -H 'Content-Type: application/json' \
     -H 'Authorization: Bearer SEU_TOKEN' \
     -d '{"senha_atual": "admin12345", "nova_senha": "novaSenha123", "nova_senha_confirmation": "novaSenha123"}'
   ```

3. Use o token novo nas outras rotas, por exemplo `GET /api/me`:

   ```bash
   curl http://127.0.0.1:8000/api/me \
     -H 'Accept: application/json' -H 'Authorization: Bearer SEU_TOKEN_NOVO'
   ```

## E-mail e fila

Quando o admin cria um colaborador (`POST /api/users`), o sistema gera a
identificação e uma senha temporária e envia as duas por e-mail. O e-mail vai
para a fila (`QUEUE_CONNECTION=database`), então só sai com a fila rodando,
como no `composer run dev`.

Em desenvolvimento, recomendamos `MAIL_MAILER=log`. Com essa opção, o Laravel
grava os e-mails em `storage/logs/laravel.log` em vez de enviá-los. Não
compartilhe nem versione o arquivo `.env`, porque ele guarda senhas.

## Atualizar depois de um pull

Depois de baixar mudanças da equipe, rode na raiz do projeto:

```bash
composer install
npm ci
php artisan migrate
npm run build
```

As migrations novas podem mudar dados que já existem. Por exemplo, a migration
`2026_11_15_000003_normaliza_cpf_dos_pacientes` coloca máscara nos CPFs
gravados só com dígitos.

## Testes

Para executar a suíte, rode:

```bash
php artisan test --compact
```

Os testes de feature usam SQLite em memória, conforme o `phpunit.xml`. Eles
não tocam no banco PostgreSQL local. Para rodar um arquivo só, passe o
caminho:

```bash
php artisan test --compact tests/Feature/PacienteListagemTest.php
```

## Solução de problemas

- **`Connection refused` ao conectar no banco:** o PostgreSQL está parado ou
  em outra porta. Confira a porta com `pg_lsclusters` e ajuste `DB_PORT`.
- **`role "..." does not exist` ou falha de senha:** o usuário do banco não
  existe ou a senha do `.env` não confere. Repita o passo
  [Crie o usuário e o banco](#3-crie-o-usuário-e-o-banco).
- **`relation "cache" does not exist` no login:** o `.env` ainda está com
  `CACHE_STORE=database`. Mude para `CACHE_STORE=file` e execute
  `php artisan config:clear`.
- **`Altere sua senha antes de continuar.`:** o usuário ainda não trocou a
  senha do primeiro acesso. Siga o
  [primeiro acesso pelas telas](#primeiro-acesso-pelas-telas).
- **Erro no `php artisan db:seed`:** o seeder atual não combina com a tabela
  `users`. Crie o administrador pelo Tinker, como no passo 6.
- **PHP sem a extensão PostgreSQL:** confira com `php -m` e habilite
  `pdo_pgsql` e `pgsql` na instalação do PHP.
- **Erro de chave da aplicação:** execute `php artisan key:generate`.
- **Mudanças no `.env` sem efeito:** execute `php artisan config:clear`.
- **Alterações no frontend não aparecem:** mantenha `npm run dev` ativo ou
  execute `npm run build` de novo.
- **Tela parada em "Carregando…" ou sem estilo:** o navegador não recebeu o
  JavaScript. Confira se o `npm run dev` está rodando. Se não estiver, apague
  o arquivo `public/hot`, que sobra de um `npm run dev` encerrado, e execute
  `npm run build`.
- **"Não foi possível carregar seus dados.":** a API respondeu com erro ao
  buscar o usuário logado. Veja o motivo no `storage/logs/laravel.log` ou no
  processo `logs` do `composer run dev`.
- **"Seu acesso está desativado.":** o colaborador está com `ativo = false`.
  Um admin precisa reativá-lo por `PUT /api/users/{id}`.
- **E-mail de senha temporária não chega:** a fila não está rodando. Execute
  `php artisan queue:work` ou use o `composer run dev`.
- **Erro `Escolha uma opção válida` ao editar paciente:** o estado civil ou a
  escolaridade vieram de um cadastro antigo, fora da lista. Escolha uma opção
  da lista e salve de novo.
- **Porta 8000 em uso:** inicie com `php artisan serve --port=8001` e acesse
  <http://127.0.0.1:8001>.
