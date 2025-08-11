<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Tous les Produits</h1>
          <p class="text-gray-600 mt-2">Découvrez des articles uniques à vendre</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-wrap gap-4">
          <div class="flex-1 min-w-64">
            <div class="relative">
              <SearchIcon class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                v-model="filters.search"
                type="text"
                placeholder="Rechercher des produits..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                @input="debouncedSearch"
              />
            </div>
          </div>
          
          <select 
            v-model="filters.category"
            @change="loadProducts"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Toutes les catégories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>

          <select 
            v-model="filters.sort"
            @change="loadProducts"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="created_at">Plus récents</option>
            <option value="price">Prix croissant</option>
            <option value="-price">Prix décroissant</option>
            <option value="views_count">Plus vus</option>
            <option value="likes_count">Plus aimés</option>
          </select>

          <button
            @click="resetFilters"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Réinitialiser
          </button>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12">
        <div class="flex justify-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-else-if="products.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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
              <ImageIcon class="w-12 h-12 text-gray-400" />
            </div>
            
            <!-- Status Badge -->
            <div class="absolute top-3 left-3">
              <span 
                :class="getStatusBadgeClass(product.status)"
                class="px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ getStatusText(product.status) }}
              </span>
            </div>

            <!-- Price -->
            <div class="absolute bottom-3 right-3 bg-white/90 backdrop-blur-sm rounded-lg px-2 py-1">
              <span class="text-lg font-bold text-indigo-600">{{ formatPrice(product.price) }}</span>
              <span v-if="product.original_price && product.original_price !== product.price" class="text-sm text-gray-500 line-through ml-2">
                {{ formatPrice(product.original_price) }}
              </span>
            </div>
          </div>
          
          <!-- Product Info -->
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ product.title }}</h3>
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ product.description }}</p>
            
            <div class="flex items-center justify-between text-sm text-gray-500 mb-3">
              <span>{{ product.category?.name }}</span>
              <span>{{ product.condition?.name }}</span>
            </div>
            
            <div class="flex items-center justify-between">
              <div class="flex items-center text-sm text-gray-500">
                <span class="flex items-center mr-3">
                  <EyeIcon class="w-4 h-4 mr-1" />
                  {{ product.views_count }}
                </span>
                <span class="flex items-center">
                  <HeartIcon class="w-4 h-4 mr-1" />
                  {{ product.likes_count }}
                </span>
              </div>
              <div class="text-sm text-gray-500">
                Par {{ product.user?.name }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Products -->
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <PackageIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun produit trouvé</h3>
        <p class="mt-1 text-sm text-gray-500">Aucun produit ne correspond à vos critères de recherche.</p>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mt-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-2">
            <button
              @click="loadProducts(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Précédent
            </button>
            
            <span class="text-sm text-gray-700">
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
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { debounce } from 'lodash'
import {
  PackageIcon,
  SearchIcon,
  EyeIcon,
  HeartIcon,
  ImageIcon
} from 'lucide-vue-next'

const router = useRouter()

// State
const loading = ref(true)
const products = ref([])
const categories = ref([])

const filters = reactive({
  search: '',
  category: '',
  sort: 'created_at'
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0,
  from: 0,
  to: 0
})

// Methods
const loadProducts = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: pagination.per_page.toString()
    })
    
    if (filters.search) params.append('search', filters.search)
    if (filters.category) params.append('category_id', filters.category)
    if (filters.sort) params.append('sort', filters.sort)
    
    const response = await window.axios.get(`/products?${params}`)
    
    if (response.data.success) {
      const paginator = response.data.data
      products.value = paginator.data || []
      
      // Update pagination
      pagination.current_page = paginator.current_page || 1
      pagination.last_page = paginator.last_page || 1
      pagination.total = paginator.total || 0
      pagination.from = paginator.from || 0
      pagination.to = paginator.to || 0
    }
  } catch (error) {
    console.error('Erreur lors du chargement des produits:', error)
  } finally {
    loading.value = false
  }
}

const loadCategories = async () => {
  try {
    const response = await window.axios.get('/categories')
    categories.value = response.data.data || response.data
  } catch (error) {
    console.error('Erreur lors du chargement des catégories:', error)
  }
}

const debouncedSearch = debounce(() => {
  pagination.current_page = 1
  loadProducts()
}, 500)

const resetFilters = () => {
  filters.search = ''
  filters.category = ''
  filters.sort = 'created_at'
  pagination.current_page = 1
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
