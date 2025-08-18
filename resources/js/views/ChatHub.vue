<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Mes Discussions</h1>
        <p class="text-gray-600 mt-2">Gérez vos conversations par produit</p>
      </div>

      <!-- Navigation Tabs -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="flex border-b border-gray-200">
          <button
            @click="activeTab = 'buyer'"
            class="flex-1 px-6 py-4 text-sm font-medium transition-colors"
            :class="activeTab === 'buyer' 
              ? 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50' 
              : 'text-gray-500 hover:text-gray-700'"
          >
            <div class="flex items-center justify-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
              </svg>
              <span>Mes Achats</span>
              <span v-if="buyerUnreadCount > 0" class="bg-red-500 text-white text-xs rounded-full px-2 py-1">
                {{ buyerUnreadCount }}
              </span>
            </div>
          </button>
          
          <button
            @click="activeTab = 'seller'"
            class="flex-1 px-6 py-4 text-sm font-medium transition-colors"
            :class="activeTab === 'seller' 
              ? 'text-indigo-600 border-b-2 border-indigo-600 bg-indigo-50' 
              : 'text-gray-500 hover:text-gray-700'"
          >
            <div class="flex items-center justify-center space-x-2">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
              </svg>
              <span>Mes Ventes</span>
              <span v-if="sellerUnreadCount > 0" class="bg-red-500 text-white text-xs rounded-full px-2 py-1">
                {{ sellerUnreadCount }}
              </span>
            </div>
          </button>
        </div>
      </div>

      <!-- Content based on active tab -->
      <div class="transition-all duration-300">
        <!-- Buyer Tab - Products I'm interested in -->
        <div v-if="activeTab === 'buyer'">
          <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="text-gray-500 mt-4">Chargement de vos discussions d'achat...</p>
          </div>
          
          <div v-else-if="buyerConversations.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1l-4 4z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune discussion d'achat</h3>
            <p class="mt-1 text-sm text-gray-500">Vous n'avez encore contacté aucun vendeur.</p>
            <div class="mt-6">
              <RouterLink
                to="/products"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
              >
                Parcourir les produits
              </RouterLink>
            </div>
          </div>
          
          <div v-else class="grid gap-6">
            <div
              v-for="conversation in buyerConversations"
              :key="conversation.id"
              class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow cursor-pointer"
              @click="openConversation(conversation.id)"
            >
              <div class="flex items-center space-x-4">
                <!-- Product Image -->
                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                  <img 
                    v-if="conversation.product?.main_image_url"
                    :src="conversation.product.main_image_url" 
                    :alt="conversation.product.title"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="flex items-center justify-center h-full">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </div>
                </div>
                
                <!-- Product Info -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 truncate">{{ conversation.product?.title }}</h3>
                    <span class="text-lg font-bold text-indigo-600">{{ formatPrice(conversation.product?.price) }}</span>
                  </div>
                  <p class="text-sm text-gray-500">Vendeur: {{ conversation.seller?.name }}</p>
                  <div v-if="conversation.last_message" class="mt-2">
                    <p class="text-sm text-gray-700 truncate">
                      {{ conversation.last_message.sender_id === conversation.seller_id ? 'Vendeur: ' : 'Vous: ' }}
                      {{ conversation.last_message.content }}
                    </p>
                    <p class="text-xs text-gray-400">{{ formatDate(conversation.last_message.created_at) }}</p>
                  </div>
                </div>
                
                <!-- Unread indicator -->
                <div v-if="getUnreadCount(conversation) > 0" class="flex-shrink-0">
                  <span class="bg-red-500 text-white text-xs rounded-full px-2 py-1 font-bold">
                    {{ getUnreadCount(conversation) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Seller Tab - My products with buyers -->
        <div v-if="activeTab === 'seller'">
          <div v-if="loading" class="text-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="text-gray-500 mt-4">Chargement de vos discussions de vente...</p>
          </div>
          
          <div v-else-if="sellerProducts.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune discussion de vente</h3>
            <p class="mt-1 text-sm text-gray-500">Aucun acheteur n'a encore contacté vos produits.</p>
            <div class="mt-6">
              <RouterLink
                to="/products/create"
                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
              >
                Vendre un produit
              </RouterLink>
            </div>
          </div>
          
          <div v-else class="space-y-6">
            <div
              v-for="productData in sellerProducts"
              :key="productData.product.id"
              class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
            >
              <!-- Product Header -->
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-4">
                  <!-- Product Image -->
                  <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                    <img 
                      v-if="productData.product.main_image_url"
                      :src="productData.product.main_image_url" 
                      :alt="productData.product.title"
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ productData.product.title }}</h3>
                    <p class="text-sm text-gray-500">{{ formatPrice(productData.product.price) }}</p>
                  </div>
                </div>
                <span class="text-sm text-gray-500">{{ productData.conversations.length }} acheteur(s) intéressé(s)</span>
              </div>
              
              <!-- Conversations List -->
              <div class="space-y-3">
                <div
                  v-for="conversation in productData.conversations"
                  :key="conversation.id"
                  class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
                  @click="openConversation(conversation.id)"
                >
                  <div class="flex items-center space-x-3">
                    <!-- Buyer Avatar -->
                    <div class="w-8 h-8 bg-indigo-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                      {{ getInitials(conversation.buyer?.name) }}
                    </div>
                    <div>
                      <p class="font-medium text-gray-900">{{ conversation.buyer?.name }}</p>
                      <div v-if="conversation.last_message" class="text-sm text-gray-500">
                        {{ conversation.last_message.content?.substring(0, 50) }}...
                        <span class="text-xs text-gray-400 ml-2">{{ formatDate(conversation.last_message.created_at) }}</span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Unread indicator -->
                  <div v-if="getUnreadCount(conversation) > 0" class="bg-red-500 text-white text-xs rounded-full px-2 py-1 font-bold">
                    {{ getUnreadCount(conversation) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const router = useRouter()
const authStore = useAuthStore()

// State
const loading = ref(true)
const activeTab = ref('buyer')
const buyerConversations = ref([])
const sellerProducts = ref([])

// Computed
const buyerUnreadCount = computed(() => {
  return buyerConversations.value.reduce((total, conv) => total + getUnreadCount(conv), 0)
})

const sellerUnreadCount = computed(() => {
  return sellerProducts.value.reduce((total, productData) => {
    return total + productData.conversations.reduce((subtotal, conv) => subtotal + getUnreadCount(conv), 0)
  }, 0)
})

// Methods
const loadBuyerConversations = async () => {
  try {
    const response = await api.get('/conversations/my-product-discussions')
    if (response.data.success) {
      buyerConversations.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading buyer conversations:', error)
  }
}

const loadSellerProducts = async () => {
  try {
    const response = await api.get('/conversations/my-products-with-buyers')
    if (response.data.success) {
      sellerProducts.value = response.data.data
    }
  } catch (error) {
    console.error('Error loading seller products:', error)
  }
}

const loadData = async () => {
  loading.value = true
  await Promise.all([
    loadBuyerConversations(),
    loadSellerProducts()
  ])
  loading.value = false
}

const openConversation = (conversationId) => {
  router.push(`/conversations/${conversationId}`)
}

const getUnreadCount = (conversation) => {
  return conversation.unread_count || 0
}

const getInitials = (name) => {
  if (!name) return '?'
  return name.split(' ').map(word => word[0]).join('').toUpperCase().substring(0, 2)
}

const formatPrice = (price) => {
  if (!price) return ''
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF'
  }).format(price)
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = now - date
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 0) {
    return date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
  } else if (diffDays === 1) {
    return 'Hier'
  } else if (diffDays < 7) {
    return `Il y a ${diffDays} jours`
  } else {
    return date.toLocaleDateString('fr-FR')
  }
}

// Lifecycle
onMounted(() => {
  loadData()
})
</script>
