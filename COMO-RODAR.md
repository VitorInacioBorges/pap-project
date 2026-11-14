# Como rodar o projeto

Este guia mostra como preparar e iniciar o projeto em uma máquina de
desenvolvimento. O projeto é uma aplicação Laravel que expõe uma API em `/api`,
usa PostgreSQL como banco e compila o frontend com o Vite.

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

### 4. Configure o banco e o cache no `.env`

No `.env`, substitua as linhas `DB_*` por estas, com a sua porta e a sua senha:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=pap_project
DB_USERNAME=pap_user
DB_PASSWORD=troque_esta_senha
```

No mesmo arquivo, mude o cache para arquivo:

```dotenv
CACHE_STORE=file
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

Para iniciar o servidor Laravel, execute:

```bash
php artisan serve
```

A aplicação fica disponível em <http://127.0.0.1:8000>. A rota `/` leva para
a tela de login.

Opcional: para recompilar o frontend automaticamente enquanto desenvolve, abra
outro terminal na raiz do projeto e execute:

```bash
npm run dev
```

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

## E-mail

Em desenvolvimento, recomendamos `MAIL_MAILER=log`. Com essa opção, o Laravel
grava os e-mails em `storage/logs/laravel.log` em vez de enviá-los. Não
compartilhe nem versione o arquivo `.env`, porque ele guarda senhas.

## Testes

Para executar a suíte, rode:

```bash
php artisan test --compact
```

Os testes de feature usam SQLite em memória, conforme o `phpunit.xml`. Eles
não tocam no banco PostgreSQL local.

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
- **Porta 8000 em uso:** inicie com `php artisan serve --port=8001` e acesse
  <http://127.0.0.1:8001>.
