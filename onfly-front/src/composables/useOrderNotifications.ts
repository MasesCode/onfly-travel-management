import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { OrderService } from '@/services/orders'
import { useNotifications } from '@/composables/useNotifications'
import type { Order } from '@/types'

export interface OrderNotification {
  id: string
  orderId: number
  type: 'approved' | 'cancelled'
  title: string
  message: string
  read: boolean
  created_at: string
}

const orderNotifications = ref<OrderNotification[]>([])
const lastCheckedOrders = ref<Map<number, string>>(new Map())
let pollingInterval: number | null = null

export function useOrderNotifications() {
  const authStore = useAuthStore()
  const { showSuccess, showError } = useNotifications()

  // Função para verificar mudanças nos pedidos
  const checkOrderStatusChanges = async () => {
    if (!authStore.isAuthenticated) return

    try {
      const orders = await OrderService.getOrders()
      const userOrders = orders.filter(order => order.user_id === authStore.user?.id)

      for (const order of userOrders) {
        const lastStatus = lastCheckedOrders.value.get(order.id)

        // Se é a primeira vez verificando este pedido, apenas armazena o status
        if (!lastStatus) {
          lastCheckedOrders.value.set(order.id, order.status)
          continue
        }

        // Se o status mudou de 'pending' para 'approved' ou 'cancelled'
        if (lastStatus === 'pending' && (order.status === 'approved' || order.status === 'cancelled')) {
          const notification = createOrderNotification(order, order.status)
          orderNotifications.value.unshift(notification)

          // Também mostra toast
          if (order.status === 'approved') {
            showSuccess(`Seu pedido para ${order.destination} foi aprovado!`, 'Pedido Aprovado')
          } else {
            showError(`Seu pedido para ${order.destination} foi cancelado.`, 'Pedido Cancelado')
          }

          // Atualiza o status armazenado
          lastCheckedOrders.value.set(order.id, order.status)
        }
      }
    } catch (error) {
      console.error('Erro ao verificar status dos pedidos:', error)
    }
  }

  // Cria uma notificação para mudança de status do pedido
  const createOrderNotification = (order: Order, type: 'approved' | 'cancelled'): OrderNotification => {
    const id = `${order.id}-${type}-${Date.now()}`

    let title: string
    let message: string

    if (type === 'approved') {
      title = 'Pedido Aprovado!'
      message = `Seu pedido para ${order.destination} foi aprovado e você pode proceder com a viagem.`
    } else {
      title = 'Pedido Cancelado'
      message = `Seu pedido para ${order.destination} foi cancelado. Entre em contato com o administrador para mais informações.`
    }

    return {
      id,
      orderId: order.id,
      type,
      title,
      message,
      read: false,
      created_at: new Date().toISOString()
    }
  }

  // Marca uma notificação como lida
  const markAsRead = (notificationId: string) => {
    const notification = orderNotifications.value.find(n => n.id === notificationId)
    if (notification) {
      notification.read = true
      saveNotificationsToStorage()
    }
  }

  // Marca todas as notificações como lidas
  const markAllAsRead = () => {
    orderNotifications.value.forEach(notification => {
      notification.read = true
    })
    saveNotificationsToStorage()
  }

  // Remove uma notificação
  const removeNotification = (notificationId: string) => {
    const index = orderNotifications.value.findIndex(n => n.id === notificationId)
    if (index > -1) {
      orderNotifications.value.splice(index, 1)
      saveNotificationsToStorage()
    }
  }

  // Salva notificações no localStorage
  const saveNotificationsToStorage = () => {
    const userId = authStore.user?.id
    if (userId) {
      localStorage.setItem(
        `order_notifications_${userId}`,
        JSON.stringify(orderNotifications.value)
      )
    }
  }

  // Carrega notificações do localStorage
  const loadNotificationsFromStorage = () => {
    const userId = authStore.user?.id
    if (userId) {
      const stored = localStorage.getItem(`order_notifications_${userId}`)
      if (stored) {
        try {
          const notifications = JSON.parse(stored) as OrderNotification[]
          // Manter apenas notificações dos últimos 30 dias
          const thirtyDaysAgo = new Date()
          thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30)

          orderNotifications.value = notifications.filter(
            n => new Date(n.created_at) > thirtyDaysAgo
          )
        } catch (error) {
          console.error('Erro ao carregar notificações do storage:', error)
        }
      }
    }
  }

  // Inicializa o sistema de notificações
  const initializeNotifications = async () => {
    if (!authStore.isAuthenticated) return

    loadNotificationsFromStorage()

    // Carrega o status inicial dos pedidos
    try {
      const orders = await OrderService.getOrders()
      const userOrders = orders.filter(order => order.user_id === authStore.user?.id)

      userOrders.forEach(order => {
        lastCheckedOrders.value.set(order.id, order.status)
      })
    } catch (error) {
      console.error('Erro ao inicializar notificações:', error)
    }

    // Inicia o polling
    startPolling()
  }

  // Inicia o polling para verificar mudanças
  const startPolling = () => {
    if (pollingInterval) {
      clearInterval(pollingInterval)
    }

    // Verifica a cada 30 segundos
    pollingInterval = window.setInterval(checkOrderStatusChanges, 30000)
  }

  // Para o polling
  const stopPolling = () => {
    if (pollingInterval) {
      clearInterval(pollingInterval)
      pollingInterval = null
    }
  }

  // Computed para contar notificações não lidas
  const unreadCount = computed(() =>
    orderNotifications.value.filter(n => !n.read).length
  )

  // Lifecycle hooks
  onMounted(() => {
    if (authStore.isAuthenticated) {
      initializeNotifications()
    }
  })

  onUnmounted(() => {
    stopPolling()
  })

  return {
    orderNotifications,
    unreadCount,
    markAsRead,
    markAllAsRead,
    removeNotification,
    initializeNotifications,
    startPolling,
    stopPolling,
    checkOrderStatusChanges
  }
}
