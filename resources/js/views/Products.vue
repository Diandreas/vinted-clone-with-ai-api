<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Mes Produits</h1>
          <p class="text-gray-600 mt-2">Gérez vos articles à vendre</p>
        </div>
        <RouterLink
          to="/products/create"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors"
        >
          <PlusIcon class="w-5 h-5 mr-2" />
          Nouveau produit
        </RouterLink>
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
                placeholder="Rechercher dans mes produits..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                @input="debouncedSearch"
              />
            </div>
          </div>
          
          <select 
            v-model="filters.status"
            @change="loadProducts"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Tous les statuts</option>
            <option value="active">Actifs</option>
            <option value="draft">Brouillons</option>
            <option value="sold">Vendus</option>
            <option value="reserved">Réservés</option>
          </select>

          <select 
            v-model="filters.category"
            @change="loadProducts"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option value="">Toutes les catégories</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
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
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-else-if="products.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div 
          v-for="product in products" 
          :key="product.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow group"
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

            <!-- Actions -->
            <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
              <div class="flex space-x-2">
                <button
                  @click="editProduct(product.id)"
                  class="p-2 bg-white rounded-full shadow-md hover:bg-gray-50 transition-colors"
                  title="Modifier"
                >
                  <PencilIcon class="w-4 h-4 text-gray-600" />
                </button>
                <button
                  @click="deleteProduct(product)"
                  class="p-2 bg-white rounded-full shadow-md hover:bg-gray-50 transition-colors"
                  title="Supprimer"
                >
                  <TrashIcon class="w-4 h-4 text-gray-700" />
                </button>
              </div>
            </div>
          </div>

          <!-- Product Info -->
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 cursor-pointer" @click="viewProduct(product.id)">
              {{ product.title }}
            </h3>
            
            <div class="flex items-center justify-between mb-2">
                                      <span class="text-lg font-bold text-primary-600">{{ product.price }} Fcfa</span>
                        <span v-if="product.original_price" class="text-sm text-gray-500 line-through">
                          {{ product.original_price }} Fcfa
                        </span>
            </div>

            <div class="flex items-center text-sm text-gray-500 mb-3">
              <CalendarIcon class="w-4 h-4 mr-1" />
              {{ formatDate(product.created_at) }}
            </div>

            <!-- Stats -->
            <div class="flex items-center justify-between text-sm text-gray-500">
              <div class="flex items-center space-x-3">
                <div class="flex items-center">
                  <EyeIcon class="w-4 h-4 mr-1" />
                  {{ product.views_count || 0 }}
                </div>
                <div class="flex items-center">
                  <HeartIcon class="w-4 h-4 mr-1" />
                  {{ product.likes_count || 0 }}
                </div>
              </div>
              <span v-if="product.category" class="text-xs bg-gray-100 px-2 py-1 rounded">
                {{ product.category.name }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <PackageIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun produit trouvé</h3>
        <p class="text-gray-600 mb-6">
          {{ filters.search || filters.status || filters.category 
            ? 'Aucun produit ne correspond à vos critères de recherche.' 
            : 'Commencez par ajouter votre premier produit à vendre.' 
          }}
        </p>
        <RouterLink
          to="/products/create"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors"
        >
          <PlusIcon class="w-5 h-5 mr-2" />
          Ajouter un produit
        </RouterLink>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="mt-8 flex justify-center">
        <nav class="flex items-center space-x-2">
          <button
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Précédent
          </button>
          
          <span v-for="page in visiblePages" :key="page">
            <button
              v-if="page !== '...'"
              @click="goToPage(page)"
              :class="[
                'px-3 py-2 border rounded-lg',
                page === pagination.current_page
                  ? 'bg-primary-600 text-white border-primary-600'
                  : 'border-gray-300 hover:bg-gray-50'
              ]"
            >
              {{ page }}
            </button>
            <span v-else class="px-3 py-2">...</span>
          </span>
          
          <button
            @click="goToPage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Suivant
          </button>
        </nav>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 max-w-md mx-4">
        <div class="flex items-center mb-4">
          <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mr-4">
            <TrashIcon class="w-6 h-6 text-gray-700" />
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Supprimer le produit</h3>
            <p class="text-gray-600">Cette action est irréversible.</p>
          </div>
        </div>
        
        <p class="text-gray-700 mb-6">
          Êtes-vous sûr de vouloir supprimer le produit "{{ productToDelete?.title }}" ?
        </p>
        
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Annuler
          </button>
          <button
            @click="confirmDelete"
            :disabled="deleting"
            class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <span v-if="deleting" class="mr-2">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            </span>
            {{ deleting ? 'Suppression...' : 'Supprimer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { 
  PlusIcon, 
  SearchIcon, 
  ImageIcon,
  PencilIcon,
  TrashIcon,
  PackageIcon,
  CalendarIcon,
  EyeIcon,
  HeartIcon
} from 'lucide-vue-next'
import { debounce } from 'lodash'

const router = useRouter()

// State
const loading = ref(false)
const deleting = ref(false)
const products = ref([])
const categories = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0
})

// Filters
const filters = reactive({
  search: '',
  status: '',
  category: '',
  page: 1
})

// Delete modal
const showDeleteModal = ref(false)
const productToDelete = ref(null)

// Load products
const loadProducts = async () => {
  loading.value = true
  
  try {
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.status) params.append('status', filters.status)
    if (filters.category) params.append('category_id', filters.category)
    params.append('page', filters.page)
    params.append('per_page', pagination.value.per_page)
    
    const response = await window.axios.get(`/products/my-products?${params}`)
    const data = response.data
    
    products.value = data.data || data.products || []
    
    // Handle pagination
    if (data.meta) {
      pagination.value = data.meta
    } else if (data.current_page) {
      pagination.value = {
        current_page: data.current_page,
        last_page: data.last_page,
        per_page: data.per_page,
        total: data.total
      }
    }
    
  } catch (error) {
    console.error('Erreur lors du chargement des produits:', error)
    products.value = []
  } finally {
    loading.value = false
  }
}

// Load categories
const loadCategories = async () => {
  try {
    const response = await window.axios.get('/categories')
    categories.value = response.data.data || response.data
  } catch (error) {
    console.error('Erreur lors du chargement des catégories:', error)
  }
}

// Debounced search
const debouncedSearch = debounce(() => {
  filters.page = 1
  loadProducts()
}, 500)

// Reset filters
const resetFilters = () => {
  filters.search = ''
  filters.status = ''
  filters.category = ''
  filters.page = 1
  loadProducts()
}

// Pagination
const visiblePages = computed(() => {
  const pages = []
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  
  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last)
    } else if (current >= last - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = last - 4; i <= last; i++) {
        pages.push(i)
      }
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last)
    }
  }
  
  return pages
})

const goToPage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page && page !== pagination.value.current_page) {
    filters.page = page
    loadProducts()
  }
}

// Actions
const viewProduct = (productId) => {
  router.push(`/products/${productId}`)
}

const editProduct = (productId) => {
  router.push(`/products/${productId}/edit`)
}

const deleteProduct = (product) => {
  productToDelete.value = product
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!productToDelete.value) return
  
  deleting.value = true
  
  try {
    await window.axios.delete(`/products/${productToDelete.value.id}`)
    
    // Remove from local array
    const index = products.value.findIndex(p => p.id === productToDelete.value.id)
    if (index !== -1) {
      products.value.splice(index, 1)
    }
    
    showDeleteModal.value = false
    productToDelete.value = null
    
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    alert('Erreur lors de la suppression du produit.')
  } finally {
    deleting.value = false
  }
}

// Utility functions
const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    draft: 'bg-gray-100 text-gray-800',
    sold: 'bg-primary-100 text-primary-800',
    reserved: 'bg-gray-100 text-gray-800',
    inactive: 'bg-gray-100 text-gray-900'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusText = (status) => {
  const texts = {
    active: 'Actif',
    draft: 'Brouillon',
    sold: 'Vendu',
    reserved: 'Réservé',
    inactive: 'Inactif'
  }
  return texts[status] || status
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Initialize
onMounted(() => {
  loadProducts()
  loadCategories()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.aspect-square {
  aspect-ratio: 1 / 1;
}
</style>
