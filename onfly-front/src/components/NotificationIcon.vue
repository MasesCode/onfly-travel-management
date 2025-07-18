<template>
  <div class="relative">
    <button
      @click="toggleDropdown"
      type="button"
      class="relative p-2 text-white transition-colors duration-200 rounded-full hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-600"
      :class="{ 'bg-blue-500': isOpen }"
    >
      <!-- Ícone de sino -->
      <svg
        class="w-6 h-6"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
        ></path>
      </svg>

      <!-- Badge de contagem -->
      <span
        v-if="unreadCount > 0"
        class="absolute flex items-center justify-center w-5 h-5 text-xs font-bold text-blue-600 bg-white rounded-full -top-1 -right-1"
      >
        {{ unreadCount }}
      </span>
    </button>

    <!-- Overlay de fundo completo -->
    <div
      v-show="isOpen"
      class="fixed inset-0 z-40"
      @click="close"
    ></div>

    <!-- Dropdown de notificações com transição -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="scale-95 -translate-y-1 opacity-0"
      enter-to-class="scale-100 translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="scale-100 translate-y-0 opacity-100"
      leave-to-class="scale-95 -translate-y-1 opacity-0"
    >
      <div
        v-show="isOpen"
        class="absolute right-0 z-50 mt-2 origin-top-right bg-white border border-gray-200 rounded-lg shadow-xl w-80"
        @click.stop
      >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Notificações</h3>
            <div class="flex space-x-2">
              <button
                v-if="visibleNotifications.length > 0"
                @click.stop="markAllRead"
                class="text-xs text-blue-600 transition-colors hover:text-blue-800 focus:outline-none"
                title="Marcar todas como lidas"
              >
                Marcar como lidas
              </button>
              <button
                v-if="visibleNotifications.length > 0"
                @click.stop="clearAllNotifications"
                class="text-xs text-red-600 transition-colors hover:text-red-800 focus:outline-none"
                title="Limpar todas as notificações"
              >
                Limpar todas
              </button>
            </div>
          </div>
        </div>

        <!-- Lista de notificações -->
        <div class="overflow-y-auto max-h-96">
          <div v-if="visibleNotifications.length === 0" class="px-4 py-8 text-center text-gray-500">
            <div class="mb-3 text-4xl">🔔</div>
            <p>Nenhuma notificação</p>
          </div>

          <div v-else>
            <div
              v-for="notification in visibleNotifications"
              :key="notification.id"
              @click.stop="markRead(notification.id)"
              class="px-4 py-3 transition-colors border-b border-gray-100 cursor-pointer hover:bg-gray-50"
              :class="{ 'bg-blue-50': !notification.read }"
            >
              <div class="flex items-start">
                <div class="flex-shrink-0 mr-3">
                  <!-- Ícone de sucesso (aprovado) -->
                  <div
                    v-if="notification.type === 'approved'"
                    class="flex items-center justify-center w-8 h-8 bg-green-100 rounded-full"
                  >
                    ✅
                  </div>

                  <!-- Ícone de erro (cancelado) -->
                  <div
                    v-else-if="notification.type === 'cancelled'"
                    class="flex items-center justify-center w-8 h-8 bg-red-100 rounded-full"
                  >
                    ❌
                  </div>
                </div>

                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900">
                    {{ notification.title }}
                  </p>
                  <p class="text-sm text-gray-600">
                    {{ notification.message }}
                  </p>
                  <p class="mt-1 text-xs text-gray-400">
                    {{ formatTime(notification.created_at) }}
                  </p>
                </div>

                <!-- Indicador de não lida -->
                <div v-if="!notification.read" class="flex-shrink-0 ml-2">
                  <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="visibleNotifications.length > 0" class="px-4 py-3 border-t border-gray-200">
          <router-link
            to="/dashboard"
            @click="close"
            class="block w-full text-sm text-center text-blue-600 transition-colors hover:text-blue-800 focus:outline-none"
          >
            Ver todos os pedidos
          </router-link>
        </div>
      </div>
    </Transition>

    <!-- Modal de confirmação para limpar notificações -->
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
          v-show="showConfirmModal"
          class="fixed inset-0 z-[9999] flex items-center justify-center"
          @click.self="showConfirmModal = false"
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
              v-show="showConfirmModal"
              class="relative z-10 w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl"
              @click.stop
            >
              <!-- Ícone de aviso -->
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                <svg
                  class="w-6 h-6 text-red-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                  />
                </svg>
              </div>

              <!-- Título -->
              <h3 class="mb-2 text-lg font-semibold text-center text-gray-900">
                Limpar todas as notificações
              </h3>

              <!-- Mensagem -->
              <p class="mb-6 text-sm text-center text-gray-600">
                Tem certeza que deseja limpar todas as notificações? Esta ação não pode ser desfeita.
              </p>

              <!-- Botões -->
              <div class="flex space-x-3">
                <button
                  @click="showConfirmModal = false"
                  class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                  Cancelar
                </button>
                <button
                  @click="confirmClearNotifications"
                  class="flex-1 px-4 py-2 text-sm font-medium text-white transition-colors bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                >
                  Sim, limpar
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

interface Notification {
  id: string
  type: 'approved' | 'cancelled' | 'pending' | 'info'
  title: string
  message: string
  read: boolean
  deleted: boolean // Para compatibilidade
  created_at: string
  order_id?: number
  destination?: string
}

// Estado local
const isOpen = ref(false)
const showConfirmModal = ref(false)
const notifications = ref<Notification[]>([])
const loading = ref(false)

// Computed
const unreadCount = computed(() =>
  notifications.value.filter(n => !n.read && !n.deleted).length
)

// Filtrar notificações não deletadas para exibição
const visibleNotifications = computed(() =>
  notifications.value.filter(n => !n.deleted)
)

// Função para buscar notificações da API
const initializeNotifications = async () => {
  try {
    loading.value = true
    const token = localStorage.getItem('auth_token')

    const response = await fetch('/api/notifications', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    })

    if (response.ok) {
      const data = await response.json()
      notifications.value = data.data || []
      saveNotificationsToStorage(notifications.value)
    } else {
      console.error('Erro ao carregar notificações:', response.statusText)
    }
  } catch (error) {
    console.error('Erro ao carregar notificações:', error)
  } finally {
    loading.value = false
  }
}

// Função global para atualizar notificações (pode ser chamada de outros componentes)
declare global {
  interface Window {
    refreshNotifications: () => Promise<void>
  }
}
window.refreshNotifications = initializeNotifications

// Função para salvar notificações no localStorage (backup)
const saveNotificationsToStorage = (notifications: Notification[]) => {
  try {
    localStorage.setItem('notifications', JSON.stringify(notifications))
  } catch (error) {
    console.error('Erro ao salvar notificações no localStorage:', error)
  }
}

// Função para fechar o dropdown quando ESC for pressionado
const closeOnEscape = (e: KeyboardEvent) => {
  if (e.key === 'Escape') {
    if (showConfirmModal.value) {
      showConfirmModal.value = false
    } else if (isOpen.value) {
      isOpen.value = false
    }
  }
}

// Adicionar/remover event listener para tecla ESC
onMounted(() => document.addEventListener('keydown', closeOnEscape))
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape))

// Métodos
const toggleDropdown = async () => {
  console.log('Toggle dropdown:', !isOpen.value)

  // Se estiver fechando o dropdown, apenas muda o estado
  if (isOpen.value) {
    isOpen.value = false
    return
  }

  // Se estiver abrindo, busca as notificações da API primeiro
  isOpen.value = true
  await initializeNotifications()
}

const close = () => {
  console.log('Fechando dropdown')
  isOpen.value = false
}

const markRead = async (id: string) => {
  const notification = notifications.value.find(n => n.id === id)
  if (notification) {
    notification.read = true
    saveNotificationsToStorage(notifications.value)

    try {
      await fetch(`/api/notifications/${id}/read`, {
        method: 'PATCH',
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
          'Content-Type': 'application/json',
        },
      })
    } catch (error) {
      console.error('Erro ao marcar notificação como lida:', error)
    }
  }
}

const markAllRead = async () => {
  visibleNotifications.value.forEach(n => n.read = true)
  saveNotificationsToStorage(notifications.value)

  try {
    await fetch('/api/notifications/mark-all-read', {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
      },
    })
  } catch (error) {
    console.error('Erro ao marcar todas como lidas:', error)
  }
}

// Função para limpar todas as notificações (soft delete)
const clearAllNotifications = () => {
  showConfirmModal.value = true
}

const confirmClearNotifications = async () => {
  // Marca todas as notificações como deletadas (soft delete)
  notifications.value.forEach(n => {
    if (!n.deleted) {
      n.deleted = true
    }
  })

  // Salva no localStorage
  saveNotificationsToStorage(notifications.value)

  // Fecha o modal e o dropdown
  showConfirmModal.value = false
  close()

  try {
    await fetch('/api/notifications', {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('auth_token')}`,
        'Content-Type': 'application/json',
      },
    })
  } catch (error) {
    console.error('Erro ao limpar notificações:', error)
  }
}

// Inicializa as notificações ao montar o componente
onMounted(async () => {
  await initializeNotifications()
})



const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = (now.getTime() - date.getTime()) / (1000 * 60 * 60)

  if (diffInHours < 1) {
    return 'agora mesmo'
  } else if (diffInHours < 24) {
    return `${Math.floor(diffInHours)}h atrás`
  } else {
    return `${Math.floor(diffInHours / 24)}d atrás`
  }
}
</script>
