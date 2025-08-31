<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header avec navigation - Ultra Compact mobile -->
    <div class="bg-white/95 backdrop-blur-md shadow-lg border-b border-slate-200/60 sticky top-0 z-10">
      <div class="max-w-4xl mx-auto px-2 py-1.5 sm:px-4 sm:py-4">
        <div class="flex items-center justify-between">
        <button
          @click="router.back()"
            class="p-1 sm:p-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all duration-200 hover:shadow-md"
        >
            <ArrowLeftIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5" />
        </button>
        
          <h1 class="text-sm sm:text-lg font-semibold text-slate-800">Profil</h1>
          
        <RouterLink
          to="/profile/edit"
            class="p-1 sm:p-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
        >
            <EditIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5" />
        </RouterLink>
        </div>
      </div>
    </div>

    <!-- Contenu principal - Ultra Compact mobile -->
    <div class="max-w-4xl mx-auto px-2 py-2 sm:px-4 sm:py-6">
      
      <!-- Section Profil - Ultra Compact mobile -->
      <div class="bg-white/90 backdrop-blur-md rounded-xl sm:rounded-2xl shadow-xl sm:shadow-2xl border border-slate-200/60 p-3 sm:p-6 mb-3 sm:mb-6">
        <!-- Avatar et infos principales - Ultra Compact mobile -->
        <div class="text-center mb-3 sm:mb-6">
          <div class="relative inline-block mb-2 sm:mb-4">
            <div class="w-14 h-14 sm:w-20 sm:h-20 lg:w-24 lg:h-24 rounded-full bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 flex items-center justify-center text-white text-lg sm:text-2xl lg:text-3xl font-bold border-3 sm:border-4 border-white shadow-xl">
                  {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                </div>
            <div class="absolute -bottom-0.5 sm:-bottom-1 -right-0.5 sm:-right-1 w-3 h-3 sm:w-5 sm:h-5 lg:w-6 lg:h-6 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center shadow-lg">
              <div class="w-1 h-1 sm:w-2 sm:h-2 lg:w-2.5 lg:h-2.5 bg-white rounded-full"></div>
                </div>
              </div>
              
          <h2 class="text-lg sm:text-2xl lg:text-3xl font-bold text-slate-800 mb-1 sm:mb-2">{{ user?.name || 'Utilisateur' }}</h2>
          <p v-if="user?.username" class="text-blue-600 text-sm sm:text-lg mb-1.5 sm:mb-3 font-medium">@{{ user.username }}</p>
          <p v-if="user?.bio" class="text-slate-600 text-xs sm:text-sm max-w-md mx-auto leading-relaxed">{{ user.bio }}</p>
                </div>
        
        <!-- Infos supplémentaires - Ultra Compact mobile -->
        <div v-if="user?.location || user?.website" class="flex flex-col sm:flex-row justify-center items-center space-y-1 sm:space-y-0 sm:space-x-6 text-xs sm:text-sm text-slate-600 mb-3 sm:mb-6">
          <span v-if="user?.location" class="flex items-center">
            <MapPinIcon class="w-2.5 h-2.5 sm:w-4 sm:h-4 mr-1 sm:mr-2 text-blue-600" />
                    {{ user.location }}
                  </span>
          <span v-if="user?.website" class="flex items-center">
            <LinkIcon class="w-2.5 h-2.5 sm:w-4 sm:h-4 mr-1 sm:mr-2 text-blue-600" />
            <a :href="user.website" target="_blank" class="text-blue-600 hover:text-blue-700 transition-colors font-medium">
                      Site web
                    </a>
                  </span>
                </div>
        
        <!-- Actions rapides - Ultra Compact mobile -->
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4 justify-center">
          <RouterLink
            to="/products/create"
            class="inline-flex items-center justify-center px-3 py-2 sm:px-6 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl sm:rounded-2xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-xs sm:text-base"
          >
            <PlusIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 mr-1 sm:mr-2" />
            Vendre un article
          </RouterLink>
          
              <RouterLink
                to="/profile/edit"
            class="inline-flex items-center justify-center px-3 py-2 sm:px-6 sm:py-3 bg-white text-slate-700 border-2 border-slate-300 font-semibold rounded-xl sm:rounded-2xl hover:bg-slate-50 hover:border-slate-400 transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-base"
              >
            <EditIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 mr-1 sm:mr-2" />
            Modifier profil
              </RouterLink>
          
          <!-- Bouton activer tous les produits en attente -->
          <button
            v-if="stats?.pending_payment_products > 0"
            @click="activateAllPendingProducts"
            :disabled="isActivatingAll"
            class="inline-flex items-center justify-center px-3 py-2 sm:px-6 sm:py-3 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-semibold rounded-xl sm:rounded-2xl transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-base disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-0.5"
          >
            <PlayIcon v-if="!isActivatingAll" class="w-3.5 h-3.5 sm:w-5 sm:h-5 mr-1 sm:mr-2" />
            <div v-else class="w-3.5 h-3.5 sm:w-5 sm:h-5 mr-1 sm:mr-2 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
            {{ isActivatingAll ? 'Activation...' : `Activer tout (${stats.pending_payment_products})` }}
          </button>

          <!-- Bouton de déconnexion -->
          <button
            @click="logout"
            class="inline-flex items-center justify-center px-3 py-2 sm:px-6 sm:py-3 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-semibold rounded-xl sm:rounded-2xl transition-all duration-300 shadow-md hover:shadow-lg text-xs sm:text-base transform hover:-translate-y-0.5"
          >
            <LogOutIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 mr-1 sm:mr-2" />
            Se déconnecter
          </button>
      </div>
    </div>

      <!-- Stats principales - Ultra Compact mobile -->
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 sm:gap-4 mb-3 sm:mb-6">
        <div class="bg-white/90 backdrop-blur-md rounded-xl sm:rounded-2xl shadow-lg border border-slate-200/60 p-2 sm:p-4 text-center hover:shadow-xl transition-all duration-300 hover:bg-blue-50/50 group">
          <div class="text-base sm:text-xl lg:text-2xl font-bold text-blue-600 mb-0.5 sm:mb-1 group-hover:text-blue-700">{{ stats?.products_count || 0 }}</div>
          <div class="text-xs sm:text-sm text-slate-600">Produits</div>
            </div>
        <div class="bg-white/90 backdrop-blur-md rounded-xl sm:rounded-2xl shadow-lg border border-slate-200/60 p-2 sm:p-4 text-center hover:shadow-xl transition-all duration-300 hover:bg-indigo-50/50 group">
          <div class="text-base sm:text-xl lg:text-2xl font-bold text-indigo-600 mb-0.5 sm:mb-1 group-hover:text-indigo-700">{{ stats?.followers_count || 0 }}</div>
          <div class="text-xs sm:text-sm text-slate-600">Followers</div>
            </div>
        <div class="bg-white/90 backdrop-blur-md rounded-xl sm:rounded-2xl shadow-lg border border-slate-200/60 p-2 sm:p-4 text-center hover:shadow-xl transition-all duration-300 hover:bg-purple-50/50 group">
          <div class="text-base sm:text-xl lg:text-2xl font-bold text-purple-600 mb-0.5 sm:mb-1 group-hover:text-purple-700">{{ stats?.following_count || 0 }}</div>
          <div class="text-xs sm:text-sm text-slate-600">Following</div>
            </div>
        <div class="bg-white/90 backdrop-blur-md rounded-xl sm:rounded-2xl shadow-lg border border-slate-200/60 p-2 sm:p-4 text-center hover:shadow-xl transition-all duration-300 hover:bg-emerald-50/50 group">
          <div class="text-base sm:text-xl lg:text-2xl font-bold text-emerald-600 mb-0.5 sm:mb-1 group-hover:text-emerald-700">{{ stats?.total_sales || 0 }}</div>
          <div class="text-xs sm:text-sm text-slate-600">Ventes</div>
        </div>
        <div 
          v-if="stats?.pending_payment_products > 0"
          @click="activeTab = 'pending'"
          class="bg-white/90 backdrop-blur-md rounded-xl sm:rounded-2xl shadow-lg border border-amber-200/60 p-2 sm:p-4 text-center hover:shadow-xl transition-all duration-300 cursor-pointer hover:bg-amber-50/50 group"
        >
          <div class="text-base sm:text-xl lg:text-2xl font-bold text-amber-600 mb-0.5 sm:mb-1 group-hover:text-amber-700">{{ stats?.pending_payment_products || 0 }}</div>
          <div class="text-xs sm:text-sm text-amber-600">En attente</div>
        </div>
      </div>

      <!-- Navigation par onglets - Ultra Compact mobile -->
      <div class="bg-white/90 backdrop-blur-md rounded-xl sm:rounded-2xl shadow-xl sm:shadow-2xl border border-slate-200/60 mb-3 sm:mb-6">
        <!-- Tabs navigation - Ultra Compact mobile -->
        <div class="flex border-b border-slate-200/60">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'flex-1 px-1.5 sm:px-4 py-1.5 sm:py-3 text-xs sm:text-sm font-medium transition-all duration-300',
              activeTab === tab.id
                ? 'text-blue-700 border-b-2 border-blue-600 bg-blue-50/50'
                : 'text-slate-600 hover:text-blue-600 hover:bg-blue-50/30'
            ]"
          >
            <div class="flex items-center justify-center space-x-1 sm:space-x-2">
              <component :is="tab.icon" class="w-3 h-3 sm:w-4 sm:h-4" />
              <span class="hidden sm:inline">{{ tab.label }}</span>
        </div>
          </button>
        </div>

        <!-- Tab content - Ultra Compact mobile -->
        <div class="p-3 sm:p-4 lg:p-6">
          <!-- Produits -->
          <div v-if="activeTab === 'products'" class="space-y-2.5 sm:space-y-4">
            <div class="flex items-center justify-between mb-2.5 sm:mb-4">
              <h3 class="text-sm sm:text-lg font-semibold text-slate-800">Mes Produits</h3>
              <RouterLink
                to="/products/create"
                class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs sm:text-sm rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-md hover:shadow-lg"
              >
                <PlusIcon class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" />
                Ajouter
              </RouterLink>
            </div>
            
            <div v-if="loadingProducts" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-4">
              <ProductSkeleton v-for="i in 4" :key="i" />
            </div>
            
            <div v-else-if="products.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-4">
              <ProductCard
                v-for="product in products"
                :key="product.id"
                :product="product"
                :show-actions="true"
                @edit="editProduct"
                @delete="deleteProduct"
                @share="shareProduct"
                @view="viewProduct"
              />
            </div>
            
            <div v-else class="text-center py-5 sm:py-8">
              <PackageIcon class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-slate-400 mb-2.5 sm:mb-4" />
              <h3 class="text-sm sm:text-lg font-medium text-slate-800 mb-1.5 sm:mb-2">Aucun produit</h3>
              <p class="text-slate-600 mb-2.5 sm:mb-4 text-xs sm:text-base">Commencez par créer votre premier produit</p>
              <RouterLink 
                to="/products/create"
                class="inline-flex items-center px-3 py-2 sm:px-6 sm:py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl text-xs sm:text-base"
              >
                <PlusIcon class="w-3.5 h-3.5 sm:w-5 sm:h-5 mr-1 sm:mr-2" />
                Créer un produit
              </RouterLink>
            </div>
          </div>

          <!-- Followers -->
          <div v-if="activeTab === 'followers'" class="space-y-1.5 sm:space-y-4">
            <h3 class="text-sm sm:text-lg font-semibold text-slate-800 mb-2.5 sm:mb-4">Mes Followers</h3>
            
            <div v-if="loadingFollowers" class="space-y-1.5 sm:space-y-4">
              <div v-for="i in 3" :key="i" class="flex items-center space-x-2.5 sm:space-x-3 p-2.5 sm:p-4 bg-slate-50 rounded-lg animate-pulse">
                <div class="w-7 h-7 sm:w-10 sm:h-10 bg-slate-300 rounded-full"></div>
                <div class="flex-1">
                  <div class="h-2.5 sm:h-4 bg-slate-300 rounded w-3/4 mb-1 sm:mb-2"></div>
                  <div class="h-2 sm:h-3 bg-slate-300 rounded w-1/2"></div>
                </div>
              </div>
            </div>
            
            <div v-else-if="followers.length > 0" class="space-y-1.5 sm:space-y-3">
              <div
                v-for="follower in followers"
                :key="follower.id"
                class="flex items-center justify-between p-2.5 sm:p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-all duration-200 hover:shadow-md"
              >
                  <div class="flex items-center space-x-2 sm:space-x-3">
                  <div class="w-7 h-7 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-xs sm:text-base">
                    {{ follower.name?.charAt(0)?.toUpperCase() || 'U' }}
                    </div>
                  <div>
                    <div class="font-medium text-slate-800 text-xs sm:text-base">{{ follower.name }}</div>
                    <div class="text-xs sm:text-sm text-slate-600">@{{ follower.username }}</div>
                  </div>
                </div>
                <RouterLink
                  :to="`/users/${follower.id}`"
                  class="px-2.5 py-1 sm:px-4 sm:py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-xs sm:text-sm rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 text-center shadow-md hover:shadow-lg"
                >
                  Voir profil
                </RouterLink>
              </div>
            </div>
            
            <div v-else class="text-center py-5 sm:py-8">
              <UsersIcon class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-slate-400 mb-2.5 sm:mb-4" />
              <h3 class="text-sm sm:text-lg font-medium text-slate-800 mb-1.5 sm:mb-2">Aucun follower</h3>
              <p class="text-slate-600 text-xs sm:text-base">Partagez vos produits pour attirer des followers</p>
            </div>
          </div>

          <!-- Following -->
          <div v-if="activeTab === 'following'" class="space-y-1.5 sm:space-y-4">
            <h3 class="text-sm sm:text-lg font-semibold text-slate-800 mb-2.5 sm:mb-4">Mes Abonnements</h3>
            
            <div v-if="loadingFollowing" class="space-y-1.5 sm:space-y-4">
              <div v-for="i in 3" :key="i" class="flex items-center space-x-2.5 sm:space-x-3 p-2.5 sm:p-4 bg-slate-50 rounded-lg animate-pulse">
                <div class="w-7 h-7 sm:w-10 sm:h-10 bg-slate-300 rounded-full"></div>
                <div class="flex-1">
                  <div class="h-2.5 sm:h-4 bg-slate-300 rounded w-3/4 mb-1 sm:mb-2"></div>
                  <div class="h-2 sm:h-3 bg-slate-300 rounded w-1/2"></div>
                </div>
              </div>
            </div>
            
            <div v-else-if="following.length > 0" class="space-y-1.5 sm:space-y-3">
              <div
                v-for="followed in following"
                :key="followed.id"
                class="flex items-center justify-between p-2.5 sm:p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-all duration-200 hover:shadow-md"
              >
                  <div class="flex items-center space-x-2 sm:space-x-3">
                  <div class="w-7 h-7 sm:w-10 sm:h-10 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white font-semibold text-xs sm:text-base">
                    {{ followed.name?.charAt(0)?.toUpperCase() || 'U' }}
                    </div>
                  <div>
                    <div class="font-medium text-slate-800 text-xs sm:text-base">{{ followed.name }}</div>
                    <div class="text-xs sm:text-sm text-slate-600">@{{ followed.username }}</div>
                  </div>
                </div>
                <button 
                  @click="unfollowUser(followed.id)"
                  class="px-2.5 py-1 sm:px-4 sm:py-2 bg-slate-600 text-white text-xs sm:text-sm rounded-lg hover:bg-slate-700 transition-all duration-200 shadow-md hover:shadow-lg"
                >
                  Se désabonner
                </button>
              </div>
            </div>
            
            <div v-else class="text-center py-5 sm:py-8">
              <UsersIcon class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-slate-400 mb-2.5 sm:mb-4" />
              <h3 class="text-sm sm:text-lg font-medium text-slate-800 mb-1.5 sm:mb-2">Aucun abonnement</h3>
              <p class="text-slate-600 text-xs sm:text-base">Découvrez et suivez d'autres vendeurs</p>
            </div>
          </div>

          <!-- Activité -->
          <div v-if="activeTab === 'activity'" class="space-y-1.5 sm:space-y-4">
            <h3 class="text-sm sm:text-lg font-semibold text-slate-800 mb-2.5 sm:mb-4">Mon Activité</h3>
            
            <div v-if="loadingActivity" class="space-y-1.5 sm:space-y-4">
              <div v-for="i in 3" :key="i" class="p-2.5 sm:p-4 bg-slate-50 rounded-lg animate-pulse">
                <div class="h-2.5 sm:h-4 bg-slate-300 rounded w-3/4 mb-1 sm:mb-2"></div>
                <div class="h-2 sm:h-3 bg-slate-300 rounded w-1/2"></div>
              </div>
            </div>
            
            <div v-else-if="recentActivity.length > 0" class="space-y-1.5 sm:space-y-3">
              <div
                v-for="activity in recentActivity"
                :key="activity.id"
                class="p-2.5 sm:p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition-all duration-200 hover:shadow-md"
              >
                <div class="flex items-start space-x-2 sm:space-x-3">
                  <div class="w-5 h-5 sm:w-8 sm:h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                    <component :is="getActivityIcon(activity.type)" class="w-2.5 h-2.5 sm:w-4 sm:h-4 text-blue-600" />
                  </div>
                  <div class="flex-1">
                    <div class="text-xs sm:text-sm text-slate-800">{{ activity.description }}</div>
                    <div class="text-xs text-slate-500 mt-0.5 sm:mt-1">{{ formatDate(activity.created_at) }}</div>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-5 sm:py-8">
              <ActivityIcon class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-slate-400 mb-2.5 sm:mb-4" />
              <h3 class="text-sm sm:text-lg font-medium text-slate-800 mb-1.5 sm:mb-2">Aucune activité</h3>
              <p class="text-slate-600 text-xs sm:text-base">Vos actions apparaîtront ici</p>
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
import api from '@/services/api'
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
  LogOutIcon,
  PlayIcon
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
const isActivatingAll = ref(false)
const products = ref([])
const followers = ref([])
const following = ref([])
const recentActivity = ref([])

// Computed
const user = computed(() => authStore.user)
const stats = computed(() => {
  return dashboardStore.stats.value
})

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
    const response = await api.get('/products/my-products', {
      params: { per_page: 20 }
    })
    
    products.value = response.data.data || []
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
    const response = await api.get('/users/my-followers')
    
    if (response.data.success && response.data.data) {
      followers.value = response.data.data.data || response.data.data || []
    } else {
      followers.value = []
    }
  } catch (error) {
    followers.value = []
  } finally {
    loadingFollowers.value = false
  }
}

const loadFollowing = async () => {
  loadingFollowing.value = true
  try {
    const response = await api.get('/users/my-following')
    
    if (response.data.success && response.data.data) {
      following.value = response.data.data.data || response.data.data || []
    } else {
      following.value = []
    }
  } catch (error) {
    following.value = []
  } finally {
    loadingFollowing.value = false
  }
}

const loadActivity = async () => {
  loadingActivity.value = true
  try {
    const response = await api.get('/me/activity', {
      params: { limit: 10 }
    })
    
    if (response.data.success && response.data.data) {
      recentActivity.value = response.data.data.recent_actions || 
                           response.data.data.activities || 
                           (Array.isArray(response.data.data) ? response.data.data : [])
    } else {
      recentActivity.value = []
    }
  } catch (error) {
    recentActivity.value = []
  } finally {
    loadingActivity.value = false
  }
}

const editProduct = (product) => {
  router.push(`/products/${product.id}/edit`)
}

const viewProduct = (product) => {
  router.push(`/products/${product.id}`)
}

const deleteProduct = async (product) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
    try {
      await api.delete(`/products/${product.id}`)
      await loadProducts()
      await loadUserStats()
    } catch (error) {
      alert('Erreur lors de la suppression du produit')
    }
  }
}

const shareProduct = async (product) => {
  try {
    const productUrl = `${window.location.origin}/products/${product.id}`
    
    if (navigator.share) {
      await navigator.share({
        title: product.title,
        text: `Découvrez ce produit : ${product.title} - ${product.formatted_price || product.price + ' Fcfa'}`,
        url: productUrl
      })
    } else {
      await navigator.clipboard.writeText(productUrl)
      
      const notification = document.createElement('div')
      notification.textContent = 'Lien copié dans le presse-papiers !'
      notification.className = 'fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg z-50'
      document.body.appendChild(notification)
      
      setTimeout(() => {
        document.body.removeChild(notification)
      }, 3000)
    }
  } catch (error) {
    try {
      const productUrl = `${window.location.origin}/products/${product.id}`
      await navigator.clipboard.writeText(productUrl)
      alert('Lien copié dans le presse-papiers !')
    } catch (clipboardError) {
      alert('Impossible de partager le produit')
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
    await api.delete(`/users/${userId}/unfollow`)
    await loadFollowing()
    await loadUserStats()
  } catch (error) {
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

// Payer et activer tous les produits en attente via Lygos
const activateAllPendingProducts = async () => {
  if (isActivatingAll.value) return
  
  isActivatingAll.value = true
  
  try {
    const response = await api.post('/products/create-bulk-payment')
    
    if (response.data.success) {
      const paymentData = response.data.data
      
      if (paymentData.payment_required && paymentData.notchpay_payment_link) {
        localStorage.setItem('bulk_payment_info', JSON.stringify({
            reference: paymentData.notchpay_reference,
            total_amount: paymentData.total_amount,
            product_count: paymentData.product_count
        }))
        
        window.location.href = paymentData.notchpay_payment_link
      } else {
        const activateResponse = await api.post('/products/activate-all-pending')
        
        if (activateResponse.data.success) {
          const result = activateResponse.data
          alert(`🎉 ${result.summary.activated} produit(s) activé(s) avec succès !`)
          
          await Promise.all([
            loadProducts(),
            loadUserStats()
          ])
        }
      }
    } else {
      alert('❌ Erreur lors de la création du paiement')
    }
    
  } catch (error) {
    if (error.response?.status === 404) {
      alert('ℹ️ Aucun produit en attente de paiement trouvé')
    } else if (error.response?.data?.message) {
      alert(`❌ ${error.response.data.message}`)
    } else {
      alert('❌ Erreur lors de la création du paiement')
    }
  } finally {
    isActivatingAll.value = false
  }
}

// Watchers
watch(activeTab, (newTab) => {
  switch (newTab) {
    case 'products':
      if (products.value.length === 0) {
        loadProducts()
      }
      break
    case 'followers':
      loadFollowers()
      break
    case 'following':
      loadFollowing()
      break
    case 'activity':
      loadActivity()
      break
  }
})

// Load user stats
const loadUserStats = async () => {
  try {
    const response = await api.get('/me/stats')
    
    if (response.data.success) {
      const userStats = response.data.data
      
      if (!dashboardStore.stats.value) {
        dashboardStore.stats.value = {
          products_count: 0,
          total_sales: 0,
          followers_count: 0,
          following_count: 0,
          monthly_views: 0,
          products_trend: 0,
          sales_trend: 0,
          followers_trend: 0,
          views_trend: 0,
          pending_payment_products: 0
        }
      }
      
      dashboardStore.stats.value.products_count = userStats.products?.total || 0
      dashboardStore.stats.value.followers_count = userStats.social?.followers_count || 0
      dashboardStore.stats.value.following_count = userStats.social?.following_count || 0
      dashboardStore.stats.value.total_sales = userStats.sales?.total_earnings || 0
    }
    
    const statsResponse = await api.get('/products/stats')
    if (statsResponse.data.success) {
      const productStats = statsResponse.data.data
      dashboardStore.stats.value.pending_payment_products = productStats.pending_payment_products || 0
    }
    
  } catch (error) {
    // Gestion silencieuse des erreurs de stats
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
/* Custom scrollbar avec design moderne */
.overflow-y-auto::-webkit-scrollbar {
  width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: linear-gradient(to bottom, #f8fafc, #e2e8f0);
  border-radius: 4px;
  border: 1px solid #e2e8f0;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #1d4ed8);
  border-radius: 4px;
  border: 1px solid #1d4ed8;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #1e40af);
}

/* Animations fluides et élégantes */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-slide-in-right {
  animation: slideInRight 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.animate-pulse-slow {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

/* Effets de hover avancés */
.group:hover .group-hover\:text-blue-700 {
  color: #1d4ed8;
  transition: color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.group:hover .group-hover\:text-indigo-700 {
  color: #4338ca;
  transition: color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.group:hover .group-hover\:text-purple-700 {
  color: #7c3aed;
  transition: color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.group:hover .group-hover\:text-emerald-700 {
  color: #047857;
  transition: color 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Effets de glassmorphism améliorés */
.backdrop-blur-md {
  backdrop-filter: blur(16px) saturate(180%);
  -webkit-backdrop-filter: blur(16px) saturate(180%);
}

/* Ombres dynamiques */
.shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Transitions fluides pour tous les éléments */
* {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Effet de focus amélioré */
button:focus, a:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Gradient text pour certains éléments */
.gradient-text {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>



