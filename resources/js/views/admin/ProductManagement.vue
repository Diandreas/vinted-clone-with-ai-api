<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Gestion des Produits
          </h2>
          <div class="mt-1 flex flex-col sm:flex-row sm:flex-wrap sm:mt-0 sm:space-x-6">
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <PackageIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" />
              {{ stats.total }} produits au total
            </div>
            <div class="mt-2 flex items-center text-sm text-gray-500">
              <CheckCircleIcon class="flex-shrink-0 mr-1.5 h-5 w-5 text-green-400" />
              {{ stats.active }} actifs
            </div>
          </div>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
          <button
            @click="showCreateModal = true"
            class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
            Nouveau Produit
          </button>
        </div>
      </div>

      <!-- Filtres -->
      <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <label for="search" class="block text-sm font-medium text-gray-700">Recherche</label>
              <div class="mt-1 relative rounded-md shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <SearchIcon class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  id="search"
                  v-model="filters.search"
                  type="text"
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                  placeholder="Rechercher un produit..."
                  @input="debounceSearch"
                />
              </div>
            </div>

            <div>
              <label for="category" class="block text-sm font-medium text-gray-700">Catégorie</label>
              <select
                id="category"
                v-model="filters.category"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                @change="loadProducts"
              >
                <option value="">Toutes les catégories</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <div>
              <label for="status" class="block text-sm font-medium text-gray-700">Statut</label>
              <select
                id="status"
                v-model="filters.status"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                @change="loadProducts"
              >
                <option value="">Tous les statuts</option>
                <option value="draft">Brouillon</option>
                <option value="active">Actif</option>
                <option value="sold">Vendu</option>
                <option value="reserved">Réservé</option>
                <option value="removed">Supprimé</option>
              </select>
            </div>

            <div>
              <label for="sort" class="block text-sm font-medium text-gray-700">Trier par</label>
              <select
                id="sort"
                v-model="filters.sort"
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                @change="loadProducts"
              >
                <option value="created_at">Date de création</option>
                <option value="price">Prix</option>
                <option value="title">Titre</option>
                <option value="views_count">Vues</option>
                <option value="likes_count">Likes</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Table des produits -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div v-if="loading" class="p-6">
          <ProductTableSkeleton />
        </div>
        
        <div v-else-if="products.length === 0" class="text-center py-12">
          <PackageIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun produit</h3>
          <p class="mt-1 text-sm text-gray-500">Commencez par créer un nouveau produit.</p>
          <div class="mt-6">
            <button
              @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
              Nouveau Produit
            </button>
          </div>
        </div>

        <ul v-else class="divide-y divide-gray-200">
          <li v-for="product in products" :key="product?.id || Math.random()" v-if="product">
            <div class="px-4 py-4 flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-16 w-16">
                  <img
                    class="h-16 w-16 rounded-lg object-cover"
                    :src="product.images?.[0]?.url || '/placeholder-product.jpg'"
                    :alt="product.title"
                  />
                </div>
                <div class="ml-4">
                  <div class="flex items-center">
                    <div class="text-sm font-medium text-gray-900">
                      {{ product.title }}
                    </div>
                    <StatusBadge :status="product.status" class="ml-2" />
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ product.category?.name }} • {{ formatPrice(product.price) }}
                  </div>
                  <div class="text-sm text-gray-500">
                    Par {{ product.user?.name }} • {{ formatDate(product.created_at) }}
                  </div>
                  <div class="flex items-center space-x-4 text-xs text-gray-500 mt-1">
                    <span class="flex items-center">
                      <EyeIcon class="h-4 w-4 mr-1" />
                      {{ product.views_count }}
                    </span>
                    <span class="flex items-center">
                      <HeartIcon class="h-4 w-4 mr-1" />
                      {{ product.likes_count }}
                    </span>
                    <span class="flex items-center">
                      <MessageCircleIcon class="h-4 w-4 mr-1" />
                      {{ product.comments_count }}
                    </span>
                  </div>
                </div>
              </div>
              
              <div class="flex items-center space-x-2">
                <button
                  @click="viewProduct(product)"
                  class="text-gray-400 hover:text-gray-500"
                  title="Voir"
                >
                  <EyeIcon class="h-5 w-5" />
                </button>
                <button
                  @click="editProduct(product)"
                  class="text-indigo-600 hover:text-indigo-900"
                  title="Modifier"
                >
                  <PencilIcon class="h-5 w-5" />
                </button>
                <button
                  @click="deleteProduct(product)"
                  class="text-red-600 hover:text-red-900"
                  title="Supprimer"
                >
                  <TrashIcon class="h-5 w-5" />
                </button>
              </div>
            </div>
          </li>
        </ul>

        <!-- Pagination -->
        <div v-if="pagination.total > pagination.per_page" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="loadProducts(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Précédent
            </button>
            <button
              @click="loadProducts(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Suivant
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Affichage de
                <span class="font-medium">{{ pagination.from }}</span>
                à
                <span class="font-medium">{{ pagination.to }}</span>
                sur
                <span class="font-medium">{{ pagination.total }}</span>
                résultats
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  @click="loadProducts(pagination.current_page - 1)"
                  :disabled="pagination.current_page === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronLeftIcon class="h-5 w-5" />
                </button>
                
                <template v-for="page in visiblePages" :key="page">
                  <button
                    v-if="page !== '...'"
                    @click="loadProducts(page)"
                    :class="[
                      page === pagination.current_page
                        ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                    ]"
                  >
                    {{ page }}
                  </button>
                  <span
                    v-else
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                  >
                    ...
                  </span>
                </template>
                
                <button
                  @click="loadProducts(pagination.current_page + 1)"
                  :disabled="pagination.current_page === pagination.last_page"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <ChevronRightIcon class="h-5 w-5" />
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de création/édition -->
    <ProductFormModal
      :show="showCreateModal || showEditModal"
      :product="selectedProduct"
      :categories="categories"
      :brands="brands"
      :conditions="conditions"
      @close="closeModal"
      @saved="onProductSaved"
    />

    <!-- Modal de confirmation de suppression -->
    <ConfirmModal
      :show="showDeleteModal"
      title="Supprimer le produit"
      :message="`Êtes-vous sûr de vouloir supprimer le produit '${selectedProduct?.title}' ? Cette action est irréversible.`"
      confirm-text="Supprimer"
      confirm-class="bg-red-600 hover:bg-red-700 focus:ring-red-500"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { debounce } from 'lodash'
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'
import {
  PackageIcon,
  CheckCircleIcon,
  PlusIcon,
  SearchIcon,
  EyeIcon,
  HeartIcon,
  MessageCircleIcon,
  PencilIcon,
  TrashIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from 'lucide-vue-next'

// Composants
import ProductTableSkeleton from '@/components/skeletons/ProductTableSkeleton.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import ProductFormModal from '@/components/modals/ProductFormModal.vue'
import ConfirmModal from '@/components/modals/ConfirmModal.vue'

// State
const loading = ref(true)
const products = ref([])
const categories = ref([])
const brands = ref([])
const conditions = ref([])
const stats = reactive({
  total: 0,
  active: 0,
  sold: 0,
  draft: 0
})

const filters = reactive({
  search: '',
  category: '',
  status: '',
  sort: 'created_at',
  order: 'desc'
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})

// Modales
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedProduct = ref(null)

// Computed
const visiblePages = computed(() => {
  const pages = []
  const currentPage = pagination.current_page
  const lastPage = pagination.last_page
  
  if (lastPage <= 7) {
    for (let i = 1; i <= lastPage; i++) {
      pages.push(i)
    }
  } else {
    if (currentPage <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(lastPage)
    } else if (currentPage >= lastPage - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = lastPage - 4; i <= lastPage; i++) {
        pages.push(i)
      }
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = currentPage - 1; i <= currentPage + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(lastPage)
    }
  }
  
  return pages
})

// Methods
const loadProducts = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: pagination.per_page.toString(),
      sort: filters.sort,
      order: filters.order,
      ...Object.fromEntries(
        Object.entries(filters).filter(([key, value]) => 
          value !== '' && key !== 'sort' && key !== 'order'
        )
      )
    })

    const response = await window.axios.get(`/admin/products?${params}`)
    
    products.value = response.data.data
    Object.assign(pagination, response.data.meta)
    
    // Charger les stats
    const statsResponse = await window.axios.get('/products/stats')
    Object.assign(stats, statsResponse.data)
    
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

const loadBrands = async () => {
  try {
    const response = await window.axios.get('/brands')
    brands.value = response.data.data || response.data
  } catch (error) {
    console.error('Erreur lors du chargement des marques:', error)
  }
}

const loadConditions = async () => {
  try {
    const response = await window.axios.get('/conditions')
    conditions.value = response.data.data || response.data
  } catch (error) {
    console.error('Erreur lors du chargement des conditions:', error)
  }
}

const debounceSearch = debounce(() => {
  pagination.current_page = 1
  loadProducts()
}, 500)

const viewProduct = (product) => {
  window.open(`/products/${product.id}`, '_blank')
}

const editProduct = (product) => {
  selectedProduct.value = { ...product }
  showEditModal.value = true
}

const deleteProduct = (product) => {
  selectedProduct.value = product
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  try {
    await window.axios.delete(`/products/${selectedProduct.value.id}`)
    showDeleteModal.value = false
    selectedProduct.value = null
    loadProducts(pagination.current_page)
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedProduct.value = null
}

const onProductSaved = () => {
  closeModal()
  loadProducts(pagination.current_page)
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(price)
}

const formatDate = (date) => {
  if (!date) return '-'
  
  const dateObj = new Date(date)
  if (isNaN(dateObj.getTime())) return '-'
  
  return formatDistanceToNow(dateObj, {
    addSuffix: true,
    locale: fr
  })
}

// Watchers
watch([() => filters.category, () => filters.status, () => filters.sort], () => {
  pagination.current_page = 1
  loadProducts()
})

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadProducts(),
    loadCategories(),
    loadBrands(),
    loadConditions()
  ])
})
</script>
