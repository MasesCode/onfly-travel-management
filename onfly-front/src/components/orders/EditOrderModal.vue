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
              <h3 class="text-lg font-semibold text-gray-900">
                Editar Pedido #{{ order?.id }}
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

            <!-- Detalhes do Pedido -->
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
                  Criado em: {{ formatDate(order.created_at) }}
                </div>
              </div>

              <!-- Form de edição -->
              <form @submit.prevent="submitForm" class="space-y-4">
                <!-- Destino -->
                <div>
                  <label for="destination" class="block mb-1 text-sm font-medium text-gray-700">
                    Destino
                  </label>
                  <input
                    id="destination"
                    v-model="form.destination"
                    type="text"
                    required
                    :disabled="!canEdit"
                    class="w-full px-3 py-2 text-black placeholder-gray-400 transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none disabled:bg-gray-100 disabled:cursor-not-allowed"
                    placeholder="Digite o destino da viagem"
                  />
                  <span v-if="errors.destination" class="text-sm text-red-600">{{ errors.destination }}</span>
                </div>

                <!-- Data de Início -->
                <div>
                  <label for="start_date" class="block mb-1 text-sm font-medium text-gray-700">
                    Data de Início
                  </label>
                  <input
                    id="start_date"
                    v-model="form.start_date"
                    type="date"
                    required
                    :disabled="!canEdit"
                    class="w-full px-3 py-2 text-black transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none disabled:bg-gray-100 disabled:cursor-not-allowed"
                  />
                  <span v-if="errors.start_date" class="text-sm text-red-600">{{ errors.start_date }}</span>
                </div>

                <!-- Data de Fim -->
                <div>
                  <label for="end_date" class="block mb-1 text-sm font-medium text-gray-700">
                    Data de Fim
                  </label>
                  <input
                    id="end_date"
                    v-model="form.end_date"
                    type="date"
                    required
                    :disabled="!canEdit"
                    class="w-full px-3 py-2 text-black transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none disabled:bg-gray-100 disabled:cursor-not-allowed"
                  />
                  <span v-if="errors.end_date" class="text-sm text-red-600">{{ errors.end_date }}</span>
                </div>

                <!-- Status (apenas para admin) -->
                <div v-if="authStore.isAdmin">
                  <label for="status" class="block mb-1 text-sm font-medium text-gray-700">
                    Status
                  </label>
                  <select
                    id="status"
                    v-model="form.status"
                    class="w-full px-3 py-2 text-black transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none"
                  >
                    <option value="requested">Solicitado</option>
                    <option value="approved" :disabled="props.order?.status === 'cancelled'">Aprovado</option>
                    <option value="cancelled" :disabled="props.order?.status === 'approved'">Cancelado</option>
                  </select>
                  <span v-if="errors.status" class="text-sm text-red-600">{{ errors.status }}</span>
                  <div v-if="props.order?.status === 'approved'" class="mt-1 text-xs text-gray-500">
                    Pedidos aprovados não podem ser cancelados
                  </div>
                  <div v-if="props.order?.status === 'cancelled'" class="mt-1 text-xs text-gray-500">
                    Pedidos cancelados não podem ter o status alterado
                  </div>
                </div>

                <!-- Aviso se não pode editar -->
                <div v-if="!canEdit" class="p-3 border border-yellow-200 rounded-md bg-yellow-50">
                  <div class="flex">
                    <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="ml-3">
                      <p class="text-sm text-yellow-700">
                        Este pedido não pode ser editado devido ao seu status atual.
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Botões -->
                <div class="flex pt-4 space-x-3">
                  <button
                    type="button"
                    @click="closeModal"
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 transition-colors bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                  >
                    {{ canEdit ? 'Cancelar' : 'Fechar' }}
                  </button>
                  <button
                    v-if="canEdit"
                    type="submit"
                    :disabled="isLoading"
                    class="flex-1 px-4 py-2 text-sm font-medium text-white transition-colors bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <span v-if="isLoading">Salvando...</span>
                    <span v-else>Salvar Alterações</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch, onMounted, onUnmounted } from 'vue'
import type { Order } from '../../types/index'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

interface OrderFormData {
  destination: string
  start_date: string
  end_date: string
  status?: string
}

interface Props {
  show: boolean
  order?: Order | null
}

interface Emits {
  (e: 'close'): void
  (e: 'updated', orderData: OrderFormData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const authStore = useAuthStore()
const isVisible = ref(false)
const isLoading = ref(false)

const form = reactive({
  destination: '',
  start_date: '',
  end_date: '',
  status: ''
})

const errors = reactive({
  destination: '',
  start_date: '',
  end_date: '',
  status: ''
})

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

const canEdit = computed(() => {
  if (!props.order) return false
  
  if (props.order.status === 'cancelled') return false
  
  if (authStore.isAdmin) return true
  
  return props.order.status === 'requested' && props.order.user_id === authStore.user?.id
})

const formatDateForInput = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toISOString().split('T')[0]
}

const resetForm = () => {
  if (props.order) {
    form.destination = props.order.destination
    form.start_date = formatDateForInput(props.order.start_date)
    form.end_date = formatDateForInput(props.order.end_date)
    form.status = props.order.status
  } else {
    form.destination = ''
    form.start_date = ''
    form.end_date = ''
    form.status = ''
  }
  clearErrors()
}

const clearErrors = () => {
  errors.destination = ''
  errors.start_date = ''
  errors.end_date = ''
  errors.status = ''
}

const validateForm = (): boolean => {
  clearErrors()
  let isValid = true

  if (!form.destination.trim()) {
    errors.destination = 'Destino é obrigatório'
    isValid = false
  }

  if (!form.start_date) {
    errors.start_date = 'Data de início é obrigatória'
    isValid = false
  }

  if (!form.end_date) {
    errors.end_date = 'Data de fim é obrigatória'
    isValid = false
  }

  if (form.start_date && form.end_date && new Date(form.start_date) > new Date(form.end_date)) {
    errors.end_date = 'Data de fim deve ser posterior à data de início'
    isValid = false
  }

  return isValid
}

const submitForm = async () => {
  if (!canEdit.value || !validateForm()) return

  isLoading.value = true

  try {
    const orderData = { ...form }
    
    if (authStore.isAdmin && form.status !== props.order?.status) {
      await api.patch(`/orders/${props.order?.id}/status`, {
        status: form.status
      })
    }
    
    emit('updated', orderData)
  } finally {
    isLoading.value = false
  }
}

const closeModal = () => {
  emit('close')
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

watch(() => props.order, () => {
  if (props.show) {
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
