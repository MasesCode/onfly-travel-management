<template>
  <div class="fixed top-4 right-4 z-50 space-y-4 pointer-events-none max-w-sm sm:max-w-md lg:max-w-lg w-full">
    <TransitionGroup
      name="notification"
      tag="div"
      class="space-y-4"
    >
      <div
        v-for="notification in notifications"
        :key="notification.id"
        :class="[
          'w-full bg-white shadow-xl rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300',
          getNotificationClass(notification.type)
        ]"
      >
        <div class="p-6">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <div :class="getIconClass(notification.type)">
                <svg v-if="notification.type === 'success'" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <svg v-else-if="notification.type === 'error'" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <svg v-else-if="notification.type === 'warning'" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <svg v-else class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4 w-0 flex-1">
              <p :class="getTitleClass(notification.type)" class="text-lg font-semibold leading-6">
                {{ notification.title }}
              </p>
              <p v-if="notification.message" class="mt-2 text-base text-gray-600 leading-relaxed">
                {{ notification.message }}
              </p>

              <!-- Lista de erros de validação com melhor formatação -->
              <div v-if="notification.validationErrors && notification.validationErrors.length > 0" class="mt-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                  <h4 class="text-sm font-semibold text-red-800 mb-2">Erros encontrados:</h4>
                  <ul class="space-y-2">
                    <li v-for="error in notification.validationErrors" :key="`${notification.id}-${error.field}`" class="flex items-start text-sm">
                      <span class="inline-block w-2 h-2 bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                      <div>
                        <span class="font-semibold text-red-800">{{ error.field }}:</span>
                        <span class="ml-1 text-red-700">{{ error.message }}</span>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
              <button
                @click="removeNotification(notification.id)"
                class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 p-1"
                type="button"
              >
                <span class="sr-only">Fechar</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { useNotifications } from '@/Composables/useNotifications';

const { notifications, removeNotification } = useNotifications();

const getIconClass = (type: string) => {
  const baseClasses = 'h-8 w-8';
  switch (type) {
    case 'success': return `${baseClasses} text-green-400`;
    case 'warning': return `${baseClasses} text-yellow-400`;
    case 'error': return `${baseClasses} text-red-400`;
    default: return `${baseClasses} text-blue-400`;
  }
};

const getTitleClass = (type: string) => {
  switch (type) {
    case 'success': return 'text-green-800';
    case 'warning': return 'text-yellow-800';
    case 'error': return 'text-red-800';
    default: return 'text-blue-800';
  }
};

const getNotificationClass = (type: string) => {
  switch (type) {
    case 'success': return 'border-l-4 border-green-400';
    case 'warning': return 'border-l-4 border-yellow-400';
    case 'error': return 'border-l-4 border-red-400';
    default: return 'border-l-4 border-blue-400';
  }
};
</script>

<style scoped>
.notification-enter-active,
.notification-leave-active {
  transition: all 0.3s ease;
}

.notification-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.notification-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

.notification-move {
  transition: transform 0.3s ease;
}
</style>
