import api from './api'

export interface Notification {
  id: string
  type: 'approved' | 'cancelled'
  title: string
  message: string
  read: boolean
  deleted: boolean
  created_at: string
  order_id?: number
  destination?: string
}

export const notificationService = {
  // Buscar todas as notificações
  async getNotifications(): Promise<Notification[]> {
    const response = await api.get('/notifications')
    return response.data
  },

  // Buscar contagem de não lidas
  async getUnreadCount(): Promise<number> {
    const response = await api.get('/notifications/unread-count')
    return response.data.count
  },

  // Marcar como lida
  async markAsRead(id: string): Promise<void> {
    await api.patch(`/notifications/${id}/read`)
  },

  // Marcar todas como lidas
  async markAllAsRead(): Promise<void> {
    await api.patch('/notifications/mark-all-read')
  },

  // Deletar notificação
  async deleteNotification(id: string): Promise<void> {
    await api.delete(`/notifications/${id}`)
  },

  // Deletar todas as notificações
  async deleteAllNotifications(): Promise<void> {
    await api.delete('/notifications')
  }
}
