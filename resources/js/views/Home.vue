<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 overflow-hidden">
      <div class="absolute inset-0 bg-black opacity-10"></div>
      <div class="relative max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-16 sm:py-24">
        <div class="text-center">
          <h1 class="text-3xl sm:text-4xl md:text-6xl font-bold text-white mb-4 sm:mb-6">
            Vendez, Achetez, Streamez
          </h1>
          <p class="text-lg sm:text-xl md:text-2xl text-white opacity-90 mb-6 sm:mb-8 max-w-3xl mx-auto px-2">
            La nouvelle façon de faire du shopping. Découvrez des produits uniques, 
            participez à des lives shopping et rejoignez notre communauté.
          </p>
          <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center px-4">
            <RouterLink
              to="/products"
              class="bg-white text-indigo-600 px-6 sm:px-8 py-3 sm:py-4 rounded-lg text-base sm:text-lg font-semibold hover:bg-gray-50 transition-colors"
            >
              Explorer les Produits
            </RouterLink>
            <RouterLink
              to="/lives"
              class="bg-transparent border-2 border-white text-white px-6 sm:px-8 py-3 sm:py-4 rounded-lg text-base sm:text-lg font-semibold hover:bg-white hover:text-indigo-600 transition-colors"
            >
              Voir les Lives
            </RouterLink>
            <button
              @click="downloadAPK"
              :disabled="downloadingAPK"
              class="bg-green-600 text-white px-6 sm:px-8 py-3 sm:py-4 rounded-lg text-base sm:text-lg font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <DownloadIcon v-if="!downloadingAPK" class="w-5 h-5 sm:w-6 sm:h-6" />
              <div v-else class="w-5 h-5 sm:w-6 sm:h-6 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              <span class="hidden sm:inline">{{ downloadingAPK ? 'Téléchargement...' : 'Télécharger l\'App' }}</span>
              <span class="sm:hidden">{{ downloadingAPK ? '...' : 'App' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Products Section - Main Content -->
    <div class="py-12 sm:py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 sm:mb-12">
          <div class="mb-4 sm:mb-0">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">Tous les Produits</h2>
            <p class="text-base sm:text-lg text-gray-600">Découvrez des articles uniques à vendre</p>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6 sm:mb-8">
          <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
            <div class="flex-1 min-w-0">
              <div class="relative">
                <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4 sm:w-5 sm:h-5" />
                <input
                  v-model="filters.search"
                  type="text"
                  placeholder="Rechercher des produits..."
                  class="w-full pl-9 sm:pl-10 pr-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
                  @input="debouncedSearch"
                />
              </div>
            </div>
            
            <select 
              v-model="filters.category"
              @change="loadProducts"
              class="px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
            >
              <option value="">Toutes les catégories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>

            <select 
              v-model="filters.sort"
              @change="loadProducts"
              class="px-3 sm:px-4 py-2 sm:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
            >
              <option value="created_at">Plus récents</option>
              <option value="price">Prix croissant</option>
              <option value="-price">Prix décroissant</option>
              <option value="views_count">Plus vus</option>
              <option value="likes_count">Plus aimés</option>
            </select>

            <button
              @click="resetFilters"
              class="px-3 sm:px-4 py-2 sm:py-3 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm sm:text-base"
            >
              Réinitialiser
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loadingProducts" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
          <ProductSkeleton v-for="i in 12" :key="i" />
        </div>
        
        <!-- Products Grid -->
        <div v-else-if="products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
          <div 
            v-for="product in products" 
            :key="product.id"
            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group cursor-pointer"
            @click="viewProduct(product)"
          >
            <!-- Product Image -->
            <div class="relative aspect-square bg-gray-100">
              <img 
                v-if="product.main_image"
                :src="product.main_image" 
                :alt="product.title"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
              <div v-else class="flex items-center justify-center h-full">
                <ImageIcon class="w-8 h-8 sm:w-12 sm:h-12 text-gray-400" />
              </div>
              
              <!-- Status Badge -->
              <div class="absolute top-2 sm:top-3 left-2 sm:left-3">
                <span 
                  :class="getStatusBadgeClass(product.status)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ getStatusText(product.status) }}
                </span>
              </div>

              <!-- Price -->
              <div class="absolute bottom-2 sm:bottom-3 right-2 sm:right-3 bg-white/90 backdrop-blur-sm rounded-lg px-2 py-1">
                <span class="text-base sm:text-lg font-bold text-indigo-600">{{ formatPrice(product.price) }}</span>
                <span v-if="product.original_price && product.original_price !== product.price" class="text-xs sm:text-sm text-gray-500 line-through ml-1 sm:ml-2">
                  {{ formatPrice(product.original_price) }}
                </span>
              </div>
            </div>
            
            <!-- Product Info -->
            <div class="p-3 sm:p-4">
              <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 text-sm sm:text-base">{{ product.title }}</h3>
              <p v-if="product.description" class="text-xs sm:text-sm text-gray-600 mb-2 sm:mb-3 line-clamp-2">{{ product.description }}</p>
              
              <div class="flex items-center justify-between text-xs sm:text-sm text-gray-500 mb-2 sm:mb-3">
                <span class="truncate">{{ product.category?.name }}</span>
                <span class="truncate ml-2">{{ product.condition?.name }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <div class="flex items-center text-xs sm:text-sm text-gray-500">
                  <span class="flex items-center mr-2 sm:mr-3">
                    <EyeIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1" />
                    {{ product.views_count || 0 }}
                  </span>
                  <span class="flex items-center">
                    <HeartIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1" />
                    {{ product.likes_count || 0 }}
                  </span>
                </div>
                <div class="text-xs sm:text-sm text-gray-500 truncate ml-2">
                  Par {{ product.user?.name }}
                </div>
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
import { TrendingUpIcon, RadioIcon, DownloadIcon, SearchIcon, EyeIcon, HeartIcon, ImageIcon, PackageIcon } from 'lucide-vue-next'

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

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(price)
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    draft: 'bg-gray-100 text-gray-800',
    sold: 'bg-blue-100 text-blue-800',
    reserved: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || classes.draft
}

const getStatusText = (status) => {
  const texts = {
    active: 'Actif',
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

