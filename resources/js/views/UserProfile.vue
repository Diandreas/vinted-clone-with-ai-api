<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black text-white">
    <!-- Header avec image de couverture -->
    <div class="relative">
      <!-- Cover Image -->
      <div class="h-40 sm:h-48 md:h-64 lg:h-80 bg-gradient-to-br from-purple-600 via-pink-600 to-red-600 relative overflow-hidden">
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
          class="absolute top-3 sm:top-4 left-3 sm:left-4 p-2 bg-black bg-opacity-50 rounded-full hover:bg-opacity-70 transition-all backdrop-blur-sm"
        >
          <ArrowLeftIcon class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
        </button>
        
        <!-- More Options -->
        <button
          v-if="!isSelf"
          @click="showMoreOptions = !showMoreOptions"
          class="absolute top-3 sm:top-4 right-3 sm:right-4 p-2 bg-black bg-opacity-50 rounded-full hover:bg-opacity-70 transition-all backdrop-blur-sm"
        >
          <MoreVerticalIcon class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
        </button>
        
        <!-- More Options Dropdown -->
        <div
          v-if="showMoreOptions"
          class="absolute top-14 sm:top-16 right-3 sm:right-4 bg-gray-900/95 backdrop-blur-sm rounded-xl shadow-xl border border-gray-700 py-2 z-50 min-w-[180px] sm:min-w-[200px]"
        >
          <button
            @click="reportUser"
            class="w-full px-3 sm:px-4 py-2 sm:py-3 text-left text-red-400 hover:bg-gray-800 transition-colors flex items-center rounded-lg mx-2 text-sm sm:text-base"
          >
            <FlagIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2 sm:mr-3" />
            Signaler
          </button>
          <button
            @click="blockUser"
            class="w-full px-3 sm:px-4 py-2 sm:py-3 text-left text-red-400 hover:bg-gray-800 transition-colors flex items-center rounded-lg mx-2 text-sm sm:text-base"
          >
            <BanIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-2 sm:mr-3" />
            Bloquer
          </button>
        </div>
      </div>
      
      <!-- Profile Info Overlay -->
      <div class="absolute -bottom-12 sm:-bottom-16 md:-bottom-20 left-0 right-0 px-3 sm:px-4 md:px-6">
        <div class="max-w-4xl mx-auto">
          <div class="flex flex-col sm:flex-row sm:items-end justify-between">
            <div class="flex flex-col sm:flex-row sm:items-end space-y-3 sm:space-y-0 sm:space-x-4 md:space-x-6">
              <!-- Avatar -->
              <div class="relative mx-auto sm:mx-0">
                <ProfileIcon
                  :src="user?.avatar"
                  :alt="user?.name || 'Utilisateur'"
                  :user-id="user?.id"
                  size="2xl"
                  :verified="user?.is_verified"
                  :fallback-to-initials="true"
                  class="border-4 border-white shadow-lg"
                />
              </div>
              
              <!-- User Info -->
              <div class="text-center sm:text-left mb-3 sm:mb-2">
                <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 mb-2">
                  <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-bold">{{ user?.name || 'Utilisateur' }}</h1>
                  <span v-if="user?.username" class="text-gray-300 text-base sm:text-lg">@{{ user.username }}</span>
                </div>
                <p v-if="user?.bio" class="text-gray-300 text-xs sm:text-sm md:text-base max-w-md mb-2 sm:mb-3">{{ user.bio }}</p>
                <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4 md:space-x-6 text-xs sm:text-sm text-gray-400">
                  <span v-if="user?.location" class="flex items-center justify-center sm:justify-start">
                    <MapPinIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" />
                    {{ user.location }}
                  </span>
                  <span v-if="user?.website" class="flex items-center justify-center sm:justify-start">
                    <LinkIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" />
                    <a :href="user.website" target="_blank" class="hover:text-white transition-colors">
                      Site web
                    </a>
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div v-if="!isSelf" class="flex flex-col sm:flex-row items-center space-y-3 sm:space-y-0 sm:space-x-3 mt-3 sm:mt-0">
              <button
                :disabled="followBusy"
                @click="toggleFollow"
                class="w-full sm:w-auto px-4 sm:px-6 py-2 sm:py-3 rounded-full transition-all flex items-center justify-center space-x-2 shadow-lg text-sm sm:text-base"
                :class="isFollowing 
                  ? 'bg-gray-700 hover:bg-gray-600 text-white' 
                  : 'bg-red-500 hover:bg-red-600 text-white'"
              >
                <template v-if="followBusy">
                  <Loader2Icon class="w-3 h-3 sm:w-4 sm:h-4 animate-spin" />
                  <span>...</span>
                </template>
                <template v-else>
                  <UsersIcon v-if="!isFollowing" class="w-3 h-3 sm:w-4 sm:h-4" />
                  <CheckIcon v-else class="w-3 h-3 sm:w-4 sm:h-4" />
                  <span>{{ isFollowing ? 'Abonné' : 'Suivre' }}</span>
                </template>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="pt-16 sm:pt-20 md:pt-24 lg:pt-28">
      <!-- Stats Bar -->
      <div class="bg-gray-800/50 backdrop-blur-sm border-b border-gray-700">
        <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 py-4 sm:py-6">
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 text-center">
            <div 
              class="cursor-pointer hover:bg-gray-700/50 rounded-xl p-3 sm:p-4 transition-all duration-200 hover:scale-105" 
              @click="activeTab = 'products'"
            >
              <div class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-1">{{ stats?.products_count ?? 0 }}</div>
              <div class="text-xs sm:text-sm text-gray-400">Produits</div>
            </div>
            <div 
              class="cursor-pointer hover:bg-gray-700/50 rounded-xl p-3 sm:p-4 transition-all duration-200 hover:scale-105" 
              @click="activeTab = 'followers'"
            >
              <div class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-1">{{ stats?.followers_count ?? 0 }}</div>
              <div class="text-xs sm:text-sm text-gray-400">Abonnés</div>
            </div>
            <div 
              class="cursor-pointer hover:bg-gray-700/50 rounded-xl p-3 sm:p-4 transition-all duration-200 hover:scale-105" 
              @click="activeTab = 'following'"
            >
              <div class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-1">{{ stats?.following_count ?? 0 }}</div>
              <div class="text-xs sm:text-sm text-gray-400">Abonnements</div>
            </div>
            <div 
              class="cursor-pointer hover:bg-gray-700/50 rounded-xl p-3 sm:p-4 transition-all duration-200 hover:scale-105" 
              @click="activeTab = 'reviews'"
            >
              <div class="text-xl sm:text-2xl md:text-3xl font-bold text-white mb-1">{{ (stats?.average_rating ?? 0).toFixed(1) }}</div>
              <div class="text-xs sm:text-sm text-gray-400">Note</div>
            </div>
          </div>
        </div>
      </div>



      <!-- Content Tabs -->
      <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 py-4 sm:py-6">
        <!-- Tab Navigation -->
        <div class="flex overflow-x-auto space-x-1 sm:space-x-4 md:space-x-8 border-b border-gray-700 mb-4 sm:mb-6 pb-2">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="flex-shrink-0 pb-3 sm:pb-4 px-2 sm:px-3 md:px-4 relative transition-colors whitespace-nowrap text-sm sm:text-base"
            :class="activeTab === tab.id ? 'text-white' : 'text-gray-400 hover:text-gray-300'"
          >
            {{ tab.label }}
            <div
              v-if="activeTab === tab.id"
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full"
            ></div>
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-12 sm:py-16">
          <div class="text-center">
            <Loader2Icon class="h-8 w-8 sm:h-12 sm:w-12 animate-spin text-indigo-500 mx-auto mb-3 sm:mb-4" />
            <span class="text-gray-400 text-sm sm:text-base">Chargement...</span>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-12 sm:py-16">
          <AlertTriangleIcon class="h-12 w-12 sm:h-16 sm:w-16 text-red-500 mx-auto mb-3 sm:mb-4" />
          <p class="text-red-400 mb-3 sm:mb-4 text-sm sm:text-base">{{ error }}</p>
          <button
            @click="retryLoad"
            class="px-4 sm:px-6 py-2 sm:py-3 bg-red-500 hover:bg-red-600 rounded-full transition-colors shadow-lg text-sm sm:text-base"
          >
            Réessayer
          </button>
        </div>

        <!-- Tab Content -->
        <div v-else class="space-y-4 sm:space-y-6">
          <!-- Products Tab -->
          <div v-if="activeTab === 'products'" class="space-y-4 sm:space-y-6">
            <div v-if="products.length === 0" class="text-center py-12 sm:py-16">
              <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-700 rounded-full mx-auto mb-3 sm:mb-4 flex items-center justify-center">
                <PackageIcon class="h-8 w-8 sm:h-10 sm:w-10 text-gray-500" />
              </div>
              <h3 class="text-lg sm:text-xl font-semibold text-gray-300 mb-2">Aucun produit</h3>
              <p class="text-gray-500 text-sm sm:text-base">Cet utilisateur n'a pas encore publié de produits.</p>
            </div>
            
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
              <TikTokProductCard
                v-for="product in products"
                :key="`product-${product.id}-${activeTab}`"
                :product="product"
              />
            </div>
            
            <!-- Pagination -->
            <div v-if="pagination.last > 1" class="flex items-center justify-center space-x-3 sm:space-x-4 mt-6 sm:mt-8">
              <button
                @click="goToPage(pagination.current - 1)"
                :disabled="pagination.current === 1"
                class="px-3 sm:px-4 py-2 rounded-full transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                :class="pagination.current === 1 
                  ? 'bg-gray-700 text-gray-500 cursor-not-allowed' 
                  : 'bg-gray-700 hover:bg-gray-600 text-white'"
              >
                Précédent
              </button>
              <span class="text-gray-400 px-3 sm:px-4 py-2 bg-gray-800 rounded-full text-sm sm:text-base">Page {{ pagination.current }} / {{ pagination.last }}</span>
              <button
                @click="goToPage(pagination.current + 1)"
                :disabled="pagination.current === pagination.last"
                class="px-3 sm:px-4 py-2 rounded-full transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                :class="pagination.current === pagination.last 
                  ? 'bg-gray-700 text-gray-500 cursor-not-allowed' 
                  : 'bg-gray-700 hover:bg-gray-600 text-white'"
              >
                Suivant
              </button>
            </div>
          </div>



          <!-- Followers Tab -->
          <div v-if="activeTab === 'followers'" class="space-y-3 sm:space-y-4">
            <div v-if="followers.length === 0" class="text-center py-12 sm:py-16">
              <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-700 rounded-full mx-auto mb-3 sm:mb-4 flex items-center justify-center">
                <UsersIcon class="h-8 w-8 sm:h-10 sm:w-10 text-gray-500" />
              </div>
              <h3 class="text-lg sm:text-xl font-semibold text-gray-300 mb-2">Aucun abonné</h3>
              <p class="text-gray-500 text-sm sm:text-base">Cet utilisateur n'a pas encore d'abonnés.</p>
            </div>
            
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
              <div
                v-for="follower in followers"
                :key="follower.id"
                class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 sm:p-4 hover:bg-gray-700/50 transition-colors border border-gray-700"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-2 sm:space-x-3">
                    <ProfileIcon
                      :src="follower.avatar"
                      :alt="follower.name"
                      :user-id="follower.id"
                      size="lg"
                      :fallback-to-initials="true"
                    />
                    <div class="min-w-0 flex-1">
                      <h4 class="font-semibold text-white text-sm sm:text-base truncate">{{ follower.name }}</h4>
                      <p class="text-xs sm:text-sm text-gray-400 truncate">@{{ follower.username }}</p>
                    </div>
                  </div>
                  <button
                    v-if="!isSelf"
                    @click="toggleFollowUser(follower.id)"
                    class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full transition-colors text-xs sm:text-sm flex-shrink-0"
                    :class="follower.is_following 
                      ? 'bg-gray-600 hover:bg-gray-500 text-white' 
                      : 'bg-indigo-600 hover:bg-indigo-500 text-white'"
                  >
                    {{ follower.is_following ? 'Abonné' : 'Suivre' }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Following Tab -->
          <div v-if="activeTab === 'following'" class="space-y-3 sm:space-y-4">
            <div v-if="following.length === 0" class="text-center py-12 sm:py-16">
              <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-700 rounded-full mx-auto mb-3 sm:mb-4 flex items-center justify-center">
                <UsersIcon class="h-8 w-8 sm:h-10 sm:w-10 text-gray-500" />
              </div>
              <h3 class="text-lg sm:text-xl font-semibold text-gray-300 mb-2">Aucun abonnement</h3>
              <p class="text-gray-500 text-sm sm:text-base">Cet utilisateur ne suit personne pour le moment.</p>
            </div>
            
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
              <div
                v-for="followed in following"
                :key="followed.id"
                class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 sm:p-4 hover:bg-gray-700/50 transition-colors border border-gray-700"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-2 sm:space-x-3">
                    <ProfileIcon
                      :src="followed.avatar"
                      :alt="followed.name"
                      :user-id="followed.id"
                      size="lg"
                      :fallback-to-initials="true"
                    />
                    <div class="min-w-0 flex-1">
                      <h4 class="font-semibold text-white text-sm sm:text-base truncate">{{ followed.name }}</h4>
                      <p class="text-xs sm:text-sm text-gray-400 truncate">@{{ followed.username }}</p>
                    </div>
                  </div>
                  <button
                    v-if="!isSelf"
                    @click="toggleFollowUser(followed.id)"
                    class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full transition-colors text-xs sm:text-sm flex-shrink-0"
                    :class="followed.is_following 
                      ? 'bg-gray-600 hover:bg-gray-500 text-white' 
                      : 'bg-indigo-600 hover:bg-indigo-500 text-white'"
                  >
                    {{ followed.is_following ? 'Abonné' : 'Suivre' }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Reviews Tab -->
          <div v-if="activeTab === 'reviews'" class="space-y-3 sm:space-y-4">
            <div v-if="reviews.length === 0" class="text-center py-12 sm:py-16">
              <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-700 rounded-full mx-auto mb-3 sm:mb-4 flex items-center justify-center">
                <StarIcon class="h-8 w-8 sm:h-10 sm:w-10 text-gray-500" />
              </div>
              <h3 class="text-lg sm:text-xl font-semibold text-gray-300 mb-2">Aucune évaluation</h3>
              <p class="text-gray-500 text-sm sm:text-base">Cet utilisateur n'a pas encore reçu d'évaluations.</p>
            </div>
            
            <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
              <div
                v-for="review in reviews"
                :key="review.id"
                class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-4 sm:p-6 border border-gray-700"
              >
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                  <div class="flex items-center space-x-2 sm:space-x-3">
                    <ProfileIcon
                      :src="review.user.avatar"
                      :alt="review.user.name"
                      :user-id="review.user.id"
                      size="md"
                      :fallback-to-initials="true"
                    />
                    <div>
                      <h4 class="font-semibold text-white text-sm sm:text-base">{{ review.user.name }}</h4>
                      <p class="text-xs sm:text-sm text-gray-400">{{ formatDate(review.created_at) }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-1">
                    <StarIcon
                      v-for="star in 5"
                      :key="star"
                      class="w-3 h-3 sm:w-4 sm:h-4"
                      :class="star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-600'"
                    />
                  </div>
                </div>
                <p class="text-gray-300 text-sm sm:text-base">{{ review.comment }}</p>
              </div>
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


import ProfileIcon from '@/components/ui/ProfileIcon.vue'
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

const pagination = ref({ current: 1, last: 1, total: 0, perPage: 20 })

const activeTab = ref('products')

// Tabs configuration
const tabs = [
  { id: 'products', label: 'Produits' },
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



// Methods

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



async function loadTabContent() {
  switch (activeTab.value) {
    case 'products':
      await fetchProducts()
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
      fetchProducts()
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

