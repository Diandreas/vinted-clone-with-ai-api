<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50">
    <!-- Header avec navigation -->
    <div class="bg-white/90 backdrop-blur-sm shadow-sm border-b border-green-200/50 sticky top-0 z-10">
      <div class="max-w-4xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <button
            @click="router.back()"
            class="p-2 rounded-lg bg-green-100 text-green-700 hover:bg-green-200 transition-colors"
          >
            <ArrowLeftIcon class="w-5 h-5" />
          </button>
          
          <h1 class="text-lg font-semibold text-green-900">Profil</h1>
          
          <RouterLink
            to="/profile/edit"
            class="p-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition-colors"
          >
            <EditIcon class="w-5 h-5" />
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-4xl mx-auto px-4 py-6">
      
      <!-- Section Profil -->
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-green-200/50 p-6 mb-6">
        <!-- Avatar et infos principales -->
        <div class="text-center mb-6">
          <div class="relative inline-block mb-4">
            <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-br from-green-500 via-emerald-500 to-teal-600 flex items-center justify-center text-white text-2xl sm:text-3xl font-bold border-4 border-white shadow-lg">
              {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
            </div>
            <div class="absolute -bottom-1 -right-1 w-5 h-5 sm:w-6 sm:h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
              <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 bg-white rounded-full"></div>
            </div>
          </div>
          
          <h2 class="text-2xl sm:text-3xl font-bold text-green-900 mb-2">{{ user?.name || 'Utilisateur' }}</h2>
          <p v-if="user?.username" class="text-green-600 text-lg mb-3">@{{ user.username }}</p>
          <p v-if="user?.bio" class="text-gray-600 text-sm max-w-md mx-auto">{{ user.bio }}</p>
          
          <div v-if="user?.is_verified" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 mt-3">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Vérifié
          </div>
        </div>
        
        <!-- Infos supplémentaires -->
        <div v-if="user?.location || user?.website" class="flex flex-col sm:flex-row justify-center items-center space-y-2 sm:space-y-0 sm:space-x-6 text-sm text-gray-600 mb-6">
          <span v-if="user?.location" class="flex items-center">
            <MapPinIcon class="w-4 h-4 mr-2 text-green-600" />
            {{ user.location }}
          </span>
          <span v-if="user?.website" class="flex items-center">
            <LinkIcon class="w-4 h-4 mr-2 text-green-600" />
            <a :href="user.website" target="_blank" class="text-green-600 hover:text-green-700 transition-colors">
              Site web
            </a>
          </span>
        </div>
        
        <!-- Actions rapides -->
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 justify-center">
          <RouterLink
            to="/products/create"
            class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Vendre un article
          </RouterLink>
          
          <RouterLink
            to="/profile/edit"
            class="inline-flex items-center justify-center px-6 py-3 bg-white text-green-700 border-2 border-green-300 font-semibold rounded-xl hover:bg-green-50 hover:border-green-400 transition-all duration-200 shadow-md hover:shadow-lg"
          >
            <EditIcon class="w-5 h-5 mr-2" />
            Modifier profil
          </RouterLink>
          
          <!-- Bouton de déconnexion -->
          <button
            @click="logout"
            class="inline-flex items-center justify-center px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg"
          >
            <LogOutIcon class="w-5 h-5 mr-2" />
            Se déconnecter
          </button>
        </div>
      </div>

      <!-- Stats principales -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-green-200/50 p-4 text-center hover:shadow-xl transition-all duration-200">
          <div class="text-2xl font-bold text-green-600 mb-1">{{ stats?.products_count || 0 }}</div>
          <div class="text-sm text-gray-600">Produits</div>
        </div>
        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-green-200/50 p-4 text-center hover:shadow-xl transition-all duration-200">
          <div class="text-2xl font-bold text-green-600 mb-1">{{ stats?.followers_count || 0 }}</div>
          <div class="text-sm text-gray-600">Followers</div>
        </div>
        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-green-200/50 p-4 text-center hover:shadow-xl transition-all duration-200">
          <div class="text-2xl font-bold text-green-600 mb-1">{{ stats?.following_count || 0 }}</div>
          <div class="text-sm text-gray-600">Following</div>
        </div>
        <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-green-200/50 p-4 text-center hover:shadow-xl transition-all duration-200">
          <div class="text-2xl font-bold text-green-600 mb-1">{{ stats?.total_sales || 0 }}</div>
          <div class="text-sm text-gray-600">Ventes</div>
        </div>
      </div>

      <!-- Navigation par onglets -->
      <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-green-200/50 mb-6">
        <!-- Tabs navigation -->
        <div class="flex border-b border-green-200/50">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'flex-1 px-4 py-3 text-sm font-medium transition-all duration-200',
              activeTab === tab.id
                ? 'text-green-700 border-b-2 border-green-600 bg-green-50/50'
                : 'text-gray-600 hover:text-green-600 hover:bg-green-50/30'
            ]"
          >
            <div class="flex items-center justify-center space-x-2">
              <component :is="tab.icon" class="w-4 h-4" />
              <span class="hidden sm:inline">{{ tab.label }}</span>
            </div>
          </button>
        </div>
        
        <!-- Tab content -->
        <div class="p-4 sm:p-6">
          <!-- Produits -->
          <div v-if="activeTab === 'products'" class="space-y-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-green-900">Mes Produits</h3>
              <RouterLink 
                to="/products/create"
                class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
              >
                <PlusIcon class="w-4 h-4 mr-2" />
                Ajouter
              </RouterLink>
            </div>
            
            <div v-if="loadingProducts" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <ProductSkeleton v-for="i in 4" :key="i" />
            </div>
            
            <div v-else-if="products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <ProductCard
                v-for="product in products"
                :key="product.id"
                :product="product"
                :show-actions="true"
                @edit="editProduct"
                @delete="deleteProduct"
              />
            </div>
            
            <div v-else class="text-center py-8">
              <PackageIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
              <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun produit</h3>
              <p class="text-gray-600 mb-4">Commencez par créer votre premier produit</p>
              <RouterLink 
                to="/products/create"
                class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
              >
                <PlusIcon class="w-5 h-5 mr-2" />
                Créer un produit
              </RouterLink>
            </div>
          </div>

          <!-- Followers -->
          <div v-if="activeTab === 'followers'" class="space-y-4">
            <h3 class="text-lg font-semibold text-green-900 mb-4">Mes Followers</h3>
            
            <div v-if="loadingFollowers" class="space-y-4">
              <div v-for="i in 3" :key="i" class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg animate-pulse">
                <div class="w-10 h-10 bg-gray-300 rounded-full"></div>
                <div class="flex-1">
                  <div class="h-4 bg-gray-300 rounded w-3/4 mb-2"></div>
                  <div class="h-3 bg-gray-300 rounded w-1/2"></div>
                </div>
              </div>
            </div>
            
            <div v-else-if="followers.length > 0" class="space-y-3">
              <div
                v-for="follower in followers"
                :key="follower.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white font-semibold">
                    {{ follower.name?.charAt(0)?.toUpperCase() || 'U' }}
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ follower.name }}</div>
                    <div class="text-sm text-gray-600">@{{ follower.username }}</div>
                  </div>
                </div>
                <RouterLink
                  :to="`/users/${follower.id}`"
                  class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors text-center"
                >
                  Voir profil
                </RouterLink>
              </div>
            </div>
            
            <div v-else class="text-center py-8">
              <UsersIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
              <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun follower</h3>
              <p class="text-gray-600">Partagez vos produits pour attirer des followers</p>
            </div>
          </div>

          <!-- Following -->
          <div v-if="activeTab === 'following'" class="space-y-4">
            <h3 class="text-lg font-semibold text-green-900 mb-4">Mes Abonnements</h3>
            
            <div v-if="loadingFollowing" class="space-y-4">
              <div v-for="i in 3" :key="i" class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg animate-pulse">
                <div class="w-10 h-10 bg-gray-300 rounded-full"></div>
                <div class="flex-1">
                  <div class="h-4 bg-gray-300 rounded w-3/4 mb-2"></div>
                  <div class="h-3 bg-gray-300 rounded w-1/2"></div>
                </div>
              </div>
            </div>
            
            <div v-else-if="following.length > 0" class="space-y-3">
              <div
                v-for="followed in following"
                :key="followed.id"
                class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white font-semibold">
                    {{ followed.name?.charAt(0)?.toUpperCase() || 'U' }}
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ followed.name }}</div>
                    <div class="text-sm text-gray-600">@{{ followed.username }}</div>
                  </div>
                </div>
                <button 
                  @click="unfollowUser(followed.id)"
                  class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition-colors"
                >
                  Se désabonner
                </button>
              </div>
            </div>
            
            <div v-else class="text-center py-8">
              <UsersIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
              <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun abonnement</h3>
              <p class="text-gray-600">Découvrez et suivez d'autres vendeurs</p>
            </div>
          </div>

          <!-- Activité -->
          <div v-if="activeTab === 'activity'" class="space-y-4">
            <h3 class="text-lg font-semibold text-green-900 mb-4">Mon Activité</h3>
            
            <div v-if="loadingActivity" class="space-y-4">
              <div v-for="i in 3" :key="i" class="p-4 bg-gray-50 rounded-lg animate-pulse">
                <div class="h-4 bg-gray-300 rounded w-3/4 mb-2"></div>
                <div class="h-3 bg-gray-300 rounded w-1/2"></div>
              </div>
            </div>
            
            <div v-else-if="recentActivity.length > 0" class="space-y-3">
              <div
                v-for="activity in recentActivity"
                :key="activity.id"
                class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-start space-x-3">
                  <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <component :is="getActivityIcon(activity.type)" class="w-4 h-4 text-green-600" />
                  </div>
                  <div class="flex-1">
                    <div class="text-sm text-gray-900">{{ activity.description }}</div>
                    <div class="text-xs text-gray-500 mt-1">{{ formatDate(activity.created_at) }}</div>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-8">
              <ActivityIcon class="mx-auto h-12 w-12 text-gray-400 mb-4" />
              <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune activité</h3>
              <p class="text-gray-600">Vos actions apparaîtront ici</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDashboardStore } from '@/stores/dashboard'
import ProductCard from '@/components/products/ProductCard.vue'
import ProductSkeleton from '@/components/skeletons/ProductSkeleton.vue'
import {
  ArrowLeftIcon,
  EditIcon,
  PlusIcon,
  PackageIcon,
  UsersIcon,
  ActivityIcon,
  MapPinIcon,
  LinkIcon,
  HeartIcon,

  ShoppingCartIcon,
  StarIcon,
  EyeIcon,
  LogOutIcon
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const dashboardStore = useDashboardStore()

// Reactive data
const activeTab = ref('products')
const loadingProducts = ref(false)
const loadingFollowers = ref(false)
const loadingFollowing = ref(false)
const loadingActivity = ref(false)
const products = ref([])
const followers = ref([])
const following = ref([])
const recentActivity = ref([])

// Computed
const user = computed(() => authStore.user)
const stats = computed(() => dashboardStore.stats)

// Tabs configuration
const tabs = [
  { id: 'products', label: 'Produits', icon: PackageIcon },
  { id: 'followers', label: 'Followers', icon: UsersIcon },
  { id: 'following', label: 'Following', icon: UsersIcon },
  { id: 'activity', label: 'Activité', icon: ActivityIcon }
]

// Methods
const loadProducts = async () => {
  loadingProducts.value = true
  try {
    const response = await window.axios.get('/products/my-products', {
      params: { per_page: 20 }
    })
    products.value = response.data.data?.data || []
  } catch (error) {
    console.error('Error loading products:', error)
    products.value = []
  } finally {
    loadingProducts.value = false
  }
}

const loadFollowers = async () => {
  loadingFollowers.value = true
  try {
    const response = await window.axios.get('/users/my-followers')
    // Les followers sont directement les utilisateurs dans la relation many-to-many
    followers.value = response.data.data?.data || []
  } catch (error) {
    console.error('Error loading followers:', error)
    followers.value = []
  } finally {
    loadingFollowers.value = false
  }
}

const loadFollowing = async () => {
  loadingFollowing.value = true
  try {
    const response = await window.axios.get('/users/my-following')
    // Les following sont directement les utilisateurs dans la relation many-to-many
    following.value = response.data.data?.data || []
  } catch (error) {
    console.error('Error loading following:', error)
    following.value = []
  } finally {
    loadingFollowing.value = false
  }
}

const loadActivity = async () => {
  loadingActivity.value = true
  try {
    const response = await window.axios.get('/me/activity', {
      params: { limit: 10 }
    })
    recentActivity.value = response.data.data?.recent_actions || []
  } catch (error) {
    console.error('Error loading activity:', error)
    recentActivity.value = []
  } finally {
    loadingActivity.value = false
  }
}

const editProduct = (product) => {
  router.push(`/products/${product.id}/edit`)
}

const deleteProduct = async (product) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
    try {
      // Appel API pour supprimer le produit
      await window.axios.delete(`/products/${product.id}`)
      await loadProducts()
      // Recharger les stats après suppression
      await loadUserStats()
    } catch (error) {
      console.error('Error deleting product:', error)
      alert('Erreur lors de la suppression du produit')
    }
  }
}

const logout = () => {
  authStore.logout()
  router.push('/login')
}

const getActivityIcon = (type) => {
  const iconMap = {
    'like': HeartIcon,
    'purchase': ShoppingCartIcon,
    'review': StarIcon,
    'view': EyeIcon
  }
  return iconMap[type] || ActivityIcon
}

const unfollowUser = async (userId) => {
  try {
    await window.axios.delete(`/users/${userId}/unfollow`)
    // Recharger la liste des suivis
    await loadFollowing()
    // Recharger les stats
    await loadUserStats()
  } catch (error) {
    console.error('Error unfollowing user:', error)
    alert('Erreur lors du désabonnement')
  }
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Watchers
watch(activeTab, (newTab) => {
  switch (newTab) {
    case 'products':
      if (products.value.length === 0) loadProducts()
      break
    case 'followers':
      if (followers.value.length === 0) loadFollowers()
      break
    case 'following':
      if (following.value.length === 0) loadFollowing()
      break
    case 'activity':
      if (recentActivity.value.length === 0) loadActivity()
      break
  }
})

// Load user stats
const loadUserStats = async () => {
  try {
    const response = await window.axios.get('/me/stats')
    if (response.data.success) {
      // Mettre à jour les stats locales
      const userStats = response.data.data
      dashboardStore.stats.products_count = userStats.products?.total || 0
      dashboardStore.stats.followers_count = userStats.social?.followers_count || 0
      dashboardStore.stats.following_count = userStats.social?.following_count || 0
      dashboardStore.stats.total_sales = userStats.sales?.total_earnings || 0
    }
  } catch (error) {
    console.error('Error loading user stats:', error)
  }
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadProducts(),
    loadUserStats()
  ])
})
</script>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #10b981;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #059669;
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out;
}
</style>



