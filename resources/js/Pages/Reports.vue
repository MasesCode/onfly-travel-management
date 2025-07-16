<template>
    <Head title="Relatórios" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                Relatórios e Analytics
            </h2>
        </template>

        <div class="min-h-screen py-12 bg-gray-50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filtros de Período -->
                <div class="mb-8">
                    <div class="overflow-hidden bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Filtros</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data Início</label>
                                    <input
                                        v-model="filters.startDate"
                                        type="date"
                                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data Fim</label>
                                    <input
                                        v-model="filters.endDate"
                                        type="date"
                                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <select
                                        v-model="filters.status"
                                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Todos</option>
                                        <option value="requested">Solicitado</option>
                                        <option value="approved">Aprovado</option>
                                        <option value="cancelled">Cancelado</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button
                                        @click="generateReport"
                                        :disabled="loading"
                                        class="w-full px-4 py-2 text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                    >
                                        {{ loading ? 'Carregando...' : 'Gerar Relatório' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cards de Estatísticas -->
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-4">
                    <div class="overflow-hidden bg-white border-l-4 border-blue-500 rounded-lg shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 w-0 ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total de Pedidos</dt>
                                        <dd class="text-3xl font-bold text-gray-900">{{ stats.total }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white border-l-4 border-green-500 rounded-lg shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-full">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 w-0 ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Aprovados</dt>
                                        <dd class="text-3xl font-bold text-gray-900">{{ stats.approved }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white border-l-4 border-yellow-500 rounded-lg shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-8 h-8 bg-yellow-100 rounded-full">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 w-0 ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Pendentes</dt>
                                        <dd class="text-3xl font-bold text-gray-900">{{ stats.requested }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white border-l-4 border-red-500 rounded-lg shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-8 h-8 bg-red-100 rounded-full">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 w-0 ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Cancelados</dt>
                                        <dd class="text-3xl font-bold text-gray-900">{{ stats.cancelled }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos e Tabelas -->
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <!-- Destinos Mais Visitados -->
                    <div class="overflow-hidden bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Destinos Mais Visitados</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div v-for="destination in topDestinations" :key="destination.name" class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="w-4 h-4 mr-3 bg-blue-500 rounded-full"></div>
                                        <span class="text-sm font-medium text-gray-900">{{ destination.name }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-32 h-2 mr-3 bg-gray-200 rounded-full">
                                            <div
                                                class="h-2 bg-blue-500 rounded-full"
                                                :style="{ width: `${(destination.count / stats.total) * 100}%` }"
                                            ></div>
                                        </div>
                                        <span class="w-8 text-sm text-right text-gray-600">{{ destination.count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pedidos por Mês -->
                    <div class="overflow-hidden bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Pedidos por Mês</h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div v-for="month in monthlyData" :key="month.month" class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <span class="w-20 text-sm font-medium text-gray-900">{{ month.month }}</span>
                                    </div>
                                    <div class="flex items-center">
                                        <div class="w-32 h-2 mr-3 bg-gray-200 rounded-full">
                                            <div
                                                class="h-2 bg-green-500 rounded-full"
                                                :style="{ width: `${(month.count / maxMonthly) * 100}%` }"
                                            ></div>
                                        </div>
                                        <span class="w-8 text-sm text-right text-gray-600">{{ month.count }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela Detalhada -->
                <div class="mt-8 overflow-hidden bg-white rounded-lg shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Detalhamento dos Pedidos</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Passageiro</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Destino</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data Ida</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data Volta</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Criado em</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="order in orders" :key="order.id" class="transition-colors duration-200 hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ order.requester }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ order.destination }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ moment(order.start_date).format('DD/MM/YYYY') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ moment(order.end_date).format('DD/MM/YYYY') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-yellow-100 text-yellow-800': order.status === 'requested',
                                            'bg-green-100 text-green-800': order.status === 'approved',
                                            'bg-red-100 text-red-800': order.status === 'cancelled'
                                        }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ statusLabels[order.status] || order.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ moment(order.created_at).format('DD/MM/YYYY') }}</div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Botão de Exportação -->
                <div class="flex justify-end mt-8">
                    <button
                        @click="exportReport"
                        :disabled="loading"
                        class="px-6 py-2 text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                    >
                        <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Exportar Relatório
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import OrderService, { Order } from '@/Services/OrderService';
import moment from 'moment';

moment.locale('pt-br');

const orders = ref<Order[]>([]);
const loading = ref(false);
const error = ref<string | null>(null);

const statusLabels: { [key: string]: string } = {
  'requested': 'Solicitado',
  'approved': 'Aprovado',
  'cancelled': 'Cancelado'
};

const filters = ref({
  startDate: moment().subtract(30, 'days').format('YYYY-MM-DD'),
  endDate: moment().format('YYYY-MM-DD'),
  status: ''
});

const stats = computed(() => {
  const total = orders.value.length;
  const approved = orders.value.filter(order => order.status === 'approved').length;
  const requested = orders.value.filter(order => order.status === 'requested').length;
  const cancelled = orders.value.filter(order => order.status === 'cancelled').length;

  return { total, approved, requested, cancelled };
});

const topDestinations = computed(() => {
  const destinationCount: { [key: string]: number } = {};

  orders.value.forEach(order => {
    destinationCount[order.destination] = (destinationCount[order.destination] || 0) + 1;
  });

  return Object.entries(destinationCount)
    .map(([name, count]) => ({ name, count }))
    .sort((a, b) => b.count - a.count)
    .slice(0, 5);
});

const monthlyData = computed(() => {
  const monthCount: { [key: string]: number } = {};

  orders.value.forEach(order => {
    const month = moment(order.start_date).format('MM/YYYY');
    monthCount[month] = (monthCount[month] || 0) + 1;
  });

  return Object.entries(monthCount)
    .map(([month, count]) => ({ month, count }))
    .sort((a, b) => a.month.localeCompare(b.month));
});

const maxMonthly = computed(() => {
  return Math.max(...monthlyData.value.map(m => m.count), 1);
});

const generateReport = async () => {
  loading.value = true;
  error.value = null;

  try {
    const filterParams: any = {};
    if (filters.value.status) filterParams.status = filters.value.status;
    if (filters.value.startDate) filterParams.start_date = filters.value.startDate;
    if (filters.value.endDate) filterParams.end_date = filters.value.endDate;

    orders.value = await OrderService.getOrders(filterParams);
  } catch (e: any) {
    error.value = e?.message || 'Erro ao gerar relatório';
  } finally {
    loading.value = false;
  }
};

const exportReport = () => {
  const csvContent = [
    ['Passageiro', 'Destino', 'Data Ida', 'Data Volta', 'Status', 'Criado em'].join(','),
    ...orders.value.map(order => [
      order.requester,
      order.destination,
      moment(order.start_date).format('DD/MM/YYYY'),
      moment(order.end_date).format('DD/MM/YYYY'),
      statusLabels[order.status] || order.status,
      moment(order.created_at).format('DD/MM/YYYY')
    ].join(','))
  ].join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = `relatorio-pedidos-${moment().format('YYYY-MM-DD')}.csv`;
  link.click();
};

onMounted(() => {
  generateReport();
});
</script>
