<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div class="flex items-center space-x-3">
          <RouterLink to="/admin/dashboard" class="flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </RouterLink>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Gestion des Marques</h1>
            <p class="text-gray-600 mt-2">Gérez les marques de produits</p>
          </div>
        </div>
        <button
          @click="showCreateModal = true"
          class="inline-flex items-center px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors"
        >
          <PlusIcon class="w-5 h-5 mr-2" />
          Nouvelle marque
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 flex flex-wrap gap-4">
        <div class="flex-1 min-w-56 relative">
          <SearchIcon class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5" />
          <input
            v-model="filters.search"
            type="text"
            placeholder="Rechercher une marque…"
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            @input="debouncedSearch"
          />
        </div>
        <select
          v-model="filters.status"
          @change="loadBrands"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500"
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

      <!-- Loading -->
      <div v-if="loading" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 flex justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>

      <!-- Table -->
      <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Marque</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Site web</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produits</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Premium</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="brands.length === 0">
                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Aucune marque trouvée</td>
              </tr>
              <tr v-for="brand in brands" :key="brand.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ brand.name }}</div>
                  <div v-if="brand.description" class="text-xs text-gray-500 truncate max-w-xs">{{ brand.description }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <a v-if="brand.website" :href="brand.website" target="_blank" class="text-primary-600 hover:underline truncate max-w-xs block">
                    {{ brand.website }}
                  </a>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ brand.products_count || 0 }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex px-2 py-1 text-xs font-medium rounded-full', brand.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600']">
                    {{ brand.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="brand.is_premium" class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Premium</span>
                  <span v-else class="text-gray-400 text-sm">—</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <button @click="editBrand(brand)" class="text-primary-600 hover:text-primary-900 transition-colors" title="Modifier">
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button @click="deleteBrand(brand)" class="text-gray-500 hover:text-red-600 transition-colors" title="Supprimer">
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last_page > 1" class="px-6 py-3 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }}–{{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }}
            sur {{ pagination.total }}
          </div>
          <div class="flex space-x-2">
            <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1"
              class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
              Précédent
            </button>
            <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page"
              class="px-3 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
              Suivant
            </button>
          </div>
        </div>
      </div>

      <!-- Brand Form Modal -->
      <BrandFormModal
        :show="showCreateModal || showEditModal"
        :brand="selectedBrand"
        @close="closeModal"
        @saved="onSaved"
        @cancel="closeModal"
      />

      <!-- Delete Confirmation Modal -->
      <ConfirmModal
        :show="showDeleteModal"
        title="Confirmer la suppression"
        :message="`Êtes-vous sûr de vouloir supprimer la marque '${brandToDelete?.name}' ?`"
        confirm-text="Supprimer"
        confirm-class="bg-red-600 hover:bg-red-700 focus:ring-red-500"
        @confirm="confirmDelete"
        @cancel="showDeleteModal = false"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { PlusIcon, SearchIcon, PencilIcon, TrashIcon } from 'lucide-vue-next'
import { debounce } from 'lodash'
import BrandFormModal from '@/components/modals/BrandFormModal.vue'
import ConfirmModal from '@/components/modals/ConfirmModal.vue'

const loading = ref(false)
const brands = ref([])
const pagination = ref({ current_page: 1, last_page: 1, per_page: 15, total: 0 })

const filters = reactive({ search: '', status: '', page: 1 })

const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedBrand = ref(null)
const brandToDelete = ref(null)

const loadBrands = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filters.search) params.append('search', filters.search)
    if (filters.status) params.append('status', filters.status)
    params.append('page', filters.page)
    params.append('per_page', pagination.value.per_page)

    const response = await window.axios.get(`/admin/brands?${params}`)
    const data = response.data
    brands.value = data.data || []
    if (data.meta) pagination.value = data.meta
  } catch (e) {
    console.error(e)
    brands.value = []
  } finally {
    loading.value = false
  }
}

const debouncedSearch = debounce(() => { filters.page = 1; loadBrands() }, 300)

const resetFilters = () => { filters.search = ''; filters.status = ''; filters.page = 1; loadBrands() }

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) { filters.page = page; loadBrands() }
}

const editBrand = (brand) => { selectedBrand.value = brand; showEditModal.value = true }

const deleteBrand = (brand) => { brandToDelete.value = brand; showDeleteModal.value = true }

const confirmDelete = async () => {
  if (!brandToDelete.value) return
  try {
    await window.axios.delete(`/admin/brands/${brandToDelete.value.id}`)
    await loadBrands()
    showDeleteModal.value = false
    brandToDelete.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Erreur lors de la suppression')
  }
}

const closeModal = () => { showCreateModal.value = false; showEditModal.value = false; selectedBrand.value = null }

const onSaved = async () => { await loadBrands(); closeModal() }

onMounted(loadBrands)
</script>
