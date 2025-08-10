<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/40">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-12">
      <!-- Header modernisé -->
      <div class="mb-8 lg:mb-12">
        <nav class="flex mb-6">
          <RouterLink 
            to="/products" 
            class="group inline-flex items-center text-slate-600 hover:text-indigo-600 transition-all duration-200 bg-white/60 backdrop-blur-sm px-4 py-2 rounded-full border border-white/50 hover:border-indigo-200 hover:bg-white/80"
          >
            <ArrowLeftIcon class="w-4 h-4 mr-2 group-hover:-translate-x-1 transition-transform duration-200" />
            Retour aux produits
          </RouterLink>
        </nav>
        <div class="text-center lg:text-left">
          <h1 class="text-3xl lg:text-4xl font-bold bg-gradient-to-r from-slate-900 via-indigo-900 to-slate-900 bg-clip-text text-transparent">
            Créer un nouveau produit
          </h1>
          <p class="text-slate-600 mt-3 text-lg max-w-2xl mx-auto lg:mx-0">
            Partagez vos articles avec notre communauté et donnez-leur une seconde vie
          </p>
        </div>
      </div>

      <form @submit.prevent="submitProduct" class="space-y-6 lg:space-y-8">
        <!-- Images Section modernisée -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 lg:p-8 hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center mb-6">
            <div class="bg-gradient-to-r from-indigo-500 to-purple-500 w-8 h-8 rounded-lg flex items-center justify-center mr-3">
              <CameraIcon class="w-5 h-5 text-white" />
            </div>
            <div>
              <h2 class="text-xl lg:text-2xl font-bold text-slate-900">Photos du produit</h2>
              <p class="text-slate-600 text-sm mt-1">Ajoutez jusqu'à 8 photos de qualité</p>
            </div>
          </div>
          
          <!-- Upload Multiple Images Button -->
          <div class="mb-6">
            <div class="flex items-center justify-between">
              <div class="text-sm text-slate-600">
                {{ form.images.filter(img => img).length }}/8 images ajoutées
              </div>
              <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200 transition-colors font-medium">
                <PlusIcon class="w-5 h-5 mr-2" />
                Ajouter plusieurs images
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

          <!-- Images Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" v-if="form.images.some(img => img)">
            <template v-for="(image, index) in form.images" :key="`image-${index}`">
              <div v-if="image" class="relative group">
              <div class="relative aspect-square bg-slate-100 rounded-xl overflow-hidden">
                <img 
                  :src="image.preview" 
                  :alt="`Photo ${index + 1}`"
                  class="w-full h-full object-cover transition-transform duration-200 group-hover:scale-105"
                />
                
                <!-- Image overlay -->
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-200"></div>
                
                <!-- Main image badge -->
                <div v-if="index === 0" class="absolute top-2 left-2 bg-indigo-500 text-white text-xs px-2 py-1 rounded-full font-medium">
                  Principale
                </div>
                
                <!-- Image actions -->
                <div class="absolute top-2 right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                  <!-- Move to main -->
                  <button
                    v-if="index !== 0"
                    @click="moveToMain(index)"
                    class="bg-blue-500 text-white rounded-full p-1.5 hover:bg-blue-600 transition-colors"
                    title="Définir comme image principale"
                  >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                    </svg>
                  </button>
                  
                  <!-- Delete -->
                  <button
                    @click="removeImage(index)"
                    class="bg-red-500 text-white rounded-full p-1.5 hover:bg-red-600 transition-colors"
                    title="Supprimer"
                  >
                    <XIcon class="w-3 h-3" />
                  </button>
                </div>
                
                <!-- Order indicator -->
                <div class="absolute bottom-2 left-2 bg-white/90 text-slate-700 text-xs px-2 py-1 rounded-full font-medium">
                  {{ index + 1 }}
                </div>
              </div>
            </div>
            </template>
          </div>

          <!-- Single Image Upload Slots -->
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <template v-for="(image, index) in form.images" :key="`slot-${index}`">
              <div 
                v-if="!image"
                class="relative aspect-square bg-slate-100 rounded-xl border-2 border-dashed border-slate-300 hover:border-indigo-400 transition-colors cursor-pointer group"
                @click="() => $refs[`imageInput${index}`]?.click()"
              >
                <div class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 group-hover:text-indigo-500 transition-colors">
                  <CameraIcon class="w-8 h-8 mb-2" />
                  <span class="text-xs text-center px-2">Cliquez pour ajouter</span>
                </div>
                <input 
                  :ref="`imageInput${index}`"
                  type="file"
                  accept="image/*"
                  @change="(event) => handleImageUpload(event, index)"
                  class="hidden"
                />
              </div>
            </template>
          </div>
        </div>

        <!-- Product Details Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 lg:p-8 hover:shadow-2xl transition-all duration-300">
          <div class="flex items-center mb-6">
            <div class="bg-gradient-to-r from-green-500 to-emerald-500 w-8 h-8 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div>
              <h2 class="text-xl lg:text-2xl font-bold text-slate-900">Détails du produit</h2>
              <p class="text-slate-600 text-sm mt-1">Informations essentielles pour les acheteurs</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                placeholder="Ex: T-shirt vintage en coton bio"
                />
            </div>

            <!-- Description -->
            <div class="lg:col-span-2">
              <label for="description" class="block text-sm font-medium text-slate-700 mb-2">
                Description détaillée *
                </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                            required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm resize-none"
                placeholder="Décrivez votre produit en détail : matériaux, état, histoire, etc."
              ></textarea>
              </div>

              <!-- Price -->
            <div>
              <label for="price" class="block text-sm font-medium text-slate-700 mb-2">
                Prix de vente (€) *
                </label>
                <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-500">€</span>
                  <input
                    id="price"
                    v-model="form.price"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                  class="w-full pl-8 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                    placeholder="0.00"
                  />
                </div>
              </div>

              <!-- Original Price -->
            <div>
              <label for="original_price" class="block text-sm font-medium text-slate-700 mb-2">
                Prix original (€)
                </label>
                <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-500">€</span>
                  <input
                    id="original_price"
                    v-model="form.original_price"
                    type="number"
                    step="0.01"
                    min="0"
                  class="w-full pl-8 pr-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                    placeholder="0.00"
                  />
              </div>
            </div>

              <!-- Category -->
            <div>
              <label for="category_id" class="block text-sm font-medium text-slate-700 mb-2">
                  Catégorie *
                </label>
                                            <select
                id="category_id"
                            v-model="form.category_id"
                            required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                          >
                <option value="">Sélectionnez une catégorie</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                            </option>
                          </select>
              </div>

              <!-- Brand -->
            <div>
              <label for="brand_id" class="block text-sm font-medium text-slate-700 mb-2">
                  Marque
                </label>
                  <select
                id="brand_id"
                    v-model="form.brand_id"
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                  >
                <option value="">Sélectionnez une marque</option>
                    <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                      {{ brand.name }}
                    </option>
                  </select>
              </div>

              <!-- Condition -->
            <div>
              <label for="condition_id" class="block text-sm font-medium text-slate-700 mb-2">
                État du produit *
                </label>
                  <select
                id="condition_id"
                    v-model="form.condition_id"
                    required
                class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
                  >
                <option value="">Sélectionnez un état</option>
                    <option v-for="condition in conditions" :key="condition.id" :value="condition.id">
                      {{ condition.name }}
                    </option>
                  </select>
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
                placeholder="S, M, L, XL, 42, 43..."
              />
              </div>

            <!-- Negotiable -->
            <div class="lg:col-span-2">
              <div class="flex items-center">
                  <input
                    id="negotiable"
                    v-model="form.is_negotiable"
                    type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded transition-all duration-200"
                />
                <label for="negotiable" class="ml-3 text-sm text-slate-700">
                  Prix négociable
              </label>
            </div>
                      </div>
                      </div>
                    </div>

        <!-- Submit Button -->
        <div class="flex justify-center">
            <button
              type="submit"
              :disabled="loading"
            class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-semibold rounded-xl hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
            <span v-if="loading" class="mr-2">
                <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
              </span>
              <svg v-if="!loading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              {{ loading ? 'Création en cours...' : 'Créer le produit' }}
            </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  ArrowLeftIcon, 
  CameraIcon, 
  PlusIcon, 
  XIcon 
} from 'lucide-vue-next'

const router = useRouter()

// Loading state
const loading = ref(false)

// Form data
const form = reactive({
  title: '',
  description: '',
  price: '',
  original_price: '',
  category_id: '',
  brand_id: '',
  condition_id: '',
  size: '',
  is_negotiable: false,
  status: 'active',
  images: Array(8).fill(null) // Support for 8 images
})

// Data for selects
const categories = ref([])
const brands = ref([])
const conditions = ref([])

// Fetch data for selects
const fetchSelectData = async () => {
  try {
    const [categoriesRes, brandsRes, conditionsRes] = await Promise.all([
      window.axios.get('/categories'),
      window.axios.get('/brands'),
      window.axios.get('/conditions')
    ])
    
    // Flatten categories to include both parent and child categories
    const categoriesData = categoriesRes.data.data || categoriesRes.data
    const flatCategories = []
    
    categoriesData.forEach(category => {
      // Add parent category
      flatCategories.push({
        id: category.id,
        name: category.name,
        icon: category.icon
      })
      
      // Add child categories with indentation
      if (category.children && category.children.length > 0) {
        category.children.forEach(child => {
          flatCategories.push({
            id: child.id,
            name: `  ↳ ${child.name}`,
            icon: child.icon
          })
        })
      }
    })
    
    categories.value = flatCategories
    brands.value = brandsRes.data.data || brandsRes.data
    conditions.value = conditionsRes.data.data || conditionsRes.data
  } catch (error) {
    console.error('Erreur lors du chargement des données:', error)
  }
}

// Handle single image upload
const handleImageUpload = (event, index) => {
  const file = event.target.files[0]
  if (!file) return

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    form.images[index] = {
      file: file,
      preview: e.target.result
    }
  }
  reader.readAsDataURL(file)
}

// Handle multiple image upload
const handleMultipleImageUpload = (event) => {
  const files = Array.from(event.target.files)
  let currentIndex = 0
  
  files.forEach((file, fileIndex) => {
    // Find next empty slot
    while (currentIndex < form.images.length && form.images[currentIndex]) {
      currentIndex++
    }
    
    if (currentIndex < form.images.length) {
      const reader = new FileReader()
      reader.onload = (e) => {
        form.images[currentIndex] = {
          file: file,
          preview: e.target.result
        }
      }
      reader.readAsDataURL(file)
      currentIndex++
    }
  })
}

// Move image to main position
const moveToMain = (index) => {
  if (index === 0 || !form.images[index]) return
  
  // Swap images
  const temp = form.images[0]
  form.images[0] = form.images[index]
  form.images[index] = temp
}

// Remove image
const removeImage = (index) => {
  // Remove image and shift remaining images
  form.images[index] = null
  
  // Compact array by moving non-null images to the left
  const compactedImages = []
  form.images.forEach(img => {
    if (img) compactedImages.push(img)
  })
  
  // Fill with nulls up to 8 slots
  while (compactedImages.length < 8) {
    compactedImages.push(null)
  }
  
  form.images = compactedImages
}

// Submit product
const submitProduct = async () => {
  loading.value = true
  
  try {
    // Create FormData for file upload
    const formData = new FormData()
    
    // Add text fields
    formData.append('title', form.title)
    formData.append('description', form.description)
    formData.append('price', form.price)
    formData.append('category_id', form.category_id)
    formData.append('status', form.status)
    
    // Add optional fields
    if (form.original_price) formData.append('original_price', form.original_price)
    if (form.brand_id) formData.append('brand_id', form.brand_id)
    if (form.condition_id) formData.append('condition_id', form.condition_id)
    if (form.size) formData.append('size', form.size)
    if (form.is_negotiable) formData.append('is_negotiable', '1')
    
    // Add images
    form.images.forEach((image) => {
      if (image && image.file) {
        formData.append('images[]', image.file)
      }
    })
    
    const response = await window.axios.post('/products', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    // Success - redirect to product page or products list
    const productId = response.data.data?.id || response.data.id
    if (productId) {
      router.push(`/products/${productId}`)
    } else {
      router.push('/products')
    }
    
  } catch (error) {
    console.error('Erreur lors de la création du produit:', error)
    
    // Handle validation errors
    if (error.response?.status === 422) {
      const errors = error.response.data.errors
      console.log('Erreurs de validation:', errors)
      // You could show these errors in the UI
    }
    
    alert('Erreur lors de la création du produit. Veuillez réessayer.')
  } finally {
    loading.value = false
  }
}

// Initialize component
onMounted(() => {
  fetchSelectData()
})
</script>

<style scoped>
/* Custom styles if needed */
.aspect-square {
  aspect-ratio: 1 / 1;
}
</style>
