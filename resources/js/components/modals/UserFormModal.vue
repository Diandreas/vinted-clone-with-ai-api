<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
              <form @submit.prevent="handleSubmit">
                <div>
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <UserIcon class="h-6 w-6 text-green-600" />
                  </div>
                  <div class="mt-3 text-center sm:mt-5">
                    <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                      Modifier l'utilisateur
                    </DialogTitle>
                  </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                  <!-- Nom -->
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                      Nom complet *
                    </label>
                    <div class="mt-1">
                      <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Nom complet"
                      />
                    </div>
                  </div>

                  <!-- Nom d'utilisateur -->
                  <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">
                      Nom d'utilisateur *
                    </label>
                    <div class="mt-1">
                      <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="nom_utilisateur"
                      />
                    </div>
                  </div>

                  <!-- Email -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                      Adresse email *
                    </label>
                    <div class="mt-1">
                      <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="email@example.com"
                      />
                    </div>
                  </div>

                  <!-- Téléphone -->
                  <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">
                      Téléphone
                    </label>
                    <div class="mt-1">
                      <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="+33 1 23 45 67 89"
                      />
                    </div>
                  </div>

                  <!-- Date de naissance -->
                  <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">
                      Date de naissance
                    </label>
                    <div class="mt-1">
                      <input
                        id="date_of_birth"
                        v-model="form.date_of_birth"
                        type="date"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      />
                    </div>
                  </div>

                  <!-- Genre -->
                  <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">
                      Genre
                    </label>
                    <div class="mt-1">
                      <select
                        id="gender"
                        v-model="form.gender"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      >
                        <option value="">Non spécifié</option>
                        <option value="male">Homme</option>
                        <option value="female">Femme</option>
                        <option value="other">Autre</option>
                      </select>
                    </div>
                  </div>

                  <!-- Bio -->
                  <div class="sm:col-span-2">
                    <label for="bio" class="block text-sm font-medium text-gray-700">
                      Biographie
                    </label>
                    <div class="mt-1">
                      <textarea
                        id="bio"
                        v-model="form.bio"
                        rows="3"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Biographie de l'utilisateur..."
                      ></textarea>
                    </div>
                  </div>

                  <!-- Adresse -->
                  <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">
                      Adresse
                    </label>
                    <div class="mt-1">
                      <input
                        id="address"
                        v-model="form.address"
                        type="text"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Adresse complète"
                      />
                    </div>
                  </div>

                  <!-- Options -->
                  <div class="sm:col-span-2">
                    <div class="space-y-4">
                      <div class="flex items-center">
                        <input
                          id="email_verified"
                          v-model="form.email_verified"
                          type="checkbox"
                          class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                        />
                        <label for="email_verified" class="ml-2 block text-sm text-gray-900">
                          Email vérifié
                        </label>
                      </div>

                      <div class="flex items-center">
                        <input
                          id="is_active"
                          v-model="form.is_active"
                          type="checkbox"
                          class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                        />
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                          Compte actif
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="error" class="mt-4 bg-red-50 border border-red-200 rounded-md p-4">
                  <div class="text-sm text-red-700">
                    {{ error }}
                  </div>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                  <button
                    type="submit"
                    :disabled="loading"
                    class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed sm:col-start-2 sm:text-sm"
                  >
                    <LoaderIcon v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    {{ loading ? 'Enregistrement...' : 'Modifier' }}
                  </button>
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm"
                  >
                    Annuler
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { UserIcon, LoaderIcon } from 'lucide-vue-next'

const props = defineProps({
  show: Boolean,
  user: Object
})

const emit = defineEmits(['close', 'saved'])

// State
const loading = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  username: '',
  email: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  bio: '',
  address: '',
  email_verified: false,
  is_active: true
})

// Methods
const resetForm = () => {
  Object.keys(form).forEach(key => {
    if (typeof form[key] === 'boolean') {
      form[key] = key === 'is_active' // is_active par défaut à true
    } else {
      form[key] = ''
    }
  })
  error.value = ''
}

const populateForm = () => {
  if (props.user) {
    form.name = props.user.name || ''
    form.username = props.user.username || ''
    form.email = props.user.email || ''
    form.phone = props.user.phone || ''
    form.date_of_birth = props.user.date_of_birth || ''
    form.gender = props.user.gender || ''
    form.bio = props.user.bio || ''
    form.address = props.user.address || ''
    form.email_verified = !!props.user.email_verified_at
    form.is_active = props.user.is_active !== false
  }
}

const handleSubmit = async () => {
  if (loading.value) return
  
  loading.value = true
  error.value = ''
  
  try {
    const data = { ...form }
    
    // Convertir les chaînes vides en null pour les champs optionnels
    Object.keys(data).forEach(key => {
      if (data[key] === '' && ['phone', 'date_of_birth', 'gender', 'bio', 'address'].includes(key)) {
        data[key] = null
      }
    })
    
    const response = await window.axios.put(`/users/${props.user.id}`, data)
    
    emit('saved', response.data)
    resetForm()
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join('\n')
    } else {
      error.value = err.response?.data?.message || 'Une erreur est survenue'
    }
  } finally {
    loading.value = false
  }
}

// Watchers
watch(() => props.show, (newValue) => {
  if (newValue) {
    if (props.user) {
      populateForm()
    } else {
      resetForm()
    }
  }
})

watch(() => props.user, (newUser) => {
  if (newUser && props.show) {
    populateForm()
  }
})
</script>




  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:p-6">
              <form @submit.prevent="handleSubmit">
                <div>
                  <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                    <UserIcon class="h-6 w-6 text-green-600" />
                  </div>
                  <div class="mt-3 text-center sm:mt-5">
                    <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                      Modifier l'utilisateur
                    </DialogTitle>
                  </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                  <!-- Nom -->
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                      Nom complet *
                    </label>
                    <div class="mt-1">
                      <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Nom complet"
                      />
                    </div>
                  </div>

                  <!-- Nom d'utilisateur -->
                  <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">
                      Nom d'utilisateur *
                    </label>
                    <div class="mt-1">
                      <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="nom_utilisateur"
                      />
                    </div>
                  </div>

                  <!-- Email -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                      Adresse email *
                    </label>
                    <div class="mt-1">
                      <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="email@example.com"
                      />
                    </div>
                  </div>

                  <!-- Téléphone -->
                  <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">
                      Téléphone
                    </label>
                    <div class="mt-1">
                      <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="+33 1 23 45 67 89"
                      />
                    </div>
                  </div>

                  <!-- Date de naissance -->
                  <div>
                    <label for="date_of_birth" class="block text-sm font-medium text-gray-700">
                      Date de naissance
                    </label>
                    <div class="mt-1">
                      <input
                        id="date_of_birth"
                        v-model="form.date_of_birth"
                        type="date"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      />
                    </div>
                  </div>

                  <!-- Genre -->
                  <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">
                      Genre
                    </label>
                    <div class="mt-1">
                      <select
                        id="gender"
                        v-model="form.gender"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                      >
                        <option value="">Non spécifié</option>
                        <option value="male">Homme</option>
                        <option value="female">Femme</option>
                        <option value="other">Autre</option>
                      </select>
                    </div>
                  </div>

                  <!-- Bio -->
                  <div class="sm:col-span-2">
                    <label for="bio" class="block text-sm font-medium text-gray-700">
                      Biographie
                    </label>
                    <div class="mt-1">
                      <textarea
                        id="bio"
                        v-model="form.bio"
                        rows="3"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Biographie de l'utilisateur..."
                      ></textarea>
                    </div>
                  </div>

                  <!-- Adresse -->
                  <div class="sm:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700">
                      Adresse
                    </label>
                    <div class="mt-1">
                      <input
                        id="address"
                        v-model="form.address"
                        type="text"
                        class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                        placeholder="Adresse complète"
                      />
                    </div>
                  </div>

                  <!-- Options -->
                  <div class="sm:col-span-2">
                    <div class="space-y-4">
                      <div class="flex items-center">
                        <input
                          id="email_verified"
                          v-model="form.email_verified"
                          type="checkbox"
                          class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                        />
                        <label for="email_verified" class="ml-2 block text-sm text-gray-900">
                          Email vérifié
                        </label>
                      </div>

                      <div class="flex items-center">
                        <input
                          id="is_active"
                          v-model="form.is_active"
                          type="checkbox"
                          class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                        />
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">
                          Compte actif
                        </label>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="error" class="mt-4 bg-red-50 border border-red-200 rounded-md p-4">
                  <div class="text-sm text-red-700">
                    {{ error }}
                  </div>
                </div>

                <div class="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                  <button
                    type="submit"
                    :disabled="loading"
                    class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed sm:col-start-2 sm:text-sm"
                  >
                    <LoaderIcon v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
                    {{ loading ? 'Enregistrement...' : 'Modifier' }}
                  </button>
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:col-start-1 sm:mt-0 sm:text-sm"
                  >
                    Annuler
                  </button>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { UserIcon, LoaderIcon } from 'lucide-vue-next'

const props = defineProps({
  show: Boolean,
  user: Object
})

const emit = defineEmits(['close', 'saved'])

// State
const loading = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  username: '',
  email: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  bio: '',
  address: '',
  email_verified: false,
  is_active: true
})

// Methods
const resetForm = () => {
  Object.keys(form).forEach(key => {
    if (typeof form[key] === 'boolean') {
      form[key] = key === 'is_active' // is_active par défaut à true
    } else {
      form[key] = ''
    }
  })
  error.value = ''
}

const populateForm = () => {
  if (props.user) {
    form.name = props.user.name || ''
    form.username = props.user.username || ''
    form.email = props.user.email || ''
    form.phone = props.user.phone || ''
    form.date_of_birth = props.user.date_of_birth || ''
    form.gender = props.user.gender || ''
    form.bio = props.user.bio || ''
    form.address = props.user.address || ''
    form.email_verified = !!props.user.email_verified_at
    form.is_active = props.user.is_active !== false
  }
}

const handleSubmit = async () => {
  if (loading.value) return
  
  loading.value = true
  error.value = ''
  
  try {
    const data = { ...form }
    
    // Convertir les chaînes vides en null pour les champs optionnels
    Object.keys(data).forEach(key => {
      if (data[key] === '' && ['phone', 'date_of_birth', 'gender', 'bio', 'address'].includes(key)) {
        data[key] = null
      }
    })
    
    const response = await window.axios.put(`/users/${props.user.id}`, data)
    
    emit('saved', response.data)
    resetForm()
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join('\n')
    } else {
      error.value = err.response?.data?.message || 'Une erreur est survenue'
    }
  } finally {
    loading.value = false
  }
}

// Watchers
watch(() => props.show, (newValue) => {
  if (newValue) {
    if (props.user) {
      populateForm()
    } else {
      resetForm()
    }
  }
})

watch(() => props.user, (newUser) => {
  if (newUser && props.show) {
    populateForm()
  }
})
</script>



