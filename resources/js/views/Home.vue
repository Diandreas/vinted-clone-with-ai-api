<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 overflow-hidden">
      <div class="absolute inset-0 bg-black opacity-10"></div>
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
          <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
            Vendez, Achetez, Streamez
          </h1>
          <p class="text-xl md:text-2xl text-white opacity-90 mb-8 max-w-3xl mx-auto">
            La nouvelle façon de faire du shopping. Découvrez des produits uniques, 
            participez à des lives shopping et rejoignez notre communauté.
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <RouterLink
              to="/products"
              class="bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition-colors"
            >
              Explorer les Produits
            </RouterLink>
            <RouterLink
              to="/lives"
              class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-indigo-600 transition-colors"
            >
              Voir les Lives
            </RouterLink>
            <button
              @click="downloadAPK"
              :disabled="downloadingAPK"
              class="bg-green-600 text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-green-700 transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <DownloadIcon v-if="!downloadingAPK" class="w-6 h-6" />
              <div v-else class="w-6 h-6 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
              {{ downloadingAPK ? 'Téléchargement...' : 'Télécharger l\'App' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Features Section -->
    <div class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Pourquoi choisir sellam ?
          </h2>
          <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Une plateforme complète qui révolutionne l'expérience du shopping en ligne
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <FeatureCard
            icon="shopping-bag"
            title="Marketplace Sécurisé"
            description="Achetez et vendez en toute sécurité avec notre système de paiement protégé et notre service client dédié."
            color="blue"
          />
          <FeatureCard
            icon="video"
            title="Live Shopping"
            description="Participez à des sessions de shopping en direct, interagissez avec les vendeurs et découvrez des produits en temps réel."
            color="red"
          />
          <FeatureCard
            icon="users"
            title="Communauté Active"
            description="Rejoignez une communauté passionnée, suivez vos vendeurs préférés et partagez vos coups de cœur."
            color="purple"
          />
        </div>
      </div>
    </div>

    <!-- Trending Products Section -->
    <div class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Produits Tendance</h2>
            <p class="text-lg text-gray-600">Découvrez les articles les plus populaires du moment</p>
          </div>
          <RouterLink
            to="/products"
            class="text-indigo-600 hover:text-indigo-700 font-semibold"
          >
            Voir tout →
          </RouterLink>
        </div>

        <div v-if="loadingTrending" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <ProductSkeleton v-for="i in 8" :key="i" />
        </div>
        
        <div v-else-if="trendingProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <RouterLink
            v-for="product in trendingProducts"
            :key="product.id"
            :to="`/products/${product.id}`"
            class="block"
          >
            <ProductCard :product="product" />
          </RouterLink>
        </div>

        <div v-else class="text-center py-16">
          <TrendingUpIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun produit tendance</h3>
          <p class="text-gray-600">Revenez plus tard pour découvrir les tendances</p>
        </div>
      </div>
    </div>

    <!-- Live Streams Section -->
    <div class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Lives en Cours</h2>
            <p class="text-lg text-gray-600">Rejoignez les sessions de shopping en direct</p>
          </div>
          <RouterLink
            to="/lives"
            class="text-red-600 hover:text-red-700 font-semibold"
          >
            Voir tout →
          </RouterLink>
        </div>

        <div v-if="loadingLives" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <LiveSkeleton v-for="i in 6" :key="i" />
        </div>
        
        <div v-else-if="liveLives.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <RouterLink
            v-for="live in liveLives"
            :key="live.id"
            :to="`/lives/${live.id}`"
            class="block"
          >
            <LiveCard :live="live" />
          </RouterLink>
        </div>

        <div v-else class="text-center py-16">
          <RadioIcon class="w-16 h-16 text-gray-300 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun live en cours</h3>
          <p class="text-gray-600">Revenez plus tard ou créez votre propre live</p>
          <RouterLink
            v-if="isAuthenticated"
            to="/lives/create"
            class="inline-block mt-4 bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition-colors"
          >
            Créer un Live
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- Categories Section -->
    <div class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-bold text-gray-900 mb-4">Parcourir par Catégorie</h2>
          <p class="text-lg text-gray-600">Trouvez exactement ce que vous cherchez</p>
        </div>

        <div v-if="loadingCategories" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
          <CategorySkeleton v-for="i in 12" :key="i" />
        </div>
        
        <div v-else-if="categories.length > 0" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
          <RouterLink
            v-for="category in categories"
            :key="category.id"
            :to="`/products?category=${category.id}`"
            class="block"
          >
            <CategoryCard :category="category" />
          </RouterLink>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="py-20 bg-indigo-600">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-white mb-4">
          Prêt à commencer ?
        </h2>
        <p class="text-xl text-indigo-100 mb-8 max-w-2xl mx-auto">
          Rejoignez des milliers d'utilisateurs qui font déjà confiance à notre plateforme
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <RouterLink
            v-if="!isAuthenticated"
            to="/register"
            class="bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition-colors"
          >
            S'inscrire Gratuitement
          </RouterLink>
          <RouterLink
            v-else
            to="/products/create"
            class="bg-white text-indigo-600 px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-50 transition-colors"
          >
            Vendre un Article
          </RouterLink>
          <RouterLink
            to="/products"
            class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-indigo-600 transition-colors"
          >
            Commencer à Acheter
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { TrendingUpIcon, RadioIcon, DownloadIcon } from 'lucide-vue-next'

// Components
import FeatureCard from '@/components/home/FeatureCard.vue'
import ProductCard from '@/components/products/ProductCard.vue'
import ProductSkeleton from '@/components/skeletons/ProductSkeleton.vue'
import LiveCard from '@/components/lives/LiveCard.vue'
import LiveSkeleton from '@/components/skeletons/LiveSkeleton.vue'
import CategoryCard from '@/components/categories/CategoryCard.vue'
import CategorySkeleton from '@/components/skeletons/CategorySkeleton.vue'

const authStore = useAuthStore()

// Reactive data
const trendingProducts = ref([])
const liveLives = ref([])
const categories = ref([])
const loadingTrending = ref(true)
const loadingLives = ref(true)
const loadingCategories = ref(true)
const downloadingAPK = ref(false)

// Computed
const isAuthenticated = computed(() => authStore.isAuthenticated)

// Methods
const downloadAPK = async () => {
  if (downloadingAPK.value) return
  
  downloadingAPK.value = true
  try {
    // Utiliser la route API pour le téléchargement
    const response = await window.axios.get('/download/app', {
      responseType: 'blob'
    })
    
    // Créer un blob et déclencher le téléchargement
    const blob = new Blob([response.data], { 
      type: 'application/vnd.android.package-archive' 
    })
    const url = window.URL.createObjectURL(blob)
    
    const link = document.createElement('a')
    link.href = url
    link.download = 'sellam-app.apk'
    link.style.display = 'none'
    
    document.body.appendChild(link)
    link.click()
    
    // Nettoyer
    document.body.removeChild(link)
    window.URL.revokeObjectURL(url)
    
    console.log('Téléchargement de l\'APK démarré')
  } catch (error) {
    console.error('Erreur lors du téléchargement:', error)
    if (error.response?.status === 404) {
      alert('L\'APK n\'est pas encore disponible. Veuillez réessayer plus tard.')
    } else {
      alert('Erreur lors du téléchargement. Veuillez réessayer.')
    }
  } finally {
    downloadingAPK.value = false
  }
}

const fetchTrendingProducts = async () => {
  loadingTrending.value = true
  try {
    const response = await window.axios.get('/trending', { params: { limit: 8 } })
    trendingProducts.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching trending products:', error)
  } finally {
    loadingTrending.value = false
  }
}

const fetchLiveLives = async () => {
  loadingLives.value = true
  try {
    const response = await window.axios.get('/lives', { 
      params: { status: 'live', limit: 6 } 
    })
    liveLives.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching live streams:', error)
  } finally {
    loadingLives.value = false
  }
}

const fetchCategories = async () => {
  loadingCategories.value = true
  try {
    const response = await window.axios.get('/categories', { params: { limit: 12 } })
    categories.value = response.data.data || []
  } catch (error) {
    console.error('Error fetching categories:', error)
  } finally {
    loadingCategories.value = false
  }
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    fetchTrendingProducts(),
    fetchLiveLives(),
    fetchCategories()
  ])
})
</script>

