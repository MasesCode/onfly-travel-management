<template>
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
        v-show="isVisible"
        class="fixed inset-0 z-[9999] flex items-center justify-center"
        @click.self="closeModal"
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
            v-show="isVisible"
            class="relative z-10 w-full max-w-lg p-6 mx-4 bg-white rounded-lg shadow-xl"
            @click.stop
          >
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-semibold text-gray-900">
                Detalhes do Usuário
              </h3>
              <button
                @click="closeModal"
                class="text-gray-400 transition-colors hover:text-gray-600 focus:outline-none"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Conteúdo do Usuário -->
            <div v-if="user" class="space-y-6">
              <!-- Avatar e Nome -->
              <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full">
                  <span class="text-xl font-semibold text-blue-600">
                    {{ user.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div>
                  <h4 class="text-lg font-semibold text-gray-900">{{ user.name }}</h4>
                  <p class="text-sm text-gray-600">{{ user.email }}</p>
                </div>
              </div>

              <!-- Informações Principais -->
              <div class="grid grid-cols-1 gap-4">
                <!-- ID -->
                <div class="p-4 rounded-lg bg-gray-50">
                  <div class="block mb-1 text-xs font-medium tracking-wider text-gray-500 uppercase">
                    ID do Usuário
                  </div>
                  <p class="font-mono text-sm text-gray-900">#{{ user.id }}</p>
                </div>

                <!-- Tipo de Usuário -->
                <div class="p-4 rounded-lg bg-gray-50">
                  <div class="block mb-1 text-xs font-medium tracking-wider text-gray-500 uppercase">
                    Tipo de Usuário
                  </div>
                  <div class="flex items-center space-x-2">
                    <span
                      class="inline-flex px-3 py-1 text-xs font-semibold rounded-full"
                      :class="user.is_admin
                        ? 'bg-purple-100 text-purple-800'
                        : 'bg-green-100 text-green-800'"
                    >
                      {{ user.is_admin ? 'Administrador' : 'Usuário' }}
                    </span>
                    <svg
                      v-if="user.is_admin"
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
                <div class="p-4 rounded-lg bg-gray-50">
                  <div class="block mb-1 text-xs font-medium tracking-wider text-gray-500 uppercase">
                    Membro desde
                  </div>
                  <p class="text-sm text-gray-900">{{ formatDate(user.created_at) }}</p>
                </div>

                <!-- Última Atualização -->
                <div class="p-4 rounded-lg bg-gray-50">
                  <div class="block mb-1 text-xs font-medium tracking-wider text-gray-500 uppercase">
                    Última atualização
                  </div>
                  <p class="text-sm text-gray-900">{{ formatDate(user.updated_at) }}</p>
                </div>
              </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex pt-6 space-x-3 border-t border-gray-200">
              <button
                @click="editUser"
                class="flex-1 px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
              </button>
              <button
                @click="deleteUser"
                :disabled="user?.id === currentUserId"
                class="flex-1 px-4 py-2 text-sm font-medium text-white transition-colors bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                :title="user?.id === currentUserId ? 'Não é possível deletar seu próprio usuário' : 'Deletar usuário'"
              >
                <svg class="inline-block w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Deletar
              </button>
              <button
                @click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                Fechar
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'

interface User {
  id: number
  name: string
  email: string
  is_admin: boolean
  created_at: string
  updated_at: string
}

interface Props {
  isVisible: boolean
  user?: User | null
  currentUserId?: number
}

interface Emits {
  (e: 'close'): void
  (e: 'edit', user: User): void
  (e: 'delete', user: User): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const closeModal = () => {
  emit('close')
}

const editUser = () => {
  if (props.user) {
    emit('edit', props.user)
  }
}

const deleteUser = () => {
  if (props.user) {
    emit('delete', props.user)
  }
}

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

const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && props.isVisible) {
    closeModal()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>
