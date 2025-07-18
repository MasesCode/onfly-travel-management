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
        @click.self="cancel"
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
              Excluir Pedido
            </h3>

            <!-- Mensagem -->
            <div class="mb-6 text-sm text-center text-gray-600">
              <p class="mb-2">
                Tem certeza que deseja excluir o pedido para <strong>{{ order?.destination }}</strong>?
              </p>
              <p class="text-xs text-gray-500">
                Esta ação não pode ser desfeita.
              </p>
            </div>

            <!-- Informações do pedido -->
            <div v-if="order" class="mb-6 p-3 bg-gray-50 rounded-lg">
              <div class="text-xs text-gray-600 space-y-1">
                <div><strong>ID:</strong> #{{ order.id }}</div>
                <div><strong>Destino:</strong> {{ order.destination }}</div>
                <div><strong>Período:</strong> {{ formatDate(order.start_date) }} - {{ formatDate(order.end_date) }}</div>
                <div>
                  <strong>Status:</strong>
                  <span
                    class="inline-flex px-2 py-1 ml-1 text-xs font-semibold rounded-full"
                    :class="statusColors[order.status] || 'bg-gray-100 text-gray-800'"
                  >
                    {{ statusLabels[order.status] || order.status }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Botões -->
            <div class="flex space-x-3">
              <button
                @click="cancel"
                :disabled="props.isLoading"
                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Cancelar
              </button>
              <button
                @click="confirm"
                :disabled="props.isLoading"
                class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="props.isLoading">Excluindo...</span>
                <span v-else>Excluir</span>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted } from 'vue'
import type { Order } from '../../types/index'

interface Props {
  show: boolean
  order?: Order | null
  isLoading?: boolean
}

interface Emits {
  (e: 'confirm'): void
  (e: 'close'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isVisible = ref(false)

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

const confirm = () => {
  emit('confirm')
}

const cancel = () => {
  emit('close')
}

// Formatar data
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('pt-BR')
}

// Fechar modal com ESC
const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && isVisible.value) {
    cancel()
  }
}

// Observar mudanças no prop show
watch(() => props.show, (newValue) => {
  isVisible.value = newValue
}, { immediate: true })

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>
