<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotifications } from '@/composables/useNotifications'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'
import TextInput from '@/components/TextInput.vue'
import InputError from '@/components/InputError.vue'

const authStore = useAuthStore()
const { showSuccess, showError } = useNotifications()

const isUpdatingProfile = ref(false)
const isUpdatingPassword = ref(false)

const profileForm = reactive({
  name: authStore.user?.name || '',
  email: authStore.user?.email || ''
})

const profileErrors = ref<Record<string, string[]>>({})

const passwordForm = reactive({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const passwordErrors = ref<Record<string, string[]>>({})

async function updateProfile() {
  if (isUpdatingProfile.value) return

  isUpdatingProfile.value = true
  profileErrors.value = {}

  try {
    const result = await authStore.updateProfile({
      name: profileForm.name,
      email: profileForm.email
    })

    if (result.success) {
      showSuccess(result.message || 'Perfil atualizado com sucesso!')
    } else {
      showError(result.message)
    }
  } catch (error: unknown) {
    const axiosError = error as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } }
    if (axiosError.response?.status === 422) {
      profileErrors.value = axiosError.response.data?.errors || {}
    } else {
      showError('Erro ao atualizar perfil')
    }
  } finally {
    isUpdatingProfile.value = false
  }
}

async function updatePassword() {
  if (isUpdatingPassword.value) return

  isUpdatingPassword.value = true
  passwordErrors.value = {}

  try {
    const result = await authStore.updatePassword({
      current_password: passwordForm.current_password,
      password: passwordForm.password,
      password_confirmation: passwordForm.password_confirmation
    })

    if (result.success) {
      showSuccess(result.message || 'Senha atualizada com sucesso!')
      passwordForm.current_password = ''
      passwordForm.password = ''
      passwordForm.password_confirmation = ''
    } else {
      showError(result.message)
    }
  } catch (error: unknown) {
    const axiosError = error as { response?: { status?: number; data?: { errors?: Record<string, string[]> } } }
    if (axiosError.response?.status === 422) {
      passwordErrors.value = axiosError.response.data?.errors || {}
    } else {
      showError('Erro ao atualizar senha')
    }
  } finally {
    isUpdatingPassword.value = false
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Meu Perfil
      </h2>
    </template>

    <div class="py-12">
      <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">

        <!-- Informações do Perfil -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-medium">Informações do Perfil</h3>
              <div v-if="authStore.user?.is_admin" class="px-3 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                Administrador
              </div>
            </div>

            <form @submit.prevent="updateProfile" class="space-y-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Nome -->
                <div>
                  <label for="name" class="block mb-2 text-sm font-medium text-gray-700">
                    Nome
                  </label>
                  <TextInput
                    id="name"
                    v-model="profileForm.name"
                    type="text"
                    class="w-full"
                    required
                    autofocus
                  />
                  <InputError v-if="profileErrors.name" :message="profileErrors.name[0]" class="mt-2" />
                </div>

                <!-- Email -->
                <div>
                  <label for="email" class="block mb-2 text-sm font-medium text-gray-700">
                    Email
                  </label>
                  <TextInput
                    id="email"
                    v-model="profileForm.email"
                    type="email"
                    class="w-full"
                    required
                  />
                  <InputError v-if="profileErrors.email" :message="profileErrors.email[0]" class="mt-2" />
                </div>
              </div>

              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="isUpdatingProfile"
                  class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                >
                  <svg
                    v-if="isUpdatingProfile"
                    class="w-4 h-4 mr-3 -ml-1 text-white animate-spin"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isUpdatingProfile ? 'Salvando...' : 'Salvar Alterações' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Alterar Senha -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h3 class="mb-6 text-lg font-medium">Alterar Senha</h3>

            <form @submit.prevent="updatePassword" class="space-y-6">
              <div class="grid grid-cols-1 gap-6">
                <!-- Senha Atual -->
                <div>
                  <label for="current_password" class="block mb-2 text-sm font-medium text-gray-700">
                    Senha Atual
                  </label>
                  <TextInput
                    id="current_password"
                    v-model="passwordForm.current_password"
                    type="password"
                    class="w-full"
                    required
                  />
                  <InputError v-if="passwordErrors.current_password" :message="passwordErrors.current_password[0]" class="mt-2" />
                </div>

                <!-- Nova Senha -->
                <div>
                  <label for="password" class="block mb-2 text-sm font-medium text-gray-700">
                    Nova Senha
                  </label>
                  <TextInput
                    id="password"
                    v-model="passwordForm.password"
                    type="password"
                    class="w-full"
                    required
                  />
                  <InputError v-if="passwordErrors.password" :message="passwordErrors.password[0]" class="mt-2" />
                </div>

                <!-- Confirmar Nova Senha -->
                <div>
                  <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-700">
                    Confirmar Nova Senha
                  </label>
                  <TextInput
                    id="password_confirmation"
                    v-model="passwordForm.password_confirmation"
                    type="password"
                    class="w-full"
                    required
                  />
                  <InputError v-if="passwordErrors.password_confirmation" :message="passwordErrors.password_confirmation[0]" class="mt-2" />
                </div>
              </div>

              <div class="flex justify-end">
                <button
                  type="submit"
                  :disabled="isUpdatingPassword"
                  class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"
                >
                  <svg
                    v-if="isUpdatingPassword"
                    class="w-4 h-4 mr-3 -ml-1 text-white animate-spin"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                  >
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ isUpdatingPassword ? 'Alterando...' : 'Alterar Senha' }}
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Informações da Conta -->
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h3 class="mb-4 text-lg font-medium">Informações da Conta</h3>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <dt class="text-sm font-medium text-gray-500">Conta criada em</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ authStore.user?.created_at ? new Date(authStore.user.created_at).toLocaleDateString('pt-BR') : 'N/A' }}
                </dd>
              </div>
              <div>
                <dt class="text-sm font-medium text-gray-500">Última atualização</dt>
                <dd class="mt-1 text-sm text-gray-900">
                  {{ authStore.user?.updated_at ? new Date(authStore.user.updated_at).toLocaleDateString('pt-BR') : 'N/A' }}
                </dd>
              </div>
            </dl>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
