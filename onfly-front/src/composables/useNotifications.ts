import { ref } from 'vue';
import { ErrorHandler, type ValidationError } from '@/services/ErrorHandler';

export interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message?: string;
  validationErrors?: ValidationError[];
  duration?: number;
}

export type { ValidationError };

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
    // Se é uma string, usar diretamente
    if (typeof error === 'string') {
      return addNotification({
        type: 'error',
        title,
        message: error,
        duration: error.length > 50 ? 8000 : 6000
      });
    }

    // Se é um objeto de erro, usar o ErrorHandler
    const { message, validationErrors } = ErrorHandler.handleError(error);

    // Duração maior para mensagens de erro para dar tempo de ler
    const duration = message.length > 50 || validationErrors.length > 0 ? 8000 : 6000;

    return addNotification({
      type: 'error',
      title,
      message,
      validationErrors,
      duration
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
