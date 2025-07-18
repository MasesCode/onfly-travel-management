<template>
  <div
    v-if="notifications.length > 0"
    class="fixed z-50 space-y-4 top-4 right-4 left-4 sm:left-auto"
    style="max-width: 700px; min-width: 500px;"
  >
    <div
      v-for="notification in notifications"
      :key="notification.id"
      class="w-full overflow-hidden bg-white border border-gray-200 rounded-lg shadow-xl"
      style="min-height: 120px;"
    >
      <div class="p-10">
        <div class="flex items-start">
          <div class="flex-shrink-0 mr-5">
            <!-- Success Icon -->
            <svg
              v-if="notification.type === 'success'"
              class="w-8 h-8 text-green-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>

            <!-- Error Icon -->
            <svg
              v-else-if="notification.type === 'error'"
              class="w-8 h-8 text-red-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>

            <!-- Warning Icon -->
            <svg
              v-else-if="notification.type === 'warning'"
              class="w-8 h-8 text-yellow-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>

            <!-- Info Icon -->
            <svg
              v-else
              class="w-8 h-8 text-blue-400"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>

          <div class="flex-1 w-0">
            <p class="mb-2 text-lg font-semibold leading-7 text-gray-900">
              {{ notification.title }}
            </p>
            <p v-if="notification.message" class="mb-2 text-base leading-relaxed text-gray-600">
              {{ notification.message }}
            </p>

            <!-- Lista de erros de validação com melhor formatação -->
            <div v-if="notification.validationErrors && notification.validationErrors.length > 0" class="mt-6">
              <div class="p-6 border border-red-200 rounded-lg bg-red-50">
                <h4 class="mb-4 text-lg font-semibold text-red-800">Erros encontrados:</h4>
                <ul class="space-y-4">
                  <li v-for="error in notification.validationErrors" :key="`${notification.id}-${error.field}`" class="flex items-start text-base">
                    <span class="inline-block w-2.5 h-2.5 bg-red-400 rounded-full mt-2.5 mr-5 flex-shrink-0"></span>
                    <div class="flex-1">
                      <span class="font-semibold text-red-800">{{ error.field }}:</span>
                      <span class="ml-2 text-red-700">{{ error.message }}</span>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>

          <div class="flex flex-shrink-0 ml-4">
            <button
              @click="removeNotification(notification.id)"
              class="inline-flex p-2 text-gray-400 transition-colors duration-200 bg-white rounded-md hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <span class="sr-only">Fechar</span>
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useNotifications } from '@/composables/useNotifications';

const { notifications, removeNotification } = useNotifications();
</script>
