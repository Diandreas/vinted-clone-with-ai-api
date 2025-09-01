<template>
  <div class="min-h-screen bg-white">
    <!-- Header avec image de couverture -->
    <div class="relative">
              <!-- Cover Image -->
        <div class="h-32 sm:h-40 md:h-48 lg:h-56 bg-gradient-to-br from-green-600 via-emerald-600 to-teal-700 relative overflow-hidden">
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
                  <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-white drop-shadow-lg">{{ user?.name || 'Utilisateur' }}</h1>
                  <span v-if="user?.username" class="text-green-100 text-sm sm:text-base font-medium">@{{ user.username }}</span>
                </div>
                <p v-if="user?.bio" class="text-green-50 text-sm max-w-md mb-2 leading-relaxed">{{ user.bio }}</p>
                <div class="flex flex-col sm:flex-row sm:items-center space-y-1 sm:space-y-0 sm:space-x-4 text-xs sm:text-sm text-green-100">
                  <span v-if="user?.location" class="flex items-center justify-center sm:justify-start">
                    <MapPinIcon class="w-3 h-3 sm:w-4 h-4 mr-1 sm:mr-2 text-green-200" />
                    {{ user.location }}
                  </span>
                  <span v-if="user?.website" class="flex items-center justify-center sm:justify-start">
                    <LinkIcon class="w-3 h-3 sm:w-4 h-4 mr-1 sm:mr-2 text-green-200" />
                    <a :href="user.website" target="_blank" class="text-green-200 hover:text-white transition-colors font-medium underline">
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
                    ? 'bg-white/90 hover:bg-white text-green-800 border border-white/20 backdrop-blur-sm' 
                    : 'bg-green-500 hover:bg-green-600 text-white shadow-lg'"
                >
                  <template v-if="followBusy">
                    <Loader2Icon class="w-3 h-3 animate-spin" />
                    <span>...</span>
                  </template>
                  <template v-else>
                    <UsersIcon v-if="!isFollowing" class="w-3 h-3" />
                    <CheckIcon v-else class="w-3 h-3" />
                    <span>{{ isFollowing ? 'Abonné' : 'Suivre' }} ({{ isFollowing ? 'OUI' : 'NON' }})</span>
                  </template>
                </button>
                

              </div>
              
              <!-- Bouton de déconnexion pour son propre profil -->
              <div v-if="isSelf" class="flex flex-col sm:flex-row items-center space-y-1 sm:space-y-0 sm:space-x-2">
                <button
                  @click="logout"
                  class="w-full sm:w-auto px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg transition-all duration-200 flex items-center justify-center space-x-1 shadow-lg hover:shadow-xl text-xs font-medium backdrop-blur-sm"
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
      <div class="bg-white/95 backdrop-blur-md border-b border-green-200/60 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-3 sm:py-4">
          <div class="grid grid-cols-4 gap-2 sm:gap-4 text-center">
            <div 
              class="cursor-pointer hover:bg-green-50 rounded-lg p-2 sm:p-3 transition-all duration-200 hover:shadow-md group" 
              @click="activeTab = 'products'"
            >
              <div class="text-lg sm:text-xl md:text-2xl font-bold text-green-600 mb-1 group-hover:text-green-700">{{ stats?.products_count ?? 0 }}</div>
              <div class="text-xs text-green-700">Produits</div>
            </div>
            <div 
              class="cursor-pointer hover:bg-green-50 rounded-lg p-2 sm:p-3 transition-all duration-200 hover:shadow-md group" 
              @click="activeTab = 'followers'"
            >
              <div class="text-lg sm:text-xl md:text-2xl font-bold text-green-600 mb-1 group-hover:text-green-700">{{ stats?.followers_count ?? 0 }}</div>
              <div class="text-xs text-green-700">Abonnés</div>
            </div>
            <div 
              class="cursor-pointer hover:bg-green-50 rounded-lg p-2 sm:p-3 transition-all duration-200 hover:shadow-md group" 
              @click="activeTab = 'following'"
            >
              <div class="text-lg sm:text-xl md:text-2xl font-bold text-green-600 mb-1 group-hover:text-green-700">{{ stats?.following_count ?? 0 }}</div>
              <div class="text-xs text-green-700">Abonnements</div>
            </div>

          </div>
        </div>
      </div>

      <!-- Content Tabs -->
      <div class="max-w-4xl mx-auto px-4 py-4 sm:py-6">
        <!-- Tab Navigation - Design compact mobile -->
        <div class="flex overflow-x-auto space-x-1 sm:space-x-4 border-b border-green-200 mb-4 sm:mb-6 pb-2">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="flex-shrink-0 pb-2 sm:pb-3 px-2 sm:px-4 relative transition-all duration-200 whitespace-nowrap text-sm sm:text-base font-medium"
            :class="activeTab === tab.id ? 'text-green-600' : 'text-green-500 hover:text-green-700'"
          >
            {{ tab.label }}
            <div
              v-if="activeTab === tab.id"
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-green-500 rounded-full"
            ></div>
          </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex items-center justify-center py-8 sm:py-12">
          <div class="text-center">
            <Loader2Icon class="h-8 w-8 sm:h-12 sm:w-12 animate-spin text-green-500 mx-auto mb-3 sm:mb-4" />
            <span class="text-green-600 text-sm sm:text-base font-medium">Chargement...</span>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center py-8 sm:py-12">
          <AlertTriangleIcon class="h-12 w-12 sm:h-16 sm:w-16 text-red-500 mx-auto mb-3 sm:mb-4" />
          <p class="text-red-600 mb-3 sm:mb-4 text-sm sm:text-base font-medium">{{ error }}</p>
          <button
            @click="retryLoad"
            class="px-4 sm:px-6 py-2 sm:py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl text-sm sm:text-base font-medium"
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
import api from '@/services/api'

import {
  ArrowLeftIcon,
  MoreVerticalIcon,
  FlagIcon,
  BanIcon,
  CheckIcon,
  PackageIcon,
  UsersIcon,
  UserIcon,
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

const pagination = ref({ current: 1, last: 1, total: 0, perPage: 20 })

const activeTab = ref('products')

// Tabs configuration
const tabs = [
  { id: 'products', label: 'Produits' },
  { id: 'followers', label: 'Abonnés' },
  { id: 'following', label: 'Abonnements' }
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
    const resp = await api.get(`/users/${route.params.id}`)
    const payload = resp.data?.data
    user.value = payload?.user || null
    stats.value = payload?.stats || null
    
    // Mise à jour explicite de l'état de suivi
    const followingStatus = Boolean(payload?.is_following)
    isFollowing.value = followingStatus

  } catch (err) {
    error.value = 'Impossible de charger ce profil.'
  }
}

async function fetchProducts(page = 1) {
  try {
    const resp = await api.get(`/users/${route.params.id}/products`, { params: { page } })
    const pageData = resp.data?.data
    products.value = Array.isArray(pageData?.data) ? pageData.data : []
    pagination.value = {
      current: Number(pageData?.current_page || 1),
      last: Number(pageData?.last_page || 1),
      total: Number(pageData?.total || 0),
      perPage: Number(pageData?.per_page || 20)
    }
  } catch (err) {
    error.value = 'Impossible de charger les produits.'
  }
}

async function fetchFollowers() {
  try {
    const resp = await api.get(`/users/${route.params.id}/followers`)
    
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
    error.value = 'Impossible de charger les abonnés.'
  }
}

async function fetchFollowing() {
  try {
    const resp = await api.get(`/users/${route.params.id}/following`)
    
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
    error.value = 'Impossible de charger les abonnements.'
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
      // L'utilisateur est déjà abonné, on le désabonne
      const resp = await api.delete(`/users/${route.params.id}/unfollow`)
      const data = resp.data?.data
      isFollowing.value = data.is_following
      if (stats.value) stats.value.followers_count = data.followers_count

    } else {
      // L'utilisateur n'est pas abonné, on l'abonne
      const resp = await api.post(`/users/${route.params.id}/follow`)
      const data = resp.data?.data
      isFollowing.value = data.is_following
      if (stats.value) stats.value.followers_count = data.followers_count

    }
  } catch (err) {
    // En cas d'erreur, on peut essayer de recharger l'état
    await fetchUser()
  } finally {
    followBusy.value = false
  }
}

async function toggleFollowUser(userId) {
  // Implementation for following/unfollowing other users
}

function goToPage(page) {
  if (page < 1 || page > pagination.value.last) return
  fetchProducts(page)
}



function logout() {
  authStore.logout()
  router.push({ name: 'login' })
}

function reportUser() {
  showMoreOptions.value = false
  // Implementation for reporting user
}

function blockUser() {
  showMoreOptions.value = false
  // Implementation for blocking user
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
    // Gestion silencieuse des erreurs
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