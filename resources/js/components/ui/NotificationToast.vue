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
              'bg-red-50 border border-red-200': notification.type === 'error',
              'bg-blue-50 border border-blue-200': notification.type === 'info',
              'bg-yellow-50 border border-yellow-200': notification.type === 'warning'
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
              class="w-5 h-5 text-red-500"
            />
            <InfoIcon 
              v-else-if="notification.type === 'info'"
              class="w-5 h-5 text-blue-500"
            />
            <AlertTriangleIcon 
              v-else-if="notification.type === 'warning'"
              class="w-5 h-5 text-yellow-500"
            />
          </div>
          
          <div class="flex-1">
            <h4 
              v-if="notification.title"
              :class="[
                'font-medium text-sm',
                {
                  'text-green-800': notification.type === 'success',
                  'text-red-800': notification.type === 'error',
                  'text-blue-800': notification.type === 'info',
                  'text-yellow-800': notification.type === 'warning'
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
                  'text-red-700': notification.type === 'error',
                  'text-blue-700': notification.type === 'info',
                  'text-yellow-700': notification.type === 'warning'
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
                'hover:bg-red-500': notification.type === 'error',
                'hover:bg-blue-500': notification.type === 'info',
                'hover:bg-yellow-500': notification.type === 'warning'
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
import { 
  CheckCircleIcon, 
  XCircleIcon, 
  InfoIcon, 
  AlertTriangleIcon,
  XIcon 
} from 'lucide-vue-next'

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

