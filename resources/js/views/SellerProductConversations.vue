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
      >
        <!-- Product Header -->
        <div class="product-header" @click="toggleProduct(productData.product.id)">
          <div class="product-main-info">
            <img 
              :src="productData.product.main_image_url" 
              :alt="productData.product.title"
              class="product-image"
            />
            <div class="product-details">
              <h3 class="product-title">{{ productData.product.title }}</h3>
              <div class="product-meta">
                <span class="product-price">{{ productData.product.formatted_price }}</span>
                <span class="product-status" :class="getProductStatusClass(productData.product)">
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
              @click="openConversation(conversation)"
            >
              <!-- Buyer Info -->
              <div class="buyer-info">
                <img 
                  :src="conversation.buyer.avatar || '/default-avatar.png'" 
                  :alt="conversation.buyer.name"
                  class="buyer-avatar"
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

              <!-- Last Message -->
              <div class="last-exchange" v-if="conversation.last_message">
                <div class="message-preview">
                  <span class="sender-indicator" :class="{ 'from-buyer': conversation.last_message.sender_id === conversation.buyer_id }">
                    {{ conversation.last_message.sender_id === conversation.buyer_id ? 'Acheteur:' : 'Vous:' }}
                  </span>
                  {{ conversation.last_message.content }}
                </div>
                <div class="message-time">
                  {{ formatTime(conversation.last_message.created_at) }}
                </div>
              </div>

              <!-- Conversation Actions -->
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
                      <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
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

export default {
  name: 'SellerProductConversations',
  setup() {
    const router = useRouter()
    
    const loading = ref(true)
    const productsWithConversations = ref([])
    const expandedProducts = ref([])
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

    const markAllAsRead = async (productId) => {
      try {
        // Implementation to mark all messages as read for this product
        console.log('Marking all as read for product:', productId)
      } catch (error) {
        console.error('Erreur:', error)
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

    onMounted(() => {
      fetchProductsWithConversations()
    })

    return {
      loading,
      productsWithConversations,
      expandedProducts,
      showQuickActionsModal,
      selectedConversation,
      totalConversations,
      totalUnreadMessages,
      activeProducts,
      toggleProduct,
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
      markAllAsRead,
      markAsSold,
      updatePrice,
      sendOffer,
      blockBuyer
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

.product-image {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  object-fit: cover;
  margin-right: 16px;
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

.buyer-info {
  display: flex;
  align-items: center;
  margin-right: 16px;
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
  background: white;
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

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.quick-actions-modal {
  background: white;
  border-radius: 12px;
  padding: 24px;
  max-width: 400px;
  width: 90%;
}

.quick-actions-modal h4 {
  margin-bottom: 20px;
  color: #1a1a1a;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.action-btn {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border: none;
  background: #f8f9fa;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
}

.action-btn:hover {
  background: #e9ecef;
}

.action-btn.danger {
  color: #f44336;
}

.action-btn.danger:hover {
  background: #ffebee;
}

.action-icon {
  margin-right: 12px;
  font-size: 18px;
}

.btn {
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
  text-decoration: none;
  display: inline-block;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-small {
  padding: 6px 12px;
  font-size: 14px;
}

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
}
</style>