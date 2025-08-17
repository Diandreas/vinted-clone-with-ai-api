<template>
  <div class="space-y-6">
    <!-- Grid Layout -->
    <div v-if="viewMode === 'grid' && products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="product in products"
        :key="product.id"
        class="bg-white rounded-lg border border-gray-200 hover:shadow-lg transition-all duration-200 overflow-hidden"
      >
        <!-- Product Image -->
        <div class="relative">
          <ProductImage
            :key="`grid-product-${product.id}`"
            :src="product.main_image_url || product.main_image"
            :alt="product.title"
            :product-id="product.id"
            fallback="/placeholder-product.jpg"
            image-classes="w-full h-48 object-cover"
          />
          
          <!-- Status Badge -->
          <div class="absolute top-2 left-2">
            <span
              v-if="product.status === 'sold'"
              class="bg-red-500 text-white text-xs font-medium px-2 py-1 rounded-full shadow-sm"
            >
              Vendu
            </span>
            <span
              v-else-if="product.status === 'reserved'"
              class="bg-yellow-500 text-white text-xs font-medium px-2 py-1 rounded-full shadow-sm"
            >
              Réservé
            </span>
            <span
              v-else-if="product.is_boosted"
              class="bg-indigo-500 text-white text-xs font-medium px-2 py-1 rounded-full shadow-sm"
            >
              Boosté
            </span>
          </div>

          <!-- Price Badge -->
          <div class="absolute top-2 right-2 bg-black bg-opacity-75 backdrop-blur-sm rounded-lg px-2 py-1">
            <span class="text-sm font-bold text-white">{{ formatPrice(product.price) }}</span>
          </div>
        </div>

        <!-- Product Info -->
        <div class="p-3">
          <h3 class="font-semibold text-gray-900 text-sm mb-2 line-clamp-2">
            {{ product.title }}
          </h3>
          
          <!-- User Info -->
          <div class="flex items-center space-x-2 mb-3">
            <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
              <span class="text-white font-bold text-xs">
                {{ product.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
              </span>
            </div>
            <span class="text-xs text-gray-600">{{ product.user?.name || 'Utilisateur' }}</span>
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center justify-between pt-2 border-t border-gray-100">
            <div class="flex items-center space-x-4">
              <button 
                @click="$emit('like', product)"
                :disabled="likingProducts.includes(product.id)"
                class="flex items-center space-x-1 text-gray-500 hover:text-red-500 transition-colors disabled:opacity-50"
              >
                <HeartIcon 
                  class="w-4 h-4"
                  :class="product.is_liked ? 'text-red-500 fill-current' : ''"
                />
                <span class="text-xs">{{ product.likes_count || 0 }}</span>
              </button>

              <button class="flex items-center space-x-1 text-gray-500 hover:text-blue-500 transition-colors">
                <ChatBubbleLeftIcon class="w-4 h-4" />
                <span class="text-xs">{{ product.comments_count || 0 }}</span>
              </button>
            </div>

            <button 
              @click="$emit('view', product)"
              class="text-blue-600 hover:text-blue-700 text-sm font-medium"
            >
              Voir
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- List Layout (using existing FacebookProductCard) -->
    <div v-else-if="viewMode === 'list'">
      <FacebookProductCard
        v-for="product in products" 
        :key="product.id"
        :product="product"
        :is-liked="product.is_liked"
        :liking-product="likingProducts.includes(product.id)"
        @like="$emit('like', product)"
        @view="$emit('view', product)"
      />
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="space-y-6">
      <FacebookProductSkeleton v-for="i in skeletonCount" :key="i" />
    </div>

    <!-- No Products -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 sm:p-12 text-center">
      <CubeIcon class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun produit trouvé</h3>
      <p class="mt-1 text-sm text-gray-500">Aucun produit ne correspond à vos critères de recherche.</p>
    </div>

    <!-- Load More Button -->
    <div v-if="showLoadMore && products.length > 0" class="text-center">
      <button
        @click="$emit('load-more')"
        :disabled="loadingMore"
        class="bg-blue-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="loadingMore">Chargement...</span>
        <span v-else>Charger plus de produits</span>
      </button>
    </div>

    <!-- Pagination Info -->
    <div v-if="pagination.total > 0" class="text-center text-sm text-gray-500">
      {{ pagination.from }}-{{ pagination.to }} sur {{ pagination.total }} produits
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { CubeIcon } from '@heroicons/vue/24/outline'
import { HeartIcon, ChatBubbleLeftIcon } from '@heroicons/vue/24/outline'
import FacebookProductCard from './FacebookProductCard.vue'
import FacebookProductSkeleton from '../skeletons/FacebookProductSkeleton.vue'
import ProductImage from '../ui/ProductImage.vue'
import { formatPrice } from '../../utils/currency.js'

export default {
  name: 'FacebookProductGridLayout',
  components: {
    FacebookProductCard,
    FacebookProductSkeleton,
    ProductImage,
    CubeIcon,
    HeartIcon,
    ChatBubbleLeftIcon
  },
  props: {
    products: {
      type: Array,
      default: () => []
    },
    loading: {
      type: Boolean,
      default: false
    },
    loadingMore: {
      type: Boolean,
      default: false
    },
    likingProducts: {
      type: Array,
      default: () => []
    },
    pagination: {
      type: Object,
      default: () => ({
        current_page: 1,
        last_page: 1,
        total: 0,
        from: 0,
        to: 0
      })
    },
    skeletonCount: {
      type: Number,
      default: 3
    },
    viewMode: {
      type: String,
      default: 'list'
    }
  },
  computed: {
    showLoadMore() {
      return this.pagination.current_page < this.pagination.last_page
    }
  },
  emits: ['like', 'view', 'load-more'],
  methods: {
    formatPrice
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
