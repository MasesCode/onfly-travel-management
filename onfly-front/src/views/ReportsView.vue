<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useReportsStore } from '@/stores/reports'
import type { ReportFilters } from '@/stores/reports'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import ChartComponent from '@/components/ChartComponent.vue'
import jsPDF from 'jspdf'
import 'jspdf-autotable'

const reportsStore = useReportsStore()

// Estado local
const activeTab = ref<'travels' | 'users' | 'metrics'>('travels')
const filters = ref<ReportFilters>({
  start_date: '',
  end_date: '',
  status: 'all',
  category: 'all'
})

// Computed
const isLoading = computed(() => reportsStore.isLoading)
const hasError = computed(() => reportsStore.hasError)
const error = computed(() => reportsStore.error)

// Dados de cada aba
const travelsData = computed(() => reportsStore.travels)
const usersData = computed(() => reportsStore.users)
const metricsData = computed(() => reportsStore.metrics)

// Computed para dados dos gráficos
const monthlyChartData = computed(() => {
  const trends = metricsData.value?.monthly_trends || []
  return {
    labels: trends.map(item => item.month),
    datasets: [{
      label: 'Pedidos',
      data: trends.map(item => item.travels || 0),
      borderColor: 'rgb(59, 130, 246)',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.4
    }]
  }
})

const statusChartData = computed(() => {
  if (!metricsData.value) return { labels: [], datasets: [] }

  // Criar dados de status a partir das métricas disponíveis
  const data = [
    { label: 'Ativos', count: metricsData.value.active_travels || 0 },
    { label: 'Pendentes', count: metricsData.value.pending_approvals || 0 },
    { label: 'Total', count: (metricsData.value.total_travels || 0) - (metricsData.value.active_travels || 0) - (metricsData.value.pending_approvals || 0) }
  ]

  return {
    labels: data.map(item => item.label),
    datasets: [{
      data: data.map(item => item.count),
      backgroundColor: [
        'rgba(34, 197, 94, 0.8)',  // Verde para ativos
        'rgba(251, 191, 36, 0.8)', // Amarelo para pendente
        'rgba(239, 68, 68, 0.8)'   // Vermelho para outros
      ]
    }]
  }
})

const destinationsChartData = computed(() => {
  const destinations = metricsData.value?.top_destinations || []
  return {
    labels: destinations.map(item => item.name),
    datasets: [{
      label: 'Pedidos',
      data: destinations.map(item => item.count),
      backgroundColor: 'rgba(59, 130, 246, 0.8)'
    }]
  }
})

// Opções dos gráficos
const monthlyChartOptions = {
  responsive: true,
  scales: {
    y: {
      beginAtZero: true
    }
  }
}

const statusChartOptions = {
  responsive: true,
  plugins: {
    legend: {
      position: 'bottom' as const
    }
  }
}

const destinationsChartOptions = {
  responsive: true,
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    y: {
      beginAtZero: true
    }
  }
}

// Métodos
const changeTab = (tab: 'travels' | 'users' | 'metrics') => {
  activeTab.value = tab
  loadTabData()
}

const loadTabData = async () => {
  try {
    switch (activeTab.value) {
      case 'travels':
        await reportsStore.fetchTravels(filters.value)
        break
      case 'users':
        await reportsStore.fetchUsers()
        break
      case 'metrics':
        await reportsStore.fetchMetrics()
        break
    }
  } catch (err) {
    console.error('Erro ao carregar dados da aba:', err)
  }
}

const applyFilters = () => {
  loadTabData()
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const exportTravelsPDF = () => {
  const doc = new jsPDF()
  
  // Título
  doc.setFontSize(20)
  doc.text('Relatório de Pedidos de Viagem', 20, 20)
  
  // Data do relatório
  doc.setFontSize(12)
  doc.text(`Gerado em: ${new Date().toLocaleDateString('pt-BR')} às ${new Date().toLocaleTimeString('pt-BR')}`, 20, 30)
  
  // Filtros aplicados
  let yPosition = 45
  doc.setFontSize(14)
  doc.text('Filtros Aplicados:', 20, yPosition)
  yPosition += 10
  
  doc.setFontSize(10)
  if (filters.value.start_date) {
    doc.text(`Data Inicial: ${new Date(filters.value.start_date).toLocaleDateString('pt-BR')}`, 20, yPosition)
    yPosition += 5
  }
  if (filters.value.end_date) {
    doc.text(`Data Final: ${new Date(filters.value.end_date).toLocaleDateString('pt-BR')}`, 20, yPosition)
    yPosition += 5
  }
  if (filters.value.status !== 'all') {
    const statusLabels = { 'requested': 'Solicitado', 'approved': 'Aprovado', 'cancelled': 'Cancelado' }
    doc.text(`Status: ${statusLabels[filters.value.status] || filters.value.status}`, 20, yPosition)
    yPosition += 5
  }
  
  yPosition += 10
  
  // Tabela de dados
  const tableData = travelsData.value.map(travel => [
    travel.id,
    travel.user?.name || travel.requester_name,
    travel.destination,
    new Date(travel.departure_date).toLocaleDateString('pt-BR'),
    travel.status === 'approved' ? 'Aprovado' : travel.status === 'requested' ? 'Solicitado' : 'Cancelado',
    new Date(travel.created_at).toLocaleDateString('pt-BR')
  ])
  
  ;(doc as any).autoTable({
    head: [['ID', 'Solicitante', 'Destino', 'Data Partida', 'Status', 'Data Criação']],
    body: tableData,
    startY: yPosition,
    styles: {
      fontSize: 8,
      cellPadding: 2
    },
    headStyles: {
      fillColor: [59, 130, 246],
      textColor: 255
    },
    alternateRowStyles: {
      fillColor: [245, 245, 245]
    }
  })
  
  // Resumo
  const finalY = (doc as any).lastAutoTable.finalY + 20
  doc.setFontSize(14)
  doc.text('Resumo:', 20, finalY)
  
  doc.setFontSize(10)
  doc.text(`Total de Pedidos: ${travelsData.value.length}`, 20, finalY + 10)
  doc.text(`Aprovados: ${travelsData.value.filter(t => t.status === 'approved').length}`, 20, finalY + 15)
  doc.text(`Pendentes: ${travelsData.value.filter(t => t.status === 'requested').length}`, 20, finalY + 20)
  doc.text(`Cancelados: ${travelsData.value.filter(t => t.status === 'cancelled').length}`, 20, finalY + 25)
  
  // Salvar
  const fileName = `relatorio-pedidos-${new Date().toISOString().split('T')[0]}.pdf`
  doc.save(fileName)
}

const exportUsersPDF = () => {
  const doc = new jsPDF()
  
  // Título
  doc.setFontSize(20)
  doc.text('Relatório de Usuários', 20, 20)
  
  // Data do relatório
  doc.setFontSize(12)
  doc.text(`Gerado em: ${new Date().toLocaleDateString('pt-BR')} às ${new Date().toLocaleTimeString('pt-BR')}`, 20, 30)
  
  // Tabela de dados
  const tableData = usersData.value.map(user => [
    user.name,
    user.email,
    user.total_travels,
    formatCurrency(user.total_spent),
    new Date(user.last_activity).toLocaleDateString('pt-BR')
  ])
  
  ;(doc as any).autoTable({
    head: [['Nome', 'Email', 'Total Viagens', 'Total Gasto', 'Última Atividade']],
    body: tableData,
    startY: 50,
    styles: {
      fontSize: 8,
      cellPadding: 2
    },
    headStyles: {
      fillColor: [59, 130, 246],
      textColor: 255
    },
    alternateRowStyles: {
      fillColor: [245, 245, 245]
    }
  })
  
  // Resumo
  const finalY = (doc as any).lastAutoTable.finalY + 20
  doc.setFontSize(14)
  doc.text('Resumo:', 20, finalY)
  
  doc.setFontSize(10)
  const totalUsers = usersData.value.length
  const totalSpent = usersData.value.reduce((sum, user) => sum + user.total_spent, 0)
  const totalTravels = usersData.value.reduce((sum, user) => sum + user.total_travels, 0)
  
  doc.text(`Total de Usuários: ${totalUsers}`, 20, finalY + 10)
  doc.text(`Total de Viagens: ${totalTravels}`, 20, finalY + 15)
  doc.text(`Gasto Total: ${formatCurrency(totalSpent)}`, 20, finalY + 20)
  doc.text(`Gasto Médio por Usuário: ${formatCurrency(totalSpent / totalUsers)}`, 20, finalY + 25)
  
  // Salvar
  const fileName = `relatorio-usuarios-${new Date().toISOString().split('T')[0]}.pdf`
  doc.save(fileName)
}

const exportMetricsPDF = () => {
  const doc = new jsPDF()
  
  // Título
  doc.setFontSize(20)
  doc.text('Relatório de Métricas e KPIs', 20, 20)
  
  // Data do relatório
  doc.setFontSize(12)
  doc.text(`Gerado em: ${new Date().toLocaleDateString('pt-BR')} às ${new Date().toLocaleTimeString('pt-BR')}`, 20, 30)
  
  // KPIs principais
  let yPos = 50
  doc.setFontSize(16)
  doc.text('Principais Indicadores:', 20, yPos)
  yPos += 15
  
  doc.setFontSize(12)
  doc.text(`Total de Pedidos: ${metricsData.value?.total_travels || 0}`, 20, yPos)
  yPos += 8
  doc.text(`Gasto Total Estimado: ${formatCurrency(metricsData.value?.total_spent || 0)}`, 20, yPos)
  yPos += 8
  doc.text(`Usuários Ativos: ${metricsData.value?.active_users || 0}`, 20, yPos)
  yPos += 8
  doc.text(`Média por Pedido: ${formatCurrency(metricsData.value?.average_per_travel || 0)}`, 20, yPos)
  yPos += 8
  doc.text(`Pedidos Ativos: ${metricsData.value?.active_travels || 0}`, 20, yPos)
  yPos += 8
  doc.text(`Pendentes de Aprovação: ${metricsData.value?.pending_approvals || 0}`, 20, yPos)
  yPos += 8
  doc.text(`Pedidos Este Mês: ${metricsData.value?.travels_this_month || 0}`, 20, yPos)
  yPos += 8
  doc.text(`Pedidos Mês Anterior: ${metricsData.value?.travels_last_month || 0}`, 20, yPos)
  yPos += 20
  
  // Top destinos
  if (metricsData.value?.top_destinations && metricsData.value.top_destinations.length > 0) {
    doc.setFontSize(16)
    doc.text('Top Destinos:', 20, yPos)
    yPos += 15
    
    const destinationTableData = metricsData.value.top_destinations.map(dest => [
      dest.name,
      dest.count.toString()
    ])
    
    ;(doc as any).autoTable({
      head: [['Destino', 'Quantidade']],
      body: destinationTableData,
      startY: yPos,
      styles: {
        fontSize: 10,
        cellPadding: 3
      },
      headStyles: {
        fillColor: [59, 130, 246],
        textColor: 255
      },
      alternateRowStyles: {
        fillColor: [245, 245, 245]
      }
    })
    
    yPos = (doc as any).lastAutoTable.finalY + 20
  }
  
  // Tendência mensal
  if (metricsData.value?.monthly_trends && metricsData.value.monthly_trends.length > 0) {
    doc.setFontSize(16)
    doc.text('Tendência Mensal:', 20, yPos)
    yPos += 15
    
    const monthlyTableData = metricsData.value.monthly_trends.map(trend => [
      trend.month,
      (trend.travels || 0).toString()
    ])
    
    ;(doc as any).autoTable({
      head: [['Mês', 'Pedidos']],
      body: monthlyTableData,
      startY: yPos,
      styles: {
        fontSize: 10,
        cellPadding: 3
      },
      headStyles: {
        fillColor: [59, 130, 246],
        textColor: 255
      },
      alternateRowStyles: {
        fillColor: [245, 245, 245]
      }
    })
  }
  
  // Salvar
  const fileName = `relatorio-metricas-${new Date().toISOString().split('T')[0]}.pdf`
  doc.save(fileName)
}

// Lifecycle
onMounted(() => {
  loadTabData()
})
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-white">
        📊 Relatórios - Sistema de Gestão de Viagens
      </h2>
    </template>

    <div class="min-h-screen py-12 bg-gray-50">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="space-y-6">
          <!-- Header -->
          <div class="sm:flex sm:items-center sm:justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Relatórios</h1>
              <p class="mt-1 text-sm text-gray-600">
                Visualize e analise dados de pedidos de viagem e usuários
              </p>
            </div>
            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
              <button
                v-if="activeTab === 'travels'"
                @click="exportTravelsPDF"
                :disabled="isLoading || travelsData.length === 0"
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Exportar PDF
              </button>
              <button
                v-else-if="activeTab === 'users'"
                @click="exportUsersPDF"
                :disabled="isLoading || usersData.length === 0"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Exportar PDF
              </button>
              <button
                v-else-if="activeTab === 'metrics'"
                @click="exportMetricsPDF"
                :disabled="isLoading || !metricsData"
                class="inline-flex items-center px-4 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-700 active:bg-purple-900 focus:outline-none focus:border-purple-900 focus:ring ring-purple-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Exportar PDF
              </button>
            </div>
          </div>

          <!-- Error message -->
          <div v-if="hasError" class="rounded-md bg-red-50 p-4">
            <div class="flex">
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Erro ao carregar dados
                </h3>
                <div class="mt-2 text-sm text-red-700">
                  {{ error }}
                </div>
                <div class="mt-4">
                  <button
                    @click="reportsStore.clearError(); applyFilters()"
                    class="bg-red-100 px-2 py-1.5 rounded-md text-sm font-medium text-red-800 hover:bg-red-200"
                  >
                    Tentar novamente
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Tabs -->
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
              <button
                v-for="tab in [
                  { id: 'travels', name: '✈️ Pedidos' },
                  { id: 'users', name: '👥 Usuários' },
                  { id: 'metrics', name: '📊 Métricas' }
                ]"
                :key="tab.id"
                @click="changeTab(tab.id)"
                :class="[
                  activeTab === tab.id
                    ? 'border-indigo-500 text-indigo-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                {{ tab.name }}
              </button>
            </nav>
          </div>

          <!-- Filters -->
          <div v-if="activeTab === 'travels'" class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros</h3>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <!-- Date range -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Data inicial</label>
                <input
                  v-model="filters.start_date"
                  type="date"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700">Data final</label>
                <input
                  v-model="filters.end_date"
                  type="date"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                />
              </div>

              <!-- Status filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select
                  v-model="filters.status"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >
                  <option value="all">Todos</option>
                  <option value="requested">Solicitado</option>
                  <option value="approved">Aprovado</option>
                  <option value="cancelled">Cancelado</option>
                </select>
              </div>

              <div>
                <button
                  @click="applyFilters"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  :disabled="isLoading"
                >
                  🔍 Aplicar Filtros
                </button>
              </div>
            </div>
          </div>

          <!-- Loading state -->
          <div v-if="isLoading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
            <p class="mt-2 text-sm text-gray-500">Carregando dados...</p>
          </div>

          <!-- Content based on active tab -->
          <div v-else class="bg-white shadow rounded-lg">
            <!-- Travels Tab -->
            <div v-if="activeTab === 'travels'" class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Relatório de Pedidos de Viagem</h3>

              <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Pedido
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Solicitante
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Destino
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data de Partida
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Criado em
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="travel in travelsData" :key="travel.id">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        Pedido #{{ travel.id }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ travel.user?.name || travel.requester_name }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ travel.destination }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ new Date(travel.departure_date).toLocaleDateString('pt-BR') }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          travel.status === 'approved' ? 'bg-green-100 text-green-800' :
                          travel.status === 'requested' ? 'bg-yellow-100 text-yellow-800' :
                          'bg-red-100 text-red-800'
                        ]">
                          {{ travel.status === 'approved' ? 'Aprovado' :
                             travel.status === 'requested' ? 'Solicitado' : 'Cancelado' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ new Date(travel.created_at).toLocaleDateString('pt-BR') }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Users Tab -->
            <div v-else-if="activeTab === 'users'" class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Relatório de Usuários</h3>

              <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nome
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Viagens
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Total Gasto
                      </th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Última Atividade
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="user in usersData" :key="user.id">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ user.name }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ user.email }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ user.total_travels }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        R$ {{ user.total_spent.toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ new Date(user.last_activity).toLocaleDateString('pt-BR') }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Metrics Tab -->
            <div v-else-if="activeTab === 'metrics'" class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-6">Métricas e KPIs</h3>

              <!-- Cards de métricas -->
              <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- Total Travels -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <div class="text-2xl">✈️</div>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">Total de Pedidos</dt>
                          <dd class="text-lg font-medium text-gray-900">{{ metricsData?.total_travels || 0 }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Total Expenses -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <div class="text-2xl">💰</div>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">Gasto Estimado</dt>
                          <dd class="text-lg font-medium text-gray-900">
                            R$ {{ (metricsData?.total_spent || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                          </dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Active Users -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <div class="text-2xl">👥</div>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">Usuários Ativos</dt>
                          <dd class="text-lg font-medium text-gray-900">{{ metricsData?.active_users || 0 }}</dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Growth Rate -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                  <div class="p-5">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <div class="text-2xl">📈</div>
                      </div>
                      <div class="ml-5 w-0 flex-1">
                        <dl>
                          <dt class="text-sm font-medium text-gray-500 truncate">Média por Pedido</dt>
                          <dd class="text-lg font-medium text-gray-900">
                            R$ {{ (metricsData?.average_per_travel || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                          </dd>
                        </dl>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Gráficos -->
              <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Gráfico de tendência mensal -->
                <div class="bg-white shadow rounded-lg p-6">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">Pedidos por Mês</h4>
                  <ChartComponent
                    v-if="monthlyChartData.labels.length > 0"
                    type="line"
                    :data="monthlyChartData"
                    :options="monthlyChartOptions"
                  />
                  <div v-else class="h-64 flex items-center justify-center text-gray-500">
                    Carregando dados...
                  </div>
                </div>

                <!-- Gráfico de distribuição por status -->
                <div class="bg-white shadow rounded-lg p-6">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">Distribuição por Status</h4>
                  <ChartComponent
                    v-if="statusChartData.labels.length > 0"
                    type="doughnut"
                    :data="statusChartData"
                    :options="statusChartOptions"
                  />
                  <div v-else class="h-64 flex items-center justify-center text-gray-500">
                    Carregando dados...
                  </div>
                </div>

                <!-- Top destinos -->
                <div class="bg-white shadow rounded-lg p-6">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">Top Destinos</h4>
                  <ChartComponent
                    v-if="destinationsChartData.labels.length > 0"
                    type="bar"
                    :data="destinationsChartData"
                    :options="destinationsChartOptions"
                  />
                  <div v-else class="h-64 flex items-center justify-center text-gray-500">
                    Carregando dados...
                  </div>
                </div>

                <!-- Estatísticas de aprovação -->
                <div class="bg-white shadow rounded-lg p-6">
                  <h4 class="text-lg font-medium text-gray-900 mb-4">Estatísticas Gerais</h4>
                  <div class="space-y-4">
                    <div class="flex justify-between">
                      <span class="text-sm font-medium text-gray-500">Pedidos Ativos</span>
                      <span class="text-sm text-gray-900">{{ metricsData?.active_travels || 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm font-medium text-gray-500">Pendentes de Aprovação</span>
                      <span class="text-sm text-gray-900">{{ metricsData?.pending_approvals || 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm font-medium text-gray-500">Este Mês</span>
                      <span class="text-sm text-gray-900">{{ metricsData?.travels_this_month || 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm font-medium text-gray-500">Mês Anterior</span>
                      <span class="text-sm text-gray-900">{{ metricsData?.travels_last_month || 0 }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-sm font-medium text-gray-500">Média por Pedido</span>
                      <span class="text-sm text-gray-900">
                        R$ {{ (metricsData?.average_per_travel || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
/* Custom styles if needed */
</style>
