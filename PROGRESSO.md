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
- `server/tests/Feature/TicketApiTest.php` e
  `server/tests/Feature/AuthApiTest.php` — testes de CRUD, permissões e login.

## Registro das próximas sessões

```text
Data:
O que concluí:
Onde parei (arquivo/funcionalidade):
Próxima ação:
Dúvidas ou bloqueios:
```
