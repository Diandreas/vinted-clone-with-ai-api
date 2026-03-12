<template>
  <div class="min-h-screen bg-gray-100">

    <!-- ───────────── COVER ───────────── -->
    <div class="relative h-52 overflow-hidden bg-gradient-to-br from-green-400 via-emerald-500 to-teal-600">
      <img
        v-if="user?.cover_image"
        :src="user.cover_image"
        class="w-full h-full object-cover"
      />
      <!-- Decorative blobs -->
      <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full blur-sm"></div>
      <div class="absolute top-10 -left-12 w-36 h-36 bg-white/10 rounded-full blur-sm"></div>
      <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-black/25 to-transparent"></div>

      <!-- Back -->
      <button
        @click="router.back()"
        class="absolute top-4 left-4 w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg hover:bg-white transition-colors"
      >
        <ArrowLeftIcon class="w-4 h-4 text-gray-800" />
      </button>

      <!-- More options -->
      <div v-if="!isSelf" class="absolute top-4 right-4">
        <button
          @click="showMoreOptions = !showMoreOptions"
          class="w-10 h-10 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-lg hover:bg-white transition-colors"
        >
          <MoreVerticalIcon class="w-4 h-4 text-gray-800" />
        </button>
        <div
          v-if="showMoreOptions"
          class="absolute top-12 right-0 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 min-w-[160px]"
        >
          <button @click="reportUser" class="w-full px-4 py-3 text-left text-sm text-red-500 hover:bg-red-50 flex items-center gap-3 transition-colors">
            <FlagIcon class="w-4 h-4" /> Signaler
          </button>
          <button @click="blockUser" class="w-full px-4 py-3 text-left text-sm text-red-500 hover:bg-red-50 flex items-center gap-3 transition-colors">
            <BanIcon class="w-4 h-4" /> Bloquer
          </button>
        </div>
      </div>
    </div>

    <!-- ───────────── PROFILE CARD ───────────── -->
    <div class="bg-white rounded-t-3xl -mt-6 relative z-10 shadow-xl">
      <div class="px-5 pt-2 pb-1">

        <!-- Avatar row -->
        <div class="flex items-start justify-between">

          <!-- Avatar -->
          <div class="-mt-12 mb-2">
            <div class="relative inline-block">
              <div class="w-24 h-24 rounded-full ring-4 ring-white shadow-2xl overflow-hidden">
                <ProfileIcon
                  :src="user?.avatar"
                  :alt="user?.name || 'Utilisateur'"
                  :user-id="user?.id"
                  size="2xl"
                  :verified="false"
                  :fallback-to-initials="true"
                  class="w-full h-full"
                />
              </div>
              <div
                v-if="user?.is_verified"
                class="absolute bottom-1 right-1 w-7 h-7 bg-green-500 rounded-full flex items-center justify-center ring-2 ring-white shadow-md"
              >
                <CheckIcon class="w-4 h-4 text-white" />
              </div>
            </div>
          </div>

          <!-- Action buttons -->
          <div class="flex items-center gap-2 mt-3">
            <!-- Follow / Unfollow -->
            <button
              v-if="!isSelf"
              :disabled="followBusy"
              @click="toggleFollow"
              class="flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold transition-all duration-200"
              :class="isFollowing
                ? 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200'
                : 'bg-green-500 text-white shadow-lg shadow-green-200/60 hover:bg-green-600 active:scale-95'"
            >
              <Loader2Icon v-if="followBusy" class="w-4 h-4 animate-spin" />
              <template v-else>
                <UserCheckIcon v-if="isFollowing" class="w-4 h-4" />
                <UserPlusIcon v-else class="w-4 h-4" />
              </template>
              {{ isFollowing ? 'Abonné' : 'Suivre' }}
            </button>

            <!-- Message -->
            <button
              v-if="!isSelf && isAuthenticated"
              @click="goToMessage"
              class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 border border-gray-200 transition-colors active:scale-95"
            >
              <MessageCircleIcon class="w-5 h-5 text-gray-600" />
            </button>

            <!-- Logout (own profile) -->
            <button
              v-if="isSelf"
              @click="logout"
              class="flex items-center gap-1.5 px-4 py-2.5 rounded-full text-sm font-semibold bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 transition-colors"
            >
              <LogOutIcon class="w-4 h-4" />
              Déconnexion
            </button>
          </div>
        </div>

        <!-- Name & Bio -->
        <div class="mb-5">
          <div class="flex items-center gap-2 flex-wrap mb-0.5">
            <h1 class="text-xl font-bold text-gray-900 leading-tight">{{ user?.name || 'Utilisateur' }}</h1>
            <span
              v-if="user?.is_verified"
              class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 border border-green-100 px-2 py-0.5 rounded-full"
            >
              <CheckCircleIcon class="w-3 h-3" /> Vérifié
            </span>
          </div>
          <p v-if="user?.username" class="text-sm text-gray-400 font-medium mb-2">@{{ user.username }}</p>
          <p v-if="user?.bio" class="text-sm text-gray-600 leading-relaxed mb-3">{{ user.bio }}</p>
          <div class="flex flex-wrap gap-3">
            <span v-if="user?.location" class="inline-flex items-center gap-1.5 text-xs text-gray-500 bg-gray-50 border border-gray-100 px-3 py-1.5 rounded-full font-medium">
              <MapPinIcon class="w-3.5 h-3.5 text-green-500" />
              {{ user.location }}
            </span>
            <a
              v-if="user?.website"
              :href="user.website"
              target="_blank"
              class="inline-flex items-center gap-1.5 text-xs text-green-600 bg-green-50 border border-green-100 px-3 py-1.5 rounded-full font-medium hover:bg-green-100 transition-colors"
            >
              <LinkIcon class="w-3.5 h-3.5" />
              Site web
            </a>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-3 gap-2.5 mb-5">
          <button
            @click="activeTab = 'products'"
            class="flex flex-col items-center py-3.5 rounded-2xl border-2 transition-all duration-200 active:scale-95"
            :class="activeTab === 'products'
              ? 'bg-green-500 border-green-500 shadow-lg shadow-green-200/60'
              : 'bg-gray-50 border-transparent hover:bg-gray-100'"
          >
            <span
              class="text-2xl font-black leading-none mb-1"
              :class="activeTab === 'products' ? 'text-white' : 'text-gray-900'"
            >{{ stats?.products_count ?? 0 }}</span>
            <span
              class="text-xs font-semibold"
              :class="activeTab === 'products' ? 'text-green-100' : 'text-gray-500'"
            >Produits</span>
          </button>
          <button
            @click="activeTab = 'followers'"
            class="flex flex-col items-center py-3.5 rounded-2xl border-2 transition-all duration-200 active:scale-95"
            :class="activeTab === 'followers'
              ? 'bg-green-500 border-green-500 shadow-lg shadow-green-200/60'
              : 'bg-gray-50 border-transparent hover:bg-gray-100'"
          >
            <span
              class="text-2xl font-black leading-none mb-1"
              :class="activeTab === 'followers' ? 'text-white' : 'text-gray-900'"
            >{{ stats?.followers_count ?? 0 }}</span>
            <span
              class="text-xs font-semibold"
              :class="activeTab === 'followers' ? 'text-green-100' : 'text-gray-500'"
            >Abonnés</span>
          </button>
          <button
            @click="activeTab = 'following'"
            class="flex flex-col items-center py-3.5 rounded-2xl border-2 transition-all duration-200 active:scale-95"
            :class="activeTab === 'following'
              ? 'bg-green-500 border-green-500 shadow-lg shadow-green-200/60'
              : 'bg-gray-50 border-transparent hover:bg-gray-100'"
          >
            <span
              class="text-2xl font-black leading-none mb-1"
              :class="activeTab === 'following' ? 'text-white' : 'text-gray-900'"
            >{{ stats?.following_count ?? 0 }}</span>
            <span
              class="text-xs font-semibold"
              :class="activeTab === 'following' ? 'text-green-100' : 'text-gray-500'"
            >Abonnements</span>
          </button>
        </div>

        <!-- Tab Pills -->
        <div class="flex gap-1 bg-gray-100 rounded-2xl p-1 mb-5">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="flex-1 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200"
            :class="activeTab === tab.id
              ? 'bg-white text-green-600 shadow-sm'
              : 'text-gray-500 hover:text-gray-700'"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- ───────────── CONTENT ───────────── -->
    <div class="px-4 pt-4 pb-28">

      <!-- Loading -->
      <div v-if="loading" class="flex flex-col items-center py-20 gap-4">
        <div class="relative w-14 h-14">
          <div class="absolute inset-0 rounded-full border-4 border-green-100"></div>
          <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-green-500 animate-spin"></div>
        </div>
        <span class="text-sm text-gray-400 font-medium">Chargement du profil...</span>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center py-20">
        <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
          <AlertTriangleIcon class="w-8 h-8 text-red-400" />
        </div>
        <p class="font-bold text-gray-800 mb-1 text-lg">Oups !</p>
        <p class="text-sm text-gray-500 mb-6 max-w-xs mx-auto">{{ error }}</p>
        <button
          @click="retryLoad"
          class="px-7 py-3 bg-green-500 text-white rounded-full text-sm font-bold shadow-lg shadow-green-200 hover:bg-green-600 transition-colors active:scale-95"
        >
          Réessayer
        </button>
      </div>

      <div v-else>
        <!-- Products -->
        <div v-if="activeTab === 'products'">
          <div v-if="products.length === 0" class="text-center py-20">
            <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-sm border border-gray-100">
              <PackageIcon class="w-9 h-9 text-gray-300" />
            </div>
            <p class="font-bold text-gray-700 text-lg mb-1">Aucun produit</p>
            <p class="text-sm text-gray-400 max-w-xs mx-auto">Cet utilisateur n'a pas encore mis de produits en vente.</p>
          </div>
          <div v-else>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <TikTokProductCard
                v-for="product in products"
                :key="product.id"
                :product="product"
                class="w-full"
              />
            </div>
            <div v-if="pagination.last > 1" class="flex items-center justify-center gap-3 mt-8">
              <button
                @click="goToPage(pagination.current - 1)"
                :disabled="pagination.current <= 1"
                class="px-5 py-2.5 text-sm font-semibold rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm transition-colors"
              >← Précédent</button>
              <span class="text-xs text-gray-400 font-medium bg-white border border-gray-100 rounded-full px-3 py-2 shadow-sm">
                {{ pagination.current }} / {{ pagination.last }}
              </span>
              <button
                @click="goToPage(pagination.current + 1)"
                :disabled="pagination.current >= pagination.last"
                class="px-5 py-2.5 text-sm font-semibold rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm transition-colors"
              >Suivant →</button>
            </div>
          </div>
        </div>

        <!-- Followers -->
        <div v-if="activeTab === 'followers'">
          <div v-if="followers.length === 0" class="text-center py-20">
            <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-sm border border-gray-100">
              <UserIcon class="w-9 h-9 text-gray-300" />
            </div>
            <p class="font-bold text-gray-700 text-lg mb-1">Aucun abonné</p>
            <p class="text-sm text-gray-400">Cet utilisateur n'a pas encore d'abonnés.</p>
          </div>
          <div v-else class="space-y-2.5">
            <div
              v-for="follower in followers"
              :key="follower.id"
              class="flex items-center justify-between bg-white rounded-2xl px-4 py-3 shadow-sm border border-gray-100"
            >
              <div class="flex items-center gap-3">
                <ProfileIcon
                  :src="follower.avatar"
                  :alt="follower.name || 'Utilisateur'"
                  :user-id="follower.id"
                  size="md"
                  :fallback-to-initials="true"
                  class="flex-shrink-0"
                />
                <div>
                  <p class="font-semibold text-gray-900 text-sm leading-tight">{{ follower.name || 'Utilisateur' }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">@{{ follower.username || 'username' }}</p>
                </div>
              </div>
              <button
                @click="toggleFollowUser(follower.id)"
                class="px-4 py-1.5 text-xs font-bold rounded-full transition-all active:scale-95"
                :class="follower.is_following
                  ? 'bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200'
                  : 'bg-green-500 text-white shadow-md shadow-green-200/50 hover:bg-green-600'"
              >
                {{ follower.is_following ? 'Abonné' : 'Suivre' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Following -->
        <div v-if="activeTab === 'following'">
          <div v-if="following.length === 0" class="text-center py-20">
            <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-sm border border-gray-100">
              <UsersIcon class="w-9 h-9 text-gray-300" />
            </div>
            <p class="font-bold text-gray-700 text-lg mb-1">Aucun abonnement</p>
            <p class="text-sm text-gray-400">Découvrez et suivez d'autres vendeurs.</p>
          </div>
          <div v-else class="space-y-2.5">
            <div
              v-for="followed in following"
              :key="followed.id"
              class="flex items-center justify-between bg-white rounded-2xl px-4 py-3 shadow-sm border border-gray-100"
            >
              <div class="flex items-center gap-3">
                <ProfileIcon
                  :src="followed.avatar"
                  :alt="followed.name || 'Utilisateur'"
                  :user-id="followed.id"
                  size="md"
                  :fallback-to-initials="true"
                  class="flex-shrink-0"
                />
                <div>
                  <p class="font-semibold text-gray-900 text-sm leading-tight">{{ followed.name || 'Utilisateur' }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">@{{ followed.username || 'username' }}</p>
                </div>
              </div>
              <button
                @click="toggleFollowUser(followed.id)"
                class="px-4 py-1.5 text-xs font-bold rounded-full transition-all active:scale-95"
                :class="followed.is_following
                  ? 'bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200'
                  : 'bg-green-500 text-white shadow-md shadow-green-200/50 hover:bg-green-600'"
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
  CheckCircleIcon,
  PackageIcon,
  UsersIcon,
  UserIcon,
  UserPlusIcon,
  UserCheckIcon,
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

const tabs = [
  { id: 'products', label: 'Produits' },
  { id: 'followers', label: 'Abonnés' },
  { id: 'following', label: 'Abonnements' }
]

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isSelf = computed(() => {
  const id = Number(route.params.id)
  return authStore.user && authStore.user.id === id
})

async function fetchUser() {
  try {
    const resp = await api.get(`/users/${route.params.id}`)
    const payload = resp.data?.data
    user.value = payload?.user || null
    stats.value = payload?.stats || null
    isFollowing.value = Boolean(payload?.is_following)
  } catch {
    error.value = 'Impossible de charger ce profil.'
  }
}

async function fetchProducts(page = 1) {
  try {
    const resp = await api.get(`/users/${route.params.id}/products`, { params: { page } })
    const d = resp.data?.data
    products.value = Array.isArray(d?.data) ? d.data : []
    pagination.value = {
      current: Number(d?.current_page || 1),
      last: Number(d?.last_page || 1),
      total: Number(d?.total || 0),
      perPage: Number(d?.per_page || 20)
    }
  } catch {
    error.value = 'Impossible de charger les produits.'
  }
}

async function fetchFollowers() {
  try {
    const resp = await api.get(`/users/${route.params.id}/followers`)
    const d = resp.data?.data
    followers.value = d?.data ?? (Array.isArray(d) ? d : [])
  } catch {
    error.value = 'Impossible de charger les abonnés.'
  }
}

async function fetchFollowing() {
  try {
    const resp = await api.get(`/users/${route.params.id}/following`)
    const d = resp.data?.data
    following.value = d?.data ?? (Array.isArray(d) ? d : [])
  } catch {
    error.value = 'Impossible de charger les abonnements.'
  }
}

async function loadTabContent() {
  switch (activeTab.value) {
    case 'products': await fetchProducts(); break
    case 'followers': await fetchFollowers(); break
    case 'following': await fetchFollowing(); break
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
      const resp = await api.delete(`/users/${route.params.id}/unfollow`)
      const data = resp.data?.data
      isFollowing.value = data.is_following
      if (stats.value) stats.value.followers_count = data.followers_count
    } else {
      const resp = await api.post(`/users/${route.params.id}/follow`)
      const data = resp.data?.data
      isFollowing.value = data.is_following
      if (stats.value) stats.value.followers_count = data.followers_count
    }
  } catch {
    await fetchUser()
  } finally {
    followBusy.value = false
  }
}

async function toggleFollowUser(userId) {
  // TODO: implement
}

function goToMessage() {
  router.push({ name: 'messages', query: { user: route.params.id } })
}

function goToPage(page) {
  if (page < 1 || page > pagination.value.last) return
  fetchProducts(page)
}

function logout() {
  authStore.logout()
  router.push({ name: 'login' })
}

function reportUser() { showMoreOptions.value = false }
function blockUser() { showMoreOptions.value = false }

function retryLoad() {
  error.value = ''
  loading.value = true
  loadInitialData()
}

async function loadInitialData() {
  try {
    await Promise.all([fetchUser(), fetchProducts()])
  } finally {
    loading.value = false
  }
}

watch(activeTab, () => loadTabContent())
watch(() => route.params.id, async () => {
  loading.value = true
  await loadInitialData()
})

onMounted(() => loadInitialData())
</script>

<style scoped>
.active\:scale-95:active {
  transform: scale(0.95);
}
</style>
