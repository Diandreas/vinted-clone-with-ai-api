<template>
  <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
    <!-- Product Image -->
    <div class="relative aspect-square overflow-hidden rounded-t-lg">
      <ProductImage
        :key="`product-card-${product.id}`"
        :src="product.main_image_url || product.main_image"
        :alt="product.title"
        :product-id="product.id"
        fallback="/placeholder-product.jpg"
        image-classes="w-full h-full object-cover"
      />

      <!-- Status Badge -->
      <div class="absolute top-2 left-2">
        <span
          v-if="product.status === 'sold'"
          class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full"
        >
          Vendu
        </span>
        <span
          v-else-if="product.status === 'reserved'"
          class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full"
        >
          Réservé
        </span>
        <span
          v-else-if="product.is_boosted"
          class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2 py-1 rounded-full"
        >
          Boosté
        </span>
      </div>

      <!-- Action Buttons -->
      <div v-if="showActions" class="absolute top-2 right-2 flex space-x-1">
        <button
          @click.stop="$emit('edit', product)"
          class="bg-white bg-opacity-90 hover:bg-opacity-100 p-2 rounded-full shadow-sm transition-all"
        >
          <EditIcon class="w-4 h-4 text-gray-600" />
        </button>
        <button
          @click.stop="$emit('delete', product)"
          class="bg-white bg-opacity-90 hover:bg-opacity-100 p-2 rounded-full shadow-sm transition-all"
        >
          <TrashIcon class="w-4 h-4 text-red-600" />
        </button>
      </div>

      <!-- Quick Actions -->
      <div v-else class="absolute top-2 right-2 flex space-x-1">
        <button
          @click.stop="toggleLike"
          :disabled="likingProduct"
          class="bg-white bg-opacity-90 hover:bg-opacity-100 p-2 rounded-full shadow-sm transition-all"
        >
          <HeartIcon
            class="w-4 h-4"
            :class="isLiked ? 'text-red-500 fill-current' : 'text-gray-600'"
          />
        </button>
        <button
          @click.stop="toggleFavorite"
          :disabled="favoritingProduct"
          class="bg-white bg-opacity-90 hover:bg-opacity-100 p-2 rounded-full shadow-sm transition-all"
        >
          <BookmarkIcon
            class="w-4 h-4"
            :class="isFavorite ? 'text-yellow-500 fill-current' : 'text-gray-600'"
          />
        </button>
      </div>
    </div>

    <!-- Product Info -->
    <div class="p-4">
      <div class="flex items-start justify-between mb-2">
        <h3 class="font-semibold text-gray-900 text-sm line-clamp-2 flex-1">
          {{ product.title }}
        </h3>
        <div class="ml-2">
          <div class="text-lg font-bold text-gray-900">
            {{ formatPrice(product.price) }}
          </div>
          <div
            v-if="product.original_price && product.original_price > product.price"
            class="text-sm text-gray-500 line-through"
          >
            {{ formatPrice(product.original_price) }}
          </div>
        </div>
      </div>

      <!-- Product Details -->
      <div class="space-y-1 mb-3">
        <div v-if="product.brand" class="text-sm text-gray-600">
          {{ product.brand.name }}
        </div>
        <div v-if="product.size" class="text-sm text-gray-600">
          Taille: {{ product.size }}
        </div>
        <div v-if="product.condition" class="text-sm text-gray-600">
          État: {{ product.condition.name }}
        </div>
      </div>

      <!-- Seller Info -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <img
            :src="product.user?.avatar || '/default-avatar.png'"
            :alt="product.user?.name"
            class="w-6 h-6 rounded-full object-cover"
            @error="handleAvatarError"
          />
          <span class="text-sm text-gray-600">{{ product.user?.name }}</span>
          <div v-if="product.user?.is_verified" class="text-blue-500">
            <CheckCircleIcon class="w-4 h-4" />
          </div>
        </div>

        <!-- Stats -->
        <div class="flex items-center space-x-3 text-xs text-gray-500">
          <div class="flex items-center space-x-1">
            <EyeIcon class="w-3 h-3" />
            <span>{{ product.views_count || 0 }}</span>
          </div>
          <div class="flex items-center space-x-1">
            <HeartIcon class="w-3 h-3" />
            <span>{{ product.likes_count || 0 }}</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons for Own Products -->
      <div v-if="showActions" class="mt-4 flex space-x-2">
        <button
          v-if="!product.is_boosted && product.status === 'active'"
          @click.stop="$emit('boost', product)"
          class="flex-1 bg-indigo-50 text-indigo-600 text-sm py-2 px-3 rounded-lg hover:bg-indigo-100 transition-colors"
        >
          Booster
        </button>
        <button
          @click.stop="$emit('share', product)"
          class="flex-1 bg-gray-50 text-gray-600 text-sm py-2 px-3 rounded-lg hover:bg-gray-100 transition-colors"
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
import {
  HeartIcon,
  BookmarkIcon,
  EyeIcon,
  EditIcon,
  TrashIcon,
  CheckCircleIcon
} from 'lucide-vue-next'

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

const emit = defineEmits(['like', 'favorite', 'edit', 'delete', 'boost', 'share'])

const authStore = useAuthStore()

// Reactive state
const likingProduct = ref(false)
const favoritingProduct = ref(false)

// Computed
const isLiked = computed(() => props.product.is_liked_by_user)
const isFavorite = computed(() => props.product.is_favorited_by_user)

// Methods
<<<<<<< Updated upstream
=======
const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
                            currency: 'XAF'
  }).format(price)
}
>>>>>>> Stashed changes

const handleImageError = (event) => {
  event.target.src = '/placeholder-product.jpg'
}

const handleAvatarError = (event) => {
  event.target.src = '/default-avatar.png'
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

