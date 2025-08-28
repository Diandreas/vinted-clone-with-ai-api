<template>
  <div class="min-h-screen bg-white">
    <!-- Header avec image de couverture -->
    <div class="relative">
      <!-- Cover Image -->
      <div class="h-32 sm:h-40 md:h-48 lg:h-56 bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 relative overflow-hidden">
        <img
          v-if="user?.cover_image"
          :src="user.cover_image"
          :alt="`Couverture de ${user?.name}`"
          class="w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
        
        <!-- Back Button -->
        <button
          @click="router.back()"
          class="absolute top-3 left-3 p-2 bg-white/90 backdrop-blur-sm rounded-full hover:bg-white transition-all shadow-soft"
        >
          <ArrowLeftIcon class="w-4 h-4 text-gray-700" />
        </button>
        
        <!-- More Options -->
        <button
          v-if="!isSelf"
          @click="showMoreOptions = !showMoreOptions"
          class="absolute top-3 right-3 p-2 bg-white/90 backdrop-blur-sm rounded-full hover:bg-white transition-all shadow-soft"
        >
          <MoreVerticalIcon class="w-4 h-4 text-gray-700" />
        </button>
        
        <!-- More Options Dropdown -->
        <div
          v-if="showMoreOptions"
          class="absolute top-12 right-3 bg-white rounded-xl shadow-medium border border-gray-100 py-2 z-50 min-w-[160px]"
        >
          <button
            @click="reportUser"
            class="w-full px-4 py-2 text-left text-red-600 hover:bg-red-50 transition-colors flex items-center rounded-lg mx-2 text-sm"
          >
            <FlagIcon class="w-4 h-4 mr-3" />
            Signaler
          </button>
          <button
            @click="blockUser"
            class="w-full px-4 py-2 text-left text-red-600 hover:bg-red-50 transition-colors flex items-center rounded-lg mx-2 text-sm"
          >
            <BanIcon class="w-4 h-4 mr-3" />
            Bloquer
          </button>
        </div>
      </div>
      
      <!-- Profile Info Overlay -->
      <div class="absolute -bottom-8 sm:-bottom-12 left-0 right-0 px-4">
        <div class="max-w-4xl mx-auto">
          <div class="flex flex-col sm:flex-row sm:items-end justify-between">
            <div class="flex flex-col sm:flex-row sm:items-end space-y-3 sm:space-y-0 sm:space-x-4">
              <!-- Avatar -->
              <div class="relative mx-auto sm:mx-0">
                <ProfileIcon
                  :src="user?.avatar"
                  :alt="user?.name || 'Utilisateur'"
                  :user-id="user?.id"
                  size="2xl"
                  :verified="user?.is_verified"
                  :fallback-to-initials="true"
                  class="border-4 border-white shadow-medium"
                />
              </div>
              
              <!-- User Info -->
              <div class="text-center sm:text-left mb-3 sm:mb-2">
                <div class="flex flex-col sm:flex-row sm:items-center space-y-1 sm:space-y-0 sm:space-x-3 mb-2">
                  <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900">{{ user?.name || 'Utilisateur' }}</h1>
                  <span v-if="user?.username" class="text-gray-500 text-sm sm:text-base">@{{ user.username }}</span>
                </div>
                <p v-if="user?.bio" class="text-gray-600 text-sm max-w-md mb-2">{{ user.bio }}</p>
                <div class="flex flex-col sm:flex-row sm:items-center space-y-1 sm:space-y-0 sm:space-x-4 text-xs sm:text-sm text-gray-500">
                  <span v-if="user?.location" class="flex items-center justify-center sm:justify-start">
                    <MapPinIcon class="w-3 h-3 sm:w-4 h-4 mr-1 sm:mr-2" />
                    {{ user.location }}
                  </span>
                  <span v-if="user?.website" class="flex items-center justify-center sm:justify-start">
                    <LinkIcon class="w-3 h-3 sm:w-4 h-4 mr-1 sm:mr-2" />
                    <a :href="user.website" target="_blank" class="text-primary-600 hover:text-primary-700 transition-colors">
                      Site web
                    </a>
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-center space-y-1 sm:space-y-0 sm:space-x-2 mt-2 sm:mt-0">
              <!-- Abonnement/désabonnement compact -->
              <div v-if="!isSelf" class="flex flex-col sm:flex-row items-center space-y-1 sm:space-y-0 sm:space-x-2">
                <button
                  :disabled="followBusy"
                  @click="toggleFollow"
                  class="w-full sm:w-auto px-3 py-1.5 rounded-lg transition-all flex items-center justify-center space-x-1 shadow-soft text-xs font-medium"
                  :class="isFollowing 
                    ? 'bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200' 
                    : 'bg-primary-500 hover:bg-primary-600 text-white'"
                >
                  <template v-if="followBusy">
                    <Loader2Icon class="w-3 h-3 animate-spin" />
                    <span>...</span>
                  </template>
                  <template v-else>
                    <UsersIcon v-if="!isFollowing" class="w-3 h-3" />
                    <CheckIcon v-else class="w-3 h-3" />
                    <span>{{ isFollowing ? 'Abonné' : 'Suivre' }}</span>
                  </template>
                </button>
                
                <!-- Message Button -->
                <button
                  @click="goToMessages"
                  class="w-full sm:w-auto px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors flex items-center justify-center space-x-1 shadow-soft text-xs font-medium border border-gray-200"
                >
                  <MessageCircleIcon class="w-3 h-3" />
                  <span>Message</span>
                </button>
              </div>
              
              <!-- Bouton de déconnexion pour son propre profil -->
              <div v-if="isSelf" class="flex flex-col sm:flex-row items-center space-y-1 sm:space-y-0 sm:space-x-2">
                <button
                  @click="logout"
                  class="w-full sm:w-auto px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors flex items-center justify-center space-x-1 shadow-soft text-xs font-medium"
                >
                  <LogOutIcon class="w-3 h-3" />
                  <span>Se déconnecter</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="pt-12 sm:pt-16 md:pt-20">
      <!-- Stats Bar - Design compact mobile -->
      <div class="bg-gray-50 border-b border-gray-100">
        <div class="max-w-4xl mx-auto px-4 py-3 sm:py-4">
          <div class="grid grid-cols-4 gap-2 sm:gap-4 text-center">
            <div 
              class="cursor-pointer hover:bg-white rounded-lg p-2 sm:p-3 transition-all duration-200 hover:shadow-soft" 
              @click="activeTab = 'products'"
            >
              <div class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-1">{{ stats?.products_count ?? 0 }}</div>
              <div class="text-xs text-gray-500">Produits</div>
            </div>
            <div 
              class="cursor-pointer hover:bg-white rounded-lg p-2 sm:p-3 transition-all duration-200 hover:shadow-soft" 
              @click="activeTab = 'followers'"
            >
              <div class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-1">{{ stats?.followers_count ?? 0 }}</div>
              <div class="text-xs text-gray-500">Abonnés</div>
            </div>
            <div 
              class="cursor-pointer hover:bg-white rounded-lg p-2 sm:p-3 transition-all duration-200 hover:shadow-soft" 
              @click="activeTab = 'following'"
            >
              <div class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-1">{{ stats?.following_count ?? 0 }}</div>
              <div class="text-xs text-gray-500">Abonnements</div>
            </div>
            <div 
              class="cursor-pointer hover:bg-white rounded-lg p-2 sm:p-3 transition-all duration-200 hover:shadow-soft" 
              @click="activeTab = 'reviews'"
            >
              <div class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-1">{{ (stats?.average_rating ?? 0).toFixed(1) }}</div>
              <div class="text-xs text-gray-500">Note</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Content Tabs -->
      <div class="max-w-4xl mx-auto px-4 py-4 sm:py-6">
        <!-- Tab Navigation - Design compact mobile -->
        <div class="flex overflow-x-auto space-x-1 sm:space-x-4 border-b border-gray-200 mb-4 sm:mb-6 pb-2">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="flex-shrink-0 pb-2 sm:pb-3 px-2 sm:px-4 relative transition-colors whitespace-nowrap text-sm sm:text-base font-medium"
            :class="activeTab === tab.id ? 'text-primary-600' : 'text-gray-500 hover:text-gray-700'"
          >
            {{ tab.label }}
            <div
              v-if="activeTab === tab.id"
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-500 rounded-full"
            ></div>
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-8 sm:py-12">
          <div class="text-center">
            <Loader2Icon class="h-8 w-8 sm:h-12 sm:w-12 animate-spin text-primary-500 mx-auto mb-3 sm:mb-4" />
            <span class="text-gray-500 text-sm sm:text-base">Chargement...</span>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-8 sm:py-12">
          <AlertTriangleIcon class="h-12 w-12 sm:h-16 sm:w-16 text-red-500 mx-auto mb-3 sm:mb-4" />
          <p class="text-red-600 mb-3 sm:mb-4 text-sm sm:text-base">{{ error }}</p>
          <button
            @click="retryLoad"
            class="px-4 sm:px-6 py-2 sm:py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-colors shadow-soft text-sm sm:text-base font-medium"
          >
            Réessayer
          </button>
        </div>

        <!-- Tab Content -->
        <div v-else class="space-y-3 sm:space-y-4">
          <!-- Products Tab -->
          <div v-if="activeTab === 'products'" class="space-y-3 sm:space-y-4">
            <div v-if="products.length === 0" class="text-center py-6 sm:py-8">
              <PackageIcon class="mx-auto h-7 w-7 sm:h-10 sm:w-10 text-gray-300 mb-2 sm:mb-3" />
              <h3 class="text-base sm:text-lg font-medium text-gray-700 mb-1">Aucun produit</h3>
              <p class="text-gray-500 text-xs sm:text-sm">Cet utilisateur n'a pas encore mis de produits en vente.</p>
            </div>
            
            <!-- Products Grid -->
            <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
              <TikTokProductCard
                v-for="product in products"
                :key="product.id"
                :product="product"
                class="w-full"
              />
            </div>
            
            <!-- Pagination -->
            <div v-if="pagination.last > 1" class="flex justify-center mt-6">
              <div class="flex space-x-1">
                <button
                  @click="goToPage(pagination.current - 1)"
                  :disabled="pagination.current <= 1"
                  class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-200 transition-colors"
                >
                  Précédent
                </button>
                <span class="px-3 py-2 text-sm text-gray-500">
                  {{ pagination.current }} / {{ pagination.last }}
                </span>
                <button
                  @click="goToPage(pagination.current + 1)"
                  :disabled="pagination.current >= pagination.last"
                  class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-200 transition-colors"
                >
                  Suivant
                </button>
              </div>
            </div>
          </div>

          <!-- Followers Tab -->
          <div v-if="activeTab === 'followers'" class="space-y-2 sm:space-y-3">
            <div v-if="followers.length === 0" class="text-center py-6 sm:py-8">
              <UserIcon class="mx-auto h-7 w-7 sm:h-10 sm:w-10 text-gray-300 mb-2 sm:mb-3" />
              <h3 class="text-base sm:text-lg font-medium text-gray-700 mb-1">Aucun abonné</h3>
              <p class="text-gray-500 text-xs sm:text-sm">Cet utilisateur n'a pas encore d'abonnés.</p>
            </div>
            <div
              v-for="follower in followers"
              :key="follower.id"
              class="flex items-center justify-between bg-white rounded-md shadow-soft px-2 py-1 sm:px-3 sm:py-2"
            >
              <div class="flex items-center space-x-2">
                <ProfileIcon :src="follower.avatar" :alt="follower.name || 'Utilisateur'" :user-id="follower.id" size="sm" class="w-6 h-6 sm:w-8 sm:h-8" />
                <div>
                  <h4 class="font-semibold text-gray-900 text-xs sm:text-sm truncate">{{ follower.name || 'Utilisateur' }}</h4>
                  <p class="text-xs text-gray-500 truncate">@{{ follower.username || 'username' }}</p>
                </div>
              </div>
              <button
                @click="toggleFollowUser(follower.id)"
                class="px-2 py-0.5 sm:px-3 sm:py-1 bg-gray-100 text-gray-700 text-xs rounded-md hover:bg-gray-200 border border-gray-200 transition-colors"
              >
                {{ follower.is_following ? 'Abonné' : 'Suivre' }}
              </button>
            </div>
          </div>

          <!-- Following Tab -->
          <div v-if="activeTab === 'following'" class="space-y-2 sm:space-y-3">
            <div v-if="following.length === 0" class="text-center py-6 sm:py-8">
              <UsersIcon class="mx-auto h-7 w-7 sm:h-10 sm:w-10 text-gray-300 mb-2 sm:mb-3" />
              <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-1">Aucun abonnement</h3>
              <p class="text-gray-600 text-xs sm:text-sm">Découvrez et suivez d'autres vendeurs</p>
            </div>
            <div
              v-for="followed in following"
              :key="followed.id"
              class="flex items-center justify-between bg-white rounded-md shadow-soft px-2 py-1 sm:px-3 sm:py-2"
            >
              <div class="flex items-center space-x-2">
                <ProfileIcon :src="followed.avatar" :alt="followed.name || 'Utilisateur'" :user-id="followed.id" size="sm" class="w-6 h-6 sm:w-8 sm:h-8" />
                <div>
                  <h4 class="font-semibold text-gray-900 text-xs sm:text-sm truncate">{{ followed.name || 'Utilisateur' }}</h4>
                  <p class="text-xs text-gray-500 truncate">@{{ followed.username || 'username' }}</p>
                </div>
              </div>
              <button
                @click="toggleFollowUser(followed.id)"
                class="px-2 py-0.5 sm:px-3 sm:py-1 bg-gray-100 text-gray-700 text-xs rounded-md hover:bg-gray-200 border border-gray-200 transition-colors"
              >
                {{ followed.is_following ? 'Abonné' : 'Suivre' }}
              </button>
            </div>
          </div>

          <!-- Reviews Tab -->
          <div v-if="activeTab === 'reviews'" class="space-y-3 sm:space-y-4">
            <div v-if="reviews.length === 0" class="text-center py-6 sm:py-8">
              <StarIcon class="mx-auto h-7 w-7 sm:h-10 sm:w-10 text-gray-300 mb-2 sm:mb-3" />
              <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-1">Aucune évaluation</h3>
              <p class="text-gray-600 text-xs sm:text-sm">Cet utilisateur n'a pas encore d'évaluations</p>
            </div>
            <div
              v-for="review in reviews"
              :key="review?.id || Math.random()"
              v-if="review && review.id"
              class="bg-white rounded-lg shadow-soft p-3 sm:p-4"
            >
              <div class="flex items-start space-x-3">
                <ProfileIcon 
                  :src="review.user?.avatar" 
                  :alt="review.user?.name" 
                  :user-id="review.user?.id" 
                  size="sm"
                  class="w-8 h-8 sm:w-10 sm:h-10 flex-shrink-0" 
                />
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between mb-2">
                    <div>
                      <h4 class="font-semibold text-gray-900 text-sm sm:text-base">{{ review.user?.name }}</h4>
                      <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(review.created_at) }}</p>
                    </div>
                    <div class="flex items-center space-x-1">
                      <StarIcon
                        v-for="star in 5"
                        :key="star"
                        class="w-3 h-3 sm:w-4 h-4"
                        :class="star <= review.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'"
                      />
                      <span class="text-xs sm:text-sm text-gray-500 ml-1">{{ review.rating }}/5</span>
                    </div>
                  </div>
                  <p class="text-gray-700 text-sm sm:text-base">{{ review.comment }}</p>
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
  UserIcon,
  StarIcon,
  LinkIcon,
  MapPinIcon,
  MessageCircleIcon,
  LogOutIcon,
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
    
    // Gestion correcte de la pagination Laravel
    if (resp.data?.data?.data) {
      // Structure paginée Laravel
      followers.value = resp.data.data.data
    } else if (Array.isArray(resp.data?.data)) {
      // Structure simple
      followers.value = resp.data.data
    } else {
      followers.value = []
    }
  } catch (err) {
    console.error('Failed to fetch followers:', err)
    error.value = 'Impossible de charger les abonnés.'
  }
}

async function fetchFollowing() {
  try {
    const resp = await window.axios.get(`/users/${route.params.id}/following`)
    
    // Gestion correcte de la pagination Laravel
    if (resp.data?.data?.data) {
      // Structure paginée Laravel
      following.value = resp.data.data.data
    } else if (Array.isArray(resp.data?.data)) {
      // Structure simple
      following.value = resp.data.data
    } else {
      following.value = []
    }
  } catch (err) {
    console.error('Failed to fetch following:', err)
    error.value = 'Impossible de charger les abonnements.'
  }
}

async function fetchReviews() {
  try {
    const resp = await window.axios.get(`/users/${route.params.id}/reviews`)
    
    // Gestion correcte de la pagination Laravel
    if (resp.data?.data?.data) {
      // Structure paginée Laravel
      reviews.value = resp.data.data.data
    } else if (Array.isArray(resp.data?.data)) {
      // Structure simple
      reviews.value = resp.data.data
    } else {
      reviews.value = []
    }
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
      await window.axios.delete(`/users/${route.params.id}/unfollow`)
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

function logout() {
  authStore.logout()
  router.push({ name: 'login' })
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