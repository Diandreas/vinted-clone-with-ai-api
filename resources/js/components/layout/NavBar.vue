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
                  class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden md:block"
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

          <!-- Notifications -->
          <div class="relative user-menu notification-menu">
              <button
                @click.stop="showNotifications = !showNotifications"
                class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors"
              >
                <BellIcon class="w-6 h-6" />
                <span
                  v-if="unreadNotifications > 0"
                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
                >
                  {{ unreadNotifications }}
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
                @click.stop="showUserMenu = !showUserMenu"
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

      <!-- Mobile Admin Menu -->
      <div
        v-if="showAdminMenu && (authStore.isAdmin || authStore.hasPermission('dashboard:view'))"
        class="md:hidden py-3 border-t border-gray-200"
      >
        <div class="text-xs font-semibold text-gray-500 mb-2">Administration</div>
        <div class="grid gap-1">
          <RouterLink
            to="/admin/dashboard"
            class="flex items-center px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-100"
            :class="{ 'bg-gray-100': $route.name === 'admin-dashboard' }"
            @click="showAdminMenu = false"
          >
            <BarChart3Icon class="w-4 h-4 mr-3" />
            Dashboard
          </RouterLink>
          <RouterLink
            v-if="authStore.isAdmin || authStore.hasPermission('users:manage')"
            to="/admin/users"
            class="flex items-center px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-100"
            :class="{ 'bg-gray-100': $route.name === 'admin-users' }"
            @click="showAdminMenu = false"
          >
            <UsersIcon class="w-4 h-4 mr-3" />
            Gestion Utilisateurs
          </RouterLink>
          <RouterLink
            v-if="authStore.isAdmin || authStore.hasPermission('products:moderate')"
            to="/admin/products"
            class="flex items-center px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-100"
            :class="{ 'bg-gray-100': $route.name === 'admin-dashboard' }"
            @click="showAdminMenu = false"
          >
            <PackageIcon class="w-4 h-4 mr-3" />
            Gestion Produits
          </RouterLink>
          <RouterLink
            to="/admin/categories"
            class="flex items-center px-3 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-100"
            :class="{ 'bg-gray-100': $route.name === 'admin-categories' }"
            @click="showAdminMenu = false"
          >
            <TagIcon class="w-4 h-4 mr-3" />
            Gestion Catégories
          </RouterLink>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
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
  if (!event.target.closest('.notification-dropdown') && !event.target.closest('.notification-menu')) {
    showNotifications.value = false
  }
  if (!event.target.closest('.user-menu')) {
    showUserMenu.value = false
  }
}

// Lifecycle
let unreadIntervalId = null

const startUnreadPolling = () => {
  if (!unreadIntervalId) {
    unreadIntervalId = setInterval(() => {
      dashboardStore.fetchUnreadNotifications()
    }, 30000)
  }
}

const stopUnreadPolling = () => {
  if (unreadIntervalId) {
    clearInterval(unreadIntervalId)
    unreadIntervalId = null
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)

  // Load notification counts and wallet balance if authenticated
  if (isAuthenticated.value) {
    dashboardStore.fetchStats()
    dashboardStore.fetchUnreadNotifications()
    startUnreadPolling()
  }
})

watch(isAuthenticated, (isAuthed) => {
  if (isAuthed) {
    dashboardStore.fetchStats()
    dashboardStore.fetchUnreadNotifications()
    startUnreadPolling()
  } else {
    stopUnreadPolling()
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  stopUnreadPolling()
})
</script>

<style scoped>
.router-link-active {
  @apply text-primary-600 font-semibold;
}
</style>
