<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Product Detail -->
    <div v-else-if="product" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Breadcrumb -->
      <nav class="flex mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
          <li class="inline-flex items-center">
            <RouterLink to="/" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600">
              <HomeIcon class="w-4 h-4 mr-2" />
              Accueil
            </RouterLink>
          </li>
          <li>
            <div class="flex items-center">
              <ChevronRightIcon class="w-4 h-4 text-gray-400" />
              <RouterLink to="/products" class="ml-1 text-sm font-medium text-gray-700 hover:text-indigo-600 md:ml-2">
                Produits
              </RouterLink>
            </div>
          </li>
          <li aria-current="page">
            <div class="flex items-center">
              <ChevronRightIcon class="w-4 h-4 text-gray-400" />
              <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ product.title }}</span>
            </div>
          </li>
        </ol>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Product Images -->
        <div class="space-y-4">
          <!-- Main Image -->
          <div class="aspect-square bg-gray-100 rounded-xl overflow-hidden">
            <!-- Debug info -->
            <div v-if="product" class="absolute top-2 left-2 bg-black bg-opacity-50 text-white text-xs p-1 rounded z-10">
              Debug: main_image_url = {{ product.main_image_url || 'null' }}
            </div>
            
            <img 
              v-if="product.main_image_url"
              :src="product.main_image_url" 
              :alt="product.title"
              class="w-full h-full object-cover"
              @load="console.log('✅ Image principale chargée:', product.main_image_url)"
              @error="console.error('❌ Erreur chargement image principale:', product.main_image_url)"
            />
            <div v-else class="flex items-center justify-center h-full">
              <ImageIcon class="w-24 h-24 text-gray-400" />
              <div class="text-center text-gray-500 mt-2">
                <p>Aucune image principale</p>
                <p class="text-xs">main_image_url: {{ product?.main_image_url || 'null' }}</p>
              </div>
            </div>
          </div>

          <!-- Image Gallery (if multiple images) -->
          <div v-if="product.images && product.images.length > 1" class="grid grid-cols-4 gap-2">
            <!-- Debug info -->
            <div class="col-span-4 bg-yellow-100 text-yellow-800 text-xs p-2 rounded mb-2">
              Galerie: {{ product.images.length }} images trouvées
            </div>
            
            <div 
              v-for="image in product.images" 
              :key="image.id"
              class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer hover:opacity-80 transition-opacity relative"
              @click="selectMainImage(image)"
            >
              <img 
                :src="image.url" 
                :alt="image.alt_text || product.title"
                class="w-full h-full object-cover"
                @load="console.log('✅ Image galerie chargée:', image.url)"
                @error="console.error('❌ Erreur chargement image galerie:', image.url)"
              />
              <!-- Debug info sur chaque image -->
              <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1">
                {{ image.filename }}
              </div>
            </div>
          </div>
          
          <!-- Debug: Aucune image -->
          <div v-else-if="product.images && product.images.length === 1" class="bg-blue-100 text-blue-800 text-xs p-2 rounded">
            Une seule image trouvée (pas de galerie)
          </div>
          
          <div v-else class="bg-red-100 text-red-800 text-xs p-2 rounded">
            Aucune image trouvée dans product.images
          </div>
        </div>

        <!-- Product Info -->
        <div class="space-y-6">
          <!-- Title and Status -->
          <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ product.title }}</h1>
            <div class="flex items-center space-x-3">
              <span 
                :class="getStatusBadgeClass(product.status)"
                class="px-3 py-1 text-sm font-medium rounded-full"
              >
                {{ getStatusText(product.status) }}
              </span>
              <span class="text-sm text-gray-500">
                {{ formatDate(product.created_at) }}
              </span>
            </div>
          </div>

          <!-- Price Section -->
          <div class="bg-gray-50 rounded-xl p-6">
            <div class="flex items-baseline space-x-3">
              <span class="text-4xl font-bold text-indigo-600">{{ formatPrice(product.price) }}</span>
              <span v-if="product.original_price && product.original_price !== product.price" class="text-xl text-gray-500 line-through">
                {{ formatPrice(product.original_price) }}
              </span>
            </div>
            
            <div v-if="product.original_price && product.original_price !== product.price" class="mt-2">
              <span class="text-sm text-green-600 font-medium">
                {{ calculateDiscount(product.original_price, product.price) }}% de réduction
              </span>
            </div>

            <div v-if="product.shipping_cost && parseFloat(product.shipping_cost) > 0" class="mt-2 text-sm text-gray-600">
              + {{ formatPrice(product.shipping_cost) }} de frais de port
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex space-x-4">
            <button
              @click="toggleLike"
              :class="isLiked ? 'bg-red-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="flex-1 flex items-center justify-center px-6 py-3 rounded-lg font-medium transition-colors"
            >
              <HeartIcon :class="isLiked ? 'w-5 h-5' : 'w-5 h-5'" class="mr-2" />
              {{ isLiked ? 'Aimé' : 'J\'aime' }}
            </button>
            
            <button
              @click="toggleFavorite"
              :class="isFavorited ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="flex-1 flex items-center justify-center px-6 py-3 rounded-lg font-medium transition-colors"
            >
              <StarIcon :class="isFavorited ? 'w-5 h-5' : 'w-5 h-5'" class="mr-2" />
              {{ isFavorited ? 'Favori' : 'Favoris' }}
            </button>
          </div>

          <!-- Contact Seller -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Contacter le vendeur</h3>
            <div class="flex items-center space-x-4 mb-4">
              <img
                :src="product.user?.avatar || '/default-avatar.png'"
                :alt="product.user?.name"
                class="w-12 h-12 rounded-full object-cover"
              />
              <div>
                <p class="font-medium text-gray-900">{{ product.user?.name }}</p>
                <p class="text-sm text-gray-500">Membre depuis {{ formatDate(product.user?.created_at) }}</p>
              </div>
            </div>
            
            <div class="flex space-x-3">
              <button
                @click="startConversation"
                class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-indigo-700 transition-colors"
              >
                <MessageCircleIcon class="w-4 h-4 mr-2 inline" />
                Message
              </button>
              
              <button
                @click="viewSellerProfile"
                class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-200 transition-colors"
              >
                <UserIcon class="w-4 h-4 mr-2 inline" />
                Profil
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Product Details -->
      <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Description -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-4">Description</h3>
            <p class="text-gray-700 leading-relaxed">{{ product.description || 'Aucune description disponible.' }}</p>
          </div>
        </div>

        <!-- Specifications -->
        <div class="space-y-6">
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Caractéristiques</h3>
            <dl class="space-y-3">
              <div v-if="product.category">
                <dt class="text-sm font-medium text-gray-500">Catégorie</dt>
                <dd class="text-sm text-gray-900">{{ product.category.name }}</dd>
              </div>
              
              <div v-if="product.brand">
                <dt class="text-sm font-medium text-gray-500">Marque</dt>
                <dd class="text-sm text-gray-900">{{ product.brand.name }}</dd>
              </div>
              
              <div v-if="product.condition">
                <dt class="text-sm font-medium text-gray-500">État</dt>
                <dd class="text-sm text-gray-900">{{ product.condition.name }}</dd>
              </div>
              
              <div v-if="product.size">
                <dt class="text-sm font-medium text-gray-500">Taille</dt>
                <dd class="text-sm text-gray-900">{{ product.size }}</dd>
              </div>
              
              <div v-if="product.color">
                <dt class="text-sm font-medium text-gray-500">Couleur</dt>
                <dd class="text-sm text-gray-900">{{ product.color }}</dd>
              </div>
              
              <div v-if="product.material">
                <dt class="text-sm font-medium text-gray-500">Matériau</dt>
                <dd class="text-sm text-gray-900">{{ product.material }}</dd>
              </div>
              
              <div v-if="product.location">
                <dt class="text-sm font-medium text-gray-500">Localisation</dt>
                <dd class="text-sm text-gray-900">{{ product.location }}</dd>
              </div>
            </dl>
          </div>

          <!-- Stats -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistiques</h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ product.views_count }}</div>
                <div class="text-sm text-gray-500">Vues</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-red-500">{{ product.likes_count }}</div>
                <div class="text-sm text-gray-500">J'aime</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-yellow-500">{{ product.favorites_count }}</div>
                <div class="text-sm text-gray-500">Favoris</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-500">{{ product.comments_count }}</div>
                <div class="text-sm text-gray-500">Commentaires</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Similar Products -->
      <div v-if="similarProducts.length > 0" class="mt-12">
        <h3 class="text-2xl font-bold text-gray-900 mb-6">Produits similaires</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="similarProduct in similarProducts" 
            :key="similarProduct.id"
            class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
            @click="viewProduct(similarProduct)"
          >
            <div class="aspect-square bg-gray-100">
              <img 
                v-if="similarProduct.main_image"
                :src="similarProduct.main_image" 
                :alt="similarProduct.title"
                class="w-full h-full object-cover"
              />
              <div v-else class="flex items-center justify-center h-full">
                <ImageIcon class="w-12 h-12 text-gray-400" />
              </div>
            </div>
            <div class="p-4">
              <h4 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ similarProduct.title }}</h4>
              <p class="text-lg font-bold text-indigo-600">{{ formatPrice(similarProduct.price) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <AlertTriangleIcon class="mx-auto h-12 w-12 text-red-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Produit non trouvé</h3>
        <p class="mt-1 text-sm text-gray-500">Le produit que vous recherchez n'existe pas ou a été supprimé.</p>
        <div class="mt-6">
          <RouterLink
            to="/products"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
          >
            Retour aux produits
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import {
  HomeIcon,
  ChevronRightIcon,
  ImageIcon,
  HeartIcon,
  StarIcon,
  MessageCircleIcon,
  UserIcon,
  AlertTriangleIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

// State
const loading = ref(true)
const product = ref(null)
const similarProducts = ref([])
const isLiked = ref(false)
const isFavorited = ref(false)
const likingProduct = ref(false)
const favoritingProduct = ref(false)

// Computed
const isAuthenticated = computed(() => authStore.isAuthenticated)
const currentUserId = computed(() => authStore.user?.id)

// Methods
const loadProduct = async () => {
  loading.value = true
  try {
    const response = await window.axios.get(`/products/${route.params.id}`)
    if (response.data.success) {
      product.value = response.data.data
      
      // Debug des données du produit
      console.log('🖼️ Données du produit chargées:', {
        id: product.value.id,
        title: product.value.title,
        main_image_url: product.value.main_image_url,
        images_count: product.value.images?.length || 0,
        images: product.value.images?.map(img => ({
          id: img.id,
          filename: img.filename,
          url: img.url,
          alt_text: img.alt_text
        }))
      })
      
      await loadSimilarProducts()
      await checkUserInteractions()
    }
  } catch (error) {
    console.error('Erreur lors du chargement du produit:', error)
    product.value = null
  } finally {
    loading.value = false
  }
}

const loadSimilarProducts = async () => {
  try {
    const params = new URLSearchParams({
      category_id: product.value.category_id,
      per_page: 4,
      exclude: route.params.id
    })
    
    const response = await window.axios.get(`/products?${params}`)
    if (response.data.success) {
      similarProducts.value = response.data.data.data || []
    }
  } catch (error) {
    console.error('Erreur lors du chargement des produits similaires:', error)
  }
}

const checkUserInteractions = async () => {
  if (!isAuthenticated.value) return
  
  try {
    // Check if user liked the product
    const likeResponse = await window.axios.get(`/products/${route.params.id}/like-status`)
    isLiked.value = likeResponse.data.liked || false
    
    // Check if user favorited the product
    const favoriteResponse = await window.axios.get(`/products/${route.params.id}/favorite-status`)
    isFavorited.value = favoriteResponse.data.favorited || false
  } catch (error) {
    console.error('Erreur lors de la vérification des interactions:', error)
  }
}

const toggleLike = async () => {
  if (!isAuthenticated.value) {
    router.push('/login')
    return
  }
  
  // Protection contre les doubles clics
  if (likingProduct.value) {
    console.log('⚠️ toggleLike déjà en cours, ignoré')
    return
  }
  
  likingProduct.value = true
  
  console.log('🔄 toggleLike appelé - État avant:', {
    isLiked: isLiked.value,
    likes_count: product.value?.likes_count
  })
  
  try {
    const response = await window.axios.post(`/products/${route.params.id}/like`)
    
    console.log('📡 Réponse API:', response.data)
    
    if (response.data.success) {
      // Mettre à jour l'état local
      isLiked.value = response.data.liked
      product.value.likes_count = response.data.likes_count
      
      console.log('✅ État après mise à jour:', {
        isLiked: isLiked.value,
        likes_count: product.value.likes_count
      })
    }
  } catch (error) {
    console.error('❌ Erreur lors du like:', error)
  } finally {
    likingProduct.value = false
  }
}

const toggleFavorite = async () => {
  if (!isAuthenticated.value) {
    router.push('/login')
    return
  }
  
  // Protection contre les doubles clics
  if (favoritingProduct.value) {
    console.log('⚠️ toggleFavorite déjà en cours, ignoré')
    return
  }
  
  favoritingProduct.value = true
  
  console.log('🔄 toggleFavorite appelé - État avant:', {
    isFavorited: isFavorited.value,
    favorites_count: product.value?.favorites_count
  })
  
  try {
    const response = await window.axios.post(`/products/${route.params.id}/favorite`)
    
    console.log('📡 Réponse API:', response.data)
    
    if (response.data.success) {
      // Mettre à jour l'état local
      isFavorited.value = response.data.favorited
      product.value.favorites_count = response.data.favorites_count
      
      console.log('✅ État après mise à jour:', {
        isFavorited: isFavorited.value,
        favorites_count: product.value.favorites_count
      })
    }
  } catch (error) {
    console.error('❌ Erreur lors du favori:', error)
  } finally {
    favoritingProduct.value = false
  }
}

const startConversation = () => {
  if (!isAuthenticated.value) {
    router.push('/login')
    return
  }
  
  if (currentUserId.value === product.value.user_id) {
    alert('Vous ne pouvez pas vous envoyer un message à vous-même.')
    return
  }
  
  router.push(`/messages?user=${product.value.user_id}&product=${product.value.id}`)
}

const viewSellerProfile = () => {
  router.push(`/users/${product.value.user_id}`)
}

const viewProduct = (product) => {
  router.push(`/products/${product.id}`)
}

const selectMainImage = (image) => {
  // Update main image display with full URL
  product.value.main_image_url = image.url
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(price)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long'
  })
}

const calculateDiscount = (originalPrice, currentPrice) => {
  const discount = ((parseFloat(originalPrice) - parseFloat(currentPrice)) / parseFloat(originalPrice)) * 100
  return Math.round(discount)
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    draft: 'bg-gray-100 text-gray-800',
    sold: 'bg-blue-100 text-blue-800',
    reserved: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || classes.draft
}

const getStatusText = (status) => {
  const texts = {
    active: 'Actif',
    draft: 'Brouillon',
    sold: 'Vendu',
    reserved: 'Réservé'
  }
  return texts[status] || 'Inconnu'
}

// Lifecycle
onMounted(() => {
  loadProduct()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>



