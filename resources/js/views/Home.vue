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
          @share="shareProduct"
          @load-more="loadMoreProducts"
          @notification="handleNotification"
        />
        <div ref="infiniteScrollTrigger" class="h-1"></div>

        <!-- Notification Toast -->
        <div
          v-if="notification.show"
          class="fixed bottom-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border border-gray-200 p-4 transform transition-all duration-300"
          :class="notification.show ? 'translate-y-0 opacity-100' : 'translate-y-2 opacity-0'"
        >
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg
                v-if="notification.type === 'success'"
                class="w-5 h-5 text-green-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              <svg
                v-else-if="notification.type === 'error'"
                class="w-5 h-5 text-red-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              <svg
                v-else
                class="w-5 h-5 text-blue-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm font-medium text-gray-900">{{ notification.message }}</p>
            </div>
            <div class="ml-4 flex-shrink-0">
              <button
                @click="hideNotification"
                class="inline-flex text-gray-400 hover:text-gray-600 transition-colors"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>


  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'

import FacebookProductGridLayout from '../components/products/FacebookProductGridLayout.vue'
import FacebookProductSkeleton from '../components/skeletons/FacebookProductSkeleton.vue'

import { useAuthStore } from '../stores/auth.js'
import { useNotificationStore } from '../stores/notification.js'
import { useRealtime } from '../composables/useRealtime.js'
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
    const { subscribeToRealtime } = useRealtime()

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
    const hasMore = ref(true)

    const notification = ref({
      show: false,
      type: 'success',
      message: ''
    })
    const infiniteScrollTrigger = ref(null)
    let infiniteObserver = null

    const isAuthenticated = computed(() => authStore.isAuthenticated)

    const loadProducts = async (page = 1) => {
      try {
        if (page === 1) {
          loadingProducts.value = true
        }
        const params = {
          page,
          per_page: 20,
          sort: 'recent',
          include: 'user,category,brand,images'
        }

        const response = await api.get('/products', { params })
        const payload = response.data || {}
        const wrapped = payload.data || {}
        const data = Array.isArray(wrapped.data) ? wrapped.data : (Array.isArray(wrapped) ? wrapped : [])

        if (page === 1) {
          products.value = data
        } else {
          products.value = [...products.value, ...data]
        }

        const meta = wrapped.meta || payload.meta || {}
        const links = wrapped.links || payload.links || {}
        const currentPage = meta.current_page || wrapped.current_page || payload.current_page || page
        const lastPage = meta.last_page || wrapped.last_page || payload.last_page || currentPage
        const total = meta.total || wrapped.total || payload.total || 0
        const from = meta.from || wrapped.from || payload.from || 0
        const to = meta.to || wrapped.to || payload.to || 0
        const perPage = meta.per_page || wrapped.per_page || payload.per_page || params.per_page
        pagination.value = {
          current_page: currentPage,
          last_page: lastPage,
          per_page: perPage,
          total,
          from,
          to
        }
        hasMore.value = Boolean(links.next) || currentPage < lastPage
      } catch (error) {

        notificationStore.error('Erreur lors du chargement des produits')
      } finally {
        loadingProducts.value = false
      }
    }

    const loadMoreProducts = async () => {
      if (loadingMore.value || loadingProducts.value) return
      if (hasMore.value) {
        loadingMore.value = true
        await loadProducts(pagination.value.current_page + 1)
        loadingMore.value = false
      }
    }

    const refreshVisibleProductStats = async () => {
      if (loadingProducts.value || loadingMore.value || products.value.length === 0) return
      try {
        const params = {
          page: 1,
          per_page: 20,
          sort: 'recent',
          include: 'user,category,brand,images',
          no_cache: 1
        }
        const response = await api.get('/products', { params })
        const payload = response.data || {}
        const wrapped = payload.data || {}
        const data = Array.isArray(wrapped.data) ? wrapped.data : []
        if (!data.length) return

        const byId = new Map(data.map(p => [p.id, p]))
        products.value = products.value.map(p => {
          const fresh = byId.get(p.id)
          if (!fresh) return p
          return {
            ...p,
            likes_count: fresh.likes_count,
            favorites_count: fresh.favorites_count,
            comments_count: fresh.comments_count,
            views_count: fresh.views_count
          }
        })
      } catch (error) {
        // Silencieux: pas d'impact UI
      }
    }





    const toggleLike = async (product) => {
      if (!isAuthenticated.value) {
        notificationStore.error('Connectez-vous pour liker ce produit')
        return
      }

      try {
        likingProducts.value.push(product.id)

        // Utiliser toujours POST pour toggle like/unlike
        const response = await api.post(`/products/${product.id}/like`)

        if (response.data.success) {
          // Mettre à jour l'état du produit selon la réponse de l'API
          product.is_liked = response.data.liked
          product.is_liked_by_user = response.data.liked
          product.likes_count = response.data.likes_count
        }
      } catch (error) {

        notificationStore.error('Erreur lors du like du produit')
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

    const shareProduct = async (product) => {
      const productUrl = `${window.location.origin}/products/${product.id}`
      const shareData = {
        title: product.title,
        text: `Découvrez ce produit : ${product.title}`,
        url: productUrl
      }

      try {
        // Essayer l'API Web Share native
        if (navigator.share && navigator.canShare(shareData)) {
          await navigator.share(shareData)
          showNotification('Produit partagé avec succès !', 'success')
        } else {
          // Fallback : copier le lien
          await copyLink(productUrl)
        }
      } catch (error) {

        // Fallback : copier le lien
        await copyLink(productUrl)
      }
    }

    const copyLink = async (url) => {
      try {
        await navigator.clipboard.writeText(url)
        showNotification('Lien copié dans le presse-papiers !', 'success')
      } catch (error) {

        // Fallback pour les navigateurs plus anciens
        const textArea = document.createElement('textarea')
        textArea.value = url
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)

        showNotification('Lien copié dans le presse-papiers !', 'success')
      }
    }

    const showNotification = (message, type = 'success') => {
      notification.value = {
        show: true,
        type,
        message
      }

      // Masquer automatiquement après 3 secondes
      setTimeout(() => {
        hideNotification()
      }, 3000)
    }

    const hideNotification = () => {
      notification.value.show = false
    }

    const handleNotification = (event) => {
      showNotification(event.message, event.type)
    }

    onMounted(async () => {
      await loadProducts()
      if ('IntersectionObserver' in window) {
        infiniteObserver = new IntersectionObserver(
          (entries) => {
            if (entries[0]?.isIntersecting) {
              loadMoreProducts()
            }
          },
          { rootMargin: '200px 0px' }
        )
        if (infiniteScrollTrigger.value) {
          infiniteObserver.observe(infiniteScrollTrigger.value)
        }
      }

      // Auto-refresh likes stats without reload (faster poll)
      subscribeToRealtime('likes', refreshVisibleProductStats, 5000)
    })

    onUnmounted(() => {
      if (infiniteObserver) {
        infiniteObserver.disconnect()
        infiniteObserver = null
      }
    })

    return {
      products,
      categories,
      loadingProducts,
      loadingMore,
      likingProducts,
      pagination,
      hasMore,
      filters,
      notification,
      isAuthenticated,
      toggleLike,
      viewProduct,
      shareProduct,
      showNotification,
      hideNotification,
      loadMoreProducts,
      handleViewModeChange,
      handleNotification,
      infiniteScrollTrigger
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
