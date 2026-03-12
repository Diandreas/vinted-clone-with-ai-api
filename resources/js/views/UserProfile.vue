<template>
  <div class="min-h-screen bg-gray-100">

    <!-- ══════════════ COVER ══════════════ -->
    <div class="relative">
      <div class="h-56 bg-gradient-to-br from-green-400 via-emerald-500 to-teal-600 overflow-hidden">
        <img v-if="user?.cover_image" :src="user.cover_image" class="w-full h-full object-cover" />
        <!-- Motif décoratif -->
        <div class="absolute inset-0 opacity-20">
          <div class="absolute top-4 right-8 w-32 h-32 rounded-full bg-white/30 blur-xl"></div>
          <div class="absolute -bottom-4 left-4 w-24 h-24 rounded-full bg-white/20 blur-lg"></div>
          <div class="absolute top-12 left-1/2 w-20 h-20 rounded-full bg-white/10 blur-lg"></div>
        </div>
        <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-transparent to-black/40"></div>
      </div>

      <!-- Bouton retour -->
      <button
        @click="router.back()"
        class="absolute top-5 left-4 w-10 h-10 bg-black/30 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-black/50 transition-colors"
      >
        <ArrowLeftIcon class="w-5 h-5 text-white" />
      </button>

      <!-- Options -->
      <div v-if="!isSelf" class="absolute top-5 right-4">
        <button
          @click="showMoreOptions = !showMoreOptions"
          class="w-10 h-10 bg-black/30 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-black/50 transition-colors"
        >
          <MoreVerticalIcon class="w-5 h-5 text-white" />
        </button>
        <div v-if="showMoreOptions" class="absolute top-12 right-0 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-50 min-w-[160px]">
          <button @click="reportUser" class="w-full px-4 py-3 text-left text-sm text-red-500 hover:bg-red-50 flex items-center gap-3">
            <FlagIcon class="w-4 h-4" /> Signaler
          </button>
          <button @click="blockUser" class="w-full px-4 py-3 text-left text-sm text-red-500 hover:bg-red-50 flex items-center gap-3">
            <BanIcon class="w-4 h-4" /> Bloquer
          </button>
        </div>
      </div>
    </div>

    <!-- ══════════════ CARTE PROFIL ══════════════ -->
    <div class="bg-white rounded-t-[2rem] -mt-8 relative z-10">
      <div class="px-5">

        <!-- ─ Avatar + Boutons ─ -->
        <div class="flex items-start justify-between pt-1">

          <!-- Avatar (géré sans ProfileIcon pour contrôler la taille) -->
          <div class="-mt-16">
            <div class="relative">
              <div class="w-28 h-28 rounded-full ring-[5px] ring-white shadow-2xl overflow-hidden bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center flex-shrink-0">
                <img
                  v-if="user?.avatar && !avatarError"
                  :src="user.avatar"
                  :alt="user?.name"
                  class="w-full h-full object-cover"
                  @error="avatarError = true"
                />
                <span v-else class="text-white font-black text-3xl select-none">
                  {{ initials }}
                </span>
              </div>
              <!-- Badge vérifié -->
              <div
                v-if="user?.is_verified"
                class="absolute bottom-1.5 right-1.5 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center ring-[3px] ring-white shadow-lg"
              >
                <CheckIcon class="w-4 h-4 text-white stroke-[3]" />
              </div>
            </div>
          </div>

          <!-- Boutons action -->
          <div class="flex items-center gap-2.5 mt-4">
            <button
              v-if="!isSelf"
              :disabled="followBusy"
              @click="toggleFollow"
              class="flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-bold transition-all duration-200 active:scale-95"
              :class="isFollowing
                ? 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200'
                : 'bg-green-500 text-white shadow-lg shadow-green-300/50 hover:bg-green-600'"
            >
              <Loader2Icon v-if="followBusy" class="w-4 h-4 animate-spin" />
              <UserCheckIcon v-else-if="isFollowing" class="w-4 h-4" />
              <UserPlusIcon v-else class="w-4 h-4" />
              <span>{{ isFollowing ? 'Abonné' : 'Suivre' }}</span>
            </button>

            <button
              v-if="!isSelf && isAuthenticated"
              @click="goToMessage"
              class="w-11 h-11 bg-gray-100 rounded-full flex items-center justify-center border border-gray-200 hover:bg-gray-200 transition-colors active:scale-95"
            >
              <MessageCircleIcon class="w-5 h-5 text-gray-600" />
            </button>

            <button
              v-if="isSelf"
              @click="logout"
              class="flex items-center gap-1.5 px-4 py-2.5 rounded-full text-sm font-semibold bg-red-50 text-red-500 border border-red-100 hover:bg-red-100 transition-colors"
            >
              <LogOutIcon class="w-4 h-4" />
              Déco.
            </button>
          </div>
        </div>

        <!-- ─ Nom & Bio ─ -->
        <div class="mt-3 mb-5">
          <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-2xl font-black text-gray-900 leading-tight">{{ user?.name || 'Utilisateur' }}</h1>
            <span
              v-if="user?.is_verified"
              class="inline-flex items-center gap-1 text-xs font-bold text-green-700 bg-green-100 px-2.5 py-1 rounded-full"
            >
              <CheckCircleIcon class="w-3.5 h-3.5" /> Vérifié
            </span>
          </div>
          <p v-if="user?.username" class="text-sm text-gray-400 font-medium mt-0.5">@{{ user.username }}</p>
          <p v-if="user?.bio" class="text-sm text-gray-600 leading-relaxed mt-2 max-w-sm">{{ user.bio }}</p>

          <div class="flex flex-wrap gap-2 mt-3">
            <span
              v-if="user?.location"
              class="inline-flex items-center gap-1.5 text-xs text-gray-500 bg-gray-50 border border-gray-100 px-3 py-1.5 rounded-full font-medium"
            >
              <MapPinIcon class="w-3.5 h-3.5 text-green-500" /> {{ user.location }}
            </span>
            <a
              v-if="user?.website"
              :href="user.website"
              target="_blank"
              class="inline-flex items-center gap-1.5 text-xs text-green-600 bg-green-50 border border-green-100 px-3 py-1.5 rounded-full font-medium hover:bg-green-100 transition-colors"
            >
              <LinkIcon class="w-3.5 h-3.5" /> Site web
            </a>
          </div>
        </div>

        <!-- ─ Stats ─ -->
        <div class="grid grid-cols-3 gap-3 mb-6">
          <button
            v-for="stat in statCards"
            :key="stat.tab"
            @click="activeTab = stat.tab"
            class="relative flex flex-col items-center py-4 rounded-2xl border-2 transition-all duration-200 active:scale-95 overflow-hidden"
            :class="activeTab === stat.tab
              ? 'border-green-500 bg-green-500 shadow-lg shadow-green-300/40'
              : 'border-gray-100 bg-gray-50 hover:bg-gray-100'"
          >
            <span
              class="text-2xl font-black leading-none"
              :class="activeTab === stat.tab ? 'text-white' : 'text-gray-900'"
            >{{ stat.value }}</span>
            <span
              class="text-xs font-semibold mt-1"
              :class="activeTab === stat.tab ? 'text-green-100' : 'text-gray-400'"
            >{{ stat.label }}</span>
          </button>
        </div>

        <!-- ─ Onglets ─ -->
        <div class="flex bg-gray-100 rounded-2xl p-1 gap-1 mb-1">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            class="flex-1 py-2.5 text-sm font-bold rounded-xl transition-all duration-200"
            :class="activeTab === tab.id
              ? 'bg-white text-green-600 shadow-sm'
              : 'text-gray-400 hover:text-gray-600'"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>
    </div>

    <!-- ══════════════ CONTENU ══════════════ -->
    <div class="px-4 pt-4 pb-28">

      <!-- Chargement -->
      <div v-if="loading" class="flex flex-col items-center py-20 gap-4">
        <div class="relative w-14 h-14">
          <div class="absolute inset-0 rounded-full border-4 border-gray-200"></div>
          <div class="absolute inset-0 rounded-full border-4 border-transparent border-t-green-500 animate-spin"></div>
        </div>
        <p class="text-sm text-gray-400 font-medium">Chargement du profil...</p>
      </div>

      <!-- Erreur -->
      <div v-else-if="error" class="text-center py-20">
        <div class="w-20 h-20 bg-red-50 rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-sm">
          <AlertTriangleIcon class="w-9 h-9 text-red-400" />
        </div>
        <p class="font-black text-gray-800 text-xl mb-1">Oups !</p>
        <p class="text-sm text-gray-500 mb-6">{{ error }}</p>
        <button @click="retryLoad" class="px-7 py-3 bg-green-500 text-white rounded-full text-sm font-bold shadow-lg shadow-green-200 hover:bg-green-600 transition-colors">
          Réessayer
        </button>
      </div>

      <div v-else>

        <!-- ── Produits ── -->
        <div v-if="activeTab === 'products'">
          <div v-if="products.length === 0" class="text-center py-20">
            <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-sm border border-gray-100">
              <PackageIcon class="w-9 h-9 text-gray-300" />
            </div>
            <p class="font-bold text-gray-700 text-lg mb-1">Aucun produit</p>
            <p class="text-sm text-gray-400">Pas encore de produits en vente.</p>
          </div>
          <div v-else>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
              <TikTokProductCard v-for="product in products" :key="product.id" :product="product" class="w-full" />
            </div>
            <div v-if="pagination.last > 1" class="flex items-center justify-center gap-3 mt-8">
              <button @click="goToPage(pagination.current - 1)" :disabled="pagination.current <= 1"
                class="px-5 py-2.5 text-sm font-bold rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                ← Précédent
              </button>
              <span class="text-xs text-gray-400 font-bold bg-white border border-gray-100 px-4 py-2.5 rounded-full shadow-sm">
                {{ pagination.current }} / {{ pagination.last }}
              </span>
              <button @click="goToPage(pagination.current + 1)" :disabled="pagination.current >= pagination.last"
                class="px-5 py-2.5 text-sm font-bold rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed shadow-sm">
                Suivant →
              </button>
            </div>
          </div>
        </div>

        <!-- ── Abonnés ── -->
        <div v-if="activeTab === 'followers'">
          <div v-if="followers.length === 0" class="text-center py-20">
            <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-sm border border-gray-100">
              <UserIcon class="w-9 h-9 text-gray-300" />
            </div>
            <p class="font-bold text-gray-700 text-lg mb-1">Aucun abonné</p>
            <p class="text-sm text-gray-400">Cet utilisateur n'a pas encore d'abonnés.</p>
          </div>
          <div v-else class="space-y-2.5">
            <UserRow v-for="u in followers" :key="u.id" :user="u" @follow="toggleFollowUser(u.id)" />
          </div>
        </div>

        <!-- ── Abonnements ── -->
        <div v-if="activeTab === 'following'">
          <div v-if="following.length === 0" class="text-center py-20">
            <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-5 shadow-sm border border-gray-100">
              <UsersIcon class="w-9 h-9 text-gray-300" />
            </div>
            <p class="font-bold text-gray-700 text-lg mb-1">Aucun abonnement</p>
            <p class="text-sm text-gray-400">Découvrez et suivez d'autres vendeurs.</p>
          </div>
          <div v-else class="space-y-2.5">
            <UserRow v-for="u in following" :key="u.id" :user="u" @follow="toggleFollowUser(u.id)" />
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
import api from '@/services/api'

import {
  ArrowLeftIcon, MoreVerticalIcon, FlagIcon, BanIcon,
  CheckIcon, CheckCircleIcon, PackageIcon, UsersIcon, UserIcon,
  UserPlusIcon, UserCheckIcon, LinkIcon, MapPinIcon,
  MessageCircleIcon, LogOutIcon, Loader2Icon, AlertTriangleIcon
} from 'lucide-vue-next'

// ─── Composant ligne utilisateur ───────────────────────────────────────────
const UserRow = defineComponent({
  name: 'UserRow',
  props: { user: Object },
  emits: ['follow'],
  setup(props, { emit }) {
    const avatarErr = ref(false)
    const initials = computed(() => {
      const n = props.user?.name || ''
      return n.trim().split(' ').slice(0, 2).map(w => w[0]?.toUpperCase()).join('') || '?'
    })
    return () => h('div', {
      class: 'flex items-center justify-between bg-white rounded-2xl px-4 py-3.5 shadow-sm border border-gray-100 hover:shadow-md transition-shadow'
    }, [
      h('div', { class: 'flex items-center gap-3 min-w-0' }, [
        h('div', {
          class: 'w-12 h-12 rounded-full flex-shrink-0 overflow-hidden bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center shadow-md'
        }, [
          props.user?.avatar && !avatarErr.value
            ? h('img', { src: props.user.avatar, class: 'w-full h-full object-cover', onError: () => { avatarErr.value = true } })
            : h('span', { class: 'text-white font-bold text-sm' }, initials.value)
        ]),
        h('div', { class: 'min-w-0' }, [
          h('p', { class: 'font-bold text-gray-900 text-sm truncate' }, props.user?.name || 'Utilisateur'),
          h('p', { class: 'text-xs text-gray-400 truncate mt-0.5' }, `@${props.user?.username || ''}`)
        ])
      ]),
      h('button', {
        onClick: () => emit('follow'),
        class: [
          'flex-shrink-0 ml-2 px-4 py-1.5 text-xs font-bold rounded-full transition-all active:scale-95',
          props.user?.is_following
            ? 'bg-gray-100 text-gray-600 border border-gray-200 hover:bg-gray-200'
            : 'bg-green-500 text-white shadow-md shadow-green-200/60 hover:bg-green-600'
        ].join(' ')
      }, props.user?.is_following ? 'Abonné' : 'Suivre')
    ])
  }
})

// ─── Setup ─────────────────────────────────────────────────────────────────
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const loading     = ref(true)
const followBusy  = ref(false)
const error       = ref('')
const avatarError = ref(false)
const showMoreOptions = ref(false)

const user      = ref(null)
const stats     = ref(null)
const isFollowing = ref(false)

const products  = ref([])
const followers = ref([])
const following = ref([])
const pagination = ref({ current: 1, last: 1, total: 0 })
const activeTab  = ref('products')

const tabs = [
  { id: 'products',  label: 'Produits' },
  { id: 'followers', label: 'Abonnés' },
  { id: 'following', label: 'Abonnements' }
]

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isSelf = computed(() => {
  const id = Number(route.params.id)
  return authStore.user && authStore.user.id === id
})

const initials = computed(() => {
  const n = user.value?.name || ''
  return n.trim().split(' ').slice(0, 2).map(w => w[0]?.toUpperCase()).join('') || '?'
})

const statCards = computed(() => [
  { tab: 'products',  label: 'Produits',     value: stats.value?.products_count  ?? 0 },
  { tab: 'followers', label: 'Abonnés',      value: stats.value?.followers_count ?? 0 },
  { tab: 'following', label: 'Abonnements',  value: stats.value?.following_count ?? 0 },
])

// ─── API ───────────────────────────────────────────────────────────────────
async function fetchUser() {
  try {
    const resp = await api.get(`/users/${route.params.id}`)
    const payload = resp.data?.data
    user.value = payload?.user || null
    stats.value = payload?.stats || null
    isFollowing.value = Boolean(payload?.is_following)
    avatarError.value = false
  } catch {
    error.value = 'Impossible de charger ce profil.'
  }
}

async function fetchProducts(page = 1) {
  try {
    const resp = await api.get(`/users/${route.params.id}/products`, { params: { page } })
    const d = resp.data?.data
    products.value = Array.isArray(d?.data) ? d.data : []
    pagination.value = { current: Number(d?.current_page || 1), last: Number(d?.last_page || 1) }
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
    case 'products':  await fetchProducts(); break
    case 'followers': await fetchFollowers(); break
    case 'following': await fetchFollowing(); break
  }
}

async function toggleFollow() {
  if (!isAuthenticated.value) { router.push({ name: 'login', query: { redirect: route.fullPath } }); return }
  if (isSelf.value || followBusy.value) return
  followBusy.value = true
  try {
    if (isFollowing.value) {
      const resp = await api.delete(`/users/${route.params.id}/unfollow`)
      const d = resp.data?.data
      isFollowing.value = d.is_following
      if (stats.value) stats.value.followers_count = d.followers_count
    } else {
      const resp = await api.post(`/users/${route.params.id}/follow`)
      const d = resp.data?.data
      isFollowing.value = d.is_following
      if (stats.value) stats.value.followers_count = d.followers_count
    }
  } catch { await fetchUser() }
  finally   { followBusy.value = false }
}

async function toggleFollowUser(userId) { /* TODO */ }

function goToMessage() { router.push({ name: 'messages', query: { user: route.params.id } }) }
function goToPage(page) { if (page >= 1 && page <= pagination.value.last) fetchProducts(page) }
function logout()       { authStore.logout(); router.push({ name: 'login' }) }
function reportUser()   { showMoreOptions.value = false }
function blockUser()    { showMoreOptions.value = false }
function retryLoad()    { error.value = ''; loading.value = true; loadInitialData() }

async function loadInitialData() {
  try   { await Promise.all([fetchUser(), fetchProducts()]) }
  finally { loading.value = false }
}

watch(activeTab, () => loadTabContent())
watch(() => route.params.id, async () => { loading.value = true; await loadInitialData() })
onMounted(() => loadInitialData())
</script>
