import { ref } from 'vue';

interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message: string;
  duration?: number;
}

const notifications = ref<Notification[]>([]);

export function useNotifications() {
  const addNotification = (notification: Omit<Notification, 'id'>) => {
    const id = Date.now().toString();
    const newNotification = { ...notification, id };

    notifications.value.push(newNotification);

    // Auto remove after duration (default 5 seconds)
    const duration = notification.duration || 5000;
    setTimeout(() => {
      removeNotification(id);
    }, duration);

    return id;
  };

  const removeNotification = (id: string) => {
    const index = notifications.value.findIndex(n => n.id === id);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  };

  const showSuccess = (message: string, title = 'Sucesso') => {
    return addNotification({
      type: 'success',
      title,
      message
    });
  };

  const showError = (error: unknown, title = 'Erro') => {
    let message = 'Ocorreu um erro inesperado';

    if (error && typeof error === 'object' && 'response' in error) {
      const axiosError = error as { response?: { data?: { message?: string } } };
      if (axiosError.response?.data?.message) {
        message = axiosError.response.data.message;
      }
    } else if (error && typeof error === 'object' && 'message' in error) {
      const errorWithMessage = error as { message: string };
      message = errorWithMessage.message;
    } else if (typeof error === 'string') {
      message = error;
    }

    return addNotification({
      type: 'error',
      title,
      message
    });
  };

  const showWarning = (message: string, title = 'Atenção') => {
    return addNotification({
      type: 'warning',
      title,
      message
    });
  };

  const showInfo = (message: string, title = 'Informação') => {
    return addNotification({
      type: 'info',
      title,
      message
    });
  };

  const clearAll = () => {
    notifications.value = [];
  };

  return {
    notifications,
    addNotification,
    removeNotification,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    clearAll
  };
}
