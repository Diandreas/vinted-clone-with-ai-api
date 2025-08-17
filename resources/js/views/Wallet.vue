<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Mon Portefeuille</h1>
        <p class="mt-2 text-gray-600">Gérez votre solde et rechargez votre portefeuille</p>
      </div>

      <!-- Messages de session -->
      <div v-if="messages.success || messages.error || messages.warning" class="mb-8">
        <!-- Success Message -->
        <div v-if="messages.success" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-green-800">{{ messages.success }}</p>
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="messages.error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-red-800">{{ messages.error }}</p>
            </div>
          </div>
        </div>

        <!-- Warning Message -->
        <div v-if="messages.warning" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-yellow-800">{{ messages.warning }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Balance Card -->
      <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 text-white mb-8">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-indigo-100 text-sm font-medium">Solde disponible</p>
            <p class="text-3xl font-bold">{{ walletStore.balanceFormatted }}</p>
            <p class="text-indigo-100 text-xs mt-1">
              Dernière mise à jour: {{ formatDate(walletStore.lastUpdated) }}
            </p>
          </div>
          <div class="text-right">
            <WalletIcon class="w-12 h-12 text-indigo-200" />
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="max-w-md mx-auto mb-8">
        <!-- Top Up Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center space-x-3 mb-4">
            <div class="p-2 bg-green-100 rounded-lg">
              <PlusIcon class="w-6 h-6 text-green-600" />
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Recharger</h3>
              <p class="text-sm text-gray-600">Ajoutez des fonds à votre portefeuille</p>
            </div>
          </div>
          <button
            @click="showTopUpModal = true"
            class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors font-medium"
            :disabled="walletStore.loading"
          >
            <span v-if="!walletStore.loading">Recharger maintenant</span>
            <span v-else class="flex items-center justify-center">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              Traitement...
            </span>
          </button>
        </div>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg mr-3">
              <TrendingUpIcon class="w-6 h-6 text-blue-600" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">{{ walletStore.formatAmount(walletStore.monthlyStats.deposits) }}</p>
              <p class="text-sm text-gray-600">Recharges ce mois</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg mr-3">
              <CalculatorIcon class="w-6 h-6 text-green-600" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">{{ walletStore.formatAmount(walletStore.totalDeposits) }}</p>
              <p class="text-sm text-gray-600">Total recharges</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg mr-3">
              <ActivityIcon class="w-6 h-6 text-purple-600" />
            </div>
            <div>
              <p class="text-2xl font-bold text-gray-900">{{ walletStore.monthlyStats.transactionCount }}</p>
              <p class="text-sm text-gray-600">Transactions ce mois</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Transaction History -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
              <HistoryIcon class="w-6 h-6 text-gray-600" />
              <h2 class="text-xl font-semibold text-gray-900">Historique des transactions</h2>
            </div>
            <button
              @click="refreshTransactions"
              class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
              :disabled="walletStore.transactionLoading"
            >
              <RefreshCcwIcon class="w-5 h-5" :class="{ 'animate-spin': walletStore.transactionLoading }" />
            </button>
          </div>
        </div>

        <div class="divide-y divide-gray-200">
          <div v-if="walletStore.transactions.length === 0" class="p-6 text-center text-gray-500">
            <CircleDollarSignIcon class="w-12 h-12 mx-auto text-gray-300 mb-4" />
            <p>Aucune transaction trouvée</p>
            <p class="text-sm mt-1">Vos transactions apparaîtront ici</p>
          </div>

          <div
            v-for="transaction in walletStore.transactions"
            :key="transaction.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <div
                  class="p-2 rounded-lg"
                  :class="getTransactionBgColor(transaction)"
                >
                  <component
                    :is="getTransactionIcon(transaction)"
                    class="w-5 h-5"
                    :class="getTransactionIconColor(transaction)"
                  />
                </div>
                <div>
                  <h4 class="font-medium text-gray-900">{{ getTransactionTitle(transaction) }}</h4>
                  <p class="text-sm text-gray-600">{{ formatDate(transaction.created_at) }}</p>
                  <div class="flex items-center space-x-2 mt-1">
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="getStatusBadgeColor(transaction.status)"
                    >
                      {{ getStatusLabel(transaction.status) }}
                    </span>
                    <span class="text-xs text-gray-500">{{ transaction.provider }}</span>
                  </div>
                </div>
              </div>
              <div class="text-right">
                <p
                  class="text-lg font-semibold"
                  :class="getAmountColor(transaction)"
                >
                  {{ getAmountPrefix(transaction) }}{{ walletStore.formatAmount(transaction.amount_xaf) }}
                </p>
                <p class="text-xs text-gray-500">{{ transaction.trans_id || 'N/A' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Load More -->
        <div v-if="walletStore.transactions.length > 0" class="p-4 border-t border-gray-200">
          <button
            @click="loadMoreTransactions"
            class="w-full py-2 px-4 text-center text-gray-600 hover:text-gray-800 transition-colors"
            :disabled="walletStore.transactionLoading"
          >
            <span v-if="!walletStore.transactionLoading">Charger plus</span>
            <span v-else class="flex items-center justify-center">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600 mr-2"></div>
              Chargement...
            </span>
          </button>
        </div>
      </div>
    </div>

    <!-- Top Up Modal -->
    <TopUpModal
      v-if="showTopUpModal"
      @close="showTopUpModal = false"
      @success="handleTopUpSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useWalletStore } from '@/stores/wallet'
import {
  WalletIcon,
  PlusIcon,
  TrendingUpIcon,
  BarChart3Icon,
  ActivityIcon,
  RefreshCcwIcon,
  HistoryIcon,
  CircleDollarSignIcon,
  ShoppingCartIcon,
  ArrowUpIcon,
  CalculatorIcon
} from 'lucide-vue-next'

// Components
import TopUpModal from '@/components/wallet/TopUpModal.vue'

// Store
const walletStore = useWalletStore()

// Reactive data
const showTopUpModal = ref(false)

// Methods
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getTransactionIcon = (transaction) => {
  switch (transaction.purpose) {
    case 'topup':
      return ArrowUpIcon
    case 'order_payment':
      return ShoppingCartIcon
    case 'refund':
      return CircleDollarSignIcon
    default:
      return CircleDollarSignIcon
  }
}

const getTransactionTitle = (transaction) => {
  switch (transaction.purpose) {
    case 'topup':
      return 'Recharge du portefeuille'
    case 'order_payment':
      return 'Paiement de commande'
    case 'refund':
      return 'Remboursement'
    default:
      return 'Transaction'
  }
}

const getTransactionBgColor = (transaction) => {
  if (transaction.status === 'failed') return 'bg-red-100'
  if (transaction.status === 'pending') return 'bg-yellow-100'
  
  switch (transaction.purpose) {
    case 'topup':
    case 'refund':
      return 'bg-green-100'
    case 'order_payment':
      return 'bg-blue-100'
    default:
      return 'bg-gray-100'
  }
}

const getTransactionIconColor = (transaction) => {
  if (transaction.status === 'failed') return 'text-red-600'
  if (transaction.status === 'pending') return 'text-yellow-600'
  
  switch (transaction.purpose) {
    case 'topup':
    case 'refund':
      return 'text-green-600'
    case 'order_payment':
      return 'text-blue-600'
    default:
      return 'text-gray-600'
  }
}

const getAmountColor = (transaction) => {
  if (transaction.status === 'failed') return 'text-red-500'
  if (transaction.status === 'pending') return 'text-yellow-500'
  
  switch (transaction.purpose) {
    case 'topup':
    case 'refund':
      return 'text-green-600'
    case 'order_payment':
      return 'text-red-600'
    default:
      return 'text-gray-600'
  }
}

const getAmountPrefix = (transaction) => {
  switch (transaction.purpose) {
    case 'topup':
    case 'refund':
      return '+'
    case 'order_payment':
      return '-'
    default:
      return ''
  }
}

const getStatusLabel = (status) => {
  switch (status) {
    case 'completed':
      return 'Terminé'
    case 'pending':
      return 'En attente'
    case 'failed':
      return 'Échec'
    case 'cancelled':
      return 'Annulé'
    default:
      return status
  }
}

const getStatusBadgeColor = (status) => {
  switch (status) {
    case 'completed':
      return 'bg-green-100 text-green-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'failed':
      return 'bg-red-100 text-red-800'
    case 'cancelled':
      return 'bg-gray-100 text-gray-800'
    default:
      return 'bg-gray-100 text-gray-800'
  }
}

const refreshTransactions = async () => {
  await walletStore.refreshWallet()
}

const loadMoreTransactions = async () => {
  // Implement pagination logic here
  console.log('Load more transactions')
}

const handleTopUpSuccess = (data) => {
  showTopUpModal.value = false
  // Refresh wallet data
  walletStore.refreshWallet()
}

// Initialize
onMounted(() => {
  walletStore.refreshWallet()
})
</script>