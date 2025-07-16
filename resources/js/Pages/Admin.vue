<template>
    <Head title="Administração" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-white">
                Painel de Administração
            </h2>
        </template>

        <div class="min-h-screen py-12 bg-gray-50">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Estatísticas do Sistema -->
                <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-3">
                    <div class="overflow-hidden bg-white border-l-4 border-blue-500 rounded-lg shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-8 h-8 bg-blue-100 rounded-full">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 w-0 ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Total de Usuários</dt>
                                        <dd class="text-3xl font-bold text-gray-900">{{ users.length }}</dd>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 w-0 ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Administradores</dt>
                                        <dd class="text-3xl font-bold text-gray-900">{{ users.filter(u => u.is_admin).length }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden bg-white border-l-4 border-purple-500 rounded-lg shadow-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="flex items-center justify-center w-8 h-8 bg-purple-100 rounded-full">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 w-0 ml-5">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">Usuários Comuns</dt>
                                        <dd class="text-3xl font-bold text-gray-900">{{ users.filter(u => !u.is_admin).length }}</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros e Ações -->
                <div class="mb-6">
                    <div class="overflow-hidden bg-white rounded-lg shadow-lg">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Gestão de Usuários</h3>
                                <button
                                    @click="openCreateModal"
                                    class="px-4 py-2 text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                >
                                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Novo Usuário
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Buscar</label>
                                    <input
                                        v-model="filters.search"
                                        type="text"
                                        placeholder="Nome ou email..."
                                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Tipo de Usuário</label>
                                    <select
                                        v-model="filters.is_admin"
                                        class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Todos</option>
                                        <option :value="true">Administradores</option>
                                        <option :value="false">Usuários Comuns</option>
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button
                                        @click="loadUsers"
                                        :disabled="loading"
                                        class="w-full px-4 py-2 text-white bg-gray-600 border border-transparent rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50"
                                    >
                                        {{ loading ? 'Carregando...' : 'Filtrar' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Usuários -->
                <div class="overflow-hidden bg-white rounded-lg shadow-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Usuário</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Tipo</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Criado em</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-right text-gray-500 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in filteredUsers" :key="user.id" class="transition-colors duration-200 hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-10 h-10">
                                                <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full">
                                                    <span class="text-sm font-medium text-blue-600">
                                                        {{ user.name.charAt(0).toUpperCase() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ user.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="{
                                            'bg-green-100 text-green-800': user.is_admin,
                                            'bg-gray-100 text-gray-800': !user.is_admin
                                        }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ user.is_admin ? 'Admin' : 'Usuário' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                                        {{ moment(user.created_at).format('DD/MM/YYYY') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-right whitespace-nowrap">
                                        <div class="flex justify-end space-x-2">
                                            <button
                                                @click="editUser(user)"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button
                                                v-if="user.id !== $page.props.auth.user.id"
                                                @click="confirmDelete(user)"
                                                class="text-red-600 hover:text-red-900"
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

                <!-- Modal de Criação/Edição -->
                <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="relative w-full max-w-md bg-white rounded-lg shadow-xl">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ isEditing ? 'Editar Usuário' : 'Novo Usuário' }}
                                </h3>
                                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <form @submit.prevent="saveUser" class="px-6 py-4">
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Nome</label>
                                        <input
                                            v-model="userForm.name"
                                            type="text"
                                            required
                                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <input
                                            v-model="userForm.email"
                                            type="email"
                                            required
                                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        >
                                    </div>

                                    <div v-if="!isEditing">
                                        <label class="block text-sm font-medium text-gray-700">Senha</label>
                                        <input
                                            v-model="userForm.password"
                                            type="password"
                                            :required="!isEditing"
                                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        >
                                    </div>

                                    <div v-if="isEditing">
                                        <label class="block text-sm font-medium text-gray-700">Nova Senha (opcional)</label>
                                        <input
                                            v-model="userForm.password"
                                            type="password"
                                            class="block w-full px-3 py-2 mt-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                        >
                                    </div>

                                    <div class="flex items-center">
                                        <input
                                            v-model="userForm.is_admin"
                                            type="checkbox"
                                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        >
                                        <label class="ml-2 text-sm text-gray-700">Administrador</label>
                                    </div>
                                </div>

                                <div class="flex justify-end mt-6 space-x-3">
                                    <button
                                        type="button"
                                        @click="closeModal"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="formLoading"
                                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                                    >
                                        {{ formLoading ? 'Salvando...' : (isEditing ? 'Atualizar' : 'Criar') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de Confirmação de Exclusão -->
                <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50">
                    <div class="flex items-center justify-center min-h-screen px-4">
                        <div class="relative w-full max-w-md bg-white rounded-lg shadow-xl">
                            <div class="px-6 py-4">
                                <h3 class="mb-4 text-lg font-semibold text-gray-900">Confirmar Exclusão</h3>
                                <p class="mb-6 text-gray-600">
                                    Tem certeza que deseja excluir o usuário <strong>{{ userToDelete?.name }}</strong>?
                                    Esta ação não pode ser desfeita.
                                </p>
                                <div class="flex justify-end space-x-3">
                                    <button
                                        @click="showDeleteModal = false; userToDelete = null"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        @click="deleteUser"
                                        :disabled="formLoading"
                                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
                                    >
                                        {{ formLoading ? 'Excluindo...' : 'Excluir' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import UserService, { User } from '@/Services/UserService';
import { useNotifications } from '@/Composables/useNotifications';
import moment from 'moment';

moment.locale('pt-br');

const users = ref<User[]>([]);
const loading = ref(false);
const formLoading = ref(false);
const { showError, showSuccess } = useNotifications();

const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const userToDelete = ref<User | null>(null);

const filters = ref({
    search: '',
    is_admin: ''
});

const userForm = ref({
    id: 0,
    name: '',
    email: '',
    password: '',
    is_admin: false
});

const filteredUsers = computed(() => {
    let filtered = users.value;

    if (filters.value.search) {
        const search = filters.value.search.toLowerCase();
        filtered = filtered.filter(user =>
            user.name.toLowerCase().includes(search) ||
            user.email.toLowerCase().includes(search)
        );
    }

    if (filters.value.is_admin !== '') {
        filtered = filtered.filter(user => user.is_admin === (filters.value.is_admin === 'true'));
    }

    return filtered;
});

const loadUsers = async () => {
    loading.value = true;
    users.value = await UserService.getUsers();
    loading.value = false;
};

const openCreateModal = () => {
    isEditing.value = false;
    userForm.value = {
        id: 0,
        name: '',
        email: '',
        password: '',
        is_admin: false
    };
    showModal.value = true;
};

const editUser = (user: User) => {
    isEditing.value = true;
    userForm.value = {
        id: user.id,
        name: user.name,
        email: user.email,
        password: '',
        is_admin: user.is_admin
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    isEditing.value = false;
};

const saveUser = async () => {
    formLoading.value = true;

    if (isEditing.value) {
        const updateData: any = {
            name: userForm.value.name,
            email: userForm.value.email,
            is_admin: userForm.value.is_admin
        };

        if (userForm.value.password) {
            updateData.password = userForm.value.password;
        }

        await UserService.updateUser(userForm.value.id, updateData);
        showSuccess('Usuário atualizado com sucesso!');
    } else {
        await UserService.createUser({
            name: userForm.value.name,
            email: userForm.value.email,
            password: userForm.value.password,
            is_admin: userForm.value.is_admin
        });
        showSuccess('Usuário criado com sucesso!');
    }

    await loadUsers();
    closeModal();
    formLoading.value = false;
};

const confirmDelete = (user: User) => {
    userToDelete.value = user;
    showDeleteModal.value = true;
};

const deleteUser = async () => {
    if (!userToDelete.value) return;

    formLoading.value = true;

    await UserService.deleteUser(userToDelete.value.id);
    showSuccess('Usuário excluído com sucesso!');
    await loadUsers();
    showDeleteModal.value = false;
    userToDelete.value = null;
    formLoading.value = false;
};

onMounted(() => {
    loadUsers();
});
</script>
