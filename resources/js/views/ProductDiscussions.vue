<template>
  <div class="product-discussions-page">
    <!-- Header -->
    <div class="header">
      <h1 class="title">Mes Discussions</h1>
      <p class="subtitle">Produits auxquels je m'intéresse</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Chargement de vos discussions...</p>
    </div>

    <!-- Empty State -->
    <div v-else-if="conversations.length === 0" class="empty-state">
      <div class="empty-icon">💬</div>
      <h3>Aucune discussion en cours</h3>
      <p>Vous n'avez pas encore commencé de discussions sur des produits.</p>
      <router-link to="/products" class="btn btn-primary">
        Découvrir des produits
      </router-link>
    </div>

    <!-- Conversations List -->
    <div v-else class="conversations-list">
      <div 
        v-for="conversation in conversations" 
        :key="conversation.id"
        class="conversation-card"
        :class="{
          'opacity-60 grayscale': isProductUnavailable(conversation.product),
          'cursor-not-allowed': isProductUnavailable(conversation.product)
        }"
        @click="openConversation(conversation)"
      >
        <!-- Product Image -->
        <div class="product-image">
          <img 
            :src="getProductImage(conversation.product)" 
            :alt="conversation.product.title"
            loading="lazy"
            @error="onImageError"
            @load="onImageLoad"
            :style="{ backgroundColor: '#f0f0f0', border: '1px solid red' }"
            :class="{ 'grayscale': isProductUnavailable(conversation.product) }"
          />
          <div v-if="getUnreadCount(conversation)" class="unread-badge">
            {{ getUnreadCount(conversation) }}
          </div>
          
          <!-- Unavailable Overlay -->
          <div 
            v-if="isProductUnavailable(conversation.product)" 
            class="product-unavailable-overlay"
          >
            <div class="unavailable-content">
              <div class="unavailable-text">{{ getUnavailableText(conversation.product) }}</div>
              <div class="unavailable-description">{{ getUnavailableDescription(conversation.product) }}</div>
            </div>
          </div>
        </div>

        <!-- Product & Seller Info -->
        <div class="conversation-content">
          <div class="product-info">
            <h3 class="product-title">{{ conversation.product.title }}</h3>
            <div class="seller-info">
              <span class="seller-name">avec @{{ conversation.seller.name }}</span>
              <span class="product-price">{{ conversation.product.formatted_price }}</span>
            </div>
            
            <!-- Product Status Badge -->
            <div v-if="isProductUnavailable(conversation.product)" class="product-status-badge">
              <span :class="['status-indicator', getProductStatusClass(conversation.product)]">
                {{ getProductStatusText(conversation.product) }}
              </span>
            </div>
          </div>

          <!-- Last Message -->
          <div class="last-message" v-if="conversation.last_message">
            <p class="message-preview">
                                {{ extractMessageContent(conversation.last_message.content) }}
            </p>
            <span class="message-time">
              {{ formatTime(conversation.last_message.created_at) }}
            </span>
          </div>

          <!-- Status Badge -->
          <div class="status-section">
            <span 
              :class="['status-badge', getStatusClass(conversation)]"
            >
              {{ getStatusText(conversation) }}
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="conversation-actions">
          <button 
            class="btn-icon"
            @click.stop="openConversation(conversation)"
            title="Ouvrir la conversation"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
            </svg>
          </button>
          
          <button 
            class="btn-icon"
            @click.stop="toggleFavorite(conversation.product)"
            :class="{ active: conversation.product.is_favorited_by_user }"
            title="Ajouter aux favoris"
          >
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="filters-section" v-if="conversations.length > 0">
      <h4>Filtrer par statut</h4>
      <div class="filter-buttons">
        <button 
          v-for="status in statusFilters" 
          :key="status.value"
          :class="['filter-btn', { active: selectedFilter === status.value }]"
          @click="selectedFilter = status.value"
        >
          {{ status.label }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { extractMessageContent } from '@/utils/messageUtils'

export default {
  name: 'ProductDiscussions',
  setup() {
    const router = useRouter()
    const authStore = useAuthStore()
    
    const loading = ref(true)
    const conversations = ref([])
    const selectedFilter = ref('all')
    
    const statusFilters = [
      { value: 'all', label: 'Tous' },
      { value: 'interested', label: 'Intéressé' },
      { value: 'negotiating', label: 'En négociation' },
      { value: 'purchased', label: 'Acheté' },
      { value: 'cancelled', label: 'Annulé' }
    ]

    const filteredConversations = computed(() => {
      if (selectedFilter.value === 'all') return conversations.value
      
      return conversations.value.filter(conv => {
        // Logique de filtrage basée sur le statut
        return conv.status === selectedFilter.value
      })
    })

    const fetchConversations = async () => {
      try {
        loading.value = true
        const response = await api.get('/conversations/my-product-discussions')
        conversations.value = response.data.data || []
        console.log('🔍 Debug fetchConversations - API response:', response.data)
        console.log('🔍 Debug fetchConversations - conversations:', conversations.value)
      } catch (error) {
        console.error('Erreur lors du chargement des conversations:', error)
      } finally {
        loading.value = false
      }
    }

    const openConversation = (conversation) => {
      router.push(`/conversations/${conversation.id}`)
    }

    const getUnreadCount = (conversation) => {
      return conversation.unread_count || 0
    }

    const getStatusClass = (conversation) => {
      const status = conversation.status || 'interested'
      const statusMap = {
        interested: 'status-interested',
        negotiating: 'status-negotiating',
        purchased: 'status-purchased',
        cancelled: 'status-cancelled'
      }
      return statusMap[status] || 'status-interested'
    }

    const getStatusText = (conversation) => {
      const status = conversation.status || 'interested'
      const statusMap = {
        interested: 'Intéressé',
        negotiating: 'En négociation',
        purchased: 'Acheté',
        cancelled: 'Annulé'
      }
      return statusMap[status] || 'Intéressé'
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

    const toggleFavorite = async (product) => {
      try {
        await api.post(`/products/${product.id}/favorite`)
        product.is_favorited_by_user = !product.is_favorited_by_user
      } catch (error) {
        console.error('Erreur lors de l\'ajout aux favoris:', error)
      }
    }

    const getProductImage = (product) => {
      console.log('🔍 Debug getProductImage - product:', product)
      
      if (!product) {
        console.log('❌ No product provided')
        return '/placeholder-product.jpg'
      }
      
      // If main_image_url is a string, use it
      if (typeof product.main_image_url === 'string') {
        console.log('✅ Using main_image_url:', product.main_image_url)
        return product.main_image_url
      }
      
      // If main_image is an object with filename, construct URL
      if (product.main_image && typeof product.main_image === 'object' && product.main_image.filename) {
        const url = `http://localhost:8000/api/v1/files/products/${product.main_image.filename}`
        console.log('✅ Using main_image object:', url)
        return url
      }
      
      // If main_image is a string (filename), construct URL
      if (typeof product.main_image === 'string') {
        const url = `http://localhost:8000/api/v1/files/products/${product.main_image}`
        console.log('✅ Using main_image string:', url)
        return url
      }
      
      // Fallback to placeholder
      console.log('❌ Fallback to placeholder')
      return '/placeholder-product.jpg'
    }

    const onImageError = (event) => {
      console.log('❌ Image failed to load:', event.target.src)
      if (event.target.src !== '/placeholder-product.jpg') {
        event.target.src = '/placeholder-product.jpg'
      }
    }

    const onImageLoad = (event) => {
      console.log('✅ Image loaded successfully:', event.target.src)
    }

    const isProductUnavailable = (product) => {
      return product.is_sold || product.is_deleted || !product.is_active
    }

    const getUnavailableText = (product) => {
      if (product.is_sold) return 'Vendu'
      if (product.is_deleted) return 'Supprimé'
      if (!product.is_active) return 'Désactivé'
      return 'Indisponible'
    }

    const getUnavailableDescription = (product) => {
      if (product.is_sold) return 'Ce produit a été vendu.'
      if (product.is_deleted) return 'Ce produit a été supprimé.'
      if (!product.is_active) return 'Ce produit est actuellement désactivé.'
      return 'Ce produit est actuellement indisponible.'
    }

    const getProductStatusClass = (product) => {
      if (product.is_sold) return 'status-sold'
      if (product.is_deleted) return 'status-deleted'
      if (!product.is_active) return 'status-inactive'
      return 'status-active'
    }

    const getProductStatusText = (product) => {
      if (product.is_sold) return 'Vendu'
      if (product.is_deleted) return 'Supprimé'
      if (!product.is_active) return 'Désactivé'
      return 'Actif'
    }

    onMounted(() => {
      fetchConversations()
    })

    return {
      loading,
      conversations: filteredConversations,
      selectedFilter,
      statusFilters,
      openConversation,
      getUnreadCount,
      getStatusClass,
      getStatusText,
      formatTime,
      toggleFavorite,
      getProductImage,
      onImageError,
      onImageLoad,
      extractMessageContent,
      isProductUnavailable,
      getUnavailableText,
      getUnavailableDescription,
      getProductStatusClass,
      getProductStatusText
    }
  }
}
</script>

<style scoped>
.product-discussions-page {
  padding: 20px;
  max-width: 800px;
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

.empty-state h3 {
  font-size: 20px;
  margin-bottom: 10px;
  color: #1a1a1a;
}

.empty-state p {
  color: #666;
  margin-bottom: 24px;
}

.conversations-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.conversation-card {
  display: flex;
  align-items: center;
  padding: 16px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: all 0.2s ease;
  border: 1px solid #f0f0f0;
}

.conversation-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.product-image {
  position: relative;
  width: 80px;
  height: 80px;
  border-radius: 8px;
  overflow: hidden;
  margin-right: 16px;
  flex-shrink: 0;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.unread-badge {
  position: absolute;
  top: -8px;
  right: -8px;
  background: #ff4757;
  color: white;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

.conversation-content {
  flex: 1;
  min-width: 0;
}

.product-info {
  margin-bottom: 12px;
}

.product-title {
  font-size: 16px;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 4px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.seller-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.seller-name {
  color: #666;
  font-size: 14px;
}

.product-price {
  font-weight: 600;
  color: #007bff;
  font-size: 16px;
}

.last-message {
  margin-bottom: 12px;
}

.message-preview {
  color: #666;
  font-size: 14px;
  margin-bottom: 4px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.message-time {
  color: #999;
  font-size: 12px;
}

.status-section {
  display: flex;
  align-items: center;
}

.status-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.status-interested {
  background: #e3f2fd;
  color: #1976d2;
}

.status-negotiating {
  background: #fff3e0;
  color: #f57c00;
}

.status-purchased {
  background: #e8f5e8;
  color: #4caf50;
}

.status-cancelled {
  background: #ffebee;
  color: #f44336;
}

.conversation-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-left: 16px;
}

.btn-icon {
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

.btn-icon:hover {
  background: #e9ecef;
  color: #007bff;
}

.btn-icon.active {
  background: #007bff;
  color: white;
}

.filters-section {
  margin-top: 32px;
  padding-top: 24px;
  border-top: 1px solid #eee;
}

.filters-section h4 {
  margin-bottom: 16px;
  color: #1a1a1a;
}

.filter-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 8px 16px;
  border: 1px solid #ddd;
  background: white;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
}

.filter-btn:hover {
  border-color: #007bff;
  color: #007bff;
}

.filter-btn.active {
  background: #007bff;
  color: white;
  border-color: #007bff;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.2s ease;
  text-decoration: none;
  display: inline-block;
  text-align: center;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover {
  background: #0056b3;
}

.product-unavailable-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
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

.status-indicator {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.status-sold {
  background: #ffebee;
  color: #f44336;
}

.status-deleted {
  background: #ffebee;
  color: #f44336;
}

.status-inactive {
  background: #f3e5f5;
  color: #7b1fa2;
}

.status-active {
  background: #e8f5e8;
  color: #4caf50;
}

/* Grisage des conversations avec produits indisponibles */
.conversation-card.opacity-60 {
  opacity: 0.6;
}

.conversation-card.grayscale {
  filter: grayscale(100%);
}

.conversation-card.cursor-not-allowed {
  cursor: not-allowed;
}

.product-image.grayscale {
  filter: grayscale(100%);
}

.product-status-badge {
  margin-top: 8px;
}

.status-indicator {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 500;
}

.status-sold {
  background: #ffebee;
  color: #f44336;
}

.status-deleted {
  background: #ffebee;
  color: #f44336;
}

.status-inactive {
  background: #f3e5f5;
  color: #7b1fa2;
}

.status-active {
  background: #e8f5e8;
  color: #4caf50;
}

@media (max-width: 768px) {
  .conversation-card {
    flex-direction: column;
    text-align: center;
  }
  
  .product-image {
    margin-right: 0;
    margin-bottom: 16px;
  }
  
  .conversation-actions {
    flex-direction: row;
    margin-left: 0;
    margin-top: 16px;
  }
}
</style>