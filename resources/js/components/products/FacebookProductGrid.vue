<template>
  <div class="space-y-6">
    <!-- Products Grid -->
    <div v-if="products.length > 0" class="space-y-6">
      <FacebookProductCard
        v-for="product in products" 
        :key="product.id"
        :product="product"
        :is-liked="product.is_liked"
        :liking-product="likingProducts.includes(product.id)"
        @like="handleLike"
        @view="handleView"
      />
    </div>

    <!-- Loading State -->
    <div v-else-if="loading" class="space-y-6">
      <FacebookProductSkeleton v-for="i in skeletonCount" :key="i" />
    </div>

    <!-- No Products -->
    <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 sm:p-12 text-center">
      <PackageIcon class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun produit trouvé</h3>
      <p class="mt-1 text-sm text-gray-500">Aucun produit ne correspond à vos critères de recherche.</p>
    </div>

    <!-- Load More Button -->
    <div v-if="showLoadMore && products.length > 0" class="text-center">
      <button
        @click="$emit('load-more')"
        :disabled="loadingMore"
        class="bg-primary-600 text-white px-8 py-3 rounded-lg font-medium hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
import { PackageIcon } from '@heroicons/vue/24/outline'
import FacebookProductCard from './FacebookProductCard.vue'
import FacebookProductSkeleton from '../skeletons/FacebookProductSkeleton.vue'

export default {
  name: 'FacebookProductGrid',
  components: {
    FacebookProductCard,
    FacebookProductSkeleton,
    PackageIcon
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
    }
  },
  computed: {
    showLoadMore() {
      return this.pagination.current_page < this.pagination.last_page
    }
  },
  emits: ['like', 'view', 'load-more'],
  methods: {
    handleLike(product) {
      this.$emit('like', product)
    },
    handleView(product) {
      this.$emit('view', product)
    }
  }
}
</script>
