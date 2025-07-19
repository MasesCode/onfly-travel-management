<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useRouter } from 'vue-router'
import { useNotifications } from '../../composables/useNotifications'
import api from '../../services/api'
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout.vue'
import LogDetailModal from '../../components/logs/LogDetailModal.vue'

interface ActivityLog {
  id: number
  log_name: string
  description: string
  subject_type: string
  subject_id: number
  causer_type: string
  causer_id: number
  properties: Record<string, any>
  created_at: string
  updated_at: string
  subject: any
  causer: {
    id: number
    name: string
    email: string
  } | null
}

interface PaginatedLogs {
  data: ActivityLog[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
}

const authStore = useAuthStore()
const router = useRouter()
const { showError } = useNotifications()

const logs = ref<ActivityLog[]>([])
const loading = ref(false)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})

const filters = ref({
  log_name: '',
  causer_id: '',
  date_from: '',
  date_to: ''
})

const logNames = ref<string[]>([])
const selectedLog = ref<ActivityLog | null>(null)
const showLogModal = ref(false)

const isAdmin = computed(() => authStore.isAdmin)

onMounted(async () => {
  if (!isAdmin.value) {
    showError('Acesso negado. Apenas administradores podem acessar esta página.')
    router.push('/dashboard')
    return
  }

  await Promise.all([
    fetchLogs(),
    fetchLogNames()
  ])
})

const fetchLogs = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      ...filters.value
    })

    const response = await api.get(`/admin/activity-logs?${params}`)
    const data: PaginatedLogs = response.data

    logs.value = data.data
    pagination.value = {
      current_page: data.current_page,
      last_page: data.last_page,
      per_page: data.per_page,
      total: data.total,
      from: data.from,
      to: data.to
    }
  } catch (error) {
    showError('Erro ao carregar logs de atividade')
  } finally {
    loading.value = false
  }
}

const fetchLogNames = async () => {
  try {
    const response = await api.get('/admin/activity-logs-names')
    logNames.value = response.data
  } catch (error) {
    console.error('Erro ao carregar tipos de log:', error)
  }
}

const applyFilters = () => {
  fetchLogs(1)
}

const clearFilters = () => {
  filters.value = {
    log_name: '',
    causer_id: '',
    date_from: '',
    date_to: ''
  }
  fetchLogs(1)
}

const changePage = (page: number) => {
  fetchLogs(page)
}

const viewLogDetails = async (log: ActivityLog) => {
  try {
    const response = await api.get(`/admin/activity-logs/${log.id}`)
    selectedLog.value = response.data
    showLogModal.value = true
  } catch (error) {
    showError('Erro ao carregar detalhes do log')
  }
}

const closeLogModal = () => {
  showLogModal.value = false
  selectedLog.value = null
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getLogTypeColor = (logName: string) => {
  const colors: Record<string, string> = {
    'user': 'bg-blue-100 text-blue-800',
    'order': 'bg-green-100 text-green-800',
    'travel': 'bg-purple-100 text-purple-800',
    'default': 'bg-gray-100 text-gray-800'
  }
  return colors[logName] || colors.default
}

const getActionColor = (description: string) => {
  if (description.includes('created')) return 'text-green-600'
  if (description.includes('updated')) return 'text-blue-600'
  if (description.includes('deleted')) return 'text-red-600'
  return 'text-gray-600'
}

const translateLogType = (logName: string) => {
  const translations: Record<string, string> = {
    'user': 'Usuário',
    'order': 'Pedido',
    'travel': 'Viagem',
    'default': 'Sistema'
  }
  return translations[logName] || logName.charAt(0).toUpperCase() + logName.slice(1)
}

const translateDescription = (description: string) => {
  const translations: Record<string, string> = {
    'Created order': 'Criou pedido',
    'Updated order': 'Atualizou pedido',
    'Updated order status': 'Alterou status do pedido',
    'Deleted order': 'Excluiu pedido',

    'Created user': 'Criou usuário',
    'Updated user': 'Atualizou usuário',
    'Deleted user': 'Excluiu usuário',

    'Created travel': 'Criou viagem',
    'Updated travel': 'Atualizou viagem',
    'Deleted travel': 'Excluiu viagem',

    'Login': 'Fez login',
    'Logout': 'Fez logout',
    'Register': 'Se registrou',

    'created order': 'Criou pedido',
    'updated order': 'Atualizou pedido',
    'updated order status': 'Alterou status do pedido',
    'deleted order': 'Excluiu pedido',
    'created user': 'Criou usuário',
    'updated user': 'Atualizou usuário',
    'deleted user': 'Excluiu usuário',
    'created travel': 'Criou viagem',
    'updated travel': 'Atualizou viagem',
    'deleted travel': 'Excluiu viagem',
    'login': 'Fez login',
    'logout': 'Fez logout',
    'register': 'Se registrou'
  }
  return translations[description] || description
}

const translateSubjectType = (subjectType: string) => {
  const translations: Record<string, string> = {
    'Order': 'Pedido',
    'User': 'Usuário',
    'Travel': 'Viagem'
  }
  const type = subjectType.split('\\').pop() || subjectType
  return translations[type] || type
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-white">
          Logs de Atividade
        </h2>
        <div class="flex items-center space-x-2 text-sm text-white/90">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Apenas administradores</span>
        </div>
      </div>
    </template>

    <div class="min-h-screen py-12 bg-gray-50">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Filtros -->
        <div class="mb-6 overflow-hidden bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">
              <div>
                <label for="log-type-filter" class="block text-sm font-medium text-gray-700">Tipo de Log</label>
                <select
                  id="log-type-filter"
                  v-model="filters.log_name"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="">Todos</option>
                  <option v-for="logName in logNames" :key="logName" :value="logName">
                    {{ translateLogType(logName) }}
                  </option>
                </select>
              </div>

              <div>
                <label for="date-from-filter" class="block text-sm font-medium text-gray-700">Data Inicial</label>
                <input
                  id="date-from-filter"
                  v-model="filters.date_from"
                  type="date"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>

              <div>
                <label for="date-to-filter" class="block text-sm font-medium text-gray-700">Data Final</label>
                <input
                  id="date-to-filter"
                  v-model="filters.date_to"
                  type="date"
                  class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
              </div>

              <div class="flex items-end space-x-4">
                <button
                  @click="applyFilters"
                  class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25"
                >
                  Filtrar
                </button>
                <button
                  @click="clearFilters"
                  class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-600 border border-transparent rounded-md hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25"
                >
                  Limpar Filtros
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Resumo dos Resultados -->
        <div class="p-4 mb-6 border border-blue-200 rounded-lg bg-blue-50">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-blue-700">
                Mostrando <span class="font-semibold">{{ pagination.from }}</span> a <span class="font-semibold">{{ pagination.to }}</span> de <span class="font-semibold">{{ pagination.total }}</span> logs
              </p>
            </div>
          </div>
        </div>

        <!-- Tabela de Logs -->
        <div class="overflow-hidden bg-white rounded-lg shadow">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Tipo</th>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Descrição</th>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Usuário</th>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data</th>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Ações</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="loading">
                  <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    <div class="inline-flex items-center">
                      <svg class="w-5 h-5 mr-3 -ml-1 text-gray-500 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Carregando logs...
                    </div>
                  </td>
                </tr>
                <tr v-else-if="logs.length === 0">
                  <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    Nenhum log encontrado com os filtros aplicados
                  </td>
                </tr>
                <tr v-else v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getLogTypeColor(log.log_name)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                      {{ translateLogType(log.log_name) }}
                    </span>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">
                      <span :class="getActionColor(log.description)" class="font-medium">
                        {{ translateDescription(log.description) }}
                      </span>
                    </div>
                    <div v-if="log.subject_type" class="mt-1 text-xs text-gray-500">
                      {{ translateSubjectType(log.subject_type) }} #{{ log.subject_id }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="log.causer" class="text-sm text-gray-900">
                      <div class="font-medium">{{ log.causer.name }}</div>
                      <div class="text-gray-500">{{ log.causer.email }}</div>
                    </div>
                    <div v-else class="text-sm text-gray-500">
                      Sistema
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                    {{ formatDate(log.created_at) }}
                  </td>
                  <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                    <button
                      @click="viewLogDetails(log)"
                      class="p-1 text-blue-600 hover:text-blue-900"
                      title="Ver detalhes"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paginação -->
        <div v-if="pagination.last_page > 1" class="flex items-center justify-between px-4 py-3 mt-4 bg-white border-t border-gray-200 rounded-lg shadow sm:px-6">
          <div class="flex justify-between flex-1 sm:hidden">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Próximo
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Mostrando <span class="font-medium">{{ pagination.from }}</span> a <span class="font-medium">{{ pagination.to }}</span> de
                <span class="font-medium">{{ pagination.total }}</span> resultados
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <button
                  @click="changePage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="sr-only">Anterior</span>
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>

                <button
                  v-for="page in Math.min(pagination.last_page, 7)"
                  :key="`page-${page}`"
                  @click="changePage(page)"
                  :class="[
                    page === pagination.current_page
                      ? 'bg-blue-50 border-blue-500 text-blue-600 z-10'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                  ]"
                >
                  {{ page }}
                </button>

                <button
                  @click="changePage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="sr-only">Próximo</span>
                  <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Detalhes do Log -->
    <LogDetailModal
      :show="showLogModal"
      :log="selectedLog"
      @close="closeLogModal"
    />
  </AuthenticatedLayout>
</template>
