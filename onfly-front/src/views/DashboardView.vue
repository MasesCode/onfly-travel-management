<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-white">
        Dashboard - Visão Geral
      </h2>
    </template>

    <div class="min-h-screen py-12 bg-gray-50">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <div class="text-lg text-gray-600">Carregando métricas...</div>
        </div>

        <!-- Cards de Métricas -->
        <div v-else class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-4">
          <!-- Total de Pedidos -->
          <div class="overflow-hidden bg-white border-l-4 border-blue-500 rounded-lg shadow-lg">
            <div class="p-8">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total de Pedidos</dt>
                    <dd class="text-4xl font-bold text-gray-900">{{ orders.length }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Pedidos Aprovados -->
          <div class="overflow-hidden bg-white border-l-4 border-green-500 rounded-lg shadow-lg">
            <div class="p-8">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                  </div>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Pedidos Aprovados</dt>
                    <dd class="text-4xl font-bold text-gray-900">{{ orders.filter(o => o.status === 'approved').length }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Pedidos Pendentes -->
          <div class="overflow-hidden bg-white border-l-4 border-yellow-500 rounded-lg shadow-lg">
            <div class="p-8">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center w-12 h-12 bg-yellow-100 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Pedidos Pendentes</dt>
                    <dd class="text-4xl font-bold text-gray-900">{{ orders.filter(o => o.status === 'requested').length }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Pedidos Cancelados -->
          <div class="overflow-hidden bg-white border-l-4 border-red-500 rounded-lg shadow-lg">
            <div class="p-8">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </div>
                </div>
                <div class="flex-1 w-0 ml-5">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Pedidos Cancelados</dt>
                    <dd class="text-4xl font-bold text-gray-900">{{ orders.filter(o => o.status === 'cancelled').length }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Cards de Ações Rápidas -->
        <div class="grid grid-cols-1 gap-8 mt-12 md:grid-cols-2">
          <!-- Novo Pedido -->
          <div class="p-8 transition-shadow duration-300 bg-white rounded-lg shadow-lg hover:shadow-xl">
            <div class="text-center">
              <div class="flex items-center justify-center w-16 h-16 mx-auto bg-blue-100 rounded-full">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
              </div>
              <h3 class="mt-4 text-xl font-semibold text-gray-900">Novo Pedido</h3>
              <p class="mt-2 text-gray-600">Crie um novo pedido de viagem</p>
              <button
                @click="openCreateModal"
                class="inline-flex items-center px-6 py-3 mt-4 text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Criar Pedido
              </button>
            </div>
          </div>

          <!-- Ver Pedidos -->
          <div class="p-8 transition-shadow duration-300 bg-white rounded-lg shadow-lg hover:shadow-xl">
            <div class="text-center">
              <div class="flex items-center justify-center w-16 h-16 mx-auto bg-green-100 rounded-full">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
              </div>
              <h3 class="mt-4 text-xl font-semibold text-gray-900">Gerenciar Pedidos</h3>
              <p class="mt-2 text-gray-600">Visualize e gerencie todos os pedidos</p>
              <router-link
                to="/orders"
                class="inline-flex items-center px-6 py-3 mt-4 text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
              >
                Ver Pedidos
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Criação de Pedido -->
    <CreateOrderModal
      :show="showCreateModal"
      @close="showCreateModal = false"
      @created="handleOrderCreated"
    />
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';
import CreateOrderModal from '@/components/orders/CreateOrderModal.vue';
import api from '@/services/api';
import type { Order } from '../types/index';

const orders = ref<Order[]>([]);
const loading = ref(false);
const showCreateModal = ref(false);

const fetchOrders = async () => {
  loading.value = true;
  try {
    const response = await api.get('/orders');
    orders.value = response.data.data || response.data;
  } catch (error) {
    console.error('Erro ao carregar pedidos:', error);
  } finally {
    loading.value = false;
  }
};

const openCreateModal = () => {
  showCreateModal.value = true;
};

const handleOrderCreated = async (orderData: { destination: string; start_date: string; end_date: string }) => {
  try {
    const backendData = {
      destination: orderData.destination,
      departure_date: orderData.start_date,
      return_date: orderData.end_date
    }
    
    await api.post('/orders', backendData);
    showCreateModal.value = false;
    fetchOrders();
  } catch (error) {
    console.error('Erro ao criar pedido:', error);
  }
};

onMounted(async () => {
  await fetchOrders();
});
</script>
