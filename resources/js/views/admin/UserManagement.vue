<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Gestion des Utilisateurs
          </h2>
          <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <UsersIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
              {{ stats.total }} utilisateurs au total
            </div>
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <CheckCircleIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-green-400" />
              {{ stats.verified }} vérifiés
            </div>
          </div>
        </div>
      </div>

      <!-- Filtres -->
      <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <label for="search" class="block text-sm font-medium text-gray-700">Recherche</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <SearchIcon class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  id="search"
                  v-model="filters.search"
                  type="text"
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="Rechercher un utilisateur..."
                  @input="debounceSearch"
                />
              </div>
            </div>

            <div>
              <label for="verified" class="block text-sm font-medium text-gray-700">Statut</label>
              <select
                id="verified"
                v-model="filters.verified"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                @change="loadUsers"
              >
                <option value="">Tous les statuts</option>
                <option value="1">Vérifiés</option>
                <option value="0">Non vérifiés</option>
              </select>
            </div>

            <div>
              <label for="sort" class="block text-sm font-medium text-gray-700">Trier par</label>
              <select
                id="sort"
                v-model="filters.sort"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                @change="loadUsers"
              >
                <option value="created_at">Date d'inscription</option>
                <option value="name">Nom</option>
                <option value="email">Email</option>
                <option value="last_seen_at">Dernière activité</option>
              </select>
            </div>

            <div>
              <label for="per_page" class="block text-sm font-medium text-gray-700">Par page</label>
              <select
                id="per_page"
                v-model="filters.per_page"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                @change="loadUsers"
              >
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Table des utilisateurs -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div v-if="loading" class="p-6">
          <div class="animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
            <div class="h-4 bg-gray-200 rounded w-5/6"></div>
          </div>
        </div>
        
        <div v-else-if="users.length === 0" class="text-center py-12">
          <UsersIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun utilisateur</h3>
          <p class="mt-1 text-sm text-gray-500">Aucun utilisateur trouvé avec ces critères.</p>
        </div>

        <ul v-else class="divide-y divide-gray-200">
          <li v-for="user in users" :key="user.id">
            <div class="px-4 py-4 flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-12 w-12">
                  <img
                    class="h-12 w-12 rounded-full object-cover"
                    :src="user.avatar || '/default-avatar.png'"
                    :alt="user.name"
                  />
                </div>
                <div class="ml-4">
                  <div class="flex items-center">
                    <p class="text-sm font-medium text-gray-900">{{ user.name }}</p>
                    <div v-if="user.is_admin" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                      Admin
                    </div>
                  </div>
                  <p class="text-sm text-gray-500">{{ user.email }}</p>
                  <div class="flex items-center mt-1 text-xs text-gray-400">
                    <CalendarIcon class="h-4 w-4 mr-1" />
                    Inscrit le {{ formatDate(user.created_at) }}
                    <span v-if="user.last_seen_at" class="ml-4">
                      <ClockIcon class="h-4 w-4 mr-1" />
                      Dernière activité: {{ formatDate(user.last_seen_at) }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="flex items-center space-x-3">
                <!-- Status badges -->
                <div class="flex flex-col items-end space-y-2">
                  <span 
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      user.email_verified_at 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-yellow-100 text-yellow-800'
                    ]"
                  >
                    <CheckCircleIcon v-if="user.email_verified_at" class="h-3 w-3 mr-1" />
                    <XCircleIcon v-else class="h-3 w-3 mr-1" />
                    {{ user.email_verified_at ? 'Vérifié' : 'Non vérifié' }}
                  </span>
                  
                  <span 
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      user.status === 'active' 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ user.status === 'active' ? 'Actif' : 'Inactif' }}
                  </span>
                </div>

                <!-- Actions -->
              <div class="flex items-center space-x-2">
                <button
                  @click="viewUser(user)"
                    class="text-indigo-600 hover:text-indigo-900 transition-colors"
                    title="Voir les détails"
                >
                  <EyeIcon class="h-5 w-5" />
                </button>
                  
                  <button
                    @click="toggleUserStatus(user)"
                    :class="[
                      'transition-colors',
                      user.status === 'active' 
                        ? 'text-red-600 hover:text-red-900' 
                        : 'text-green-600 hover:text-green-900'
                    ]"
                    :title="user.status === 'active' ? 'Désactiver' : 'Activer'"
                  >
                    <PowerIcon v-if="user.status === 'active'" class="h-5 w-5" />
                    <CheckCircleIcon v-else class="h-5 w-5" />
                  </button>
                  
                <button
                    v-if="!user.is_admin"
                    @click="toggleAdminStatus(user)"
                    class="text-purple-600 hover:text-purple-900 transition-colors"
                    title="Donner les droits admin"
                  >
                    <ShieldIcon class="h-5 w-5" />
                </button>
                  
                <button
                    v-else
                    @click="toggleAdminStatus(user)"
                    class="text-gray-600 hover:text-gray-900 transition-colors"
                    title="Retirer les droits admin"
                  >
                    <UserIcon class="h-5 w-5" />
                </button>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>

        <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
            @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Précédent
            </button>
            <button
            @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Suivant
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
              Affichage de <span class="font-medium">{{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}</span> à 
              <span class="font-medium">{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}</span> sur 
              <span class="font-medium">{{ pagination.total }}</span> résultats
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                @click="changePage(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronLeftIcon class="h-5 w-5" />
                </button>
                
                  <button
                @click="changePage(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronRightIcon class="h-5 w-5" />
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import {
  UsersIcon,
  SearchIcon, 
  CheckCircleIcon,
  XCircleIcon,
  CalendarIcon,
  ClockIcon,
  EyeIcon,
  PowerIcon,
  ShieldIcon,
  UserIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from 'lucide-vue-next'
import { debounce } from 'lodash'

// State
const loading = ref(false)
const users = ref([])
const stats = ref({
  total: 0,
  verified: 0
})

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

// Filters
const filters = reactive({
  search: '',
  verified: '',
  sort: 'created_at',
  per_page: 15,
  page: 1
})

// Load users
const loadUsers = async () => {
  loading.value = true
  
  try {
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.verified !== '') params.append('verified', filters.verified)
    if (filters.sort) params.append('sort', filters.sort)
    params.append('page', filters.page)
    params.append('per_page', filters.per_page)
    
    const response = await window.axios.get(`/admin/users?${params}`)
    const data = response.data
    
    users.value = data.data || []
    
    if (data.meta) {
      pagination.value = data.meta
    }
    
    // Update stats
    if (data.stats) {
      stats.value = data.stats
    }
    
  } catch (error) {
    console.error('Erreur lors du chargement des utilisateurs:', error)
    users.value = []
  } finally {
    loading.value = false
  }
}

// Debounced search
const debounceSearch = debounce(() => {
  filters.page = 1
  loadUsers()
}, 300)

// Change page
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    filters.page = page
    loadUsers()
  }
}

// View user details
const viewUser = (user) => {
  // Navigate to user detail page or open modal
  console.log('View user:', user)
}

// Toggle user status
const toggleUserStatus = async (user) => {
  try {
    const newStatus = user.status === 'active' ? 'inactive' : 'active'
    await window.axios.patch(`/admin/users/${user.id}/status`, {
      status: newStatus
    })
    
    // Update local state
    user.status = newStatus
    
    // Reload users to update stats
    await loadUsers()
    
  } catch (error) {
    console.error('Erreur lors du changement de statut:', error)
    alert('Erreur lors du changement de statut de l\'utilisateur')
  }
}

// Toggle admin status
const toggleAdminStatus = async (user) => {
  try {
    const newAdminStatus = !user.is_admin
    await window.axios.patch(`/admin/users/${user.id}/admin`, {
      is_admin: newAdminStatus
    })
    
    // Update local state
    user.is_admin = newAdminStatus
    
  } catch (error) {
    console.error('Erreur lors du changement de statut admin:', error)
    alert('Erreur lors du changement de statut admin de l\'utilisateur')
  }
}

// Format date
const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

// Initialize
onMounted(() => {
  loadUsers()
})
</script>
