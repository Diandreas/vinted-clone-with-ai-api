<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Products Section - Main Content -->
    <div class="py-8 sm:py-12 bg-gray-50">
      <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center sm:text-left mb-8 sm:mb-12">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">Découvrir</h2>
          <p class="text-lg sm:text-xl text-gray-600">Des articles uniques partagés par notre communauté</p>
        </div>



        <!-- Loading State -->
        <div v-if="loadingProducts" class="space-y-6">
          <FacebookProductSkeleton v-for="i in 3" :key="i" />
        </div>
        
        <!-- Products Feed - Facebook Style -->
        <FacebookProductGridLayout
          :products="products"
          :loading="loadingProducts"
          :loading-more="loadingMore"
          :liking-products="likingProducts"
          :pagination="pagination"
          :view-mode="filters.viewMode"
          @like="toggleLike"
          @view="viewProduct"
          @load-more="loadMoreProducts"
        />
      </div>
    </div>


  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'

import FacebookProductGridLayout from '../components/products/FacebookProductGridLayout.vue'
import FacebookProductSkeleton from '../components/skeletons/FacebookProductSkeleton.vue'

import { useAuthStore } from '../stores/auth.js'
import { useNotificationStore } from '../stores/notification.js'
import api from '../services/api.js'

export default {
  name: 'Home',
  components: {
    FacebookProductGridLayout,
    FacebookProductSkeleton,
    RouterLink
  },
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    const notificationStore = useNotificationStore()

    const products = ref([])
    const categories = ref([])
    const loadingProducts = ref(true)
    const loadingCategories = ref(true)
    const loadingMore = ref(false)
    const likingProducts = ref([])
    const filters = ref({
      viewMode: 'list'
    })
    const pagination = ref({
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0,
      from: 0,
      to: 0
    })

    const isAuthenticated = computed(() => authStore.isAuthenticated)

    const loadProducts = async (page = 1) => {
      try {
        loadingProducts.value = true
        const params = {
          page,
          per_page: 10,
          include: 'user,category,brand,images'
        }
        
        const response = await api.get('/products', { params })
        
        if (page === 1) {
          products.value = response.data.data
        } else {
          products.value = [...products.value, ...response.data.data]
        }
        
        pagination.value = {
          current_page: response.data.current_page,
          last_page: response.data.last_page,
          per_page: response.data.per_page,
          total: response.data.total,
          from: response.data.from,
          to: response.data.to
        }
      } catch (error) {
        console.error('Erreur lors du chargement des produits:', error)
        notificationStore.showError('Erreur lors du chargement des produits')
      } finally {
        loadingProducts.value = false
      }
    }

    const loadMoreProducts = async () => {
      if (pagination.value.current_page < pagination.value.last_page) {
        loadingMore.value = true
        await loadProducts(pagination.value.current_page + 1)
        loadingMore.value = false
      }
    }





    const toggleLike = async (product) => {
      if (!isAuthenticated.value) {
        notificationStore.showError('Connectez-vous pour liker ce produit')
        return
      }

      try {
        likingProducts.value.push(product.id)
        
        if (product.is_liked) {
          await api.delete(`/products/${product.id}/like`)
          product.is_liked = false
          product.likes_count = Math.max(0, (product.likes_count || 1) - 1)
        } else {
          await api.post(`/products/${product.id}/like`)
          product.is_liked = true
          product.likes_count = (product.likes_count || 0) + 1
        }
        
        notificationStore.showSuccess(
          product.is_liked ? 'Produit ajouté aux favoris' : 'Produit retiré des favoris'
        )
      } catch (error) {
        console.error('Erreur lors du like:', error)
        notificationStore.showError('Erreur lors du like du produit')
      } finally {
        const index = likingProducts.value.indexOf(product.id)
        if (index > -1) {
          likingProducts.value.splice(index, 1)
        }
      }
    }

    const viewProduct = (product) => {
      router.push(`/products/${product.id}`)
    }

    const handleViewModeChange = (viewMode) => {
      filters.value.viewMode = viewMode
    }

    onMounted(() => {
      loadProducts()
    })

    return {
      products,
      categories,
      loadingProducts,
      loadingMore,
      likingProducts,
      pagination,
      filters,
      isAuthenticated,
      toggleLike,
      viewProduct,
      loadMoreProducts,
      handleViewModeChange
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

