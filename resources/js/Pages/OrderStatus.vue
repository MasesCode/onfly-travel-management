<template>
    <Head title="Status de Pedido" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-white">
                    Status de Pedido
                </h2>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 transition-colors duration-200 bg-white border border-white rounded-lg shadow-sm hover:bg-gray-100"
                >
                    <PlusIcon class="w-4 h-4 mr-2" />
                    Novo Status
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filtros -->
                <div class="mb-6 overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Buscar por nome
                                </label>
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Digite o nome do status..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-700">
                                    Status
                                </label>
                                <select
                                    v-model="filters.active"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="">Todos</option>
                                    <option value="1">Ativos</option>
                                    <option value="0">Inativos</option>
                                </select>
                            </div>
                            <div class="flex items-end space-x-2">
                                <button
                                    @click="applyFilters"
                                    class="px-4 py-2 font-medium text-white transition-colors duration-200 bg-blue-600 rounded-lg hover:bg-blue-700"
                                >
                                    Filtrar
                                </button>
                                <button
                                    @click="resetFilters"
                                    class="px-4 py-2 font-medium text-white transition-colors duration-200 bg-gray-600 rounded-lg hover:bg-gray-700"
                                >
                                    Limpar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lista de Status -->
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="status in orderStatuses"
                                :key="status.id"
                                class="p-6 transition-shadow duration-200 border border-gray-200 rounded-lg bg-gradient-to-br from-gray-50 to-gray-100 hover:shadow-lg"
                            >
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <div
                                            class="w-4 h-4 mr-3 rounded-full"
                                            :style="{ backgroundColor: status.color }"
                                        ></div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ status.name }}
                                        </h3>
                                    </div>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="status.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800'"
                                    >
                                        {{ status.is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </div>

                                <p v-if="status.description" class="mb-4 text-sm text-gray-600">
                                    {{ status.description }}
                                </p>

                                <div class="flex items-center justify-between mb-4 text-sm text-gray-500">
                                    <span>Ordem: {{ status.order }}</span>
                                    <span>{{ status.orders_count || 0 }} pedidos</span>
                                </div>

                                <div class="flex space-x-2">
                                    <button
                                        @click="viewStatus(status)"
                                        class="flex-1 px-3 py-2 text-sm font-medium text-white transition-colors duration-200 bg-blue-600 rounded-lg hover:bg-blue-700"
                                    >
                                        <EyeIcon class="inline w-4 h-4 mr-1" />
                                        Ver
                                    </button>
                                    <button
                                        @click="editStatus(status)"
                                        class="flex-1 px-3 py-2 text-sm font-medium text-white transition-colors duration-200 bg-orange-600 rounded-lg hover:bg-orange-700"
                                    >
                                        <PencilIcon class="inline w-4 h-4 mr-1" />
                                        Editar
                                    </button>
                                    <button
                                        @click="deleteStatus(status)"
                                        class="flex-1 px-3 py-2 text-sm font-medium text-white transition-colors duration-200 bg-red-600 rounded-lg hover:bg-red-700"
                                    >
                                        <TrashIcon class="inline w-4 h-4 mr-1" />
                                        Excluir
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Mensagem quando não há resultados -->
                        <div v-if="orderStatuses.length === 0" class="py-12 text-center">
                            <div class="w-24 h-24 mx-auto mb-4 text-gray-300">
                                <svg fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                </svg>
                            </div>
                            <h3 class="mb-2 text-lg font-medium text-gray-900">
                                Nenhum status encontrado
                            </h3>
                            <p class="text-gray-500">
                                Não há status de pedido que correspondam aos filtros aplicados.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Paginação -->
                <div v-if="pagination && pagination.last_page > 1" class="flex justify-center mt-6">
                    <nav class="flex items-center space-x-2">
                        <button
                            v-for="page in getVisiblePages()"
                            :key="page"
                            @click="changePage(page)"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
                                page === pagination.current_page
                                    ? 'bg-blue-600 text-white'
                                    : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                            ]"
                        >
                            {{ page }}
                        </button>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Modal de Visualização -->
        <Modal :show="viewModal.show" @close="closeViewModal">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">
                    Detalhes do Status
                </h3>
                <div v-if="viewModal.status" class="space-y-4">
                    <div class="flex items-center">
                        <div
                            class="w-6 h-6 mr-3 rounded-full"
                            :style="{ backgroundColor: viewModal.status.color }"
                        ></div>
                        <span class="text-xl font-medium">{{ viewModal.status.name }}</span>
                        <span
                            class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="viewModal.status.is_active
                                ? 'bg-green-100 text-green-800'
                                : 'bg-red-100 text-red-800'"
                        >
                            {{ viewModal.status.is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                    <div v-if="viewModal.status.description">
                        <strong>Descrição:</strong>
                        <p class="mt-1 text-gray-600">{{ viewModal.status.description }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <strong>Ordem:</strong> {{ viewModal.status.order }}
                        </div>
                        <div>
                            <strong>Pedidos:</strong> {{ viewModal.status.orders_count || 0 }}
                        </div>
                    </div>
                    <div class="pt-4 text-xs text-gray-500 border-t">
                        <div>Criado em: {{ formatDate(viewModal.status.created_at) }}</div>
                        <div v-if="viewModal.status.updated_at !== viewModal.status.created_at">
                            Atualizado em: {{ formatDate(viewModal.status.updated_at) }}
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button
                        @click="closeViewModal"
                        class="px-4 py-2 font-medium text-white transition-colors duration-200 bg-gray-500 rounded-lg hover:bg-gray-600"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Modal de Criação/Edição -->
        <Modal :show="formModal.show" @close="closeFormModal">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">
                    {{ formModal.isEdit ? 'Editar Status' : 'Novo Status' }}
                </h3>
                <form @submit.prevent="saveStatus" class="space-y-4">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">
                            Nome *
                        </label>
                        <input
                            v-model="formModal.data.name"
                            type="text"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.name }"
                        />
                        <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name[0] }}</p>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-700">
                            Descrição
                        </label>
                        <textarea
                            v-model="formModal.data.description"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="{ 'border-red-500': errors.description }"
                        ></textarea>
                        <p v-if="errors.description" class="mt-1 text-sm text-red-500">{{ errors.description[0] }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                Cor *
                            </label>
                            <div class="flex items-center space-x-2">
                                <input
                                    v-model="formModal.data.color"
                                    type="color"
                                    required
                                    class="w-12 h-10 border border-gray-300 rounded cursor-pointer"
                                />
                                <input
                                    v-model="formModal.data.color"
                                    type="text"
                                    required
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': errors.color }"
                                />
                            </div>
                            <p v-if="errors.color" class="mt-1 text-sm text-red-500">{{ errors.color[0] }}</p>
                        </div>

                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-700">
                                Ordem *
                            </label>
                            <input
                                v-model.number="formModal.data.order"
                                type="number"
                                required
                                min="1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="{ 'border-red-500': errors.order }"
                            />
                            <p v-if="errors.order" class="mt-1 text-sm text-red-500">{{ errors.order[0] }}</p>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="formModal.data.is_active"
                            type="checkbox"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label class="ml-2 text-sm text-gray-700">
                            Status ativo
                        </label>
                    </div>

                    <div class="flex justify-end pt-4 space-x-3 border-t">
                        <button
                            type="button"
                            @click="closeFormModal"
                            class="px-4 py-2 font-medium text-white transition-colors duration-200 bg-gray-500 rounded-lg hover:bg-gray-600"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="isLoading"
                            class="px-4 py-2 font-medium text-white transition-colors duration-200 bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ isLoading ? 'Salvando...' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal de Confirmação de Exclusão -->
        <Modal :show="deleteModal.show" @close="closeDeleteModal">
            <div class="p-6">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">
                    Confirmar Exclusão
                </h3>
                <p class="mb-6 text-gray-600">
                    Tem certeza que deseja excluir o status "<strong>{{ deleteModal.status?.name }}</strong>"?
                    Esta ação não pode ser desfeita.
                </p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="closeDeleteModal"
                        class="px-4 py-2 font-medium text-white transition-colors duration-200 bg-gray-500 rounded-lg hover:bg-gray-600"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="confirmDelete"
                        :disabled="isLoading"
                        class="px-4 py-2 font-medium text-white transition-colors duration-200 bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50"
                    >
                        {{ isLoading ? 'Excluindo...' : 'Excluir' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Modal from '@/Components/Modal.vue';
import {
    PlusIcon,
    EyeIcon,
    PencilIcon,
    TrashIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';
import moment from 'moment';
import { useNotifications } from '@/Composables/useNotifications';

interface OrderStatus {
    id: number;
    name: string;
    description?: string;
    color: string;
    order: number;
    is_active: boolean;
    orders_count?: number;
    created_at: string;
    updated_at: string;
}

interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

const orderStatuses = ref<OrderStatus[]>([]);
const pagination = ref<Pagination | null>(null);
const isLoading = ref(false);
const errors = ref<Record<string, string[]>>({});
const { showError, showSuccess } = useNotifications();

const filters = reactive({
    search: '',
    active: '',
    page: 1
});

const viewModal = reactive({
    show: false,
    status: null as OrderStatus | null
});

const formModal = reactive({
    show: false,
    isEdit: false,
    data: {
        id: null as number | null,
        name: '',
        description: '',
        color: '#3B82F6',
        order: 1,
        is_active: true
    }
});

const deleteModal = reactive({
    show: false,
    status: null as OrderStatus | null
});

onMounted(() => {
    loadOrderStatuses();
});

const loadOrderStatuses = async () => {
    isLoading.value = true;
    const params = new URLSearchParams();

    if (filters.search) params.append('search', filters.search);
    if (filters.active !== '') params.append('is_active', filters.active);
    params.append('page', filters.page.toString());

    const response = await axios.get(`/api/order-statuses?${params}`);
    orderStatuses.value = response.data.data;
    pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        per_page: response.data.per_page,
        total: response.data.total
    };
    isLoading.value = false;
};

const applyFilters = () => {
    filters.page = 1;
    loadOrderStatuses();
};

const resetFilters = () => {
    filters.search = '';
    filters.active = '';
    filters.page = 1;
    loadOrderStatuses();
};

const changePage = (page: number) => {
    filters.page = page;
    loadOrderStatuses();
};

const getVisiblePages = () => {
    if (!pagination.value) return [];

    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;

    let start = Math.max(1, current - 2);
    let end = Math.min(last, start + 4);

    if (end - start < 4) {
        start = Math.max(1, end - 4);
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    return pages;
};

const openCreateModal = () => {
    formModal.isEdit = false;
    formModal.data = {
        id: null,
        name: '',
        description: '',
        color: '#3B82F6',
        order: (orderStatuses.value.length + 1),
        is_active: true
    };
    errors.value = {};
    formModal.show = true;
};

const viewStatus = (status: OrderStatus) => {
    viewModal.status = status;
    viewModal.show = true;
};

const editStatus = (status: OrderStatus) => {
    formModal.isEdit = true;
    formModal.data = {
        id: status.id,
        name: status.name,
        description: status.description || '',
        color: status.color,
        order: status.order,
        is_active: status.is_active
    };
    errors.value = {};
    formModal.show = true;
};

const deleteStatus = (status: OrderStatus) => {
    deleteModal.status = status;
    deleteModal.show = true;
};

const saveStatus = async () => {
    try {
        isLoading.value = true;
        errors.value = {};

        const url = formModal.isEdit
            ? `/api/order-statuses/${formModal.data.id}`
            : '/api/order-statuses';

        const method = formModal.isEdit ? 'put' : 'post';

        await axios[method](url, formModal.data);

        closeFormModal();
        loadOrderStatuses();

        alert(formModal.isEdit ? 'Status atualizado com sucesso!' : 'Status criado com sucesso!');
    } catch (error: any) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors;
        } else {
            console.error('Erro ao salvar status:', error);
            alert('Erro ao salvar status. Tente novamente.');
        }
    } finally {
        isLoading.value = false;
    }
};

const confirmDelete = async () => {
    isLoading.value = true;
    await axios.delete(`/api/order-statuses/${deleteModal.status?.id}`);

    closeDeleteModal();
    loadOrderStatuses();
    showSuccess('Status excluído com sucesso!');
    isLoading.value = false;
};

const closeViewModal = () => {
    viewModal.show = false;
    viewModal.status = null;
};

const closeFormModal = () => {
    formModal.show = false;
    errors.value = {};
};

const closeDeleteModal = () => {
    deleteModal.show = false;
    deleteModal.status = null;
};

const formatDate = (date: string) => {
    return moment(date).format('DD/MM/YYYY HH:mm');
};
</script>
