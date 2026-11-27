# Norte e progresso do projeto

## Objetivo para o portfólio

Apresentar um projeto full-stack simples de chamados, com API Laravel e
interface Vue. O recrutador deve conseguir perceber que o projeto demonstra:

- modelagem relacional entre usuários, categorias e chamados;
- autenticação com Sanctum e autorização por perfil;
- API com validação e testes;
- interface Vue consumindo a API;
- instruções claras para instalar, executar e testar o projeto.

Manter o escopo enxuto: sem histórico complexo, grupos extras ou recursos que
não sejam necessários para o fluxo de chamados.

## Fluxo esperado

- **Usuário:** relata um chamado associado a uma categoria e acompanha o próprio
  chamado; não o exclui nem o marca como concluído.
- **Administrador:** consulta os chamados, valida se o atendimento foi
  executado e altera o status para concluído.
- **Visibilidade confirmada:** usuário vê apenas os chamados que relatou;
  administrador vê todos.

## Já implementado

- Login e logout com tokens Bearer do Laravel Sanctum.
- Usuário comum vê e altera somente os próprios chamados; admin vê todos.
- Criação associa o chamado ao usuário autenticado; `user_id` enviado pelo
  cliente é rejeitado.
- Usuário comum não exclui nem altera status; admin pode validar e concluir.
- Categorias existentes estão disponíveis em `GET /api/categories`.
- Vue conectado à API; controles de status e exclusão respeitam a role.
- Cinco categorias no seed: Suporte Técnico, Financeiro, Dúvidas,
  Infraestrutura e Acesso e Login.
- Interface com cartões de categoria, filtros por categoria/status e coluna de
  edição maior que a lista.
- Login limitado a cinco tentativas por minuto; seed de demonstração protegido
  para ambientes local/teste; CORS configurável por origem.
- 20 testes passaram (81 verificações); build validado após `npm ci`.

## Bloco 1 — Finalizar backend

- [x] Confirmar a regra de visibilidade: próprios chamados para usuário comum;
  todos os chamados para administrador.
- [x] Associar a criação ao usuário autenticado no servidor, sem confiar em
  `user_id` enviado pelo cliente. O token identifica o autor; o cliente não
  escolhe em nome de quem o chamado é criado.
- [x] Restringir usuário comum para não excluir chamados nem alterar o status
  para concluído.
- [x] Permitir que administrador valide o atendimento e conclua o chamado.
- [x] Expor a lista das cinco categorias pela API, sem criar categorias extras.
- [x] Criar testes focados nas permissões e nas regras de propriedade.

## Bloco 2 — Criar tarefas do frontend

- [x] Trocar o campo numérico de categoria por cartões de seleção com os cinco
  nomes definidos.
- [x] Dar mais espaço à coluna de criação/edição e menos espaço à lista de
  chamados, que será secundária nessa tela.
- [x] Mostrar ações diferentes conforme o perfil: usuário relata/acompanha;
  administrador valida e conclui.
- [x] Mostrar chamados por categoria e status, com estados de carregamento,
  lista vazia e erro.
- [x] Ajustar layout responsivo e revisar textos/fluxo visual.

## Bloco 3 — Preparar para compartilhar

- [x] Atualizar o README com objetivo, tecnologias, arquitetura simples e
  comandos para executar client e servidor.
- [x] Documentar migrations, seed e contas exclusivamente de desenvolvimento.
- [x] Confirmar instalação limpa em cópias temporárias: Composer + migrations,
  seed e testes; `npm ci` e build.
- [x] Proteger o seed de demonstração para rodar somente em `local`/`testing`,
  limitar tentativas de login e configurar CORS por origem.
- [ ] Antes de publicar, definir a origem CORS de produção, habilitar HTTPS,
  definir `APP_DEBUG=false` e revisar o armazenamento do token; nunca publicar
  segredos.

## Próxima ação

Fazer uma revisão final antes da publicação: URL HTTPS definitiva, origem CORS,
configuração de produção e credenciais.

## Arquivos principais

- `server/app/Http/Controllers/Api/TicketController.php` — CRUD e regras de
  propriedade/status dos chamados.
- `server/app/Http/Controllers/Api/CategoryController.php` — listagem das
  categorias existentes.
- `server/app/Http/Controllers/Api/AuthController.php` — login e logout.
- `server/routes/api.php` — rotas da API.
- `server/database/seeders/DatabaseSeeder.php` — contas e categorias de teste.
- `client/src/App.vue` — interface Vue atual.
- `server/tests/Feature/TicketaApiTest.php` e
  `server/tests/Feature/AuthApiTest.php` — testes de CRUD, permissões e login.

## Registro das próximas sessões

## Próxima ação

## Bloco 4 — Publicar e apresentar (por ordem de impacto)

### 4.1 Deploy no ar
- [ ] Escolher onde hospedar: front (client) na Vercel ou Netlify; API (server) no Render, Railway ou Fly.io.
- [ ] Trocar SQLite por PostgreSQL ou MySQL no ambiente de produção (configurar `DB_*` no `.env` do host, sem versionar).
- [ ] Rodar `php artisan migrate --force` no deploy.
- [ ] Definir `APP_ENV=production`, `APP_DEBUG=false` e `APP_KEY` próprio.
- [ ] Definir `CORS_ALLOWED_ORIGINS` com a URL HTTPS exata do front.
- [ ] Configurar `VITE_API_URL` apontando para a API publicada e gerar o build do client.
- [ ] Testar o fluxo completo online: login, criar chamado, filtrar, admin alterar status e excluir.
- [ ] Revisar o armazenamento do token (hoje em `sessionStorage`) e registrar a decisão no README.

### 4.2 Contas de demonstração no deploy
- [ ] Criar um seeder ou comando separado só para a demo (o seeder atual continua restrito a `local`/`testing`).
- [ ] Criar 1 usuário e 1 admin de demonstração, com dados fictícios e senhas usadas só na demo.
- [ ] Colocar essas credenciais no README, na seção da demo online.
- [ ] Definir como restaurar os dados da demo (ex.: `migrate:fresh` com o seeder de demo) caso alguém apague chamados.

### 4.3 Prints e GIF no README
- [ ] Tirar prints de: login, tela de usuário (criar e acompanhar chamado), lista com filtros e visão do administrador.
- [ ] Gravar um GIF curto (20 a 30 segundos) mostrando o fluxo usuário → admin concluindo o chamado.
- [ ] Salvar as imagens em uma pasta `docs/` e referenciar no README.
- [ ] Colocar o link da demo online no topo do README.

### 4.4 Repositório no GitHub
- [ ] Preencher o campo "About" com uma descrição curta (ex.: "Sistema de chamados fullstack com Laravel 12, Sanctum e Vue 3").
- [ ] Adicionar o link da demo no campo "Website".
- [ ] Adicionar topics: `laravel`, `vue`, `rest-api`, `sanctum`, `fullstack`, `php`.
- [ ] Fixar o repositório no perfil do GitHub.

### 4.5 Histórico de commits
- [ ] A partir de agora, fazer commits pequenos, com mensagens descritivas (ex.: `feat: adiciona filtro por status`).
- [ ] Decidir sobre o `PROGRESSO.md`: manter e citar no README, ou remover do repositório público.

### 4.6 LinkedIn (depois dos itens 4.1 a 4.4)
- [ ] Escrever o post: problema, stack, 2 ou 3 decisões técnicas, link da demo e do GitHub, GIF.
- [ ] Adicionar o projeto na seção "Projetos" do perfil, com as skills (Laravel, Vue, PHP, REST API).
- [ ] Colocar o repositório na seção "Destaque".


```text
Data:
O que concluí:
Onde parei (arquivo/funcionalidade):
Próxima ação:
Dúvidas ou bloqueios:
```
