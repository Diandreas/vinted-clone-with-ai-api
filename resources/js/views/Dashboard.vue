<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              Bonjour, {{ user?.name || 'Utilisateur' }} 👋
            </h1>
            <p class="text-gray-600 mt-1">Voici un aperçu de votre activité</p>
          </div>
          <div class="flex space-x-3">
            <RouterLink
              to="/products/create"
              class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors flex items-center space-x-2"
            >
              <PlusIcon class="w-5 h-5" />
              <span>Vendre un article</span>
            </RouterLink>
            <RouterLink
              to="/lives/create"
              class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-colors flex items-center space-x-2"
            >
              <VideoIcon class="w-5 h-5" />
              <span>Créer un Live</span>
            </RouterLink>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Sales Chart -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-semibold text-gray-900">Ventes des 30 derniers jours</h2>
              <select v-model="chartPeriod" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="7">7 jours</option>
                <option value="30">30 jours</option>
                <option value="90">90 jours</option>
              </select>
            </div>
            <SalesChart :data="salesChartData" :loading="loadingStats" />
          </div>

          <!-- Recent Products -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-semibold text-gray-900">Mes Produits Récents</h2>
              <RouterLink
                to="/products?filter=my-products"
                class="text-indigo-600 hover:text-indigo-700 text-sm font-medium"
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
                :show-actions="true"
                @edit="editProduct"
                @delete="deleteProduct"
              />
            </div>
            <div v-else class="text-center py-12">
              <PackageIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
              <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun produit</h3>
              <p class="text-gray-600 mb-4">Commencez par créer votre premier produit</p>
              <RouterLink
                to="/products/create"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition-colors"
              >
                Créer un produit
              </RouterLink>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-8">
          <!-- Quick Actions -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Actions Rapides</h2>
            <div class="space-y-3">
              <QuickActionButton
                to="/messages"
                icon="message-circle"
                title="Messages"
                :badge="unreadMessages"
                color="blue"
              />
              <QuickActionButton
                to="/orders"
                icon="shopping-bag"
                title="Commandes"
                :badge="pendingOrders"
                color="green"
              />
              <QuickActionButton
                to="/notifications"
                icon="bell"
                title="Notifications"
                :badge="unreadNotifications"
                color="yellow"
              />
              <QuickActionButton
                to="/stories"
                icon="camera"
                title="Stories"
                color="purple"
              />
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Activité Récente</h2>
            <div v-if="loadingActivity" class="space-y-4">
              <ActivitySkeleton v-for="i in 5" :key="i" />
            </div>
            <div v-else-if="recentActivity.length > 0" class="space-y-4">
              <ActivityItem
                v-for="activity in recentActivity"
                :key="activity.id"
                :activity="activity"
              />
            </div>
            <div v-else class="text-center py-8">
              <ClockIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
              <p class="text-gray-600">Aucune activité récente</p>
            </div>
          </div>

          <!-- Trending Products -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Produits Tendance</h2>
            <div v-if="loadingTrending" class="space-y-4">
              <TrendingProductSkeleton v-for="i in 3" :key="i" />
            </div>
            <div v-else-if="trendingProducts.length > 0" class="space-y-4">
              <TrendingProductCard
                v-for="product in trendingProducts"
                :key="product.id"
                :product="product"
              />
            </div>
            <div v-else class="text-center py-8">
              <TrendingUpIcon class="w-12 h-12 text-gray-300 mx-auto mb-3" />
              <p class="text-gray-600">Aucun produit tendance</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useDashboardStore } from '@/stores/dashboard'
import { 
  PlusIcon, 
  VideoIcon, 
  PackageIcon, 
  ClockIcon, 
  TrendingUpIcon 
} from 'lucide-vue-next'

// Components
import StatsCard from '@/components/dashboard/StatsCard.vue'
import SalesChart from '@/components/dashboard/SalesChart.vue'
import ProductCard from '@/components/products/ProductCard.vue'
import ProductSkeleton from '@/components/skeletons/ProductSkeleton.vue'
import ActivityItem from '@/components/dashboard/ActivityItem.vue'
import ActivitySkeleton from '@/components/skeletons/ActivitySkeleton.vue'
import QuickActionButton from '@/components/dashboard/QuickActionButton.vue'
import TrendingProductCard from '@/components/products/TrendingProductCard.vue'
import TrendingProductSkeleton from '@/components/skeletons/TrendingProductSkeleton.vue'

// Stores
const authStore = useAuthStore()
const dashboardStore = useDashboardStore()
const router = useRouter()

// Reactive data
const chartPeriod = ref('30')
const loadingStats = ref(true)
const loadingProducts = ref(true)
const loadingActivity = ref(true)
const loadingTrending = ref(true)

// Computed
const user = computed(() => authStore.user)
const stats = computed(() => dashboardStore.stats)
const recentProducts = computed(() => dashboardStore.recentProducts)
const recentActivity = computed(() => dashboardStore.recentActivity)
const trendingProducts = computed(() => dashboardStore.trendingProducts)
const salesChartData = computed(() => dashboardStore.salesChartData)
const unreadMessages = computed(() => dashboardStore.unreadMessages)
const pendingOrders = computed(() => dashboardStore.pendingOrders)
const unreadNotifications = computed(() => dashboardStore.unreadNotifications)

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

const editProduct = (product) => {
  // Navigate to edit product page
  router.push(`/products/${product.id}/edit`)
}

const deleteProduct = async (product) => {
  if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
    try {
      await dashboardStore.deleteProduct(product.id)
      // Refresh products list
      await loadRecentProducts()
    } catch (error) {
      console.error('Error deleting product:', error)
    }
  }
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

const loadTrendingProducts = async () => {
  loadingTrending.value = true
  try {
    await dashboardStore.fetchTrendingProducts()
  } catch (error) {
    console.error('Error loading trending products:', error)
  } finally {
    loadingTrending.value = false
  }
}

// Watch chart period changes
watch(chartPeriod, async (newPeriod) => {
  loadingStats.value = true
  try {
    await dashboardStore.fetchSalesChart(newPeriod)
  } catch (error) {
    console.error('Error loading sales chart:', error)
  } finally {
    loadingStats.value = false
  }
})

// Lifecycle
onMounted(async () => {
  // Load all dashboard data
  await Promise.all([
    loadStats(),
    loadRecentProducts(),
    loadRecentActivity(),
    loadTrendingProducts()
  ])
})
</script>
