import { ref, computed } from 'vue'

export interface ApiNotification {
  id: string
  type: 'approved' | 'cancelled' | 'pending' | 'info'
  title: string
  message: string
  read: boolean
  deleted: boolean
  created_at: string
  order_id?: number
  destination?: string
}

const notifications = ref<ApiNotification[]>([])
const loading = ref(false)

export const useApiNotifications = () => {
  const refreshNotifications = async () => {
    try {
      loading.value = true
      const token = localStorage.getItem('auth_token')

      if (!token) {
        console.warn('Token de autenticação não encontrado')
        return
      }

      const response = await fetch('/api/notifications', {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
      })

      if (response.ok) {
        const data = await response.json()
        notifications.value = data.data || []
        console.log('Notificações atualizadas:', notifications.value.length)
      } else {
        console.error('Erro ao buscar notificações:', response.statusText)
      }
    } catch (error) {
      console.error('Erro ao buscar notificações:', error)
    } finally {
      loading.value = false
    }
  }

  const unreadCount = computed(() => {
    return notifications.value.filter(n => !n.read && !n.deleted).length
  })

  const visibleNotifications = computed(() => {
    return notifications.value.filter(n => !n.deleted)
  })

  return {
    notifications,
    visibleNotifications,
    loading,
    refreshNotifications,
    unreadCount
  }
}
