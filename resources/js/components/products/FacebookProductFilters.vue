<template>
  <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
      <!-- Search Input -->
      <div class="flex-1 max-w-md">
        <div class="relative">
          <input
            v-model="searchQuery"
            @input="handleSearch"
            type="text"
            placeholder="Rechercher des produits..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex items-center space-x-4">
        <!-- Category Filter -->
        <select
          v-model="selectedCategory"
          @change="handleCategoryChange"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Toutes les catégories</option>
          <option
            v-for="category in categories"
            :key="category.id"
            :value="category.id"
          >
            {{ category.name }}
          </option>
        </select>

        <!-- Sort Filter -->
        <select
          v-model="selectedSort"
          @change="handleSortChange"
          class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="created_at">Plus récents</option>
          <option value="price_asc">Prix croissant</option>
          <option value="price_desc">Prix décroissant</option>
          <option value="likes_count">Plus populaires</option>
          <option value="views_count">Plus vus</option>
        </select>

        <!-- View Toggle -->
        <div class="flex items-center bg-gray-100 rounded-lg p-1">
          <button
            @click="setViewMode('list')"
            :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              viewMode === 'list' 
                ? 'bg-white text-gray-900 shadow-sm' 
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            Liste
          </button>
          <button
            @click="setViewMode('grid')"
            :class="[
              'px-3 py-1 rounded-md text-sm font-medium transition-colors',
              viewMode === 'grid' 
                ? 'bg-white text-gray-900 shadow-sm' 
                : 'text-gray-600 hover:text-gray-900'
            ]"
          >
            Grille
          </button>
        </div>
      </div>
    </div>

    <!-- Active Filters -->
    <div v-if="hasActiveFilters" class="flex items-center space-x-2 mt-4 pt-4 border-t border-gray-100">
      <span class="text-sm text-gray-600">Filtres actifs :</span>
      
      <span
        v-if="selectedCategory"
        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
      >
        {{ getCategoryName(selectedCategory) }}
        <button
          @click="clearCategory"
          class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-blue-400 hover:bg-blue-200 hover:text-blue-500"
        >
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </span>

      <span
        v-if="searchQuery"
        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
      >
        "{{ searchQuery }}"
        <button
          @click="clearSearch"
          class="ml-1 inline-flex items-center justify-center w-4 h-4 rounded-full text-green-400 hover:bg-green-200 hover:text-green-500"
        >
          <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </span>

      <button
        @click="clearAllFilters"
        class="text-sm text-gray-500 hover:text-gray-700 underline"
      >
        Effacer tous les filtres
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, watch } from 'vue'

export default {
  name: 'FacebookProductFilters',
  props: {
    categories: {
      type: Array,
      default: () => []
    },
    initialSearch: {
      type: String,
      default: ''
    },
    initialCategory: {
      type: [String, Number],
      default: ''
    },
    initialSort: {
      type: String,
      default: 'created_at'
    },
    initialViewMode: {
      type: String,
      default: 'list'
    }
  },
  emits: ['search', 'category-change', 'sort-change', 'view-mode-change', 'filters-change'],
  setup(props, { emit }) {
    const searchQuery = ref(props.initialSearch)
    const selectedCategory = ref(props.initialCategory)
    const selectedSort = ref(props.initialSort)
    const viewMode = ref(props.initialViewMode)

    const hasActiveFilters = computed(() => {
      return searchQuery.value || selectedCategory.value
    })

    const getCategoryName = (categoryId) => {
      const category = props.categories.find(cat => cat.id == categoryId)
      return category ? category.name : 'Catégorie'
    }

    const handleSearch = () => {
      emit('search', searchQuery.value)
      emit('filters-change', getFilters())
    }

    const handleCategoryChange = () => {
      emit('category-change', selectedCategory.value)
      emit('filters-change', getFilters())
    }

    const handleSortChange = () => {
      emit('sort-change', selectedSort.value)
      emit('filters-change', getFilters())
    }

    const setViewMode = (mode) => {
      viewMode.value = mode
      emit('view-mode-change', mode)
    }

    const clearSearch = () => {
      searchQuery.value = ''
      handleSearch()
    }

    const clearCategory = () => {
      selectedCategory.value = ''
      handleCategoryChange()
    }

    const clearAllFilters = () => {
      searchQuery.value = ''
      selectedCategory.value = ''
      selectedSort.value = 'created_at'
      emit('search', '')
      emit('category-change', '')
      emit('sort-change', 'created_at')
      emit('filters-change', getFilters())
    }

    const getFilters = () => ({
      search: searchQuery.value,
      category: selectedCategory.value,
      sort: selectedSort.value,
      viewMode: viewMode.value
    })

    // Watch for prop changes
    watch(() => props.initialSearch, (newVal) => {
      searchQuery.value = newVal
    })

    watch(() => props.initialCategory, (newVal) => {
      selectedCategory.value = newVal
    })

    return {
      searchQuery,
      selectedCategory,
      selectedSort,
      viewMode,
      hasActiveFilters,
      getCategoryName,
      handleSearch,
      handleCategoryChange,
      handleSortChange,
      setViewMode,
      clearSearch,
      clearCategory,
      clearAllFilters
    }
  }
}
</script>
