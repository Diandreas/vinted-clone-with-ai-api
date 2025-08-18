<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Messages</h1>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Conversations list -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 lg:col-span-1">
          <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Conversations</h2>
            <button
              class="text-sm text-gray-600 hover:text-gray-900"
              @click="loadConversations()"
            >Rafraîchir</button>
          </div>
          <div v-if="loadingConversations" class="p-6 text-center text-gray-500">Chargement…</div>
          <div v-else-if="conversationsError" class="p-6 text-center text-red-600">{{ conversationsError }}</div>
          <ul v-else class="divide-y divide-gray-100 max-h-[60vh] overflow-auto">
            <li
              v-for="c in conversations"
              :key="c.id"
              class="p-4 cursor-pointer hover:bg-gray-50"
              :class="selectedConversation && selectedConversation.id === c.id ? 'bg-gray-50' : ''"
              @click="selectConversation(c)"
            >
              <div class="flex items-center justify-between">
                <div class="text-sm font-medium text-gray-900">{{ otherParticipantName(c) }}</div>
                <div class="text-xs text-gray-500">{{ formatDate(c.last_message_at || c.updated_at) }}</div>
              </div>
              <div class="mt-1 text-sm text-gray-600 truncate">
                {{ previewContent(c) }}
              </div>
            </li>
          </ul>
        </div>

        <!-- Conversation pane -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 lg:col-span-2">
          <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold">
              <span v-if="selectedConversation">Avec {{ otherParticipantName(selectedConversation) }}</span>
              <span v-else>Nouvelle conversation</span>
            </h2>
          </div>

          <!-- Start new conversation shortcut from query -->
          <div v-if="!selectedConversation && startTarget.userId" class="p-6 border-b border-gray-100">
            <div class="text-sm text-gray-700">
              Envoi d’un message à l’utilisateur #{{ startTarget.userId }}
              <span v-if="startTarget.productId">à propos du produit #{{ startTarget.productId }}</span>
            </div>
            <div class="mt-3 flex items-center gap-2">
              <input v-model="compose" type="text" class="flex-1 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Votre message…" />
              <button class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700" :disabled="composeBusy || !compose.trim()" @click="startConversation()">Envoyer</button>
            </div>
            <div v-if="composeError" class="mt-2 text-sm text-red-600">{{ composeError }}</div>
          </div>

          <!-- Messages -->
          <div class="p-4 h-[50vh] overflow-auto" v-if="selectedConversation">
            <div v-if="loadingMessages" class="text-center text-gray-500">Chargement des messages…</div>
            <div v-else-if="messagesError" class="text-center text-red-600">{{ messagesError }}</div>
            <div v-else class="space-y-3">
              <div v-for="m in messages" :key="m.id" class="max-w-xl" :class="m.sender_id === meId ? 'ml-auto text-right' : ''">
                <div class="inline-block px-3 py-2 rounded-lg" :class="m.sender_id === meId ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-900'">
                  <div class="text-sm">{{ displayContent(m) }}</div>
                  <div class="text-[11px] mt-1 opacity-70">{{ formatDate(m.created_at) }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Composer -->
          <div class="p-4 border-t border-gray-100" v-if="selectedConversation">
            <div class="flex items-center gap-2">
              <input v-model="compose" type="text" class="flex-1 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Votre message…" @keyup.enter="sendMessage()" />
              <button class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700" :disabled="composeBusy || !compose.trim()" @click="sendMessage()">Envoyer</button>
            </div>
            <div v-if="composeError" class="mt-2 text-sm text-red-600">{{ composeError }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const auth = useAuthStore()
const meId = computed(() => auth.user?.id)

// Conversations
const conversations = ref([])
const loadingConversations = ref(false)
const conversationsError = ref('')
const selectedConversation = ref(null)

// Messages
const messages = ref([])
const loadingMessages = ref(false)
const messagesError = ref('')

// Composer
const compose = ref('')
const composeBusy = ref(false)
const composeError = ref('')

// Start target via query (?user=&product=)
const startTarget = ref({ userId: null, productId: null })

// Utils
function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleString()
}

function safeParseMaybeJson(text) {
  if (typeof text !== 'string') return String(text ?? '')
  const trimmed = text.trim()
  if (!(trimmed.startsWith('{') || trimmed.startsWith('['))) return text
  try {
    const obj = JSON.parse(trimmed)
    // Heuristique: si c'est un message structuré {content, type}
    if (obj && typeof obj === 'object' && 'content' in obj) return String(obj.content ?? '')
    return typeof obj === 'string' ? obj : text
  } catch (_) {
    return text
  }
}

function otherParticipantName(conv) {
  if (!conv) return 'Conversation'
  const me = meId.value
  const other = conv.buyer_id === me ? conv.seller : conv.buyer
  return other?.name || other?.username || 'Conversation'
}

function previewContent(conv) {
  // lastMessage peut être chargé via relation ou via c.messages[0]
  const last = conv.last_message || (conv.messages && conv.messages[0])
  if (!last) return '—'
  return safeParseMaybeJson(last.content)
}

function displayContent(m) {
  return safeParseMaybeJson(m.content)
}

async function loadConversations() {
  loadingConversations.value = true
  conversationsError.value = ''
  try {
    const resp = await window.axios.get('/conversations')
    conversations.value = resp.data?.data?.data || []
  } catch (e) {
    console.error(e)
    conversationsError.value = 'Impossible de charger les conversations.'
  } finally {
    loadingConversations.value = false
  }
}

function selectConversation(c) {
  selectedConversation.value = c
  loadMessages(c.id)
}

async function loadMessages(conversationId, page = 1) {
  loadingMessages.value = true
  messagesError.value = ''
  try {
    const resp = await window.axios.get(`/conversations/${conversationId}/messages`, { params: { page } })
    messages.value = resp.data?.data?.data || []
  } catch (e) {
    console.error(e)
    messagesError.value = 'Impossible de charger les messages.'
  } finally {
    loadingMessages.value = false
  }
}

async function sendMessage() {
  if (!selectedConversation.value || !compose.value.trim()) return
  composeBusy.value = true
  composeError.value = ''
  try {
    const resp = await window.axios.post(`/conversations/${selectedConversation.value.id}/messages`, {
      content: compose.value.trim(),
      type: 'text',
      product_id: startTarget.value.productId || null
    })
    const msg = resp.data?.data
    if (msg) messages.value.unshift(msg)
    compose.value = ''
  } catch (e) {
    console.error(e)
    composeError.value = 'Échec de l’envoi du message.'
  } finally {
    composeBusy.value = false
  }
}

async function startConversation() {
  if (!startTarget.value.userId || !compose.value.trim()) return
  composeBusy.value = true
  composeError.value = ''
  try {
    const resp = await window.axios.post('/conversations', {
      participant_id: startTarget.value.userId,
      message: compose.value.trim()
    })
    const conv = resp.data?.data
    if (conv) {
      conversations.value.unshift(conv)
      selectedConversation.value = conv
      compose.value = ''
      await loadMessages(conv.id)
    }
  } catch (e) {
    console.error(e)
    composeError.value = 'Impossible de démarrer la conversation.'
  } finally {
    composeBusy.value = false
  }
}

function hydrateStartTargetFromQuery() {
  const userId = route.query.user ? Number(route.query.user) : null
  const productId = route.query.product ? Number(route.query.product) : null
  startTarget.value = { userId, productId }
}

onMounted(async () => {
  hydrateStartTargetFromQuery()
  await loadConversations()
})

watch(() => route.query, () => hydrateStartTargetFromQuery(), { deep: true })
</script>

