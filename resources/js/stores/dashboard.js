import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useDashboardStore = defineStore('dashboard', () => {
  // State
  const stats = ref({
    products_count: 0,
    total_sales: 0,
    followers_count: 0,
    following_count: 0,
    monthly_views: 0,
    products_trend: 0,
    sales_trend: 0,
    followers_trend: 0,
    views_trend: 0
  })

  const recentProducts = ref([])
  const recentActivity = ref([])
  const trendingProducts = ref([])
  const salesChartData = ref({
    labels: [],
    datasets: []
  })

  const unreadMessages = ref(0)
  const pendingOrders = ref(0)
  const unreadNotifications = ref(0)

  // Actions
  const fetchStats = async () => {
    try {
      const response = await api.get('/analytics/dashboard')
      
      // L'API retourne response.data.data.overview
      if (response.data.success && response.data.data && response.data.data.overview) {
        const overview = response.data.data.overview
        const thisMonth = response.data.data.this_month || {}
        
        // Mapper les données de l'API vers le format attendu par le store
        stats.value = {
          products_count: overview.total_products || 0,
          total_sales: overview.total_sales || 0,
          followers_count: overview.followers_count || 0,
          following_count: overview.following_count || 0,
          monthly_views: thisMonth.products_added || 0, // Utiliser les produits ajoutés ce mois comme approximation
          products_trend: 0, // À calculer si nécessaire
          sales_trend: 0, // À calculer si nécessaire
          followers_trend: thisMonth.new_followers || 0,
          views_trend: 0 // À calculer si nécessaire
        }
      }
      
      return response.data
    } catch (error) {
      console.error('Failed to fetch dashboard stats:', error)
      throw error
    }
  }

  const fetchRecentProducts = async (limit = 5) => {
    try {
      const response = await api.get('/products/my-products', {
        params: { limit, sort: 'recent' }
      })
      recentProducts.value = response.data.data || []
      return response.data
    } catch (error) {
      console.error('Failed to fetch recent products:', error)
      throw error
    }
  }

  const fetchRecentActivity = async (limit = 10) => {
    try {
      const response = await api.get('/me/activity', {
        params: { limit }
      })
      recentActivity.value = response.data.data || []
      return response.data
    } catch (error) {
      console.error('Failed to fetch recent activity:', error)
      throw error
    }
  }

  const fetchTrendingProducts = async (limit = 5) => {
    try {
      const response = await api.get('/trending', {
        params: { limit }
      })
      trendingProducts.value = response.data.data || []
      return response.data
    } catch (error) {
      console.error('Failed to fetch trending products:', error)
      throw error
    }
  }

  const fetchSalesChart = async (period = 30) => {
    try {
      const response = await api.get('/analytics/sales', {
        params: { period }
      })

      const salesByDay = response.data.data?.sales_by_day || {}
      
      // Convertir l'objet sales_by_day en tableaux pour le graphique
      const dates = Object.keys(salesByDay)
      const salesData = Object.values(salesByDay)
      
      // Pour l'instant, on utilise les mêmes données pour les ventes et commandes
      // car l'API ne retourne qu'un seul type de données par jour
      salesChartData.value = {
        labels: dates,
        datasets: [
          {
            label: 'Ventes (Fcfa)',
            data: salesData,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
          },
          {
            label: 'Commandes',
            data: salesData, // Même données pour l'instant
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true,
            yAxisID: 'y1'
          }
        ]
      }

      return response.data
    } catch (error) {
      console.error('Failed to fetch sales chart:', error)
      // Return empty data on error
      salesChartData.value = {
        labels: [],
        datasets: []
      }
      throw error
    }
  }

  const deleteProduct = async (productId) => {
    try {
      await api.delete(`/products/${productId}`)

      // Remove from recent products list
      recentProducts.value = recentProducts.value.filter(
        product => product.id !== productId
      )

      // Update stats
      stats.value.products_count = Math.max(0, stats.value.products_count - 1)

      return true
    } catch (error) {
      console.error('Failed to delete product:', error)
      throw error
    }
  }

  const boostProduct = async (productId) => {
    try {
      const response = await api.put(`/products/${productId}/boost`)

      // Update product in recent products list
      const productIndex = recentProducts.value.findIndex(
        product => product.id === productId
      )

      if (productIndex !== -1) {
        recentProducts.value[productIndex] = {
          ...recentProducts.value[productIndex],
          is_boosted: true,
          boosted_until: response.data.boosted_until
        }
      }

      return response.data
    } catch (error) {
      console.error('Failed to boost product:', error)
      throw error
    }
  }

  const markNotificationsAsRead = async () => {
    try {
      await api.post('/notifications/mark-all-read')
      unreadNotifications.value = 0
      return true
    } catch (error) {
      console.error('Failed to mark notifications as read:', error)
      throw error
    }
  }

  const refreshData = async () => {
    try {
      await Promise.all([
        fetchStats(),
        fetchRecentProducts(),
        fetchRecentActivity(),
        fetchTrendingProducts(),
        fetchSalesChart()
      ])
    } catch (error) {
      console.error('Failed to refresh dashboard data:', error)
      throw error
    }
  }

  return {
    // State
    stats,
    recentProducts,
    recentActivity,
    trendingProducts,
    salesChartData,
    unreadMessages,
    pendingOrders,
    unreadNotifications,

    // Actions
    fetchStats,
    fetchRecentProducts,
    fetchRecentActivity,
    fetchTrendingProducts,
    fetchSalesChart,
    deleteProduct,
    boostProduct,
    markNotificationsAsRead,
    refreshData
  }
})

