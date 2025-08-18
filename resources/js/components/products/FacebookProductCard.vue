<template>
  <div class="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-200 overflow-hidden">
    <!-- User Header -->
    <div class="flex items-center p-3 border-b border-gray-100">
      <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center mr-3">
        <span class="text-white font-bold text-sm">
          {{ product.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
        </span>
      </div>
      <div class="flex-1">
        <div class="font-semibold text-gray-900 text-sm">{{ product.user?.name || 'Utilisateur' }}</div>
        <div class="text-xs text-gray-500">{{ formatDate(product.created_at) }}</div>
      </div>

      <!-- Three Dots Menu -->
      <div class="relative">
        <button
          @click="toggleMenu"
          class="text-gray-400 hover:text-gray-600 p-1 transition-colors"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
          </svg>
        </button>

        <!-- Dropdown Menu -->
        <div
          v-if="showMenu"
          class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-10"
        >
          <div class="py-1">
            <button
              @click="shareProduct"
              class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
            >
              <ShareIcon class="w-4 h-4 mr-2" />
              Partager
            </button>
            <button
              @click="copyLink"
              class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              Copier le lien
            </button>
            <button
              @click="reportProduct"
              class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
              Signaler
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Product Image -->
    <div class="relative">
      <ProductImage
        :key="`facebook-product-${product.id}`"
        :src="product.main_image_url || product.main_image"
        :alt="product.title"
        :product-id="product.id"
        fallback="/placeholder-product.jpg"
        image-classes="w-full h-64 object-cover"
      />

      <!-- Status Badge -->
      <div class="absolute top-3 left-3">
        <span
          v-if="product.status === 'sold'"
          class="bg-gray-600 text-white text-xs font-medium px-3 py-1 rounded-full shadow-sm"
        >
          Vendu
        </span>
        <span
          v-else-if="product.status === 'reserved'"
          class="bg-gray-500 text-white text-xs font-medium px-3 py-1 rounded-full shadow-sm"
        >
          Réservé
        </span>
        <span
          v-else-if="product.is_boosted"
          class="bg-primary-500 text-white text-xs font-medium px-3 py-1 rounded-full shadow-sm"
        >
          Boosté
        </span>
      </div>

      <!-- Price Badge -->
      <div class="absolute top-3 right-3 bg-black bg-opacity-75 backdrop-blur-sm rounded-lg px-3 py-2">
        <span class="text-lg font-bold text-white">{{ formatPrice(product.price) }}</span>
        <span
          v-if="product.original_price && product.original_price > product.price"
          class="text-sm text-gray-300 line-through ml-2"
        >
          {{ formatPrice(product.original_price) }}
        </span>
      </div>
    </div>

    <!-- Product Info -->
    <div class="p-3">
      <h3 class="font-semibold text-gray-900 text-base mb-2 line-clamp-2">
        {{ product.title }}
      </h3>

      <p v-if="product.description" class="text-sm text-gray-600 mb-3 line-clamp-2">
        {{ product.description }}
      </p>

      <!-- Product Details -->
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-4 text-sm text-gray-500">
          <span v-if="product.brand" class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ product.brand.name }}
          </span>
          <span v-if="product.category" class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
            </svg>
            {{ product.category.name }}
          </span>
        </div>

        <div class="text-xs text-gray-400">
          {{ product.views_count || 0 }} vues
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-between pt-3 border-t border-gray-100">
        <div class="flex items-center space-x-6">
                     <!-- Like Button -->
           <button
             @click="toggleLike"
             :disabled="likingProduct"
             class="flex items-center space-x-2 text-gray-500 hover:text-primary-500 transition-colors disabled:opacity-50"
           >
             <HeartIcon
               class="w-5 h-5"
               :class="isLiked ? 'text-primary-500 fill-current' : ''"
             />
             <span class="text-sm font-medium">{{ product.likes_count || 0 }}</span>
           </button>

           <!-- Share Button -->
           <button
             @click="shareProduct"
             class="flex items-center space-x-2 text-gray-500 hover:text-primary-500 transition-colors"
           >
             <ShareIcon class="w-5 h-5" />
             <span class="text-sm font-medium">Partager</span>
           </button>
        </div>

                 <!-- View Product Button -->
         <button
           @click="viewProduct"
           class="bg-primary-500 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-600 transition-colors"
         >
           Voir
         </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { HeartIcon, ShareIcon } from '@heroicons/vue/24/outline'
import ProductImage from '../ui/ProductImage.vue'
import { formatPrice } from '../../utils/currency.js'

export default {
  name: 'FacebookProductCard',
  components: {
    HeartIcon,
    ShareIcon,
    ProductImage
  },
  props: {
    product: {
      type: Object,
      required: true
    },
    isLiked: {
      type: Boolean,
      default: false
    },
    likingProduct: {
      type: Boolean,
      default: false
    }
  },
  emits: ['like', 'view', 'notification'],
  setup() {
    const showMenu = ref(false)

    const toggleMenu = () => {
      showMenu.value = !showMenu.value
    }

    const closeMenu = () => {
      showMenu.value = false
    }

    // Fermer le menu quand on clique ailleurs
    const handleClickOutside = (event) => {
      if (!event.target.closest('.relative')) {
        closeMenu()
      }
    }

    // Ajouter l'écouteur d'événement global
    if (typeof window !== 'undefined') {
      document.addEventListener('click', handleClickOutside)
    }

    return {
      showMenu,
      toggleMenu,
      closeMenu
    }
  },
  methods: {
    formatPrice,
    formatDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      const now = new Date()
      const diffTime = Math.abs(now - date)
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))

      if (diffDays === 1) return 'Hier'
      if (diffDays < 7) return `Il y a ${diffDays} jours`
      if (diffDays < 30) return `Il y a ${Math.floor(diffDays / 7)} semaines`
      return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
    },
    toggleLike() {
      this.$emit('like', this.product)
    },
    viewProduct() {
      this.$emit('view', this.product)
    },
    async shareProduct() {
      const productUrl = `${window.location.origin}/products/${this.product.id}`
      const shareData = {
        title: this.product.title,
        text: `Découvrez ce produit : ${this.product.title}`,
        url: productUrl
      }

      try {
        // Essayer l'API Web Share native
        if (navigator.share && navigator.canShare(shareData)) {
          await navigator.share(shareData)
          this.$emit('notification', {
            type: 'success',
            message: 'Produit partagé avec succès !'
          })
        } else {
          // Fallback : copier le lien
          await this.copyLink()
        }
      } catch (error) {
        console.error('Erreur lors du partage:', error)
        // Fallback : copier le lien
        await this.copyLink()
      }
    },
    async copyLink() {
      const productUrl = `${window.location.origin}/products/${this.product.id}`

      try {
        await navigator.clipboard.writeText(productUrl)
        // Afficher une notification de succès
        this.$emit('notification', {
          type: 'success',
          message: 'Lien copié dans le presse-papiers !'
        })
      } catch (error) {
        console.error('Erreur lors de la copie:', error)
        // Fallback pour les navigateurs plus anciens
        const textArea = document.createElement('textarea')
        textArea.value = productUrl
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)

        this.$emit('notification', {
          type: 'success',
          message: 'Lien copié dans le presse-papiers !'
        })
      }
    },
    reportProduct() {
      // Implémenter la fonctionnalité de signalement
      this.$emit('notification', {
        type: 'info',
        message: 'Fonctionnalité de signalement à venir'
      })
      this.closeMenu()
    }
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
