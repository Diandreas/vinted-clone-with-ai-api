<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Profil utilisateur</h1>
      </div>

      <!-- User block -->
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 sm:p-8">
          <!-- Loading state -->
          <div v-if="loadingUser" class="flex items-center justify-center py-16">
            <Loader2Icon class="h-6 w-6 animate-spin text-gray-400" />
            <span class="ml-2 text-gray-500">Chargement du profil…</span>
          </div>

          <!-- Error state -->
          <div v-else-if="userError" class="flex items-center justify-center py-12">
            <AlertTriangleIcon class="h-6 w-6 text-red-500" />
            <span class="ml-2 text-red-600">{{ userError }}</span>
          </div>

          <!-- Content -->
          <div v-else class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start">
              <img
                :src="userAvatar"
                :alt="user?.name || 'Utilisateur'"
                class="h-20 w-20 rounded-full object-cover ring-2 ring-gray-100"
                @error="onAvatarError"
              />
              <div class="ml-4">
                <div class="flex items-center flex-wrap gap-2">
                  <h2 class="text-2xl font-semibold text-gray-900">{{ user?.name || 'Utilisateur' }}</h2>
                  <BadgeCheckIcon v-if="user?.is_verified" class="h-5 w-5 text-blue-600" />
                </div>
                <p v-if="user?.username" class="text-gray-500">@{{ user.username }}</p>
                <div class="mt-2 flex items-center gap-4 text-sm text-gray-600 flex-wrap">
                  <div v-if="user?.location" class="flex items-center gap-1">
                    <MapPinIcon class="h-4 w-4" /> {{ user.location }}
                  </div>
                  <a v-if="user?.website" :href="user.website" target="_blank" rel="noopener" class="flex items-center gap-1 hover:underline">
                    <LinkIcon class="h-4 w-4" /> Site web
                  </a>
                </div>
              </div>
            </div>

            <div class="mt-4 sm:mt-0 flex items-center gap-3">
              <button
                v-if="!isSelf"
                @click="goToMessages()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border border-gray-200 text-gray-700 bg-white hover:bg-gray-50"
              >
                <MessageCircleIcon class="h-4 w-4" />
                <span>Message</span>
              </button>
              <button
                v-if="!isSelf"
                :disabled="followBusy"
                @click="toggleFollow"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border transition disabled:opacity-60 disabled:cursor-not-allowed"
                :class="isFollowing ? 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200' : 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700'"
              >
                <template v-if="followBusy">
                  <Loader2Icon class="h-4 w-4 animate-spin" />
                  <span>Patientez…</span>
                </template>
                <template v-else>
                  <UsersIcon class="h-4 w-4" />
                  <span>{{ isFollowing ? 'Abonné' : 'Suivre' }}</span>
                </template>
              </button>
            </div>
          </div>
        </div>

        <!-- Stats -->
        <div v-if="!loadingUser && !userError" class="grid grid-cols-2 sm:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-gray-100 bg-gray-50">
          <div class="p-5 flex items-center gap-3">
            <PackageIcon class="h-5 w-5 text-gray-500" />
            <div>
              <div class="text-sm text-gray-500">Produits</div>
              <div class="text-lg font-semibold">{{ stats?.products_count ?? 0 }}</div>
            </div>
          </div>
          <div class="p-5 flex items-center gap-3">
            <UsersIcon class="h-5 w-5 text-gray-500" />
            <div>
              <div class="text-sm text-gray-500">Abonnés</div>
              <div class="text-lg font-semibold">{{ stats?.followers_count ?? 0 }}</div>
            </div>
          </div>
          <div class="p-5 flex items-center gap-3">
            <UsersIcon class="h-5 w-5 text-gray-500" />
            <div>
              <div class="text-sm text-gray-500">Abonnements</div>
              <div class="text-lg font-semibold">{{ stats?.following_count ?? 0 }}</div>
            </div>
          </div>
          <div class="p-5 flex items-center gap-3">
            <StarIcon class="h-5 w-5 text-amber-500" />
            <div>
              <div class="text-sm text-gray-500">Note</div>
              <div class="text-lg font-semibold">{{ (stats?.average_rating ?? 0).toFixed(1) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Products list -->
      <div class="mt-8">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Produits de {{ user?.name || 'l’utilisateur' }}</h3>
        </div>

        <!-- Loading products -->
        <div v-if="loadingProducts" class="flex items-center justify-center py-16">
          <Loader2Icon class="h-6 w-6 animate-spin text-gray-400" />
          <span class="ml-2 text-gray-500">Chargement des produits…</span>
        </div>

        <!-- Error products -->
        <div v-else-if="productsError" class="flex items-center justify-center py-12">
          <AlertTriangleIcon class="h-6 w-6 text-red-500" />
          <span class="ml-2 text-red-600">{{ productsError }}</span>
        </div>

        <!-- Empty state -->
        <div v-else-if="products.length === 0" class="text-center py-16 bg-white rounded-xl border border-gray-200">
          <PackageIcon class="mx-auto h-10 w-10 text-gray-400" />
          <h4 class="mt-2 text-sm font-medium text-gray-900">Aucun produit</h4>
          <p class="mt-1 text-sm text-gray-500">Cet utilisateur n’a pas encore publié de produits.</p>
        </div>

        <!-- Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <ProductCard v-for="p in products" :key="p.id" :product="p" />
        </div>

        <!-- Pagination -->
        <div v-if="pagination.last > 1" class="mt-6 flex items-center justify-center gap-2">
          <button
            class="px-3 py-2 rounded-md border text-sm"
            :class="pagination.current === 1 ? 'text-gray-400 border-gray-200 cursor-not-allowed' : 'text-gray-700 border-gray-300 hover:bg-gray-50'"
            :disabled="pagination.current === 1 || loadingProducts"
            @click="goToPage(pagination.current - 1)"
          >Précédent</button>
          <span class="text-sm text-gray-600">Page {{ pagination.current }} / {{ pagination.last }}</span>
          <button
            class="px-3 py-2 rounded-md border text-sm"
            :class="pagination.current === pagination.last ? 'text-gray-400 border-gray-200 cursor-not-allowed' : 'text-gray-700 border-gray-300 hover:bg-gray-50'"
            :disabled="pagination.current === pagination.last || loadingProducts"
            @click="goToPage(pagination.current + 1)"
          >Suivant</button>
        </div>
      </div>
    </div>
  </div>
  
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import ProductCard from '@/components/products/ProductCard.vue'
import {
  UserIcon,
  PackageIcon,
  UsersIcon,
  StarIcon,
  LinkIcon,
  MapPinIcon,
  BadgeCheckIcon,
  Loader2Icon,
  AlertTriangleIcon,
  MessageCircleIcon
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const loadingUser = ref(true)
const loadingProducts = ref(true)
const followBusy = ref(false)
const userError = ref('')
const productsError = ref('')

const user = ref(null)
const stats = ref(null)
const isFollowing = ref(false)

const products = ref([])
const pagination = ref({ current: 1, last: 1, total: 0, perPage: 20 })

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isSelf = computed(() => {
  const id = Number(route.params.id)
  return authStore.user && authStore.user.id === id
})

const userAvatar = computed(() => {
  return user.value?.avatar || '/default-avatar.png'
})

function onAvatarError(event) {
  event.target.src = '/default-avatar.png'
}

async function fetchUser() {
  loadingUser.value = true
  userError.value = ''
  try {
    const resp = await window.axios.get(`/users/${route.params.id}`)
    const payload = resp.data?.data
    user.value = payload?.user || null
    stats.value = payload?.stats || null
    isFollowing.value = Boolean(payload?.is_following)
  } catch (err) {
    console.error('Failed to fetch user:', err)
    userError.value = 'Impossible de charger ce profil.'
  } finally {
    loadingUser.value = false
  }
}

async function fetchProducts(page = 1) {
  loadingProducts.value = true
  productsError.value = ''
  try {
    const resp = await window.axios.get(`/users/${route.params.id}/products`, { params: { page } })
    // API returns a paginator object in data
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
    productsError.value = 'Impossible de charger les produits.'
  } finally {
    loadingProducts.value = false
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
      const resp = await window.axios.delete(`/users/${route.params.id}/unfollow`)
      const data = resp.data?.data
      isFollowing.value = false
      if (stats.value) stats.value.followers_count = Number(data?.followers_count ?? Math.max((stats.value.followers_count || 1) - 1, 0))
    } else {
      const resp = await window.axios.post(`/users/${route.params.id}/follow`)
      const data = resp.data?.data
      isFollowing.value = Boolean(data?.is_following ?? true)
      if (stats.value) stats.value.followers_count = Number(data?.followers_count ?? (Number(stats.value.followers_count || 0) + 1))
    }
  } catch (err) {
    console.error('Failed to toggle follow:', err)
  } finally {
    followBusy.value = false
  }
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

onMounted(async () => {
  await Promise.all([fetchUser(), fetchProducts(1)])
})

watch(() => route.params.id, async () => {
  await Promise.all([fetchUser(), fetchProducts(1)])
})
</script>

