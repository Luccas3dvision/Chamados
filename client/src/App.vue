<script setup>
import { onMounted, reactive, ref } from 'vue'
import defaultDemoAccounts from './demoAccounts.js'

const apiUrl = (import.meta.env.VITE_API_URL || '/api').replace(/\/+$/, '')
const token = ref(sessionStorage.getItem('api_token') || '')
const user = ref(null)
const isDemoEnabled = import.meta.env.VITE_DISABLE_DEMO !== 'true'
const tickets = ref([])
const categories = ref([])
const categoriesLoading = ref(false)
const loading = ref(false)
const error = ref('')
const notice = ref('')
const editingTicketId = ref(null)

const loginForm = reactive({
  email: '',
  password: '',
})

const demoAccounts = isDemoEnabled ? defaultDemoAccounts : []
const selectedDemoAccount = ref('')

const ticketForm = reactive({
  title: '',
  description: '',
  category_id: '',
  status: 'open',
  priority: 'medium',
})

const filters = reactive({
  category_id: '',
  status: '',
})

function fillDemoAccount() {
  const account = demoAccounts.find(({ email }) => email === selectedDemoAccount.value)
  if (!account) return

  loginForm.email = account.email
  loginForm.password = account.password
}

function clearAuthentication() {
  sessionStorage.removeItem('api_token')
  token.value = ''
  user.value = null
  tickets.value = []
  categories.value = []
  editingTicketId.value = null
}

async function request(path, { method = 'GET', body } = {}) {
  const headers = { Accept: 'application/json' }

  if (token.value) {
    headers.Authorization = `Bearer ${token.value}`
  }

  if (body !== undefined) {
    headers['Content-Type'] = 'application/json'
  }

  let response
  try {
    response = await fetch(`${apiUrl}${path}`, {
      method,
      headers,
      body: body === undefined ? undefined : JSON.stringify(body),
    })
  } catch {
    throw new Error('Não foi possível conectar à API. Confira se o servidor Laravel está ativo.')
  }

  let data
  try {
    data = await response.json()
  } catch {
    throw new Error('A API retornou uma resposta inválida.')
  }

  if (!response.ok) {
    if (response.status === 401 && token.value) {
      clearAuthentication()
    }

    const validationMessage = data.errors
      ? Object.values(data.errors).flat().join(' ')
      : ''
    throw new Error(validationMessage || data.message || 'Ocorreu um erro na requisição.')
  }

  return data
}

async function loadTickets() {
  const params = new URLSearchParams()
  if (filters.category_id) params.set('category_id', filters.category_id)
  if (filters.status) params.set('status', filters.status)

  const query = params.toString()
  tickets.value = await request(`/tickets${query ? `?${query}` : ''}`)
}

async function loadCategories() {
  categoriesLoading.value = true
  try {
    categories.value = await request('/categories')
  } finally {
    categoriesLoading.value = false
  }
}

async function refreshTickets() {
  error.value = ''
  loading.value = true
  try {
    await loadTickets()
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    loading.value = false
  }
}

function clearFilters() {
  filters.category_id = ''
  filters.status = ''
  refreshTickets()
}

function statusLabel(status) {
  return {
    open: 'Aberto',
    in_progress: 'Em andamento',
    closed: 'Concluído',
  }[status] || status
}

function priorityLabel(priority) {
  return {
    low: 'Baixa',
    medium: 'Média',
    high: 'Alta',
  }[priority] || priority
}

async function signIn() {
  error.value = ''
  notice.value = ''
  loading.value = true

  try {
    const result = await request('/login', {
      method: 'POST',
      body: loginForm,
    })
    token.value = result.token
    sessionStorage.setItem('api_token', result.token)
    user.value = result.user
    loginForm.password = ''
    await Promise.all([loadCategories(), loadTickets()])
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    loading.value = false
  }
}

async function restoreSession() {
  if (!token.value) return

  loading.value = true
  try {
    user.value = await request('/user')
    await Promise.all([loadCategories(), loadTickets()])
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    loading.value = false
  }
}

async function signOut() {
  error.value = ''
  notice.value = ''

  try {
    await request('/logout', { method: 'POST' })
    notice.value = 'Você saiu da sua conta.'
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    clearAuthentication()
  }
}

function resetTicketForm() {
  editingTicketId.value = null
  ticketForm.title = ''
  ticketForm.description = ''
  ticketForm.category_id = ''
  ticketForm.status = 'open'
  ticketForm.priority = 'medium'
}

function editTicket(ticket) {
  error.value = ''
  notice.value = ''
  editingTicketId.value = ticket.id
  ticketForm.title = ticket.title
  ticketForm.description = ticket.description
  ticketForm.category_id = String(ticket.category_id)
  ticketForm.status = ticket.status
  ticketForm.priority = ticket.priority
}

async function saveTicket() {
  error.value = ''
  notice.value = ''

  if (!ticketForm.category_id) {
    error.value = 'Selecione uma categoria para o chamado.'
    return
  }

  const body = {
    category_id: Number(ticketForm.category_id),
    title: ticketForm.title,
    description: ticketForm.description,
    priority: ticketForm.priority,
  }

  if (editingTicketId.value && user.value.role === 'admin') {
    body.status = ticketForm.status
  }

  loading.value = true
  try {
    if (editingTicketId.value) {
      await request(`/tickets/${editingTicketId.value}`, {
        method: 'PATCH',
        body,
      })
      notice.value = 'Chamado atualizado.'
    } else {
      await request('/tickets', {
        method: 'POST',
        body,
      })
      notice.value = 'Chamado criado.'
    }

    resetTicketForm()
    await loadTickets()
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    loading.value = false
  }
}

async function deleteTicket(ticket) {
  if (!window.confirm(`Excluir o chamado "${ticket.title}"?`)) return

  error.value = ''
  notice.value = ''
  loading.value = true

  try {
    await request(`/tickets/${ticket.id}`, { method: 'DELETE' })
    notice.value = 'Chamado excluído.'
    await loadTickets()
  } catch (requestError) {
    error.value = requestError.message
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  restoreSession()
})
</script>

<template>
  <main class="page">
    <header class="topbar">
      <a class="brand" href="/">PortDEV</a>
      <div v-if="user" class="account">
        <span>{{ user.name }} <small>({{ user.role }})</small></span>
        <button class="button button-quiet" type="button" @click="signOut">Sair</button>
      </div>
    </header>

    <p v-if="error" class="message message-error" role="alert">{{ error }}</p>
    <p v-if="notice" class="message message-success" role="status">{{ notice }}</p>
    <p v-if="loading && !user" class="loading">Conectando à API...</p>

    <section v-if="!token" class="panel login-panel">
      <p class="eyebrow">Área de atendimento</p>
      <h1>Entrar</h1>
      <p class="muted">Use sua conta para acessar os chamados.</p>

      <div v-if="demoAccounts.length" class="demo-access">
        <label for="demo-account">Conta de demonstração</label>
        <select id="demo-account" v-model="selectedDemoAccount" @change="fillDemoAccount">
          <option value="">Selecione uma role para preencher o login</option>
          <option v-for="account in demoAccounts" :key="account.email" :value="account.email">
            {{ account.role }} — {{ account.email }}
          </option>
        </select>
        <p class="muted">Contas públicas para avaliação do projeto.</p>
      </div>

      <form class="form" @submit.prevent="signIn">
        <label>
          E-mail
          <input v-model="loginForm.email" type="email" autocomplete="username" required>
        </label>
        <label>
          Senha
          <input
            v-model="loginForm.password"
            type="password"
            autocomplete="current-password"
            required
          >
        </label>
        <button class="button button-primary" type="submit" :disabled="loading">
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>
    </section>

    <template v-else-if="user">
      <section class="intro">
        <div>
          <p class="eyebrow">Painel</p>
          <h1>Chamados</h1>
          <p class="muted">
            {{ user.role === 'admin'
              ? 'Valide e acompanhe os chamados da equipe.'
              : 'Relate e acompanhe seus chamados.' }}
          </p>
        </div>
        <button
          class="button button-quiet"
          type="button"
          :disabled="loading"
          @click="refreshTickets"
        >
          Atualizar lista
        </button>
      </section>

      <div class="content-grid" :class="{ 'content-grid-admin': user.role === 'admin' }">
        <section class="panel form-panel">
          <h2>{{ editingTicketId ? 'Editar chamado' : 'Novo chamado' }}</h2>
          <form class="form" @submit.prevent="saveTicket">
            <label>
              Título
              <input v-model="ticketForm.title" maxlength="255" required>
            </label>
            <label>
              Descrição
              <textarea v-model="ticketForm.description" rows="4" required />
            </label>
            <fieldset class="category-field">
              <legend>Categoria <span>obrigatória</span></legend>
              <div v-if="categories.length" class="category-grid">
                <button
                  v-for="category in categories"
                  :key="category.id"
                  class="category-card"
                  :class="{ 'category-card-selected': Number(ticketForm.category_id) === category.id }"
                  type="button"
                  :aria-pressed="Number(ticketForm.category_id) === category.id"
                  @click="ticketForm.category_id = String(category.id)"
                >
                  {{ category.name }}
                </button>
              </div>
              <p v-else-if="categoriesLoading" class="muted">Carregando categorias...</p>
              <p v-else class="muted">Não foi possível carregar as categorias.</p>
            </fieldset>
            <div class="field-row" :class="{ 'field-row-single': user.role !== 'admin' }">
              <label v-if="user.role === 'admin'">
                Status
                <select v-model="ticketForm.status">
                  <option value="open">Aberto</option>
                  <option value="in_progress">Em andamento</option>
                  <option value="closed">Fechado</option>
                </select>
              </label>
              <label>
                Prioridade
                <select v-model="ticketForm.priority">
                  <option value="low">Baixa</option>
                  <option value="medium">Média</option>
                  <option value="high">Alta</option>
                </select>
              </label>
            </div>
            <div class="form-actions">
              <button class="button button-primary" type="submit" :disabled="loading">
                {{ editingTicketId ? 'Salvar alterações' : 'Criar chamado' }}
              </button>
              <button
                v-if="editingTicketId"
                class="button button-quiet"
                type="button"
                @click="resetTicketForm"
              >
                Cancelar
              </button>
            </div>
          </form>
        </section>

        <section class="ticket-list" aria-label="Lista de chamados">
          <div class="ticket-filters">
            <label>
              Categoria
              <select v-model="filters.category_id" :disabled="loading" @change="refreshTickets">
                <option value="">Todas</option>
                <option v-for="category in categories" :key="category.id" :value="String(category.id)">
                  {{ category.name }}
                </option>
              </select>
            </label>
            <label>
              Status
              <select v-model="filters.status" :disabled="loading" @change="refreshTickets">
                <option value="">Todos</option>
                <option value="open">Aberto</option>
                <option value="in_progress">Em andamento</option>
                <option value="closed">Concluído</option>
              </select>
            </label>
            <button
              v-if="filters.category_id || filters.status"
              class="button button-quiet clear-filters"
              type="button"
              :disabled="loading"
              @click="clearFilters"
            >
              Limpar filtros
            </button>
          </div>

          <p v-if="loading" class="loading" role="status">Carregando chamados...</p>

          <div v-else-if="tickets.length === 0" class="panel empty-state">
            <h2>{{ filters.category_id || filters.status ? 'Nenhum chamado com esses filtros' : 'Nenhum chamado encontrado' }}</h2>
            <p class="muted">
              {{ filters.category_id || filters.status
                ? 'Tente remover um filtro para ver outros chamados.'
                : 'Crie um chamado usando o formulário.' }}
            </p>
          </div>

          <article v-for="ticket in tickets" v-else :key="ticket.id" class="panel ticket">
            <div class="ticket-heading">
              <div>
                <p class="ticket-id">Chamado #{{ ticket.id }}</p>
                <h2>{{ ticket.title }}</h2>
              </div>
              <span class="badge" :class="`badge-${ticket.status}`">{{ statusLabel(ticket.status) }}</span>
            </div>
            <p class="ticket-description">{{ ticket.description }}</p>
            <dl class="ticket-meta">
              <div><dt>Categoria</dt><dd>{{ ticket.category?.name || `#${ticket.category_id}` }}</dd></div>
              <div><dt>Prioridade</dt><dd>{{ priorityLabel(ticket.priority) }}</dd></div>
              <div><dt>Solicitante</dt><dd>{{ ticket.user?.name || `#${ticket.user_id}` }}</dd></div>
            </dl>
            <div class="ticket-actions">
              <button class="button button-quiet" type="button" @click="editTicket(ticket)">
                Editar
              </button>
              <button
                v-if="user.role === 'admin'"
                class="button button-danger"
                type="button"
                :disabled="loading"
                @click="deleteTicket(ticket)"
              >
                Excluir
              </button>
            </div>
          </article>
        </section>
      </div>
    </template>
  </main>
</template>

<style scoped>
    .page {
      width: min(1120px, calc(100% - 40px));
      margin: 0 auto;
      padding: 28px 0 56px;
    }

    .topbar,
    .account,
    .intro,
    .ticket-heading,
    .ticket-actions,
    .form-actions {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .topbar {
      min-height: 48px;
      margin-bottom: 48px;
    }

    .brand {
      color: #1d4ed8;
      font-size: 1.2rem;
      font-weight: 800;
      letter-spacing: -0.04em;
      text-decoration: none;
    }

    .account {
      color: #475569;
      font-size: 0.95rem;
    }

    .account small,
    .muted {
      color: #64748b;
    }

    h1,
    h2,
    p {
      margin-top: 0;
    }

    h1 {
      margin-bottom: 8px;
      font-size: clamp(2rem, 5vw, 2.6rem);
      letter-spacing: -0.045em;
    }

    h2 {
      margin-bottom: 18px;
      font-size: 1.15rem;
      letter-spacing: -0.02em;
    }

    .eyebrow,
    .ticket-id {
      margin-bottom: 8px;
      color: #2563eb;
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.1em;
      text-transform: uppercase;
    }

    .panel {
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      background: #fff;
      box-shadow: 0 8px 24px rgb(15 23 42 / 4%);
    }

    .login-panel {
      width: min(440px, 100%);
      margin: 0 auto;
      padding: 32px;
    }

    .demo-access {
      display: grid;
      gap: 7px;
      margin-top: 20px;
    }

    .demo-access label {
      color: #334155;
      font-size: 0.9rem;
      font-weight: 600;
    }

    .demo-access select {
      width: 100%;
      border: 1px solid #cbd5e1;
      border-radius: 9px;
      padding: 10px 12px;
      color: #172033;
      background: #fff;
    }

    .demo-access .muted {
      margin: 0;
      font-size: 0.82rem;
    }

    .form {
      display: grid;
      gap: 16px;
      margin-top: 24px;
    }

    .form label {
      display: grid;
      gap: 7px;
      color: #334155;
      font-size: 0.9rem;
      font-weight: 600;
    }

    .form input,
    .form textarea,
    .form select {
      width: 100%;
      border: 1px solid #cbd5e1;
      border-radius: 9px;
      padding: 10px 12px;
      color: #172033;
      background: #fff;
      outline: none;
    }

    .form input:focus,
    .form textarea:focus,
    .form select:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgb(59 130 246 / 15%);
    }

    .form textarea {
      resize: vertical;
    }

    .button {
      min-height: 40px;
      border: 1px solid transparent;
      border-radius: 9px;
      padding: 8px 14px;
      font-weight: 650;
      transition: background-color 0.15s ease, border-color 0.15s ease;
    }

    .button-primary {
      color: #fff;
      background: #2563eb;
    }

    .button-primary:hover {
      background: #1d4ed8;
    }

    .button-quiet {
      border-color: #dbe3ee;
      color: #334155;
      background: #fff;
    }

    .button-quiet:hover {
      background: #f8fafc;
    }

    .button-danger {
      border-color: #fecaca;
      color: #b91c1c;
      background: #fff;
    }

    .button-danger:hover {
      background: #fef2f2;
    }

    .message {
      margin: 0 0 18px;
      border: 1px solid;
      border-radius: 10px;
      padding: 12px 14px;
    }

    .message-error {
      border-color: #fecaca;
      color: #991b1b;
      background: #fef2f2;
    }

    .message-success {
      border-color: #bbf7d0;
      color: #166534;
      background: #f0fdf4;
    }

    .loading {
      color: #475569;
      text-align: center;
    }

    .intro {
      margin-bottom: 24px;
    }

    .intro .muted,
    .login-panel .muted {
      margin-bottom: 0;
    }

    .content-grid {
      display: grid;
      grid-template-columns: minmax(0, 1.4fr) minmax(300px, 0.8fr);
      align-items: start;
      gap: 24px;
    }

    .content-grid-admin {
      grid-template-columns: minmax(300px, 0.8fr) minmax(0, 1.4fr);
    }

    .form-panel {
      padding: 24px;
    }

    .form-panel .form {
      margin-top: 0;
    }

    .field-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .field-row-single {
      grid-template-columns: 1fr;
    }

    .category-field {
      display: grid;
      gap: 10px;
      border: 0;
      padding: 0;
    }

    .category-field legend {
      margin-bottom: 10px;
      color: #334155;
      font-size: 0.9rem;
      font-weight: 600;
    }

    .category-field legend span {
      color: #64748b;
      font-size: 0.78rem;
      font-weight: 400;
    }

    .category-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
    }

    .category-card {
      min-height: 48px;
      border: 1px solid #cbd5e1;
      border-radius: 10px;
      padding: 10px 12px;
      color: #334155;
      background: #fff;
      text-align: left;
      transition: border-color 0.15s ease, background-color 0.15s ease;
    }

    .category-card:hover,
    .category-card:focus-visible {
      border-color: #3b82f6;
      outline: none;
    }

    .category-card-selected {
      border-color: #2563eb;
      color: #1d4ed8;
      background: #eff6ff;
      font-weight: 650;
    }

    .form-actions {
      justify-content: flex-start;
      flex-wrap: wrap;
    }

    .ticket-list {
      display: grid;
      gap: 14px;
    }

    .ticket-filters {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      align-items: end;
      gap: 10px;
    }

    .ticket-filters label {
      display: grid;
      gap: 6px;
      color: #475569;
      font-size: 0.82rem;
      font-weight: 600;
    }

    .ticket-filters select {
      width: 100%;
      min-width: 0;
      border: 1px solid #cbd5e1;
      border-radius: 9px;
      padding: 9px 10px;
      color: #172033;
      background: #fff;
    }

    .clear-filters {
      grid-column: 1 / -1;
      justify-self: start;
    }

    .ticket {
      padding: 20px;
    }

    .ticket-heading {
      align-items: flex-start;
    }

    .ticket-heading h2 {
      margin-bottom: 0;
    }

    .badge {
      flex: 0 0 auto;
      border-radius: 999px;
      padding: 5px 10px;
      color: #1d4ed8;
      background: #eff6ff;
      font-size: 0.75rem;
      font-weight: 700;
    }

    .badge-in_progress {
      color: #92400e;
      background: #fffbeb;
    }

    .badge-closed {
      color: #166534;
      background: #f0fdf4;
    }

    .ticket-description {
      margin: 16px 0;
      color: #475569;
      white-space: pre-wrap;
    }

    .ticket-meta {
      display: flex;
      flex-wrap: wrap;
      gap: 12px 24px;
      margin: 0;
      color: #475569;
      font-size: 0.85rem;
    }

    .ticket-meta div {
      display: grid;
      gap: 3px;
    }

    .ticket-meta dt {
      color: #94a3b8;
      font-size: 0.72rem;
      text-transform: uppercase;
    }

    .ticket-meta dd {
      margin: 0;
    }

    .ticket-actions {
      justify-content: flex-start;
      margin-top: 18px;
    }

    .empty-state {
      padding: 24px;
    }

    .empty-state h2 {
      margin-bottom: 8px;
    }

    .empty-state p {
      margin-bottom: 0;
    }

    @media (max-width: 760px) {
      .page {
        width: min(100% - 28px, 560px);
        padding-top: 16px;
      }

      .topbar {
        margin-bottom: 32px;
      }

      .content-grid {
        grid-template-columns: 1fr;
      }

      .intro {
        align-items: flex-start;
        flex-direction: column;
      }

      .login-panel {
        padding: 24px;
      }
    }

    @media (max-width: 420px) {
      .account {
        gap: 8px;
        font-size: 0.82rem;
      }

      .field-row {
        grid-template-columns: 1fr;
      }

      .category-grid,
      .ticket-filters {
        grid-template-columns: 1fr;
      }

      .clear-filters {
        grid-column: auto;
      }
    }
</style>
