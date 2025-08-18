<template>
  <div class="notification-dropdown absolute right-0 top-full mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
      <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
      <div class="flex items-center space-x-2">
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-sm text-primary-600 hover:text-primary-700"
        >
          Tout marquer comme lu
        </button>
        <button
          @click="$emit('close')"
          class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100"
        >
          <XIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Notifications List -->
    <div class="max-h-96 overflow-y-auto">
      <div v-if="loading" class="p-4">
        <div class="space-y-3">
          <NotificationSkeleton v-for="i in 5" :key="i" />
        </div>
      </div>
      
      <div v-else-if="notifications.length > 0" class="divide-y divide-gray-100">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          :class="[
            'p-4 hover:bg-gray-50 cursor-pointer transition-colors',
            { 'bg-primary-50': !notification.read_at }
          ]"
          @click="handleNotificationClick(notification)"
        >
          <div class="flex items-start space-x-3">
            <!-- Icon -->
            <div class="flex-shrink-0">
              <div
                :class="[
                  'w-8 h-8 rounded-full flex items-center justify-center',
                  getNotificationIconClass(notification.type)
                ]"
              >
                <component
                  :is="getNotificationIcon(notification.type)"
                  class="w-4 h-4"
                />
              </div>
            </div>
            
            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900">
                {{ notification.title }}
              </p>
              <p class="text-sm text-gray-600 mt-1">
                {{ notification.message }}
              </p>
              <p class="text-xs text-gray-400 mt-2">
                {{ formatDate(notification.created_at) }}
              </p>
            </div>
            
            <!-- Unread indicator -->
            <div v-if="!notification.read_at" class="flex-shrink-0">
              <div class="w-2 h-2 bg-primary-500 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="p-8 text-center">
        <BellIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
        <h3 class="text-sm font-medium text-gray-900 mb-1">Aucune notification</h3>
        <p class="text-sm text-gray-600">Vous êtes à jour !</p>
      </div>
    </div>

    <!-- Footer -->
    <div v-if="notifications.length > 0" class="border-t border-gray-100 p-3">
      <RouterLink
        to="/notifications"
        class="block text-center text-sm text-primary-600 hover:text-primary-700"
        @click="$emit('close')"
      >
        Voir toutes les notifications
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useDashboardStore } from '@/stores/dashboard'
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'
import {
  XIcon,
  BellIcon,
  HeartIcon,
  MessageCircleIcon,
  ShoppingBagIcon,
  UserPlusIcon,
  StarIcon,
  PackageIcon
} from 'lucide-vue-next'

// Components
import NotificationSkeleton from '@/components/skeletons/NotificationSkeleton.vue'

const dashboardStore = useDashboardStore()
const router = useRouter()

defineEmits(['close'])

// State
const loading = ref(true)
const notifications = ref([])

// Computed
const unreadCount = computed(() => 
  notifications.value.filter(n => !n.read_at).length
)

// Methods
const fetchNotifications = async () => {
  loading.value = true
  try {
            const response = await window.axios.get('/notifications', {
      params: { limit: 10 }
    })
    notifications.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching notifications:', error)
  } finally {
    loading.value = false
  }
}

const markAllAsRead = async () => {
  try {
    await dashboardStore.markNotificationsAsRead()
    // Mark all local notifications as read
    notifications.value.forEach(notification => {
      notification.read_at = new Date().toISOString()
    })
  } catch (error) {
    console.error('Error marking notifications as read:', error)
  }
}

const handleNotificationClick = async (notification) => {
  // Mark as read if not already
  if (!notification.read_at) {
    try {
              await window.axios.put(`/notifications/${notification.id}/read`)
      notification.read_at = new Date().toISOString()
    } catch (error) {
      console.error('Error marking notification as read:', error)
    }
  }
  
  // Navigate based on notification type
  const url = getNotificationUrl(notification)
  if (url) {
    router.push(url)
  }
  
  // Close dropdown
  $emit('close')
}

const getNotificationUrl = (notification) => {
  switch (notification.type) {
    case 'like':
    case 'comment':
      return `/products/${notification.data?.product_id}`
    case 'order':
      return `/orders/${notification.data?.order_id}`
    case 'message':
      return `/messages/${notification.data?.conversation_id}`
    case 'follow':
      return `/users/${notification.data?.user_id}`
    default:
      return null
  }
}

const getNotificationIcon = (type) => {
  const iconMap = {
    like: HeartIcon,
    comment: MessageCircleIcon,
    order: ShoppingBagIcon,
    follow: UserPlusIcon,
    review: StarIcon,
    message: MessageCircleIcon,
    product: PackageIcon
  }
  return iconMap[type] || BellIcon
}

const getNotificationIconClass = (type) => {
  const classMap = {
    like: 'bg-gray-100 text-gray-700',
    comment: 'bg-primary-100 text-primary-600',
    order: 'bg-green-100 text-green-600',
    follow: 'bg-primary-100 text-primary-600',
    review: 'bg-gray-100 text-gray-600',
    message: 'bg-primary-100 text-primary-600',
    product: 'bg-gray-100 text-gray-600'
  }
  return classMap[type] || 'bg-gray-100 text-gray-600'
}

const formatDate = (date) => {
  return formatDistanceToNow(new Date(date), {
    addSuffix: true,
    locale: fr
  })
}

// Lifecycle
onMounted(() => {
  fetchNotifications()
})
</script>

