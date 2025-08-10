<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gestion des Catégories</h1>
          <p class="text-gray-600 mt-2">Organisez et gérez les catégories de produits</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors"
        >
          <PlusIcon class="w-5 h-5 mr-2" />
          Nouvelle catégorie
        </button>
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
                placeholder="Rechercher une catégorie..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                @input="debouncedSearch"
              />
            </div>
          </div>
          
          <select 
            v-model="filters.status"
            @change="loadCategories"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Tous les statuts</option>
            <option value="active">Actives</option>
            <option value="inactive">Inactives</option>
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

      <!-- Categories Table -->
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Catégorie
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Parent
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produits
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Statut
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ordre
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div 
                      v-if="category.color"
                      class="w-4 h-4 rounded-full mr-3"
                      :style="{ backgroundColor: category.color }"
                    ></div>
                    <div 
                      v-else-if="category.icon"
                      class="w-4 h-4 mr-3 text-gray-400"
                      v-html="category.icon"
                    ></div>
                    <TagIcon v-else class="w-4 h-4 mr-3 text-gray-400" />
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ category.name }}</div>
                      <div v-if="category.description" class="text-sm text-gray-500 truncate max-w-xs">
                        {{ category.description }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ category.parent?.name || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ category.products_count || 0 }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                      category.status === 'active' 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ category.status === 'active' ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ category.order || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <button
                      @click="editCategory(category)"
                      class="text-indigo-600 hover:text-indigo-900 transition-colors"
                      title="Modifier"
                    >
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button
                      @click="deleteCategory(category)"
                      class="text-red-600 hover:text-red-900 transition-colors"
                      title="Supprimer"
                    >
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="bg-white px-6 py-3 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Affichage de {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} à 
              {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} 
              sur {{ pagination.total }} résultats
            </div>
            <div class="flex space-x-2">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page === 1"
                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Précédent
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page === pagination.last_page"
                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Suivant
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Category Form Modal -->
      <CategoryFormModal
        :show="showCreateModal || showEditModal"
        :category="selectedCategory"
        @close="closeModal"
        @saved="onCategorySaved"
      />

      <!-- Delete Confirmation Modal -->
      <ConfirmModal
        :show="showDeleteModal"
        title="Confirmer la suppression"
        :message="`Êtes-vous sûr de vouloir supprimer la catégorie '${categoryToDelete?.name}' ?`"
        confirm-text="Supprimer"
        confirm-class="bg-red-600 hover:bg-red-700 focus:ring-red-500"
        @confirm="confirmDelete"
        @cancel="showDeleteModal = false"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { 
  PlusIcon, 
  SearchIcon, 
  TagIcon,
  PencilIcon,
  TrashIcon
} from 'lucide-vue-next'
import { debounce } from 'lodash'
import CategoryFormModal from '@/components/modals/CategoryFormModal.vue'
import ConfirmModal from '@/components/modals/ConfirmModal.vue'

// State
const loading = ref(false)
const categories = ref([])
const allCategories = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0
})

// Filters
const filters = reactive({
  search: '',
  status: '',
  page: 1
})

// Modals
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedCategory = ref(null)
const categoryToDelete = ref(null)

// Load categories
const loadCategories = async () => {
  loading.value = true
  
  try {
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.status) params.append('status', filters.status)
    params.append('page', filters.page)
    params.append('per_page', pagination.value.per_page)
    
    const response = await window.axios.get(`/admin/categories?${params}`)
    const data = response.data
    
    categories.value = data.data || []
    
    if (data.meta) {
      pagination.value = data.meta
    }
    
  } catch (error) {
    console.error('Erreur lors du chargement des catégories:', error)
    categories.value = []
  } finally {
    loading.value = false
  }
}

// Load all categories for parent selection
const loadAllCategories = async () => {
  try {
    const response = await window.axios.get('/categories')
    allCategories.value = response.data.data || response.data
  } catch (error) {
    console.error('Erreur lors du chargement de toutes les catégories:', error)
  }
}

// Debounced search
const debouncedSearch = debounce(() => {
  filters.page = 1
  loadCategories()
}, 300)

// Reset filters
const resetFilters = () => {
  filters.search = ''
  filters.status = ''
  filters.page = 1
  loadCategories()
}

// Pagination
const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    filters.page = page
    loadCategories()
  }
}

// Edit category
const editCategory = (category) => {
  selectedCategory.value = category
  showEditModal.value = true
}

// Delete category
const deleteCategory = (category) => {
  categoryToDelete.value = category
  showDeleteModal.value = true
}

// Confirm delete
const confirmDelete = async () => {
  if (!categoryToDelete.value) return
  
  try {
    await window.axios.delete(`/admin/categories/${categoryToDelete.value.id}`)
    
    // Reload categories
    await loadCategories()
    
    // Close modal
    showDeleteModal.value = false
    categoryToDelete.value = null
    
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
    alert('Erreur lors de la suppression de la catégorie')
  }
}

// Close modal
const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedCategory.value = null
}

// On category saved
const onCategorySaved = async () => {
  await loadCategories()
  closeModal()
}

// Initialize
onMounted(() => {
  loadCategories()
  loadAllCategories()
})
</script>



