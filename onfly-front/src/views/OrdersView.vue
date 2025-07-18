<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import CreateOrderModal from '../components/orders/CreateOrderModal.vue'
import EditOrderModal from '../components/orders/EditOrderModal.vue'
import DeleteOrderModal from '../components/orders/DeleteOrderModal.vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import api from '@/services/api'
import type { Order } from '../types/index'

const orders = ref<Order[]>([])
const allOrders = ref<Order[]>([])
const loading = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedOrder = ref<Order | null>(null)
const deleteLoading = ref(false)

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

const fetchOrders = async () => {
  loading.value = true
  try {
    const response = await api.get('/orders')
    allOrders.value = response.data.data || response.data
    applyFilters()
  } catch (error) {
    console.error('Erro ao carregar pedidos:', error)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  let filtered = [...allOrders.value]

  if (filters.value.status) {
    filtered = filtered.filter(order => order.status === filters.value.status)
  }

  if (filters.value.destination) {
    filtered = filtered.filter(order => 
      order.destination.toLowerCase().includes(filters.value.destination.toLowerCase())
    )
  }

  if (filters.value.startDate) {
    filtered = filtered.filter(order => 
      new Date(order.start_date) >= new Date(filters.value.startDate)
    )
  }

  if (filters.value.endDate) {
    filtered = filtered.filter(order => 
      new Date(order.end_date) <= new Date(filters.value.endDate)
    )
  }

  orders.value = filtered
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

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR')
}

const formatCurrency = (value: number | string) => {
  const num = typeof value === 'string' ? parseFloat(value) : value
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(num)
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
    await api.post('/orders', orderData)
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
    await api.put(`/orders/${selectedOrder.value.id}`, orderData)
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
  await fetchOrders()
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
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select
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
                <label class="block text-sm font-medium text-gray-700">Destino</label>
                <input
                  v-model="filters.destination"
                  @input="applyFilters"
                  type="text"
                  placeholder="Filtrar por destino..."
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Data Início (de)</label>
                <input
                  v-model="filters.startDate"
                  @change="applyFilters"
                  type="date"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Data Fim (até)</label>
                <input
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
                Mostrando <span class="font-semibold">{{ orders.length }}</span> de <span class="font-semibold">{{ allOrders.length }}</span> pedidos
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
