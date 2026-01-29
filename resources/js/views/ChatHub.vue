<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-primary-50/20 to-primary-50/30 pb-16 sm:pb-0">
    <div class="max-w-7xl mx-auto px-2 sm:px-4 py-2 sm:py-3">
      <!-- Header - Ultra Compact -->
      <div class="mb-2 sm:mb-3">
        <h1 class="text-lg sm:text-xl font-bold text-gray-900">Discussions</h1>
        <p class="text-gray-600 mt-0.5 text-xs sm:text-sm">Vos conversations</p>
      </div>

      <!-- Navigation Tabs - Ultra Compact -->
      <div class="bg-white/80 backdrop-blur-sm rounded-md shadow-md border border-white/50 mb-2 sm:mb-3">
        <div class="flex border-b border-gray-200">
          <button
            @click="activeTab = 'buyer'"
            class="flex-1 px-2 py-2 text-xs font-medium transition-colors"
            :class="activeTab === 'buyer'
              ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50'
              : 'text-gray-500 hover:text-gray-700'"
          >
            <div class="flex items-center justify-center space-x-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
              </svg>
              <span>Achats</span>
              <span v-if="buyerUnreadCount > 0" class="bg-gray-500 text-white text-xs rounded-full px-1 py-0.5 min-w-[16px] h-4 flex items-center justify-center">
                {{ buyerUnreadCount }}
              </span>
            </div>
          </button>

          <button
            @click="activeTab = 'seller'"
            class="flex-1 px-2 py-2 text-xs font-medium transition-colors"
            :class="activeTab === 'seller'
              ? 'text-primary-600 border-b-2 border-primary-600 bg-primary-50'
              : 'text-gray-500 hover:text-gray-700'"
          >
            <div class="flex items-center justify-center space-x-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
              </svg>
              <span>Ventes</span>
              <span v-if="sellerUnreadCount > 0" class="bg-gray-500 text-white text-xs rounded-full px-1 py-0.5 min-w-[16px] h-4 flex items-center justify-center">
                {{ sellerUnreadCount }}
              </span>
            </div>
          </button>
        </div>
      </div>

      <!-- Content based on active tab - Ultra Compact -->
      <div class="transition-all duration-300">
        <!-- Buyer Tab - Products I'm interested in -->
        <div v-if="activeTab === 'buyer'">
          <div v-if="loading" class="text-center py-4 sm:py-6">
            <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-primary-600 mx-auto"></div>
            <p class="text-gray-500 mt-2 text-xs sm:text-sm">Chargement...</p>
          </div>

          <div v-else-if="buyerConversations.length === 0" class="text-center py-4 sm:py-6">
            <svg class="mx-auto h-6 w-6 sm:h-8 sm:w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-1l-4 4z"/>
            </svg>
            <h3 class="mt-1 text-xs font-medium text-gray-900">Aucune discussion</h3>
            <p class="mt-0.5 text-xs text-gray-500">Contactez des vendeurs.</p>
            <div class="mt-2 sm:mt-3">
              <RouterLink
                to="/products"
                class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
              >
                Parcourir
              </RouterLink>
            </div>
          </div>

          <div v-else class="grid gap-2 sm:gap-3">
            <div
              v-for="conversation in buyerConversations"
              :key="conversation.id"
              class="bg-white/80 backdrop-blur-sm rounded-md shadow-md border border-white/50 p-2 sm:p-3 hover:shadow-lg transition-shadow cursor-pointer"
              :class="{
                'opacity-60 grayscale': isProductUnavailable(conversation.product),
                'cursor-not-allowed': isProductUnavailable(conversation.product)
              }"
              @click="openConversation(conversation.id)"
            >
              <div class="flex items-center space-x-2 sm:space-x-3">
                <!-- Product Image - Ultra Compact -->
                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 rounded-md overflow-hidden flex-shrink-0 relative">
                  <img
                    v-if="conversation.product?.main_image_url"
                    :src="conversation.product.main_image_url"
                    :alt="conversation.product.title"
                    class="w-full h-full object-cover"
                    :class="{ 'grayscale': isProductUnavailable(conversation.product) }"
                  />
                  <div v-else class="flex items-center justify-center h-full">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                  </div>

                  <!-- Unavailable Overlay -->
                  <div
                    v-if="isProductUnavailable(conversation.product)"
                    class="absolute inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center rounded-md"
                  >
                    <div class="text-center text-white">
                      <div class="text-xs font-semibold">{{ getUnavailableText(conversation.product) }}</div>
                    </div>
                  </div>
                </div>

                <!-- Product Info - Ultra Compact -->
                <div class="flex-1 min-w-0">
                  <h3 class="text-sm font-semibold text-gray-900 truncate">{{ conversation.product?.title }}</h3>
                  <p class="text-xs text-gray-500">{{ formatPrice(conversation.product?.price) }}</p>

                  <!-- Product Status Badge -->
                  <div v-if="isProductUnavailable(conversation.product)" class="mt-1">
                    <span :class="['inline-block px-1.5 py-0.5 text-xs font-medium rounded-full', getProductStatusClass(conversation.product)]">
                      {{ getProductStatusText(conversation.product) }}
                    </span>
                  </div>
                </div>

                <!-- Unread indicator - Ultra Compact -->
                <div v-if="getUnreadCount(conversation) > 0" class="flex-shrink-0">
                  <span class="bg-gray-500 text-white text-xs rounded-full px-1.5 py-0.5 font-bold min-w-[20px] h-5 flex items-center justify-center">
                    {{ getUnreadCount(conversation) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Seller Tab - My products with buyers -->
        <div v-if="activeTab === 'seller'">
          <div v-if="loading" class="text-center py-4 sm:py-6">
            <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-primary-600 mx-auto"></div>
            <p class="text-gray-500 mt-2 text-xs sm:text-sm">Chargement...</p>
          </div>

          <div v-else-if="sellerProducts.length === 0" class="text-center py-4 sm:py-6">
            <svg class="mx-auto h-6 w-6 sm:h-8 sm:w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="mt-1 text-xs font-medium text-gray-900">Aucune discussion</h3>
            <p class="mt-0.5 text-xs text-gray-500">Aucun acheteur contacté.</p>
            <div class="mt-2 sm:mt-3">
              <RouterLink
                to="/products/create"
                class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700"
              >
                Vendre
              </RouterLink>
            </div>
          </div>

          <div v-else class="space-y-2 sm:space-y-3">
            <div
              v-for="productData in sellerProducts"
              :key="productData.product.id"
              class="bg-white/80 backdrop-blur-sm rounded-md shadow-md border border-white/50 p-2 sm:p-3"
            >
              <!-- Product Header with Dropdown Toggle -->
              <div class="product-header-dropdown" @click="toggleProductDropdown(productData.product.id)">
                <div class="flex items-center space-x-2">
                  <!-- Product Image - Ultra Compact -->
                  <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gray-100 rounded-md overflow-hidden flex-shrink-0">
                    <img
                      v-if="productData.product.main_image_url"
                      :src="productData.product.main_image_url"
                      :alt="productData.product.title"
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <div>
                    <h3 class="text-sm font-semibold text-gray-900 truncate">{{ productData.product.title }}</h3>
                    <p class="text-xs text-gray-500">{{ formatPrice(productData.product.price) }}</p>
                  </div>
                </div>

                <div class="flex items-center space-x-2">
                  <span class="text-xs text-gray-500">{{ productData.conversations.length }} intéressé(s)</span>

                  <!-- Expand/Collapse Button for Product -->
                  <button
                    class="product-expand-btn"
                    :class="{ expanded: expandedProducts.includes(productData.product.id) }"
                    @click.stop="toggleProductDropdown(productData.product.id)"
                  >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Expanded Product Conversations (Dropdown) -->
              <div
                v-if="expandedProducts.includes(productData.product.id)"
                class="product-conversations-expanded"
              >
                <!-- Conversations List -->
                <div class="space-y-2">
                  <div
                    v-for="conversation in productData.conversations"
                    :key="conversation.id"
                    class="conversation-item"
                    @click="openConversation(conversation.id)"
                  >
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded-md hover:bg-gray-100 transition-colors cursor-pointer">
                      <div class="flex items-center space-x-2">
                        <!-- Buyer Avatar -->
                        <div class="w-6 h-6 bg-primary-600 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                          {{ getInitials(conversation.buyer?.name) }}
                        </div>
                        <div>
                          <p class="text-xs font-medium text-gray-900">{{ conversation.buyer?.name }}</p>
                          <div v-if="conversation.last_message" class="text-xs text-gray-500 truncate max-w-[200px]">
                            {{ extractMessageContent(conversation.last_message.content, 40) }}
                            <span class="text-xs text-gray-400 ml-1">{{ formatMessageTime(conversation.last_message.created_at) }}</span>
                          </div>
                        </div>
                      </div>

                      <!-- Unread indicator -->
                      <div v-if="getUnreadCount(conversation) > 0" class="bg-gray-500 text-white text-xs rounded-full px-1.5 py-0.5 font-bold min-w-[20px] h-5 flex items-center justify-center">
                        {{ getUnreadCount(conversation) }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Product Summary Actions -->
                <div class="product-actions mt-3 pt-3 border-t border-gray-200">
                  <div class="flex justify-between items-center">
                    <span class="text-xs text-gray-600">
                      {{ productData.conversations.length }} conversation(s) active(s)
                    </span>
                    <button
                      class="btn btn-primary btn-small"
                      @click="viewProductDetails(productData.product)"
                    >
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="mr-2">
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                      Voir le produit
                    </button>
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
import { useRealtime } from '@/composables/useRealtime'
import smartUpdateService from '@/services/smartUpdateService'
import api from '@/services/api'
import { extractMessageContent } from '@/utils/messageUtils'
import { formatMessageTime } from '@/utils/timeUtils'

const router = useRouter()
const authStore = useAuthStore()
const { subscribeToRealtime, forceRealtimeUpdate } = useRealtime()

// State
const loading = ref(true)
const activeTab = ref('buyer')
const buyerConversations = ref([])
const sellerProducts = ref([])
const expandedConversations = ref([]) // New state for expanded conversations
const expandedProducts = ref([]) // New state for expanded products

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
      const newData = response.data.data

      // Utiliser le service de mise à jour intelligente
      smartUpdateService.smartUpdate(
        'buyer-conversations',
        newData,
        (newItems, updatedItems, removedItems, unchangedItems) => {
          // Mettre à jour l'interface intelligemment
          if (newItems.length > 0) {
            // Ajouter les nouvelles conversations
            buyerConversations.value.unshift(...newItems)
          }

          if (updatedItems.length > 0) {
            // Mettre à jour les conversations modifiées
            updatedItems.forEach(({ old, new: updated }) => {
              const index = buyerConversations.value.findIndex(c => c.id === updated.id)
              if (index !== -1) {
                buyerConversations.value[index] = updated
              }
            })
          }

          if (removedItems.length > 0) {
            // Supprimer les conversations supprimées
            removedItems.forEach(removed => {
              const index = buyerConversations.value.findIndex(c => c.id === removed.id)
              if (index !== -1) {
                buyerConversations.value.splice(index, 1)
              }
            })
          }

          // Les éléments inchangés restent en place
        },
        (changeType, items) => {
          // Callback pour les animations
          if (changeType === 'new') {
            // Animation placeholder for new items
          }
        }
      )
    }
  } catch (error) {

  }
}

const loadSellerProducts = async () => {
  try {
    const response = await api.get('/conversations/my-products-with-buyers')
    if (response.data.success) {
      const newData = response.data.data

      // Utiliser le service de mise à jour intelligente
      smartUpdateService.smartUpdate(
        'seller-products',
        newData,
        (newItems, updatedItems, removedItems, unchangedItems) => {
          // Mettre à jour l'interface intelligemment
          if (newItems.length > 0) {
            // Ajouter les nouveaux produits
            sellerProducts.value.unshift(...newItems)
          }

          if (updatedItems.length > 0) {
            // Mettre à jour les produits modifiés
            updatedItems.forEach(({ old, new: updated }) => {
              const index = sellerProducts.value.findIndex(p => p.product.id === updated.product.id)
              if (index !== -1) {
                sellerProducts.value[index] = updated
              }
            })
          }

          if (removedItems.length > 0) {
            // Supprimer les produits supprimés
            removedItems.forEach(removed => {
              const index = sellerProducts.value.findIndex(p => p.product.id === removed.product.id)
              if (index !== -1) {
                sellerProducts.value.splice(index, 1)
              }
            })
          }

          // Les éléments inchangés restent en place
        },
        (changeType, items) => {
          // Callback pour les animations
          if (changeType === 'new') {
            // Animation placeholder for new items
          }
        }
      )
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

const toggleConversationDetails = (conversationId) => {
  if (expandedConversations.value.includes(conversationId)) {
    expandedConversations.value = expandedConversations.value.filter(id => id !== conversationId)
  } else {
    expandedConversations.value.push(conversationId)
  }
}

const toggleProductDropdown = (productId) => {
  if (expandedProducts.value.includes(productId)) {
    expandedProducts.value = expandedProducts.value.filter(id => id !== productId)
  } else {
    expandedProducts.value.push(productId)
  }
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

const isProductUnavailable = (product) => {
  return product?.is_sold || product?.is_deleted || product?.is_inactive
}

const getUnavailableText = (product) => {
  if (product?.is_sold) return 'Vendu'
  if (product?.is_deleted) return 'Supprimé'
  if (product?.is_inactive) return 'Désactivé'
  return 'Indisponible'
}

const getProductStatusClass = (product) => {
  if (product?.is_sold) return 'bg-green-100 text-green-800'
  if (product?.is_deleted) return 'bg-red-100 text-red-800'
  if (product?.is_inactive) return 'bg-yellow-100 text-yellow-800'
  return 'bg-gray-100 text-gray-800'
}

const getProductStatusText = (product) => {
  if (product?.is_sold) return 'Vendu'
  if (product?.is_deleted) return 'Supprimé'
  if (product?.is_inactive) return 'Désactivé'
  return 'Indisponible'
}

const viewBuyerProfile = (buyer) => {
  if (buyer) {
    router.push(`/users/${buyer.id}`)
  }
}

const viewProductDetails = (product) => {
  router.push(`/products/${product.id}`)
}

const markConversationAsRead = async (conversationId) => {
  try {
    await api.post(`/conversations/${conversationId}/mark-as-read`)
    // Update unread count in the UI
    const conversation = sellerProducts.value.find(p => p.conversations.some(c => c.id === conversationId))?.conversations.find(c => c.id === conversationId)
    if (conversation) {
      conversation.unread_count = 0
    }
  } catch (error) {
    console.error('Error marking conversation as read:', error)
  }
}

// Lifecycle
onMounted(() => {
  loadData()

  // S'abonner aux mises à jour temps réel
  subscribeToRealtime('messages', async () => {
    // Utiliser le service intelligent au lieu de recharger tout
    await loadBuyerConversations()
    await loadSellerProducts()
  })

  // S'abonner aux mises à jour des likes/vues (moins fréquent)
  subscribeToRealtime('likes', async () => {
    // Utiliser le service intelligent au lieu de recharger tout
    await loadSellerProducts()
  })
})
</script>

<style scoped>
/* Styles pour le système de dropdown */
.buyer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 8px 12px;
  border-radius: 6px;
  transition: background-color 0.2s ease;
  cursor: pointer;
  border: 1px solid transparent;
}

.buyer-header:hover {
  background-color: #f1f5f9;
  border-color: #e2e8f0;
}

.expand-conversation-btn {
  width: 24px;
  height: 24px;
  border: none;
  background: #f8fafc;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #64748b;
  flex-shrink: 0;
  border: 1px solid #e2e8f0;
}

.expand-conversation-btn:hover {
  background: #e2e8f0;
  color: #475569;
  transform: scale(1.1);
}

.expand-conversation-btn.expanded {
  transform: rotate(180deg);
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.product-conversations-expanded {
  padding: 12px;
  background: #f8fafc;
  border-radius: 6px;
  margin: 8px;
  border-left: 3px solid #3b82f6;
  animation: slideDown 0.3s ease-out;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
    max-height: 0;
  }
  to {
    opacity: 1;
    transform: translateY(0);
    max-height: 300px;
  }
}

.conversation-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.conversation-item:hover {
  background-color: #f1f5f9;
}

/* Animations pour les mises à jour intelligentes */
.conversation-update-new {
  animation: slideInFromTop 0.3s ease-out;
}

.conversation-update-modified {
  animation: highlightUpdate 0.5s ease-out;
}

.conversation-update-removed {
  animation: slideOutToRight 0.3s ease-out;
}

@keyframes slideInFromTop {
  from {
    opacity: 0;
    transform: translateY(-20px);
    max-height: 0;
  }
  to {
    opacity: 1;
    transform: translateY(0);
    max-height: 200px;
  }
}

@keyframes highlightUpdate {
  0% {
    background-color: transparent;
  }
  50% {
    background-color: #fef3c7;
    border-left: 3px solid #f59e0b;
  }
  100% {
    background-color: transparent;
  }
}

@keyframes slideOutToRight {
  from {
    opacity: 1;
    transform: translateX(0);
    max-height: 200px;
  }
  to {
    opacity: 0;
    transform: translateX(100%);
    max-height: 0;
  }
}

/* Responsive design */
@media (max-width: 768px) {
  .conversation-stats {
    grid-template-columns: 1fr;
    gap: 8px;
  }

  .quick-actions-expanded {
    flex-direction: column;
    gap: 8px;
  }

  .product-conversations-expanded {
    margin: 4px;
    padding: 8px;
  }
}
</style>
