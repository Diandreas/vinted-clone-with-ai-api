<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Mes Produits</h1>
            <p class="text-gray-600 mt-1">Gérez vos articles en vente</p>
          </div>
          <div class="flex space-x-3">
            <RouterLink
              to="/products/create"
              class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              <span>Ajouter un produit</span>
            </RouterLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nom du produit..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              @input="debouncedSearch"
            />
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              @change="loadProducts"
            >
              <option value="">Tous les statuts</option>
              <option value="active">Actif</option>
              <option value="draft">Brouillon</option>
              <option value="sold">Vendu</option>
              <option value="reserved">Réservé</option>
            </select>
          </div>

          <!-- Category Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catégorie</label>
            <select
              v-model="filters.category"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              @change="loadProducts"
            >
              <option value="">Toutes les catégories</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
          </div>

          <!-- Actions -->
          <div class="flex items-end space-x-2">
            <button
              @click="resetFilters"
              class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Réinitialiser
            </button>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Chargement de vos produits...</p>
      </div>

      <div v-else-if="products.length === 0" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun produit</h3>
        <p class="mt-1 text-sm text-gray-500">Commencez par ajouter votre premier produit.</p>
        <div class="mt-6">
          <RouterLink
            to="/products/create"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Ajouter un produit
          </RouterLink>
        </div>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div
          v-for="product in products"
          :key="product.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
        >
          <!-- Product Image -->
          <div class="aspect-square bg-gray-100 relative">
            <img
              v-if="product.main_image_url"
              :src="product.main_image_url"
              :alt="product.title"
              class="w-full h-full object-cover"
            />
            <div v-else class="flex items-center justify-center h-full">
              <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
            </div>

            <!-- Status Badge -->
            <div class="absolute top-2 left-2">
              <span
                :class="getStatusBadgeClass(product.status)"
                class="px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ getStatusText(product.status) }}
              </span>
            </div>

            <!-- Quick Actions -->
            <div class="absolute top-2 right-2 flex space-x-1">
              <RouterLink
                :to="`/products/${product.id}/edit`"
                class="bg-white bg-opacity-90 hover:bg-opacity-100 p-2 rounded-full shadow-sm transition-all"
                title="Modifier"
              >
                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
              </RouterLink>
              
              <button
                @click="confirmDelete(product)"
                class="bg-white bg-opacity-90 hover:bg-opacity-100 p-2 rounded-full shadow-sm transition-all"
                title="Supprimer"
              >
                <svg class="w-4 h-4 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>

          <!-- Product Info -->
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ product.title }}</h3>
            
            <div class="flex items-center justify-between mb-2">
              <span class="text-lg font-bold text-primary-600">{{ formatPrice(product.price) }}</span>
              <span class="text-sm text-gray-500">{{ formatDate(product.created_at) }}</span>
            </div>

            <div class="flex items-center justify-between text-sm text-gray-500">
              <span>{{ product.category?.name || 'Sans catégorie' }}</span>
              <div class="flex items-center space-x-2">
                <span class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                  {{ product.views_count || 0 }}
                </span>
                <span class="flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                  </svg>
                  {{ product.likes_count || 0 }}
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-4 flex space-x-2">
              <RouterLink
                :to="`/products/${product.id}`"
                class="flex-1 bg-gray-100 text-gray-700 px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-200 transition-colors text-center"
              >
                Voir
              </RouterLink>
              
              <RouterLink
                :to="`/products/${product.id}/edit`"
                class="flex-1 bg-primary-100 text-primary-700 px-3 py-2 rounded-md text-sm font-medium hover:bg-primary-200 transition-colors text-center"
              >
                Modifier
              </RouterLink>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="mt-8 flex justify-center">
        <nav class="flex items-center space-x-2">
          <button
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Précédent
          </button>
          
          <span class="px-3 py-2 text-sm text-gray-700">
            Page {{ pagination.current_page }} sur {{ pagination.last_page }}
          </span>
          
          <button
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Suivant
          </button>
        </nav>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
            <svg class="h-6 w-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">Confirmer la suppression</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Êtes-vous sûr de vouloir supprimer "{{ productToDelete?.title }}" ? Cette action est irréversible.
            </p>
          </div>
          <div class="flex justify-center space-x-4 mt-4">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-400 transition-colors"
            >
              Annuler
            </button>
            <button
              @click="deleteProduct"
              :disabled="deleting"
              class="px-4 py-2 bg-gray-700 text-white rounded-md text-sm font-medium hover:bg-gray-800 disabled:opacity-50 transition-colors"
            >
              {{ deleting ? 'Suppression...' : 'Supprimer' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
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
const changePage = (page) => {
  filters.page = page
  loadProducts()
}

// Delete product
const confirmDelete = (product) => {
  productToDelete.value = product
  showDeleteModal.value = true
}

const deleteProduct = async () => {
  if (!productToDelete.value) return
  
  deleting.value = true
  
  try {
    await window.axios.delete(`/products/${productToDelete.value.id}`)
    
    // Remove from list
    products.value = products.value.filter(p => p.id !== productToDelete.value.id)
    
    // Close modal
    showDeleteModal.value = false
    productToDelete.value = null
    
    // Reload if no products left
    if (products.value.length === 0 && pagination.value.current_page > 1) {
      filters.page = pagination.value.current_page - 1
      loadProducts()
    }
    
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    alert('Erreur lors de la suppression du produit')
  } finally {
    deleting.value = false
  }
}

// Utility functions
const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
                            currency: 'XAF'
  }).format(price)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short'
  })
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    draft: 'bg-gray-100 text-gray-800',
    sold: 'bg-primary-100 text-primary-800',
    reserved: 'bg-gray-100 text-gray-800',
    removed: 'bg-gray-100 text-gray-900'
  }
  return classes[status] || classes.draft
}

const getStatusText = (status) => {
  const texts = {
    active: 'Actif',
    draft: 'Brouillon',
    sold: 'Vendu',
    reserved: 'Réservé',
    removed: 'Supprimé'
  }
  return texts[status] || 'Inconnu'
}

// Lifecycle
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
</style>
