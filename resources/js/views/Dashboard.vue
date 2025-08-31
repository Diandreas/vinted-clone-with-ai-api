<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50/20 to-indigo-50/30">
    <!-- Header avec navigation -->
    <div class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200/50 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-6 space-y-4 sm:space-y-0">
          <div class="text-center sm:text-left">
            <h1 class="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-gray-900 via-blue-700 to-indigo-800 bg-clip-text text-transparent">
              Tableau de bord
            </h1>
            <p class="text-base sm:text-lg text-gray-600 mt-2">Bienvenue, {{ user?.name || 'Utilisateur' }} 👋</p>
          </div>
          
          <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 justify-center sm:justify-end">
            <RouterLink
              to="/products/create"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <PlusIcon class="w-5 h-5 mr-2" />
              Vendre un article
            </RouterLink>
            
            <RouterLink
              to="/profile/edit"
              class="inline-flex items-center px-6 py-3 bg-white text-gray-700 border-2 border-gray-300 font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 shadow-md hover:shadow-lg"
            >
              <EditIcon class="w-5 h-5 mr-2" />
              Modifier profil
            </RouterLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      <!-- Section Profil et Stats principales -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Carte de profil -->
        <div class="lg:col-span-1">
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 hover:shadow-2xl transition-all duration-300">
            <!-- Avatar et infos -->
            <div class="text-center mb-6">
              <div class="relative inline-block mb-4">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 flex items-center justify-center text-white text-3xl font-bold border-4 border-white shadow-lg">
                  {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                </div>
                <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                  <div class="w-2.5 h-2.5 bg-white rounded-full"></div>
                </div>
              </div>
              
              <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ user?.name || 'Utilisateur' }}</h2>
              <p v-if="user?.username" class="text-gray-500 text-lg mb-3">@{{ user.username }}</p>
              <p v-if="user?.bio" class="text-gray-600 text-sm">{{ user.bio }}</p>
              
              <div v-if="user?.is_verified" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mt-3">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Vérifié
              </div>
            </div>
            
            <!-- Stats rapides -->
            <div class="grid grid-cols-3 gap-4 mb-6">
              <div class="text-center p-3 bg-blue-50 rounded-xl hover:bg-blue-100 transition-colors cursor-pointer" @click="goToProfile('products')">
                <div class="text-2xl font-bold text-blue-600">{{ stats?.products_count || 0 }}</div>
                <div class="text-sm text-blue-700">Produits</div>
              </div>
              <div class="text-center p-3 bg-purple-50 rounded-xl hover:bg-purple-100 transition-colors cursor-pointer" @click="goToProfile('followers')">
                <div class="text-2xl font-bold text-purple-600">{{ stats?.followers_count || 0 }}</div>
                <div class="text-sm text-purple-700">Followers</div>
              </div>
              <div class="text-center p-3 bg-green-50 rounded-xl hover:bg-green-100 transition-colors cursor-pointer" @click="goToProfile('following')">
                <div class="text-2xl font-bold text-green-600">{{ stats?.following_count || 0 }}</div>
                <div class="text-sm text-green-700">Following</div>
              </div>
            </div>
            
            <!-- Actions rapides -->
            <div class="space-y-3">
              <RouterLink
                :to="`/profile/${user?.id}`"
                class="w-full bg-gradient-to-r from-gray-900 to-gray-700 text-white px-4 py-3 rounded-xl hover:from-gray-800 hover:to-gray-600 transition-all duration-200 flex items-center justify-center space-x-2 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
              >
                <UserIcon class="w-5 h-5" />
                <span>Voir mon profil</span>
              </RouterLink>
            </div>
          </div>
        </div>
        
        <!-- Stats principales -->
        <div class="lg:col-span-2">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <StatsCard
              title="Mes Produits"
              :value="stats?.products_count || 0"
              icon="package"
              color="blue"
              :trend="stats?.products_trend || 0"
              class="hover:scale-105 transition-transform duration-200"
            />
            <StatsCard
              title="Ventes Totales"
              :value="formatCurrency(stats?.total_sales || 0)"
              icon="dollar-sign"
              color="green"
              :trend="stats?.sales_trend || 0"
              class="hover:scale-105 transition-transform duration-200"
            />
            <StatsCard
              title="Followers"
              :value="stats?.followers_count || 0"
              icon="users"
              color="purple"
              :trend="stats?.followers_trend || 0"
              class="hover:scale-105 transition-transform duration-200"
            />
            <StatsCard
              title="Vues ce mois"
              :value="stats?.monthly_views || 0"
              icon="eye"
              color="orange"
              :trend="stats?.views_trend || 0"
              class="hover:scale-105 transition-transform duration-200"
            />
          </div>
        </div>
      </div>

      <!-- Section Graphiques et Activité -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-8">
        
        <!-- Graphique des ventes -->
        <div class="xl:col-span-2">
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-bold text-gray-900">Évolution des ventes</h2>
              <select 
                v-model="chartPeriod" 
                @change="onChartPeriodChange"
                class="px-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="7">7 jours</option>
                <option value="30">30 jours</option>
                <option value="90">90 jours</option>
                <option value="365">1 an</option>
              </select>
            </div>
            
            <div v-if="loadingStats" class="flex items-center justify-center h-64">
              <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            </div>
            <SalesChart v-else :data="salesChartData" />
          </div>
        </div>
        
        <!-- Activité récente -->
        <div class="xl:col-span-1">
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 hover:shadow-2xl transition-all duration-300">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Activité Récente</h2>
            
            <div v-if="loadingActivity" class="space-y-4">
              <ActivitySkeleton v-for="i in 3" :key="i" />
            </div>
            
            <div v-else-if="recentActivity.length > 0" class="space-y-4 max-h-96 overflow-y-auto">
              <ActivityItem
                v-for="activity in recentActivity"
                :key="activity.id"
                :activity="activity"
              />
            </div>
            
            <div v-else class="text-center py-8">
              <ActivityIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune activité</h3>
              <p class="mt-1 text-sm text-gray-500">Vos actions apparaîtront ici</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Section Produits récents et Actions rapides -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Produits récents -->
        <div class="lg:col-span-2">
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-bold text-gray-900">Mes Produits Récents</h2>
              <RouterLink 
                to="/my-products" 
                class="text-blue-600 hover:text-blue-700 font-medium text-sm hover:underline"
              >
                Voir tous →
              </RouterLink>
            </div>
            
            <div v-if="loadingProducts" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <ProductSkeleton v-for="i in 4" :key="i" />
            </div>
            
            <div v-else-if="recentProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <ProductCard
                v-for="product in recentProducts"
                :key="product.id"
                :product="product"
                :show-actions="true"
                @edit="$emit('edit', $event)"
                @delete="$emit('delete', $event)"
                @boost="$emit('boost', $event)"
                @share="$emit('share', $event)"
              />
            </div>
            
            <div v-else class="text-center py-8">
              <PackageIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun produit</h3>
              <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier produit</p>
              <RouterLink 
                to="/products/create"
                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                Créer un produit
              </RouterLink>
            </div>
          </div>
        </div>
        
        <!-- Actions rapides et notifications -->
        <div class="lg:col-span-1 space-y-6">
          
          <!-- Actions rapides -->
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 hover:shadow-2xl transition-all duration-300">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Actions Rapides</h2>
            <div class="space-y-3">
              <QuickActionButton
                title="Créer un produit"
                description="Vendre un article"
                icon="plus"
                color="blue"
                @click="router.push('/products/create')"
              />
              <QuickActionButton
                title="Mes commandes"
                description="Gérer les ventes"
                icon="shopping-cart"
                color="green"
                @click="router.push('/orders')"
              />

              <QuickActionButton
                title="Analytics"
                description="Voir les statistiques"
                icon="bar-chart"
                color="orange"
                @click="router.push('/analytics')"
              />
            </div>
          </div>
          
          <!-- Notifications et alertes -->
          <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 hover:shadow-2xl transition-all duration-300">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Alertes</h2>
            <div class="space-y-4">
              <div v-if="pendingOrders > 0" class="flex items-center p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                <ClockIcon class="w-5 h-5 text-yellow-600 mr-3" />
                <div class="flex-1">
                  <p class="text-sm font-medium text-yellow-900">{{ pendingOrders }} commande(s) en attente</p>
                  <p class="text-xs text-yellow-700">À traiter</p>
                </div>
              </div>
              
              <div v-if="unreadNotifications > 0" class="flex items-center p-3 bg-red-50 rounded-lg border border-red-200">
                <BellIcon class="w-5 h-5 text-red-600 mr-3" />
                <div class="flex-1">
                  <p class="text-sm font-medium text-red-900">{{ unreadNotifications }} notification(s)</p>
                  <p class="text-xs text-red-700">À consulter</p>
                </div>
              </div>
              
              <div v-if="pendingOrders === 0 && unreadNotifications === 0" class="text-center py-4">
                <CheckCircleIcon class="mx-auto h-8 w-8 text-green-500 mb-2" />
                <p class="text-sm text-gray-600">Tout est à jour !</p>
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
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDashboardStore } from '@/stores/dashboard'
import StatsCard from '@/components/dashboard/StatsCard.vue'
import SalesChart from '@/components/dashboard/SalesChart.vue'
import ProductCard from '@/components/products/ProductCard.vue'
import ProductSkeleton from '@/components/skeletons/ProductSkeleton.vue'
import QuickActionButton from '@/components/dashboard/QuickActionButton.vue'
import ActivityItem from '@/components/dashboard/ActivityItem.vue'
import ActivitySkeleton from '@/components/skeletons/ActivitySkeleton.vue'
import {
  PlusIcon,
  PackageIcon, 
  ClockIcon, 
  TrendingUpIcon,
  ActivityIcon,
  UserIcon,
  EditIcon,
  BellIcon,
  CheckCircleIcon
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const dashboardStore = useDashboardStore()

// Reactive data
const chartPeriod = ref('30')
const loadingStats = ref(true)
const loadingProducts = ref(true)
const loadingActivity = ref(true)

// Computed
const user = computed(() => authStore.user)
const stats = computed(() => dashboardStore.stats)
const recentProducts = computed(() => dashboardStore.recentProducts)
const recentActivity = computed(() => dashboardStore.recentActivity)
const salesChartData = computed(() => dashboardStore.salesChartData)
const pendingOrders = computed(() => dashboardStore.pendingOrders)
const unreadNotifications = computed(() => dashboardStore.unreadNotifications)

// Methods
const formatCurrency = (amount) => {
  if (!amount) return '0 Fcfa'
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF',
    minimumFractionDigits: 0
  }).format(amount)
}

const loadStats = async () => {
  loadingStats.value = true
  try {
    await dashboardStore.fetchStats()
  } catch (error) {
    console.error('Error loading stats:', error)
  } finally {
    loadingStats.value = false
  }
}

const loadRecentProducts = async () => {
  loadingProducts.value = true
  try {
    await dashboardStore.fetchRecentProducts()
  } catch (error) {
    console.error('Error loading recent products:', error)
  } finally {
    loadingProducts.value = false
  }
}

const loadRecentActivity = async () => {
  loadingActivity.value = true
  try {
    await dashboardStore.fetchRecentActivity()
  } catch (error) {
    console.error('Error loading recent activity:', error)
  } finally {
    loadingActivity.value = false
  }
}

const onChartPeriodChange = async () => {
  if (chartPeriod.value) {
    await dashboardStore.fetchSalesChart(chartPeriod.value)
  }
}

const goToProfile = (tab = null) => {
  if (tab) {
    router.push(`/profile/${user.value?.id}?tab=${tab}`)
  } else {
    router.push(`/profile/${user.value?.id}`)
  }
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadStats(),
    loadRecentProducts(),
    loadRecentActivity(),
    dashboardStore.fetchSalesChart(chartPeriod.value)
  ])
})
</script>

<style scoped>
/* Scrollbar personnalisée */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Animations personnalisées */
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
