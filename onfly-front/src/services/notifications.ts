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
  async getNotifications(): Promise<Notification[]> {
    const response = await api.get('/notifications')
    return response.data
  },

  async getUnreadCount(): Promise<number> {
    const response = await api.get('/notifications/unread-count')
    return response.data.count
  },

  async markAsRead(id: string): Promise<void> {
    await api.patch(`/notifications/${id}/read`)
  },

  async markAllAsRead(): Promise<void> {
    await api.patch('/notifications/mark-all-read')
  },

  async deleteNotification(id: string): Promise<void> {
    await api.delete(`/notifications/${id}`)
  },

  async deleteAllNotifications(): Promise<void> {
    await api.delete('/notifications')
  }
}
