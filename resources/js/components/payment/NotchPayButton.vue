<template>
  <button
    @click="handlePayment"
    :disabled="loading"
    :class="[
      'w-full py-3 px-4 rounded-lg font-medium text-white bg-gradient-to-r from-purple-600 to-purple-800 hover:from-purple-700 hover:to-purple-900 transition-all duration-200 flex items-center justify-center gap-2',
      loading ? 'opacity-75 cursor-not-allowed' : ''
    ]"
  >
    <Loader2 v-if="loading" class="w-5 h-5 animate-spin" />
    <Smartphone v-else class="w-5 h-5" />
    <span>{{ loading ? 'Traitement...' : 'Payer avec Mobile Money' }}</span>
  </button>
</template>

<script setup>
import { ref } from 'vue'
import { Loader2, Smartphone } from 'lucide-vue-next'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  product: {
    type: Object,
    required: true
  },
  amount: {
    type: Number,
    required: true
  },
  onSuccess: {
    type: Function,
    default: () => {}
  }
})

const emit = defineEmits(['success', 'error'])

const loading = ref(false)
const { toast } = useToast()

const handlePayment = async () => {
  try {
    loading.value = true
    
    // Get CSRF token from cookies
    const csrfToken = decodeURIComponent(
      document.cookie
        .split('; ')
        .find(row => row.startsWith('XSRF-TOKEN='))
        ?.split('=')[1] || ''
    )

    const response = await fetch('/api/v1/notchpay/initialize', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify({
        product_id: props.product.id,
        amount: props.amount,
        email: window?.App?.user?.email || 'user@example.com'
      })
    })

    if (!response.ok) {
      const errorData = await response.json()
      throw new Error(errorData.message || 'Erreur lors de l\'initialisation du paiement')
    }

    const result = await response.json()

    if (result.success && result.authorization_url) {
      // Redirect to NotchPay payment page
      window.location.href = result.authorization_url
    } else {
      throw new Error('Réponse invalide du serveur de paiement')
    }

  } catch (error) {
    console.error('Erreur de paiement:', error)
    
    toast({
      variant: "destructive",
      title: "Erreur de paiement",
      description: error.message || "Une erreur s'est produite lors du traitement du paiement"
    })

    emit('error', error)
  } finally {
    loading.value = false
  }
}
</script>
