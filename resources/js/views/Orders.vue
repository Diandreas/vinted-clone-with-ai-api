<template>
  <div class="min-h-screen bg-gray-50">

    <!-- Header sticky -->
    <div class="bg-white border-b border-gray-200 sticky top-14 sm:top-16 z-10">
      <div class="max-w-2xl mx-auto px-4 pt-4 pb-0">
        <div class="flex items-center space-x-3 mb-4">
          <button
            @click="router.back()"
            class="flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors text-gray-600"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <h1 class="text-xl font-bold text-gray-900">Mes Commandes</h1>
        </div>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200">
          <button
            @click="switchTab('purchases')"
            :class="[
              'px-4 py-2.5 text-sm font-medium border-b-2 transition-colors',
              tab === 'purchases'
                ? 'border-primary-600 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            Achats
            <span v-if="purchasesCount > 0" class="ml-1.5 bg-primary-100 text-primary-700 text-xs rounded-full px-1.5 py-0.5">
              {{ purchasesCount }}
            </span>
          </button>
          <button
            @click="switchTab('sales')"
            :class="[
              'px-4 py-2.5 text-sm font-medium border-b-2 transition-colors',
              tab === 'sales'
                ? 'border-primary-600 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700'
            ]"
          >
            Ventes
            <span v-if="salesCount > 0" class="ml-1.5 bg-primary-100 text-primary-700 text-xs rounded-full px-1.5 py-0.5">
              {{ salesCount }}
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-2xl mx-auto px-4 py-4 space-y-3">

      <!-- Loading skeletons -->
      <template v-if="loading">
        <div v-for="i in 4" :key="i" class="bg-white rounded-xl border border-gray-200 p-4 animate-pulse">
          <div class="flex space-x-3">
            <div class="w-20 h-20 bg-gray-200 rounded-lg flex-shrink-0"></div>
            <div class="flex-1 space-y-2 py-1">
              <div class="h-4 bg-gray-200 rounded w-3/4"></div>
              <div class="h-3 bg-gray-200 rounded w-1/2"></div>
              <div class="h-3 bg-gray-200 rounded w-1/3"></div>
              <div class="h-3 bg-gray-200 rounded w-2/5 mt-2"></div>
            </div>
          </div>
        </div>
      </template>

      <!-- Empty state -->
      <div v-else-if="orders.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
        <p class="text-sm font-medium text-gray-900">Aucune commande</p>
        <p class="text-sm text-gray-500 mt-1">
          {{ tab === 'purchases' ? "Vous n'avez pas encore acheté de produits." : "Vous n'avez pas encore vendu de produits." }}
        </p>
        <RouterLink
          to="/products"
          class="mt-4 inline-block bg-primary-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors"
        >
          Explorer les produits
        </RouterLink>
      </div>

      <!-- Order cards -->
      <template v-else>
        <div
          v-for="order in orders"
          :key="order.id"
          class="bg-white rounded-xl border border-gray-200 overflow-hidden"
        >
          <!-- Card header -->
          <div class="flex items-center justify-between px-4 py-2.5 border-b border-gray-100 bg-gray-50/70">
            <div class="flex items-center space-x-2 text-xs text-gray-500">
              <span class="font-mono font-medium text-gray-700">#{{ order.order_number }}</span>
              <span>·</span>
              <span>{{ formatDate(order.created_at) }}</span>
            </div>
            <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', statusClass(order.status)]">
              {{ statusLabel(order.status) }}
            </span>
          </div>

          <!-- Card body -->
          <div class="p-4">
            <div class="flex space-x-3">
              <!-- Product image -->
              <RouterLink :to="`/products/${order.product?.id}`" class="flex-shrink-0">
                <img
                  :src="productImage(order.product)"
                  :alt="order.product?.title"
                  class="w-20 h-20 object-cover rounded-lg bg-gray-100"
                  loading="lazy"
                  @error="e => e.target.src = '/images/placeholder-product.png'"
                />
              </RouterLink>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <RouterLink :to="`/products/${order.product?.id}`">
                  <p class="text-sm font-semibold text-gray-900 truncate hover:text-primary-600 transition-colors">
                    {{ order.product?.title }}
                  </p>
                </RouterLink>

                <p class="text-xs text-gray-500 mt-0.5">
                  {{ tab === 'purchases' ? 'Vendeur' : 'Acheteur' }} :
                  <span class="font-medium text-gray-700">
                    {{ tab === 'purchases' ? order.seller?.name : order.buyer?.name }}
                  </span>
                </p>

                <p v-if="order.tracking_number" class="text-xs text-gray-500 mt-0.5">
                  Suivi : <span class="font-mono text-xs text-gray-700">{{ order.tracking_number }}</span>
                </p>

                <div class="flex items-center justify-between mt-2">
                  <div>
                    <span class="text-sm font-bold text-gray-900">{{ formatPrice(order.total_amount) }}</span>
                    <span v-if="order.shipping_cost > 0" class="text-xs text-gray-400 ml-1">
                      (dont {{ formatPrice(order.shipping_cost) }} livraison)
                    </span>
                  </div>
                  <span :class="['text-xs font-medium px-1.5 py-0.5 rounded', paymentClass(order.payment_status)]">
                    {{ paymentLabel(order.payment_status) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div v-if="orderActions(order).length > 0" class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-gray-100">
              <button
                v-for="action in orderActions(order)"
                :key="action.key"
                @click="handleAction(order, action.key)"
                :disabled="actionLoading === order.id + action.key"
                :class="[
                  'flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors disabled:opacity-50',
                  action.variant === 'danger'
                    ? 'bg-red-50 text-red-700 hover:bg-red-100'
                    : action.variant === 'success'
                    ? 'bg-green-50 text-green-700 hover:bg-green-100'
                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
                ]"
              >
                <span
                  v-if="actionLoading === order.id + action.key"
                  class="inline-block w-3 h-3 border border-current border-t-transparent rounded-full animate-spin"
                ></span>
                {{ action.label }}
              </button>
            </div>
          </div>
        </div>

        <!-- Load more -->
        <div v-if="hasMore" class="text-center py-2">
          <button
            @click="loadMore"
            :disabled="loadingMore"
            class="text-sm text-primary-600 hover:text-primary-700 font-medium disabled:opacity-50"
          >
            {{ loadingMore ? 'Chargement…' : 'Voir plus' }}
          </button>
        </div>
      </template>
    </div>

    <!-- Modal numéro de suivi -->
    <div
      v-if="showTrackingModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-end sm:items-center justify-center z-50 px-4 pb-4"
    >
      <div class="bg-white rounded-xl w-full max-w-sm p-5">
        <h3 class="text-base font-semibold text-gray-900 mb-1">Marquer comme expédié</h3>
        <p class="text-xs text-gray-500 mb-3">Numéro de suivi (optionnel)</p>
        <input
          v-model="trackingNumber"
          type="text"
          placeholder="Ex: FR123456789XX"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 mb-4"
          @keyup.enter="confirmShip"
        />
        <div class="flex gap-2">
          <button
            @click="showTrackingModal = false"
            class="flex-1 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Annuler
          </button>
          <button
            @click="confirmShip"
            :disabled="actionLoading !== null"
            class="flex-1 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 transition-colors disabled:opacity-50"
          >
            Confirmer l'envoi
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'

const router = useRouter()

const tab = ref('purchases')
const loading = ref(true)
const loadingMore = ref(false)
const actionLoading = ref(null)
const orders = ref([])
const currentPage = ref(1)
const lastPage = ref(1)
const purchasesCount = ref(0)
const salesCount = ref(0)
const showTrackingModal = ref(false)
const trackingNumber = ref('')
const pendingShipOrder = ref(null)

const hasMore = computed(() => currentPage.value < lastPage.value)

// ─── Fetch ────────────────────────────────────────────────────────────────────

const fetchOrders = async (page = 1) => {
  if (page === 1) loading.value = true
  else loadingMore.value = true

  try {
    const endpoint = tab.value === 'purchases' ? '/orders/purchases' : '/orders/sales'
    const res = await window.axios.get(endpoint, { params: { page } })
    const payload = res.data?.data || {}
    const items = payload.data || []

    orders.value = page === 1 ? items : [...orders.value, ...items]
    currentPage.value = payload.current_page || page
    lastPage.value = payload.last_page || 1

    if (tab.value === 'purchases') purchasesCount.value = payload.total || 0
    else salesCount.value = payload.total || 0
  } catch (e) {
    console.error('Orders fetch error', e)
  } finally {
    loading.value = false
    loadingMore.value = false
  }
}

const switchTab = (newTab) => {
  if (tab.value === newTab) return
  tab.value = newTab
  orders.value = []
  currentPage.value = 1
  fetchOrders(1)
}

const loadMore = () => {
  if (!loadingMore.value && hasMore.value) fetchOrders(currentPage.value + 1)
}

// ─── Actions ──────────────────────────────────────────────────────────────────

const orderActions = (order) => {
  const actions = []
  if (tab.value === 'purchases') {
    if (['pending', 'confirmed'].includes(order.status)) {
      actions.push({ key: 'cancel', label: 'Annuler', variant: 'danger' })
    }
    if (order.status === 'delivered') {
      actions.push({ key: 'dispute', label: 'Ouvrir un litige', variant: 'danger' })
    }
  } else {
    if (order.status === 'pending') {
      actions.push({ key: 'confirm', label: 'Confirmer', variant: 'success' })
      actions.push({ key: 'cancel', label: 'Refuser', variant: 'danger' })
    }
    if (order.status === 'confirmed') {
      actions.push({ key: 'ship', label: "Marquer expédié", variant: 'success' })
    }
    if (order.status === 'shipped') {
      actions.push({ key: 'deliver', label: 'Marquer livré', variant: 'success' })
    }
  }
  return actions
}

const handleAction = async (order, key) => {
  if (key === 'ship') {
    pendingShipOrder.value = order
    trackingNumber.value = ''
    showTrackingModal.value = true
    return
  }

  actionLoading.value = order.id + key
  try {
    if (key === 'cancel') {
      await window.axios.post(`/orders/${order.id}/cancel`)
      order.status = 'cancelled'
    } else if (key === 'dispute') {
      await window.axios.post(`/orders/${order.id}/dispute`)
      order.status = 'disputed'
    } else if (key === 'confirm') {
      await window.axios.put(`/orders/${order.id}/status`, { status: 'confirmed' })
      order.status = 'confirmed'
    } else if (key === 'deliver') {
      await window.axios.put(`/orders/${order.id}/status`, { status: 'delivered' })
      order.status = 'delivered'
    }
  } catch (e) {
    alert(e.response?.data?.message || 'Une erreur est survenue')
  } finally {
    actionLoading.value = null
  }
}

const confirmShip = async () => {
  if (!pendingShipOrder.value) return
  actionLoading.value = pendingShipOrder.value.id + 'ship'
  try {
    await window.axios.put(`/orders/${pendingShipOrder.value.id}/status`, {
      status: 'shipped',
      tracking_number: trackingNumber.value || null
    })
    pendingShipOrder.value.status = 'shipped'
    if (trackingNumber.value) pendingShipOrder.value.tracking_number = trackingNumber.value
    showTrackingModal.value = false
    pendingShipOrder.value = null
  } catch (e) {
    alert(e.response?.data?.message || 'Une erreur est survenue')
  } finally {
    actionLoading.value = null
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

const productImage = (product) => {
  if (!product) return '/images/placeholder-product.png'
  if (product.main_image_url) return product.main_image_url
  if (product.main_image) return product.main_image
  const first = product.images?.[0]
  return first?.url || first?.path || '/images/placeholder-product.png'
}

const formatPrice = (val) =>
  new Intl.NumberFormat('fr-FR').format(Number(val || 0)) + ' FCFA'

const formatDate = (date) => {
  if (!date) return ''
  try {
    return formatDistanceToNow(new Date(date), { addSuffix: true, locale: fr })
  } catch {
    return date
  }
}

const STATUS_LABELS = {
  pending:   'En attente',
  confirmed: 'Confirmée',
  shipped:   'Expédiée',
  delivered: 'Livrée',
  cancelled: 'Annulée',
  refunded:  'Remboursée',
  disputed:  'Litige',
}

const STATUS_CLASSES = {
  pending:   'bg-yellow-100 text-yellow-800',
  confirmed: 'bg-blue-100 text-blue-800',
  shipped:   'bg-indigo-100 text-indigo-800',
  delivered: 'bg-green-100 text-green-800',
  cancelled: 'bg-gray-100 text-gray-600',
  refunded:  'bg-orange-100 text-orange-800',
  disputed:  'bg-red-100 text-red-700',
}

const statusLabel = (s) => STATUS_LABELS[s] || s
const statusClass = (s) => STATUS_CLASSES[s] || 'bg-gray-100 text-gray-600'

const PAYMENT_LABELS = { pending: 'Non payé', paid: 'Payé', failed: 'Échoué', refunded: 'Remboursé' }
const PAYMENT_CLASSES = {
  pending:  'bg-yellow-50 text-yellow-700',
  paid:     'bg-green-50 text-green-700',
  failed:   'bg-red-50 text-red-700',
  refunded: 'bg-orange-50 text-orange-700',
}
const paymentLabel = (s) => PAYMENT_LABELS[s] || s
const paymentClass = (s) => PAYMENT_CLASSES[s] || 'bg-gray-50 text-gray-600'

// ─── Lifecycle ────────────────────────────────────────────────────────────────

onMounted(() => fetchOrders(1))
</script>
