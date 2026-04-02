<template>
  <div class="fixed inset-x-0 top-14 sm:top-16 bottom-20 flex flex-col bg-[#f0f2f5] overflow-hidden z-10">

    <!-- ── Loading ── -->
    <div v-if="loading" class="flex-1 flex items-center justify-center">
      <div class="flex flex-col items-center gap-3">
        <div class="relative w-12 h-12">
          <div class="absolute inset-0 rounded-full border-4 border-gray-200"></div>
          <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-emerald-500 animate-spin"></div>
        </div>
        <p class="text-sm text-gray-400 font-medium">Chargement...</p>
      </div>
    </div>

    <template v-else-if="conversation">

      <!-- ── Header ── -->
      <div class="bg-white shadow-sm flex-shrink-0">

        <!-- Ligne principale -->
        <div class="flex items-center gap-3 px-3 py-2.5">

          <!-- Retour -->
          <button
            @click="goBack"
            class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 active:bg-gray-200 transition-colors flex-shrink-0"
          >
            <ArrowLeftIcon class="w-5 h-5 text-gray-600" />
          </button>

          <!-- Avatar interlocuteur -->
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-green-600 flex items-center justify-center flex-shrink-0 shadow-md overflow-hidden">
            <img 
              v-if="otherParticipant?.avatar_url || otherParticipant?.avatar"
              :src="otherParticipant?.avatar_url || otherParticipant?.avatar"
              class="w-full h-full object-cover"
            />
            <span v-else class="text-white font-bold text-sm select-none">
              {{ getInitials(otherParticipant?.name) }}
            </span>
          </div>

          <!-- Nom + produit -->
          <div class="flex-1 min-w-0">
            <p class="font-bold text-gray-900 text-sm leading-tight truncate">
              {{ otherParticipant?.name || 'Utilisateur' }}
            </p>
            <p class="text-xs text-gray-400 truncate leading-tight mt-0.5">
              {{ conversation.product?.title }}
            </p>
          </div>

          <!-- Vignette produit cliquable -->
          <RouterLink
            :to="`/products/${conversation.product?.id}`"
            class="flex-shrink-0 group"
          >
            <div class="w-11 h-11 rounded-xl overflow-hidden bg-gray-100 border-2 border-gray-200 group-hover:border-emerald-300 transition-colors shadow-sm">
              <ProductImage
                :src="getProductImageUrl(conversation.product)"
                :alt="conversation.product?.title"
                :product-id="conversation.product?.id"
                fallback="/images/placeholder-product.png"
                image-classes="w-full h-full object-cover"
                :class="{ 'grayscale opacity-60': isProductUnavailable }"
              />
            </div>
          </RouterLink>
        </div>

        <!-- Bande prix / statut produit -->
        <div class="px-4 pb-2 flex items-center gap-2 border-t border-gray-50">
          <span class="text-xs font-semibold text-emerald-600">
            {{ formatPrice(conversation.product?.price) }}
          </span>
          <span v-if="isProductUnavailable"
            :class="['text-xs font-semibold px-2 py-0.5 rounded-full', getProductStatusClass()]"
          >
            {{ getProductStatusText() }}
          </span>
        </div>
      </div>

      <!-- ── Zone messages ── -->
      <div
        class="flex-1 overflow-y-auto px-3 py-3 space-y-1"
        ref="messagesContainer"
      >

        <!-- Vide -->
        <div v-if="messages.length === 0" class="flex flex-col items-center justify-center h-full text-center py-10">
          <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center shadow-sm border border-gray-100 mb-4">
            <MessageCircleIcon class="w-7 h-7 text-gray-300" />
          </div>
          <p class="text-sm font-semibold text-gray-600">Démarrez la conversation</p>
          <p class="text-xs text-gray-400 mt-1">Envoyez votre premier message ci-dessous</p>
        </div>

        <!-- Messages -->
        <div
          v-for="(message, index) in messages"
          :key="message.id"
          class="flex items-end gap-2"
          :class="message.sender_id === currentUserId ? 'justify-end' : 'justify-start'"
        >
          <!-- Avatar reçu (affiché seulement sur le dernier message du groupe) -->
          <div
            v-if="message.sender_id !== currentUserId"
            class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-green-600 flex items-center justify-center flex-shrink-0 overflow-hidden shadow-sm"
            :class="isLastInGroup(index) ? 'opacity-100' : 'opacity-0'"
          >
            <img 
              v-if="otherParticipant?.avatar_url || otherParticipant?.avatar"
              :src="otherParticipant?.avatar_url || otherParticipant?.avatar"
              class="w-full h-full object-cover"
            />
            <span v-else class="text-white text-xs font-bold select-none">
              {{ getInitials(otherParticipant?.name) }}
            </span>
          </div>

          <!-- Bulle -->
          <div class="max-w-[72%] flex flex-col"
            :class="message.sender_id === currentUserId ? 'items-end' : 'items-start'"
          >
            <div
              :class="[
                'px-3.5 py-2 shadow-sm text-sm leading-relaxed',
                message.sender_id === currentUserId
                  ? 'bg-emerald-500 text-white rounded-2xl rounded-br-md'
                  : 'bg-white text-gray-900 rounded-2xl rounded-bl-md border border-gray-100'
              ]"
            >
              {{ extractMessageContent(message.content) }}
            </div>
            <!-- Horodatage (seulement sur le dernier du groupe) -->
            <p
              v-if="isLastInGroup(index)"
              class="text-[11px] text-gray-400 mt-1 px-1"
            >
              {{ formatMessageTime(message.created_at) }}
            </p>
          </div>
        </div>

      </div>

      <!-- ── Picker emoji ── -->
      <div
        v-if="showEmojiPicker"
        class="bg-white border-t border-gray-100 flex-shrink-0 shadow-[0_-2px_12px_rgba(0,0,0,0.05)]"
      >
        <!-- Onglets catégories -->
        <div class="flex border-b border-gray-100 px-2 pt-1.5">
          <button
            v-for="cat in emojiCategories"
            :key="cat.label"
            @click="activeEmojiCat = cat.label"
            class="flex-1 py-1.5 text-lg transition-all"
            :class="activeEmojiCat === cat.label ? 'border-b-2 border-emerald-500' : 'opacity-50'"
          >
            {{ cat.icon }}
          </button>
        </div>
        <!-- Grille emojis -->
        <div class="grid grid-cols-8 gap-0.5 p-2 h-36 overflow-y-auto">
          <button
            v-for="emoji in currentEmojis"
            :key="emoji"
            @click="insertEmoji(emoji)"
            class="text-xl h-9 flex items-center justify-center rounded-lg hover:bg-gray-100 active:bg-gray-200 transition-colors"
          >
            {{ emoji }}
          </button>
        </div>
      </div>

      <!-- ── Barre de saisie ── -->
      <div class="bg-white border-t border-gray-100 px-3 py-2.5 flex-shrink-0 shadow-[0_-2px_12px_rgba(0,0,0,0.05)]">
        <form @submit.prevent="sendMessage" class="flex items-center gap-2">
          <!-- Bouton emoji -->
          <button
            type="button"
            @click="showEmojiPicker = !showEmojiPicker"
            class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 active:bg-gray-200 transition-colors flex-shrink-0 text-xl"
            :class="showEmojiPicker ? 'bg-emerald-50' : ''"
          >
            😊
          </button>

          <input
            v-model="newMessage"
            ref="messageInput"
            type="text"
            placeholder="Message..."
            class="flex-1 bg-gray-100 rounded-full px-4 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-gray-50 focus:ring-2 focus:ring-emerald-300 transition-all"
            :disabled="sendingMessage"
            @keydown.enter.exact.prevent="sendMessage"
            @focus="showEmojiPicker = false"
          />
          <button
            type="submit"
            :disabled="!newMessage.trim() || sendingMessage"
            class="w-10 h-10 bg-emerald-500 text-white rounded-full flex items-center justify-center flex-shrink-0 shadow-md shadow-emerald-200/50 hover:bg-emerald-600 active:bg-emerald-700 disabled:opacity-40 disabled:cursor-not-allowed disabled:shadow-none transition-all active:scale-95"
          >
            <SendIcon v-if="!sendingMessage" class="w-4 h-4 translate-x-px" />
            <LoaderIcon v-else class="w-4 h-4 animate-spin" />
          </button>
        </form>
      </div>

    </template>

    <!-- ── Erreur ── -->
    <div v-else class="flex-1 flex items-center justify-center px-6">
      <div class="text-center">
        <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
          <AlertTriangleIcon class="w-8 h-8 text-red-400" />
        </div>
        <p class="font-bold text-gray-800 text-lg mb-1">Conversation introuvable</p>
        <p class="text-sm text-gray-500 mb-6">Cette conversation n'existe pas ou vous n'y avez pas accès.</p>
        <button
          @click="goBack"
          class="px-6 py-2.5 bg-emerald-500 text-white rounded-full text-sm font-bold shadow-lg shadow-emerald-200 hover:bg-emerald-600 transition-colors"
        >
          Retour aux discussions
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import {
  ArrowLeftIcon, AlertTriangleIcon, SendIcon, LoaderIcon, MessageCircleIcon
} from 'lucide-vue-next'
import { extractMessageContent } from '@/utils/messageUtils'
import { formatMessageTime } from '@/utils/timeUtils'
import ProductImage from '@/components/ui/ProductImage.vue'

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
const messageInput = ref(null)

// Emoji picker
const showEmojiPicker = ref(false)
const activeEmojiCat = ref('Smileys')

const emojiCategories = [
  {
    label: 'Smileys', icon: '😊',
    emojis: ['😀','😃','😄','😁','😆','😅','🤣','😂','🙂','🙃','😉','😊','😇','🥰','😍','🤩','😘','😗','😚','😙','🥲','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😒','🙄','😬','🤥','😌','😔','😪','🤤','😴','😷','🤒','🤕','🤢','🤮','🤧','🥵','🥶','🥴','😵','🤯','🤠','🥳','🥸','😎','🤓','🧐','😕','😟','🙁','☹️','😮','😯','😲','😳','🥺','😦','😧','😨','😰','😥','😢','😭','😱','😖','😣','😞','😓','😩','😫','🥱','😤','😡','😠','🤬','😈','👿','💀','☠️','💩','🤡','👹','👺','👻','👽','👾','🤖']
  },
  {
    label: 'Gestes', icon: '👋',
    emojis: ['👋','🤚','🖐','✋','🖖','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','🖕','👇','☝️','👍','👎','✊','👊','🤛','🤜','👏','🙌','👐','🤲','🤝','🙏','✍️','💅','🤳','💪','🦾','🦵','🦶','👂','🦻','👃','🫀','🫁','🧠','🦷','🦴','👀','👁','👅','👄','💋','🫦']
  },
  {
    label: 'Coeurs', icon: '❤️',
    emojis: ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','❤️‍🔥','❤️‍🩹','💔','💕','💞','💓','💗','💖','💘','💝','💟','☮️','✝️','☯️','🕉','🔯','💯','♾','‼️','⁉️','❓','❔','❕','❗','🔅','🔆','⚜️','🔱','📛','🔰','✅','❎','🆗','🆙','🆒','🆕','🆓','🔝','🛑','⛔','🚫','💢','♨️','🌀','✨','🎊','🎉','🎈']
  },
  {
    label: 'Nature', icon: '🌿',
    emojis: ['🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯','🦁','🐮','🐷','🐸','🐵','🙈','🙉','🙊','🐔','🐧','🐦','🐤','🦆','🦅','🦉','🦇','🐺','🐗','🦋','🐛','🐌','🐞','🐜','🪲','🦟','🦗','🌸','🌺','🌻','🌹','🌷','🌱','🌿','☘️','🍀','🎍','🎋','🍃','🍂','🍁','🌾','🌵','🦀','🐠','🐟','🐡','🦈','🐳','🦋']
  },
  {
    label: 'Objets', icon: '🎁',
    emojis: ['⚽','🏀','🏈','⚾','🎾','🏐','🏉','🥏','🎱','🏓','🏸','🥅','⛳','🏹','🎣','🤿','🎽','🎿','🛷','🥌','🎯','🎮','🎲','🎭','🎨','🎬','🎤','🎧','🎼','🎵','🎶','🎸','🎹','🎺','🎻','🥁','📱','💻','⌨️','🖥','🖨','🖱','🖲','💽','💾','💿','📀','📷','📸','📹','🎥','📽','🎞','📞','☎️','📟','📠','📺','📻','🧭','⏱','⌚','⏰','🎁','🎀','🎊','🎉','🎈','🛍','🛒','💎','💰','💳','💵','📈','📉','📊','📋','📌','📍','🗓','📅','📆']
  },
]

const currentEmojis = computed(() => {
  return emojiCategories.find(c => c.label === activeEmojiCat.value)?.emojis || []
})

const insertEmoji = (emoji) => {
  newMessage.value += emoji
  messageInput.value?.focus()
}

// Computed
const currentUserId = computed(() => authStore.user?.id)
const otherParticipant = computed(() => {
  if (!conversation.value) return null
  return conversation.value.buyer_id === currentUserId.value
    ? conversation.value.seller
    : conversation.value.buyer
})

const isProductUnavailable = computed(() => {
  if (!conversation.value?.product) return false
  return conversation.value.product.is_sold
    || conversation.value.product.is_deleted
    || conversation.value.product.is_inactive
})

// Helpers
const getInitials = (name) => {
  if (!name) return '?'
  return name.trim().split(' ').slice(0, 2).map(w => w[0]?.toUpperCase()).join('')
}

const isLastInGroup = (index) => {
  const next = messages.value[index + 1]
  const current = messages.value[index]
  if (!next) return true
  return next.sender_id !== current.sender_id
}

const getProductStatusClass = () => {
  if (!conversation.value?.product) return ''
  if (conversation.value.product.is_sold)    return 'bg-green-100 text-green-700'
  if (conversation.value.product.is_deleted) return 'bg-red-100 text-red-700'
  if (conversation.value.product.is_inactive) return 'bg-yellow-100 text-yellow-700'
  return ''
}

const getProductStatusText = () => {
  if (!conversation.value?.product) return ''
  if (conversation.value.product.is_sold)    return 'Vendu'
  if (conversation.value.product.is_deleted) return 'Supprimé'
  if (conversation.value.product.is_inactive) return 'Désactivé'
  return ''
}

const formatPrice = (price) => {
  if (!price) return ''
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XAF' }).format(price)
}

const getProductImageUrl = (product) => {
  if (!product) return '/images/placeholder-product.png'
  if (typeof product.main_image_url === 'string') return product.main_image_url
  if (product.main_image?.filename) return `http://localhost:8000/storage/products/${product.main_image.filename}`
  if (typeof product.main_image === 'string') return `http://localhost:8000/storage/products/${product.main_image}`
  return '/images/placeholder-product.png'
}

// API
const loadConversation = async () => {
  loading.value = true
  try {
    const response = await api.get(`/conversations/${route.params.id}`)
    if (response.data.success) {
      conversation.value = response.data.data
      messages.value = response.data.data.messages?.reverse() || []
      await nextTick()
      scrollToBottom()
      // L'utilisateur est dans la conversation → tout marquer lu
      markAllRead()
    }
  } catch (error) {
    conversation.value = null
    if (error.response?.status === 403 || error.response?.status === 404) {
      setTimeout(() => router.push('/discussions'), 3000)
    }
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
    const response = await api.post(`/conversations/${conversation.value.id}/messages`, {
      content: messageContent
    })
    if (response.data.success) {
      messages.value.push(response.data.data)
      await nextTick()
      scrollToBottom()
    }
  } catch {
    newMessage.value = messageContent
  } finally {
    sendingMessage.value = false
  }
}

const scrollToBottom = () => {
  // Double rAF : garantit que le scroll se fait après le rendu complet du DOM
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
      }
    })
  })
}

const markAllRead = () => {
  if (!conversation.value) return
  api.post(`/conversations/${conversation.value.id}/mark-read`).catch(() => {})
}

const goBack = () => router.push('/discussions')

// ── Polling temps réel ──────────────────────────────────────────────────────
let pollingInterval = null

const pollNewMessages = async () => {
  if (!conversation.value) return
  const lastId = messages.value.length > 0
    ? messages.value[messages.value.length - 1].id
    : 0
  try {
    const response = await api.get(
      `/conversations/${conversation.value.id}/messages`,
      { params: { after_id: lastId } }
    )
    if (response.data.success) {
      const fetched = Array.isArray(response.data.data) ? response.data.data : []
      // Dédoublonnage par ID : évite les doublons dus à la race condition
      // entre sendMessage() qui ajoute localement et le poll qui récupère le même message
      const existingIds = new Set(messages.value.map(m => m.id))
      const uniqueNew = fetched.filter(m => !existingIds.has(m.id))
      if (uniqueNew.length > 0) {
        messages.value.push(...uniqueNew)
        await nextTick()
        scrollToBottom()
        // Nouveaux messages reçus pendant qu'on est dans la conv → marquer lus
        markAllRead()
      }
    }
  } catch {
    // silencieux — ne pas perturber l'UI
  }
}

onMounted(() => {
  loadConversation()
  pollingInterval = setInterval(pollNewMessages, 3000)
})

onUnmounted(() => {
  if (pollingInterval) clearInterval(pollingInterval)
})
</script>

<style scoped>
.overflow-y-auto::-webkit-scrollbar { width: 3px; }
.overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
.overflow-y-auto::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
.overflow-y-auto::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>
