<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Modifier le produit</h1>
            <p class="text-gray-600 mt-1">Modifiez les informations de votre article</p>
          </div>
          <div class="flex space-x-3">
            <RouterLink
              to="/my-products"
              class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Retour à mes produits
            </RouterLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Chargement du produit...</p>
      </div>

      <div v-else-if="!product" class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Produit non trouvé</h3>
        <p class="mt-1 text-sm text-gray-500">Le produit que vous recherchez n'existe pas ou a été supprimé.</p>
        <div class="mt-6">
          <RouterLink
            to="/my-products"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
          >
            Retour à mes produits
          </RouterLink>
        </div>
      </div>

      <form v-else @submit.prevent="updateProduct" class="space-y-8">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-6">Informations de base</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Title -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Titre du produit <span class="text-gray-500">*</span>
              </label>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Ex: iPhone 13 Pro Max 256GB"
              />
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Description <span class="text-gray-500">*</span>
              </label>
              <textarea
                v-model="form.description"
                rows="4"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Décrivez votre produit en détail..."
              ></textarea>
            </div>

            <!-- Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Prix de vente <span class="text-gray-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">Fcfa</span>
                <input
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  min="0.01"
                  required
                  class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="0.00"
                />
              </div>
            </div>

            <!-- Original Price -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Prix original (optionnel)
              </label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">Fcfa</span>
                <input
                  v-model="form.original_price"
                  type="number"
                  step="0.01"
                  min="0.01"
                  class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="0.00"
                />
              </div>
            </div>

            <!-- Category -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Catégorie <span class="text-gray-500">*</span>
              </label>
              <select
                v-model="form.category_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">Sélectionner une catégorie</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Brand -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Marque (optionnel)
              </label>
              <select
                v-model="form.brand_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">Sans marque</option>
                <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                  {{ brand.name }}
                </option>
              </select>
            </div>

            <!-- Condition -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                État <span class="text-gray-500">*</span>
              </label>
              <select
                v-model="form.condition_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="">Sélectionner l'état</option>
                <option v-for="condition in conditions" :key="condition.id" :value="condition.id">
                  {{ condition.name }}
                </option>
              </select>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Statut
              </label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              >
                <option value="active">Actif</option>
                <option value="draft">Brouillon</option>
                <option value="reserved">Réservé</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Additional Details -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-6">Détails supplémentaires</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Size -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Taille (optionnel)
              </label>
              <input
                v-model="form.size"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Ex: M, 42, 10.5"
              />
            </div>

            <!-- Color -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Couleur (optionnel)
              </label>
              <input
                v-model="form.color"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Ex: Rouge, Bleu, Noir"
              />
            </div>

            <!-- Material -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Matériau (optionnel)
              </label>
              <input
                v-model="form.material"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Ex: Cuir, Coton, Métal"
              />
            </div>

            <!-- Shipping Cost -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Frais de livraison (optionnel)
              </label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">Fcfa</span>
                <input
                  v-model="form.shipping_cost"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="0.00"
                />
              </div>
            </div>

            <!-- Location -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Localisation (optionnel)
              </label>
              <input
                v-model="form.location"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                placeholder="Ex: Paris, Lyon, Marseille"
              />
            </div>

            <!-- Negotiable -->
            <div class="flex items-center">
              <input
                v-model="form.is_negotiable"
                type="checkbox"
                id="negotiable"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <label for="negotiable" class="ml-2 block text-sm text-gray-900">
                Prix négociable
              </label>
            </div>

            <!-- Minimum Offer -->
            <div v-if="form.is_negotiable">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Offre minimum (optionnel)
              </label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">Fcfa</span>
                <input
                  v-model="form.minimum_offer"
                  type="number"
                  step="0.01"
                  min="0.01"
                  class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="0.00"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Current Images -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-6">Images actuelles</h2>
          
          <div v-if="product.images && product.images.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
              v-for="(image, index) in product.images"
              :key="image.id"
              class="relative group"
            >
              <img
                :src="image.url"
                :alt="`Image ${index + 1}`"
                class="w-full h-32 object-cover rounded-lg"
              />
              <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                <button
                  @click="removeImage(image.id)"
                  type="button"
                  class="bg-gray-700 text-white p-2 rounded-full hover:bg-gray-800 transition-colors"
                  title="Supprimer cette image"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p class="mt-2">Aucune image pour ce produit</p>
          </div>
        </div>

        <!-- Add New Images -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <h2 class="text-lg font-medium text-gray-900 mb-6">Ajouter de nouvelles images</h2>
          
          <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
            <input
              ref="imageInput"
              type="file"
              multiple
              accept="image/*"
              @change="handleImageUpload"
              class="hidden"
            />
            
            <div v-if="newImages.length === 0">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
              <p class="mt-2 text-sm text-gray-600">
                Glissez-déposez vos images ici ou
                <button
                  type="button"
                  @click="$refs.imageInput.click()"
                  class="text-primary-600 hover:text-primary-500 font-medium"
                >
                  cliquez pour sélectionner
                </button>
              </p>
              <p class="mt-1 text-xs text-gray-500">
                PNG, JPG, GIF jusqu'à 5MB. Maximum 10 images.
              </p>
            </div>
            
            <div v-else class="space-y-4">
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div
                  v-for="(image, index) in newImages"
                  :key="index"
                  class="relative group"
                >
                  <img
                    :src="image.preview"
                    :alt="`Nouvelle image ${index + 1}`"
                    class="w-full h-32 object-cover rounded-lg"
                  />
                  <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                    <button
                      @click="removeNewImage(index)"
                      type="button"
                      class="bg-gray-700 text-white p-2 rounded-full hover:bg-gray-800 transition-colors"
                      title="Supprimer cette image"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              
              <button
                type="button"
                @click="$refs.imageInput.click()"
                class="text-primary-600 hover:text-primary-500 font-medium"
              >
                Ajouter plus d'images
              </button>
            </div>
          </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-4">
          <RouterLink
            to="/my-products"
            class="px-6 py-3 border border-gray-300 rounded-md text-gray-700 font-medium hover:bg-gray-50 transition-colors"
          >
            Annuler
          </RouterLink>
          
          <button
            type="submit"
            :disabled="updating"
            class="px-6 py-3 bg-primary-600 text-white font-medium rounded-md hover:bg-primary-700 disabled:opacity-50 transition-colors"
          >
            {{ updating ? 'Mise à jour...' : 'Mettre à jour le produit' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// State
const loading = ref(true)
const updating = ref(false)
const product = ref(null)
const categories = ref([])
const brands = ref([])
const conditions = ref([])
const newImages = ref([])

// Form
const form = reactive({
  title: '',
  description: '',
  price: '',
  original_price: '',
  category_id: '',
  brand_id: '',
  condition_id: '',
  size: '',
  color: '',
  material: '',
  shipping_cost: '',
  location: '',
  is_negotiable: false,
  minimum_offer: '',
  status: 'active'
})

// Load product
const loadProduct = async () => {
  try {
    const response = await window.axios.get(`/products/${route.params.id}`)
    product.value = response.data.data
    
    // Fill form with current values
    Object.keys(form).forEach(key => {
      if (product.value[key] !== undefined) {
        form[key] = product.value[key]
      }
    })
    
    // Convert to string for form inputs
    form.price = product.value.price?.toString() || ''
    form.original_price = product.value.original_price?.toString() || ''
    form.shipping_cost = product.value.shipping_cost?.toString() || ''
    form.minimum_offer = product.value.minimum_offer?.toString() || ''
    
  } catch (error) {
    console.error('Erreur lors du chargement du produit:', error)
    if (error.response?.status === 404) {
      product.value = null
    }
  } finally {
    loading.value = false
  }
}

// Load form data
const loadFormData = async () => {
  try {
    const [categoriesRes, brandsRes, conditionsRes] = await Promise.all([
      window.axios.get('/categories'),
      window.axios.get('/brands'),
      window.axios.get('/conditions')
    ])
    
    categories.value = categoriesRes.data.data || categoriesRes.data
    brands.value = brandsRes.data.data || brandsRes.data
    conditions.value = conditionsRes.data.data || conditionsRes.data
    
  } catch (error) {
    console.error('Erreur lors du chargement des données:', error)
  }
}

// Handle image upload
const handleImageUpload = (event) => {
  const files = Array.from(event.target.files)
  
  files.forEach(file => {
    if (newImages.value.length >= 10) {
      alert('Maximum 10 images autorisées')
      return
    }
    
    if (file.size > 5 * 1024 * 1024) {
      alert(`L'image ${file.name} est trop volumineuse (max 5MB)`)
      return
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
      newImages.value.push({
        file: file,
        preview: e.target.result
      })
    }
    reader.readAsDataURL(file)
  })
  
  // Reset input
  event.target.value = ''
}

// Remove new image
const removeNewImage = (index) => {
  newImages.value.splice(index, 1)
}

// Remove existing image
const removeImage = async (imageId) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette image ?')) return
  
  try {
    await window.axios.delete(`/product-images/${imageId}`)
    
    // Remove from product images
    product.value.images = product.value.images.filter(img => img.id !== imageId)
    
  } catch (error) {
    console.error('Erreur lors de la suppression de l\'image:', error)
    alert('Erreur lors de la suppression de l\'image')
  }
}

// Update product
const updateProduct = async () => {
  updating.value = true
  
  try {
    const formData = new FormData()
    
    // Add form fields
    Object.keys(form).forEach(key => {
      if (form[key] !== '' && form[key] !== null && form[key] !== undefined) {
        formData.append(key, form[key])
      }
    })
    
    // Add new images
    newImages.value.forEach(image => {
      formData.append('new_images[]', image.file)
    })
    
    // Update product
    await window.axios.put(`/products/${route.params.id}`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
    
    // Redirect to product detail
    router.push(`/products/${route.params.id}`)
    
  } catch (error) {
    console.error('Erreur lors de la mise à jour:', error)
    
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors
      Object.keys(errors).forEach(key => {
        alert(`${key}: ${errors[key][0]}`)
      })
    } else {
      alert('Erreur lors de la mise à jour du produit')
    }
  } finally {
    updating.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadProduct()
  loadFormData()
})
</script>
