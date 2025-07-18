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
                Novo Pedido de Viagem
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

            <!-- Form -->
            <form @submit.prevent="submitForm" class="space-y-4">
              <!-- Destino -->
              <div>
                <label for="destination" class="block text-sm font-medium text-gray-700 mb-1">
                  Destino
                </label>
                <input
                  id="destination"
                  v-model="form.destination"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black placeholder-gray-400 bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none transition duration-150 ease-in-out"
                  placeholder="Digite o destino da viagem"
                />
                <span v-if="errors.destination" class="text-sm text-red-600">{{ errors.destination }}</span>
              </div>

              <!-- Data de Início -->
              <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                  Data de Início
                </label>
                <input
                  id="start_date"
                  v-model="form.start_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none transition duration-150 ease-in-out"
                />
                <span v-if="errors.start_date" class="text-sm text-red-600">{{ errors.start_date }}</span>
              </div>

              <!-- Data de Fim -->
              <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                  Data de Fim
                </label>
                <input
                  id="end_date"
                  v-model="form.end_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none transition duration-150 ease-in-out"
                />
                <span v-if="errors.end_date" class="text-sm text-red-600">{{ errors.end_date }}</span>
              </div>

              <!-- Botões -->
              <div class="flex space-x-3 pt-4">
                <button
                  type="button"
                  @click="closeModal"
                  class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  :disabled="isLoading"
                  class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="isLoading">Criando...</span>
                  <span v-else>Criar Pedido</span>
                </button>
              </div>
            </form>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, reactive, watch, onMounted, onUnmounted } from 'vue'

interface OrderFormData {
  destination: string
  start_date: string
  end_date: string
}

interface Props {
  show: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'created', orderData: OrderFormData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isVisible = ref(false)
const isLoading = ref(false)

const form = reactive({
  destination: '',
  start_date: '',
  end_date: ''
})

const errors = reactive({
  destination: '',
  start_date: '',
  end_date: ''
})

// Resetar formulário
const resetForm = () => {
  form.destination = ''
  form.start_date = ''
  form.end_date = ''
  clearErrors()
}

const clearErrors = () => {
  errors.destination = ''
  errors.start_date = ''
  errors.end_date = ''
}

// Validar formulário
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

// Submeter formulário
const submitForm = async () => {
  if (!validateForm()) return

  isLoading.value = true

  try {
    const orderData = { ...form }
    emit('created', orderData)
  } finally {
    isLoading.value = false
  }
}

// Fechar modal
const closeModal = () => {
  emit('close')
}

// Fechar modal com ESC
const handleKeydown = (e: KeyboardEvent) => {
  if (e.key === 'Escape' && isVisible.value) {
    closeModal()
  }
}

// Observar mudanças no prop show
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
