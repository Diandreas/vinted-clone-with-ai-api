<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Paiement</h1>
        <p class="mt-2 text-gray-600">Gestion de votre paiement</p>
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
        <div v-if="messages.error" class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-900">{{ messages.error }}</p>
            </div>
          </div>
        </div>

        <!-- Warning Message -->
        <div v-if="messages.warning" class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-gray-800">{{ messages.warning }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Redirection automatique vers wallet -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
        <div class="mb-4">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Redirection vers votre portefeuille</h3>
        <p class="text-gray-600 mb-4">
          Vous allez être redirigé vers votre page portefeuille dans quelques secondes...
        </p>
        <div class="flex justify-center space-x-4">
          <button
            @click="goToWallet"
            class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700 transition-colors"
          >
            Aller au portefeuille maintenant
          </button>
          <button
            @click="goToPayment"
            class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors"
          >
            Retour au paiement
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// Props from the controller
const props = defineProps({
  auth: {
    type: Object,
    default: () => ({})
  },
  messages: {
    type: Object,
    default: () => ({})
  }
})

// Auto-redirect to wallet after 3 seconds
onMounted(() => {
  setTimeout(() => {
    goToWallet()
  }, 3000)
})

const goToWallet = () => {
  router.push('/wallet')
}

const goToPayment = () => {
  router.push('/payment')
}
</script>
