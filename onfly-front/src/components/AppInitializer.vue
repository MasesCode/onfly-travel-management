<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import NotificationToast from './NotificationToast.vue'

const authStore = useAuthStore()
const router = useRouter()
const isLoading = ref(true)

onMounted(async () => {
  if (!authStore.isInitialized) {
    await authStore.initialize()
  }

  isLoading.value = false

  if (authStore.isAuthenticated && router.currentRoute.value.path === '/') {
    router.push('/dashboard')
  } else if (!authStore.isAuthenticated && router.currentRoute.value.path === '/') {
    router.push('/login')
  }
})
</script>

<template>
  <div v-if="isLoading" class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="text-center">
      <div class="inline-flex items-center px-4 py-2 text-sm font-semibold leading-6 text-white bg-indigo-500 rounded-md shadow">
        <svg class="w-5 h-5 mr-3 -ml-1 text-white animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Carregando aplicação...
      </div>
      <p class="mt-4 text-sm text-gray-600">Verificando autenticação...</p>
    </div>
  </div>
  <div v-else>
    <router-view />
    <!-- Componente de notificações global -->
    <NotificationToast />
  </div>
</template>
