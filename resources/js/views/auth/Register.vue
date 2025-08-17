<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-6 sm:py-12 px-3 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6 sm:space-y-8">
      <div>
        <div class="mx-auto h-10 w-10 sm:h-12 sm:w-12 bg-indigo-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-lg sm:text-xl">V</span>
        </div>
        <h2 class="mt-4 sm:mt-6 text-center text-2xl sm:text-3xl font-extrabold text-gray-900">
          Créez votre compte
        </h2>
        <p class="mt-2 text-center text-xs sm:text-sm text-gray-600">
          Ou
          <RouterLink to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
            connectez-vous à votre compte existant
          </RouterLink>
        </p>
      </div>
      
      <form class="mt-6 sm:mt-8 space-y-4 sm:space-y-6" @submit.prevent="handleSubmit">
        <div class="space-y-3 sm:space-y-4">
          <div>
            <label for="name" class="block text-xs sm:text-sm font-medium text-gray-700">Nom complet</label>
            <input
              id="name"
              v-model="form.name"
              name="name"
              type="text"
              autocomplete="name"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 sm:py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
              placeholder="Votre nom complet"
            />
          </div>
          
          <div>
            <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700">Adresse email</label>
            <input
              id="email"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 sm:py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
              placeholder="votre@email.com"
            />
          </div>
          
          <div>
            <label for="username" class="block text-xs sm:text-sm font-medium text-gray-700">Nom d'utilisateur</label>
            <input
              id="username"
              v-model="form.username"
              name="username"
              type="text"
              autocomplete="username"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 sm:py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
              placeholder="nom_utilisateur"
            />
          </div>
          
          <div>
            <label for="password" class="block text-xs sm:text-sm font-medium text-gray-700">Mot de passe</label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="new-password"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 sm:py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
              placeholder="Mot de passe"
            />
          </div>
          
          <div>
            <label for="password_confirmation" class="block text-xs sm:text-sm font-medium text-gray-700">Confirmer le mot de passe</label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              class="mt-1 appearance-none relative block w-full px-3 py-2 sm:py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-sm sm:text-base"
              placeholder="Confirmer le mot de passe"
            />
          </div>
        </div>

        <div class="flex items-start space-x-3">
          <input
            id="terms"
            v-model="form.terms"
            name="terms"
            type="checkbox"
            required
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded mt-0.5 flex-shrink-0"
          />
          <label for="terms" class="text-xs sm:text-sm text-gray-900 leading-relaxed">
            J'accepte les
            <a href="/terms" class="text-indigo-600 hover:text-indigo-500">conditions d'utilisation</a>
            et la
            <a href="/privacy" class="text-indigo-600 hover:text-indigo-500">politique de confidentialité</a>
          </label>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 sm:py-3 px-4 border border-transparent text-sm sm:text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <LoaderIcon class="h-5 w-5 text-indigo-500 animate-spin" />
            </span>
            {{ loading ? 'Création du compte...' : 'Créer mon compte' }}
          </button>
        </div>

        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-3 sm:p-4">
          <div class="text-xs sm:text-sm text-red-700">
            {{ error }}
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { LoaderIcon } from 'lucide-vue-next'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// State
const loading = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  email: '',
  username: '',
  password: '',
  password_confirmation: '',
  terms: false
})

// Methods
const handleSubmit = async () => {
  if (loading.value) return
  
  // Validation
  if (form.password !== form.password_confirmation) {
    error.value = 'Les mots de passe ne correspondent pas'
    return
  }
  
  if (!form.terms) {
    error.value = 'Vous devez accepter les conditions d\'utilisation'
    return
  }
  
  loading.value = true
  error.value = ''
  
  try {
    await authStore.register({
      name: form.name,
      email: form.email,
      username: form.username,
      password: form.password,
      password_confirmation: form.password_confirmation
    })
    
    notificationStore.success('Compte créé avec succès ! Vérifiez votre email.')
  } catch (err) {
    error.value = err.response?.data?.message || 'Une erreur est survenue lors de la création du compte'
  } finally {
    loading.value = false
  }
}
</script>



