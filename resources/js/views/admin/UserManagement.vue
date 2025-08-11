<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Gestion des Utilisateurs</h1>
        <button
          @click="showCreateModal = true"
          class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700"
        >
          Créer un utilisateur
        </button>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Rechercher..."
            class="border border-gray-300 rounded-lg px-3 py-2"
          />
          <select v-model="filters.role" class="border border-gray-300 rounded-lg px-3 py-2">
            <option value="">Tous les rôles</option>
            <option value="user">Utilisateur</option>
            <option value="admin">Admin</option>
            <option value="manager">Manager</option>
            <option value="analyst">Analyste</option>
            <option value="moderator">Modérateur</option>
          </select>
          <select v-model="filters.status" class="border border-gray-300 rounded-lg px-3 py-2">
            <option value="">Tous les statuts</option>
            <option value="verified">Vérifiés</option>
            <option value="unverified">Non vérifiés</option>
          </select>
          <select v-model="filters.admin" class="border border-gray-300 rounded-lg px-3 py-2">
            <option value="">Tous</option>
            <option value="true">Admins</option>
            <option value="false">Non admins</option>
          </select>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedUsers.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
          <span class="text-blue-800">{{ selectedUsers.length }} utilisateur(s) sélectionné(s)</span>
          <div class="flex gap-2">
            <select v-model="bulkAction" class="border border-blue-300 rounded px-3 py-1">
              <option value="">Action groupée...</option>
              <option value="verify">Vérifier</option>
              <option value="unverify">Dévérifier</option>
              <option value="activate">Activer</option>
              <option value="suspend">Suspendre</option>
              <option value="ban">Bannir</option>
              <option value="delete">Supprimer</option>
            </select>
            <button
              @click="executeBulkAction"
              :disabled="!bulkAction"
              class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 disabled:opacity-50"
            >
              Appliquer
            </button>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <input
                    type="checkbox"
                    :checked="selectedUsers.length === users.length && users.length > 0"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300"
                  />
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Utilisateur
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Rôle
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Statut
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Produits
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Commandes
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :value="user.id"
                    v-model="selectedUsers"
                    class="rounded border-gray-300"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                      <span class="text-sm font-medium text-gray-700">
                        {{ user.name?.charAt(0)?.toUpperCase() }}
                      </span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                      <div class="text-xs text-gray-400">@{{ user.username }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="{
                      'bg-red-100 text-red-800': user.role === 'admin',
                      'bg-blue-100 text-blue-800': user.role === 'manager',
                      'bg-green-100 text-green-800': user.role === 'analyst',
                      'bg-yellow-100 text-yellow-800': user.role === 'moderator',
                      'bg-gray-100 text-gray-800': user.role === 'user'
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ user.role }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-col gap-1">
                    <span
                      :class="{
                        'bg-green-100 text-green-800': user.is_verified,
                        'bg-red-100 text-red-800': !user.is_verified
                      }"
                      class="px-2 py-1 text-xs font-medium rounded-full"
                    >
                      {{ user.is_verified ? 'Vérifié' : 'Non vérifié' }}
                    </span>
                    <span
                      v-if="user.is_admin"
                      class="bg-purple-100 text-purple-800 px-2 py-1 text-xs font-medium rounded-full"
                    >
                      Admin
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.products_count }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ user.orders_count }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex gap-2">
                    <button
                      @click="editUser(user)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Modifier
                    </button>
                    <button
                      @click="viewUser(user)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Voir
                    </button>
                    <button
                      v-if="user.id !== authStore.user?.id"
                      @click="deleteUser(user)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Supprimer
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Affichage de {{ pagination.from }} à {{ pagination.to }} sur {{ pagination.total }} résultats
        </div>
        <div class="flex gap-2">
          <button
            @click="loadUsers(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-2 border border-gray-300 rounded-md text-sm disabled:opacity-50"
          >
            Précédent
          </button>
          <button
            @click="loadUsers(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-2 border border-gray-300 rounded-md text-sm disabled:opacity-50"
          >
            Suivant
          </button>
        </div>
      </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ showEditModal ? 'Modifier l\'utilisateur' : 'Créer un utilisateur' }}
        </h3>
        
        <form @submit.prevent="submitUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nom</label>
            <input
              v-model="userForm.name"
              type="text"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input
              v-model="userForm.email"
              type="email"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Nom d'utilisateur</label>
            <input
              v-model="userForm.username"
              type="text"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
            />
          </div>
          
          <div v-if="!showEditModal">
            <label class="block text-sm font-medium text-gray-700">Mot de passe</label>
            <input
              v-model="userForm.password"
              type="password"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Rôle</label>
            <select
              v-model="userForm.role"
              required
              class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2"
            >
              <option value="user">Utilisateur</option>
              <option value="admin">Admin</option>
              <option value="manager">Manager</option>
              <option value="analyst">Analyste</option>
              <option value="moderator">Modérateur</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700">Permissions</label>
            <div class="mt-2 space-y-2">
              <label v-for="permission in availablePermissions" :key="permission.value" class="flex items-center">
                <input
                  type="checkbox"
                  :value="permission.value"
                  v-model="userForm.permissions"
                  class="rounded border-gray-300 mr-2"
                />
                <span class="text-sm text-gray-700">{{ permission.label }}</span>
              </label>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <label class="flex items-center">
              <input
                v-model="userForm.is_verified"
                type="checkbox"
                class="rounded border-gray-300 mr-2"
              />
              <span class="text-sm text-gray-700">Vérifié</span>
            </label>
            <label class="flex items-center">
              <input
                v-model="userForm.is_admin"
                type="checkbox"
                class="rounded border-gray-300 mr-2"
              />
              <span class="text-sm text-gray-700">Admin</span>
            </label>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700"
            >
              {{ showEditModal ? 'Modifier' : 'Créer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const users = ref([])
const selectedUsers = ref([])
const loading = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const editingUser = ref(null)
const bulkAction = ref('')

const filters = ref({
  search: '',
  role: '',
  status: '',
  admin: ''
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 15,
  total: 0,
  from: 0,
  to: 0
})

const userForm = ref({
  name: '',
  email: '',
  username: '',
  password: '',
  role: 'user',
  permissions: [],
  is_verified: true,
  is_admin: false
})

const availablePermissions = [
  { value: 'dashboard:view', label: 'Voir le dashboard' },
  { value: 'users:manage', label: 'Gérer les utilisateurs' },
  { value: 'products:moderate', label: 'Modérer les produits' },
  { value: 'lives:moderate', label: 'Modérer les lives' },
  { value: 'orders:view', label: 'Voir les commandes' },
  { value: 'analytics:view', label: 'Voir les analytics' }
]

const loadUsers = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: pagination.value.per_page.toString(),
      ...Object.fromEntries(
        Object.entries(filters.value).filter(([key, value]) => value !== '')
      )
    })
    
    const response = await window.axios.get(`/admin/users?${params}`)
    users.value = response.data.data
    Object.assign(pagination.value, response.data.meta)
  } catch (error) {
    console.error('Erreur lors du chargement des utilisateurs:', error)
  } finally {
    loading.value = false
  }
}

const editUser = (user) => {
  editingUser.value = user
  userForm.value = {
    name: user.name,
    email: user.email,
    username: user.username,
    password: '',
    role: user.role || 'user',
    permissions: user.permissions || [],
    is_verified: user.is_verified,
    is_admin: user.is_admin
  }
  showEditModal.value = true
}

const viewUser = (user) => {
  // TODO: Implement user detail view
  console.log('View user:', user)
}

const deleteUser = async (user) => {
  if (!confirm(`Êtes-vous sûr de vouloir supprimer ${user.name} ?`)) return
  
  try {
    await window.axios.delete(`/admin/users/${user.id}`)
    await loadUsers(pagination.value.current_page)
  } catch (error) {
    console.error('Erreur lors de la suppression:', error)
  }
}

const submitUser = async () => {
  try {
    if (showEditModal.value) {
      await window.axios.put(`/admin/users/${editingUser.value.id}`, userForm.value)
    } else {
      await window.axios.post('/admin/users', userForm.value)
    }
    
    closeModal()
    await loadUsers(pagination.value.current_page)
  } catch (error) {
    console.error('Erreur lors de la soumission:', error)
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingUser.value = null
  userForm.value = {
    name: '',
    email: '',
    username: '',
    password: '',
    role: 'user',
    permissions: [],
    is_verified: true,
    is_admin: false
  }
}

const toggleSelectAll = () => {
  if (selectedUsers.value.length === users.value.length) {
    selectedUsers.value = []
  } else {
    selectedUsers.value = users.value.map(user => user.id)
  }
}

const executeBulkAction = async () => {
  if (!bulkAction.value || selectedUsers.value.length === 0) return
  
  try {
    await window.axios.post('/admin/users/bulk-update', {
      user_ids: selectedUsers.value,
      action: bulkAction.value
    })
    
    selectedUsers.value = []
    bulkAction.value = ''
    await loadUsers(pagination.value.current_page)
  } catch (error) {
    console.error('Erreur lors de l\'action groupée:', error)
  }
}

onMounted(() => {
  loadUsers()
})
</script>
