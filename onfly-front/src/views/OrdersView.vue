<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import CreateOrderModal from '../components/orders/CreateOrderModal.vue'
import EditOrderModal from '../components/orders/EditOrderModal.vue'
import DeleteOrderModal from '../components/orders/DeleteOrderModal.vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import api from '../services/api'
import type { Order } from '../types/index'

interface PaginationData {
  current_page: number
  data: Order[]
  from: number
  last_page: number
  per_page: number
  to: number
  total: number
}

const orders = ref<Order[]>([])
const allOrders = ref<Order[]>([])
const loading = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedOrder = ref<Order | null>(null)
const deleteLoading = ref(false)

// Paginação
const currentPage = ref(1)
const totalPages = ref(1)
const totalOrders = ref(0)
const perPage = ref(10)
const fromItem = ref(0)
const toItem = ref(0)

// Filtros
const filters = ref({
  status: '',
  destination: '',
  startDate: '',
  endDate: ''
})

const statusLabels: Record<string, string> = {
  'requested': 'Solicitado',
  'approved': 'Aprovado',
  'cancelled': 'Cancelado',
}

const statusColors: Record<string, string> = {
  'requested': 'bg-yellow-100 text-yellow-800',
  'approved': 'bg-green-100 text-green-800',
  'cancelled': 'bg-red-100 text-red-800'
}

const fetchOrders = async (page: number = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: perPage.value.toString()
    })

    // Adicionar filtros apenas se tiverem valor
    if (filters.value.status) {
      params.append('status', filters.value.status)
    }
    if (filters.value.destination) {
      params.append('destination', filters.value.destination)
    }
    if (filters.value.startDate) {
      params.append('start_date', filters.value.startDate)
    }
    if (filters.value.endDate) {
      params.append('end_date', filters.value.endDate)
    }

    const response = await api.get(`/orders?${params}`)
    const paginationData: PaginationData = response.data

    orders.value = paginationData.data
    currentPage.value = paginationData.current_page
    totalPages.value = paginationData.last_page
    totalOrders.value = paginationData.total
    fromItem.value = paginationData.from || 0
    toItem.value = paginationData.to || 0

    // Para manter compatibilidade com o PDF export, vamos buscar todos os pedidos filtrados
    // apenas quando necessário (na função de exportação)
    allOrders.value = orders.value
  } catch (error) {
    console.error('Erro ao carregar pedidos:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  // Resetar para a primeira página quando aplicar filtros
  currentPage.value = 1
  fetchOrders(1)
}

const clearFilters = () => {
  filters.value = {
    status: '',
    destination: '',
    startDate: '',
    endDate: ''
  }
  applyFilters()
}

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchOrders(page)
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    goToPage(currentPage.value + 1)
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    goToPage(currentPage.value - 1)
  }
}

// Computed para gerar array de páginas para navegação
const pageNumbers = computed(() => {
  const delta = 2
  const range = []
  const rangeWithDots = []

  for (let i = Math.max(2, currentPage.value - delta); i <= Math.min(totalPages.value - 1, currentPage.value + delta); i++) {
    range.push(i)
  }

  if (currentPage.value - delta > 2) {
    rangeWithDots.push(1, '...')
  } else {
    rangeWithDots.push(1)
  }

  rangeWithDots.push(...range)

  if (currentPage.value + delta < totalPages.value - 1) {
    rangeWithDots.push('...', totalPages.value)
  } else {
    rangeWithDots.push(totalPages.value)
  }

  return rangeWithDots.filter((item, index, array) => array.indexOf(item) === index && item !== 1 || index === 0)
})

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const editOrder = (order: Order) => {
  selectedOrder.value = order
  showEditModal.value = true
}

const deleteOrder = (order: Order) => {
  selectedOrder.value = order
  showDeleteModal.value = true
}

const handleOrderCreated = async (orderData: { destination: string; start_date: string; end_date: string }) => {
  try {
    // Converter os campos para os nomes esperados pelo backend
    const backendData = {
      destination: orderData.destination,
      departure_date: orderData.start_date,
      return_date: orderData.end_date
    }

    await api.post('/orders', backendData)
    showCreateModal.value = false
    await fetchOrders()
    // Atualizar notificações se necessário
  } catch (error) {
    console.error('Erro ao criar pedido:', error)
  }
}

const handleOrderUpdated = async (orderData: { destination: string; start_date: string; end_date: string }) => {
  if (!selectedOrder.value) return

  try {
    // Converter os campos para os nomes esperados pelo backend
    const backendData = {
      destination: orderData.destination,
      departure_date: orderData.start_date,
      return_date: orderData.end_date
    }

    await api.put(`/orders/${selectedOrder.value.id}`, backendData)
    showEditModal.value = false
    selectedOrder.value = null
    await fetchOrders()
    // Atualizar notificações se necessário
  } catch (error) {
    console.error('Erro ao atualizar pedido:', error)
  }
}

const handleOrderDeleted = async () => {
  if (!selectedOrder.value) return

  deleteLoading.value = true
  try {
    await api.delete(`/orders/${selectedOrder.value.id}`)
    showDeleteModal.value = false
    selectedOrder.value = null
    await fetchOrders()
    // Atualizar notificações se necessário
  } catch (error) {
    console.error('Erro ao excluir pedido:', error)
  } finally {
    deleteLoading.value = false
  }
}

const exportToPDF = () => {
  const doc = new jsPDF()

  // Configuração da fonte
  doc.setFont('helvetica')

  // Cabeçalho do documento
  doc.setFontSize(20)
  doc.text('Relatório de Pedidos de Viagem', 20, 20)

  // Informações do filtro
  doc.setFontSize(10)
  let yPosition = 40

  doc.text('Filtros aplicados:', 20, yPosition)
  yPosition += 7

  if (filters.value.status) {
    doc.text(`• Status: ${statusLabels[filters.value.status]}`, 25, yPosition)
    yPosition += 5
  }

  if (filters.value.destination) {
    doc.text(`• Destino: ${filters.value.destination}`, 25, yPosition)
    yPosition += 5
  }

  if (filters.value.startDate) {
    doc.text(`• Data início: ${formatDate(filters.value.startDate)}`, 25, yPosition)
    yPosition += 5
  }

  if (filters.value.endDate) {
    doc.text(`• Data fim: ${formatDate(filters.value.endDate)}`, 25, yPosition)
    yPosition += 5
  }

  // Resumo
  doc.setFontSize(12)
  yPosition += 10
  doc.text(`Total de pedidos: ${orders.value.length}`, 20, yPosition)

  // Tabela com os dados
  const tableData = orders.value.map(order => [
    `#${order.id}`,
    order.destination,
    `${formatDate(order.start_date)} - ${formatDate(order.end_date)}`,
    statusLabels[order.status]
  ])

  autoTable(doc, {
    head: [['ID', 'Destino', 'Período', 'Status']],
    body: tableData,
    startY: yPosition + 20,
    styles: {
      fontSize: 8,
      cellPadding: 3
    },
    headStyles: {
      fillColor: [37, 99, 235], // Azul
      textColor: 255,
      fontStyle: 'bold'
    },
    alternateRowStyles: {
      fillColor: [248, 250, 252] // Cinza claro
    },
    margin: { top: 20, right: 20, bottom: 20, left: 20 }
  })

  // Rodapé
  const pageCount = doc.getNumberOfPages()
  for (let i = 1; i <= pageCount; i++) {
    doc.setPage(i)
    doc.setFontSize(8)
    doc.text(
      `Página ${i} de ${pageCount} - Gerado em ${new Date().toLocaleString('pt-BR')}`,
      20,
      doc.internal.pageSize.height - 10
    )
    doc.text(
      'OnFly - Sistema de Gestão de Viagens',
      doc.internal.pageSize.width - 20,
      doc.internal.pageSize.height - 10,
      { align: 'right' }
    )
  }

  // Download do PDF
  const fileName = `pedidos-viagem-${new Date().toISOString().split('T')[0]}.pdf`
  doc.save(fileName)
}

onMounted(async () => {
  await fetchOrders(1)
})
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-white">
          Pedidos de Viagem
        </h2>
        <div>
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Novo Pedido
          </button>
        </div>
      </div>
    </template>

    <div class="min-h-screen py-12 bg-gray-50">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Filtros -->
        <div class="mb-6 overflow-hidden bg-white rounded-lg shadow">
          <div class="px-6 py-4 bg-gray-50 border-b">
            <h3 class="text-lg font-medium text-gray-900">Filtros</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-2">
              <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700">Status</label>
                <select
                  id="status-filter"
                  v-model="filters.status"
                  @change="applyFilters"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option value="">Todos</option>
                  <option value="requested">Solicitado</option>
                  <option value="approved">Aprovado</option>
                  <option value="cancelled">Cancelado</option>
                </select>
              </div>

              <div>
                <label for="destination-filter" class="block text-sm font-medium text-gray-700">Destino</label>
                <input
                  id="destination-filter"
                  v-model="filters.destination"
                  @input="applyFilters"
                  type="text"
                  placeholder="Filtrar por destino..."
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
              </div>

              <div>
                <label for="start-date-filter" class="block text-sm font-medium text-gray-700">Data Início (de)</label>
                <input
                  id="start-date-filter"
                  v-model="filters.startDate"
                  @change="applyFilters"
                  type="date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
              </div>

              <div>
                <label for="end-date-filter" class="block text-sm font-medium text-gray-700">Data Fim (até)</label>
                <input
                  id="end-date-filter"
                  v-model="filters.endDate"
                  @change="applyFilters"
                  type="date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
              </div>
            </div>

            <div class="flex justify-end space-x-4 mt-4">
              <button
                @click="exportToPDF"
                :disabled="orders.length === 0"
                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-900 focus:outline-none focus:border-red-900 focus:ring ring-red-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Exportar PDF
              </button>

              <button
                @click="clearFilters"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                Limpar Filtros
              </button>
            </div>
          </div>
        </div>

        <!-- Resumo dos Resultados -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-blue-700">
                Mostrando <span class="font-semibold">{{ fromItem }}</span> a <span class="font-semibold">{{ toItem }}</span> de <span class="font-semibold">{{ totalOrders }}</span> pedidos
              </p>
            </div>
          </div>
        </div>

        <!-- Tabela de Pedidos -->
        <div class="overflow-hidden bg-white rounded-lg shadow">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destino</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Período</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-if="loading">
                  <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    Carregando...
                  </td>
                </tr>
                <tr v-else-if="orders.length === 0">
                  <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    Nenhum pedido encontrado com os filtros aplicados
                  </td>
                </tr>
                <tr v-else v-for="order in orders" :key="order.id" class="hover:bg-gray-50 cursor-pointer" @click="editOrder(order)">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    #{{ order.id }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ order.destination }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(order.start_date) }} - {{ formatDate(order.end_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="statusColors[order.status]"
                    >
                      {{ statusLabels[order.status] }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2" @click.stop>
                      <button
                        @click="editOrder(order)"
                        class="text-blue-600 hover:text-blue-900 p-1"
                        title="Editar"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </button>
                      <button
                        @click="deleteOrder(order)"
                        class="text-red-600 hover:text-red-900 p-1"
                        title="Excluir"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paginação -->
        <div v-if="totalPages > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 rounded-lg shadow mt-4">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="prevPage"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            <button
              @click="nextPage"
              :disabled="currentPage === totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Próxima
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Mostrando <span class="font-medium">{{ fromItem }}</span> a <span class="font-medium">{{ toItem }}</span> de
                <span class="font-medium">{{ totalOrders }}</span> resultados
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button
                  @click="prevPage"
                  :disabled="currentPage === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="sr-only">Anterior</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>

                <template v-for="page in pageNumbers">
                  <button
                    v-if="page !== '...'"
                    :key="`page-${page}`"
                    @click="goToPage(page)"
                    :class="[
                      page === currentPage
                        ? 'bg-blue-50 border-blue-500 text-blue-600 z-10'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                    ]"
                  >
                    {{ page }}
                  </button>
                  <span
                    v-else
                    :key="`dots-${page}`"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                  >
                    ...
                  </span>
                </template>

                <button
                  @click="nextPage"
                  :disabled="currentPage === totalPages"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span class="sr-only">Próxima</span>
                  <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modais -->
    <CreateOrderModal
      :show="showCreateModal"
      @close="showCreateModal = false"
      @created="handleOrderCreated"
    />

    <EditOrderModal
      :show="showEditModal"
      :order="selectedOrder"
      @close="showEditModal = false"
      @updated="handleOrderUpdated"
    />

    <DeleteOrderModal
      :show="showDeleteModal"
      :order="selectedOrder"
      :is-loading="deleteLoading"
      @close="showDeleteModal = false"
      @confirm="handleOrderDeleted"
    />
  </AuthenticatedLayout>
</template>
