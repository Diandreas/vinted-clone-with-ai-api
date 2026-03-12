<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Cover + Header -->
    <div class="relative bg-white shadow-sm">

      <!-- Cover Image -->
      <div class="h-36 sm:h-48 md:h-56 bg-gradient-to-br from-green-500 via-emerald-600 to-teal-700 relative overflow-hidden">
        <img
          v-if="user?.cover_image"
          :src="user.cover_image"
          :alt="`Couverture de ${user?.name}`"
          class="w-full h-full object-cover"
        />
        <div class="absolute inset-0 bg-black/15"></div>

        <!-- Back Button -->
        <button
          @click="router.back()"
          class="absolute top-4 left-4 p-2 bg-white/90 backdrop-blur-sm rounded-full shadow-md hover:bg-white transition-colors"
        >
          <ArrowLeftIcon class="w-4 h-4 text-gray-700" />
        </button>

        <!-- More Options -->
        <div class="absolute top-4 right-4">
          <button
            v-if="!isSelf"
            @click="showMoreOptions = !showMoreOptions"
            class="p-2 bg-white/90 backdrop-blur-sm rounded-full shadow-md hover:bg-white transition-colors"
          >
            <MoreVerticalIcon class="w-4 h-4 text-gray-700" />
          </button>

          <!-- Dropdown -->
          <div
            v-if="showMoreOptions"
            class="absolute top-10 right-0 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50 min-w-[150px]"
          >
            <button
              @click="reportUser"
              class="w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2.5 transition-colors"
            >
              <FlagIcon class="w-4 h-4 flex-shrink-0" />
              Signaler
            </button>
            <button
              @click="blockUser"
              class="w-full px-4 py-2.5 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2.5 transition-colors"
            >
              <BanIcon class="w-4 h-4 flex-shrink-0" />
              Bloquer
            </button>
          </div>
        </div>
      </div>

      <!-- Profile Info -->
      <div class="max-w-3xl mx-auto px-4">
        <!-- Avatar row -->
        <div class="flex items-end justify-between -mt-10 sm:-mt-12 mb-3">
          <div class="relative">
            <ProfileIcon
              :src="user?.avatar"
              :alt="user?.name || 'Utilisateur'"
              :user-id="user?.id"
              size="2xl"
              :verified="user?.is_verified"
              :fallback-to-initials="true"
              class="border-4 border-white shadow-lg w-20 h-20 sm:w-24 sm:h-24"
            />
          </div>

          <!-- Action Buttons -->
          <div class="flex items-center gap-2 mb-1">
            <!-- Bouton Suivre/Abonné -->
            <button
              v-if="!isSelf"
              :disabled="followBusy"
              @click="toggleFollow"
              class="flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold transition-all shadow-sm"
              :class="isFollowing
                ? 'bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200'
                : 'bg-green-500 text-white hover:bg-green-600 shadow-green-200 shadow-md'"
            >
              <Loader2Icon v-if="followBusy" class="w-4 h-4 animate-spin" />
              <template v-else>
                <CheckIcon v-if="isFollowing" class="w-4 h-4" />
                <UserPlusIcon v-else class="w-4 h-4" />
              </template>
              <span>{{ isFollowing ? 'Abonné' : 'Suivre' }}</span>
            </button>

            <!-- Message (hors son propre profil) -->
            <button
              v-if="!isSelf && isAuthenticated"
              @click="goToMessage"
              class="flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-200 transition-colors"
            >
              <MessageCircleIcon class="w-4 h-4" />
              <span class="hidden sm:inline">Message</span>
            </button>

            <!-- Déconnexion (son propre profil) -->
            <button
              v-if="isSelf"
              @click="logout"
              class="flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 transition-colors"
            >
              <LogOutIcon class="w-4 h-4" />
              <span>Déconnexion</span>
            </button>
          </div>
        </div>

        <!-- Name & Bio -->
        <div class="pb-4">
          <div class="flex flex-wrap items-center gap-2 mb-0.5">
            <h1 class="text-lg sm:text-xl font-bold text-gray-900">{{ user?.name || 'Utilisateur' }}</h1>
            <span v-if="user?.username" class="text-sm text-gray-400 font-medium">@{{ user.username }}</span>
          </div>
          <p v-if="user?.bio" class="text-gray-600 text-sm leading-relaxed mt-1 max-w-md">{{ user.bio }}</p>
          <div class="flex flex-wrap gap-4 mt-2 text-xs text-gray-500">
            <span v-if="user?.location" class="flex items-center gap-1">
              <MapPinIcon class="w-3.5 h-3.5 text-green-500" />
              {{ user.location }}
            </span>
            <a
              v-if="user?.website"
              :href="user.website"
              target="_blank"
              class="flex items-center gap-1 text-green-600 hover:text-green-700 font-medium transition-colors"
            >
              <LinkIcon class="w-3.5 h-3.5" />
              Site web
            </a>
          </div>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-3 divide-x divide-gray-100 border-t border-gray-100">
          <button
            class="flex flex-col items-center py-3 hover:bg-gray-50 transition-colors group"
            @click="activeTab = 'products'"
            :class="activeTab === 'products' ? 'text-green-600' : 'text-gray-500'"
          >
            <span class="text-lg font-bold text-gray-900 group-hover:text-green-600 transition-colors">{{ stats?.products_count ?? 0 }}</span>
            <span class="text-xs font-medium">Produits</span>
          </button>
          <button
            class="flex flex-col items-center py-3 hover:bg-gray-50 transition-colors group"
            @click="activeTab = 'followers'"
            :class="activeTab === 'followers' ? 'text-green-600' : 'text-gray-500'"
          >
            <span class="text-lg font-bold text-gray-900 group-hover:text-green-600 transition-colors">{{ stats?.followers_count ?? 0 }}</span>
            <span class="text-xs font-medium">Abonnés</span>
          </button>
          <button
            class="flex flex-col items-center py-3 hover:bg-gray-50 transition-colors group"
            @click="activeTab = 'following'"
            :class="activeTab === 'following' ? 'text-green-600' : 'text-gray-500'"
          >
            <span class="text-lg font-bold text-gray-900 group-hover:text-green-600 transition-colors">{{ stats?.following_count ?? 0 }}</span>
            <span class="text-xs font-medium">Abonnements</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Tabs + Content -->
    <div class="max-w-3xl mx-auto px-4 pt-4 pb-8">

      <!-- Tab Navigation -->
      <div class="flex bg-white rounded-xl border border-gray-100 shadow-sm p-1 mb-5 gap-1">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="flex-1 py-2 px-3 text-sm font-medium rounded-lg transition-all duration-200"
          :class="activeTab === tab.id
            ? 'bg-green-500 text-white shadow-sm'
            : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
        >
          {{ tab.label }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex flex-col items-center justify-center py-16 gap-3">
        <Loader2Icon class="w-8 h-8 animate-spin text-green-500" />
        <span class="text-sm text-gray-500 font-medium">Chargement...</span>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="text-center py-16">
        <div class="w-14 h-14 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
          <AlertTriangleIcon class="w-7 h-7 text-red-500" />
        </div>
        <p class="text-gray-700 font-medium mb-1">Impossible de charger ce profil</p>
        <p class="text-gray-500 text-sm mb-4">{{ error }}</p>
        <button
          @click="retryLoad"
          class="px-5 py-2 bg-green-500 hover:bg-green-600 text-white rounded-full text-sm font-medium transition-colors shadow-md"
        >
          Réessayer
        </button>
      </div>

      <!-- Content -->
      <div v-else>

        <!-- Products Tab -->
        <div v-if="activeTab === 'products'">
          <div v-if="products.length === 0" class="text-center py-16">
            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <PackageIcon class="w-7 h-7 text-gray-400" />
            </div>
            <p class="text-gray-700 font-medium mb-1">Aucun produit</p>
            <p class="text-gray-500 text-sm">Cet utilisateur n'a pas encore mis de produits en vente.</p>
          </div>

          <div v-else>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
              <TikTokProductCard
                v-for="product in products"
                :key="product.id"
                :product="product"
                class="w-full"
              />
            </div>

            <!-- Pagination -->
            <div v-if="pagination.last > 1" class="flex items-center justify-center gap-2 mt-8">
              <button
                @click="goToPage(pagination.current - 1)"
                :disabled="pagination.current <= 1"
                class="px-4 py-2 text-sm font-medium bg-white border border-gray-200 rounded-full text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm"
              >
                Précédent
              </button>
              <span class="text-sm text-gray-500 font-medium px-2">
                {{ pagination.current }} / {{ pagination.last }}
              </span>
              <button
                @click="goToPage(pagination.current + 1)"
                :disabled="pagination.current >= pagination.last"
                class="px-4 py-2 text-sm font-medium bg-white border border-gray-200 rounded-full text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm"
              >
                Suivant
              </button>
            </div>
          </div>
        </div>

        <!-- Followers Tab -->
        <div v-if="activeTab === 'followers'">
          <div v-if="followers.length === 0" class="text-center py-16">
            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <UserIcon class="w-7 h-7 text-gray-400" />
            </div>
            <p class="text-gray-700 font-medium mb-1">Aucun abonné</p>
            <p class="text-gray-500 text-sm">Cet utilisateur n'a pas encore d'abonnés.</p>
          </div>
          <div v-else class="space-y-2">
            <UserListCard
              v-for="follower in followers"
              :key="follower.id"
              :user="follower"
              @toggle-follow="toggleFollowUser(follower.id)"
            />
          </div>
        </div>

        <!-- Following Tab -->
        <div v-if="activeTab === 'following'">
          <div v-if="following.length === 0" class="text-center py-16">
            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
              <UsersIcon class="w-7 h-7 text-gray-400" />
            </div>
            <p class="text-gray-700 font-medium mb-1">Aucun abonnement</p>
            <p class="text-gray-500 text-sm">Découvrez et suivez d'autres vendeurs.</p>
          </div>
          <div v-else class="space-y-2">
            <UserListCard
              v-for="followed in following"
              :key="followed.id"
              :user="followed"
              @toggle-follow="toggleFollowUser(followed.id)"
            />
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, defineComponent, h } from 'vue'
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
  PackageIcon,
  UsersIcon,
  UserIcon,
  UserPlusIcon,
  LinkIcon,
  MapPinIcon,
  MessageCircleIcon,
  LogOutIcon,
  Loader2Icon,
  AlertTriangleIcon
} from 'lucide-vue-next'

// Composant inline pour les cartes utilisateurs (followers/following)
const UserListCard = defineComponent({
  props: { user: Object },
  emits: ['toggle-follow'],
  setup(props, { emit }) {
    return () => h('div', {
      class: 'flex items-center justify-between bg-white rounded-xl px-4 py-3 border border-gray-100 shadow-sm hover:shadow-md transition-shadow'
    }, [
      h('div', { class: 'flex items-center gap-3' }, [
        h(ProfileIcon, {
          src: props.user.avatar,
          alt: props.user.name || 'Utilisateur',
          userId: props.user.id,
          size: 'md',
          fallbackToInitials: true,
          class: 'flex-shrink-0'
        }),
        h('div', {}, [
          h('p', { class: 'font-semibold text-gray-900 text-sm' }, props.user.name || 'Utilisateur'),
          h('p', { class: 'text-xs text-gray-400' }, `@${props.user.username || 'username'}`)
        ])
      ]),
      h('button', {
        onClick: () => emit('toggle-follow'),
        class: props.user.is_following
          ? 'px-3 py-1.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-200 transition-colors'
          : 'px-3 py-1.5 text-xs font-semibold rounded-full bg-green-500 text-white hover:bg-green-600 transition-colors shadow-sm'
      }, props.user.is_following ? 'Abonné' : 'Suivre')
    ])
  }
})

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

// Computed
const isAuthenticated = computed(() => authStore.isAuthenticated)
const isSelf = computed(() => {
  const id = Number(route.params.id)
  return authStore.user && authStore.user.id === id
})

// Methods
async function fetchUser() {
  try {
    const resp = await api.get(`/users/${route.params.id}`)
    const payload = resp.data?.data
    user.value = payload?.user || null
    stats.value = payload?.stats || null
    isFollowing.value = Boolean(payload?.is_following)
  } catch (err) {
    error.value = 'Impossible de charger ce profil.'
  }
}

async function fetchProducts(page = 1) {
  try {
    const resp = await api.get(`/users/${route.params.id}/products`, { params: { page } })
    const pageData = resp.data?.data
    products.value = Array.isArray(pageData?.data) ? pageData.data : []
    pagination.value = {
      current: Number(pageData?.current_page || 1),
      last: Number(pageData?.last_page || 1),
      total: Number(pageData?.total || 0),
      perPage: Number(pageData?.per_page || 20)
    }
  } catch (err) {
    error.value = 'Impossible de charger les produits.'
  }
}

async function fetchFollowers() {
  try {
    const resp = await api.get(`/users/${route.params.id}/followers`)
    if (resp.data?.data?.data) {
      followers.value = resp.data.data.data
    } else if (Array.isArray(resp.data?.data)) {
      followers.value = resp.data.data
    } else {
      followers.value = []
    }
  } catch (err) {
    error.value = 'Impossible de charger les abonnés.'
  }
}

async function fetchFollowing() {
  try {
    const resp = await api.get(`/users/${route.params.id}/following`)
    if (resp.data?.data?.data) {
      following.value = resp.data.data.data
    } else if (Array.isArray(resp.data?.data)) {
      following.value = resp.data.data
    } else {
      following.value = []
    }
  } catch (err) {
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
  } catch (err) {
    await fetchUser()
  } finally {
    followBusy.value = false
  }
}

async function toggleFollowUser(userId) {
  // Implementation for following/unfollowing other users
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

function reportUser() {
  showMoreOptions.value = false
}

function blockUser() {
  showMoreOptions.value = false
}

function retryLoad() {
  error.value = ''
  loading.value = true
  loadInitialData()
}

async function loadInitialData() {
  try {
    await Promise.all([fetchUser(), fetchProducts()])
  } catch (err) {
    // silent
  } finally {
    loading.value = false
  }
}

// Watchers
watch(activeTab, () => loadTabContent())
watch(() => route.params.id, async () => {
  loading.value = true
  await loadInitialData()
})

// Lifecycle
onMounted(async () => {
  await loadInitialData()
})
</script>
