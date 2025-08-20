<template>
  <div v-if="showInstallButton" class="relative">
    <button
      @click="showInstallMenu = !showInstallMenu"
      class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-primary-600 transition-all duration-200 rounded-xl hover:bg-primary-50"
      title="Installer l'application"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
      </svg>
      <span class="hidden sm:inline">Installer</span>
    </button>

    <!-- Dropdown Menu -->
    <div v-if="showInstallMenu" class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
      <div class="px-4 py-3 border-b border-gray-100">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 bg-primary-500 rounded-lg flex items-center justify-center">
            <img src="/logo.png" alt="RIKEAA" class="w-6 h-6 rounded" />
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-900">RIKEAA</h3>
            <p class="text-xs text-gray-600">Marketplace</p>
          </div>
        </div>
      </div>
      
      <div class="px-4 py-3">
        <p class="text-xs text-gray-600 mb-3">
          Installez RIKEAA sur votre appareil pour un accès rapide et une meilleure expérience.
        </p>
        
        <button
          @click="installApp"
          :disabled="installing"
          class="w-full bg-primary-500 hover:bg-primary-600 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors mb-2"
        >
          <span v-if="installing">Installation...</span>
          <span v-else>Installer l'application</span>
        </button>
        
        <div class="text-xs text-gray-500 text-center">
          <p>✓ Fonctionne hors ligne</p>
          <p>✓ Accès rapide</p>
          <p>✓ Notifications push</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const showInstallButton = ref(false)
const showInstallMenu = ref(false)
const installing = ref(false)
let deferredPrompt = null

onMounted(() => {
  // Écouter l'événement beforeinstallprompt
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault()
    deferredPrompt = e
    showInstallButton.value = true
  })

  // Vérifier si l'app est déjà installée
  if (window.matchMedia('(display-mode: standalone)').matches || 
      window.navigator.standalone === true) {
    showInstallButton.value = false
  }

  // Fermer le menu si on clique ailleurs
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', () => {})
  document.removeEventListener('click', handleClickOutside)
})

const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showInstallMenu.value = false
  }
}

const installApp = async () => {
  if (!deferredPrompt) return

  installing.value = true
  showInstallMenu.value = false
  
  deferredPrompt.prompt()

  const { outcome } = await deferredPrompt.userChoice
  
  if (outcome === 'accepted') {
    console.log('PWA installée avec succès')
    showInstallButton.value = false
  } else {
    console.log('Installation PWA annulée')
  }

  deferredPrompt = null
  installing.value = false
}
</script>
