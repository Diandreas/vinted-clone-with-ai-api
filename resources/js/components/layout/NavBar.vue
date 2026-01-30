<template>
  <nav class="bg-white/95 backdrop-blur-md shadow-soft border-b border-primary-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8">
      <div class="flex justify-between items-center h-14 sm:h-16">
        <!-- Logo and Brand -->
        <div class="flex items-center space-x-4 sm:space-x-8">
          <RouterLink to="/" class="flex items-center space-x-2 sm:space-x-3 group">
            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-white rounded-lg flex items-center justify-center shadow-soft group-hover:shadow-medium transition-all duration-150 group-hover:scale-105 overflow-hidden">
              <img src="/logo.png" alt="RIKEAA" class="w-full h-full object-cover rounded-lg" />
            </div>
            <span class="text-lg sm:text-xl font-bold text-primary-600">RIKEAA</span>
          </RouterLink>

          <!-- Main Navigation -->
          <div class="hidden md:flex items-center space-x-1 lg:space-x-2">
            <RouterLink
              to="/products"
              class="text-gray-600 hover:text-primary-600 px-3 py-2 text-sm font-medium transition-all duration-200 rounded-lg hover:bg-primary-50"
              :class="{ 'text-primary-600 bg-primary-50': $route.name === 'products' }"
            >
              Produits
            </RouterLink>

          </div>
        </div>

        <!-- Search Bar -->
        <div class="hidden md:flex flex-1 max-w-md lg:max-w-lg mx-4 sm:mx-6 lg:mx-8">
          <div class="relative w-full">
            <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher des produits..."
              class="w-full pl-9 sm:pl-10 pr-12 py-2 sm:py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200 text-sm bg-gray-50/50 hover:bg-white focus:bg-white"
              @keydown.enter="performSearch"
            />
            <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-1">
              <RouterLink
                to="/search/image"
                class="p-1.5 text-gray-400 hover:text-primary-600 transition-colors rounded-lg hover:bg-primary-50"
                title="Recherche par image"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </RouterLink>
              <button
                v-if="searchQuery"
                @click="clearSearch"
                class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
              >
                <XIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Right Side Actions -->
        <div class="flex items-center space-x-1 sm:space-x-3 lg:space-x-4">
          <!-- Bouton Vendre - Visible sur desktop -->
          <RouterLink
            v-if="isAuthenticated"
            to="/products/create"
            class="hidden md:flex items-center space-x-2 bg-primary-500 hover:bg-primary-600 text-white px-4 lg:px-6 py-2 lg:py-2.5 rounded-lg font-medium transition-all duration-150 shadow-soft hover:shadow-medium transform hover:scale-105 active:scale-95"
          >
            <svg class="w-4 h-4 lg:w-5 lg:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span class="text-sm lg:text-base font-semibold">Vendre</span>
          </RouterLink>

          <!-- Bouton Vendre Mobile -->
          <RouterLink
            v-if="isAuthenticated"
            to="/products/create"
            class="md:hidden bg-primary-500 hover:bg-primary-600 text-white px-3 py-2 rounded-lg transition-all duration-150 shadow-soft hover:shadow-medium transform hover:scale-105 active:scale-95 text-sm font-medium"
            title="Vendre"
          >
            Vendre
          </RouterLink>

          <!-- PWA Install Button -->
          <InstallPWAButton />

          <!-- Mobile Search Toggle -->
          <button
            @click="showMobileSearch = !showMobileSearch"
            class="md:hidden p-2 text-gray-400 hover:text-primary-600 transition-colors rounded-lg hover:bg-primary-50"
          >
            <SearchIcon class="w-5 h-5" />
          </button>

          <!-- Authenticated User Actions -->
          <template v-if="isAuthenticated">

            <!-- Admin Dropdown -->
            <div v-if="authStore.isAdmin || authStore.hasPermission('dashboard:view')" class="relative">
              <button
                @click="showAdminMenu = !showAdminMenu"
                class="flex items-center space-x-1 px-2 sm:px-3 py-2 text-xs sm:text-sm font-medium text-gray-600 hover:text-gray-900 transition-all duration-200 rounded-xl hover:bg-gray-50"
                :class="{ 'text-primary-600 bg-primary-50': $route.name?.includes('admin') }"
              >
                <PackageIcon class="w-4 h-4" />
                <span class="hidden sm:inline">Admin</span>
                <ChevronDownIcon class="hidden sm:inline w-3 h-3" />
              </button>

              <Transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <div
                  v-show="showAdminMenu"
                  @click="showAdminMenu = false"
                  class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                >
                  <RouterLink
                    to="/admin/dashboard"
                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    :class="{ 'bg-gray-100': $route.name === 'admin-dashboard' }"
                  >
                    <BarChart3Icon class="w-4 h-4 mr-3" />
                    Dashboard
                  </RouterLink>
                  <RouterLink
                    v-if="authStore.isAdmin || authStore.hasPermission('users:manage')"
                    to="/admin/users"
                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    :class="{ 'bg-gray-100': $route.name === 'admin-users' }"
                  >
                    <UsersIcon class="w-4 h-4 mr-3" />
                    Gestion Utilisateurs
                  </RouterLink>
                  <RouterLink
                    v-if="authStore.isAdmin || authStore.hasPermission('products:moderate')"
                    to="/admin/products"
                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    :class="{ 'bg-gray-100': $route.name === 'admin-dashboard' }"
                  >
                    <PackageIcon class="w-4 h-4 mr-3" />
                    Gestion Produits
                  </RouterLink>
                  <RouterLink
                    to="/admin/categories"
                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    :class="{ 'bg-gray-100': $route.name === 'admin-categories' }"
                  >
                    <TagIcon class="w-4 h-2 mr-3" />
                    Gestion Catégories
                  </RouterLink>
                </div>
              </Transition>
            </div>

            <!-- Notifications - Visible sur desktop seulement -->
            <div class="relative hidden md:block">
              <button
                @click="showNotifications = !showNotifications"
                class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors"
              >
                <BellIcon class="w-6 h-6" />
                <span
                  v-if="unreadNotifications > 0"
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
                >
                  {{ unreadNotifications > 9 ? '9+' : unreadNotifications }}
                </span>
              </button>

              <!-- Notifications Dropdown -->
              <NotificationDropdown
                v-if="showNotifications"
                @close="showNotifications = false"
              />
            </div>

            <!-- User Menu - Visible sur desktop seulement -->
            <div class="relative hidden md:block">
              <button
                @click="showUserMenu = !showUserMenu"
                class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <img
                  :src="user?.avatar || generateDefaultAvatar(user?.name, user?.id)"
                  :alt="user?.name"
                  class="w-8 h-8 rounded-full object-cover"
                  @error="handleAvatarError"
                />
                <ChevronDownIcon class="w-4 h-4 text-gray-400" />
              </button>

              <!-- User Dropdown -->
              <UserDropdown
                v-if="showUserMenu"
                @close="showUserMenu = false"
              />
            </div>
          </template>

          <!-- Guest Actions -->
          <template v-else>
            <RouterLink
              to="/login"
              class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium"
            >
              Connexion
            </RouterLink>
            <RouterLink
              to="/register"
              class="bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors"
            >
              S'inscrire
            </RouterLink>
          </template>
        </div>
      </div>

      <!-- Mobile Search -->
      <div v-if="showMobileSearch" class="md:hidden py-4 border-t border-gray-200">
        <div class="relative">
          <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher des produits..."
            class="w-full pl-10 pr-16 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @keydown.enter="performSearch"
          />
          <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-1">
            <RouterLink
              to="/search/image"
              class="p-1.5 text-gray-400 hover:text-primary-600 transition-colors rounded-lg hover:bg-primary-50"
              title="Recherche par image"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </RouterLink>
            <button
              v-if="searchQuery"
              @click="clearSearch"
              class="p-1.5 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
            >
              <XIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDashboardStore } from '@/stores/dashboard'
import {
  SearchIcon,
  BellIcon,
  XIcon,
  ChevronDownIcon,
  PackageIcon,
  UsersIcon,
  TagIcon,
  BarChart3Icon,
} from 'lucide-vue-next'

// Components
import NotificationDropdown from '@/components/layout/NotificationDropdown.vue'
import UserDropdown from '@/components/layout/UserDropdown.vue'
import InstallPWAButton from '@/components/ui/InstallPWAButton.vue'

// Fonctions de génération d'avatar dynamique
const generateDefaultAvatar = (name, id) => {
  const initials = getUserInitials(name)
  const color = generateUserColor(name || id?.toString() || 'User')
  
  const svg = `
    <svg width="40" height="40" xmlns="http://www.w3.org/2000/svg">
      <rect width="40" height="40" fill="${color}"/>
      <text x="50%" y="50%" text-anchor="middle" dy="0.35em" fill="white" font-family="Arial, sans-serif" font-size="16" font-weight="bold">
        ${initials}
      </text>
    </svg>
  `
  
  return 'data:image/svg+xml;base64,' + btoa(svg)
}

const getUserInitials = (name) => {
  if (!name) return '?'
  const cleanName = name.trim()
  const names = cleanName.split(' ')
  if (names.length === 1) {
    return names[0].charAt(0).toUpperCase()
  } else {
    return names[0].charAt(0).toUpperCase() + names[names.length - 1].charAt(0).toUpperCase()
  }
}

const generateUserColor = (name) => {
  if (!name) return '#6B7280'
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  const colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#06B6D4']
  return colors[Math.abs(hash) % colors.length]
}

// Stores
const authStore = useAuthStore()
const dashboardStore = useDashboardStore()
const router = useRouter()

// Reactive data
const searchQuery = ref('')
const showMobileSearch = ref(false)
const showNotifications = ref(false)
const showUserMenu = ref(false)
const showAdminMenu = ref(false)

// Computed
const isAuthenticated = computed(() => authStore.isAuthenticated)
const user = computed(() => authStore.user)
const unreadNotifications = computed(() => dashboardStore.unreadNotifications)

// Methods
const performSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({
      name: 'search',
      query: { q: searchQuery.value.trim() }
    })
    showMobileSearch.value = false
  }
}

const clearSearch = () => {
  searchQuery.value = ''
}

const handleAvatarError = (event) => {
  const dynamicAvatar = generateDefaultAvatar(user.value?.name, user.value?.id)
  if (event.target.src !== dynamicAvatar) {
    event.target.src = dynamicAvatar
  }
}

const logout = async () => {
  await authStore.logout()
}

// Close dropdowns when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.notification-dropdown')) {
    showNotifications.value = false
  }
  if (!event.target.closest('.user-dropdown')) {
    showUserMenu.value = false
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)

  // Load notification counts and wallet balance if authenticated
  if (isAuthenticated.value) {
    dashboardStore.fetchStats()
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.router-link-active {
  @apply text-primary-600 font-semibold;
}
</style>
