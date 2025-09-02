<template>
  <div v-if="showInstallPrompt" class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-lg">
    <div class="max-w-md mx-auto p-4">
      <div class="flex items-center justify-between">
        <div class="flex-1">
          <h3 class="text-lg font-semibold text-gray-900">Installer RIKEAA</h3>
          <p class="text-sm text-gray-600 mt-1">
            {{ installInstructions }}
          </p>
        </div>
        <div class="flex items-center space-x-2">
          <button
            @click="dismissPrompt"
            class="text-gray-400 hover:text-gray-600 p-2"
            aria-label="Fermer"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
      
      <!-- iOS Specific Instructions -->
      <div v-if="isIOS" class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <h4 class="text-sm font-medium text-blue-900">Instructions pour iOS</h4>
            <ol class="mt-2 text-xs text-blue-800 space-y-1">
              <li>1. Appuyez sur le bouton <strong>Partager</strong> <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M16 5l-1.42 1.42-1.59-1.59V16h-1.02V4.83L13.42 6.25 12 4.83 16 5zM20 10v11c0 .55-.45 1-1 1H5c-.55 0-1-.45-1-1V10c0-.55.45-1 1-1h3V8H5c-1.11 0-2 .89-2 2v11c0 1.11.89 2 2 2h14c1.11 0 2-.89 2-2V10c0-1.11-.89-2-2-2h-3V9h3c.55 0 1 .45 1 1z"/></svg></li>
              <li>2. Sélectionnez <strong>Sur l'écran d'accueil</strong></li>
              <li>3. Appuyez sur <strong>Ajouter</strong></li>
            </ol>
          </div>
        </div>
      </div>

      <!-- Android/Chrome Instructions -->
      <div v-else-if="isAndroid" class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <h4 class="text-sm font-medium text-green-900">Instructions pour Android</h4>
            <p class="mt-1 text-xs text-green-800">
              Appuyez sur <strong>Installer</strong> dans la barre d'adresse ou utilisez le menu Chrome.
            </p>
          </div>
        </div>
      </div>

      <!-- Desktop Instructions -->
      <div v-else class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
        <div class="flex items-start space-x-3">
          <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="flex-1">
            <h4 class="text-sm font-medium text-gray-900">Instructions pour ordinateur</h4>
            <p class="mt-1 text-xs text-gray-800">
              Cliquez sur l'icône d'installation dans la barre d'adresse de votre navigateur.
            </p>
          </div>
        </div>
      </div>

      <!-- Install Button for supported browsers -->
      <div v-if="!isIOS && canInstall" class="mt-4">
        <button
          @click="installApp"
          class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
        >
          Installer l'application
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const showInstallPrompt = ref(false)
const deferredPrompt = ref(null)
const canInstall = ref(false)

// Detect device type
const isIOS = computed(() => {
  return /iPad|iPhone|iPod/.test(navigator.userAgent) || 
         (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)
})

const isAndroid = computed(() => {
  return /Android/.test(navigator.userAgent)
})

// Install instructions based on device
const installInstructions = computed(() => {
  if (isIOS.value) {
    return 'Ajoutez RIKEAA à votre écran d\'accueil pour un accès rapide'
  } else if (isAndroid.value) {
    return 'Installez RIKEAA sur votre appareil Android'
  } else {
    return 'Installez RIKEAA sur votre ordinateur'
  }
})

// Check if app is already installed
const isAppInstalled = computed(() => {
  return window.matchMedia('(display-mode: standalone)').matches ||
         window.navigator.standalone === true
})

// Handle install prompt
const handleBeforeInstallPrompt = (e) => {
  e.preventDefault()
  deferredPrompt.value = e
  canInstall.value = true
  
  // Show prompt after a delay
  setTimeout(() => {
    if (!isAppInstalled.value) {
      showInstallPrompt.value = true
    }
  }, 3000)
}

// Install the app
const installApp = async () => {
  if (!deferredPrompt.value) return
  
  deferredPrompt.value.prompt()
  const { outcome } = await deferredPrompt.value.userChoice
  
  if (outcome === 'accepted') {
    console.log('User accepted the install prompt')
    showInstallPrompt.value = false
  } else {
    console.log('User dismissed the install prompt')
  }
  
  deferredPrompt.value = null
  canInstall.value = false
}

// Dismiss the prompt
const dismissPrompt = () => {
  showInstallPrompt.value = false
  // Store dismissal in localStorage to avoid showing again soon
  localStorage.setItem('pwa-prompt-dismissed', Date.now().toString())
}

// Check if we should show the prompt
const shouldShowPrompt = () => {
  if (isAppInstalled.value) return false
  
  const dismissed = localStorage.getItem('pwa-prompt-dismissed')
  if (dismissed) {
    const dismissedTime = parseInt(dismissed)
    const now = Date.now()
    // Show again after 7 days
    if (now - dismissedTime < 7 * 24 * 60 * 60 * 1000) {
      return false
    }
  }
  
  return true
}

// Lifecycle
onMounted(() => {
  // Listen for install prompt
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
  
  // Check if we should show the prompt
  if (shouldShowPrompt()) {
    // Show iOS prompt immediately, others after delay
    if (isIOS.value) {
      setTimeout(() => {
        showInstallPrompt.value = true
      }, 2000)
    }
  }
  
  // Check if app is already installed
  if (isAppInstalled.value) {
    showInstallPrompt.value = false
  }
})

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt)
})
</script>

<style scoped>
/* iOS specific styles */
.ios-device .pwa-install-prompt {
  padding-bottom: env(safe-area-inset-bottom);
}
</style>
