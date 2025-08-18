<template>
  <Teleport to="body">
    <div 
      v-if="notifications.length > 0"
      class="fixed top-4 right-4 z-50 space-y-2"
    >
      <TransitionGroup
        name="notification"
        tag="div"
        class="space-y-2"
      >
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'flex items-center p-4 rounded-lg shadow-lg max-w-sm',
            {
              'bg-green-50 border border-green-200': notification.type === 'success',
              'bg-gray-50 border border-gray-200': notification.type === 'error',
              'bg-primary-50 border border-primary-200': notification.type === 'info',
              'bg-gray-50 border border-gray-200': notification.type === 'warning'
            }
          ]"
        >
          <div class="flex-shrink-0 mr-3">
            <CheckCircleIcon 
              v-if="notification.type === 'success'"
              class="w-5 h-5 text-green-500"
            />
            <XCircleIcon 
              v-else-if="notification.type === 'error'"
              class="w-5 h-5 text-gray-500"
            />
            <InfoIcon 
              v-else-if="notification.type === 'info'"
              class="w-5 h-5 text-primary-500"
            />
            <AlertTriangleIcon 
              v-else-if="notification.type === 'warning'"
              class="w-5 h-5 text-gray-500"
            />
          </div>
          
          <div class="flex-1">
            <h4 
              v-if="notification.title"
              :class="[
                'font-medium text-sm',
                {
                  'text-green-800': notification.type === 'success',
                  'text-gray-900': notification.type === 'error',
                  'text-primary-800': notification.type === 'info',
                  'text-gray-800': notification.type === 'warning'
                }
              ]"
            >
              {{ notification.title }}
            </h4>
            <p 
              :class="[
                'text-sm',
                {
                  'text-green-700': notification.type === 'success',
                  'text-gray-800': notification.type === 'error',
                  'text-primary-700': notification.type === 'info',
                  'text-gray-700': notification.type === 'warning'
                }
              ]"
            >
              {{ notification.message }}
            </p>
          </div>
          
          <button
            @click="removeNotification(notification.id)"
            :class="[
              'flex-shrink-0 ml-3 p-1 rounded-full hover:bg-opacity-20 transition-colors',
              {
                'hover:bg-green-500': notification.type === 'success',
                'hover:bg-gray-500': notification.type === 'error',
                'hover:bg-primary-500': notification.type === 'info',
                'hover:bg-gray-500': notification.type === 'warning'
              }
            ]"
          >
            <XIcon class="w-4 h-4 text-gray-500" />
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'
import { useNotificationStore } from '@/stores/notification'
// Icônes SVG inline pour éviter les problèmes d'import
const CheckCircleIcon = {
  template: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
  </svg>`
}

const XCircleIcon = {
  template: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
  </svg>`
}

const InfoIcon = {
  template: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
  </svg>`
}

const AlertTriangleIcon = {
  template: `<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
  </svg>`
}

const XIcon = {
  template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
  </svg>`
}

const notificationStore = useNotificationStore()

const notifications = computed(() => notificationStore.notifications)

const removeNotification = (id) => {
  notificationStore.removeNotification(id)
}
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

