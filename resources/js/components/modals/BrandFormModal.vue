<template>
  <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">
          {{ isEditing ? 'Modifier la marque' : 'Nouvelle marque' }}
        </h3>
        <button @click="$emit('cancel')" class="text-gray-400 hover:text-gray-600 transition-colors">
          <XIcon class="w-6 h-6" />
        </button>
      </div>

      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="Ex: Nike, Samsung…"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          />
          <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</p>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Description de la marque…"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          ></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
        </div>

        <!-- Website -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Site web</label>
          <input
            v-model="form.website"
            type="url"
            placeholder="https://exemple.com"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          />
          <p v-if="errors.website" class="mt-1 text-sm text-red-600">{{ errors.website[0] }}</p>
        </div>

        <!-- Sort Order -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Ordre d'affichage</label>
          <input
            v-model="form.sort_order"
            type="number"
            min="0"
            placeholder="0"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          />
        </div>

        <!-- Toggles -->
        <div class="flex items-center space-x-6">
          <label class="flex items-center space-x-2 cursor-pointer">
            <input v-model="form.is_active" type="checkbox" class="h-4 w-4 text-primary-600 border-gray-300 rounded" />
            <span class="text-sm text-gray-900">Active</span>
          </label>
          <label class="flex items-center space-x-2 cursor-pointer">
            <input v-model="form.is_premium" type="checkbox" class="h-4 w-4 text-primary-600 border-gray-300 rounded" />
            <span class="text-sm text-gray-900">Premium</span>
          </label>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4">
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
            class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 flex items-center"
          >
            <div v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
            {{ loading ? 'Sauvegarde…' : (isEditing ? 'Modifier' : 'Créer') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { XIcon } from 'lucide-vue-next'

const props = defineProps({ show: Boolean, brand: Object })
const emit = defineEmits(['saved', 'cancel'])

const loading = ref(false)
const errors = ref({})

const form = reactive({
  name: '',
  description: '',
  website: '',
  sort_order: 0,
  is_active: true,
  is_premium: false,
})

const isEditing = computed(() => !!props.brand?.id)

const resetForm = () => {
  Object.assign(form, { name: '', description: '', website: '', sort_order: 0, is_active: true, is_premium: false })
  errors.value = {}
}

const fillForm = (brand) => {
  if (brand) {
    Object.assign(form, {
      name:        brand.name || '',
      description: brand.description || '',
      website:     brand.website || '',
      sort_order:  brand.sort_order ?? 0,
      is_active:   brand.is_active !== undefined ? brand.is_active : true,
      is_premium:  brand.is_premium !== undefined ? brand.is_premium : false,
    })
  }
}

const handleSubmit = async () => {
  loading.value = true
  errors.value = {}
  try {
    const payload = { ...form }
    if (!payload.description) payload.description = null
    if (!payload.website) payload.website = null

    if (isEditing.value) {
      await window.axios.put(`/admin/brands/${props.brand.id}`, payload)
    } else {
      await window.axios.post('/admin/brands', payload)
    }
    emit('saved')
    resetForm()
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      alert(error.response?.data?.message || 'Erreur lors de la sauvegarde')
    }
  } finally {
    loading.value = false
  }
}

watch(() => props.brand, (b) => b ? fillForm(b) : resetForm(), { immediate: true })
watch(() => props.show, (v) => { if (v && !props.brand) resetForm() })
</script>
