<template>
  <div v-if="showNotification" class="fixed top-4 left-4 right-4 z-50">
    <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg shadow-lg text-white p-4 max-w-md mx-auto">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
          <img src="/logo.png" alt="RIKEAA" class="w-6 h-6 rounded" />
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-semibold">Installez RIKEAA !</h3>
          <p class="text-xs text-primary-100">Accédez rapidement à l'application depuis votre écran d'accueil</p>
        </div>
        <button
          @click="installApp"
          :disabled="installing"
          class="bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
        >
          <span v-if="installing">Installation...</span>
          <span v-else>Installer</span>
        </button>
        <button
          @click="dismissNotification"
          class="text-white/70 hover:text-white p-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const showNotification = ref(false)
const installing = ref(false)
let deferredPrompt = null

onMounted(() => {
  // Attendre un peu avant d'afficher la notification
  setTimeout(() => {
    // Écouter l'événement beforeinstallprompt
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault()
      deferredPrompt = e
      
      // Vérifier si l'utilisateur n'a pas déjà fermé la notification
      if (!localStorage.getItem('pwa-notification-dismissed')) {
        showNotification.value = true
      }
    })
  }, 3000) // Afficher après 3 secondes
})

const installApp = async () => {
  if (!deferredPrompt) return

  installing.value = true
  deferredPrompt.prompt()

  const { outcome } = await deferredPrompt.userChoice
  
  if (outcome === 'accepted') {
    console.log('PWA installée avec succès')
    showNotification.value = false
    localStorage.setItem('pwa-installed', 'true')
  } else {
    console.log('Installation PWA annulée')
  }

  deferredPrompt = null
  installing.value = false
}

const dismissNotification = () => {
  showNotification.value = false
  localStorage.setItem('pwa-notification-dismissed', 'true')
}
</script>
