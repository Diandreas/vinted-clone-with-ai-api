<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Recharger le portefeuille</h3>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <XIcon class="w-6 h-6" />
        </button>
      </div>

      <form @submit.prevent="submitTopUp">
        <!-- Amount -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Montant à recharger
          </label>
          <div class="relative">
            <input
              v-model="form.amount"
              type="number"
              min="100"
              max="1000000"
              step="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent pr-16"
              placeholder="Entrez le montant"
              required
            />
            <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">
              FCFA
            </span>
          </div>
          <p class="text-xs text-gray-500 mt-1">
            Minimum: 100 FCFA - Maximum: 1,000,000 FCFA
          </p>
        </div>

        <!-- Quick Amount Buttons -->
        <div class="mb-4">
          <p class="text-sm font-medium text-gray-700 mb-2">Montants rapides</p>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="amount in quickAmounts"
              :key="amount"
              type="button"
              @click="form.amount = amount"
              class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              :class="{ 'bg-indigo-50 border-indigo-300 text-indigo-700': form.amount == amount }"
            >
              {{ formatAmount(amount) }}
            </button>
          </div>
        </div>


        <!-- Message (Optional) -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">
            Message (optionnel)
          </label>
          <textarea
            v-model="form.message"
            rows="2"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            placeholder="Note pour cette transaction..."
            maxlength="255"
          ></textarea>
        </div>

        <!-- Payment Info -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
          <div class="flex items-start space-x-3">
            <InfoIcon class="w-5 h-5 text-purple-600 mt-0.5" />
            <div class="text-sm text-purple-800">
              <p class="font-medium mb-1">Paiement sécurisé avec NotchPay</p>
              <p>Vous serez redirigé vers la plateforme de paiement sécurisée. Les méthodes acceptées :</p>
              <ul class="list-disc list-inside mt-1 space-y-1">
                <li>Orange Money</li>
                <li>MTN Mobile Money</li>
                <li>Carte bancaire</li>
                <li>Visa & Mastercard</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Error Display -->
        <div v-if="error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
          <p class="text-sm text-red-800">{{ error }}</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-3">
          <button
            type="button"
            @click="$emit('close')"
            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
          >
            Annuler
          </button>
          <button
            type="submit"
            :disabled="loading || !form.amount || form.amount < 100"
            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="!loading">Recharger {{ formatAmount(form.amount || 0) }}</span>
            <span v-else class="flex items-center justify-center">
              <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
              Traitement...
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useWalletStore } from '@/stores/wallet'
import { XIcon, InfoIcon } from 'lucide-vue-next'

// Emits
const emit = defineEmits(['close', 'success'])

// Store
const walletStore = useWalletStore()

// Reactive data
const loading = ref(false)
const error = ref('')

const form = reactive({
  amount: null,
  message: ''
})

const quickAmounts = [1000, 5000, 10000, 25000, 50000, 100000]

// Methods
const formatAmount = (amount) => {
  if (!amount) return '0 FCFA'
  return `${amount.toLocaleString()} FCFA`
}

const submitTopUp = async () => {
  if (!form.amount || form.amount < 100) {
    error.value = 'Le montant minimum est de 100 FCFA'
    return
  }

  loading.value = true
  error.value = ''

  try {
    const result = await walletStore.topUp(
      form.amount,
      form.message || null
    )

    if (result.success) {
      // Redirect to NotchPay payment page
      if (result.data.authorization_url) {
        window.location.href = result.data.authorization_url
      } else {
        emit('success', result.data)
      }
    } else {
      error.value = result.message || 'Erreur lors de l\'initialisation de la recharge'
    }
  } catch (err) {
    console.error('Top up error:', err)
    error.value = err.response?.data?.message || 'Erreur technique lors de la recharge'
  } finally {
    loading.value = false
  }
}
</script>