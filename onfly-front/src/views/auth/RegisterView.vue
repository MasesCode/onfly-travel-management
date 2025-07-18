<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotifications } from '@/composables/useNotifications'
import GuestLayout from '@/layouts/GuestLayout.vue'
import TextInput from '@/components/TextInput.vue'
import InputError from '@/components/InputError.vue'

const router = useRouter()
const authStore = useAuthStore()
const { showError } = useNotifications()

const isSubmitting = ref(false)

// Formulário de registro
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const errors = ref<Record<string, string[]>>({})

// Função de registro
async function handleRegister() {
  if (isSubmitting.value) return

  isSubmitting.value = true
  errors.value = {}

  try {
    const result = await authStore.register({
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    })

    if (result.success) {
      // Registro bem-sucedido, redirecionar para dashboard
      router.push('/dashboard')
    } else {
      showError(result.message || 'Erro ao criar conta')
    }
  } catch (error: unknown) {
    const axiosError = error as {
      response?: {
        status?: number
        data?: {
          errors?: Record<string, string[]>
          message?: string
        }
      }
    }

    if (axiosError.response?.status === 422) {
      // Erros de validação
      errors.value = axiosError.response.data?.errors || {}
    } else {
      const message = axiosError.response?.data?.message || 'Erro ao criar conta'
      showError(message)
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
          class="font-medium text-blue-600 hover:text-blue-500 transition duration-150 ease-in-out"
        >
          Faça login
        </router-link>
      </p>
    </div>

    <!-- Formulário -->
    <form @submit.prevent="handleRegister" class="space-y-4">
      <!-- Nome -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
          Nome Completo
        </label>
        <TextInput
          id="name"
          v-model="form.name"
          type="text"
          required
          autofocus
          autocomplete="name"
          placeholder="Digite seu nome completo"
        />
        <InputError v-if="errors.name" :message="errors.name[0]" class="mt-2" />
      </div>

      <!-- Email -->
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
          Email
        </label>
        <TextInput
          id="email"
          v-model="form.email"
          type="email"
          required
          autocomplete="username"
          placeholder="Digite seu email"
        />
        <InputError v-if="errors.email" :message="errors.email[0]" class="mt-2" />
      </div>

      <!-- Senha -->
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
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
        <InputError v-if="errors.password" :message="errors.password[0]" class="mt-2" />
      </div>

      <!-- Confirmar Senha -->
      <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
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
        <InputError v-if="errors.password_confirmation" :message="errors.password_confirmation[0]" class="mt-2" />
      </div>

      <!-- Botão de Registro -->
      <div class="pt-4">
        <button
          type="submit"
          :disabled="isSubmitting"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out"
        >
          <svg
            v-if="isSubmitting"
            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
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
