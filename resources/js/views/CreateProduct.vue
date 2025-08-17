<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-primary-50/20 to-green-50/30">
    <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
      <!-- Header -->
      <div class="mb-6 sm:mb-8">
        <nav class="flex mb-4 sm:mb-6">
          <RouterLink 
            to="/products" 
            class="group inline-flex items-center text-gray-600 hover:text-primary-600 transition-all duration-200 bg-white/80 backdrop-blur-sm px-3 sm:px-4 py-2 rounded-xl border border-white/50 hover:border-primary-200 hover:bg-white shadow-soft hover:shadow-medium text-sm sm:text-base"
          >
            <ArrowLeftIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2 group-hover:-translate-x-1 transition-transform duration-200" />
            <span class="hidden sm:inline">Retour aux produits</span>
            <span class="sm:hidden">Retour</span>
          </RouterLink>
        </nav>
        <div class="text-center lg:text-left">
          <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold bg-gradient-to-r from-gray-900 via-primary-700 to-gray-900 bg-clip-text text-transparent">
            Créer un nouveau produit
          </h1>
          <p class="text-gray-600 mt-2 sm:mt-3 text-base sm:text-lg max-w-2xl mx-auto lg:mx-0">
            Partagez vos articles avec notre communauté RIKEAA et donnez-leur une seconde vie
          </p>
        </div>
      </div>

      <!-- Error Display -->
      <div v-if="showErrors && Object.keys(errors).length > 0" class="mb-4 sm:mb-6">
        <div class="bg-red-50 border border-red-200 rounded-xl p-3 sm:p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-4 w-4 sm:h-5 sm:w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-xs sm:text-sm font-medium text-red-800">
                Erreurs de validation
              </h3>
              <div class="mt-2 text-xs sm:text-sm text-red-700">
                <ul class="list-disc pl-4 sm:pl-5 space-y-1">
                  <li v-for="(fieldErrors, field) in errors" :key="field">
                    <span v-if="field === 'general'">
                      <span v-for="error in fieldErrors" :key="error">{{ error }}</span>
                    </span>
                    <span v-else>
                      <strong>{{ field }}:</strong>
                      <span v-for="error in fieldErrors" :key="error" class="ml-1">{{ error }}</span>
                    </span>
                  </li>
                </ul>
              </div>
            </div>
            <div class="ml-auto pl-3">
              <button
                @click="showErrors = false"
                class="inline-flex bg-red-50 rounded-md p-1 sm:p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
              >
                <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <form @submit.prevent="submitProduct" class="space-y-4 sm:space-y-6 lg:space-y-8">
        <!-- Images Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-4 sm:p-6 lg:p-8 hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center mb-4 sm:mb-6">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-500 w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
              <CameraIcon class="w-3 h-3 sm:w-5 sm:h-5 text-white" />
            </div>
            <div>
              <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-slate-900">Photos du produit</h2>
              <p class="text-slate-600 text-xs sm:text-sm mt-1">Ajoutez jusqu'à 8 photos de qualité</p>
            </div>
          </div>
          
          <!-- Upload Multiple Images Button -->
          <div class="mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
              <div class="text-xs sm:text-sm text-slate-600">
                {{ form.images.filter(img => img).length }}/8 images ajoutées
              </div>
              <label class="cursor-pointer inline-flex items-center px-3 sm:px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors font-medium text-sm sm:text-base">
                <PlusIcon class="w-4 h-4 sm:w-5 sm:h-5 mr-2" />
                <span class="hidden sm:inline">Ajouter plusieurs images</span>
                <span class="sm:hidden">Ajouter images</span>
                <input 
                  ref="multipleImageInput"
                  type="file"
                  accept="image/*"
                  multiple
                  @change="handleMultipleImageUpload"
                  class="hidden"
                />
              </label>
            </div>
          </div>

          <!-- Image Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            <div
              v-for="(image, index) in form.images"
              :key="index"
              class="relative aspect-square bg-slate-100 rounded-xl border-2 border-dashed border-slate-300 hover:border-indigo-400 transition-colors overflow-hidden"
            >
              <img
                v-if="image"
                :src="image"
                :alt="`Image ${index + 1}`"
                class="w-full h-full object-cover"
              />
              <div v-else class="flex flex-col items-center justify-center h-full p-2">
                <CameraIcon class="w-6 h-6 sm:w-8 sm:h-8 text-slate-400 mb-2" />
                <span class="text-xs text-slate-500 text-center">Image {{ index + 1 }}</span>
              </div>
              
              <!-- Remove Button -->
              <button
                v-if="image"
                @click="removeImage(index)"
                type="button"
                class="absolute top-2 right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors"
              >
                <XMarkIcon class="w-3 h-3" />
              </button>
            </div>
          </div>
        </div>

        <!-- Basic Information -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-4 sm:p-6 lg:p-8 hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center mb-4 sm:mb-6">
            <div class="bg-gradient-to-r from-blue-500 to-cyan-500 w-6 h-6 sm:w-8 sm:h-8 rounded-lg flex items-center justify-center mr-2 sm:mr-3">
              <InformationCircleIcon class="w-3 h-3 sm:w-5 sm:h-5 text-white" />
            </div>
            <div>
              <h2 class="text-lg sm:text-xl lg:text-2xl font-bold text-slate-900">Informations de base</h2>
              <p class="text-slate-600 text-xs sm:text-sm mt-1">Décrivez votre produit</p>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Title -->
            <div class="lg:col-span-2">
              <label for="title" class="block text-sm font-medium text-slate-700 mb-2">
                Titre du produit *
              </label>
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                placeholder="Ex: Robe d'été élégante"
              />
            </div>

            <!-- Description -->
            <div class="lg:col-span-2">
              <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                Description *
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm resize-none"
                placeholder="Décrivez votre produit en détail..."
              ></textarea>
            </div>

            <!-- Category -->
            <div>
              <label for="category" class="block text-sm font-medium text-slate-700 mb-2">
                Catégorie *
              </label>
              <select
                id="category"
                v-model="form.category_id"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
              >
                <option value="">Sélectionnez une catégorie</option>
                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Brand -->
            <div>
              <label for="brand" class="block text-sm font-medium text-slate-700 mb-2">
                Marque
              </label>
              <select
                id="brand"
                v-model="form.brand_id"
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
              >
                <option value="">Sélectionnez une marque</option>
                <option
                  v-for="brand in brands"
                  :key="brand.id"
                  :value="brand.id"
                >
                  {{ brand.name }}
                </option>
              </select>
            </div>

            <!-- Condition -->
            <div>
              <label for="condition" class="block text-sm font-medium text-slate-700 mb-2">
                État *
              </label>
              <select
                id="condition"
                v-model="form.condition"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
              >
                <option value="">Sélectionnez l'état</option>
                <option value="new">Neuf</option>
                <option value="like_new">Comme neuf</option>
                <option value="good">Bon état</option>
                <option value="fair">État correct</option>
                <option value="poor">Usé</option>
              </select>
            </div>

            <!-- Price -->
            <div>
              <label for="price" class="block text-sm font-medium text-slate-700 mb-2">
                Prix de vente (Fcfa) *
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-500">Fcfa</span>
                <input
                  id="price"
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                  placeholder="0.00"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-center">
          <button
            type="submit"
            :disabled="submitting"
            class="inline-flex items-center px-8 sm:px-12 py-3 sm:py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="submitting" class="inline-flex items-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Création en cours...
            </span>
            <span v-else class="inline-flex items-center">
              <PlusIcon class="w-5 h-5 mr-2" />
              Créer le produit
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import {
  ArrowLeftIcon,
  CameraIcon,
  PlusIcon,
  XMarkIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

export default {
  name: 'CreateProduct',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    const notificationStore = useNotificationStore()

    // Form data
    const form = ref({
      title: '',
      description: '',
      price: '',
      category_id: '',
      brand_id: '',
      condition: '',
      images: Array(8).fill(null)
    })

    // UI state
    const submitting = ref(false)
    const showErrors = ref(false)
    const errors = ref({})

    // Data
    const categories = ref([])
    const brands = ref([])

    // Refs
    const multipleImageInput = ref(null)

    // Methods
    const loadCategories = async () => {
      try {
        const response = await window.axios.get('/categories')
        categories.value = response.data.data || response.data
      } catch (error) {
        console.error('Error loading categories:', error)
      }
    }

    const loadBrands = async () => {
      try {
        const response = await window.axios.get('/brands')
        brands.value = response.data.data || response.data
      } catch (error) {
        console.error('Error loading brands:', error)
      }
    }

    const handleMultipleImageUpload = (event) => {
      const files = Array.from(event.target.files)
      const availableSlots = form.value.images.filter(img => !img).length
      
      if (files.length > availableSlots) {
        notificationStore.showError(`Vous ne pouvez ajouter que ${availableSlots} images supplémentaires`)
        return
      }

      files.forEach((file, index) => {
        const reader = new FileReader()
        reader.onload = (e) => {
          const emptySlotIndex = form.value.images.findIndex(img => !img)
          if (emptySlotIndex !== -1) {
            form.value.images[emptySlotIndex] = e.target.result
          }
        }
        reader.readAsDataURL(file)
      })

      // Reset input
      if (multipleImageInput.value) {
        multipleImageInput.value.value = ''
      }
    }

    const removeImage = (index) => {
      form.value.images[index] = null
    }

    const validateForm = () => {
      errors.value = {}
      
      if (!form.value.title.trim()) {
        errors.value.title = ['Le titre est requis']
      }
      
      if (!form.value.description.trim()) {
        errors.value.description = ['La description est requise']
      }
      
      if (!form.value.price || form.value.price <= 0) {
        errors.value.price = ['Le prix doit être supérieur à 0']
      }
      
      if (!form.value.category_id) {
        errors.value.category_id = ['La catégorie est requise']
      }
      
      if (!form.value.condition) {
        errors.value.condition = ['L\'état est requis']
      }
      
      const hasImages = form.value.images.some(img => img)
      if (!hasImages) {
        errors.value.images = ['Au moins une image est requise']
      }

      return Object.keys(errors.value).length === 0
    }

    const submitProduct = async () => {
      if (!validateForm()) {
        showErrors.value = true
        return
      }

      submitting.value = true
      showErrors.value = false

      try {
        const productData = {
          ...form.value,
          images: form.value.images.filter(img => img)
        }

        const response = await window.axios.post('/products', productData)
        
        if (response.data.success) {
          notificationStore.showSuccess('Produit créé avec succès !')
          router.push(`/products/${response.data.data.id}`)
        } else {
          notificationStore.showError(response.data.message || 'Erreur lors de la création')
        }
      } catch (error) {
        console.error('Error creating product:', error)
        
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
          showErrors.value = true
        } else {
          notificationStore.showError('Erreur lors de la création du produit')
        }
      } finally {
        submitting.value = false
      }
    }

    // Lifecycle
    onMounted(() => {
      loadCategories()
      loadBrands()
    })

    return {
      form,
      submitting,
      showErrors,
      errors,
      categories,
      brands,
      multipleImageInput,
      handleMultipleImageUpload,
      removeImage,
      submitProduct
    }
  }
}
</script>

<style scoped>
/* Custom styles if needed */
</style>
