<template>
  <div 
    class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200"
    :class="{
      'opacity-60 grayscale': isProductUnavailable,
      'cursor-not-allowed': isProductUnavailable
    }"
  >
    <!-- Product Image -->
    <div class="relative aspect-square overflow-hidden rounded-t-lg">
      <ProductImage
        :key="`product-card-${product.id}`"
        :src="product.main_image_url || product.main_image"
        :alt="product.title"
        :product-id="product.id"
        fallback="/placeholder-product.jpg"
        image-classes="w-full h-full object-cover"
        :class="{ 'grayscale': isProductUnavailable }"
      />

      <!-- Status Badge - Ultra Compact mobile -->
      <div class="absolute top-0.5 sm:top-1 left-0.5 sm:left-1">
        <span
          v-if="product.status === 'sold'"
          class="bg-red-100 text-red-800 text-xs font-medium px-1 py-0.5 sm:px-1.5 sm:py-0.5 rounded-full"
        >
          Vendu
        </span>
        <span
          v-else-if="product.status === 'removed'"
          class="bg-gray-100 text-gray-800 text-xs font-medium px-1 py-0.5 sm:px-1.5 sm:py-0.5 rounded-full"
        >
          Supprimé
        </span>
        <span
          v-else-if="product.status === 'reserved'"
          class="bg-yellow-100 text-yellow-800 text-xs font-medium px-1 py-0.5 sm:px-1.5 sm:py-0.5 rounded-full"
        >
          Réservé
        </span>
        <span
          v-else-if="product.is_boosted"
          class="bg-primary-100 text-primary-800 text-xs font-medium px-1 py-0.5 sm:px-1.5 sm:py-0.5 rounded-full"
        >
          Boosté
        </span>
      </div>

      <!-- Unavailable Overlay -->
      <div 
        v-if="isProductUnavailable" 
        class="absolute inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center"
      >
        <div class="text-center text-white">
          <div class="text-lg font-semibold mb-1">
            {{ getUnavailableText() }}
          </div>
          <div class="text-sm opacity-90">
            {{ getUnavailableDescription() }}
          </div>
        </div>
      </div>

      <!-- Action Buttons - Ultra Compact mobile -->
      <div v-if="showActions" class="absolute top-0.5 sm:top-1 right-0.5 sm:right-1 flex space-x-0.5 sm:space-x-1">
        <button
          v-if="canEditProduct"
          @click.stop="$emit('edit', product)"
          class="bg-white bg-opacity-90 hover:bg-opacity-100 p-0.5 sm:p-1 rounded-full shadow-sm transition-all"
          title="Modifier (disponible 30 min après création)"
        >
          <EditIcon class="w-3 h-3 sm:w-4 sm:h-4 text-gray-600" />
        </button>
        <button
          @click.stop="$emit('delete', product)"
          class="bg-white bg-opacity-90 hover:bg-opacity-100 p-0.5 sm:p-1 rounded-full shadow-sm transition-all"
          title="Supprimer"
        >
          <TrashIcon class="w-3 h-3 sm:w-4 sm:h-4 text-red-600" />
        </button>
      </div>

      <!-- Quick Actions - Ultra Compact mobile -->
      <div v-else class="absolute top-0.5 sm:top-1 right-0.5 sm:right-1 flex space-x-0.5 sm:space-x-1">
        <button
          @click.stop="toggleLike"
          :disabled="likingProduct"
          class="bg-white bg-opacity-90 hover:bg-opacity-100 p-0.5 sm:p-1 rounded-full shadow-sm transition-all"
        >
          <HeartIcon
            class="w-3 h-3 sm:w-4 sm:h-4"
            :class="isLiked ? 'text-primary-500 fill-current' : 'text-gray-600'"
          />
        </button>
        <button
          @click.stop="toggleFavorite"
          :disabled="favoritingProduct"
          class="bg-white bg-opacity-90 hover:bg-opacity-100 p-0.5 sm:p-1 rounded-full shadow-sm transition-all"
        >
          <BookmarkIcon
            class="w-3 h-3 sm:w-4 sm:h-4"
            :class="isFavorite ? 'text-primary-500 fill-current' : 'text-gray-600'"
          />
        </button>
      </div>
    </div>

    <!-- Product Info - Ultra Compact mobile -->
    <div class="p-2 sm:p-3 lg:p-4">
      <div class="flex items-start justify-between mb-1 sm:mb-1.5 lg:mb-2">
        <h3 class="font-semibold text-gray-900 text-xs sm:text-sm line-clamp-2 flex-1">
          {{ product.title }}
        </h3>
        <div class="ml-1 sm:ml-1.5 lg:ml-2">
          <div class="text-sm sm:text-base lg:text-lg font-bold text-gray-900">
            {{ formatPrice(product.price) }}
          </div>
          <div
            v-if="product.original_price && product.original_price > product.price"
            class="text-xs sm:text-sm text-gray-500 line-through"
          >
            {{ formatPrice(product.original_price) }}
          </div>
        </div>
      </div>

      <!-- Product Details -->
      <div class="space-y-0.5 sm:space-y-1 mb-1.5 sm:mb-2 lg:mb-3">
        <div v-if="product.brand" class="text-xs sm:text-sm text-gray-600">
          {{ product.brand.name }}
        </div>
        <div v-if="product.size" class="text-xs sm:text-sm text-gray-600">
          Taille: {{ product.size }}
        </div>
        <div v-if="product.condition" class="text-xs sm:text-sm text-gray-600">
          État: {{ product.condition.name }}
        </div>
      </div>

      <!-- Seller Info -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-1 sm:space-x-1.5 lg:space-x-2">
          <img
            :src="product.user?.avatar || generateDefaultAvatar(product.user?.name, product.user?.id)"
            :alt="product.user?.name"
            class="w-4 h-4 sm:w-5 sm:h-5 lg:w-6 lg:h-6 rounded-full object-cover"
            @error="handleAvatarError"
          />
          <VerifiedSellerName 
            :seller-name="product.user?.name" 
            :is-verified="product.user?.is_verified"
            text-class="text-xs sm:text-sm text-gray-600"
          />
        </div>

        <!-- Stats -->
        <div class="flex items-center space-x-1.5 sm:space-x-2 lg:space-x-3 text-xs text-gray-500">
          <div class="flex items-center space-x-0.5 sm:space-x-1">
            <EyeIcon class="w-2 h-2 sm:w-2.5 sm:h-2.5 lg:w-3 lg:h-3" />
            <span>{{ product.views_count || 0 }}</span>
          </div>
          <div class="flex items-center space-x-0.5 sm:space-x-1">
            <HeartIcon class="w-2 h-2 sm:w-2.5 sm:h-2.5 lg:w-3 lg:h-3" />
            <span>{{ product.likes_count || 0 }}</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons for Own Products -->
      <div v-if="showActions" class="mt-2 sm:mt-3 lg:mt-4 flex space-x-1 sm:space-x-1.5 lg:space-x-2">
        <button
          @click.stop="$emit('view', product)"
          class="flex-1 bg-blue-50 text-blue-700 text-xs sm:text-sm py-1 sm:py-1.5 lg:py-2 px-1.5 sm:px-2 lg:px-3 rounded-lg hover:bg-blue-100 transition-colors font-medium"
        >
          <EyeIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1 inline" />
          Voir
        </button>
        <button
          @click.stop="$emit('share', product)"
          class="flex-1 bg-gray-50 text-gray-600 text-xs sm:text-sm py-1 sm:py-1.5 lg:py-2 px-1.5 sm:px-2 lg:px-3 rounded-lg hover:bg-gray-100 transition-colors"
        >
          Partager
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { formatPrice } from '@/utils/currency'
import ProductImage from '@/components/ui/ProductImage.vue'
import VerifiedSellerName from '@/components/ui/VerifiedSellerName.vue'
import {
  HeartIcon,
  BookmarkIcon,
  EyeIcon,
  EditIcon,
  TrashIcon
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

const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  showActions: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['like', 'favorite', 'edit', 'delete', 'boost', 'share', 'view'])

const authStore = useAuthStore()

// Reactive state
const likingProduct = ref(false)
const favoritingProduct = ref(false)

// Computed
const isLiked = computed(() => props.product.is_liked_by_user)
const isFavorite = computed(() => props.product.is_favorited_by_user)

// Check if product is unavailable (sold, removed, or disabled)
const isProductUnavailable = computed(() => {
  return ['sold', 'removed'].includes(props.product.status)
})

// Check if product can be edited (only within 30 minutes of creation)
const canEditProduct = computed(() => {
  if (!props.product.created_at) return false
  
  const createdAt = new Date(props.product.created_at)
  const now = new Date()
  const diffInMinutes = (now - createdAt) / (1000 * 60)
  
  return diffInMinutes <= 30
})

// Methods

const getUnavailableText = () => {
  switch (props.product.status) {
    case 'sold':
      return 'Vendu'
    case 'removed':
      return 'Supprimé'
    default:
      return 'Indisponible'
  }
}

const getUnavailableDescription = () => {
  switch (props.product.status) {
    case 'sold':
      return 'Ce produit a été vendu'
    case 'removed':
      return 'Ce produit a été supprimé'
    default:
      return 'Ce produit n\'est plus disponible'
  }
}

const handleImageError = (event) => {
  event.target.src = '/placeholder-product.jpg'
}

const handleAvatarError = (event) => {
  const dynamicAvatar = generateDefaultAvatar(props.product.user?.name, props.product.user?.id)
  if (event.target.src !== dynamicAvatar) {
    event.target.src = dynamicAvatar
  }
}

const toggleLike = async () => {
  if (!authStore.isAuthenticated) {
    // Redirect to login or show modal
    return
  }

  likingProduct.value = true
  try {
    const response = await window.axios.post(`/products/${props.product.id}/like`)

    if (response.data.success) {
      // Mettre à jour l'état local du produit
      props.product.is_liked_by_user = response.data.liked
      props.product.likes_count = response.data.likes_count

      // Émettre l'événement pour notifier le parent
      emit('like', props.product)
    }
  } catch (error) {
    console.error('Erreur lors du like/unlike:', error)
    // Optionnel : afficher un message d'erreur
  } finally {
    likingProduct.value = false
  }
}

const toggleFavorite = async () => {
  if (!authStore.isAuthenticated) {
    // Redirect to login or show modal
    return
  }

  favoritingProduct.value = true
  try {
    const response = await window.axios.post(`/products/${props.product.id}/favorite`)

    if (response.data.success) {
      // Mettre à jour l'état local du produit
      props.product.is_favorited_by_user = response.data.favorited
      props.product.favorites_count = response.data.favorites_count

      // Émettre l'événement pour notifier le parent
      emit('favorite', props.product)
    }
  } catch (error) {
    console.error('Erreur lors du favorite/unfavorite:', error)
    // Optionnel : afficher un message d'erreur
  } finally {
    favoritingProduct.value = false
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

