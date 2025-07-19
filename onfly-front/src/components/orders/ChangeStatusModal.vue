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
            class="relative z-10 w-full max-w-md p-6 mx-4 bg-white rounded-lg shadow-xl"
            @click.stop
          >
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900">
                Alterar Status do Pedido #{{ order?.id }}
              </h3>
              <button
                @click="closeModal"
                class="text-gray-400 hover:text-gray-600 focus:outline-none"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Content -->
            <div v-if="order" class="space-y-6">
              <!-- Status atual -->
              <div class="p-4 rounded-lg bg-gray-50">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-700">Status atual:</span>
                  <span
                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    :class="statusColors[order.status] || 'bg-gray-100 text-gray-800'"
                  >
                    {{ statusLabels[order.status] || order.status }}
                  </span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                  Pedido: {{ order.destination }}
                </div>
              </div>

              <!-- Aviso sobre regras -->
              <div class="p-3 border border-blue-200 rounded-md bg-blue-50">
                <div class="flex">
                  <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                  </svg>
                  <div class="ml-3">
                    <p class="text-sm text-blue-700">
                      <strong>Regras:</strong> Pedidos aprovados não podem ser cancelados. Pedidos cancelados não podem ser alterados.
                    </p>
                  </div>
                </div>
              </div>

              <!-- Seleção de novo status -->
              <div>
                <label for="newStatus" class="block mb-2 text-sm font-medium text-gray-700">
                  Alterar para:
                </label>
                <select
                  id="newStatus"
                  v-model="selectedStatus"
                  class="w-full px-3 py-2 text-black transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none"
                >
                  <option value="">Selecione um status</option>
                  <option 
                    v-for="(label, status) in availableStatuses" 
                    :key="status" 
                    :value="status"
                  >
                    {{ label }}
                  </option>
                </select>
                <span v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</span>
              </div>

              <!-- Botões -->
              <div class="flex pt-4 space-x-3">
                <button
                  type="button"
                  @click="closeModal"
                  class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                  Cancelar
                </button>
                <button
                  type="button"
                  @click="changeStatus"
                  :disabled="!selectedStatus || isLoading"
                  class="flex-1 px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="isLoading">Alterando...</span>
                  <span v-else>Alterar Status</span>
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import type { Order } from '../../types/index'
import api from '../../services/api'

interface Props {
  show: boolean
  order?: Order | null
}

interface Emits {
  (e: 'close'): void
  (e: 'statusChanged'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isVisible = ref(false)
const isLoading = ref(false)
const selectedStatus = ref('')
const error = ref('')

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

const availableStatuses = computed(() => {
  if (!props.order) return {}
  
  const currentStatus = props.order.status
  const available: Record<string, string> = {}
  
  if (currentStatus === 'requested') {
    available.approved = 'Aprovar'
    available.cancelled = 'Cancelar'
  }
  
  return available
})

const changeStatus = async () => {
  if (!props.order || !selectedStatus.value) return
  
  isLoading.value = true
  error.value = ''
  
  try {
    await api.patch(`/orders/${props.order.id}/status`, {
      status: selectedStatus.value
    })
    
    emit('statusChanged')
    closeModal()
  } catch (err: unknown) {
    const axiosError = err as { response?: { data?: { error?: string } } }
    error.value = axiosError.response?.data?.error || 'Erro ao alterar status'
  } finally {
    isLoading.value = false
  }
}

const closeModal = () => {
  emit('close')
}

const resetForm = () => {
  selectedStatus.value = ''
  error.value = ''
}

const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && isVisible.value) {
    closeModal()
  }
}

watch(() => props.show, (newValue) => {
  isVisible.value = newValue
  if (newValue) {
    resetForm()
  }
}, { immediate: true })

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>
