<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEditing ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
        </h3>
        <button
          @click="$emit('cancel')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <XIcon class="w-6 h-6" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Name -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
            Nom de la catégorie *
          </label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-300': errors.name }"
            placeholder="Ex: Électronique, Vêtements..."
          />
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
        </div>

        <!-- Description -->
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            Description
          </label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-300': errors.description }"
            placeholder="Description de la catégorie..."
          ></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
        </div>

        <!-- Parent Category -->
        <div>
          <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
            Catégorie parente
          </label>
          <select
            id="parent_id"
            v-model="form.parent_id"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-300': errors.parent_id }"
          >
            <option value="">Aucune (catégorie racine)</option>
            <option 
              v-for="cat in availableParents" 
              :key="cat.id" 
              :value="cat.id"
            >
              {{ cat.name }}
            </option>
          </select>
          <p v-if="errors.parent_id" class="mt-1 text-sm text-red-600">{{ errors.parent_id[0] }}</p>
        </div>

        <!-- Icon -->
        <div>
          <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
            Icône (HTML/SVG)
          </label>
          <input
            id="icon"
            v-model="form.icon"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-300': errors.icon }"
            placeholder="Ex: <svg>...</svg> ou classe d'icône"
          />
          <p v-if="errors.icon" class="mt-1 text-sm text-red-600">{{ errors.icon[0] }}</p>
        </div>

        <!-- Color -->
        <div>
          <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
            Couleur
          </label>
          <div class="flex space-x-2">
            <input
              id="color"
              v-model="form.color"
              type="color"
              class="w-16 h-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              :class="{ 'border-red-300': errors.color }"
            />
            <input
              v-model="form.color"
              type="text"
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              :class="{ 'border-red-300': errors.color }"
              placeholder="#000000"
            />
          </div>
          <p v-if="errors.color" class="mt-1 text-sm text-red-600">{{ errors.color[0] }}</p>
        </div>

        <!-- Sort Order -->
        <div>
          <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
            Ordre d'affichage
          </label>
          <input
            id="sort_order"
            v-model="form.sort_order"
            type="number"
            min="0"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            :class="{ 'border-red-300': errors.sort_order }"
            placeholder="0"
          />
          <p v-if="errors.sort_order" class="mt-1 text-sm text-red-600">{{ errors.sort_order[0] }}</p>
        </div>

        <!-- Active Status -->
        <div class="flex items-center">
          <input
            id="is_active"
            v-model="form.is_active"
            type="checkbox"
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
          />
          <label for="is_active" class="ml-2 block text-sm text-gray-900">
            Catégorie active
          </label>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-6">
          <button
            type="button"
            @click="$emit('cancel')"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Annuler
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <span v-if="loading" class="mr-2">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
            </span>
            {{ loading ? 'Sauvegarde...' : (isEditing ? 'Modifier' : 'Créer') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { XIcon } from 'lucide-vue-next'

const props = defineProps({
  show: Boolean,
  category: Object,
  categories: Array
})

const emit = defineEmits(['save', 'cancel'])

// State
const loading = ref(false)
const errors = ref({})

// Form data
const form = reactive({
  name: '',
  description: '',
  parent_id: '',
  icon: '',
  color: '',
  sort_order: 0,
  is_active: true
})

// Computed
const isEditing = computed(() => !!props.category?.id)

const availableParents = computed(() => {
  if (!props.categories) return []
  
  // Exclude the current category to prevent loops
  return props.categories.filter(cat => 
    !props.category?.id || cat.id !== props.category.id
  )
})

// Reset form
const resetForm = () => {
  Object.assign(form, {
    name: '',
    description: '',
    parent_id: '',
    icon: '',
    color: '',
    sort_order: 0,
    is_active: true
  })
  errors.value = {}
}

// Fill form with category data
const fillForm = (category) => {
  if (category) {
    Object.assign(form, {
      name: category.name || '',
      description: category.description || '',
      parent_id: category.parent_id || '',
      icon: category.icon || '',
      color: category.color || '',
      sort_order: category.sort_order || 0,
      is_active: category.is_active !== undefined ? category.is_active : true
    })
  }
}

// Handle submit
const handleSubmit = async () => {
  loading.value = true
  errors.value = {}
  
  try {
    // Prepare form data
    const formData = { ...form }
    
    // Convert empty strings to null for optional fields
    if (!formData.parent_id) formData.parent_id = null
    if (!formData.description) formData.description = null
    if (!formData.icon) formData.icon = null
    if (!formData.color) formData.color = null
    
    await emit('save', formData)
    resetForm()
    
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
    
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      alert(error.response?.data?.message || 'Erreur lors de la sauvegarde')
    }
  } finally {
    loading.value = false
  }
}

// Watch for category changes
watch(() => props.category, (newCategory) => {
  if (newCategory) {
    fillForm(newCategory)
  } else {
    resetForm()
  }
}, { immediate: true })

// Watch for show changes
watch(() => props.show, (newShow) => {
  if (newShow && !props.category) {
    resetForm()
  }
})
</script>



