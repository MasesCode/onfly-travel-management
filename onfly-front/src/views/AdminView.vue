<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-white">
          Administração de Usuários
        </h2>
        <button
          @click="openCreateModal"
          class="px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-transparent rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
        >
          <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Novo Usuário
        </button>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
          <!-- Filtros e Busca -->
          <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
              <!-- Busca -->
              <div class="flex-1 max-w-md">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>
                  <input
                    v-model="searchTerm"
                    type="text"
                    placeholder="Buscar usuários..."
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 text-gray-900 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>

              <!-- Filtros -->
              <div class="flex space-x-4">
                <select
                  v-model="filterRole"
                  class="px-3 py-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">Todos os usuários</option>
                  <option value="admin">Apenas Admins</option>
                  <option value="user">Apenas Usuários</option>
                </select>

                <button
                  @click="refreshUsers"
                  :disabled="loading"
                  class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Loading -->
          <div v-if="loading" class="flex items-center justify-center py-12">
            <div class="flex items-center space-x-2">
              <div class="w-4 h-4 bg-blue-600 rounded-full animate-pulse"></div>
              <div class="w-4 h-4 bg-blue-600 rounded-full animate-pulse" style="animation-delay: 0.1s"></div>
              <div class="w-4 h-4 bg-blue-600 rounded-full animate-pulse" style="animation-delay: 0.2s"></div>
              <span class="ml-2 text-gray-600">Carregando usuários...</span>
            </div>
          </div>

          <!-- Tabela de Usuários -->
          <div v-else-if="filteredUsers.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Usuário
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Data de Criação
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="user in filteredUsers"
                  :key="user.id"
                  class="hover:bg-blue-50 cursor-pointer transition-colors group"
                  @click="openViewModal(user)"
                  title="Clique para ver detalhes do usuário"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full group-hover:bg-blue-200 transition-colors">
                        <span class="text-sm font-medium text-blue-600">
                          {{ user.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors">
                          {{ user.name }}
                        </div>
                      </div>
                      <!-- Ícone indicativo que é clicável -->
                      <div class="ml-auto opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ user.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                      :class="user.is_admin
                        ? 'bg-purple-100 text-purple-800'
                        : 'bg-green-100 text-green-800'"
                    >
                      {{ user.is_admin ? 'Administrador' : 'Usuário' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(user.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2" @click.stop>
                      <button
                        @click="openEditModal(user)"
                        class="text-blue-600 hover:text-blue-900 p-1 rounded-md hover:bg-blue-50 transition-colors"
                        title="Editar usuário"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button
                        @click="openDeleteModal(user)"
                        :disabled="user.id === authStore.user?.id"
                        class="text-red-600 hover:text-red-900 p-1 rounded-md hover:bg-red-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        :title="user.id === authStore.user?.id ? 'Não é possível deletar seu próprio usuário' : 'Deletar usuário'"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Estado vazio -->
          <div v-else class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum usuário encontrado</h3>
            <p class="mt-1 text-sm text-gray-500">
              {{ searchTerm ? 'Nenhum usuário corresponde aos critérios de busca.' : 'Comece criando um novo usuário.' }}
            </p>
            <div class="mt-6">
              <button
                @click="openCreateModal"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Novo Usuário
              </button>
            </div>
          </div>

          <!-- Paginação -->
          <div v-if="!loading && filteredUsers.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Mostrando {{ filteredUsers.length }} usuário(s)
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Usuário -->
    <UserModal
      :is-visible="showUserModal"
      :user="selectedUser"
      @close="closeUserModal"
      @submit="handleUserSubmit"
    />

    <!-- Modal de Confirmação de Exclusão -->
    <DeleteUserModal
      :is-visible="showDeleteModal"
      :user-name="userToDelete?.name || ''"
      :is-loading="deleteLoading"
      @confirm="confirmDelete"
      @cancel="closeDeleteModal"
    />

    <!-- Modal de Visualização de Usuário -->
    <div v-if="showViewModal && userToView">
      <Teleport to="body">
        <Transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0"
          enter-to-class="opacity-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100"
          leave-to-class="opacity-0"
        >
          <div
            v-show="showViewModal"
            class="fixed inset-0 z-[9999] flex items-center justify-center"
            @click.self="closeViewModal"
          >
            <!-- Overlay -->
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>

            <!-- Modal -->
            <Transition
              enter-active-class="transition duration-300 ease-out"
              enter-from-class="scale-95 opacity-0"
              enter-to-class="scale-100 opacity-100"
              leave-active-class="transition duration-200 ease-in"
              leave-from-class="scale-100 opacity-100"
              leave-to-class="scale-95 opacity-0"
            >
              <div
                v-show="showViewModal"
                class="relative z-10 w-full max-w-lg p-6 mx-4 bg-white rounded-lg shadow-xl"
                @click.stop
              >
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                  <h3 class="text-xl font-semibold text-gray-900">
                    Detalhes do Usuário
                  </h3>
                  <button
                    @click="closeViewModal"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none transition-colors"
                  >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>

                <!-- Conteúdo do Usuário -->
                <div class="space-y-6">
                  <!-- Avatar e Nome -->
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full">
                      <span class="text-xl font-semibold text-blue-600">
                        {{ userToView.name.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                    <div>
                      <h4 class="text-lg font-semibold text-gray-900">{{ userToView.name }}</h4>
                      <p class="text-sm text-gray-600">{{ userToView.email }}</p>
                    </div>
                  </div>

                  <!-- Informações Principais -->
                  <div class="grid grid-cols-1 gap-4">
                    <!-- ID -->
                    <div class="bg-gray-50 rounded-lg p-4">
                      <div class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        ID do Usuário
                      </div>
                      <p class="text-sm font-mono text-gray-900">#{{ userToView.id }}</p>
                    </div>

                    <!-- Tipo de Usuário -->
                    <div class="bg-gray-50 rounded-lg p-4">
                      <div class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Tipo de Usuário
                      </div>
                      <div class="flex items-center space-x-2">
                        <span
                          class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                          :class="userToView.is_admin
                            ? 'bg-purple-100 text-purple-800'
                            : 'bg-green-100 text-green-800'"
                        >
                          {{ userToView.is_admin ? 'Administrador' : 'Usuário' }}
                        </span>
                        <svg
                          v-if="userToView.is_admin"
                          class="w-4 h-4 text-purple-600"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </div>
                    </div>

                    <!-- Data de Criação -->
                    <div class="bg-gray-50 rounded-lg p-4">
                      <div class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Membro desde
                      </div>
                      <p class="text-sm text-gray-900">{{ formatDate(userToView.created_at) }}</p>
                    </div>

                    <!-- Última Atualização -->
                    <div class="bg-gray-50 rounded-lg p-4">
                      <div class="block text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">
                        Última atualização
                      </div>
                      <p class="text-sm text-gray-900">{{ formatDate(userToView.updated_at) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex space-x-3 pt-6 border-t border-gray-200">
                  <button
                    @click="handleViewEdit(userToView)"
                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                  >
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Editar
                  </button>
                  <button
                    @click="handleViewDelete(userToView)"
                    :disabled="userToView.id === authStore.user?.id"
                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    :title="userToView.id === authStore.user?.id ? 'Não é possível deletar seu próprio usuário' : 'Deletar usuário'"
                  >
                    <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Deletar
                  </button>
                  <button
                    @click="closeViewModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                  >
                    Fechar
                  </button>
                </div>
              </div>
            </Transition>
          </div>
        </Transition>
      </Teleport>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import AuthenticatedLayout from '../layouts/AuthenticatedLayout.vue'
import UserModal from '../components/admin/UserModal.vue'
import DeleteUserModal from '../components/admin/DeleteUserModal.vue'
import api from '../services/api'

interface User {
  id: number
  name: string
  email: string
  is_admin: boolean
  created_at: string
  updated_at: string
}

interface UserFormData {
  name: string
  email: string
  password?: string
  password_confirmation?: string
  is_admin: boolean
}

// Stores
const authStore = useAuthStore()

// Estado
const users = ref<User[]>([])
const loading = ref(true)
const searchTerm = ref('')
const filterRole = ref('')

// Modal states
const showUserModal = ref(false)
const selectedUser = ref<User | null>(null)
const showDeleteModal = ref(false)
const userToDelete = ref<User | null>(null)
const deleteLoading = ref(false)
const showViewModal = ref(false)
const userToView = ref<User | null>(null)

// Computed
const filteredUsers = computed(() => {
  let filtered = users.value

  // Filtrar por termo de busca
  if (searchTerm.value) {
    const term = searchTerm.value.toLowerCase()
    filtered = filtered.filter(user =>
      user.name.toLowerCase().includes(term) ||
      user.email.toLowerCase().includes(term)
    )
  }

  // Filtrar por tipo
  if (filterRole.value === 'admin') {
    filtered = filtered.filter(user => user.is_admin)
  } else if (filterRole.value === 'user') {
    filtered = filtered.filter(user => !user.is_admin)
  }

  return filtered
})

// Métodos
const loadUsers = async () => {
  try {
    loading.value = true
    const response = await api.get('/admin/users')
    users.value = response.data.users || response.data
  } catch (error) {
    console.error('Erro ao carregar usuários:', error)
    // Aqui você pode adicionar uma notificação de erro
  } finally {
    loading.value = false
  }
}

const refreshUsers = () => {
  loadUsers()
}

// Modal methods
const openCreateModal = () => {
  selectedUser.value = null
  showUserModal.value = true
}

const openEditModal = (user: User) => {
  selectedUser.value = user
  showUserModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  selectedUser.value = null
}

const openDeleteModal = (user: User) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  userToDelete.value = null
}

const openViewModal = (user: User) => {
  userToView.value = user
  showViewModal.value = true
}

const closeViewModal = () => {
  showViewModal.value = false
  userToView.value = null
}

const handleViewEdit = (user: User) => {
  closeViewModal()
  openEditModal(user)
}

const handleViewDelete = (user: User) => {
  closeViewModal()
  openDeleteModal(user)
}

// CRUD operations
const handleUserSubmit = async (userData: UserFormData) => {
  try {
    if (selectedUser.value) {
      // Editar usuário
      const response = await api.put(`/admin/users/${selectedUser.value.id}`, userData)
      const updatedUser = response.data.user || response.data

      const index = users.value.findIndex(u => u.id === selectedUser.value!.id)
      if (index !== -1) {
        users.value[index] = updatedUser
      }
    } else {
      // Criar usuário
      const response = await api.post('/admin/users', userData)
      const newUser = response.data.user || response.data
      users.value.unshift(newUser)
    }

    closeUserModal()
  } catch (error) {
    console.error('Erro ao salvar usuário:', error)
    // Aqui você pode adicionar uma notificação de erro
  }
}

const confirmDelete = async () => {
  if (!userToDelete.value) return

  try {
    deleteLoading.value = true
    await api.delete(`/admin/users/${userToDelete.value.id}`)

    users.value = users.value.filter(u => u.id !== userToDelete.value!.id)
    closeDeleteModal()
  } catch (error) {
    console.error('Erro ao deletar usuário:', error)
    // Aqui você pode adicionar uma notificação de erro
  } finally {
    deleteLoading.value = false
  }
}

// Utility methods
const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  loadUsers()
})
</script>
