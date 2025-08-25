<template>
  <div class="seller-conversations-page">
    <!-- Header -->
    <div class="header">
      <h1 class="title">Mes Produits - Discussions</h1>
      <p class="subtitle">Gérez les conversations avec vos acheteurs potentiels</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-cards" v-if="!loading">
      <div class="stat-card">
        <div class="stat-number">{{ totalConversations }}</div>
        <div class="stat-label">Conversations totales</div>
      </div>
      <div class="stat-card">
        <div class="stat-number">{{ totalUnreadMessages }}</div>
        <div class="stat-label">Messages non lus</div>
      </div>
      <div class="stat-card">
        <div class="stat-number">{{ activeProducts }}</div>
        <div class="stat-label">Produits avec intérêt</div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Chargement de vos produits...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="productsWithConversations.length === 0" class="empty-state">
      <div class="empty-icon">📦</div>
      <h3>Aucune conversation sur vos produits</h3>
      <p>Aucun acheteur ne s'est encore manifesté pour vos produits.</p>
      <router-link to="/products/create" class="btn btn-primary">
        Ajouter un produit
      </router-link>
    </div>

    <!-- Products with Conversations -->
    <div v-else class="products-list">
      <div 
        v-for="productData in productsWithConversations" 
        :key="productData.product.id"
        class="product-conversations-card"
        :class="{
          'opacity-60 grayscale': isProductUnavailable(productData.product),
          'cursor-not-allowed': isProductUnavailable(productData.product)
        }"
      >
        <!-- Product Header -->
        <div class="product-header" @click="toggleProduct(productData.product.id)">
          <div class="product-main-info">
            <div class="product-image-container relative">
              <ProductImage
                :src="productData.product.main_image_url || productData.product.main_image"
                :alt="productData.product.title"
                :product-id="productData.product.id"
                fallback="/placeholder-product.jpg"
                image-classes="product-image"
                :class="{ 'grayscale': isProductUnavailable(productData.product) }"
              />
              
              <!-- Unavailable Overlay -->
              <div 
                v-if="isProductUnavailable(productData.product)" 
                class="product-unavailable-overlay"
              >
                <div class="unavailable-content">
                  <div class="unavailable-text">{{ getUnavailableText(productData.product) }}</div>
                  <div class="unavailable-description">{{ getUnavailableDescription(productData.product) }}</div>
                </div>
              </div>
            </div>
            
            <div class="product-details">
              <h3 class="product-title">{{ productData.product.title }}</h3>
              <div class="product-meta">
                <span class="product-price">{{ productData.product.formatted_price }}</span>
                <span class="product-status" :class="getProductStatusClass(productData.product)">
                  {{ getProductStatusText(productData.product) }}
                </span>
              </div>
              
              <!-- Product Status Badge -->
              <div v-if="isProductUnavailable(productData.product)" class="mt-2">
                <span :class="['inline-block px-2 py-1 text-xs font-medium rounded-full', getProductStatusClass(productData.product)]">
                  {{ getProductStatusText(productData.product) }}
                </span>
              </div>
            </div>
          </div>

          <div class="product-stats">
            <div class="stat-item">
              <span class="stat-number">{{ productData.conversation_count }}</span>
              <span class="stat-text">intéressé(s)</span>
            </div>
            <div class="stat-item" v-if="productData.unread_count > 0">
              <span class="stat-number unread">{{ productData.unread_count }}</span>
              <span class="stat-text">non lu(s)</span>
            </div>
            <button 
              class="expand-btn"
              :class="{ expanded: expandedProducts.includes(productData.product.id) }"
            >
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Conversations for this Product -->
        <div 
          v-if="expandedProducts.includes(productData.product.id)"
          class="conversations-section"
        >
          <div class="conversations-header">
            <h4>Acheteurs intéressés</h4>
            <button 
              class="btn btn-secondary btn-small"
              @click="markAllAsRead(productData.product.id)"
              v-if="productData.unread_count > 0"
            >
              Tout marquer comme lu
            </button>
          </div>

          <div class="buyers-list">
            <div 
              v-for="conversation in productData.conversations" 
              :key="conversation.id"
              class="buyer-conversation"
              :class="{ 'has-unread': getConversationUnreadCount(conversation) > 0 }"
            >
              <!-- Buyer Header - Clickable to expand conversation details -->
              <div class="buyer-header" @click="toggleConversationDetails(conversation.id)">
                <!-- Buyer Info -->
                <div class="buyer-info">
                  <img 
                    :src="conversation.buyer.avatar || generateDefaultAvatar(conversation.buyer.name, conversation.buyer.id)" 
                    :alt="conversation.buyer.name"
                    class="buyer-avatar"
                    @error="handleBuyerAvatarError($event, conversation.buyer)"
                  />
                  <div class="buyer-details">
                    <div class="buyer-name">@{{ conversation.buyer.name }}</div>
                    <div class="buyer-meta">
                      <span class="join-date">
                        Membre depuis {{ formatJoinDate(conversation.buyer.created_at) }}
                      </span>
                      <span class="rating" v-if="conversation.buyer.rating">
                        ⭐ {{ conversation.buyer.rating }}/5
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Expand/Collapse Button -->
                <button 
                  class="expand-conversation-btn"
                  :class="{ expanded: expandedConversations.includes(conversation.id) }"
                  @click.stop="toggleConversationDetails(conversation.id)"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"/>
                  </svg>
                </button>
              </div>

              <!-- Last Message Preview (Always visible) -->
              <div class="last-exchange" v-if="conversation.last_message">
                <div class="message-preview">
                  <span class="sender-indicator" :class="{ 'from-buyer': conversation.last_message.sender_id === conversation.buyer_id }">
                    {{ conversation.last_message.sender_id === conversation.buyer_id ? 'Acheteur:' : 'Vous:' }}
                  </span>
                  {{ extractMessageContent(conversation.last_message.content) }}
                </div>
                <div class="message-time">
                  {{ formatTime(conversation.last_message.created_at) }}
                </div>
              </div>

              <!-- Expanded Conversation Details (Dropdown) -->
              <div 
                v-if="expandedConversations.includes(conversation.id)"
                class="conversation-details-expanded"
              >
                <!-- Conversation Statistics -->
                <div class="conversation-stats">
                  <div class="stat-item">
                    <span class="stat-label">Messages</span>
                    <span class="stat-value">{{ conversation.message_count || 0 }}</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-label">Dernière activité</span>
                    <span class="stat-value">{{ formatLastActivity(conversation.last_message?.created_at) }}</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-label">Statut</span>
                    <span class="stat-value" :class="getConversationStatusClass(conversation)">
                      {{ getConversationStatusText(conversation) }}
                    </span>
                  </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions-expanded">
                  <button 
                    class="btn btn-primary btn-small"
                    @click="openConversation(conversation)"
                  >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="mr-2">
                      <path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/>
                    </svg>
                    Répondre
                  </button>
                  
                  <button 
                    class="btn btn-secondary btn-small"
                    @click="viewBuyerProfile(conversation.buyer)"
                  >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="mr-2">
                      <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                    Profil
                  </button>

                  <button 
                    class="btn btn-outline btn-small"
                    @click="showQuickActions(conversation)"
                  >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="mr-2">
                      <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                    Actions
                  </button>
                </div>

                <!-- Unread Messages Info -->
                <div v-if="getConversationUnreadCount(conversation) > 0" class="unread-info">
                  <div class="unread-badge">
                    {{ getConversationUnreadCount(conversation) }} message(s) non lu(s)
                  </div>
                  <button 
                    class="btn btn-text btn-small"
                    @click="markConversationAsRead(conversation.id)"
                  >
                    Marquer comme lu
                  </button>
                </div>
              </div>

              <!-- Conversation Actions (Compact - Always visible) -->
              <div class="conversation-actions">
                <div class="status-info">
                  <span 
                    :class="['conversation-status', getConversationStatusClass(conversation)]"
                  >
                    {{ getConversationStatusText(conversation) }}
                  </span>
                  <div v-if="getConversationUnreadCount(conversation) > 0" class="unread-indicator">
                    {{ getConversationUnreadCount(conversation) }} nouveau(x)
                  </div>
                </div>

                <div class="quick-actions">
                  <button 
                    class="btn-icon"
                    @click.stop="openConversation(conversation)"
                    title="Répondre"
                  >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M10 9V5l-7 7 7 7v-4.1c5 0 8.5 1.6 11 5.1-1-5-4-10-11-11z"/>
                    </svg>
                  </button>
                  
                  <button 
                    class="btn-icon"
                    @click.stop="viewBuyerProfile(conversation.buyer)"
                    title="Voir profil"
                  >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                    </svg>
                  </button>

                  <button 
                    class="btn-icon"
                    @click.stop="showQuickActions(conversation)"
                    title="Plus d'actions"
                  >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions Modal -->
    <div v-if="showQuickActionsModal" class="modal-overlay" @click="closeQuickActions">
      <div class="quick-actions-modal" @click.stop>
        <h4>Actions rapides</h4>
        <div class="action-buttons">
          <button class="action-btn" @click="markAsSold">
            <span class="action-icon">✅</span>
            Marquer comme vendu
          </button>
          <button class="action-btn" @click="updatePrice">
            <span class="action-icon">💰</span>
            Modifier le prix
          </button>
          <button class="action-btn" @click="sendOffer">
            <span class="action-icon">🤝</span>
            Faire une offre
          </button>
          <button class="action-btn danger" @click="blockBuyer">
            <span class="action-icon">🚫</span>
            Bloquer cet acheteur
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { extractMessageContent } from '@/utils/messageUtils'
import ProductImage from '@/components/ui/ProductImage.vue'

// Fonctions de génération d'avatar dynamique
const generateDefaultAvatar = (name, id) => {
  const initials = getUserInitials(name)
  const color = generateUserColor(name || id?.toString() || 'User')
  
  const svg = `
    <svg width="40" height="40" xmlns="http://www.w3.org/2000/svg">
      <rect width="40" height="40" fill="${color}"/>
      <text x="50%" y="50%" text-anchor="middle" dy="0.35em" fill="white" font-family="Arial, sans-serif" font-size="16" font-weight="bold">
        ${initials}
      </text>
    </svg>
  `
  
  return 'data:image/svg+xml;base64,' + btoa(svg)
}

const getUserInitials = (name) => {
  if (!name) return '?'
  const cleanName = name.trim()
  const names = cleanName.split(' ')
  if (names.length === 1) {
    return names[0].charAt(0).toUpperCase()
  } else {
    return names[0].charAt(0).toUpperCase() + names[names.length - 1].charAt(0).toUpperCase()
  }
}

const generateUserColor = (name) => {
  if (!name) return '#6B7280'
  let hash = 0
  for (let i = 0; i < name.length; i++) {
    hash = name.charCodeAt(i) + ((hash << 5) - hash)
  }
  const colors = ['#EF4444', '#F59E0B', '#10B981', '#3B82F6', '#6366F1', '#8B5CF6', '#EC4899', '#06B6D4']
  return colors[Math.abs(hash) % colors.length]
}

export default {
  name: 'SellerProductConversations',
  components: {
    ProductImage
  },
  setup() {
    const router = useRouter()
    
    const loading = ref(true)
    const productsWithConversations = ref([])
    const expandedProducts = ref([])
    const expandedConversations = ref([]) // New state for expanded conversations
    const showQuickActionsModal = ref(false)
    const selectedConversation = ref(null)

    const totalConversations = computed(() => {
      return productsWithConversations.value.reduce((total, product) => {
        return total + product.conversation_count
      }, 0)
    })

    const totalUnreadMessages = computed(() => {
      return productsWithConversations.value.reduce((total, product) => {
        return total + product.unread_count
      }, 0)
    })

    const activeProducts = computed(() => {
      return productsWithConversations.value.length
    })

    const fetchProductsWithConversations = async () => {
      try {
        loading.value = true
        const response = await api.get('/conversations/my-products-with-buyers')
        productsWithConversations.value = response.data.data || []
        
        // Auto-expand first product if only one
        if (productsWithConversations.value.length === 1) {
          expandedProducts.value = [productsWithConversations.value[0].product.id]
        }
      } catch (error) {
        console.error('Erreur lors du chargement:', error)
      } finally {
        loading.value = false
      }
    }

    const toggleProduct = (productId) => {
      const index = expandedProducts.value.indexOf(productId)
      if (index > -1) {
        expandedProducts.value.splice(index, 1)
      } else {
        expandedProducts.value.push(productId)
      }
    }

    const toggleConversationDetails = (conversationId) => {
      const index = expandedConversations.value.indexOf(conversationId)
      if (index > -1) {
        expandedConversations.value.splice(index, 1)
      } else {
        expandedConversations.value.push(conversationId)
      }
    }

    const openConversation = (conversation) => {
      router.push(`/conversations/${conversation.id}`)
    }

    const viewBuyerProfile = (buyer) => {
      router.push(`/users/${buyer.id}`)
    }

    const showQuickActions = (conversation) => {
      selectedConversation.value = conversation
      showQuickActionsModal.value = true
    }

    const closeQuickActions = () => {
      showQuickActionsModal.value = false
      selectedConversation.value = null
    }

    const getProductStatusClass = (product) => {
      const statusMap = {
        active: 'status-active',
        sold: 'status-sold',
        reserved: 'status-reserved'
      }
      return statusMap[product.status] || 'status-active'
    }

    const getProductStatusText = (product) => {
      const statusMap = {
        active: 'Actif',
        sold: 'Vendu',
        reserved: 'Réservé'
      }
      return statusMap[product.status] || 'Actif'
    }

    const getConversationStatusClass = (conversation) => {
      // Logic to determine conversation status
      return 'status-active'
    }

    const getConversationStatusText = (conversation) => {
      return 'En cours'
    }

    const getConversationUnreadCount = (conversation) => {
      return conversation.unread_count || 0
    }

    const formatTime = (timestamp) => {
      const date = new Date(timestamp)
      const now = new Date()
      const diffInHours = (now - date) / (1000 * 60 * 60)
      
      if (diffInHours < 1) {
        return 'À l\'instant'
      } else if (diffInHours < 24) {
        return `il y a ${Math.floor(diffInHours)}h`
      } else {
        return `il y a ${Math.floor(diffInHours / 24)}j`
      }
    }

    const formatJoinDate = (timestamp) => {
      const date = new Date(timestamp)
      const options = { year: 'numeric', month: 'long' }
      return date.toLocaleDateString('fr-FR', options)
    }

    const formatLastActivity = (timestamp) => {
      if (!timestamp) return 'Aucune activité'
      const date = new Date(timestamp)
      const now = new Date()
      const diffInHours = (now - date) / (1000 * 60 * 60)

      if (diffInHours < 1) {
        return 'À l\'instant'
      } else if (diffInHours < 24) {
        return `il y a ${Math.floor(diffInHours)}h`
      } else {
        return `il y a ${Math.floor(diffInHours / 24)}j`
      }
    }

    const markAllAsRead = async (productId) => {
      try {
        // Implementation to mark all messages as read for this product
        console.log('Marking all as read for product:', productId)
      } catch (error) {
        console.error('Erreur:', error)
      }
    }

    const markConversationAsRead = async (conversationId) => {
      try {
        await api.post(`/conversations/${conversationId}/mark-as-read`)
        // Refresh the conversation data to update unread count
        const productIndex = productsWithConversations.value.findIndex(
          (product) => product.conversations.some((conv) => conv.id === conversationId)
        )
        if (productIndex !== -1) {
          const productData = productsWithConversations.value[productIndex]
          const conversationIndex = productData.conversations.findIndex(
            (conv) => conv.id === conversationId
          )
          if (conversationIndex !== -1) {
            productData.conversations[conversationIndex].unread_count = 0
          }
        }
      } catch (error) {
        console.error('Erreur lors du marquage comme lu:', error)
      }
    }

    // Quick actions
    const markAsSold = () => {
      console.log('Mark as sold')
      closeQuickActions()
    }

    const updatePrice = () => {
      console.log('Update price')
      closeQuickActions()
    }

    const sendOffer = () => {
      console.log('Send offer')
      closeQuickActions()
    }

    const blockBuyer = () => {
      console.log('Block buyer')
      closeQuickActions()
    }

    const isProductUnavailable = (product) => {
      return product.status === 'sold' || product.status === 'reserved' || product.status === 'inactive'
    }

    const getUnavailableText = (product) => {
      if (product.status === 'sold') return 'Vendu'
      if (product.status === 'reserved') return 'Réservé'
      if (product.status === 'inactive') return 'Désactivé'
      return 'Indisponible'
    }

    const getUnavailableDescription = (product) => {
      if (product.status === 'sold') return 'Ce produit a déjà été vendu.'
      if (product.status === 'reserved') return 'Ce produit est déjà réservé.'
      if (product.status === 'inactive') return 'Ce produit est actuellement désactivé.'
      return 'Ce produit est actuellement indisponible.'
    }

    onMounted(() => {
      fetchProductsWithConversations()
    })

    return {
      loading,
      productsWithConversations,
      expandedProducts,
      expandedConversations, // Expose expandedConversations
      showQuickActionsModal,
      selectedConversation,
      totalConversations,
      totalUnreadMessages,
      activeProducts,
      toggleProduct,
      toggleConversationDetails, // Add toggleConversationDetails to return
      openConversation,
      viewBuyerProfile,
      showQuickActions,
      closeQuickActions,
      getProductStatusClass,
      getProductStatusText,
      getConversationStatusClass,
      getConversationStatusText,
      getConversationUnreadCount,
      formatTime,
      formatJoinDate,
      formatLastActivity, // Add formatLastActivity to return
      markAllAsRead,
      markConversationAsRead, // Add markConversationAsRead to return
      markAsSold,
      updatePrice,
      sendOffer,
      blockBuyer,
      generateDefaultAvatar,
      handleBuyerAvatarError: (event, buyer) => {
        if (event.target.src !== generateDefaultAvatar(buyer.name, buyer.id)) {
          event.target.src = generateDefaultAvatar(buyer.name, buyer.id)
        }
      },
      isProductUnavailable,
      getUnavailableText,
      getUnavailableDescription
    }
  }
}
</script>

<style scoped>
.seller-conversations-page {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}

.header {
  margin-bottom: 30px;
  text-align: center;
}

.title {
  font-size: 28px;
  font-weight: bold;
  color: #1a1a1a;
  margin-bottom: 8px;
}

.subtitle {
  color: #666;
  font-size: 16px;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  text-align: center;
}

.stat-number {
  font-size: 32px;
  font-weight: bold;
  color: #007bff;
  margin-bottom: 8px;
}

.stat-label {
  color: #666;
  font-size: 14px;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 40px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #f3f3f3;
  border-top: 3px solid #007bff;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.products-list {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.product-conversations-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
}

.product-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  transition: background-color 0.2s ease;
}

.product-header:hover {
  background-color: #f8f9fa;
}

.product-main-info {
  display: flex;
  align-items: center;
  flex: 1;
}

.product-image-container {
  position: relative;
  width: 60px;
  height: 60px;
  border-radius: 8px;
  overflow: hidden;
  margin-right: 16px;
}

.product-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-unavailable-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  border-radius: 8px;
  z-index: 1;
}

.unavailable-content {
  text-align: center;
}

.unavailable-text {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 8px;
}

.unavailable-description {
  font-size: 14px;
  color: #ccc;
}

.product-details {
  flex: 1;
}

.product-title {
  font-size: 18px;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 4px;
}

.product-meta {
  display: flex;
  align-items: center;
  gap: 12px;
}

.product-price {
  font-weight: 600;
  color: #007bff;
  font-size: 16px;
}

.product-status {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.status-active {
  background: #e8f5e8;
  color: #4caf50;
}

.status-sold {
  background: #ffebee;
  color: #f44336;
}

.status-reserved {
  background: #fff3e0;
  color: #f57c00;
}

.product-stats {
  display: flex;
  align-items: center;
  gap: 20px;
}

.stat-item {
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 18px;
  font-weight: bold;
  color: #1a1a1a;
}

.stat-number.unread {
  color: #ff4757;
}

.stat-text {
  font-size: 12px;
  color: #666;
}

.expand-btn {
  width: 40px;
  height: 40px;
  border: none;
  background: #f8f9fa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #666;
}

.expand-btn:hover {
  background: #e9ecef;
}

.expand-btn.expanded {
  transform: rotate(180deg);
}

.conversations-section {
  padding: 20px;
}

.conversations-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.conversations-header h4 {
  margin: 0;
  color: #1a1a1a;
}

.buyers-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.buyer-conversation {
  display: flex;
  align-items: center;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.buyer-conversation:hover {
  background: #e9ecef;
}

.buyer-conversation.has-unread {
  border-left: 4px solid #007bff;
  background: #e3f2fd;
}

/* Styles pour le système de dropdown */
.buyer-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 8px 12px;
  border-radius: 8px;
  transition: background-color 0.2s ease;
  cursor: pointer;
  border: 1px solid transparent;
}

.buyer-header:hover {
  background-color: #f8f9fa;
  border-color: #e9ecef;
}

.expand-conversation-btn {
  width: 28px;
  height: 28px;
  border: none;
  background: #f8f9fa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #666;
  flex-shrink: 0;
  border: 1px solid #e9ecef;
}

.expand-conversation-btn:hover {
  background: #e9ecef;
  color: #495057;
  transform: scale(1.1);
}

.expand-conversation-btn.expanded {
  transform: rotate(180deg);
  background: #007bff;
  color: white;
  border-color: #007bff;
}

.conversation-details-expanded {
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
  margin-top: 12px;
  border-left: 3px solid #007bff;
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
    max-height: 500px;
  }
}

.conversation-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 16px;
  margin-bottom: 20px;
}

.stat-item {
  text-align: center;
  padding: 12px;
  background: white;
  border-radius: 6px;
  border: 1px solid #e9ecef;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-label {
  display: block;
  font-size: 11px;
  color: #6c757d;
  margin-bottom: 4px;
  text-transform: uppercase;
  font-weight: 500;
  letter-spacing: 0.5px;
}

.stat-value {
  display: block;
  font-size: 18px;
  font-weight: 700;
  color: #212529;
}

.quick-actions-expanded {
  display: flex;
  gap: 8px;
  margin-bottom: 16px;
  flex-wrap: wrap;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  min-height: 36px;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-secondary:hover {
  background: #545b62;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
}

.btn-outline {
  border: 1px solid #007bff;
  color: #007bff;
  background: white;
}

.btn-outline:hover {
  background: #007bff;
  color: white;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
}

.btn-text {
  color: #007bff;
  background: none;
  border: none;
  padding: 8px 12px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  text-decoration: underline;
}

.btn-text:hover {
  color: #0056b3;
  background: #f8f9fa;
  border-radius: 4px;
}

.btn-small {
  padding: 6px 12px;
  font-size: 13px;
  min-height: 32px;
}

.unread-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  background: white;
  border-radius: 6px;
  border: 1px solid #e9ecef;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.unread-badge {
  background: #fff3cd;
  color: #856404;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid #ffeaa7;
}

/* Amélioration de l'apparence générale */
.buyer-conversation {
  border: 1px solid #e9ecef;
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 16px;
  background: white;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.buyer-conversation:hover {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  transform: translateY(-1px);
}

.buyer-conversation.has-unread {
  border-left: 4px solid #ffc107;
  background: #fffbf0;
}

/* Styles pour les éléments de base */
.buyer-info {
  display: flex;
  align-items: center;
  margin-right: 16px;
  flex: 1;
}

.buyer-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
  margin-right: 12px;
}

.buyer-details {
  flex: 1;
}

.buyer-name {
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 4px;
}

.buyer-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 12px;
  color: #666;
}

.last-exchange {
  flex: 1;
  margin-right: 16px;
  cursor: pointer;
  padding: 8px 12px;
  border-radius: 6px;
  transition: background-color 0.2s ease;
}

.last-exchange:hover {
  background-color: #f8f9fa;
}

.message-preview {
  font-size: 14px;
  color: #666;
  margin-bottom: 4px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.sender-indicator {
  font-weight: 600;
  margin-right: 4px;
}

.sender-indicator.from-buyer {
  color: #007bff;
}

.message-time {
  font-size: 12px;
  color: #999;
}

/* Styles pour les actions de conversation */
.conversation-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.status-info {
  text-align: right;
}

.conversation-status {
  display: block;
  font-size: 12px;
  color: #666;
  margin-bottom: 4px;
}

.unread-indicator {
  font-size: 12px;
  color: #ff4757;
  font-weight: 600;
}

.quick-actions {
  display: flex;
  gap: 8px;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border: none;
  background: #f8f9fa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  color: #666;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.btn-icon:hover {
  background: #007bff;
  color: white;
}

/* Responsive design */
@media (max-width: 768px) {
  .product-header {
    flex-direction: column;
    align-items: stretch;
  }
  
  .product-stats {
    margin-top: 16px;
    justify-content: space-between;
  }
  
  .buyer-conversation {
    flex-direction: column;
    align-items: stretch;
  }
  
  .conversation-actions {
    margin-top: 12px;
    justify-content: space-between;
  }

  .conversation-stats {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  
  .quick-actions-expanded {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
    justify-content: center;
  }
  
  .unread-info {
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }
  
  .buyer-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }
  
  .expand-conversation-btn {
    align-self: flex-end;
  }
}
</style>