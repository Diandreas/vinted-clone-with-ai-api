<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-6 sm:py-8 space-y-6 sm:space-y-0">
          <div class="text-center sm:text-left">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
              Bonjour, {{ user?.name || 'Utilisateur' }} 👋
            </h1>
            <p class="text-base sm:text-lg text-gray-600">Voici un aperçu de votre activité</p>
          </div>
          <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 justify-center sm:justify-end">
            <RouterLink
              to="/products/create"
              class="bg-indigo-600 text-white px-6 sm:px-8 py-3 sm:py-4 rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center space-x-3 text-base sm:text-lg font-medium shadow-lg"
            >
              <PlusIcon class="w-5 h-5 sm:w-6 sm:h-6" />
              <span class="hidden sm:inline">Vendre un article</span>
              <span class="sm:hidden">Vendre</span>
            </RouterLink>
            <RouterLink
              to="/lives/create"
              class="bg-red-600 text-white px-6 sm:px-8 py-3 sm:py-4 rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center space-x-3 text-base sm:text-lg font-medium shadow-lg"
            >
              <VideoIcon class="w-5 h-5 sm:w-6 sm:h-6" />
              <span class="hidden sm:inline">Créer un Live</span>
              <span class="sm:hidden">Live</span>
            </RouterLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Profile Section - Style TikTok -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
        <!-- Profile Header - Centered on mobile -->
        <div class="text-center sm:text-left mb-6 sm:mb-8">
          <!-- Profile Avatar - Centered on mobile -->
          <div class="relative mx-auto sm:mx-0 mb-4 sm:mb-6">
            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-3xl sm:text-4xl font-bold border-4 border-white shadow-lg mx-auto sm:mx-0">
              {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
            </div>
            <div class="absolute -bottom-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
              <div class="w-2.5 h-2.5 sm:w-3 sm:h-3 bg-white rounded-full"></div>
            </div>
          </div>

          <!-- Profile Info - Centered on mobile -->
          <div class="mb-6 sm:mb-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ user?.name || 'Utilisateur' }}</h2>
            <span v-if="user?.username" class="text-gray-500 text-base sm:text-lg block mb-3">@{{ user.username }}</span>
            <span v-if="user?.is_verified" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mb-3">
              <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Vérifié
            </span>
            <p v-if="user?.bio" class="text-gray-600 text-sm sm:text-base max-w-2xl mx-auto sm:mx-0">{{ user.bio }}</p>
          </div>
        </div>

        <!-- Profile Stats - Centered on mobile -->
        <div class="grid grid-cols-3 gap-4 sm:gap-6 max-w-xs sm:max-w-md mx-auto sm:mx-0 mb-6 sm:mb-8">
          <div class="text-center cursor-pointer hover:bg-gray-50 rounded-lg p-3 sm:p-4 transition-colors" @click="goToProfile('products')">
            <div class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">{{ stats.products_count || 0 }}</div>
            <div class="text-sm text-gray-500">Produits</div>
          </div>
          <div class="text-center cursor-pointer hover:bg-gray-50 rounded-lg p-3 sm:p-4 transition-colors" @click="goToProfile('followers')">
            <div class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">{{ stats.followers_count || 0 }}</div>
            <div class="text-sm text-gray-500">Followers</div>
          </div>
          <div class="text-center cursor-pointer hover:bg-gray-50 rounded-lg p-3 sm:p-4 transition-colors" @click="goToProfile('following')">
            <div class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">{{ stats.following_count || 0 }}</div>
            <div class="text-sm text-gray-500">Following</div>
          </div>
        </div>

        <!-- Profile Actions - Centered on mobile -->
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 justify-center sm:justify-start">
          <RouterLink
            :to="`/profile/${user?.id}`"
            class="bg-gray-900 text-white px-6 sm:px-8 py-3 sm:py-4 rounded-full hover:bg-gray-800 transition-colors flex items-center justify-center space-x-3 text-base sm:text-lg font-medium shadow-lg"
          >
            <UserIcon class="w-5 h-5 sm:w-6 sm:h-6" />
            <span>Voir mon profil</span>
          </RouterLink>
          <RouterLink
            to="/profile/edit"
            class="bg-white text-gray-700 border-2 border-gray-300 px-6 sm:px-8 py-3 sm:py-4 rounded-full hover:bg-gray-50 hover:border-gray-400 transition-colors flex items-center justify-center space-x-3 text-base sm:text-lg font-medium shadow-lg"
          >
            <EditIcon class="w-5 h-5 sm:w-6 sm:h-6" />
            <span>Modifier</span>
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8 sm:mb-12">
        <StatsCard
          title="Mes Produits"
          :value="stats.products_count"
          icon="package"
          color="blue"
          :trend="stats.products_trend"
        />
        <StatsCard
          title="Ventes Totales"
          :value="formatCurrency(stats.total_sales)"
          icon="dollar-sign"
          color="green"
          :trend="stats.sales_trend"
        />
        <StatsCard
          title="Followers"
          :value="stats.followers_count"
          icon="users"
          color="purple"
          :trend="stats.followers_trend"
        />
        <StatsCard
          title="Vues ce mois"
          :value="stats.monthly_views"
          icon="eye"
          color="orange"
          :trend="stats.views_trend"
        />
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6 sm:space-y-8">
          <!-- Sales Chart -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
              <h2 class="text-xl sm:text-2xl font-semibold text-gray-900">Ventes des 30 derniers jours</h2>
              <select v-model="chartPeriod" class="border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="7">7 jours</option>
                <option value="30">30 jours</option>
                <option value="90">90 jours</option>
              </select>
            </div>
            <SalesChart :data="salesChartData" :loading="loadingStats" />
          </div>

          <!-- Recent Products -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 space-y-4 sm:space-y-0">
              <h2 class="text-xl sm:text-2xl font-semibold text-gray-900">Mes Produits Récents</h2>
              <RouterLink
                to="/products?filter=my-products"
                class="text-indigo-600 hover:text-indigo-700 text-sm font-medium hover:underline"
              >
                Voir tout
              </RouterLink>
            </div>
            <div v-if="loadingProducts" class="space-y-4">
              <ProductSkeleton v-for="i in 3" :key="i" />
            </div>
            <div v-else-if="recentProducts.length > 0" class="space-y-4">
              <ProductCard
                v-for="product in recentProducts"
                :key="product.id"
                :product="product"
                class="border border-gray-200 rounded-lg p-4"
              />
            </div>
            <div v-else class="text-center py-8">
              <PackageIcon class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun produit</h3>
              <p class="mt-1 text-sm text-gray-500">Commencez par créer votre premier produit</p>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6 sm:space-y-8">
          <!-- Quick Actions -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-900 mb-6">Actions Rapides</h2>
            <div class="space-y-4">
              <QuickActionButton
                to="/products/create"
                icon="plus"
                title="Ajouter un produit"
                description="Vendez vos articles"
                color="blue"
              />
              <QuickActionButton
                to="/lives/create"
                icon="video"
                title="Créer un live"
                description="Vendez en direct"
                color="red"
              />
              <QuickActionButton
                :to="`/profile/${user?.id}?tab=followers`"
                icon="users"
                title="Gérer mes followers"
                description="Voir mes abonnés"
                color="purple"
              />
              <QuickActionButton
                :to="`/profile/${user?.id}`"
                icon="user"
                title="Mon profil complet"
                description="Voir tous mes détails"
                color="gray"
              />
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-900 mb-6">Activité Récente</h2>
            <div v-if="loadingActivity" class="space-y-4">
              <ActivitySkeleton v-for="i in 3" :key="i" />
            </div>
            <div v-else-if="recentActivity.length > 0" class="space-y-4">
              <ActivityItem
                v-for="activity in recentActivity"
                :key="activity.id"
                :activity="activity"
              />
            </div>
            <div v-else class="text-center py-8">
              <ActivityIcon class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune activité</h3>
              <p class="mt-1 text-sm text-gray-500">Vos actions apparaîtront ici</p>
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
  VideoIcon,
  PackageIcon, 
  ClockIcon, 
  TrendingUpIcon,
  ActivityIcon,
  UserIcon,
  EditIcon
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

// Methods
const formatCurrency = (amount) => {
  if (!amount) return '0 FCFA'
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

const goToProfile = (tab = null) => {
  if (tab) {
    router.push(`/profile/${user.value?.id}?tab=${tab}`)
  } else {
    router.push(`/profile/${user.value?.id}`)
  }
}

// Watchers
watch(chartPeriod, async (newPeriod) => {
  if (newPeriod) {
    await dashboardStore.fetchSalesChartData(newPeriod)
  }
})

// Lifecycle
onMounted(async () => {
  await Promise.all([
    loadStats(),
    loadRecentProducts(),
    loadRecentActivity()
  ])
})
</script>
