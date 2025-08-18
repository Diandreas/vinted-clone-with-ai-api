<template>
  <div v-if="showDebugger" class="fixed bottom-4 right-4 bg-white border border-gray-300 rounded-lg shadow-lg p-4 max-w-md z-50">
    <div class="flex items-center justify-between mb-3">
      <h3 class="text-sm font-semibold text-gray-900">🔍 Debug Auth</h3>
      <button @click="showDebugger = false" class="text-gray-400 hover:text-gray-600">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </button>
    </div>
    
    <div class="space-y-2 text-xs">
      <!-- État de l'authentification -->
      <div class="flex items-center justify-between">
        <span class="text-gray-600">Authentifié:</span>
        <span :class="isAuthenticated ? 'text-green-600' : 'text-red-600'">
          {{ isAuthenticated ? '✅ Oui' : '❌ Non' }}
        </span>
      </div>
      
      <!-- Token -->
      <div class="flex items-center justify-between">
        <span class="text-gray-600">Token:</span>
        <span :class="hasToken ? 'text-green-600' : 'text-red-600'">
          {{ hasToken ? '✅ Présent' : '❌ Absent' }}
        </span>
      </div>
      
      <!-- Utilisateur -->
      <div class="flex items-center justify-between">
        <span class="text-gray-600">Utilisateur:</span>
        <span :class="user ? 'text-green-600' : 'text-red-600'">
          {{ user ? `✅ ${user.name}` : '❌ Non chargé' }}
        </span>
      </div>
      
      <!-- Token preview -->
      <div v-if="hasToken" class="bg-gray-100 p-2 rounded text-xs font-mono break-all">
        {{ tokenPreview }}
      </div>
      
      <!-- Actions de debug -->
      <div class="flex space-x-2 pt-2">
        <button 
          @click="testAuthEndpoint"
          class="px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600"
        >
          Test Auth
        </button>
        <button 
          @click="testConversationEndpoint"
          class="px-2 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600"
        >
          Test Conv
        </button>
        <button 
          @click="refreshAuth"
          class="px-2 py-1 bg-purple-500 text-white text-white text-xs rounded hover:bg-purple-600"
        >
          Refresh
        </button>
      </div>
      
      <!-- Résultats des tests -->
      <div v-if="testResults.length > 0" class="mt-3 space-y-1">
        <div v-for="(result, index) in testResults" :key="index" class="text-xs">
          <span :class="result.success ? 'text-green-600' : 'text-red-600'">
            {{ result.success ? '✅' : '❌' }}
          </span>
          {{ result.message }}
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bouton pour afficher le debugger -->
  <button 
    v-if="!showDebugger"
    @click="showDebugger = true"
    class="fixed bottom-4 right-4 bg-gray-800 text-white p-2 rounded-full shadow-lg z-50 hover:bg-gray-700"
    title="Debug Auth"
  >
    🔍
  </button>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const authStore = useAuthStore()
const showDebugger = ref(false)
const testResults = ref([])

// Computed
const isAuthenticated = computed(() => authStore.isAuthenticated)
const hasToken = computed(() => !!authStore.token)
const user = computed(() => authStore.user)
const tokenPreview = computed(() => {
  if (!authStore.token) return 'Aucun token'
  return authStore.token.substring(0, 20) + '...'
})

// Methods
const addTestResult = (success, message) => {
  testResults.value.unshift({
    success,
    message,
    timestamp: new Date().toLocaleTimeString()
  })
  
  // Garder seulement les 5 derniers résultats
  if (testResults.value.length > 5) {
    testResults.value = testResults.value.slice(0, 5)
  }
}

const testAuthEndpoint = async () => {
  try {
    const response = await api.get('/auth/user')
    addTestResult(true, `Auth endpoint OK: ${response.status}`)
  } catch (error) {
    addTestResult(false, `Auth endpoint FAIL: ${error.response?.status || 'Network error'}`)
  }
}

const testConversationEndpoint = async () => {
  try {
    // Utiliser un ID de produit fictif pour le test
    const response = await api.post('/conversations/start/999', {
      message: 'Test message'
    })
    addTestResult(true, `Conversation endpoint OK: ${response.status}`)
  } catch (error) {
    const status = error.response?.status
    if (status === 401) {
      addTestResult(false, `Conversation endpoint: Non authentifié (401)`)
    } else if (status === 404) {
      addTestResult(false, `Conversation endpoint: Produit non trouvé (404)`)
    } else if (status === 405) {
      addTestResult(false, `Conversation endpoint: Méthode non autorisée (405)`)
    } else {
      addTestResult(false, `Conversation endpoint FAIL: ${status || 'Network error'}`)
    }
  }
}

const refreshAuth = async () => {
  try {
    await authStore.initialize()
    addTestResult(true, 'Auth refresh OK')
  } catch (error) {
    addTestResult(false, `Auth refresh FAIL: ${error.message}`)
  }
}

// Debug info dans la console
onMounted(() => {
  console.log('🔍 Auth Debugger monté')
  console.log('État auth:', {
    isAuthenticated: isAuthenticated.value,
    hasToken: hasToken.value,
    user: user.value,
    token: authStore.token ? authStore.token.substring(0, 20) + '...' : 'Aucun'
  })
})
</script>

<style scoped>
/* Styles pour le debugger */
.debugger-enter-active,
.debugger-leave-active {
  transition: all 0.3s ease;
}

.debugger-enter-from,
.debugger-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
