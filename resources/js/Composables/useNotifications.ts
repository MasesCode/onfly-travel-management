import { ref } from 'vue';
import { ErrorHandler, type ValidationError } from '@/Services/ErrorHandler';

export interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message?: string;
  validationErrors?: ValidationError[];
  duration?: number;
}

const notifications = ref<Notification[]>([]);
let notificationIdCounter = 0;

export function useNotifications() {
  const addNotification = (notification: Omit<Notification, 'id'>) => {
    const id = (++notificationIdCounter).toString();
    const newNotification: Notification = {
      id,
      duration: 3000,
      ...notification
    };

    notifications.value.push(newNotification);

    if (newNotification.duration && newNotification.duration > 0) {
      setTimeout(() => {
        removeNotification(id);
      }, newNotification.duration);
    }

    return id;
  };

  const removeNotification = (id: string) => {
    const index = notifications.value.findIndex(n => n.id === id);
    if (index > -1) {
      notifications.value.splice(index, 1);
    }
  };

  const clearAll = () => {
    notifications.value = [];
  };

  const showSuccess = (title: string, message?: string, duration?: number) => {
    return addNotification({
      type: 'success',
      title,
      message,
      duration
    });
  };

  const showError = (error: any, customTitle?: string) => {
    const { message, validationErrors } = ErrorHandler.handleError(error);

    return addNotification({
      type: 'error',
      title: customTitle || 'Erro',
      message,
      validationErrors,
      duration: 3000
    });
  };

  const showWarning = (title: string, message?: string, duration?: number) => {
    return addNotification({
      type: 'warning',
      title,
      message,
      duration
    });
  };

  const showInfo = (title: string, message?: string, duration?: number) => {
    return addNotification({
      type: 'info',
      title,
      message,
      duration
    });
  };

  return {
    notifications,
    addNotification,
    removeNotification,
    clearAll,
    showSuccess,
    showError,
    showWarning,
    showInfo
  };
}
