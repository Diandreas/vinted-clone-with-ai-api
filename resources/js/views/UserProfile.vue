<template>
  <div class="min-h-screen bg-black text-white">
    <!-- Header avec image de couverture -->
    <div class="relative">
      <!-- Cover Image -->
      <div class="h-64 bg-gradient-to-br from-purple-600 via-pink-600 to-red-600 relative overflow-hidden">
        <img
          v-if="user?.cover_image"
          :src="user.cover_image"
          :alt="`Couverture de ${user?.name}`"
          class="w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
        
        <!-- Back Button -->
        <button
          @click="router.back()"
          class="absolute top-4 left-4 p-2 bg-black bg-opacity-50 rounded-full hover:bg-opacity-70 transition-all"
        >
          <ArrowLeftIcon class="w-5 h-5 text-white" />
        </button>
        
        <!-- More Options -->
        <button
          v-if="!isSelf"
          @click="showMoreOptions = !showMoreOptions"
          class="absolute top-4 right-4 p-2 bg-black bg-opacity-50 rounded-full hover:bg-opacity-70 transition-all"
        >
          <MoreVerticalIcon class="w-5 h-5 text-white" />
        </button>
        
        <!-- More Options Dropdown -->
        <div
          v-if="showMoreOptions"
          class="absolute top-16 right-4 bg-gray-900 rounded-lg shadow-xl border border-gray-800 py-2 z-50 min-w-[200px]"
        >
          <button
            @click="reportUser"
            class="w-full px-4 py-2 text-left text-red-400 hover:bg-gray-800 transition-colors flex items-center"
          >
            <FlagIcon class="w-4 h-4 mr-3" />
            Signaler
          </button>
          <button
            @click="blockUser"
            class="w-full px-4 py-2 text-left text-red-400 hover:bg-gray-800 transition-colors flex items-center"
          >
            <BanIcon class="w-4 h-4 mr-3" />
            Bloquer
          </button>
        </div>
      </div>
      
      <!-- Profile Info Overlay -->
      <div class="absolute bottom-0 left-0 right-0 p-6">
        <div class="flex items-end justify-between">
          <div class="flex items-end space-x-4">
            <!-- Avatar -->
            <div class="relative">
              <img
                :src="userAvatar"
                :alt="user?.name || 'Utilisateur'"
                class="w-24 h-24 rounded-full border-4 border-white object-cover"
                @error="onAvatarError"
              />
              <div
                v-if="user?.is_verified"
                class="absolute -bottom-1 -right-1 bg-blue-500 rounded-full p-1"
              >
                <CheckIcon class="w-4 h-4 text-white" />
              </div>
            </div>
            
            <!-- User Info -->
            <div class="mb-2">
              <div class="flex items-center space-x-2 mb-1">
                <h1 class="text-2xl font-bold">{{ user?.name || 'Utilisateur' }}</h1>
                <span v-if="user?.username" class="text-gray-300">@{{ user.username }}</span>
              </div>
              <p v-if="user?.bio" class="text-gray-300 text-sm max-w-md">{{ user.bio }}</p>
              <div class="flex items-center space-x-4 mt-2 text-sm text-gray-400">
                <span v-if="user?.location" class="flex items-center">
                  <MapPinIcon class="w-4 h-4 mr-1" />
                  {{ user.location }}
                </span>
                <span v-if="user?.website" class="flex items-center">
                  <LinkIcon class="w-4 h-4 mr-1" />
                  <a :href="user.website" target="_blank" class="hover:text-white transition-colors">
                    Site web
                  </a>
                </span>
              </div>
            </div>
          </div>
          
          <!-- Action Buttons -->
          <div v-if="!isSelf" class="flex items-center space-x-3">
            <button
              @click="goToMessages"
              class="px-6 py-2 bg-gray-800 hover:bg-gray-700 rounded-full transition-colors flex items-center space-x-2"
            >
              <MessageCircleIcon class="w-4 h-4" />
              <span>Message</span>
            </button>
            <button
              :disabled="followBusy"
              @click="toggleFollow"
              class="px-6 py-2 rounded-full transition-all flex items-center space-x-2"
              :class="isFollowing 
                ? 'bg-gray-800 hover:bg-gray-700 text-white' 
                : 'bg-red-500 hover:bg-red-600 text-white'"
            >
              <template v-if="followBusy">
                <Loader2Icon class="w-4 h-4 animate-spin" />
                <span>...</span>
              </template>
              <template v-else>
                <UsersIcon v-if="!isFollowing" class="w-4 h-4" />
                <CheckIcon v-else class="w-4 h-4" />
                <span>{{ isFollowing ? 'Abonné' : 'Suivre' }}</span>
              </template>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Bar -->
    <div class="bg-gray-900 border-b border-gray-800">
      <div class="max-w-4xl mx-auto px-6 py-4">
        <div class="grid grid-cols-4 gap-4 text-center">
          <div class="cursor-pointer hover:bg-gray-800 rounded-lg p-3 transition-colors" @click="activeTab = 'products'">
            <div class="text-xl font-bold text-white">{{ stats?.products_count ?? 0 }}</div>
            <div class="text-sm text-gray-400">Produits</div>
          </div>
          <div class="cursor-pointer hover:bg-gray-800 rounded-lg p-3 transition-colors" @click="activeTab = 'followers'">
            <div class="text-xl font-bold text-white">{{ stats?.followers_count ?? 0 }}</div>
            <div class="text-sm text-gray-400">Abonnés</div>
          </div>
          <div class="cursor-pointer hover:bg-gray-800 rounded-lg p-3 transition-colors" @click="activeTab = 'following'">
            <div class="text-xl font-bold text-white">{{ stats?.following_count ?? 0 }}</div>
            <div class="text-sm text-gray-400">Abonnements</div>
          </div>
          <div class="cursor-pointer hover:bg-gray-800 rounded-lg p-3 transition-colors" @click="activeTab = 'reviews'">
            <div class="text-xl font-bold text-white">{{ (stats?.average_rating ?? 0).toFixed(1) }}</div>
            <div class="text-sm text-gray-400">Note</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stories Section -->
    <div class="max-w-4xl mx-auto px-6 py-6">
      <StoriesGrid :stories="stories" :user-id="route.params.id" />
    </div>

    <!-- Content Tabs -->
    <div class="max-w-4xl mx-auto px-6 py-6">
      <!-- Tab Navigation -->
      <div class="flex space-x-8 border-b border-gray-800 mb-6">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="pb-4 px-2 relative transition-colors"
          :class="activeTab === tab.id ? 'text-white' : 'text-gray-400 hover:text-gray-300'"
        >
          {{ tab.label }}
          <div
            v-if="activeTab === tab.id"
            class="absolute bottom-0 left-0 right-0 h-0.5 bg-red-500"
          ></div>
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex items-center justify-center py-16">
        <Loader2Icon class="h-8 w-8 animate-spin text-red-500" />
        <span class="ml-3 text-gray-400">Chargement...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-16">
        <AlertTriangleIcon class="h-12 w-12 text-red-500 mx-auto mb-4" />
        <p class="text-red-400">{{ error }}</p>
        <button
          @click="retryLoad"
          class="mt-4 px-6 py-2 bg-red-500 hover:bg-red-600 rounded-full transition-colors"
        >
          Réessayer
        </button>
      </div>

      <!-- Tab Content -->
      <div v-else>
        <!-- Products Tab -->
        <div v-if="activeTab === 'products'" class="space-y-6">
          <div v-if="products.length === 0" class="text-center py-16">
            <PackageIcon class="h-16 w-16 text-gray-600 mx-auto mb-4" />
            <h3 class="text-xl font-semibold text-gray-300 mb-2">Aucun produit</h3>
            <p class="text-gray-500">Cet utilisateur n'a pas encore publié de produits.</p>
          </div>
          
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <TikTokProductCard
              v-for="product in products"
              :key="product.id"
              :product="product"
            />
          </div>
          
          <!-- Pagination -->
          <div v-if="pagination.last > 1" class="flex items-center justify-center space-x-4 mt-8">
            <button
              @click="goToPage(pagination.current - 1)"
              :disabled="pagination.current === 1"
              class="px-4 py-2 rounded-full transition-colors"
              :class="pagination.current === 1 
                ? 'bg-gray-800 text-gray-600 cursor-not-allowed' 
                : 'bg-gray-800 hover:bg-gray-700 text-white'"
            >
              Précédent
            </button>
            <span class="text-gray-400">Page {{ pagination.current }} / {{ pagination.last }}</span>
            <button
              @click="goToPage(pagination.current + 1)"
              :disabled="pagination.current === pagination.last"
              class="px-4 py-2 rounded-full transition-colors"
              :class="pagination.current === pagination.last 
                ? 'bg-gray-800 text-gray-600 cursor-not-allowed' 
                : 'bg-gray-800 hover:bg-gray-700 text-white'"
            >
              Suivant
            </button>
          </div>
        </div>

        <!-- Lives Tab -->
        <div v-if="activeTab === 'lives'" class="space-y-6">
          <div v-if="lives.length === 0" class="text-center py-16">
            <div class="w-16 h-16 bg-gray-800 rounded-full mx-auto mb-4 flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-300 mb-2">Aucun live</h3>
            <p class="text-gray-500">Cet utilisateur n'a pas encore créé de lives.</p>
          </div>
          
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <TikTokLiveCard
              v-for="live in lives"
              :key="live.id"
              :live="live"
            />
          </div>
        </div>

        <!-- Followers Tab -->
        <div v-if="activeTab === 'followers'" class="space-y-4">
          <div v-if="followers.length === 0" class="text-center py-16">
            <UsersIcon class="h-16 w-16 text-gray-600 mx-auto mb-4" />
            <h3 class="text-xl font-semibold text-gray-300 mb-2">Aucun abonné</h3>
            <p class="text-gray-500">Cet utilisateur n'a pas encore d'abonnés.</p>
          </div>
          
          <div v-else class="space-y-3">
            <div
              v-for="follower in followers"
              :key="follower.id"
              class="flex items-center justify-between p-4 bg-gray-900 rounded-xl hover:bg-gray-800 transition-colors"
            >
              <div class="flex items-center space-x-3">
                <img
                  :src="follower.avatar || '/default-avatar.png'"
                  :alt="follower.name"
                  class="w-12 h-12 rounded-full object-cover"
                />
                <div>
                  <h4 class="font-semibold text-white">{{ follower.name }}</h4>
                  <p class="text-sm text-gray-400">@{{ follower.username }}</p>
                </div>
              </div>
              <button
                v-if="!isSelf"
                @click="toggleFollowUser(follower.id)"
                class="px-4 py-2 rounded-full transition-colors"
                :class="follower.is_following 
                  ? 'bg-gray-700 hover:bg-gray-600 text-white' 
                  : 'bg-red-500 hover:bg-red-600 text-white'"
              >
                {{ follower.is_following ? 'Abonné' : 'Suivre' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Following Tab -->
        <div v-if="activeTab === 'following'" class="space-y-4">
          <div v-if="following.length === 0" class="text-center py-16">
            <UsersIcon class="h-16 w-16 text-gray-600 mx-auto mb-4" />
            <h3 class="text-xl font-semibold text-gray-300 mb-2">Aucun abonnement</h3>
            <p class="text-gray-500">Cet utilisateur ne suit personne pour le moment.</p>
          </div>
          
          <div v-else class="space-y-3">
            <div
              v-for="followed in following"
              :key="followed.id"
              class="flex items-center justify-between p-4 bg-gray-900 rounded-xl hover:bg-gray-800 transition-colors"
            >
              <div class="flex items-center space-x-3">
                <img
                  :src="followed.avatar || '/default-avatar.png'"
                  :alt="followed.name"
                  class="w-12 h-12 rounded-full object-cover"
                />
                <div>
                  <h4 class="font-semibold text-white">{{ followed.name }}</h4>
                  <p class="text-sm text-gray-400">@{{ followed.username }}</p>
                </div>
              </div>
              <button
                v-if="!isSelf"
                @click="toggleFollowUser(followed.id)"
                class="px-4 py-2 rounded-full transition-colors"
                :class="followed.is_following 
                  ? 'bg-gray-700 hover:bg-gray-600 text-white' 
                  : 'bg-red-500 hover:bg-red-600 text-white'"
              >
                {{ followed.is_following ? 'Abonné' : 'Suivre' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Reviews Tab -->
        <div v-if="activeTab === 'reviews'" class="space-y-4">
          <div v-if="reviews.length === 0" class="text-center py-16">
            <StarIcon class="h-16 w-16 text-gray-600 mx-auto mb-4" />
            <h3 class="text-xl font-semibold text-gray-300 mb-2">Aucune évaluation</h3>
            <p class="text-gray-500">Cet utilisateur n'a pas encore reçu d'évaluations.</p>
          </div>
          
          <div v-else class="space-y-4">
            <div
              v-for="review in reviews"
              :key="review.id"
              class="p-4 bg-gray-900 rounded-xl"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center space-x-3">
                  <img
                    :src="review.user.avatar || '/default-avatar.png'"
                    :alt="review.user.name"
                    class="w-10 h-10 rounded-full object-cover"
                  />
                  <div>
                    <h4 class="font-semibold text-white">{{ review.user.name }}</h4>
                    <p class="text-sm text-gray-400">{{ formatDate(review.created_at) }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-1">
                  <StarIcon
                    v-for="star in 5"
                    :key="star"
                    class="w-4 h-4"
                    :class="star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-600'"
                  />
                </div>
              </div>
              <p class="text-gray-300">{{ review.comment }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import TikTokProductCard from '@/components/products/TikTokProductCard.vue'
import TikTokLiveCard from '@/components/lives/TikTokLiveCard.vue'
import StoriesGrid from '@/components/stories/StoriesGrid.vue'
import {
  ArrowLeftIcon,
  MoreVerticalIcon,
  FlagIcon,
  BanIcon,
  CheckIcon,
  PackageIcon,
  UsersIcon,
  StarIcon,
  LinkIcon,
  MapPinIcon,
  MessageCircleIcon,
  Loader2Icon,
  AlertTriangleIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

// State
const loading = ref(true)
const followBusy = ref(false)
const error = ref('')
const showMoreOptions = ref(false)

const user = ref(null)
const stats = ref(null)
const isFollowing = ref(false)

const products = ref([])
const followers = ref([])
const following = ref([])
const reviews = ref([])
const stories = ref([])
const lives = ref([])
const pagination = ref({ current: 1, last: 1, total: 0, perPage: 20 })

const activeTab = ref('products')

// Tabs configuration
const tabs = [
  { id: 'products', label: 'Produits' },
  { id: 'lives', label: 'Lives' },
  { id: 'followers', label: 'Abonnés' },
  { id: 'following', label: 'Abonnements' },
  { id: 'reviews', label: 'Évaluations' }
]

// Computed
const isAuthenticated = computed(() => authStore.isAuthenticated)
const isSelf = computed(() => {
  const id = Number(route.params.id)
  return authStore.user && authStore.user.id === id
})

const userAvatar = computed(() => {
  return user.value?.avatar || '/default-avatar.png'
})

// Methods
function onAvatarError(event) {
  event.target.src = '/default-avatar.png'
}

async function fetchUser() {
  try {
    const resp = await window.axios.get(`/users/${route.params.id}`)
    const payload = resp.data?.data
    user.value = payload?.user || null
    stats.value = payload?.stats || null
    isFollowing.value = Boolean(payload?.is_following)
  } catch (err) {
    console.error('Failed to fetch user:', err)
    error.value = 'Impossible de charger ce profil.'
  }
}

async function fetchProducts(page = 1) {
  try {
    const resp = await window.axios.get(`/users/${route.params.id}/products`, { params: { page } })
    const pageData = resp.data?.data
    products.value = Array.isArray(pageData?.data) ? pageData.data : []
    pagination.value = {
      current: Number(pageData?.current_page || 1),
      last: Number(pageData?.last_page || 1),
      total: Number(pageData?.total || 0),
      perPage: Number(pageData?.per_page || 20)
    }
  } catch (err) {
    console.error('Failed to fetch user products:', err)
    error.value = 'Impossible de charger les produits.'
  }
}

async function fetchFollowers() {
  try {
    const resp = await window.axios.get(`/users/${route.params.id}/followers`)
    followers.value = resp.data?.data || []
  } catch (err) {
    console.error('Failed to fetch followers:', err)
    error.value = 'Impossible de charger les abonnés.'
  }
}

async function fetchFollowing() {
  try {
    const resp = await window.axios.get(`/users/${route.params.id}/following`)
    following.value = resp.data?.data || []
  } catch (err) {
    console.error('Failed to fetch following:', err)
    error.value = 'Impossible de charger les abonnements.'
  }
}

async function fetchReviews() {
  try {
    const resp = await window.axios.get(`/users/${route.params.id}/reviews`)
    reviews.value = resp.data?.data || []
  } catch (err) {
    console.error('Failed to fetch reviews:', err)
    error.value = 'Impossible de charger les évaluations.'
  }
}

async function fetchStories() {
  try {
    const resp = await window.axios.get(`/users/${route.params.id}/stories`)
    stories.value = resp.data?.data || []
  } catch (err) {
    console.error('Failed to fetch stories:', err)
    // Don't set error for stories as they're optional
  }
}

async function fetchLives() {
  try {
    const resp = await window.axios.get(`/users/${route.params.id}/lives`)
    lives.value = resp.data?.data || []
  } catch (err) {
    console.error('Failed to fetch lives:', err)
    // Don't set error for lives as they're optional
  }
}

async function loadTabContent() {
  switch (activeTab.value) {
    case 'products':
      await fetchProducts()
      break
    case 'lives':
      await fetchLives()
      break
    case 'followers':
      await fetchFollowers()
      break
    case 'following':
      await fetchFollowing()
      break
    case 'reviews':
      await fetchReviews()
      break
  }
}

async function toggleFollow() {
  if (!isAuthenticated.value) {
    router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }
  if (isSelf.value || followBusy.value) return

  followBusy.value = true
  try {
    if (isFollowing.value) {
      const resp = await window.axios.delete(`/users/${route.params.id}/unfollow`)
      const data = resp.data?.data
      isFollowing.value = false
      if (stats.value) stats.value.followers_count = Math.max((stats.value.followers_count || 1) - 1, 0)
    } else {
      const resp = await window.axios.post(`/users/${route.params.id}/follow`)
      const data = resp.data?.data
      isFollowing.value = Boolean(data?.is_following ?? true)
      if (stats.value) stats.value.followers_count = (stats.value.followers_count || 0) + 1
    }
  } catch (err) {
    console.error('Failed to toggle follow:', err)
  } finally {
    followBusy.value = false
  }
}

async function toggleFollowUser(userId) {
  // Implementation for following/unfollowing other users
  console.log('Toggle follow user:', userId)
}

function goToPage(page) {
  if (page < 1 || page > pagination.value.last) return
  fetchProducts(page)
}

function goToMessages() {
  if (!isAuthenticated.value) {
    router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }
  const q = { user: user.value?.id }
  if (route.query.product) q.product = route.query.product
  router.push({ name: 'messages', query: q })
}

function reportUser() {
  showMoreOptions.value = false
  // Implementation for reporting user
  console.log('Report user:', user.value?.id)
}

function blockUser() {
  showMoreOptions.value = false
  // Implementation for blocking user
  console.log('Block user:', user.value?.id)
}

function retryLoad() {
  error.value = ''
  loading.value = true
  loadInitialData()
}

function formatDate(dateString) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

async function loadInitialData() {
  try {
    await Promise.all([
      fetchUser(),
      fetchProducts(),
      fetchStories(),
      fetchLives()
    ])
  } catch (err) {
    console.error('Failed to load initial data:', err)
  } finally {
    loading.value = false
  }
}

// Watchers
watch(activeTab, () => {
  loadTabContent()
})

watch(() => route.params.id, async () => {
  loading.value = true
  await loadInitialData()
})

// Lifecycle
onMounted(async () => {
  await loadInitialData()
})
</script>

<style scoped>
/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #1f2937;
}

::-webkit-scrollbar-thumb {
  background: #4b5563;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>

