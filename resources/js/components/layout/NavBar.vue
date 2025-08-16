<template>
  <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo and Brand -->
        <div class="flex items-center space-x-8">
          <RouterLink to="/" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-lg">V</span>
            </div>
            <span class="text-xl font-bold text-gray-900">Linkea</span>
          </RouterLink>

          <!-- Main Navigation -->
          <div class="hidden md:flex items-center space-x-6">
            <RouterLink
              to="/products"
              class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors"
              :class="{ 'text-indigo-600': $route.name === 'products' }"
            >
              Produits
            </RouterLink>
            <RouterLink
              to="/lives"
              class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors flex items-center space-x-1"
              :class="{ 'text-red-600': $route.name === 'lives' }"
            >
              <RadioIcon class="w-4 h-4" />
              <span>Lives</span>
            </RouterLink>
            <RouterLink
              to="/stories"
              class="text-gray-600 hover:text-gray-900 px-3 py-2 text-sm font-medium transition-colors"
              :class="{ 'text-purple-600': $route.name === 'stories' }"
            >
              Stories
            </RouterLink>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="hidden md:flex flex-1 max-w-lg mx-8">
          <div class="relative w-full">
            <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Rechercher des produits..."
              class="w-full pl-10 pr-12 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              @keydown.enter="performSearch"
            />
            <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center space-x-1">
              <RouterLink
                to="/search/image"
                class="p-1 text-gray-400 hover:text-indigo-600 transition-colors"
                title="Recherche par image"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </RouterLink>
              <button
                v-if="searchQuery"
                @click="clearSearch"
                class="p-1 text-gray-400 hover:text-gray-600"
              >
                <XIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Right Side Actions -->
        <div class="flex items-center space-x-4">
          <!-- Mobile Search Toggle -->
          <button
            @click="showMobileSearch = !showMobileSearch"
            class="md:hidden p-2 text-gray-400 hover:text-gray-600"
          >
            <SearchIcon class="w-5 h-5" />
          </button>

          <!-- Authenticated User Actions -->
          <template v-if="isAuthenticated">
            <!-- Wallet Balance -->
            <RouterLink
              to="/wallet"
              class="hidden md:flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-600 hover:text-indigo-600 hover:bg-gray-50 rounded-lg transition-colors"
            >
              <WalletIcon class="w-4 h-4" />
              <span class="font-semibold">{{ walletBalance }}</span>
            </RouterLink>

            <!-- Admin Dropdown -->
            <div v-if="authStore.isAdmin || authStore.hasPermission('dashboard:view')" class="relative hidden md:block">
              <button
                @click="showAdminMenu = !showAdminMenu"
                class="flex items-center space-x-1 px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors"
                :class="{ 'text-indigo-600': $route.name?.includes('admin') }"
              >
                <PackageIcon class="w-4 h-4" />
                <span>Admin</span>
                <ChevronDownIcon class="w-3 h-3" />
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

            <!-- Messages -->
            <RouterLink
              to="/messages"
              class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors"
            >
              <MessageCircleIcon class="w-6 h-6" />
              <span
                v-if="unreadMessages > 0"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center"
              >
                {{ unreadMessages > 9 ? '9+' : unreadMessages }}
              </span>
            </RouterLink>

            <!-- Notifications -->
            <div class="relative">
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

            <!-- User Menu -->
            <div class="relative">
              <button
                @click="showUserMenu = !showUserMenu"
                class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <img
                  :src="user?.avatar || '/default-avatar.png'"
                  :alt="user?.name"
                  class="w-8 h-8 rounded-full object-cover"
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
              class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors"
            >
              S'inscrire
            </RouterLink>
          </template>

          <!-- Mobile Menu Toggle -->
          <button
            @click="showMobileMenu = !showMobileMenu"
            class="md:hidden p-2 text-gray-400 hover:text-gray-600"
          >
            <MenuIcon v-if="!showMobileMenu" class="w-6 h-6" />
            <XIcon v-else class="w-6 h-6" />
          </button>
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
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            @keydown.enter="performSearch"
          />
        </div>
      </div>

      <!-- Mobile Menu -->
      <div v-if="showMobileMenu" class="md:hidden py-4 border-t border-gray-200">
        <div class="space-y-2">
          <RouterLink
            to="/products"
            class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg"
            @click="showMobileMenu = false"
          >
            Produits
          </RouterLink>
          <RouterLink
            to="/lives"
            class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg"
            @click="showMobileMenu = false"
          >
            Lives
          </RouterLink>
          <RouterLink
            to="/stories"
            class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg"
            @click="showMobileMenu = false"
          >
            Stories
          </RouterLink>

          <template v-if="isAuthenticated">
            <div class="border-t border-gray-200 pt-2 mt-2">
              <RouterLink
                to="/wallet"
                class="flex items-center justify-between px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg"
                @click="showMobileMenu = false"
              >
                <div class="flex items-center space-x-2">
                  <WalletIcon class="w-4 h-4" />
                  <span>Mon Portefeuille</span>
                </div>
                <span class="font-semibold text-indigo-600">{{ walletBalance }}</span>
              </RouterLink>
              <RouterLink
                to="/dashboard"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg"
                @click="showMobileMenu = false"
              >
                Dashboard
              </RouterLink>
              <RouterLink
                to="/profile"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg"
                @click="showMobileMenu = false"
              >
                Mon Profil
              </RouterLink>
              <RouterLink
                to="/orders"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg"
                @click="showMobileMenu = false"
              >
                Mes Commandes
              </RouterLink>
              <RouterLink
                to="/my-products"
                class="block px-3 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg"
                @click="showMobileMenu = false"
              >
                Mes Produits
              </RouterLink>
              <button
                @click="logout"
                class="block w-full text-left px-3 py-2 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg"
              >
                Déconnexion
              </button>
            </div>
          </template>
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
import { useWalletStore } from '@/stores/wallet'
import {
  SearchIcon,
  BellIcon,
  MessageCircleIcon,
  MenuIcon,
  XIcon,
  ChevronDownIcon,
  RadioIcon,
  PackageIcon,
  UsersIcon,
  TagIcon,
  BarChart3Icon,
  WalletIcon
} from 'lucide-vue-next'

// Components
import NotificationDropdown from '@/components/layout/NotificationDropdown.vue'
import UserDropdown from '@/components/layout/UserDropdown.vue'

// Stores
const authStore = useAuthStore()
const dashboardStore = useDashboardStore()
const walletStore = useWalletStore()
const router = useRouter()

// Reactive data
const searchQuery = ref('')
const showMobileSearch = ref(false)
const showMobileMenu = ref(false)
const showNotifications = ref(false)
const showUserMenu = ref(false)
const showAdminMenu = ref(false)

// Computed
const isAuthenticated = computed(() => authStore.isAuthenticated)
const user = computed(() => authStore.user)
const unreadMessages = computed(() => dashboardStore.unreadMessages)
const unreadNotifications = computed(() => dashboardStore.unreadNotifications)
const walletBalance = computed(() => walletStore.balanceFormatted)

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

const logout = async () => {
  showMobileMenu.value = false
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
    walletStore.fetchBalance()
  }
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.router-link-active {
  @apply text-indigo-600 font-semibold;
}
</style>
