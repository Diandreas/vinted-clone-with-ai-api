import { onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { onForegroundMessage } from '@/services/firebaseService'
import { useNotificationStore } from '@/stores/notification'
import { isNative } from '@/utils/platform'

/**
 * Composable branché dans App.vue.
 * Gère les notifications FCM :
 *  - Foreground  : toast in-app (web + Android)
 *  - Background  : clic sur la bannière Android → navigation vers la bonne page
 *  - Background  : clic sur le push web (via sw.js) → navigation
 */
export function useFcmNotifications() {
  const router = useRouter()
  const notificationStore = useNotificationStore()

  let cleanupForeground = null

  const getUrlFromData = (data = {}) => {
    if (data.type === 'product_liked' || data.type === 'product_favorited' || data.type === 'product_commented' || data.type === 'product_published') {
      return `/products/${data.product_id}`
    }
    if (data.type === 'new_message') {
      return `/conversations/${data.conversation_id}`
    }
    if (data.type === 'new_follower') {
      return `/profile/${data.follower_id}`
    }
    return null
  }

  // Notification reçue quand l'app est OUVERTE → toast cliquable
  const handleForegroundMessage = ({ notification, data }) => {
    const url = getUrlFromData(data)
    notificationStore.addNotification({
      type: 'info',
      title: notification?.title || data?.title || 'RIKEAA',
      message: notification?.body  || data?.body  || '',
      duration: 6000,
      action: url ? () => router.push(url) : null,
    })
  }

  // Clic sur une notification Android en arrière-plan → navigation
  const setupAndroidBackgroundClick = async () => {
    const { PushNotifications } = await import('@capacitor/push-notifications')

    await PushNotifications.addListener('pushNotificationActionPerformed', (event) => {
      const data = event.notification?.data || {}
      const url = getUrlFromData(data)
      if (url) router.push(url)
    })
  }

  // Clic sur une notification web (envoyé par sw.js) → navigation
  const handleSwClick = (event) => {
    if (event.data?.type === 'NOTIFICATION_CLICK' && event.data.url) {
      router.push(event.data.url)
    }
  }

  onMounted(async () => {
    // Foreground messages (web + Android)
    cleanupForeground = await onForegroundMessage(handleForegroundMessage)

    if (isNative()) {
      // Android : clic sur notification en arrière-plan
      setupAndroidBackgroundClick()
    } else {
      // Web : clic sur notification SW
      navigator.serviceWorker?.addEventListener('message', handleSwClick)
    }
  })

  onUnmounted(() => {
    if (typeof cleanupForeground === 'function') cleanupForeground()
    if (!isNative()) {
      navigator.serviceWorker?.removeEventListener('message', handleSwClick)
    }
  })
}
