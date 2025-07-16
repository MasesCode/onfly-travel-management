
<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import OrderService, { Order } from '@/Services/OrderService';
import { useNotifications } from '@/Composables/useNotifications';
import moment from 'moment';

moment.locale('pt-br');

const orders = ref<Order[]>([]);
const loading = ref(false);
const { showError, showSuccess } = useNotifications();
const page = usePage();

const statusLabels: { [key: string]: string } = {
  'requested': 'Solicitado',
  'approved': 'Aprovado',
  'cancelled': 'Cancelado'
};

const filters = ref({
  status: '',
  destination: '',
  startDate: '',
  endDate: ''
});

const showCreateModal = ref(false);
const createForm = ref({
  destination: '',
  departure_date: '',
  return_date: ''
});
const createLoading = ref(false);

const showViewModal = ref(false);
const showEditModal = ref(false);
const selectedOrder = ref<Order | null>(null);
const editForm = ref({
  id: 0,
  destination: '',
  start_date: '',
  end_date: '',
  status: '',
  notes: ''
});
const editLoading = ref(false);

const formatDate = (date: string) => {
  return moment(date).format('DD/MM/YYYY');
};

const translateStatus = (status: string) => {
  const statusMap: { [key: string]: string } = {
    'requested': 'Solicitado',
    'approved': 'Aprovado',
    'cancelled': 'Cancelado'
  };
  return statusMap[status] || status;
};

const fetchOrders = async () => {
  loading.value = true;
  try {
    const filterParams: any = {};
    if (filters.value.status) filterParams.status = filters.value.status;
    if (filters.value.destination) filterParams.destination = filters.value.destination;
    if (filters.value.startDate) filterParams.start_date = filters.value.startDate;
    if (filters.value.endDate) filterParams.end_date = filters.value.endDate;

    orders.value = await OrderService.getOrders(filterParams);
  } catch (e: any) {
    showError(e, 'Erro ao buscar pedidos');
  } finally {
    loading.value = false;
  }
};

const clearFilters = () => {
  filters.value = {
    status: '',
    destination: '',
    startDate: '',
    endDate: ''
  };
  fetchOrders();
};

const openCreateModal = () => {
  showCreateModal.value = true;
  createForm.value = {
    destination: '',
    departure_date: '',
    return_date: ''
  };
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  createForm.value = {
    destination: '',
    departure_date: '',
    return_date: ''
  };
};

const createOrder = async () => {
  createLoading.value = true;

  await OrderService.createOrder(createForm.value);
  closeCreateModal();
  showSuccess('Pedido criado com sucesso!');
  fetchOrders();
  createLoading.value = false;
};

const viewOrder = (order: Order) => {
  selectedOrder.value = order;
  showViewModal.value = true;
};

const closeViewModal = () => {
  showViewModal.value = false;
  showEditModal.value = false;
  selectedOrder.value = null;
  editForm.value = {
    id: 0,
    destination: '',
    start_date: '',
    end_date: '',
    status: '',
    notes: ''
  };
};

const formatDateForInput = (dateString: string) => {
  if (!dateString) return '';
  if (dateString.match(/^\d{4}-\d{2}-\d{2}$/)) return dateString;
  if (dateString.includes('T')) {
    return dateString.split('T')[0];
  }
  if (dateString.includes(' ')) {
    return dateString.split(' ')[0];
  }
  const momentDate = moment(dateString);
  if (momentDate.isValid()) {
    return momentDate.format('YYYY-MM-DD');
  }
  return '';
};

const openEditModal = () => {
  if (selectedOrder.value) {
    const currentUser = page.props.auth.user;
    if (!currentUser.is_admin && selectedOrder.value.user_id !== currentUser.id) {
      showError(
        { response: { status: 403, data: { message: 'Você não tem permissão para editar este pedido.' } } },
        'Erro de Permissão'
      );
      return;
    }

    editForm.value = {
      id: selectedOrder.value.id,
      destination: selectedOrder.value.destination,
      start_date: formatDateForInput(selectedOrder.value.start_date),
      end_date: formatDateForInput(selectedOrder.value.end_date),
      status: selectedOrder.value.status,
      notes: selectedOrder.value.notes || ''
    };
    showEditModal.value = true;
  }
};

const closeEditModal = () => {
  showEditModal.value = false;
  showViewModal.value = false;
  selectedOrder.value = null;
  editForm.value = {
    id: 0,
    destination: '',
    start_date: '',
    end_date: '',
    status: '',
    notes: ''
  };
};

const updateOrder = async () => {
  editLoading.value = true;

  await OrderService.updateOrder(editForm.value.id, {
    destination: editForm.value.destination,
    departure_date: editForm.value.start_date,
    return_date: editForm.value.end_date
  });

  if (editForm.value.status !== selectedOrder.value?.status) {
    await OrderService.updateOrderStatus(editForm.value.id, editForm.value.status);
  }

  closeEditModal();
  showSuccess('Pedido atualizado com sucesso!');
  fetchOrders();
  editLoading.value = false;
};

onMounted(async () => {
  fetchOrders();
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                Dashboard - Pedidos de Viagem
            </h2>
        </template>

        <div class="min-h-screen py-12 bg-gray-50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header Section with Stats -->
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
                    <div class="overflow-hidden bg-white border-l-4 border-blue-500 rounded-lg shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 w-0 ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total de Pedidos</dt>
                                        <dd class="text-3xl font-bold text-gray-900">{{ orders.length }}</dd>
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
                                        <dd class="text-3xl font-bold text-gray-900">{{ orders.filter(o => o.status === 'approved').length }}</dd>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 w-0 ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Pendentes</dt>
                                        <dd class="text-3xl font-bold text-gray-900">{{ orders.filter(o => o.status === 'requested').length }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros Section -->
                <div class="mb-8 overflow-hidden bg-white rounded-lg shadow-lg">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">Filtros</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <!-- Filtro Status -->
                            <div>
                                <label for="status-filter" class="block mb-2 text-sm font-medium text-gray-700">Status</label>
                                <select
                                    id="status-filter"
                                    v-model="filters.status"
                                    @change="fetchOrders"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                >
                                    <option value="">Todos os status</option>
                                    <option value="requested">Solicitado</option>
                                    <option value="approved">Aprovado</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                            </div>

                            <!-- Filtro Destino -->
                            <div>
                                <label for="destination-filter" class="block mb-2 text-sm font-medium text-gray-700">Destino</label>
                                <input
                                    id="destination-filter"
                                    type="text"
                                    v-model="filters.destination"
                                    @input="fetchOrders"
                                    placeholder="Digite o destino..."
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                />
                            </div>

                            <!-- Filtro Data Início -->
                            <div>
                                <label for="start-date-filter" class="block mb-2 text-sm font-medium text-gray-700">Data Início</label>
                                <input
                                    id="start-date-filter"
                                    type="date"
                                    v-model="filters.startDate"
                                    @change="fetchOrders"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                />
                            </div>

                            <!-- Filtro Data Fim -->
                            <div>
                                <label for="end-date-filter" class="block mb-2 text-sm font-medium text-gray-700">Data Fim</label>
                                <input
                                    id="end-date-filter"
                                    type="date"
                                    v-model="filters.endDate"
                                    @change="fetchOrders"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                />
                            </div>
                        </div>

                        <!-- Botão Limpar Filtros -->
                        <div class="flex justify-end mt-4">
                            <button
                                @click="clearFilters"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Limpar Filtros
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Table Section -->
                <div class="overflow-hidden bg-white rounded-lg shadow-xl">
                    <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700">
                        <h3 class="text-lg font-semibold text-white">Pedidos de Viagem</h3>
                        <button
                            @click="openCreateModal"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-transparent rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-600 focus:ring-white"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Novo Pedido
                        </button>
                    </div>

                    <div class="p-6">
                        <template v-if="loading">
                            <div class="flex items-center justify-center py-8">
                                <div class="w-8 h-8 border-b-2 border-blue-600 rounded-full animate-spin"></div>
                                <span class="ml-2 text-gray-600">Carregando pedidos...</span>
                            </div>
                        </template>

                        <template v-else>
                            <div class="overflow-hidden rounded-lg shadow ring-1 ring-black ring-opacity-5">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">ID</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Solicitante</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Destino</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data Ida</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Data Volta</th>
                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="order in orders" :key="order.id" @click="viewOrder(order)" class="transition-colors duration-200 cursor-pointer hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">#{{ order.id }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ order.requester }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ order.destination }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ formatDate(order.start_date) }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ formatDate(order.end_date) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                                                      :class="{
                                                        'bg-yellow-100 text-yellow-800': order.status === 'requested',
                                                        'bg-green-100 text-green-800': order.status === 'approved',
                                                        'bg-red-100 text-red-800': order.status === 'cancelled'
                                                      }">
                                                    {{ translateStatus(order.status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="orders.length === 0">
                                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                                <div class="flex flex-col items-center">
                                                    <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    <span class="text-lg font-medium">Nenhum pedido encontrado</span>
                                                    <span class="text-sm">Quando houver pedidos, eles aparecerão aqui.</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Criação de Pedido -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeCreateModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-blue-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div class="w-full mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Novo Pedido de Viagem
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <!-- Destino -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Destino</label>
                                        <input
                                            v-model="createForm.destination"
                                            type="text"
                                            placeholder="Ex: São Paulo, SP"
                                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                        />
                                    </div>

                                    <!-- Data de Ida -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Data de Ida</label>
                                        <input
                                            v-model="createForm.departure_date"
                                            type="date"
                                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                        />
                                    </div>

                                    <!-- Data de Volta -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Data de Volta</label>
                                        <input
                                            v-model="createForm.return_date"
                                            type="date"
                                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                        />
                                    </div>

                                    <!-- Erro removido - será exibido via notificação -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            @click="createOrder"
                            :disabled="createLoading"
                            type="button"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            <span v-if="createLoading" class="inline-flex items-center">
                                <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Criando...
                            </span>
                            <span v-else>Criar Pedido</span>
                        </button>
                        <button
                            @click="closeCreateModal"
                            type="button"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Visualizar/Editar Pedido -->
        <div v-if="showViewModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <!-- Cabeçalho do Modal -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ showEditModal ? 'Editar Pedido' : 'Detalhes do Pedido' }}
                        </h3>
                        <button @click="closeViewModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Conteúdo do Modal -->
                    <div class="px-6 py-4" v-if="selectedOrder">
                        <div v-if="!showEditModal">
                            <!-- Modo Visualização -->
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Passageiro</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ selectedOrder.requester }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Destino</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ selectedOrder.destination }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data de Ida</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ moment(selectedOrder.start_date).format('DD/MM/YYYY') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data de Volta</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ moment(selectedOrder.end_date).format('DD/MM/YYYY') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status</label>
                                    <span :class="{
                                        'bg-yellow-100 text-yellow-800': selectedOrder.status === 'requested',
                                        'bg-green-100 text-green-800': selectedOrder.status === 'approved',
                                        'bg-red-100 text-red-800': selectedOrder.status === 'cancelled'
                                    }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ statusLabels[selectedOrder.status] || selectedOrder.status }}
                                    </span>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Observações</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ selectedOrder.notes || 'Nenhuma observação' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Criado em</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ moment(selectedOrder.created_at).format('DD/MM/YYYY HH:mm') }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Atualizado em</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ moment(selectedOrder.updated_at).format('DD/MM/YYYY HH:mm') }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-else>
                            <!-- Modo Edição -->
                            <form @submit.prevent="updateOrder">
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Destino</label>
                                        <input
                                            v-model="editForm.destination"
                                            type="text"
                                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Data de Ida</label>
                                        <input
                                            v-model="editForm.start_date"
                                            type="date"
                                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Data de Volta</label>
                                        <input
                                            v-model="editForm.end_date"
                                            type="date"
                                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                            required
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Status</label>
                                        <select
                                            v-model="editForm.status"
                                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        >
                                            <option value="requested">Solicitado</option>
                                            <option value="approved">Aprovado</option>
                                            <option value="cancelled">Cancelado</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Observações</label>
                                        <textarea
                                            v-model="editForm.notes"
                                            rows="3"
                                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        ></textarea>
                                    </div>
                                </div>
                                <!-- Erro removido - será exibido via notificação -->
                            </form>
                        </div>
                    </div>

                    <!-- Rodapé do Modal -->
                    <div class="flex justify-end px-6 py-4 space-x-3 border-t border-gray-200">
                        <button
                            @click="closeViewModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Fechar
                        </button>

                        <button
                            v-if="!showEditModal && $page.props.auth.user.is_admin"
                            @click="openEditModal"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Editar
                        </button>

                        <button
                            v-if="showEditModal"
                            @click="updateOrder"
                            :disabled="editLoading"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                        >
                            {{ editLoading ? 'Salvando...' : 'Salvar' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
