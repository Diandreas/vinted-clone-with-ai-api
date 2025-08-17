<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Products Section - Main Content -->
    <div class="py-8 sm:py-12 bg-gray-50">
      <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center sm:text-left mb-8 sm:mb-12">
          <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">Tous les Produits</h2>
          <p class="text-lg sm:text-xl text-gray-600">Découvrez des articles uniques à vendre</p>
        </div>



        <!-- Loading State -->
        <div v-if="loadingProducts" class="space-y-4">
          <ProductSkeleton v-for="i in 5" :key="i" />
        </div>
        
        <!-- Products Feed - Responsive TikTok Style -->
        <div v-else-if="products.length > 0" class="space-y-0 sm:space-y-6">
          <div 
            v-for="product in products" 
            :key="product.id"
            class="relative bg-black text-white h-screen sm:h-auto sm:min-h-[400px] flex flex-col mb-0 sm:mb-0 sm:rounded-xl sm:overflow-hidden sm:shadow-lg"
          >
            <!-- Product Image Container -->
            <div class="relative flex-1 bg-gray-900">
              <img 
                v-if="product.main_image"
                :src="product.main_image" 
                :alt="product.title"
                class="w-full h-full object-cover"
              />
              <div v-else class="flex items-center justify-center h-full">
                <ImageIcon class="w-24 h-24 text-gray-600" />
              </div>
              
              <!-- Status Badge -->
              <div class="absolute top-4 left-4">
                <span 
                  :class="getStatusBadgeClass(product.status)"
                  class="px-3 py-1 text-sm font-medium rounded-full"
                >
                  {{ getStatusText(product.status) }}
                </span>
              </div>

              <!-- Price Badge -->
              <div class="absolute top-4 right-4 bg-black bg-opacity-70 backdrop-blur-sm rounded-lg px-3 py-2">
                <span class="text-lg font-bold text-white">{{ formatPrice(product.price) }}</span>
                <span v-if="product.original_price && product.original_price !== product.price" class="text-sm text-gray-300 line-through ml-2">
                  {{ formatPrice(product.original_price) }}
                </span>
              </div>
            </div>
            
            <!-- Product Info & Actions - Responsive -->
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black via-black/80 to-transparent p-4 sm:relative sm:bg-white sm:text-gray-900 sm:p-4 sm:border-t sm:border-gray-200">
              <!-- Product Title & Description -->
              <div class="mb-4">
                <h3 class="text-lg font-semibold mb-2">{{ product.title }}</h3>
                <p v-if="product.description" class="text-sm text-gray-300 sm:text-gray-600 line-clamp-2">{{ product.description }}</p>
              </div>

              <!-- User Info -->
              <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                  <span class="text-white font-bold text-sm">{{ product.user?.name?.charAt(0)?.toUpperCase() || 'U' }}</span>
                </div>
                <div>
                  <div class="font-medium text-white sm:text-gray-900">{{ product.user?.name || 'Utilisateur' }}</div>
                  <div class="text-xs text-gray-400 sm:text-gray-500">{{ product.category?.name }}</div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <div class="flex items-center justify-center sm:justify-start space-x-6">
                  <!-- Like Button -->
                  <button 
                    @click="toggleLike(product)"
                    :disabled="!isAuthenticated"
                    class="flex flex-col items-center space-y-1 text-white sm:text-gray-600 hover:text-red-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed group"
                    :title="isAuthenticated ? 'Cliquer pour liker' : 'Connectez-vous pour liker'"
                  >
                    <HeartIcon 
                      :class="[
                        'w-6 h-6 sm:w-8 sm:h-8 transition-all duration-200',
                        product.is_liked ? 'fill-red-500 text-red-500 scale-110' : 'group-hover:scale-110'
                      ]"
                    />
                    <span class="text-xs">{{ product.likes_count || 0 }}</span>
                  </button>

                  <!-- Comment Button -->
                  <button class="flex flex-col items-center space-y-1 text-white sm:text-gray-600 hover:text-blue-400 transition-colors">
                    <MessageCircleIcon class="w-6 h-6 sm:w-8 sm:h-8" />
                    <span class="text-xs">{{ product.comments_count || 0 }}</span>
                  </button>

                  <!-- Share Button -->
                  <button class="flex flex-col items-center space-y-1 text-white sm:text-gray-600 hover:text-green-400 transition-colors">
                    <ShareIcon class="w-6 h-6 sm:w-8 sm:h-8" />
                    <span class="text-xs">Partager</span>
                  </button>
                </div>

                <!-- View Product Button -->
                <button 
                  @click="viewProduct(product)"
                  class="w-full sm:w-auto bg-white text-black px-6 py-3 sm:py-2 rounded-full font-semibold hover:bg-gray-100 transition-colors"
                >
                  Voir le produit
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- No Products -->
        <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 sm:p-12 text-center">
          <PackageIcon class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun produit trouvé</h3>
          <p class="mt-1 text-sm text-gray-500">Aucun produit ne correspond à vos critères de recherche.</p>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mt-6 sm:mt-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 sm:gap-0">
            <div class="flex items-center justify-center sm:justify-start space-x-2">
              <button
                @click="loadProducts(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Précédent
              </button>
              
              <span class="text-sm text-gray-700 px-2">
                Page {{ pagination.current_page }} sur {{ pagination.last_page }}
              </span>
              
              <button
                @click="loadProducts(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Suivant
              </button>
            </div>
            
            <div class="text-sm text-gray-700">
              {{ pagination.from }}-{{ pagination.to }} sur {{ pagination.total }} produits
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Live Streams Section -->
    <div class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Lives en Cours</h2>
            <p class="text-lg text-gray-600">Rejoignez les sessions de shopping en direct</p>
          </div>
          <RouterLink
            to="/lives"
            class="text-red-600 hover:text-red-700 font-semibold"
          >
            Voir tout →
          </RouterLink>
        </div>

        <div v-if="loadingLives" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <LiveSkeleton v-for="i in 6" :key="i" />
        </div>
        
        <div v-else-if="liveLives.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <RouterLink
            v-for="live in liveLives"
            :key="live.id"
            :to="`/lives/${live.id}`"
            class="block"
          >
            <LiveCard :live="live" />
          </RouterLink>
        </div>

        <div v-else class="text-center py-16">
          <RadioIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun live en cours</h3>
          <p class="text-gray-600">Revenez plus tard ou créez votre propre live</p>
          <RouterLink
            v-if="isAuthenticated"
            to="/lives/create"
            class="inline-block mt-4 bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors"
          >
            Créer un Live
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Categories Section -->
    <div class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Parcourir par Catégorie</h2>
          <p class="text-lg text-gray-600">Trouvez exactement ce que vous cherchez</p>
        </div>

        <div v-if="loadingCategories" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
          <CategorySkeleton v-for="i in 12" :key="i" />
        </div>
        
        <div v-else-if="categories.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
          <RouterLink
            v-for="category in categories"
            :key="category.id"
            :to="`/products?category=${category.id}`"
            class="block"
          >
            <CategoryCard :category="category" />
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="py-20 bg-indigo-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">
          Prêt à commencer ?
        </h2>
        <p class="text-xl text-indigo-100 mb-8 max-w-2xl mx-auto">
          Rejoignez des milliers d'utilisateurs qui font déjà confiance à notre plateforme
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <RouterLink
            v-if="!isAuthenticated"
            to="/register"
            class="bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition-colors"
          >
            S'inscrire Gratuitement
          </RouterLink>
          <RouterLink
            v-else
            to="/products/create"
            class="bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition-colors"
          >
            Vendre un Article
          </RouterLink>
          <RouterLink
            to="/products"
            class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-indigo-600 transition-colors"
          >
            Commencer à Acheter
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { debounce } from 'lodash'
import { TrendingUpIcon, RadioIcon, DownloadIcon, SearchIcon, EyeIcon, HeartIcon, ImageIcon, PackageIcon, MessageCircleIcon, ShareIcon } from 'lucide-vue-next'
import { formatPrice } from '@/utils/currency'

// Components
import ProductSkeleton from '@/components/skeletons/ProductSkeleton.vue'
import LiveCard from '@/components/lives/LiveCard.vue'
import LiveSkeleton from '@/components/skeletons/LiveSkeleton.vue'
import CategoryCard from '@/components/categories/CategoryCard.vue'
import CategorySkeleton from '@/components/skeletons/CategorySkeleton.vue'

const authStore = useAuthStore()
const router = useRouter()

// Reactive data
const products = ref([])
const liveLives = ref([])
const categories = ref([])
const loadingProducts = ref(true)
const loadingLives = ref(true)
const loadingCategories = ref(true)
const downloadingAPK = ref(false)

// Filters for products
const filters = ref({
  search: '',
  category: '',
  sort: 'created_at'
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0
})

// Computed
const isAuthenticated = computed(() => authStore.isAuthenticated)

// Methods
const downloadAPK = async () => {
  if (downloadingAPK.value) return
  
  downloadingAPK.value = true
  try {
    // Utiliser la route API pour le téléchargement
    const response = await window.axios.get('/download/app', {
      responseType: 'blob'
    })
    
    // Créer un blob et déclencher le téléchargement
    const blob = new Blob([response.data], { 
      type: 'application/vnd.android.package-archive' 
    })
    const url = window.URL.createObjectURL(blob)
    
    const link = document.createElement('a')
    link.href = url
    link.download = 'sellam-app.apk'
    link.style.display = 'none'
    
    document.body.appendChild(link)
    link.click()
    
    // Nettoyer
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
    
    console.log('Téléchargement de l\'APK démarré')
  } catch (error) {
    console.error('Erreur lors du téléchargement:', error)
    if (error.response?.status === 404) {
      alert('L\'APK n\'est pas encore disponible. Veuillez réessayer plus tard.')
    } else {
      alert('Erreur lors du téléchargement. Veuillez réessayer.')
    }
  } finally {
    downloadingAPK.value = false
  }
}

const loadProducts = async (page = 1) => {
  loadingProducts.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: pagination.value.per_page.toString()
    })
    
    if (filters.value.search) params.append('search', filters.value.search)
    if (filters.value.category) params.append('category_id', filters.value.category)
    if (filters.value.sort) params.append('sort', filters.value.sort)
    
    const response = await window.axios.get(`/products?${params}`)
    
    // L'API retourne {success: true, data: Array(10)}
    const productsData = response.data.data
    products.value = productsData || []
    
    // Pour la pagination, on utilise les données de base
    // car l'API retourne directement le tableau de produits
    pagination.value.current_page = 1
    pagination.value.last_page = 1
    pagination.value.total = productsData.length || 0
    pagination.value.from = 1
    pagination.value.to = productsData.length || 0
  } catch (error) {
    console.error('Erreur lors du chargement des produits:', error)
  } finally {
    loadingProducts.value = false
  }
}

const loadLiveLives = async () => {
  loadingLives.value = true
  try {
    const response = await window.axios.get('/lives', { 
      params: { status: 'live', limit: 6 } 
    })
    liveLives.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching live streams:', error)
  } finally {
    loadingLives.value = false
  }
}

const loadCategories = async () => {
  loadingCategories.value = true
  try {
    const response = await window.axios.get('/categories', { params: { limit: 12 } })
    categories.value = response.data.data || response.data
  } catch (error) {
    console.error('Error fetching categories:', error)
  } finally {
    loadingCategories.value = false
  }
}

const toggleLike = async (product) => {
  if (!isAuthenticated.value) {
    // Rediriger vers la page de connexion si non connecté
    router.push('/login')
    return
  }

  try {
    // Optimistic update - mettre à jour l'interface immédiatement
    const wasLiked = product.is_liked
    product.is_liked = !wasLiked
    product.likes_count = wasLiked ? (product.likes_count || 1) - 1 : (product.likes_count || 0) + 1

    // Appel API pour sauvegarder le like/dislike
    const response = await window.axios.post(`/products/${product.id}/like`)
    
    if (response.data.success) {
      // Mettre à jour avec les données de l'API
      product.is_liked = response.data.liked
      product.likes_count = response.data.likes_count
      
      // Notification de succès
      if (product.is_liked) {
        console.log('Produit liké avec succès')
      } else {
        console.log('Like supprimé avec succès')
      }
    } else {
      // En cas d'erreur, remettre l'état précédent
      product.is_liked = wasLiked
      product.likes_count = wasLiked ? (product.likes_count || 0) + 1 : (product.likes_count || 1) - 1
      console.error('Erreur lors du like:', response.data.message)
    }
  } catch (error) {
    console.error('Erreur lors du like:', error)
    
    // En cas d'erreur, remettre l'état précédent
    const wasLiked = !product.is_liked
    product.is_liked = wasLiked
    product.likes_count = wasLiked ? (product.likes_count || 0) + 1 : (product.likes_count || 1) - 1
    
    // Notification d'erreur
    alert('Erreur lors du like. Veuillez réessayer.')
  }
}

const debouncedSearch = debounce(() => {
  pagination.value.current_page = 1
  loadProducts()
}, 500)

const resetFilters = () => {
  filters.value.search = ''
  filters.value.category = ''
  filters.value.sort = 'created_at'
  pagination.value.current_page = 1
  loadProducts()
}

const viewProduct = (product) => {
  router.push(`/products/${product.id}`)
}



const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-500 text-white',
    draft: 'bg-gray-500 text-white',
    sold: 'bg-red-500 text-white',
    reserved: 'bg-yellow-500 text-white'
  }
  return classes[status] || classes.draft
}

const getStatusText = (status) => {
  const texts = {
    active: 'Disponible',
    draft: 'Brouillon',
    sold: 'Vendu',
    reserved: 'Réservé'
  }
  return texts[status] || 'Inconnu'
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadProducts(),
    loadLiveLives(),
    loadCategories()
  ])
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

