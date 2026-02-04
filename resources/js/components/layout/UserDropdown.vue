<template>
  <div class="user-dropdown absolute right-0 top-full mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50" @click.stop>
    <div class="px-4 py-3 border-b border-gray-100">
      <div class="flex items-center space-x-3">
        <img
          :src="user?.avatar || generateDefaultAvatar(user?.name, user?.id)"
          :alt="user?.name"
          class="w-10 h-10 rounded-full object-cover"
          @error="handleAvatarError"
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
        class="flex items-center px-4 py-2 text-sm text-primary-600 hover:bg-primary-50"
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
        class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
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

const authStore = useAuthStore()

defineEmits(['close'])

// Computed
const user = computed(() => authStore.user)

// Methods
const handleAvatarError = (event) => {
  const dynamicAvatar = generateDefaultAvatar(user.value?.name, user.value?.id)
  if (event.target.src !== dynamicAvatar) {
    event.target.src = dynamicAvatar
  }
}

const logout = async () => {
  await authStore.logout()
}
</script>
