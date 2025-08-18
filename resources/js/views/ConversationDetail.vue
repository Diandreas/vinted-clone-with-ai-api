<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Loading -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Conversation Detail -->
    <div v-else-if="conversation" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <!-- Product Image -->
            <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden">
              <img 
                v-if="conversation.product?.main_image_url"
                :src="conversation.product.main_image_url" 
                :alt="conversation.product.title"
                class="w-full h-full object-cover"
              />
              <div v-else class="flex items-center justify-center h-full">
                <ImageIcon class="w-6 h-6 text-gray-400" />
              </div>
            </div>
            
            <!-- Product & Participant Info -->
            <div>
              <h1 class="text-xl font-semibold text-gray-900">{{ conversation.product?.title }}</h1>
              <p class="text-sm text-gray-500">
                Conversation avec {{ otherParticipant?.name }}
              </p>
              <p class="text-xs text-gray-400">{{ formatPrice(conversation.product?.price) }}</p>
            </div>
          </div>
          
          <!-- Back Button -->
          <button
            @click="goBack"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors"
          >
            ← Retour
          </button>
        </div>
      </div>

      <!-- Messages -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-96">
        <!-- Messages List -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="messagesContainer">
          <div
            v-for="message in messages"
            :key="message.id"
            :class="[
              'flex',
              message.sender_id === currentUserId ? 'justify-end' : 'justify-start'
            ]"
          >
            <div
              :class="[
                'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
                message.sender_id === currentUserId
                  ? 'bg-indigo-600 text-white'
                  : 'bg-gray-100 text-gray-900'
              ]"
            >
              <p class="text-sm">{{ message.content }}</p>
              <p class="text-xs opacity-75 mt-1">
                {{ formatMessageDate(message.created_at) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Message Input -->
        <div class="p-4 border-t border-gray-200">
          <form @submit.prevent="sendMessage" class="flex space-x-3">
            <input
              v-model="newMessage"
              type="text"
              placeholder="Tapez votre message..."
              class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              :disabled="sendingMessage"
            />
            <button
              type="submit"
              :disabled="!newMessage.trim() || sendingMessage"
              class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {{ sendingMessage ? 'Envoi...' : 'Envoyer' }}
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <AlertTriangleIcon class="mx-auto h-12 w-12 text-red-400" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">Conversation non trouvée</h3>
        <p class="mt-1 text-sm text-gray-500">Cette conversation n'existe pas ou vous n'y avez pas accès.</p>
        <div class="mt-6">
          <button
            @click="goBack"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
          >
            Retour aux discussions
          </button>
        </div>
      </div>
    </div>
  </div>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { ImageIcon, AlertTriangleIcon } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

// State
const loading = ref(true)
const conversation = ref(null)
const messages = ref([])
const newMessage = ref('')
const sendingMessage = ref(false)
const messagesContainer = ref(null)

// Computed
const currentUserId = computed(() => authStore.user?.id)
const otherParticipant = computed(() => {
  if (!conversation.value) return null
  return conversation.value.buyer_id === currentUserId.value 
    ? conversation.value.seller 
    : conversation.value.buyer
})

// Methods
const loadConversation = async () => {
  loading.value = true
  try {
    console.log('🔵 Loading conversation:', route.params.id)
    const response = await api.get(`/conversations/${route.params.id}`)
    
    if (response.data.success) {
      conversation.value = response.data.data
      messages.value = response.data.data.messages?.reverse() || []
      
      console.log('✅ Conversation loaded:', {
        id: conversation.value.id,
        product: conversation.value.product?.title,
        messages_count: messages.value.length
      })
      
      // Scroll to bottom after loading
      await nextTick()
      scrollToBottom()
    }
  } catch (error) {
    console.error('❌ Error loading conversation:', error)
    conversation.value = null
  } finally {
    loading.value = false
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || sendingMessage.value) return
  
  sendingMessage.value = true
  const messageContent = newMessage.value.trim()
  newMessage.value = ''
  
  try {
    console.log('🔵 Sending message:', messageContent)
    const response = await api.post(`/conversations/${conversation.value.id}/messages`, {
      content: messageContent
    })
    
    if (response.data.success) {
      messages.value.push(response.data.data)
      console.log('✅ Message sent successfully')
      
      // Scroll to bottom
      await nextTick()
      scrollToBottom()
    }
  } catch (error) {
    console.error('❌ Error sending message:', error)
    // Restore message if failed
    newMessage.value = messageContent
  } finally {
    sendingMessage.value = false
  }
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const goBack = () => {
  router.back()
}

const formatPrice = (price) => {
  if (!price) return ''
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XAF'
  }).format(price)
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

// Lifecycle
onMounted(() => {
  console.log('🔵 ConversationDetail mounted for ID:', route.params.id)
  loadConversation()
})
</script>
</template>

<style scoped>
/* Custom scrollbar for messages */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>