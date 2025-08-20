<template>
  <div class="min-h-screen bg-gray-50 pb-16 sm:pb-0">
    <!-- Loading -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <!-- Product Detail -->
    <div v-else-if="product" class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3 sm:py-6">
      <!-- Breadcrumb - Compact -->
      <nav class="flex mb-3 sm:mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-2">
          <li class="inline-flex items-center">
            <RouterLink to="/" class="inline-flex items-center text-xs sm:text-sm font-medium text-gray-700 hover:text-primary-600">
              <HomeIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" />
              Accueil
            </RouterLink>
          </li>
          <li>
            <div class="flex items-center">
              <ChevronRightIcon class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400" />
              <RouterLink to="/products" class="ml-1 text-xs sm:text-sm font-medium text-gray-700 hover:text-primary-600 md:ml-2">
                Produits
              </RouterLink>
            </div>
          </li>
          <li aria-current="page">
            <div class="flex items-center">
              <ChevronRightIcon class="w-3 h-3 sm:w-4 sm:h-4 text-gray-400" />
              <span class="ml-1 text-xs sm:text-sm font-medium text-gray-500 md:ml-2 truncate max-w-32 sm:max-w-none">{{ product.title }}</span>
            </div>
          </li>
        </ol>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Product Images - Compact -->
        <div class="space-y-3">
          <!-- Main Image -->
          <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden relative">
            <img
              v-if="product.main_image_url"
              :src="product.main_image_url"
              :alt="product.title"
              class="w-full h-full object-cover"
            />
            <div v-else class="flex items-center justify-center h-full">
              <ImageIcon class="w-16 h-16 sm:w-24 sm:h-24 text-gray-400" />
              <div class="text-center text-gray-500 mt-2">
                <p class="text-sm">Aucune image disponible</p>
              </div>
            </div>
          </div>

          <!-- Image Gallery (if multiple images) - Compact -->
          <div v-if="product.images && product.images.length > 1" class="grid grid-cols-4 gap-2">
            <div
              v-for="image in product.images"
              :key="image.id"
              class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer hover:opacity-80 transition-opacity"
              @click="selectMainImage(image)"
            >
              <img
                :src="image.url"
                :alt="image.alt_text || product.title"
                class="w-full h-full object-cover"
              />
            </div>
          </div>

        </div>

        <!-- Product Info - Compact -->
        <div class="space-y-4 sm:space-y-6">
          <!-- Title and Status - Compact -->
          <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-2">{{ product.title }}</h1>
            <div class="flex items-center space-x-2 sm:space-x-3">
              <span
                :class="getStatusBadgeClass(product.status)"
                class="px-2 py-1 text-xs sm:text-sm font-medium rounded-full"
              >
                {{ getStatusText(product.status) }}
              </span>
              <span class="text-xs sm:text-sm text-gray-500">
                {{ formatDate(product.created_at) }}
              </span>
            </div>
          </div>

          <!-- Price Section - Compact -->
          <div class="bg-gray-50 rounded-lg p-4 sm:p-6">
            <div class="flex items-baseline space-x-2 sm:space-x-3">
              <span class="text-2xl sm:text-3xl lg:text-4xl font-bold text-primary-600">{{ formatPrice(product.price) }}</span>
              <span v-if="product.original_price && product.original_price !== product.price" class="text-lg sm:text-xl text-gray-500 line-through">
                {{ formatPrice(product.original_price) }}
              </span>
            </div>

            <div v-if="product.original_price && product.original_price !== product.price" class="mt-1 sm:mt-2">
              <span class="text-xs sm:text-sm text-green-600 font-medium">
                {{ calculateDiscount(product.original_price, product.price) }}% de réduction
              </span>
            </div>

            <div v-if="product.shipping_cost && parseFloat(product.shipping_cost) > 0" class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600">
              + {{ formatPrice(product.shipping_cost) }} de frais de port
            </div>
          </div>

          <!-- Action Buttons - Compact -->
          <div class="flex space-x-3">
            <button
              @click="toggleLike"
              :class="isLiked ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="flex-1 flex items-center justify-center px-4 py-2.5 rounded-lg font-medium transition-colors text-sm"
            >
              <HeartIcon :class="isLiked ? 'w-4 h-4' : 'w-4 h-4'" class="mr-2" />
              {{ isLiked ? 'Aimé' : 'J\'aime' }}
            </button>

            <button
              @click="toggleFavorite"
              :class="isFavorited ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              class="flex-1 flex items-center justify-center px-6 py-3 rounded-lg font-medium transition-colors"
            >
              <StarIcon :class="isFavorited ? 'w-5 h-5' : 'w-5 h-5'" class="mr-2" />
              {{ isFavorited ? 'Favori' : 'Favoris' }}
            </button>
          </div>

          <!-- Contact Seller / Product Owner Actions -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              {{ isProductOwner ? 'Gestion du produit' : 'Contacter le vendeur' }}
            </h3>
            <div class="flex items-center space-x-4 mb-4">
              <!-- Avatar avec initiales -->
              <div class="w-12 h-12 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold text-lg">
                {{ getUserInitials(product.user?.name) }}
              </div>
              <div>
                <p class="font-medium text-gray-900">{{ product.user?.name }}</p>
                <p class="text-sm text-gray-500">Membre depuis {{ formatDate(product.user?.created_at) }}</p>
              </div>
            </div>

            <!-- Actions pour le propriétaire du produit -->
            <div v-if="isProductOwner" class="space-y-3">
              <button
                @click="loadProductConversations"
                class="w-full bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition-colors"
              >
                <MessageCircleIcon class="w-4 h-4 mr-2 inline" />
                {{ showProductConversations ? 'Masquer' : 'Voir' }} conversations ({{ productConversations.length }})
              </button>

              <div class="grid gap-2" :class="canEditProduct ? 'grid-cols-2' : 'grid-cols-1'">
                <button
                  v-if="canEditProduct"
                  @click="editProduct"
                  class="bg-primary-600 text-white px-3 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors text-sm"
                >
                  Modifier
                </button>
                <div
                  v-else-if="isProductOwner"
                  class="bg-gray-100 text-gray-500 px-3 py-2 rounded-lg text-sm text-center border border-gray-200 mb-2"
                  title="La modification n'est plus possible après 30 minutes"
                >
                  ⏰ Modification expirée (30min dépassées)
                </div>
                <button
                  @click="toggleProductStatus"
                  class="bg-gray-600 text-white px-3 py-2 rounded-lg font-medium hover:bg-gray-700 transition-colors text-sm"
                >
                  {{ product?.status === 'active' ? 'Suspendre' : 'Activer' }}
                </button>
              </div>
            </div>

            <!-- Actions pour les autres utilisateurs -->
            <div v-else class="flex space-x-3">
              <button
                @click="startConversation"
                class="flex-1 bg-primary-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-primary-700 transition-colors"
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
                <div class="text-2xl font-bold text-primary-600">{{ product.views_count }}</div>
                <div class="text-sm text-gray-500">Vues</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-500">{{ product.likes_count }}</div>
                <div class="text-sm text-gray-500">J'aime</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-gray-500">{{ product.favorites_count }}</div>
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
              <p class="text-lg font-bold text-primary-600">{{ formatPrice(similarProduct.price) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Product Conversations (for owner) -->
      <div v-if="isProductOwner && showProductConversations" class="mt-8">
        <div class="bg-white rounded-xl border border-gray-200 p-6">
          <h3 class="text-xl font-semibold text-gray-900 mb-4">
            Personnes intéressées par ce produit
          </h3>

          <!-- Loading conversations -->
          <div v-if="loadingConversations" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            <span class="ml-2 text-gray-600">Chargement des conversations...</span>
          </div>

          <!-- No conversations -->
          <div v-else-if="productConversations.length === 0" class="text-center py-8">
            <MessageCircleIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
            <p class="text-gray-500">Aucune personne ne s'est encore manifestée pour ce produit.</p>
          </div>

          <!-- Conversations list -->
          <div v-else class="space-y-4">
            <div
              v-for="conversation in productConversations"
              :key="conversation.id"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center space-x-4">
                <!-- Buyer avatar -->
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold">
                  {{ getUserInitials(conversation.buyer?.name) }}
                </div>

                <!-- Buyer info & last message -->
                <div>
                  <p class="font-medium text-gray-900">{{ conversation.buyer?.name }}</p>
                  <p class="text-sm text-gray-500" v-if="conversation.last_message">
                    "{{ extractMessageContent(conversation.last_message.content, 50) }}..."
                  </p>
                  <p class="text-xs text-gray-400">
                    {{ formatMessageDate(conversation.last_message_at) }}
                  </p>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center space-x-2">
                <span
                  v-if="conversation.unread_count > 0"
                  class="bg-gray-500 text-white text-xs px-2 py-1 rounded-full"
                >
                  {{ conversation.unread_count }} nouveau{{ conversation.unread_count > 1 ? 'x' : '' }}
                </span>
                <button
                  @click="openConversation(conversation)"
                  class="px-3 py-1 bg-primary-600 text-white text-sm rounded hover:bg-primary-700 transition-colors"
                >
                  Répondre
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <AlertTriangleIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Produit non trouvé</h3>
        <p class="mt-1 text-sm text-gray-500">Le produit que vous recherchez n'existe pas ou a été supprimé.</p>
        <div class="mt-6">
          <RouterLink
            to="/products"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
          >
            Retour aux produits
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Modal d'envoi de message -->
    <div v-if="showMessageModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full" @click.stop>
        <!-- Header du modal -->
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Envoyer un message</h3>
            <button
              @click="closeMessageModal"
              class="text-gray-400 hover:text-gray-600 transition-colors"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Body du modal -->
        <div class="p-6">
          <!-- Info produit -->
          <div class="flex items-center space-x-3 mb-4 p-3 bg-gray-50 rounded-lg">
            <img
              :src="product?.main_image_url"
              :alt="product?.title"
              class="w-12 h-12 object-cover rounded-lg"
            />
            <div>
              <p class="font-medium text-gray-900 text-sm">{{ product?.title }}</p>
              <p class="text-sm text-gray-500">{{ formatPrice(product?.price) }}</p>
            </div>
          </div>

          <!-- Info vendeur -->
          <div class="flex items-center space-x-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white font-semibold text-sm">
              {{ getUserInitials(product?.user?.name) }}
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">{{ product?.user?.name }}</p>
              <p class="text-xs text-gray-500">Vendeur</p>
            </div>
          </div>

          <!-- Zone de message -->
          <div class="mb-4">
            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
              Votre message
            </label>
            <textarea
              id="message"
              v-model="messageContent"
              rows="4"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 resize-none"
              placeholder="Bonjour, je suis intéressé(e) par votre produit..."
              :disabled="sendingMessage"
            ></textarea>
          </div>

          <!-- Erreur -->
          <div v-if="messageError" class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded-lg">
            <p class="text-sm text-gray-700">{{ messageError }}</p>
          </div>
        </div>

        <!-- Footer du modal -->
        <div class="p-6 border-t border-gray-200 flex space-x-3">
          <button
            @click="closeMessageModal"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            :disabled="sendingMessage"
          >
            Annuler
          </button>
          <button
            @click="sendMessage"
            class="flex-1 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="sendingMessage || !messageContent.trim()"
          >
            <span v-if="sendingMessage">Envoi...</span>
            <span v-else>Envoyer</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { extractMessageContent } from '@/utils/messageUtils'
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

// Message Modal State
const showMessageModal = ref(false)
const messageContent = ref('')
const sendingMessage = ref(false)
const messageError = ref('')

// Product Conversations State (for owner)
const showProductConversations = ref(false)
const productConversations = ref([])
const loadingConversations = ref(false)

// Computed
const isAuthenticated = computed(() => authStore.isAuthenticated)
const currentUserId = computed(() => authStore.user?.id)
const isProductOwner = computed(() => {
  return currentUserId.value === product.value?.user_id
})

const canEditProduct = computed(() => {
  if (!isProductOwner.value) return false
  
  // Vérifier si le produit a été créé il y a moins de 30 minutes
  if (product.value?.created_at) {
    const createdAt = new Date(product.value.created_at)
    const now = new Date()
    const diffInMinutes = (now - createdAt) / (1000 * 60) // différence en minutes
    
    return diffInMinutes <= 30
  }
  
  return false
})

// Methods
const loadProduct = async () => {
  console.log('🔵 loadProduct appelé pour ID:', route.params.id)
  loading.value = true
  try {
    console.log('📡 Appel API GET /products/' + route.params.id)
    const response = await api.get(`/products/${route.params.id}`)
    console.log('📊 Réponse API produit:', response.data)

    if (response.data.success) {
      product.value = response.data.data

      // Debug des données du produit
      console.log('🖼️ Données du produit chargées:', {
        id: product.value.id,
        title: product.value.title,
        user_id: product.value.user_id,
        user: product.value.user,
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

    const response = await api.get(`/products?${params}`)
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
    const likeResponse = await api.get(`/products/${route.params.id}/like-status`)
    isLiked.value = likeResponse.data.liked || false

    // Check if user favorited the product
    const favoriteResponse = await api.get(`/products/${route.params.id}/favorite-status`)
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
    const response = await api.post(`/products/${route.params.id}/like`)

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
    const response = await api.post(`/products/${route.params.id}/favorite`)

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
  console.log('🔵 startConversation appelé')
  console.log('Authentification:', {
    isAuthenticated: isAuthenticated.value,
    currentUserId: currentUserId.value,
    productOwnerId: product.value?.user_id,
    authStore: authStore.user
  })

  if (!isAuthenticated.value) {
    console.log('❌ Utilisateur non authentifié, redirection vers /login')
    router.push('/login')
    return
  }

  if (currentUserId.value === product.value.user_id) {
    console.log('❌ Utilisateur essaie de s\'envoyer un message à lui-même')
    alert('Vous ne pouvez pas vous envoyer un message à vous-même.')
    return
  }

  console.log('✅ Ouverture du modal de message')
  // Ouvrir le modal d'envoi de message
  showMessageModal.value = true
}

// Fonctions pour le modal de message
const closeMessageModal = () => {
  console.log('🔵 closeMessageModal appelé')
  showMessageModal.value = false
  messageContent.value = ''
  messageError.value = ''
}

const sendMessage = async () => {
  console.log('🔵 sendMessage appelé')
  console.log('Message content:', messageContent.value)
  console.log('Modal state:', {
    showMessageModal: showMessageModal.value,
    sendingMessage: sendingMessage.value,
    messageError: messageError.value
  })

  if (!messageContent.value.trim()) {
    console.log('❌ Message vide')
    messageError.value = 'Veuillez écrire un message'
    return
  }

  sendingMessage.value = true
  messageError.value = ''

  console.log('🚀 Début envoi message:', {
    productId: product.value.id,
    message: messageContent.value.trim(),
    isAuthenticated: isAuthenticated.value,
    userId: currentUserId.value,
    token: localStorage.getItem('auth_token')?.substring(0, 20) + '...'
  })

  try {
    // Utiliser le nouvel endpoint pour démarrer une conversation par produit
    const response = await api.post(`/conversations/start/${product.value.id}`, {
      message: messageContent.value.trim()
    })

    console.log('📡 Réponse API:', response.data)

    if (response.data.success) {
      console.log('✅ Message envoyé avec succès')
      // Fermer le modal
      closeMessageModal()

      // Rediriger vers les discussions produit (vue acheteur)
      router.push('/discussions')
    } else {
      console.log('❌ Échec envoi (success=false):', response.data.message)
      messageError.value = response.data.message || 'Erreur lors de l\'envoi du message'
    }
  } catch (error) {
    console.error('❌ Erreur envoi message:', error)
    console.error('Status:', error.response?.status)
    console.error('Data:', error.response?.data)

    if (error.response?.status === 401) {
      messageError.value = 'Vous devez être connecté pour envoyer un message'
    } else if (error.response?.status === 403) {
      messageError.value = 'Vous n\'êtes pas autorisé à envoyer ce message'
    } else {
      messageError.value = error.response?.data?.message || 'Erreur lors de l\'envoi du message'
    }
  } finally {
    sendingMessage.value = false
  }
}

const viewSellerProfile = () => {
  router.push(`/users/${product.value.user_id}`)
}

// Nouvelles méthodes pour le propriétaire du produit
const viewMyProductConversations = () => {
  // Rediriger vers la vue vendeur avec ce produit spécifique
  router.push(`/my-sales-conversations?product=${product.value.id}`)
}

const loadProductConversations = async () => {
  if (showProductConversations.value) {
    // Si déjà ouvert, fermer
    showProductConversations.value = false
    return
  }

  showProductConversations.value = true
  loadingConversations.value = true

  console.log('🔵 Chargement conversations pour produit:', product.value.id)

  try {
    const response = await api.get(`/conversations/product/${product.value.id}/conversations`)

    console.log('📡 Réponse conversations produit:', response.data)

    if (response.data.success) {
      productConversations.value = response.data.data.conversations || []

      console.log('✅ Conversations chargées:', {
        product: response.data.data.product?.title,
        conversations_count: productConversations.value.length,
        unread_total: response.data.data.unread_count
      })
    }
  } catch (error) {
    console.error('❌ Erreur chargement conversations produit:', error)
    productConversations.value = []
  } finally {
    loadingConversations.value = false
  }
}

const openConversation = (conversation) => {
  console.log('🔵 Ouverture conversation:', conversation.id)
  router.push(`/conversations/${conversation.id}`)
}

const editProduct = () => {
  router.push(`/products/${product.value.id}/edit`)
}

const toggleProductStatus = async () => {
  try {
    const newStatus = product.value.status === 'active' ? 'draft' : 'active'
    const response = await api.put(`/products/${product.value.id}/status`, {
      status: newStatus
    })

    if (response.data.success) {
      product.value.status = newStatus
    }
  } catch (error) {
    console.error('Erreur changement statut:', error)
  }
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
                            currency: 'XAF'
  }).format(price)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long'
  })
}

const formatMessageDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = (now - date) / (1000 * 60 * 60)

  if (diffInHours < 1) {
    return 'À l\'instant'
  } else if (diffInHours < 24) {
    return `il y a ${Math.floor(diffInHours)}h`
  } else {
    return date.toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
}

const calculateDiscount = (originalPrice, currentPrice) => {
  const discount = ((parseFloat(originalPrice) - parseFloat(currentPrice)) / parseFloat(originalPrice)) * 100
  return Math.round(discount)
}

const getUserInitials = (fullName) => {
  if (!fullName) return '?'

  // Diviser le nom en mots et prendre la première lettre de chaque mot
  const names = fullName.trim().split(' ')
  if (names.length === 1) {
    // Si un seul mot, prendre les 2 premières lettres
    return names[0].substring(0, 2).toUpperCase()
  } else {
    // Si plusieurs mots, prendre la première lettre de chaque mot (max 2)
    return names.slice(0, 2).map(name => name.charAt(0).toUpperCase()).join('')
  }
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    draft: 'bg-gray-100 text-gray-800',
    sold: 'bg-primary-100 text-primary-800',
    reserved: 'bg-gray-100 text-gray-800'
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
  console.log('🔵 ProductDetail onMounted')
  console.log('Route params:', route.params)
  console.log('Auth store state:', {
    isAuthenticated: authStore.isAuthenticated,
    user: authStore.user,
    token: authStore.token?.substring(0, 20) + '...'
  })
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



