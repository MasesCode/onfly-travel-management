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
                {{ isEditing ? 'Editar Usuário' : 'Criar Usuário' }}
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
              <!-- Nome -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                  Nome
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black placeholder-gray-400 bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none transition duration-150 ease-in-out"
                  placeholder="Digite o nome completo"
                />
                <span v-if="errors.name" class="text-sm text-red-600">{{ errors.name }}</span>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                  Email
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black placeholder-gray-400 bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none transition duration-150 ease-in-out"
                  placeholder="Digite o email"
                />
                <span v-if="errors.email" class="text-sm text-red-600">{{ errors.email }}</span>
              </div>

              <!-- Senha (apenas para criação ou se quiser alterar) -->
              <div v-if="!isEditing || showPasswordFields">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                  {{ isEditing ? 'Nova Senha' : 'Senha' }}
                </label>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  :required="!isEditing"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black placeholder-gray-400 bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none transition duration-150 ease-in-out"
                  placeholder="Digite a senha"
                />
                <span v-if="errors.password" class="text-sm text-red-600">{{ errors.password }}</span>
              </div>

              <!-- Confirmar Senha -->
              <div v-if="(!isEditing || showPasswordFields) && form.password">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                  Confirmar Senha
                </label>
                <input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  :required="!!form.password"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm text-black placeholder-gray-400 bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 focus:outline-none transition duration-150 ease-in-out"
                  placeholder="Confirme a senha"
                />
                <span v-if="errors.password_confirmation" class="text-sm text-red-600">{{ errors.password_confirmation }}</span>
              </div>

              <!-- Botão para mostrar campos de senha (apenas na edição) -->
              <div v-if="isEditing && !showPasswordFields">
                <button
                  type="button"
                  @click="showPasswordFields = true"
                  class="text-sm text-blue-600 hover:text-blue-800 focus:outline-none"
                >
                  Alterar senha
                </button>
              </div>

              <!-- É Admin -->
              <div class="flex items-center">
                <input
                  id="is_admin"
                  v-model="form.is_admin"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                />
                <label for="is_admin" class="ml-2 text-sm font-medium text-gray-700">
                  Usuário Administrador
                </label>
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
                  <span v-if="isLoading">Salvando...</span>
                  <span v-else>{{ isEditing ? 'Atualizar' : 'Criar' }}</span>
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
  password?: string | undefined
  password_confirmation?: string | undefined
  is_admin: boolean
}

interface Props {
  isVisible: boolean
  user?: User | null
}

interface Emits {
  (e: 'close'): void
  (e: 'submit', userData: UserFormData): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isEditing = ref(false)
const isLoading = ref(false)
const showPasswordFields = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  is_admin: false
})

const errors = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

// Resetar formulário
const resetForm = () => {
  form.name = ''
  form.email = ''
  form.password = ''
  form.password_confirmation = ''
  form.is_admin = false
  showPasswordFields.value = false
  clearErrors()
}

const clearErrors = () => {
  errors.name = ''
  errors.email = ''
  errors.password = ''
  errors.password_confirmation = ''
}

// Validar formulário
const validateForm = () => {
  clearErrors()
  let isValid = true

  if (!form.name.trim()) {
    errors.name = 'Nome é obrigatório'
    isValid = false
  }

  if (!form.email.trim()) {
    errors.email = 'Email é obrigatório'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = 'Email inválido'
    isValid = false
  }

  if (!isEditing.value && !form.password) {
    errors.password = 'Senha é obrigatória'
    isValid = false
  }

  if (form.password && form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Senhas não coincidem'
    isValid = false
  }

  if (form.password && form.password.length < 6) {
    errors.password = 'Senha deve ter pelo menos 6 caracteres'
    isValid = false
  }

  return isValid
}

// Submeter formulário
const submitForm = async () => {
  if (!validateForm()) return

  isLoading.value = true

  try {
    const userData = { ...form }

    // Remove campos de senha vazios na edição
    if (isEditing.value && !form.password) {
      userData.password = undefined
      userData.password_confirmation = undefined
    }

    emit('submit', userData)
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
  if (e.key === 'Escape' && props.isVisible) {
    closeModal()
  }
}

// Observar mudanças no prop user
watch(() => props.user, (newUser) => {
  if (newUser) {
    isEditing.value = true
    form.name = newUser.name
    form.email = newUser.email
    form.password = ''
    form.password_confirmation = ''
    form.is_admin = newUser.is_admin
    showPasswordFields.value = false
  } else {
    isEditing.value = false
    resetForm()
  }
}, { immediate: true })

// Observar visibilidade do modal
watch(() => props.isVisible, (visible) => {
  if (!visible) {
    setTimeout(resetForm, 300) // Aguarda animação terminar
  }
})

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>
