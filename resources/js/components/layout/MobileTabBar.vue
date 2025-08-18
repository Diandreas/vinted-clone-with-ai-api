<template>
  <div class="md:hidden fixed bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-gray-200 shadow-xl">
    <!-- Safe area pour iPhone -->
    <div class="safe-area-inset-bottom">
      <div class="flex justify-around items-center py-2 px-4">
        <!-- Accueil -->
        <RouterLink 
          to="/"
          class="flex flex-col items-center justify-center p-2 min-w-0 flex-1 transform transition-all duration-150 hover:scale-105 active:scale-95"
          :class="{ 'text-primary-600': $route.name === 'home' || $route.path === '/' }"
        >
          <div class="relative">
            <svg class="w-6 h-6" :class="$route.name === 'home' || $route.path === '/' ? 'text-primary-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
          </div>
          <span class="text-xs mt-1 font-medium truncate" :class="$route.name === 'home' || $route.path === '/' ? 'text-primary-600' : 'text-gray-500'">
            Accueil
          </span>
        </RouterLink>

        <!-- Rechercher -->
        <RouterLink 
          to="/products"
          class="flex flex-col items-center justify-center p-2 min-w-0 flex-1 transform transition-all duration-150 hover:scale-105 active:scale-95"
          :class="{ 'text-primary-600': $route.name === 'products' || $route.name === 'search' }"
        >
          <div class="relative">
            <svg class="w-6 h-6" :class="$route.name === 'products' || $route.name === 'search' ? 'text-primary-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
          </div>
          <span class="text-xs mt-1 font-medium truncate" :class="$route.name === 'products' || $route.name === 'search' ? 'text-primary-600' : 'text-gray-500'">
            Rechercher
          </span>
        </RouterLink>

        <!-- Bouton Vendre (Central et proéminent) -->
        <RouterLink 
          to="/products/create"
          class="flex flex-col items-center justify-center p-1 min-w-0 flex-1 relative transform transition-all duration-150 hover:scale-105 active:scale-95"
        >
          <div class="relative">
            <!-- Cercle de fond avec animation et ombre -->
            <div class="w-14 h-14 bg-primary-500 rounded-full flex items-center justify-center shadow-medium hover:shadow-strong transform transition-all duration-150 hover:scale-110 active:scale-95 relative -mt-2">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
              </svg>
              <!-- Effet de brillance -->
              <div class="absolute inset-0 rounded-full bg-white opacity-20 animate-pulse"></div>
            </div>
          </div>
          <span class="text-xs mt-1 font-bold text-primary-600">
            Vendre
          </span>
        </RouterLink>

        <!-- Messages / Discussions -->
        <RouterLink 
          to="/discussions"
          class="flex flex-col items-center justify-center p-2 min-w-0 flex-1 transform transition-all duration-150 hover:scale-105 active:scale-95"
          :class="{ 'text-primary-600': $route.name === 'product-discussions' || $route.name?.includes('conversation') || $route.name === 'seller-conversations' }"
        >
          <div class="relative">
            <svg class="w-6 h-6" :class="$route.name === 'product-discussions' || $route.name?.includes('conversation') || $route.name === 'search' ? 'text-primary-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <!-- Badge pour les messages non lus -->
            <div v-if="unreadMessages > 0" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold shadow-md">
              {{ unreadMessages > 9 ? '9+' : unreadMessages }}
            </div>
          </div>
          <span class="text-xs mt-1 font-medium truncate" :class="$route.name === 'product-discussions' || $route.name?.includes('conversation') || $route.name === 'seller-conversations' ? 'text-primary-600' : 'text-gray-500'">
            Messages
          </span>
        </RouterLink>

        <!-- Profil -->
        <RouterLink 
          to="/profile"
          class="flex flex-col items-center justify-center p-2 min-w-0 flex-1 transform transition-all duration-150 hover:scale-105 active:scale-95"
          :class="{ 'text-primary-600': $route.name === 'profile' || $route.name === 'edit-profile' }"
          v-if="isAuthenticated"
        >
          <div class="relative">
            <div class="w-6 h-6 rounded-full overflow-hidden border-2 transition-all duration-150" :class="$route.name === 'profile' || $route.name === 'edit-profile' ? 'border-primary-600 shadow-md' : 'border-gray-300'">
              <img 
                v-if="user?.avatar" 
                :src="user.avatar" 
                :alt="user.name"
                class="w-full h-full object-cover"
              >
              <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center">
                <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
          </div>
          <span class="text-xs mt-1 font-medium truncate" :class="$route.name === 'profile' || $route.name === 'edit-profile' ? 'text-primary-600' : 'text-gray-500'">
            Profil
          </span>
        </RouterLink>

        <!-- Connexion pour les utilisateurs non connectés -->
        <RouterLink 
          to="/login"
          class="flex flex-col items-center justify-center p-2 min-w-0 flex-1 transform transition-all duration-200 hover:scale-105 active:scale-95"
          v-if="!isAuthenticated"
        >
          <div class="relative">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
          </div>
          <span class="text-xs mt-1 font-medium text-gray-500 truncate">
            Connexion
          </span>
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useDashboardStore } from '@/stores/dashboard'

const authStore = useAuthStore()
const dashboardStore = useDashboardStore()

// Reactive properties
const isAuthenticated = computed(() => authStore.isAuthenticated)
const user = computed(() => authStore.user)
const unreadMessages = computed(() => dashboardStore.unreadMessages || 0)
</script>

<style scoped>
.safe-area-inset-bottom {
  padding-bottom: env(safe-area-inset-bottom);
}

/* Animation smooth pour les boutons */
.flex-col {
  transition: all 0.2s ease-in-out;
}

/* Effet de pression pour le bouton vendre */
.bg-gradient-to-r:active {
  transform: scale(0.95);
}

/* Hover states pour desktop si nécessaire */
@media (hover: hover) {
  .flex-col:hover {
    opacity: 0.8;
  }
}
</style>
