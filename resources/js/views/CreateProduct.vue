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
                :src="getImageSrc(image)"
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
              <label for="condition_id" class="block text-sm font-medium text-slate-700 mb-2">
                État *
              </label>
              <select
                id="condition_id"
                v-model="form.condition_id"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
              >
                <option value="">Sélectionnez l'état</option>
                <option
                  v-for="condition in conditions"
                  :key="condition.id"
                  :value="condition.id"
                >
                  {{ condition.name }}
                </option>
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

            <!-- Size -->
            <div>
              <label for="size" class="block text-sm font-medium text-slate-700 mb-2">
                Taille
              </label>
              <input
                id="size"
                v-model="form.size"
                type="text"
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                placeholder="Ex: M, L, XL, 42, 43..."
              />
            </div>

            <!-- Color -->
            <div>
              <label for="color" class="block text-sm font-medium text-slate-700 mb-2">
                Couleur
              </label>
              <input
                id="color"
                v-model="form.color"
                type="text"
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                placeholder="Ex: Bleu, Rouge, Noir..."
              />
            </div>

            <!-- Material -->
            <div>
              <label for="material" class="block text-sm font-medium text-slate-700 mb-2">
                Matériau
              </label>
              <input
                id="material"
                v-model="form.material"
                type="text"
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                placeholder="Ex: Coton, Cuir, Polyester..."
              />
            </div>

            <!-- Shipping Cost -->
            <div>
              <label for="shipping_cost" class="block text-sm font-medium text-slate-700 mb-2">
                Frais de livraison (Fcfa)
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-500">Fcfa</span>
                <input
                  id="shipping_cost"
                  v-model="form.shipping_cost"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full pl-12 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                  placeholder="0.00"
                />
              </div>
            </div>

            <!-- Location -->
            <div>
              <label for="location" class="block text-sm font-medium text-slate-700 mb-2">
                Localisation
              </label>
              <input
                id="location"
                v-model="form.location"
                type="text"
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                placeholder="Ex: Abidjan, Yamoussoukro..."
              />
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
// Icônes SVG inline pour éviter les problèmes d'import
const ArrowLeftIcon = {
  template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
  </svg>`
}

const CameraIcon = {
  template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
  </svg>`
}

const PlusIcon = {
  template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
  </svg>`
}

const XMarkIcon = {
  template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
  </svg>`
}

const InformationCircleIcon = {
  template: `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
  </svg>`
}

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
      condition_id: '',
      size: '',
      color: '',
      material: '',
      shipping_cost: '',
      location: '',
      images: Array(8).fill(null)
    })

    // UI state
    const submitting = ref(false)
    const showErrors = ref(false)
    const errors = ref({})

    // Data
    const categories = ref([])
    const brands = ref([])
    const conditions = ref([])

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

    const loadConditions = async () => {
      try {
        const response = await window.axios.get('/conditions')
        conditions.value = response.data.data || response.data
      } catch (error) {
        console.error('Error loading conditions:', error)
      }
    }

    const handleMultipleImageUpload = (event) => {
      const files = Array.from(event.target.files)
      const availableSlots = form.value.images.filter(img => !img).length
      
      if (files.length > availableSlots) {
        notificationStore.error(`Vous ne pouvez ajouter que ${availableSlots} images supplémentaires`)
        return
      }

      files.forEach((file, index) => {
        const emptySlotIndex = form.value.images.findIndex(img => !img)
        if (emptySlotIndex !== -1) {
          form.value.images[emptySlotIndex] = file
        }
      })

      // Reset input
      if (multipleImageInput.value) {
        multipleImageInput.value.value = ''
      }
    }

    const removeImage = (index) => {
      form.value.images[index] = null
    }

    // Helper function to check if image is a File object
    const isFile = (image) => {
      return image && typeof image === 'object' && image.name && image.type
    }

    // Helper function to get image source
    const getImageSrc = (image) => {
      if (isFile(image)) {
        return URL.createObjectURL(image)
      }
      return image
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
      
      if (!form.value.condition_id) {
        errors.value.condition_id = ['L\'état est requis']
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
        const formData = new FormData()
        
        // Ajouter les champs texte
        formData.append('title', form.value.title)
        formData.append('description', form.value.description)
        formData.append('price', form.value.price)
        formData.append('category_id', form.value.category_id)
        if (form.value.brand_id) {
          formData.append('brand_id', form.value.brand_id)
        }
        formData.append('condition_id', form.value.condition_id)
        if (form.value.size) {
          formData.append('size', form.value.size)
        }
        if (form.value.color) {
          formData.append('color', form.value.color)
        }
        if (form.value.material) {
          formData.append('material', form.value.material)
        }
        if (form.value.shipping_cost) {
          formData.append('shipping_cost', form.value.shipping_cost)
        }
        if (form.value.location) {
          formData.append('location', form.value.location)
        }
        
        // Ajouter les images
        const validImages = form.value.images.filter(img => img)
        validImages.forEach((image, index) => {
          formData.append(`images[${index}]`, image)
        })

        const response = await window.axios.post('/products', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.data.success) {
          notificationStore.success('Produit créé avec succès !')
          router.push(`/products/${response.data.data.id}`)
        } else {
          notificationStore.error(response.data.message || 'Erreur lors de la création')
        }
      } catch (error) {
        console.error('Error creating product:', error)
        
        if (error.response?.data?.errors) {
          errors.value = error.response.data.errors
          showErrors.value = true
        } else {
          notificationStore.error('Erreur lors de la création du produit')
        }
      } finally {
        submitting.value = false
      }
    }

    // Lifecycle
    onMounted(() => {
      loadCategories()
      loadBrands()
      loadConditions()
    })

    return {
      form,
      submitting,
      showErrors,
      errors,
      categories,
      brands,
      conditions,
      multipleImageInput,
      handleMultipleImageUpload,
      removeImage,
      isFile,
      getImageSrc,
      submitProduct
    }
  }
}
</script>

<style scoped>
/* Custom styles if needed */
</style>
