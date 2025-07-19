<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useNotifications } from '../../composables/useNotifications'
import GuestLayout from '../../layouts/GuestLayout.vue'
import TextInput from '../../components/TextInput.vue'

const router = useRouter()
const authStore = useAuthStore()
const { showError, showSuccess } = useNotifications()

const isSubmitting = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const handleRegister = async () => {
  if (isSubmitting.value) return

  if (!form.name || !form.email || !form.password || !form.password_confirmation) {
    showError('Por favor, preencha todos os campos obrigatórios.')
    return
  }

  if (form.password !== form.password_confirmation) {
    showError('As senhas não coincidem.')
    return
  }

  if (form.password.length < 8) {
    showError('A senha deve ter pelo menos 8 caracteres.')
    return
  }

  isSubmitting.value = true

  try {
    await authStore.register(form.name, form.email, form.password, form.password_confirmation)
    showSuccess('Conta criada com sucesso! Bem-vindo(a) ao onfly!')
    router.push('/dashboard')
  } catch (error: unknown) {
    console.error('Erro no registro:', error)

    const axiosError = error as {
      response?: {
        status?: number;
        data?: {
          errors?: Record<string, string[]>;
          message?: string
        }
      };
      code?: string
    }

    if (axiosError?.response?.status === 422) {
      const errorData = axiosError.response.data

      if (errorData?.errors) {
        const firstFieldErrors = Object.values(errorData.errors)[0] as string[]
        const errorMessage = firstFieldErrors[0]

        if (errorMessage.includes('The email has already been taken')) {
          showError('Este e-mail já está cadastrado no sistema. Tente fazer login ou use outro e-mail.')
        } else if (errorMessage.includes('password confirmation does not match')) {
          showError('A confirmação da senha não confere com a senha digitada.')
        } else if (errorMessage.includes('password must be at least 8 characters')) {
          showError('A senha deve ter pelo menos 8 caracteres.')
        } else {
          showError(errorMessage)
        }
      } else if (errorData?.message) {
        showError(errorData.message)
      } else {
        showError('Dados inválidos. Verifique os campos e tente novamente.')
      }
    } else if (axiosError?.response?.data?.message) {
      const message = axiosError.response.data.message
      if (message.includes('email') && (message.includes('taken') || message.includes('exists'))) {
        showError('Este e-mail já está cadastrado no sistema. Tente fazer login ou use outro e-mail.')
      } else {
        showError(message)
      }
    } else if (axiosError?.response?.status && axiosError.response.status >= 500) {
      showError('Erro interno do servidor. Tente novamente em alguns minutos.')
    } else if (axiosError?.code === 'NETWORK_ERROR' || !axiosError?.response) {
      showError('Erro de conexão. Verifique sua internet e tente novamente.')
    } else {
      showError('Erro ao criar conta. Verifique os dados e tente novamente.')
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <GuestLayout>
    <!-- Título -->
    <div class="mb-6 text-center">
      <h1 class="text-2xl font-semibold text-gray-900">Criar Conta</h1>
      <p class="mt-2 text-sm text-gray-600">
        Já tem uma conta?
        <router-link
          to="/login"
          class="font-medium text-blue-600 transition duration-150 ease-in-out hover:text-blue-500"
        >
          Faça login
        </router-link>
      </p>
    </div>

    <!-- Formulário -->
    <form @submit.prevent="handleRegister" class="space-y-4">
      <!-- Nome -->
      <div>
        <label for="name" class="block mb-1 text-sm font-medium text-gray-700">
          Nome Completo
        </label>
        <TextInput
          id="name"
          v-model="form.name"
          type="text"
          required
          autofocus
          autocomplete="given-name"
          placeholder="Digite seu nome completo"
        />
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block mb-1 text-sm font-medium text-gray-700">
          Email
        </label>
        <TextInput
          id="email"
          v-model="form.email"
          type="email"
          required
          autocomplete="email"
          placeholder="Digite seu email"
        />
      </div>

      <!-- Senha -->
      <div>
        <label for="password" class="block mb-1 text-sm font-medium text-gray-700">
          Senha
        </label>
        <TextInput
          id="password"
          v-model="form.password"
          type="password"
          required
          autocomplete="new-password"
          placeholder="Digite sua senha"
        />
      </div>

      <!-- Confirmar Senha -->
      <div>
        <label for="password_confirmation" class="block mb-1 text-sm font-medium text-gray-700">
          Confirmar Senha
        </label>
        <TextInput
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          required
          autocomplete="new-password"
          placeholder="Confirme sua senha"
        />
      </div>

      <!-- Botão de Registro -->
      <div class="pt-4">
        <button
          type="submit"
          :disabled="isSubmitting"
          class="flex justify-center w-full px-4 py-2 text-sm font-medium text-white transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg
            v-if="isSubmitting"
            class="w-5 h-5 mr-3 -ml-1 text-white animate-spin"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ isSubmitting ? 'Criando conta...' : 'Criar conta' }}
        </button>
      </div>
    </form>

    <!-- Termos e Condições -->
    <div class="mt-6 text-center">
      <p class="text-xs text-gray-500">
        Ao criar uma conta, você concorda com nossos
        <a href="#" class="text-blue-600 hover:text-blue-500">Termos de Serviço</a>
        e
        <a href="#" class="text-blue-600 hover:text-blue-500">Política de Privacidade</a>
      </p>
    </div>
  </GuestLayout>
</template>
