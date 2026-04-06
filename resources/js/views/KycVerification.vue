<template>
  <div class="min-h-screen bg-gray-100">
    <div class="max-w-2xl mx-auto px-4 py-5 space-y-4">
      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h1 class="text-lg font-bold text-gray-900">Vérification vendeur</h1>
        <p class="text-sm text-gray-500 mt-1">
          Pour publier des produits, vous devez vérifier votre identité (CNI ou passeport).
        </p>
      </div>

      <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">Statut</h2>
        <div class="flex items-center justify-between">
          <div>
            <p class="font-semibold text-gray-900">{{ statusLabel }}</p>
            <p v-if="kyc.rejection_reason" class="text-sm text-red-500 mt-1">
              {{ kyc.rejection_reason }}
            </p>
          </div>
          <span
            :class="statusBadgeClass"
            class="text-xs font-bold px-3 py-1 rounded-full"
          >
            {{ kyc.status || 'none' }}
          </span>
        </div>
      </div>

      <form @submit.prevent="submitKyc" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 space-y-4">
        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1.5">Type de document</label>
          <select v-model="form.document_type" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm">
            <option value="cni">CNI</option>
            <option value="passport">Passeport</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1.5">Document (photo ou PDF)</label>
          <input type="file" accept="image/*,application/pdf" @change="onDocumentChange" class="block w-full text-sm" />
        </div>

        <div>
          <label class="block text-xs font-semibold text-gray-600 mb-1.5">Selfie (optionnel)</label>
          <input type="file" accept="image/*" capture="user" @change="onSelfieChange" class="block w-full text-sm" />
        </div>

        <div class="flex gap-2">
          <button
            type="submit"
            :disabled="submitting"
            class="flex-1 px-4 py-2.5 bg-primary-600 text-white rounded-xl font-semibold text-sm disabled:opacity-60"
          >
            {{ submitting ? 'Envoi...' : 'Soumettre' }}
          </button>
          <RouterLink
            to="/profile"
            class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-semibold text-sm text-center"
          >
            Retour
          </RouterLink>
        </div>

        <p v-if="error" class="text-sm text-red-500">{{ error }}</p>
        <p v-if="message" class="text-sm text-green-600">{{ message }}</p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '@/services/api'
import { useAuthStore } from '@/stores/auth'
import { useRealtime } from '@/composables/useRealtime'

const submitting = ref(false)
const error = ref('')
const message = ref('')
const authStore = useAuthStore()
const { subscribeToRealtime } = useRealtime()

const kyc = ref({
  status: 'none',
  document_type: null,
  verified_at: null,
  rejection_reason: null
})

const form = ref({
  document_type: 'cni',
  document: null,
  selfie: null
})

const statusLabel = computed(() => {
  switch (kyc.value.status) {
    case 'verified': return 'Compte vérifié'
    case 'pending': return 'En attente de vérification'
    case 'rejected': return 'Rejeté'
    default: return 'Non vérifié'
  }
})

const statusBadgeClass = computed(() => {
  switch (kyc.value.status) {
    case 'verified': return 'bg-green-100 text-green-700'
    case 'pending': return 'bg-yellow-100 text-yellow-700'
    case 'rejected': return 'bg-red-100 text-red-600'
    default: return 'bg-gray-100 text-gray-600'
  }
})

const fetchStatus = async () => {
  try {
    const res = await api.get('/users/kyc')
    kyc.value = res.data?.data || kyc.value
    const me = await api.get('/auth/user')
    if (me.data?.user && authStore.user) {
      Object.assign(authStore.user, me.data.user)
    }
  } catch {
    // ignore
  }
}

const onDocumentChange = (e) => {
  form.value.document = e.target.files[0] || null
}

const onSelfieChange = (e) => {
  form.value.selfie = e.target.files[0] || null
}

const submitKyc = async () => {
  error.value = ''
  message.value = ''
  if (!form.value.document) {
    error.value = 'Veuillez ajouter un document.'
    return
  }
  submitting.value = true
  try {
    const payload = new FormData()
    payload.append('document_type', form.value.document_type)
    payload.append('document', form.value.document)
    if (form.value.selfie) payload.append('selfie', form.value.selfie)
    const res = await api.post('/users/kyc', payload, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    message.value = res.data?.message || 'Documents envoyés.'
    await fetchStatus()
  } catch (err) {
    error.value = err.response?.data?.message || 'Erreur lors de l’envoi.'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  fetchStatus()
  subscribeToRealtime('notifications', fetchStatus, 3000)
})
</script>
