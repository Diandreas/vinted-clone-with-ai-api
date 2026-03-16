import { initializeApp } from 'firebase/app'
import { getMessaging, getToken, onMessage } from 'firebase/messaging'
import { isNative } from '@/utils/platform'
import api from '@/services/api'

// ─── Firebase Web Config ────────────────────────────────────────────────────
const firebaseConfig = {
  apiKey:            import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain:        import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId:         import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket:     import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId:             import.meta.env.VITE_FIREBASE_APP_ID,
}

const VAPID_KEY = import.meta.env.VITE_FIREBASE_VAPID_KEY

let messaging = null

/**
 * Initialise Firebase (Web seulement).
 * Sur Android natif, c'est le plugin Capacitor qui gère nativement.
 */
export function initFirebase() {
  if (isNative()) return null // Géré par @capacitor-firebase/messaging

  if (!firebaseConfig.apiKey) {
    console.warn('[FCM] Config Firebase manquante.')
    return null
  }

  try {
    const app = initializeApp(firebaseConfig)
    messaging = getMessaging(app)
    return messaging
  } catch (err) {
    console.error('[FCM] Erreur initialisation Firebase:', err)
    return null
  }
}

/**
 * Demande la permission et enregistre le token FCM.
 * - Sur Android natif  → utilise @capacitor-firebase/messaging
 * - Sur le Web         → utilise le SDK Firebase Web
 */
export async function registerFcmToken() {
  try {
    let token = null

    if (isNative()) {
      // ── Android (Capacitor) ───────────────────────────────────────────────
      const { PushNotifications } = await import('@capacitor/push-notifications')

      // Demande la permission de notifications
      const { receive } = await PushNotifications.requestPermissions()
      if (receive !== 'granted') {
        console.warn('[FCM] Permission refusée sur Android')
        return
      }

      // Récupère le token FCM natif Android via l'événement 'registration'
      token = await new Promise((resolve, reject) => {
        PushNotifications.addListener('registration', (tokenData) => {
          resolve(tokenData.value)
        })
        PushNotifications.addListener('registrationError', (err) => {
          reject(new Error(err.error))
        })
        PushNotifications.register()
      })

    } else {
      // ── Web (navigateur) ─────────────────────────────────────────────────
      if (!messaging) return
      if (Notification.permission === 'denied') return

      const permission = await Notification.requestPermission()
      if (permission !== 'granted') return

      const swRegistration = await navigator.serviceWorker.ready
      token = await getToken(messaging, {
        vapidKey: VAPID_KEY,
        serviceWorkerRegistration: swRegistration,
      })
    }

    if (token) {
      await api.post('/notifications/fcm-token', { fcm_token: token })
      console.log('[FCM] Token enregistré ✓')
    }

  } catch (err) {
    console.error('[FCM] Erreur lors de la récupération du token:', err)
  }
}

/**
 * Supprime le token FCM du serveur au logout.
 */
export async function removeFcmToken() {
  try {
    await api.delete('/notifications/fcm-token')
  } catch (err) {
    // Silencieux au logout
  }
}

/**
 * Écoute les notifications quand l'app est ouverte (foreground).
 * - Sur Android natif  → utilise @capacitor-firebase/messaging
 * - Sur le Web         → utilise le SDK Firebase Web
 * @param {Function} callback — reçoit { title, body, data }
 * @returns {Function} unsubscribe
 */
export async function onForegroundMessage(callback) {
  if (isNative()) {
    const { PushNotifications } = await import('@capacitor/push-notifications')

    // Sur Android, les notifications foreground ne s'affichent pas automatiquement
    // Il faut les afficher manuellement via le callback
    await PushNotifications.addListener('pushNotificationReceived', (notification) => {
      callback({
        notification: {
          title: notification.title,
          body:  notification.body,
        },
        data: notification.data || {},
      })
    })

    // Retourne une fonction de nettoyage
    return () => PushNotifications.removeAllListeners()
  }

  // Web
  if (!messaging) return () => {}
  return onMessage(messaging, callback)
}
