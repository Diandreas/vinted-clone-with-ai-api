<template>
  <div v-if="showInstallPrompt" class="fixed bottom-4 left-4 right-4 z-50">
    <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-4 max-w-sm mx-auto">
      <div class="flex items-center space-x-3">
        <div class="w-12 h-12 bg-primary-500 rounded-lg flex items-center justify-center">
          <img src="/logo.png" alt="RIKEAA" class="w-8 h-8 rounded" />
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-semibold text-gray-900">Installer RIKEAA</h3>
          <p class="text-xs text-gray-600">Accédez rapidement à l'application</p>
        </div>
        <button
          @click="installApp"
          :disabled="installing"
          class="bg-primary-500 hover:bg-primary-600 disabled:bg-gray-400 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        >
          <span v-if="installing">Installation...</span>
          <span v-else>Installer</span>
        </button>
        <button
          @click="dismissPrompt"
          class="text-gray-400 hover:text-gray-600 p-1"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const showInstallPrompt = ref(false)
const installing = ref(false)
let deferredPrompt = null

onMounted(() => {
  // Écouter l'événement beforeinstallprompt
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault()
    deferredPrompt = e
    showInstallPrompt.value = true
  })

  // Vérifier si l'app est déjà installée
  if (window.matchMedia('(display-mode: standalone)').matches || 
      window.navigator.standalone === true) {
    showInstallPrompt.value = false
  }
})

onUnmounted(() => {
  window.removeEventListener('beforeinstallprompt', () => {})
})

const installApp = async () => {
  if (!deferredPrompt) return

  installing.value = true
  deferredPrompt.prompt()

  const { outcome } = await deferredPrompt.userChoice
  
  if (outcome === 'accepted') {
    console.log('PWA installée avec succès')
    showInstallPrompt.value = false
  } else {
    console.log('Installation PWA annulée')
  }

  deferredPrompt = null
  installing.value = false
}

const dismissPrompt = () => {
  showInstallPrompt.value = false
  deferredPrompt = null
}
</script>
