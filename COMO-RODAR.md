# Como rodar o projeto

Este projeto é uma aplicação Laravel com API e recursos de frontend compilados pelo Vite.

## Requisitos

- PHP 8.3 ou superior, com as extensões `pdo_pgsql` e `pgsql`.
- Composer 2.
- Node.js `^20.19.0` ou `>=22.12.0` e npm.
- PostgreSQL acessível pela aplicação.
- Git, caso ainda precise clonar o repositório.

## Instalação inicial

Crie o `.env` a partir do exemplo, caso ainda não exista:

```bash
cp .env.example .env
```

Configure a conexão PostgreSQL no `.env` antes de executar migrations:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nome_do_banco
DB_USERNAME=usuario
DB_PASSWORD=sua_senha
```

Crie previamente o banco e o usuário no PostgreSQL e conceda ao usuário acesso ao banco. Em seguida, na raiz do projeto, instale as dependências e prepare a aplicação:

```bash
composer install
php artisan key:generate
php artisan migrate
npm ci
npm run build
```

O mailer pode permanecer como `log` para desenvolvimento local, sem necessidade de configurar um serviço externo de e-mail. Não compartilhe nem versione o arquivo `.env`.

## Iniciar em desenvolvimento

Inicie o servidor Laravel:

```bash
php artisan serve
```

A aplicação ficará disponível, por padrão, em <http://127.0.0.1:8000>.

Para recompilar o frontend automaticamente enquanto desenvolve, abra outro terminal na raiz do projeto e execute:

```bash
npm run dev
```

## Testes

Execute a suíte:

```bash
php artisan test --compact
```

Os testes de feature usam SQLite em memória conforme a configuração de `phpunit.xml`, mesmo que a aplicação local esteja configurada para PostgreSQL.

## Solução de problemas

- **PHP ou extensão PostgreSQL ausente:** confira `php -v` e `php -m`; habilite `pdo_pgsql` e `pgsql` na instalação do PHP.
- **Erro de chave da aplicação:** execute `php artisan key:generate`.
- **Falha na conexão com o banco:** confira se o serviço PostgreSQL está ativo, se o banco e o usuário existem e se `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` e `DB_PASSWORD` estão corretos no `.env`. Depois execute `php artisan migrate`.
- **Alterações no frontend não aparecem:** mantenha `npm run dev` ativo ou execute novamente `npm run build`.
- **Porta 8000 em uso:** inicie com `php artisan serve --port=8001` e acesse <http://127.0.0.1:8001>.
