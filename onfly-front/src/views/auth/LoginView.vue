<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import GuestLayout from '@/layouts/GuestLayout.vue'
import TextInput from '@/components/TextInput.vue'
import InputError from '@/components/InputError.vue'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
})

const errors = ref<Record<string, string>>({})
const processing = ref(false)
const status = ref('')

const submit = async () => {
  processing.value = true
  errors.value = {}

  const result = await authStore.login(form.value)

  if (result.success) {
    router.push('/dashboard')
  } else {
    if (result.message) {
      status.value = result.message
    }
  }

  processing.value = false
}
</script>

<template>
  <GuestLayout>
    <div class="mb-6 text-center">
      <h1 class="text-2xl font-semibold text-gray-900">Entrar</h1>
      <p class="mt-2 text-sm text-gray-600">
        Não tem uma conta?
        <router-link 
          to="/register" 
          class="font-medium text-blue-600 hover:text-blue-500 transition duration-150 ease-in-out"
        >
          Criar nova conta
        </router-link>
      </p>
    </div>

    <div v-if="status" class="mb-4 text-sm font-medium text-red-600 text-center bg-red-50 border border-red-200 rounded-md p-3">
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
          Email
        </label>

        <TextInput
          id="email"
          type="email"
          class="w-full"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
          placeholder="Digite seu email"
        />

        <InputError class="mt-2" :message="errors.email" />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
          Senha
        </label>

        <TextInput
          id="password"
          type="password"
          class="w-full"
          v-model="form.password"
          required
          autocomplete="current-password"
          placeholder="Digite sua senha"
        />

        <InputError class="mt-2" :message="errors.password" />
      </div>

      <div class="pt-4">
        <button
          type="submit"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 ease-in-out"
          :disabled="processing"
        >
          <svg
            v-if="processing"
            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
          >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ processing ? 'Entrando...' : 'Entrar' }}
        </button>
      </div>
    </form>
  </GuestLayout>
</template>
