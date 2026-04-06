<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Sticky header -->
    <div class="sticky top-14 sm:top-16 z-10 bg-gray-50 pt-6 pb-3 border-b border-gray-200">
      <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <!-- Title row -->
        <div class="flex items-center justify-between mb-3">
          <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-sm text-primary-600 hover:text-primary-700 font-medium"
          >
            Tout marquer comme lu
          </button>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-1 bg-white border border-gray-200 rounded-xl p-1">
        <button
          @click="activeTab = 'all'"
          :class="[
            'flex-1 py-2 text-sm font-medium rounded-lg transition-colors',
            activeTab === 'all'
              ? 'bg-primary-600 text-white'
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          Toutes
        </button>
        <button
          @click="activeTab = 'unread'"
          :class="[
            'flex-1 py-2 text-sm font-medium rounded-lg transition-colors',
            activeTab === 'unread'
              ? 'bg-primary-600 text-white'
              : 'text-gray-600 hover:text-gray-900'
          ]"
        >
          Non lues
          <span v-if="unreadCount > 0" class="ml-1 bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">
            {{ unreadCount }}
          </span>
        </button>
        </div>
      </div>
    </div>

    <!-- Scrollable content -->
    <div class="max-w-2xl mx-auto px-4 sm:px-6 py-4">

      <!-- Loading -->
      <div v-if="loading" class="space-y-3">
        <div v-for="i in 6" :key="i" class="bg-white rounded-xl border border-gray-200 p-4 animate-pulse">
          <div class="flex items-start space-x-3">
            <div class="w-10 h-10 bg-gray-200 rounded-full flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-3 bg-gray-200 rounded w-full"></div>
              <div class="h-3 bg-gray-200 rounded w-1/4"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- List -->
      <div v-else-if="filteredNotifications.length > 0" class="space-y-2">
        <div
          v-for="notification in filteredNotifications"
          :key="notification.id"
          @click="handleClick(notification)"
          :class="[
            'bg-white rounded-xl border border-gray-200 p-4 cursor-pointer hover:shadow-sm transition-all',
            !notification.read_at ? 'border-l-4 border-l-primary-500' : ''
          ]"
        >
          <div class="flex items-start space-x-3">
            <!-- Icon -->
            <div
              :class="[
                'w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0',
                getIconClass(notification.type)
              ]"
            >
              <component :is="getIcon(notification.type)" class="w-5 h-5" />
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900">{{ notification.title }}</p>
              <p class="text-sm text-gray-600 mt-0.5">{{ notification.message }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ formatDate(notification.created_at) }}</p>
            </div>

            <!-- Unread dot + delete -->
            <div class="flex items-center space-x-2 flex-shrink-0">
              <div v-if="!notification.read_at" class="w-2 h-2 bg-primary-500 rounded-full"></div>
              <button
                @click.stop="deleteNotification(notification)"
                class="p-1 text-gray-300 hover:text-red-500 transition-colors rounded"
              >
                <XIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <BellIcon class="w-14 h-14 text-gray-300 mx-auto mb-4" />
        <h3 class="text-base font-medium text-gray-900 mb-1">
          {{ activeTab === 'unread' ? 'Aucune notification non lue' : 'Aucune notification' }}
        </h3>
        <p class="text-sm text-gray-500">Vous êtes à jour !</p>
      </div>

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
  BellIcon,
  HeartIcon,
  StarIcon,
  UserPlusIcon,
  PackageIcon,
  MessageCircleIcon,
  XIcon
} from 'lucide-vue-next'

const router = useRouter()
const dashboardStore = useDashboardStore()

const loading = ref(true)
const notifications = ref([])
const activeTab = ref('all')

const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length)

const filteredNotifications = computed(() =>
  activeTab.value === 'unread'
    ? notifications.value.filter(n => !n.read_at)
    : notifications.value
)

const normalizeNotification = (raw) => {
  const data = raw?.data || {}
  const type = data.type || raw?.type || 'notification'

  const titleByType = {
    product_liked: 'Nouveau like',
    product_favorited: 'Nouveau favori',
    product_commented: 'Nouveau commentaire',
    new_message: 'Nouveau message',
    new_follower: 'Nouveau follower',
    order_status_changed: 'Commande mise à jour',
    kyc_status_updated: data.status === 'verified' ? 'KYC approuvé' : 'KYC rejeté',
    kyc_submission_received: 'Nouvelle demande KYC',
  }

  return {
    id: raw.id,
    type,
    title: data.title || titleByType[type] || 'Notification',
    message: data.message || raw.message || '',
    data,
    read_at: raw.read_at,
    created_at: raw.created_at,
  }
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const response = await window.axios.get('/notifications', { params: { limit: 50 } })
    const payload = response.data?.data || {}
    const items = payload.data || payload || []
    notifications.value = (Array.isArray(items) ? items : []).map(normalizeNotification)
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const markAllAsRead = async () => {
  try {
    await dashboardStore.markNotificationsAsRead()
    notifications.value.forEach(n => { n.read_at = new Date().toISOString() })
    dashboardStore.unreadNotifications = 0
  } catch (e) {
    console.error(e)
  }
}

const handleClick = async (notification) => {
  if (!notification.read_at) {
    try {
      await window.axios.put(`/notifications/${notification.id}/read`)
      notification.read_at = new Date().toISOString()
      dashboardStore.unreadNotifications = Math.max(0, dashboardStore.unreadNotifications - 1)
    } catch (e) { /* silencieux */ }
  }

  const url = getUrl(notification)
  if (url) router.push(url)
}

const deleteNotification = async (notification) => {
  try {
    await window.axios.delete(`/notifications/${notification.id}`)
    notifications.value = notifications.value.filter(n => n.id !== notification.id)
    if (!notification.read_at) {
      dashboardStore.unreadNotifications = Math.max(0, dashboardStore.unreadNotifications - 1)
    }
  } catch (e) { console.error(e) }
}

const getUrl = (notification) => {
  switch (notification.type) {
    case 'product_liked':
    case 'product_favorited':
    case 'product_commented':
      return `/products/${notification.data?.product_id}`
    case 'new_message':
      return `/conversations/${notification.data?.conversation_id}`
    case 'new_follower':
      return `/profile/${notification.data?.follower_id || notification.data?.user_id}`
    case 'order_status_changed':
      return `/orders/${notification.data?.order_id}`
    case 'kyc_status_updated':
      return '/profile/verification'
    case 'kyc_submission_received':
      return '/admin/users'
    default:
      return null
  }
}

const getIcon = (type) => {
  const map = {
    product_liked: HeartIcon,
    product_favorited: StarIcon,
    product_commented: MessageCircleIcon,
    new_message: MessageCircleIcon,
    new_follower: UserPlusIcon,
    order_status_changed: PackageIcon,
    kyc_status_updated: BellIcon,
    kyc_submission_received: BellIcon,
  }
  return map[type] || BellIcon
}

const getIconClass = (type) => {
  const map = {
    product_liked: 'bg-red-100 text-red-600',
    product_favorited: 'bg-yellow-100 text-yellow-600',
    product_commented: 'bg-blue-100 text-blue-600',
    new_message: 'bg-green-100 text-green-600',
    new_follower: 'bg-purple-100 text-purple-600',
    order_status_changed: 'bg-green-100 text-green-600',
    kyc_status_updated: 'bg-blue-100 text-blue-600',
    kyc_submission_received: 'bg-amber-100 text-amber-700',
  }
  return map[type] || 'bg-gray-100 text-gray-600'
}

const formatDate = (date) => {
  try {
    return formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr })
  } catch {
    return ''
  }
}

onMounted(fetchNotifications)
</script>
