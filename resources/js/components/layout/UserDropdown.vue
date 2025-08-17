<template>
  <div class="user-dropdown absolute right-0 top-full mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
    <div class="px-4 py-3 border-b border-gray-100">
      <div class="flex items-center space-x-3">
        <img
          :src="user?.avatar || '/default-avatar.png'"
          :alt="user?.name"
          class="w-10 h-10 rounded-full object-cover"
        />
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-900 truncate">
            {{ user?.name }}
          </p>
          <p class="text-sm text-gray-600 truncate">
            {{ user?.email }}
          </p>
        </div>
      </div>
    </div>

    <nav class="py-2">
      <RouterLink
        to="/dashboard"
        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
        @click="$emit('close')"
      >
        <LayoutDashboardIcon class="w-4 h-4 mr-3" />
        Dashboard
      </RouterLink>
      
      <RouterLink
        to="/profile"
        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
        @click="$emit('close')"
      >
        <UserIcon class="w-4 h-4 mr-3" />
        Mon Profil
      </RouterLink>
      
      <RouterLink
        to="/my-products"
        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
        @click="$emit('close')"
      >
        <PackageIcon class="w-4 h-4 mr-3" />
        Mes Produits
      </RouterLink>
      
      <RouterLink
        to="/orders"
        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
        @click="$emit('close')"
      >
        <ShoppingBagIcon class="w-4 h-4 mr-3" />
        Mes Commandes
      </RouterLink>
      
      <RouterLink
        to="/products?filter=favorites"
        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
        @click="$emit('close')"
      >
        <HeartIcon class="w-4 h-4 mr-3" />
        Mes Favoris
      </RouterLink>
      
      <div class="border-t border-gray-100 my-2"></div>
      
      <RouterLink
        to="/products/create"
        class="flex items-center px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50"
        @click="$emit('close')"
      >
        <PlusIcon class="w-4 h-4 mr-3" />
        Vendre un article
      </RouterLink>
      

      
      <div class="border-t border-gray-100 my-2"></div>
      
      <RouterLink
        to="/settings"
        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
        @click="$emit('close')"
      >
        <SettingsIcon class="w-4 h-4 mr-3" />
        Paramètres
      </RouterLink>
      
      <button
        @click="logout"
        class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50"
      >
        <LogOutIcon class="w-4 h-4 mr-3" />
        Déconnexion
      </button>
    </nav>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import {
  LayoutDashboardIcon,
  UserIcon,
  PackageIcon,
  ShoppingBagIcon,
  HeartIcon,
  PlusIcon,
  SettingsIcon,
  LogOutIcon
} from 'lucide-vue-next'

const authStore = useAuthStore()

defineEmits(['close'])

// Computed
const user = computed(() => authStore.user)

// Methods
const logout = async () => {
  await authStore.logout()
}
</script>

