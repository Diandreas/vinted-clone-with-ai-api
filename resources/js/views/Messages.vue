<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Messages & Conversations</h1>
        <p class="text-lg text-gray-600">Gérez vos discussions centrées sur les produits</p>
      </div>

      <!-- Navigation Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Buyer View - My Product Discussions -->
        <div 
          class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow cursor-pointer"
          @click="goToProductDiscussions"
        >
          <div class="p-6">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
              </div>
              <div>
                <h3 class="text-xl font-semibold text-gray-900">Mes Discussions</h3>
                <p class="text-sm text-gray-500">Mode Acheteur</p>
              </div>
            </div>
            <p class="text-gray-600 mb-4">
              Consultez vos conversations sur les produits qui vous intéressent. 
              Suivez vos négociations et l'état de vos achats potentiels.
            </p>
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4 text-sm text-gray-500">
                <span v-if="buyerStats.loading">Chargement...</span>
                <template v-else>
                  <span>{{ buyerStats.totalDiscussions }} discussions</span>
                  <span v-if="buyerStats.unreadCount > 0" class="text-red-600 font-medium">
                    {{ buyerStats.unreadCount }} non lus
                  </span>
                </template>
              </div>
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Seller View - My Products with Buyers -->
        <div 
          class="bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-shadow cursor-pointer"
          @click="goToSellerConversations"
        >
          <div class="p-6">
            <div class="flex items-center mb-4">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
              <div>
                <h3 class="text-xl font-semibold text-gray-900">Mes Ventes</h3>
                <p class="text-sm text-gray-500">Mode Vendeur</p>
              </div>
            </div>
            <p class="text-gray-600 mb-4">
              Gérez les conversations sur vos produits. Voyez qui s'intéresse à quoi 
              et répondez aux acheteurs potentiels.
            </p>
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4 text-sm text-gray-500">
                <span v-if="sellerStats.loading">Chargement...</span>
                <template v-else>
                  <span>{{ sellerStats.activeProducts }} produits</span>
                  <span v-if="sellerStats.unreadCount > 0" class="text-red-600 font-medium">
                    {{ sellerStats.unreadCount }} non lus
                  </span>
                </template>
              </div>
              <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Transition vers nouveau système -->
      <div class="bg-gradient-to-r from-green-50 to-blue-50 rounded-xl p-6 mb-6">
        <div class="flex items-start">
          <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3 mt-0.5">
            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <div>
            <h4 class="text-sm font-medium text-green-900 mb-1">Système de conversations amélioré</h4>
            <p class="text-sm text-green-700">
              Vos conversations sont maintenant organisées par produit pour une meilleure expérience d'achat et de vente. 
              Chaque produit a sa propre discussion dédiée.
            </p>
            <div class="mt-3 flex items-center space-x-4 text-xs text-green-600">
              <span>✨ Organisation par produit</span>
              <span>🎯 Suivi des négociations</span>
              <span>📱 Interface optimisée</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Guide d'utilisation -->
      <div class="bg-indigo-50 rounded-xl p-6">
        <div class="flex items-start">
          <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center mr-3 mt-0.5">
            <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
          </div>
          <div>
            <h4 class="text-sm font-medium text-indigo-900 mb-1">Comment ça marche</h4>
            <p class="text-sm text-indigo-700 mb-3">
              Chaque produit a maintenant sa propre messagerie dédiée pour une expérience plus organisée.
            </p>
            <div class="space-y-2 text-xs text-indigo-600">
              <div class="flex items-center">
                <span class="w-4 h-4 bg-indigo-200 rounded-full flex items-center justify-center text-indigo-800 font-bold mr-2 text-xs">1</span>
                <span>Cliquez sur "Message" sur un produit qui vous intéresse</span>
              </div>
              <div class="flex items-center">
                <span class="w-4 h-4 bg-indigo-200 rounded-full flex items-center justify-center text-indigo-800 font-bold mr-2 text-xs">2</span>
                <span>Envoyez votre message directement au vendeur</span>
              </div>
              <div class="flex items-center">
                <span class="w-4 h-4 bg-indigo-200 rounded-full flex items-center justify-center text-indigo-800 font-bold mr-2 text-xs">3</span>
                <span>Suivez toutes vos négociations dans les sections ci-dessus</span>
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
const auth = useAuthStore()

// Stats
const buyerStats = ref({
  loading: true,
  totalDiscussions: 0,
  unreadCount: 0
})

const sellerStats = ref({
  loading: true,
  activeProducts: 0,
  unreadCount: 0
})

// Conversations stats only

// Navigation methods
const goToProductDiscussions = () => {
  router.push({ name: 'product-discussions' })
}

const goToSellerConversations = () => {
  router.push({ name: 'seller-conversations' })
}

// Load stats
const loadBuyerStats = async () => {
  try {
    const response = await api.get('/conversations/my-product-discussions')
    const discussions = response.data.data || []
    buyerStats.value = {
      loading: false,
      totalDiscussions: discussions.length,
      unreadCount: discussions.reduce((sum, conv) => sum + (conv.unread_count || 0), 0)
    }
  } catch (error) {
    console.error('Erreur chargement stats acheteur:', error)
    buyerStats.value.loading = false
  }
}

const loadSellerStats = async () => {
  try {
    const response = await api.get('/conversations/my-products-with-buyers')
    const products = response.data.data || []
    sellerStats.value = {
      loading: false,
      activeProducts: products.length,
      unreadCount: products.reduce((sum, product) => sum + (product.unread_count || 0), 0)
    }
  } catch (error) {
    console.error('Erreur chargement stats vendeur:', error)
    sellerStats.value.loading = false
  }
}

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
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

onMounted(() => {
  loadBuyerStats()
  loadSellerStats()
})
</script>

<style scoped>
/* Add any additional styles if needed */
</style>