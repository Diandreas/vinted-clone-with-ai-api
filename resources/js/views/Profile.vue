<template>
  <div class="min-h-screen bg-gray-100">

    <!-- Header -->
    <div class="bg-white border-b border-gray-100 sticky top-0 z-10">
      <div class="flex items-center justify-between px-4 py-3">
        <button @click="router.back()" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
          <ArrowLeftIcon class="w-5 h-5 text-gray-700" />
        </button>
        <h1 class="text-base font-bold text-gray-900">Profil</h1>
        <RouterLink to="/profile/edit" class="w-9 h-9 flex items-center justify-center rounded-full bg-green-50 hover:bg-green-100 transition-colors">
          <EditIcon class="w-4 h-4 text-green-600" />
        </RouterLink>
      </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-5 space-y-4">

      <!-- Carte profil -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

        <!-- Avatar + infos -->
        <div class="flex flex-col items-center text-center mb-5">
          <div class="relative mb-3">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white text-3xl font-black shadow-lg">
              {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
            </div>
            <div class="absolute bottom-1 right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-white shadow-md"></div>
          </div>

          <h2 class="text-xl font-black text-gray-900">{{ user?.name || 'Utilisateur' }}</h2>
          <p v-if="user?.username" class="text-sm text-gray-400 font-medium mt-0.5">@{{ user.username }}</p>
          <p v-if="user?.bio" class="text-sm text-gray-600 leading-relaxed mt-2 max-w-sm">{{ user.bio }}</p>

          <div class="flex flex-wrap justify-center gap-2 mt-3">
            <span v-if="user?.location" class="inline-flex items-center gap-1.5 text-xs text-gray-500 bg-gray-50 border border-gray-100 px-3 py-1.5 rounded-full">
              <MapPinIcon class="w-3.5 h-3.5 text-green-500" /> {{ user.location }}
            </span>
            <a v-if="user?.website" :href="user.website" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-green-600 bg-green-50 border border-green-100 px-3 py-1.5 rounded-full">
              <LinkIcon class="w-3.5 h-3.5" /> Site web
            </a>
          </div>
        </div>

        <!-- Boutons action -->
        <div class="flex flex-col gap-2.5">
          <RouterLink to="/products/create"
            class="flex items-center justify-center gap-2 py-3 bg-green-500 text-white font-bold rounded-2xl shadow-md shadow-green-200/50 hover:bg-green-600 transition-colors active:scale-95">
            <PlusIcon class="w-5 h-5" /> Vendre un article
          </RouterLink>

          <div class="flex gap-2.5">
            <RouterLink to="/profile/edit"
              class="flex-1 flex items-center justify-center gap-2 py-2.5 border-2 border-gray-200 text-gray-700 font-semibold rounded-2xl hover:bg-gray-50 transition-colors text-sm">
              <EditIcon class="w-4 h-4" /> Modifier profil
            </RouterLink>
            <button @click="logout"
              class="flex-1 flex items-center justify-center gap-2 py-2.5 border-2 border-red-100 text-red-500 font-semibold rounded-2xl hover:bg-red-50 transition-colors text-sm">
              <LogOutIcon class="w-4 h-4" /> Déconnexion
            </button>
          </div>

          <!-- Activer produits en attente -->
          <button
            v-if="stats?.pending_payment_products > 0"
            @click="activateAllPendingProducts"
            :disabled="isActivatingAll"
            class="flex items-center justify-center gap-2 py-2.5 bg-amber-500 text-white font-semibold rounded-2xl hover:bg-amber-600 transition-colors text-sm disabled:opacity-60"
          >
            <div v-if="isActivatingAll" class="w-4 h-4 rounded-full border-2 border-white border-t-transparent animate-spin"></div>
            <PlayIcon v-else class="w-4 h-4" />
            {{ isActivatingAll ? 'Activation...' : `Activer les produits en attente (${stats.pending_payment_products})` }}
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-4 gap-2">
        <div v-for="s in statCards" :key="s.label"
          class="bg-white rounded-2xl border border-gray-100 shadow-sm py-3 flex flex-col items-center">
          <span class="text-xl font-black text-gray-900">{{ s.value }}</span>
          <span class="text-[11px] font-medium text-gray-400 mt-0.5">{{ s.label }}</span>
        </div>
      </div>

      <!-- Onglets + Contenu -->
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Tab bar -->
        <div class="flex border-b border-gray-100">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="flex-1 flex items-center justify-center gap-1.5 py-3 text-xs font-bold transition-colors relative"
            :class="activeTab === tab.id ? 'text-green-600' : 'text-gray-400 hover:text-gray-600'"
          >
            <component :is="tab.icon" class="w-4 h-4" />
            <span class="hidden sm:inline">{{ tab.label }}</span>
            <div v-if="activeTab === tab.id" class="absolute bottom-0 left-0 right-0 h-0.5 bg-green-500 rounded-t-full"></div>
          </button>
        </div>

        <div class="p-4">

          <!-- Produits -->
          <div v-if="activeTab === 'products'">
            <div class="flex items-center justify-between mb-4">
              <p class="font-bold text-gray-900">Mes produits</p>
              <RouterLink to="/products/create"
                class="flex items-center gap-1 text-xs font-bold text-green-600 bg-green-50 px-3 py-1.5 rounded-full hover:bg-green-100 transition-colors">
                <PlusIcon class="w-3.5 h-3.5" /> Ajouter
              </RouterLink>
            </div>

            <div v-if="loadingProducts" class="grid grid-cols-2 gap-3">
              <div v-for="i in 4" :key="i" class="bg-gray-100 rounded-xl aspect-square animate-pulse"></div>
            </div>

            <div v-else-if="products.length > 0" class="grid grid-cols-2 gap-3">
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

            <div v-else class="text-center py-12">
              <div class="w-16 h-16 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-4">
                <PackageIcon class="w-8 h-8 text-gray-300" />
              </div>
              <p class="font-bold text-gray-700 mb-1">Aucun produit</p>
              <p class="text-sm text-gray-400 mb-4">Commencez par créer votre premier article</p>
              <RouterLink to="/products/create"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-500 text-white text-sm font-bold rounded-full shadow-md shadow-green-200/50 hover:bg-green-600 transition-colors">
                <PlusIcon class="w-4 h-4" /> Créer un produit
              </RouterLink>
            </div>
          </div>

          <!-- Followers -->
          <div v-if="activeTab === 'followers'">
            <p class="font-bold text-gray-900 mb-4">Mes abonnés</p>
            <div v-if="loadingFollowers" class="space-y-2">
              <div v-for="i in 3" :key="i" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl animate-pulse">
                <div class="w-10 h-10 bg-gray-200 rounded-full flex-shrink-0"></div>
                <div class="flex-1 space-y-1.5">
                  <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                  <div class="h-2.5 bg-gray-200 rounded w-1/2"></div>
                </div>
              </div>
            </div>
            <div v-else-if="followers.length > 0" class="space-y-2">
              <div v-for="follower in followers" :key="follower.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ follower.name?.charAt(0)?.toUpperCase() || 'U' }}
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900 text-sm">{{ follower.name }}</p>
                    <p class="text-xs text-gray-400">@{{ follower.username }}</p>
                  </div>
                </div>
                <RouterLink :to="`/users/${follower.id}`"
                  class="text-xs font-bold text-green-600 bg-green-50 px-3 py-1.5 rounded-full hover:bg-green-100 transition-colors">
                  Voir
                </RouterLink>
              </div>
            </div>
            <div v-else class="text-center py-10">
              <div class="w-14 h-14 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-3">
                <UsersIcon class="w-7 h-7 text-gray-300" />
              </div>
              <p class="font-bold text-gray-700 mb-1">Aucun abonné</p>
              <p class="text-sm text-gray-400">Partagez vos produits pour attirer des abonnés</p>
            </div>
          </div>

          <!-- Following -->
          <div v-if="activeTab === 'following'">
            <p class="font-bold text-gray-900 mb-4">Mes abonnements</p>
            <div v-if="loadingFollowing" class="space-y-2">
              <div v-for="i in 3" :key="i" class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl animate-pulse">
                <div class="w-10 h-10 bg-gray-200 rounded-full flex-shrink-0"></div>
                <div class="flex-1 space-y-1.5">
                  <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                  <div class="h-2.5 bg-gray-200 rounded w-1/2"></div>
                </div>
              </div>
            </div>
            <div v-else-if="following.length > 0" class="space-y-2">
              <div v-for="followed in following" :key="followed.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ followed.name?.charAt(0)?.toUpperCase() || 'U' }}
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900 text-sm">{{ followed.name }}</p>
                    <p class="text-xs text-gray-400">@{{ followed.username }}</p>
                  </div>
                </div>
                <button @click="unfollowUser(followed.id)"
                  class="text-xs font-bold text-gray-500 bg-gray-100 border border-gray-200 px-3 py-1.5 rounded-full hover:bg-gray-200 transition-colors">
                  Se désabonner
                </button>
              </div>
            </div>
            <div v-else class="text-center py-10">
              <div class="w-14 h-14 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-3">
                <UsersIcon class="w-7 h-7 text-gray-300" />
              </div>
              <p class="font-bold text-gray-700 mb-1">Aucun abonnement</p>
              <p class="text-sm text-gray-400">Découvrez et suivez d'autres vendeurs</p>
            </div>
          </div>

          <!-- Activité -->
          <div v-if="activeTab === 'activity'">
            <p class="font-bold text-gray-900 mb-4">Mon activité</p>
            <div v-if="loadingActivity" class="space-y-2">
              <div v-for="i in 3" :key="i" class="p-3 bg-gray-50 rounded-xl animate-pulse">
                <div class="h-3 bg-gray-200 rounded w-3/4 mb-2"></div>
                <div class="h-2.5 bg-gray-200 rounded w-1/2"></div>
              </div>
            </div>
            <div v-else-if="recentActivity.length > 0" class="space-y-2">
              <div v-for="activity in recentActivity" :key="activity.id"
                class="flex items-start gap-3 p-3 bg-gray-50 rounded-xl">
                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                  <component :is="getActivityIcon(activity.type)" class="w-4 h-4 text-green-600" />
                </div>
                <div>
                  <p class="text-sm text-gray-700">{{ activity.description }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(activity.created_at) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-10">
              <div class="w-14 h-14 bg-gray-100 rounded-3xl flex items-center justify-center mx-auto mb-3">
                <ActivityIcon class="w-7 h-7 text-gray-300" />
              </div>
              <p class="font-bold text-gray-700 mb-1">Aucune activité</p>
              <p class="text-sm text-gray-400">Vos actions apparaîtront ici</p>
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
import ProductCard from '@/components/products/ProductCard.vue'
import api from '@/services/api'
import {
  ArrowLeftIcon, EditIcon, PlusIcon, PackageIcon, UsersIcon,
  ActivityIcon, MapPinIcon, LinkIcon, HeartIcon, ShoppingCartIcon,
  StarIcon, EyeIcon, LogOutIcon, PlayIcon
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const dashboardStore = useDashboardStore()

const activeTab      = ref('products')
const loadingProducts  = ref(false)
const loadingFollowers = ref(false)
const loadingFollowing = ref(false)
const loadingActivity  = ref(false)
const isActivatingAll  = ref(false)

const products       = ref([])
const followers      = ref([])
const following      = ref([])
const recentActivity = ref([])

const user  = computed(() => authStore.user)
const stats = computed(() => dashboardStore.stats.value)

const statCards = computed(() => [
  { label: 'Produits',   value: stats.value?.products_count  ?? 0 },
  { label: 'Abonnés',    value: stats.value?.followers_count ?? 0 },
  { label: 'Abonnements',value: stats.value?.following_count ?? 0 },
  { label: 'Ventes',     value: stats.value?.total_sales     ?? 0 },
])

const tabs = [
  { id: 'products',  label: 'Produits',      icon: PackageIcon  },
  { id: 'followers', label: 'Abonnés',       icon: UsersIcon    },
  { id: 'following', label: 'Abonnements',   icon: UsersIcon    },
  { id: 'activity',  label: 'Activité',      icon: ActivityIcon },
]

const loadProducts = async () => {
  loadingProducts.value = true
  try {
    const response = await api.get('/products/my-products', { params: { per_page: 20 } })
    products.value = response.data.data || []
  } catch { products.value = [] }
  finally  { loadingProducts.value = false }
}

const loadFollowers = async () => {
  loadingFollowers.value = true
  try {
    const response = await api.get('/users/my-followers')
    const d = response.data.data
    followers.value = d?.data ?? (Array.isArray(d) ? d : [])
  } catch { followers.value = [] }
  finally  { loadingFollowers.value = false }
}

const loadFollowing = async () => {
  loadingFollowing.value = true
  try {
    const response = await api.get('/users/my-following')
    const d = response.data.data
    following.value = d?.data ?? (Array.isArray(d) ? d : [])
  } catch { following.value = [] }
  finally  { loadingFollowing.value = false }
}

const loadActivity = async () => {
  loadingActivity.value = true
  try {
    const response = await api.get('/me/activity', { params: { limit: 10 } })
    const d = response.data.data
    recentActivity.value = d?.recent_actions ?? d?.activities ?? (Array.isArray(d) ? d : [])
  } catch { recentActivity.value = [] }
  finally  { loadingActivity.value = false }
}

const editProduct   = (p) => router.push(`/products/${p.id}/edit`)
const viewProduct   = (p) => router.push(`/products/${p.id}`)

const deleteProduct = async (product) => {
  if (!confirm('Supprimer ce produit ?')) return
  try {
    await api.delete(`/products/${product.id}`)
    await Promise.all([loadProducts(), loadUserStats()])
  } catch { alert('Erreur lors de la suppression') }
}

const shareProduct = async (product) => {
  const url = `${window.location.origin}/products/${product.id}`
  try {
    if (navigator.share) {
      await navigator.share({ title: product.title, url })
    } else {
      await navigator.clipboard.writeText(url)
      alert('Lien copié !')
    }
  } catch { /* ignored */ }
}

const logout = async () => { await authStore.logout() }

const unfollowUser = async (userId) => {
  try {
    await api.delete(`/users/${userId}/unfollow`)
    await Promise.all([loadFollowing(), loadUserStats()])
  } catch { alert('Erreur lors du désabonnement') }
}

const getActivityIcon = (type) => ({ like: HeartIcon, purchase: ShoppingCartIcon, review: StarIcon, view: EyeIcon }[type] ?? ActivityIcon)

const formatDate = (date) => date
  ? new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
  : ''

const activateAllPendingProducts = async () => {
  if (isActivatingAll.value) return
  isActivatingAll.value = true
  try {
    const response = await api.post('/products/create-bulk-payment')
    if (response.data.success) {
      const d = response.data.data
      if (d.payment_required && d.notchpay_payment_link) {
        localStorage.setItem('bulk_payment_info', JSON.stringify({ reference: d.notchpay_reference, total_amount: d.total_amount, product_count: d.product_count }))
        window.location.href = d.notchpay_payment_link
      } else {
        const r = await api.post('/products/activate-all-pending')
        if (r.data.success) {
          alert(`🎉 ${r.data.summary.activated} produit(s) activé(s) !`)
          await Promise.all([loadProducts(), loadUserStats()])
        }
      }
    }
  } catch (error) {
    alert(error.response?.data?.message ?? 'Erreur lors de l\'activation')
  } finally {
    isActivatingAll.value = false
  }
}

const loadUserStats = async () => {
  try {
    const [meRes, statsRes] = await Promise.all([
      api.get('/me/stats'),
      api.get('/products/stats')
    ])
    if (!dashboardStore.stats.value) {
      dashboardStore.stats.value = { products_count: 0, total_sales: 0, followers_count: 0, following_count: 0, pending_payment_products: 0 }
    }
    const u = meRes.data.data
    dashboardStore.stats.value.products_count  = u?.products?.total            ?? 0
    dashboardStore.stats.value.followers_count = u?.social?.followers_count    ?? 0
    dashboardStore.stats.value.following_count = u?.social?.following_count    ?? 0
    dashboardStore.stats.value.total_sales     = u?.sales?.total_earnings      ?? 0
    dashboardStore.stats.value.pending_payment_products = statsRes.data.data?.pending_payment_products ?? 0
  } catch { /* silent */ }
}

watch(activeTab, (tab) => {
  if (tab === 'products'  && products.value.length === 0)  loadProducts()
  if (tab === 'followers')  loadFollowers()
  if (tab === 'following')  loadFollowing()
  if (tab === 'activity')   loadActivity()
})

onMounted(() => Promise.all([loadProducts(), loadUserStats()]))
</script>
