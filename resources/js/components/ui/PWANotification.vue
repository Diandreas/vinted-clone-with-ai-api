<template>
  <div v-if="showNotificationPrompt" class="fixed top-4 left-4 right-4 z-50">
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-4 max-w-sm mx-auto">
      <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-semibold text-gray-900">Activer les notifications</h3>
          <p class="text-xs text-gray-600 mt-1">
            {{ notificationInstructions }}
          </p>
        </div>
        <button
          @click="dismissNotificationPrompt"
          class="text-gray-400 hover:text-gray-600 p-1"
          aria-label="Fermer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <!-- iOS Specific Notification Instructions -->
      <div v-if="isIOS" class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
        <div class="flex items-start space-x-2">
          <div class="flex-shrink-0">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <h4 class="text-xs font-medium text-blue-900">Instructions iOS</h4>
            <ol class="mt-1 text-xs text-blue-800 space-y-1">
              <li>1. Allez dans <strong>Paramètres</strong> → <strong>Safari</strong></li>
              <li>2. Sélectionnez <strong>Notifications</strong></li>
              <li>3. Activez les notifications pour RIKEAA</li>
            </ol>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mt-3 flex space-x-2">
        <button
          @click="requestNotificationPermission"
          class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium py-2 px-3 rounded-lg transition-colors duration-200"
        >
          {{ isIOS ? 'Paramètres' : 'Activer' }}
        </button>
        <button
          @click="dismissNotificationPrompt"
          class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium py-2 px-3 rounded-lg transition-colors duration-200"
        >
          Plus tard
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const showNotificationPrompt = ref(false)
const notificationPermission = ref('default')

// Detect device type
const isIOS = computed(() => {
  return /iPad|iPhone|iPod/.test(navigator.userAgent) || 
         (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
})

const isAndroid = computed(() => {
  return /Android/.test(navigator.userAgent)
})

// Notification instructions based on device
const notificationInstructions = computed(() => {
  if (isIOS.value) {
    return 'Recevez des alertes pour vos commandes et messages'
  } else if (isAndroid.value) {
    return 'Recevez des notifications push pour rester informé'
  } else {
    return 'Activez les notifications pour rester informé'
  }
})

// Check notification permission
const checkNotificationPermission = () => {
  if ('Notification' in window) {
    notificationPermission.value = Notification.permission
  }
}

// Request notification permission
const requestNotificationPermission = async () => {
  if (!('Notification' in window)) {
    alert('Ce navigateur ne supporte pas les notifications')
    return
  }

  if (isIOS.value) {
    // On iOS, guide user to Safari settings
    if (confirm('Pour activer les notifications sur iOS, vous devez aller dans les paramètres Safari. Voulez-vous être guidé ?')) {
      // Show iOS specific instructions
      showIOSNotificationGuide()
    }
  } else {
    // On other platforms, request permission directly
    try {
      const permission = await Notification.requestPermission()
      notificationPermission.value = permission
      
      if (permission === 'granted') {
        showNotificationPrompt.value = false
        showSuccessMessage()
        // Store permission granted
        localStorage.setItem('notification-permission-granted', 'true')
      } else if (permission === 'denied') {
        showDeniedMessage()
      }
    } catch (error) {
      console.error('Error requesting notification permission:', error)
    }
  }
}

// Show iOS specific notification guide
const showIOSNotificationGuide = () => {
  const guide = `
Instructions pour activer les notifications sur iOS :

1. Ouvrez l'application Paramètres sur votre iPhone/iPad
2. Faites défiler et appuyez sur "Safari"
3. Appuyez sur "Notifications"
4. Activez "Autoriser les notifications"
5. Assurez-vous que RIKEAA est activé

Ou utilisez la recherche dans Paramètres en tapant "Notifications Safari"
  `
  
  alert(guide)
}

// Show success message
const showSuccessMessage = () => {
  // You can implement a toast notification here
  console.log('Notifications activées avec succès')
}

// Show denied message
const showDeniedMessage = () => {
  alert('Les notifications ont été refusées. Vous pouvez les activer plus tard dans les paramètres de votre navigateur.')
}

// Dismiss notification prompt
const dismissNotificationPrompt = () => {
  showNotificationPrompt.value = false
  // Store dismissal in localStorage
  localStorage.setItem('notification-prompt-dismissed', Date.now().toString())
}

// Check if we should show the notification prompt
const shouldShowNotificationPrompt = () => {
  // Don't show if already granted
  if (notificationPermission.value === 'granted') return false
  
  // Don't show if recently dismissed
  const dismissed = localStorage.getItem('notification-prompt-dismissed')
  if (dismissed) {
    const dismissedTime = parseInt(dismissed)
    const now = Date.now()
    // Show again after 3 days
    if (now - dismissedTime < 3 * 24 * 60 * 60 * 1000) {
      return false
    }
  }
  
  // Don't show if permission was previously granted
  if (localStorage.getItem('notification-permission-granted')) return false
  
  return true
}

// Lifecycle
onMounted(() => {
  checkNotificationPermission()
  
  // Show prompt after a delay if conditions are met
  setTimeout(() => {
    if (shouldShowNotificationPrompt()) {
      showNotificationPrompt.value = true
    }
  }, 5000)
})
</script>

<style scoped>
/* iOS specific styles */
.ios-device .notification-prompt {
  padding-top: env(safe-area-inset-top);
}
</style>
