import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import type { User, LoginCredentials, RegisterData } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))
  const isInitialized = ref(false)

  const isAuthenticated = computed(() => !!token.value && !!user.value && isInitialized.value)
  const isAdmin = computed(() => user.value?.is_admin || false)

  async function login(credentials: LoginCredentials) {
    try {
      const response = await api.post('/login', credentials)
      const { user: userData, token: authToken } = response.data

      user.value = userData
      token.value = authToken
      localStorage.setItem('auth_token', authToken)
      isInitialized.value = true

      return { success: true }
    } catch (error: unknown) {
      const axiosError = error as { response?: { data?: { message?: string } } }
      const message = axiosError.response?.data?.message || 'Erro ao fazer login'
      return { success: false, message }
    }
  }

  async function register(data: RegisterData) {
    try {
      const response = await api.post('/register', data)
      const { user: userData, token: authToken } = response.data

      user.value = userData
      token.value = authToken
      localStorage.setItem('auth_token', authToken)

      return { success: true }
    } catch (error: unknown) {
      const axiosError = error as { response?: { data?: { message?: string } } }
      const message = axiosError.response?.data?.message || 'Erro ao criar conta'
      return { success: false, message }
    }
  }

  async function logout() {
    try {
      if (token.value) {
        await api.post('/logout')
      }
    } catch (error) {
      console.error('Erro ao fazer logout:', error)
    } finally {
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      isInitialized.value = true
    }
  }

  async function fetchUser() {
    if (!token.value) {
      isInitialized.value = true
      return
    }

    try {
      const response = await api.get('/user')
      user.value = response.data
      isInitialized.value = true
    } catch (error) {
      console.error('Erro ao buscar usuário:', error)
      // Se falhou ao buscar usuário, limpar token inválido
      user.value = null
      token.value = null
      localStorage.removeItem('auth_token')
      isInitialized.value = true
    }
  }

  async function updateProfile(profileData: { name: string; email: string }) {
    try {
      const response = await api.put('/profile', profileData)
      user.value = response.data.user
      return { success: true, message: response.data.message }
    } catch (error: unknown) {
      const axiosError = error as { response?: { data?: { message?: string } } }
      const message = axiosError.response?.data?.message || 'Erro ao atualizar perfil'
      return { success: false, message }
    }
  }

  async function updatePassword(passwordData: {
    current_password: string
    password: string
    password_confirmation: string
  }) {
    try {
      const response = await api.put('/profile/password', passwordData)
      return { success: true, message: response.data.message }
    } catch (error: unknown) {
      const axiosError = error as { response?: { data?: { message?: string } } }
      const message = axiosError.response?.data?.message || 'Erro ao atualizar senha'
      return { success: false, message }
    }
  }

  // Função para inicializar a autenticação
  async function initialize() {
    if (token.value && !user.value && !isInitialized.value) {
      await fetchUser()
    } else if (!token.value) {
      isInitialized.value = true
    }
  }

  // Inicializar na criação do store
  initialize()

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    isInitialized,
    login,
    register,
    logout,
    fetchUser,
    initialize,
    updateProfile,
    updatePassword
  }
})
