<template>
  <div
    :key="`product-${product.id}`"
    class="group relative bg-white rounded-2xl overflow-hidden cursor-pointer shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5"
    @click="goToProduct"
  >
    <!-- Image -->
    <div class="relative aspect-square overflow-hidden bg-gray-100">
      <img
        :key="`img-${product.id}`"
        :src="imageSrc"
        :alt="product.title"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
        @error="onImageError"
        loading="lazy"
      />

      <!-- Gradient hover -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

      <!-- Badges statut -->
      <div class="absolute top-2 left-2">
        <span v-if="product.status === 'sold'"     class="bg-gray-800/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">VENDU</span>
        <span v-else-if="product.status === 'reserved'" class="bg-orange-500/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">RÉSERVÉ</span>
        <span v-else-if="product.is_boosted"       class="bg-green-500/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">⚡ BOOSTÉ</span>
        <span v-else-if="product.status === 'draft'" class="bg-gray-400/90 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">BROUILLON</span>
      </div>

      <!-- Prix -->
      <div class="absolute bottom-2 left-2 right-2 flex justify-between items-end">
        <span class="bg-white/95 backdrop-blur-sm text-gray-900 font-black text-xs px-2.5 py-1 rounded-full shadow-sm">
          {{ formattedPrice }}
        </span>

        <!-- Actions rapides (hover) -->
        <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-all duration-200 translate-y-1 group-hover:translate-y-0">
          <button @click.stop="toggleLike" :disabled="likingProduct"
            class="w-7 h-7 bg-white/95 backdrop-blur-sm rounded-full flex items-center justify-center shadow-md hover:scale-110 transition-transform">
            <HeartIcon class="w-3.5 h-3.5" :class="isLiked ? 'text-red-500 fill-red-500' : 'text-gray-600'" />
          </button>
          <button @click.stop="shareProduct"
            class="w-7 h-7 bg-white/95 backdrop-blur-sm rounded-full flex items-center justify-center shadow-md hover:scale-110 transition-transform">
            <ShareIcon class="w-3.5 h-3.5 text-gray-600" />
          </button>
        </div>
      </div>
    </div>

    <!-- Infos produit -->
    <div class="px-3 py-2.5">
      <h3 class="font-bold text-gray-900 text-sm leading-snug line-clamp-2 mb-1.5">
        {{ product.title }}
      </h3>
      <span class="text-[11px] text-gray-400 font-medium">{{ formattedDate }}</span>
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
  return props.product.images?.[0]?.thumbnail_url || props.product.main_image_url || props.product.main_image || '/images/placeholder-product.png'
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

const formattedDate = computed(() => timeAgo(props.product.created_at))

function timeAgo(dateString) {
  if (!dateString) return ''
  // Si pas de timezone info, on interprète comme UTC (Laravel stocke en UTC par défaut)
  let raw = dateString
  if (!raw.includes('Z') && !raw.includes('+') && !/[+-]\d{2}:\d{2}/.test(raw)) {
    raw = raw.replace(' ', 'T') + 'Z'
  }
  const date = new Date(raw)
  const diffMs = Math.max(0, Date.now() - date.getTime())
  const diffSecs  = Math.floor(diffMs / 1000)
  const diffMins  = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays  = Math.floor(diffMs / 86400000)
  if (diffSecs  <  60)  return 'À l\'instant'
  if (diffMins  <  60)  return `Il y a ${diffMins} min`
  if (diffHours <  24)  return `Il y a ${diffHours}h`
  if (diffDays  === 1)  return 'Hier'
  if (diffDays  <   7)  return `Il y a ${diffDays} jours`
  if (diffDays  <  30)  return `Il y a ${Math.floor(diffDays / 7)} sem.`
  if (diffDays  < 365)  return `Il y a ${Math.floor(diffDays / 30)} mois`
  const years = Math.floor(diffDays / 365)
  return `Il y a ${years} an${years > 1 ? 's' : ''}`
}

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
  if (event.target.src !== '/images/placeholder-product.png') {
    event.target.src = '/images/placeholder-product.png'
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
</style>
