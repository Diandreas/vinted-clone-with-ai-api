<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-6 sm:py-12 px-3 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6 sm:space-y-8">
      <div>
        <h2 class="mt-4 sm:mt-6 text-center text-2xl sm:text-3xl font-extrabold text-gray-900">
          Réinitialiser le mot de passe
        </h2>
        <p class="mt-2 text-center text-xs sm:text-sm text-gray-600">
          Choisissez un nouveau mot de passe pour votre compte
        </p>
      </div>

      <form class="mt-6 sm:mt-8 space-y-4 sm:space-y-6" @submit.prevent="submitForm">
        <div v-if="message" class="rounded-md bg-green-50 p-3 sm:p-4">
          <div class="flex">
            <div class="ml-3">
              <h3 class="text-xs sm:text-sm font-medium text-green-800">
                {{ message }}
              </h3>
            </div>
          </div>
        </div>

        <div v-if="error" class="rounded-md bg-gray-50 p-3 sm:p-4">
          <div class="flex">
            <div class="ml-3">
              <h3 class="text-xs sm:text-sm font-medium text-gray-900">
                {{ error }}
              </h3>
            </div>
          </div>
        </div>

        <div class="rounded-md shadow-sm space-y-3">
          <div>
            <label for="email" class="sr-only">Email</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none rounded-md relative block w-full px-3 py-2 sm:py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 text-sm sm:text-base"
              placeholder="Adresse email"
            />
          </div>
          <div>
            <label for="password" class="sr-only">Nouveau mot de passe</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="new-password"
              required
              class="appearance-none rounded-md relative block w-full px-3 py-2 sm:py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 text-sm sm:text-base"
              placeholder="Nouveau mot de passe"
            />
          </div>
          <div>
            <label for="password_confirmation" class="sr-only">Confirmer le mot de passe</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              class="appearance-none rounded-md relative block w-full px-3 py-2 sm:py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 text-sm sm:text-base"
              placeholder="Confirmer le mot de passe"
            />
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 sm:py-3 px-4 border border-transparent text-sm sm:text-base font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 transition-colors"
          >
            <span v-if="loading">Mise à jour...</span>
            <span v-else>Mettre à jour</span>
          </button>
        </div>

        <div class="text-center">
          <RouterLink
            to="/login"
            class="font-medium text-primary-600 hover:text-primary-500 text-xs sm:text-sm"
          >
            Retour à la connexion
          </RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()

const form = ref({
  email: '',
  password: '',
  password_confirmation: '',
  token: ''
})

const loading = ref(false)
const message = ref('')
const error = ref('')

onMounted(() => {
  form.value.token = route.query.token || ''
  form.value.email = route.query.email || ''
  if (!form.value.token || !form.value.email) {
    error.value = 'Lien de réinitialisation invalide ou expiré.'
  }
})

const submitForm = async () => {
  if (!form.value.token) {
    error.value = 'Lien de réinitialisation invalide ou expiré.'
    return
  }

  loading.value = true
  error.value = ''
  message.value = ''

  try {
    await api.post('/auth/reset-password', form.value)
    message.value = 'Mot de passe mis à jour. Vous pouvez vous connecter.'
    form.value.password = ''
    form.value.password_confirmation = ''
    setTimeout(() => router.push('/login'), 1500)
  } catch (err) {
    error.value = err.response?.data?.message || 'Une erreur est survenue'
  } finally {
    loading.value = false
  }
}
</script>
