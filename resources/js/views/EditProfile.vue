<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-black text-white">
    <!-- Header -->
    <div class="bg-gray-800/50 backdrop-blur-sm border-b border-gray-700">
      <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 py-4 sm:py-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-4">
            <button
              @click="router.back()"
              class="p-2 bg-gray-700/50 rounded-full hover:bg-gray-600/50 transition-colors"
            >
              <ArrowLeftIcon class="w-5 h-5" />
            </button>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold">Modifier mon profil</h1>
          </div>
          <button
            @click="saveProfile"
            :disabled="saving"
            class="px-4 sm:px-6 py-2 sm:py-3 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-600 rounded-full transition-colors flex items-center space-x-2"
          >
            <Loader2Icon v-if="saving" class="w-4 h-4 animate-spin" />
            <CheckIcon v-else class="w-4 h-4" />
            <span>{{ saving ? 'Sauvegarde...' : 'Sauvegarder' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-3 sm:px-4 md:px-6 py-6 sm:py-8">
      <!-- Success/Error Messages -->
      <div v-if="message" class="mb-6">
        <div class="bg-green-500/20 border border-green-500/50 rounded-xl p-4 text-green-400">
          {{ message }}
        </div>
      </div>
      
      <div v-if="error" class="mb-6">
        <div class="bg-red-500/20 border border-red-500/50 rounded-xl p-4 text-red-400">
          {{ error }}
        </div>
      </div>

      <form @submit.prevent="saveProfile" class="space-y-6 sm:space-y-8">
        <!-- Profile Picture Section -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-gray-700">
          <h2 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-6 flex items-center">
            <UserIcon class="w-5 h-5 mr-3" />
            Photo de profil
          </h2>
          
          <div class="flex flex-col sm:flex-row items-center sm:items-start space-y-4 sm:space-y-0 sm:space-x-6">
            <!-- Current Avatar -->
            <div class="relative">
              <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl sm:text-3xl md:text-4xl font-bold border-4 border-white shadow-lg overflow-hidden">
                <img
                  v-if="form.avatar"
                  :src="form.avatar"
                  :alt="form.name"
                  class="w-full h-full object-cover"
                />
                <span v-else>{{ form.name?.charAt(0)?.toUpperCase() || 'U' }}</span>
              </div>
              <div class="absolute -bottom-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                <div class="w-2 h-2 sm:w-2.5 sm:h-2.5 bg-white rounded-full"></div>
              </div>
            </div>
            
            <!-- Upload Controls -->
            <div class="flex-1 space-y-3">
              <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
                <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
                  <CameraIcon class="w-4 h-4 mr-2" />
                  Changer la photo
                  <input
                    ref="avatarInput"
                    type="file"
                    accept="image/*"
                    @change="handleAvatarChange"
                    class="hidden"
                  />
                </label>
                <button
                  v-if="form.avatar"
                  @click="removeAvatar"
                  type="button"
                  class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors"
                >
                  Supprimer
                </button>
              </div>
              <p class="text-sm text-gray-400">
                Formats acceptés : JPG, PNG, GIF. Taille max : 5MB
              </p>
            </div>
          </div>
        </div>

        <!-- Cover Image Section -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-gray-700">
          <h2 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-6 flex items-center">
            <ImageIcon class="w-5 h-5 mr-3" />
            Image de couverture
          </h2>
          
          <div class="space-y-4">
            <!-- Current Cover -->
            <div v-if="form.cover_image" class="relative">
              <img
                :src="form.cover_image"
                :alt="`Couverture de ${form.name}`"
                class="w-full h-32 sm:h-40 md:h-48 object-cover rounded-xl"
              />
              <button
                @click="removeCoverImage"
                type="button"
                class="absolute top-2 right-2 p-2 bg-red-600 hover:bg-red-700 rounded-full transition-colors"
              >
                <XIcon class="w-4 h-4" />
              </button>
            </div>
            
            <!-- Upload Cover -->
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
              <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors">
                <ImageIcon class="w-4 h-4 mr-2" />
                {{ form.cover_image ? 'Changer la couverture' : 'Ajouter une couverture' }}
                <input
                  ref="coverInput"
                  type="file"
                  accept="image/*"
                  @change="handleCoverChange"
                  class="hidden"
                />
              </label>
            </div>
            <p class="text-sm text-gray-400">
              Recommandé : 1200x400px. Formats : JPG, PNG. Taille max : 5MB
            </p>
          </div>
        </div>

        <!-- Basic Information -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-gray-700">
          <h2 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-6 flex items-center">
            <UserIcon class="w-5 h-5 mr-3" />
            Informations de base
          </h2>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                Nom complet *
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                placeholder="Votre nom complet"
              />
            </div>
            
            <div>
              <label for="username" class="block text-sm font-medium text-gray-300 mb-2">
                Nom d'utilisateur *
              </label>
              <input
                id="username"
                v-model="form.username"
                type="text"
                required
                class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                placeholder="nom_utilisateur"
              />
            </div>
            
            <div>
              <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                Email *
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                placeholder="votre@email.com"
              />
            </div>
            
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">
                Téléphone
              </label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                placeholder="+33 6 12 34 56 78"
              />
            </div>
          </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-gray-700">
          <h2 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-6 flex items-center">
            <InfoIcon class="w-5 h-5 mr-3" />
            Informations supplémentaires
          </h2>
          
          <div class="space-y-4 sm:space-y-6">
            <div>
              <label for="bio" class="block text-sm font-medium text-gray-300 mb-2">
                Bio
              </label>
              <textarea
                id="bio"
                v-model="form.bio"
                rows="4"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400 resize-none"
                placeholder="Parlez-nous un peu de vous..."
              ></textarea>
              <p class="text-xs text-gray-400 mt-1">
                {{ form.bio?.length || 0 }}/500 caractères
              </p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="location" class="block text-sm font-medium text-gray-300 mb-2">
                  Localisation
                </label>
                <input
                  id="location"
                  v-model="form.location"
                  type="text"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                  placeholder="Paris, France"
                />
              </div>
              
              <div>
                <label for="website" class="block text-sm font-medium text-gray-300 mb-2">
                  Site web
                </label>
                <input
                  id="website"
                  v-model="form.website"
                  type="url"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                  placeholder="https://monsite.com"
                />
              </div>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <div>
                <label for="birth_date" class="block text-sm font-medium text-gray-300 mb-2">
                  Date de naissance
                </label>
                <input
                  id="birth_date"
                  v-model="form.birth_date"
                  type="date"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                />
              </div>
              
              <div>
                <label for="gender" class="block text-sm font-medium text-gray-300 mb-2">
                  Genre
                </label>
                <select
                  id="gender"
                  v-model="form.gender"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white"
                >
                  <option value="">Non spécifié</option>
                  <option value="male">Homme</option>
                  <option value="female">Femme</option>
                  <option value="other">Autre</option>
                  <option value="prefer_not_to_say">Préfère ne pas dire</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Social Links -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-gray-700">
          <h2 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-6 flex items-center">
            <Share2Icon class="w-5 h-5 mr-3" />
            Réseaux sociaux
          </h2>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div>
              <label for="instagram" class="block text-sm font-medium text-gray-300 mb-2">
                Instagram
              </label>
              <input
                id="instagram"
                v-model="form.instagram"
                type="text"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                placeholder="@votre_username"
              />
            </div>
            
            <div>
              <label for="twitter" class="block text-sm font-medium text-gray-300 mb-2">
                Twitter/X
              </label>
              <input
                id="twitter"
                v-model="form.twitter"
                type="text"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                placeholder="@votre_username"
              />
            </div>
            
            <div>
              <label for="facebook" class="block text-sm font-medium text-gray-300 mb-2">
                Facebook
              </label>
              <input
                id="facebook"
                v-model="form.facebook"
                type="text"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                placeholder="votre.nom"
              />
            </div>
            
            <div>
              <label for="linkedin" class="block text-sm font-medium text-gray-300 mb-2">
                LinkedIn
              </label>
              <input
                id="linkedin"
                v-model="form.linkedin"
                type="text"
                class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-white placeholder-gray-400"
                placeholder="votre-nom"
              />
            </div>
          </div>
        </div>

        <!-- Privacy Settings -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-gray-700">
          <h2 class="text-lg sm:text-xl font-semibold mb-4 sm:mb-6 flex items-center">
            <ShieldIcon class="w-5 h-5 mr-3" />
            Paramètres de confidentialité
          </h2>
          
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-medium text-white">Profil public</h3>
                <p class="text-sm text-gray-400">Permettre à tous de voir votre profil</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  v-model="form.is_public"
                  type="checkbox"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
              </label>
            </div>
            
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-medium text-white">Recevoir des messages</h3>
                <p class="text-sm text-gray-400">Permettre aux autres de vous contacter</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  v-model="form.can_receive_messages"
                  type="checkbox"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
              </label>
            </div>
            
            <div class="flex items-center justify-between">
              <div>
                <h3 class="font-medium text-white">Notifications push</h3>
                <p class="text-sm text-gray-400">Recevoir des notifications sur votre appareil</p>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input
                  v-model="form.push_notifications"
                  type="checkbox"
                  class="sr-only peer"
                />
                <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
              </label>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
          <button
            type="submit"
            :disabled="saving"
            class="flex-1 sm:flex-none px-6 sm:px-8 py-3 sm:py-4 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-600 rounded-full transition-colors font-medium flex items-center justify-center space-x-2"
          >
            <Loader2Icon v-if="saving" class="w-5 h-5 animate-spin" />
            <CheckIcon v-else class="w-5 h-5" />
            <span>{{ saving ? 'Sauvegarde...' : 'Sauvegarder les modifications' }}</span>
          </button>
          
          <button
            type="button"
            @click="router.back()"
            class="flex-1 sm:flex-none px-6 sm:px-8 py-3 sm:py-4 bg-gray-600 hover:bg-gray-700 rounded-full transition-colors font-medium"
          >
            Annuler
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
  avatar: authStore.user?.avatar || '',
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
    const response = await window.axios.get('/user/profile')
    console.log('API Response:', response.data) // Debug log
    
    // Vérifier la structure de la réponse et utiliser les données de l'auth store si nécessaire
    let userData = null
    
    if (response.data && response.data.data) {
      userData = response.data.data
    } else if (response.data && response.data.user) {
      userData = response.data.user
    } else if (response.data) {
      userData = response.data
    } else {
      // Fallback: utiliser les données de l'auth store
      userData = authStore.user || {}
    }
    
    console.log('User data to populate:', userData) // Debug log
    
    // Populate form with user data, avec vérification de sécurité
    Object.keys(form).forEach(key => {
      if (userData && userData[key] !== undefined && userData[key] !== null) {
        form[key] = userData[key]
      }
    })
    
    // Remplir avec les données de l'auth store si l'API n'a pas certaines données
    if (authStore.user) {
      if (!form.name && authStore.user.name) form.name = authStore.user.name
      if (!form.username && authStore.user.username) form.username = authStore.user.username
      if (!form.email && authStore.user.email) form.email = authStore.user.email
      if (!form.avatar && authStore.user.avatar) form.avatar = authStore.user.avatar
      if (!form.cover_image && authStore.user.cover_image) form.cover_image = authStore.user.cover_image
      if (!form.bio && authStore.user.bio) form.bio = authStore.user.bio
      if (!form.location && authStore.user.location) form.location = authStore.user.location
      if (!form.website && authStore.user.website) form.website = authStore.user.website
    }
    
  } catch (err) {
    console.error('Failed to load user data:', err)
    console.log('Auth store user data:', authStore.user) // Debug log
    
    // Fallback: utiliser les données de l'auth store
    if (authStore.user) {
      Object.keys(form).forEach(key => {
        if (authStore.user[key] !== undefined && authStore.user[key] !== null) {
          form[key] = authStore.user[key]
        }
      })
    }
    
    error.value = 'Impossible de charger vos données de profil. Utilisation des données locales.'
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
    
    console.log('Sending data to API:', dataToSend) // Debug log
    
    const response = await window.axios.put('/user/profile', dataToSend)
    console.log('API Update Response:', response.data) // Debug log
    
    // Vérifier la structure de la réponse
    let updatedUserData = null
    if (response.data && response.data.data) {
      updatedUserData = response.data.data
    } else if (response.data && response.data.user) {
      updatedUserData = response.data.user
    } else if (response.data) {
      updatedUserData = response.data
    }
    
    // Update auth store with new user data
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
    console.error('Failed to update profile:', err)
    console.log('Error response:', err.response?.data) // Debug log
    
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

<style scoped>
/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #1f2937;
}

::-webkit-scrollbar-thumb {
  background: #4b5563;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #6b7280;
}
</style>
