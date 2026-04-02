<template>
  <div class="min-h-screen bg-gray-100">

    <!-- Header -->
    <div class="bg-white border-b border-gray-100 sticky top-0 z-10">
      <div class="flex items-center justify-between px-4 py-3 max-w-2xl mx-auto">
        <button @click="router.back()" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
          <ArrowLeftIcon class="w-5 h-5 text-gray-700" />
        </button>
        <h1 class="text-base font-bold text-gray-900">Modifier le profil</h1>
        <button
          @click="saveProfile"
          :disabled="saving"
          class="flex items-center gap-1.5 px-4 py-2 bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white text-sm font-bold rounded-full transition-colors"
        >
          <Loader2Icon v-if="saving" class="w-4 h-4 animate-spin" />
          <CheckIcon v-else class="w-4 h-4" />
          {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
        </button>
      </div>
    </div>

    <div class="max-w-2xl mx-auto px-4 py-5 space-y-4 pb-20">

      <!-- Messages -->
      <div v-if="message" class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 rounded-2xl px-4 py-3 text-sm font-medium">
        <CheckIcon class="w-4 h-4 flex-shrink-0" /> {{ message }}
      </div>
      <div v-if="error" class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-600 rounded-2xl px-4 py-3 text-sm font-medium">
        {{ error }}
      </div>

      <form @submit.prevent="saveProfile" class="space-y-4">

        <!-- Photo de profil -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Photo de profil</h2>
          <div class="flex items-center gap-5">
            <div class="relative flex-shrink-0">
              <div class="w-20 h-20 rounded-full bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center text-white text-2xl font-black shadow-md overflow-hidden">
                <img v-if="form.avatar" :src="form.avatar" :alt="form.name" class="w-full h-full object-cover" />
                <span v-else>{{ form.name?.charAt(0)?.toUpperCase() || 'U' }}</span>
              </div>
              <div class="absolute bottom-0.5 right-0.5 w-5 h-5 bg-green-500 rounded-full border-2 border-white"></div>
            </div>
            <div class="space-y-2">
              <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-green-50 text-green-700 border border-green-200 text-sm font-semibold rounded-full hover:bg-green-100 transition-colors">
                <CameraIcon class="w-4 h-4" /> Changer la photo
                <input ref="avatarInput" type="file" accept="image/*" @change="handleAvatarChange" class="hidden" />
              </label>
              <button v-if="form.avatar" @click="removeAvatar" type="button"
                class="block text-xs text-red-500 hover:text-red-600 font-medium transition-colors">
                Supprimer la photo
              </button>
              <p class="text-xs text-gray-400">JPG, PNG, GIF — max 5 Mo</p>
            </div>
          </div>
        </div>

        <!-- Image de couverture -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Image de couverture</h2>
          <div v-if="form.cover_image" class="relative mb-3">
            <img :src="form.cover_image" class="w-full h-32 object-cover rounded-xl" />
            <button @click="removeCoverImage" type="button"
              class="absolute top-2 right-2 w-7 h-7 bg-black/50 hover:bg-black/70 rounded-full flex items-center justify-center transition-colors">
              <XIcon class="w-4 h-4 text-white" />
            </button>
          </div>
          <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 border border-gray-200 text-sm font-semibold rounded-full hover:bg-gray-100 transition-colors">
            <ImageIcon class="w-4 h-4" />
            {{ form.cover_image ? 'Changer la couverture' : 'Ajouter une couverture' }}
            <input ref="coverInput" type="file" accept="image/*" @change="handleCoverChange" class="hidden" />
          </label>
          <p class="text-xs text-gray-400 mt-2">Recommandé : 1200×400px — max 5 Mo</p>
        </div>

        <!-- Informations de base -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Informations</h2>
          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom complet *</label>
                <input v-model="form.name" type="text" required placeholder="Votre nom complet"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Nom d'utilisateur *</label>
                <input v-model="form.username" type="text" required placeholder="nom_utilisateur"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email *</label>
                <input v-model="form.email" type="email" required placeholder="votre@email.com"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Téléphone</label>
                <input v-model="form.phone" type="tel" placeholder="+237 6XX XXX XXX"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Localisation</label>
                <input v-model="form.location" type="text" placeholder="Douala, Cameroun"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Site web</label>
                <input v-model="form.website" type="url" placeholder="https://monsite.com"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Date de naissance</label>
                <input v-model="form.birth_date" type="date"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
              </div>
              <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Genre</label>
                <select v-model="form.gender"
                  class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                  <option value="">Non spécifié</option>
                  <option value="male">Homme</option>
                  <option value="female">Femme</option>
                  <option value="other">Autre</option>
                  <option value="prefer_not_to_say">Préfère ne pas dire</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-600 mb-1.5">Bio</label>
              <textarea v-model="form.bio" rows="3" placeholder="Parlez-nous de vous..."
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent resize-none"></textarea>
              <p class="text-xs text-gray-400 mt-1 text-right">{{ form.bio?.length || 0 }}/500</p>
            </div>
          </div>
        </div>

        <!-- Réseaux sociaux -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Réseaux sociaux</h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div v-for="social in [
              { key: 'instagram', label: 'Instagram', placeholder: '@votre_username' },
              { key: 'twitter',   label: 'Twitter/X',  placeholder: '@votre_username' },
              { key: 'facebook',  label: 'Facebook',   placeholder: 'votre.nom' },
              { key: 'linkedin',  label: 'LinkedIn',   placeholder: 'votre-nom' },
            ]" :key="social.key">
              <label class="block text-xs font-semibold text-gray-600 mb-1.5">{{ social.label }}</label>
              <input v-model="form[social.key]" type="text" :placeholder="social.placeholder"
                class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent" />
            </div>
          </div>
        </div>

        <!-- Confidentialité -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
          <h2 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-4">Confidentialité</h2>
          <div class="space-y-4">
            <div v-for="toggle in [
              { key: 'is_public',            title: 'Profil public',          desc: 'Permettre à tous de voir votre profil' },
              { key: 'can_receive_messages', title: 'Recevoir des messages',  desc: 'Permettre aux autres de vous contacter' },
              { key: 'push_notifications',   title: 'Notifications push',     desc: 'Recevoir des notifications sur votre appareil' },
            ]" :key="toggle.key" class="flex items-center justify-between">
              <div>
                <p class="text-sm font-semibold text-gray-900">{{ toggle.title }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ toggle.desc }}</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer ml-4 flex-shrink-0">
                <input v-model="form[toggle.key]" type="checkbox" class="sr-only peer" />
                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:bg-green-500 peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all after:shadow-sm"></div>
              </label>
            </div>
          </div>
        </div>

        <!-- Boutons -->
        <div class="flex gap-3">
          <button type="button" @click="router.back()"
            class="flex-1 py-3 border-2 border-gray-200 text-gray-700 font-bold rounded-2xl hover:bg-gray-50 transition-colors text-sm">
            Annuler
          </button>
          <button type="submit" :disabled="saving"
            class="flex-1 flex items-center justify-center gap-2 py-3 bg-green-500 hover:bg-green-600 disabled:bg-green-300 text-white font-bold rounded-2xl transition-colors text-sm shadow-md shadow-green-200/50">
            <Loader2Icon v-if="saving" class="w-4 h-4 animate-spin" />
            <CheckIcon v-else class="w-4 h-4" />
            {{ saving ? 'Enregistrement...' : 'Sauvegarder' }}
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import api from '@/services/api'
import {
  ArrowLeftIcon,
  UserIcon,
  CameraIcon,
  ImageIcon,
  InfoIcon,
  Share2Icon,
  ShieldIcon,
  CheckIcon,
  XIcon,
  Loader2Icon
} from 'lucide-vue-next'

const router = useRouter()
const authStore = useAuthStore()
const notificationStore = useNotificationStore()

// Refs
const avatarInput = ref(null)
const coverInput = ref(null)

// State
const saving = ref(false)
const message = ref('')
const error = ref('')

// Form data
const form = reactive({
  name: authStore.user?.name || '',
  username: authStore.user?.username || '',
  email: authStore.user?.email || '',
  phone: authStore.user?.phone || '',
  bio: authStore.user?.bio || '',
  location: authStore.user?.location || '',
  website: authStore.user?.website || '',
  birth_date: authStore.user?.birth_date || '',
  gender: authStore.user?.gender || '',
  avatar: authStore.user?.avatar_url || authStore.user?.avatar || '',
  cover_image: authStore.user?.cover_image || '',
  instagram: authStore.user?.instagram || '',
  twitter: authStore.user?.twitter || '',
  facebook: authStore.user?.facebook || '',
  linkedin: authStore.user?.linkedin || '',
  is_public: authStore.user?.is_public !== undefined ? authStore.user.is_public : true,
  can_receive_messages: authStore.user?.can_receive_messages !== undefined ? authStore.user.can_receive_messages : true,
  push_notifications: authStore.user?.push_notifications !== undefined ? authStore.user.push_notifications : true
})

// Methods
const loadUserData = async () => {
  try {
    // Récupère les données fraîches incluant email et phone (rendus visibles côté API)
    const response = await api.get('/auth/user')
    const userData = response.data?.user || {}

    // Met à jour le store avec les données complètes
    if (authStore.user) Object.assign(authStore.user, userData)

    // Pré-remplit le formulaire
    Object.keys(form).forEach(key => {
      if (userData[key] !== undefined && userData[key] !== null) {
        form[key] = userData[key]
      }
    })
    if (userData.avatar_url) {
      form.avatar = userData.avatar_url
    }
  } catch (err) {
    error.value = 'Impossible de charger vos données depuis le serveur.'
  }
}

const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 5 * 1024 * 1024) { // 5MB
      error.value = 'L\'image est trop volumineuse. Taille max : 5MB'
      return
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
      form.avatar = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const handleCoverChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 5 * 1024 * 1024) { // 5MB
      error.value = 'L\'image est trop volumineuse. Taille max : 5MB'
      return
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
      form.cover_image = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removeAvatar = () => {
  form.avatar = ''
  if (avatarInput.value) {
    avatarInput.value.value = ''
  }
}

const removeCoverImage = () => {
  form.cover_image = ''
  if (coverInput.value) {
    coverInput.value.value = ''
  }
}

const saveProfile = async () => {
  saving.value = true
  error.value = ''
  message.value = ''
  
  try {
    // Préparer les données à envoyer (exclure les propriétés non nécessaires)
    const dataToSend = {
      name: form.name,
      username: form.username,
      email: form.email,
      phone: form.phone,
      bio: form.bio,
      location: form.location,
      website: form.website,
      birth_date: form.birth_date,
      gender: form.gender,
      avatar: form.avatar,
      cover_image: form.cover_image,
      instagram: form.instagram,
      twitter: form.twitter,
      facebook: form.facebook,
      linkedin: form.linkedin,
      is_public: form.is_public,
      can_receive_messages: form.can_receive_messages,
      push_notifications: form.push_notifications
    }
    
    const response = await api.put('/auth/update-profile', dataToSend)
    const updatedUserData = response.data?.user || response.data?.data || null

    // Met à jour le store avec les nouvelles données
    if (authStore.user && updatedUserData) {
      Object.assign(authStore.user, updatedUserData)
    }
    
    message.value = 'Profil mis à jour avec succès !'
    notificationStore.success('Profil mis à jour avec succès !')
    
    // Redirect back after a short delay
    setTimeout(() => {
      router.back()
    }, 1500)
    
  } catch (err) {
    if (err.response?.status === 422) {
      // Validation errors
      const validationErrors = err.response.data.errors
      if (validationErrors) {
        const errorMessages = Object.values(validationErrors).flat()
        error.value = `Erreurs de validation : ${errorMessages.join(', ')}`
      } else {
        error.value = 'Données invalides. Vérifiez vos informations.'
      }
    } else if (err.response?.status === 401) {
      error.value = 'Session expirée. Veuillez vous reconnecter.'
    } else if (err.response?.status === 403) {
      error.value = 'Vous n\'avez pas les permissions pour modifier ce profil.'
    } else {
      error.value = err.response?.data?.message || 'Erreur lors de la mise à jour du profil'
    }
  } finally {
    saving.value = false
  }
}

// Lifecycle
onMounted(() => {
  // Vérifier que l'utilisateur est connecté
  if (!authStore.isAuthenticated) {
    error.value = 'Vous devez être connecté pour modifier votre profil'
    return
  }
  
  // Vérifier que les données utilisateur sont disponibles
  if (!authStore.user) {
    error.value = 'Données utilisateur non disponibles'
    return
  }
  
  loadUserData()
})
</script>
