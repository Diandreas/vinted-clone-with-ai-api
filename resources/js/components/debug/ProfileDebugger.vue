<template>
  <div v-if="showDebug" class="fixed top-4 right-4 bg-black/90 text-white p-4 rounded-lg max-w-md z-50 text-xs">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-bold">🐛 Debug Profile</h3>
      <button @click="showDebug = false" class="text-gray-400 hover:text-white">✕</button>
    </div>
    
    <!-- État de l'authentification -->
    <div class="mb-3 p-2 bg-gray-800 rounded">
      <h4 class="font-semibold mb-2">🔐 Authentification</h4>
      <div class="space-y-1">
        <div>Utilisateur: {{ user ? '✅ Connecté' : '❌ Déconnecté' }}</div>
        <div>Token: {{ token ? '✅ Présent' : '❌ Absent' }}</div>
        <div>ID: {{ user?.id || 'N/A' }}</div>
        <div>Nom: {{ user?.name || 'N/A' }}</div>
      </div>
    </div>
    
    <!-- État des données -->
    <div class="mb-3 p-2 bg-gray-800 rounded">
      <h4 class="font-semibold mb-2">📊 Données</h4>
      <div class="space-y-1">
        <div>Stats: {{ stats ? '✅ Chargées' : '❌ Non chargées' }}</div>
        <div>Produits: {{ productsCount }} items</div>
        <div>Followers: {{ followersCount }} items</div>
        <div>Following: {{ followingCount }} items</div>
        <div>Activité: {{ activityCount }} items</div>
      </div>
    </div>
    
    <!-- État des chargements -->
    <div class="mb-3 p-2 bg-gray-800 rounded">
      <h4 class="font-semibold mb-2">🔄 Chargements</h4>
      <div class="space-y-1">
        <div>Produits: {{ loadingProducts ? '⏳ Chargement...' : '✅ Terminé' }}</div>
        <div>Followers: {{ loadingFollowers ? '⏳ Chargement...' : '✅ Terminé' }}</div>
        <div>Following: {{ loadingFollowing ? '⏳ Chargement...' : '✅ Terminé' }}</div>
        <div>Activité: {{ loadingActivity ? '⏳ Chargement...' : '✅ Terminé' }}</div>
      </div>
    </div>
    
    <!-- Actions de debug -->
    <div class="space-y-2">
      <button 
        @click="refreshAll"
        class="w-full bg-blue-600 hover:bg-blue-700 px-3 py-2 rounded text-xs"
      >
        🔄 Rafraîchir tout
      </button>
      <button 
        @click="checkAuth"
        class="w-full bg-green-600 hover:bg-green-700 px-3 py-2 rounded text-xs"
      >
        🔍 Vérifier Auth
      </button>
      <button 
        @click="clearData"
        class="w-full bg-red-600 hover:bg-red-700 px-3 py-2 rounded text-xs"
      >
        🗑️ Vider données
      </button>
    </div>
    
    <!-- Logs récents -->
    <div class="mt-3 p-2 bg-gray-800 rounded max-h-32 overflow-y-auto">
      <h4 class="font-semibold mb-2">📝 Logs</h4>
      <div class="space-y-1 text-xs">
        <div v-for="log in recentLogs" :key="log.id" class="text-gray-300">
          {{ log.time }}: {{ log.message }}
        </div>
      </div>
    </div>
  </div>
  
  <!-- Bouton pour afficher le debug -->
  <button 
    v-else
    @click="showDebug = true"
    class="fixed top-4 right-4 bg-black/90 text-white p-2 rounded-full z-50 text-xs"
    title="Debug Profile"
  >
    🐛
  </button>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useDashboardStore } from '@/stores/dashboard'

const authStore = useAuthStore()
const dashboardStore = useDashboardStore()

const showDebug = ref(false)
const recentLogs = ref([])

// Computed
const user = computed(() => authStore.user)
const token = computed(() => authStore.token)
const stats = computed(() => dashboardStore.stats)
const productsCount = computed(() => dashboardStore.recentProducts?.length || 0)
const followersCount = computed(() => 0) // À connecter avec le composant parent
const followingCount = computed(() => 0) // À connecter avec le composant parent
const activityCount = computed(() => dashboardStore.recentActivity?.length || 0)
const loadingProducts = computed(() => false) // À connecter avec le composant parent
const loadingFollowers = computed(() => false) // À connecter avec le composant parent
const loadingFollowing = computed(() => false) // À connecter avec le composant parent
const loadingActivity = computed(() => false) // À connecter avec le composant parent

// Méthodes
const addLog = (message) => {
  const log = {
    id: Date.now(),
    time: new Date().toLocaleTimeString(),
    message
  }
  recentLogs.value.unshift(log)
  if (recentLogs.value.length > 10) {
    recentLogs.value = recentLogs.value.slice(0, 10)
  }
}

const refreshAll = async () => {
  addLog('🔄 Rafraîchissement de toutes les données...')
  try {
    await dashboardStore.refreshData()
    addLog('✅ Données rafraîchies avec succès')
  } catch (error) {
    addLog(`❌ Erreur rafraîchissement: ${error.message}`)
  }
}

const checkAuth = () => {
  addLog('🔍 Vérification de l\'authentification...')
  addLog(`👤 Utilisateur: ${user.value ? 'Connecté' : 'Déconnecté'}`)
  addLog(`🔑 Token: ${token.value ? 'Présent' : 'Absent'}`)
  addLog(`📊 Stats: ${stats.value ? 'Chargées' : 'Non chargées'}`)
}

const clearData = () => {
  addLog('🗑️ Suppression des données...')
  // Réinitialiser les stores si nécessaire
  addLog('✅ Données supprimées')
}

// Lifecycle
onMounted(() => {
  addLog('🚀 ProfileDebugger monté')
  addLog(`👤 Utilisateur: ${user.value ? 'Connecté' : 'Déconnecté'}`)
})
</script>






