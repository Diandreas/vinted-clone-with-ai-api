<template>
  <div 
    :key="`product-${product.id}`"
    class="group relative bg-gray-900 rounded-xl overflow-hidden cursor-pointer transform transition-all duration-300 hover:scale-105 hover:shadow-2xl"
    @click="goToProduct"
  >
    <!-- Product Image -->
    <div class="relative aspect-square overflow-hidden">
      <img
        :key="`img-${product.id}`"
        :src="imageSrc"
        :alt="product.title"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
        @error="onImageError"
        loading="lazy"
      />
      
      <!-- Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      
      <!-- Status Badge -->
      <div class="absolute top-3 left-3">
        <span
          v-if="product.status === 'sold'"
          class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg"
        >
          VENDU
        </span>
        <span
          v-else-if="product.status === 'reserved'"
          class="bg-yellow-500 text-black text-xs font-bold px-3 py-1 rounded-full shadow-lg"
        >
          RÉSERVÉ
        </span>
        <span
          v-else-if="product.is_boosted"
          class="bg-gradient-to-r from-purple-500 to-pink-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg"
        >
          BOOSTÉ
        </span>
        <span
          v-else-if="product.status === 'draft'"
          class="bg-gray-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg"
        >
          BROUILLON
        </span>
      </div>

      <!-- Price Badge -->
      <div class="absolute bottom-3 right-3">
        <div class="bg-black/80 backdrop-blur-sm rounded-full px-3 py-2 text-white font-bold text-sm">
          {{ formattedPrice }}
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
        <div class="flex flex-col space-y-2">
          <button
            @click.stop="toggleLike"
            :disabled="likingProduct"
            class="bg-black/80 backdrop-blur-sm hover:bg-red-500 p-2 rounded-full shadow-lg transition-all duration-200 transform hover:scale-110"
          >
            <HeartIcon
              class="w-4 h-4 text-white"
              :class="isLiked ? 'text-red-500 fill-current' : ''"
            />
          </button>
          <button
            @click.stop="toggleFavorite"
            :disabled="favoritingProduct"
            class="bg-black/80 backdrop-blur-sm hover:bg-yellow-500 p-2 rounded-full shadow-lg transition-all duration-200 transform hover:scale-110"
          >
            <BookmarkIcon
              class="w-4 h-4 text-white"
              :class="isFavorite ? 'text-yellow-500 fill-current' : ''"
            />
          </button>
          <button
            @click.stop="shareProduct"
            class="bg-black/80 backdrop-blur-sm hover:bg-blue-500 p-2 rounded-full shadow-lg transition-all duration-200 transform hover:scale-110"
          >
            <ShareIcon class="w-4 h-4 text-white" />
          </button>
        </div>
      </div>

      <!-- View Count -->
      <div class="absolute bottom-3 left-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <div class="bg-black/80 backdrop-blur-sm rounded-full px-3 py-1 text-white text-xs font-medium flex items-center space-x-1">
          <EyeIcon class="w-3 h-3" />
          <span>{{ formattedViews }}</span>
        </div>
      </div>
    </div>

    <!-- Product Info -->
    <div class="p-4">
      <h3 class="font-bold text-white text-sm line-clamp-2 mb-2 group-hover:text-red-400 transition-colors duration-200">
        {{ product.title }}
      </h3>
      
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <div class="w-6 h-6 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold">
            {{ getUserInitials(product.user?.name) }}
          </div>
          <span class="text-gray-400 text-xs">{{ product.user?.name }}</span>
        </div>
        
        <div class="flex items-center space-x-2">
          <span class="text-gray-400 text-xs">{{ formattedDate }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  HeartIcon,
  BookmarkIcon,
  ShareIcon,
  EyeIcon
} from 'lucide-vue-next'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const router = useRouter()
const authStore = useAuthStore()

// State
const likingProduct = ref(false)
const favoritingProduct = ref(false)

// Computed properties mémorisées
const imageSrc = computed(() => {
  return props.product.main_image_url || props.product.main_image || '/placeholder-product.jpg'
})

const formattedPrice = computed(() => {
          if (!props.product.price) return 'Fcfa 0.00'
        return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XAF'
      }).format(props.product.price)
})

const formattedViews = computed(() => {
  const num = props.product.views_count || 0
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  } else if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num.toString()
})

const formattedDate = computed(() => {
  if (!props.product.created_at) return ''
  const date = new Date(props.product.created_at)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) return 'Aujourd\'hui'
  if (diffDays === 2) return 'Hier'
  if (diffDays <= 7) return `Il y a ${diffDays - 1} jours`
  if (diffDays <= 30) return `Il y a ${Math.floor(diffDays / 7)} semaines`
  if (diffDays <= 365) return `Il y a ${Math.floor(diffDays / 30)} mois`
  return `Il y a ${Math.floor(diffDays / 365)} ans`
})

const isLiked = computed(() => props.product.is_liked || false)
const isFavorite = computed(() => props.product.is_favorited || false)

// Methods
function getUserInitials(fullName) {
  if (!fullName) return '?'
  const names = fullName.trim().split(' ')
  if (names.length === 1) {
    return names[0].substring(0, 2).toUpperCase()
  } else {
    return names.slice(0, 2).map(name => name.charAt(0).toUpperCase()).join('')
  }
}

function goToProduct() {
  router.push(`/products/${props.product.id}`)
}

function onImageError(event) {
  // Éviter les rechargements en boucle
  if (event.target.src !== '/placeholder-product.jpg') {
    event.target.src = '/placeholder-product.jpg'
  }
}

async function toggleLike() {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
    return
  }

  if (likingProduct.value) return
  
  likingProduct.value = true
  try {
    const response = await window.axios.post(`/products/${props.product.id}/like`)
    props.product.is_liked = response.data.liked
    props.product.likes_count = response.data.likes_count
  } catch (error) {
    console.error('Error toggling like:', error)
  } finally {
    likingProduct.value = false
  }
}

async function toggleFavorite() {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
    return
  }

  if (favoritingProduct.value) return
  
  favoritingProduct.value = true
  try {
    const response = await window.axios.post(`/products/${props.product.id}/favorite`)
    props.product.is_favorited = response.data.favorited
    props.product.favorites_count = response.data.favorites_count
  } catch (error) {
    console.error('Error toggling favorite:', error)
  } finally {
    favoritingProduct.value = false
  }
}

function shareProduct() {
  if (navigator.share) {
    navigator.share({
      title: props.product.title,
      text: `Découvrez ${props.product.title} sur notre plateforme !`,
      url: `${window.location.origin}/products/${props.product.id}`
    })
  } else {
    // Fallback: copy to clipboard
    const url = `${window.location.origin}/products/${props.product.id}`
    navigator.clipboard.writeText(url).then(() => {
      // Show toast or alert
      alert('Lien copié dans le presse-papiers !')
    })
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

/* Custom animations */
@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
  }
  50% {
    box-shadow: 0 0 30px rgba(239, 68, 68, 0.6);
  }
}

.group:hover {
  animation: pulse-glow 2s ease-in-out infinite;
}
</style>
