# PortDEV — Sistema de chamados

Aplicação full-stack para registrar chamados associados a categorias. Usuários
acompanham os chamados que abriram; administradores consultam todos e validam
o atendimento alterando o status.

## O que o projeto demonstra

- API REST com Laravel 12, PHP 8.2+ e Laravel Sanctum.
- Interface SPA com Vue 3 e Vite.
- Relações entre usuários, chamados e categorias.
- Autorização por perfil, validação de dados e testes automatizados.
- Filtros de chamados por categoria e status.

### Regras de acesso

- Usuários autenticados criam e acompanham os próprios chamados; não podem
  alterar o status nem excluir chamados.
- Administradores consultam todos os chamados, alteram o status após validar o
  atendimento e podem excluir chamados.
- O autor do chamado é definido pela API com base no token, não pelo formulário.
- O seed contém cinco categorias: Suporte Técnico, Financeiro, Dúvidas,
  Infraestrutura e Acesso e Login.

## Tecnologias e estrutura

- `server/`: API Laravel, Sanctum, regras de acesso, migrations, seeders e testes.
- `client/`: interface Vue que consome a API usando token Bearer.

## Banco e migrations

- `users`: contas e perfil (`user` ou `admin`).
- `categories`: categorias usadas para classificar os chamados.
- `tickets`: autor, categoria, título, descrição, status e prioridade; possui
  relações com `users` e `categories`.
- `personal_access_tokens`: tokens Bearer gerenciados pelo Sanctum.

`php artisan migrate` cria as tabelas. `php artisan migrate --seed` também
insere dados de demonstração; o seeder é restrito a ambientes `local` e
`testing`.

## Requisitos

- PHP 8.2 ou superior e Composer.
- Node.js 22.18 ou superior (linha 22) ou 24.12 ou superior, e npm.
- SQLite habilitado no PHP.

## Executar localmente no Windows

### 1. Preparar e iniciar a API

No PowerShell:

```powershell
cd C:\caminho\para\portDEV\server
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
if (-not (Test-Path .\database\database.sqlite)) {
    New-Item -ItemType File .\database\database.sqlite | Out-Null
}
composer install
php artisan key:generate
php artisan migrate --seed
php artisan test
php artisan serve
```

A API ficará em `http://127.0.0.1:8000`.

### 2. Iniciar a interface

Em outro terminal:

```powershell
cd C:\caminho\para\portDEV\client
npm ci
npm run dev
```

Abra `http://localhost:5173`. Durante o desenvolvimento, as requisições em
`/api` são encaminhadas pelo Vite para o Laravel em
`http://127.0.0.1:8000`. Para uma API em outra origem, configure `VITE_API_URL`
antes de iniciar/buildar o cliente.

## Compartilhar temporariamente com ngrok

Deixe a API e o cliente rodando nos terminais separados. Em um terceiro
terminal, na pasta do cliente, execute:

```powershell
ngrok http 5173 --host-header=rewrite
```

Envie aos amigos a URL HTTPS exibida pelo ngrok. O Vite encaminha as chamadas
`/api` para o Laravel local na porta `8000`, então não é necessário abrir um
segundo túnel para a API.

O túnel torna o app acessível publicamente enquanto estiver ativo. Use somente
dados de demonstração e encerre-o com `Ctrl+C` ao terminar; quem tiver o link
poderá entrar como administrador e alterar ou excluir chamados.

## Reiniciar o banco local

Na pasta `server`, rode:

```powershell
php artisan migrate:fresh --seed
```

Esse comando apaga todas as tabelas do banco configurado no `.env`, recria o
schema e carrega somente as duas contas de demonstração e as cinco categorias.
Confirme que `.env` aponta para o banco local correto antes de executar; não
rode em produção ou em um banco com dados que queira manter.

## Contas de demonstração

`php artisan db:seed` cria as duas contas e as cinco categorias **somente nos
ambientes `local` e `testing`**. As credenciais abaixo são públicas e
exclusivas para demonstração local; nunca as use em produção:

| Perfil | E-mail | Senha |
| --- | --- | --- |
| Administrador | `admin@sistema.com` | `12345678` |
| Usuário | `keebler.marjolaine@example.net` | `password` |

O seeder interrompe a execução fora de `local`/`testing`. Não coloque valores
reais no `.env` nem publique esse arquivo.

## API principal

- `POST /api/login` — login; limitado a cinco tentativas por minuto.
- `GET /api/user` e `POST /api/logout` — dados e encerramento da autenticação.
- `GET /api/categories` — categorias disponíveis.
- `/api/tickets` — listar, criar, consultar, atualizar e excluir chamados.

As rotas, exceto login, exigem `Authorization: Bearer <token>`.

## Testes e build

```powershell
cd server
php artisan test
cd ..\client
npm run build
```

## Antes de publicar

- Configure `CORS_ALLOWED_ORIGINS` com a origem HTTPS exata do cliente publicado.
- Use HTTPS, `APP_DEBUG=false` e credenciais próprias para o ambiente.
- O token do cliente fica atualmente no `sessionStorage`; revisar a estratégia
  de armazenamento de token antes de usar com dados reais.
- O rate limit do login está habilitado; revise limites e monitoramento para o
  ambiente de produção.
