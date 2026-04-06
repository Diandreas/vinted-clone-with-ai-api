<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <div class="flex justify-between items-center mb-6">
        <div class="flex items-center space-x-3">
          <RouterLink to="/admin/dashboard" class="flex items-center justify-center w-9 h-9 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </RouterLink>
          <h1 class="text-2xl font-bold text-gray-900">Gestion des Utilisateurs</h1>
        </div>
        <button
          @click="showCreateModal = true"
          class="bg-primary-600 text-white px-4 py-2 rounded-lg hover:bg-primary-700"
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
          <select v-model="filters.kyc" class="border border-gray-300 rounded-lg px-3 py-2">
            <option value="">KYC (tous)</option>
            <option value="pending">En attente</option>
            <option value="verified">Vérifié</option>
            <option value="rejected">Rejeté</option>
            <option value="none">Non soumis</option>
          </select>
          <select v-model="filters.admin" class="border border-gray-300 rounded-lg px-3 py-2">
            <option value="">Tous</option>
            <option value="true">Admins</option>
            <option value="false">Non admins</option>
          </select>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedUsers.length > 0" class="bg-primary-50 border border-primary-200 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
          <span class="text-primary-800">{{ selectedUsers.length }} utilisateur(s) sélectionné(s)</span>
          <div class="flex gap-2">
            <select v-model="bulkAction" class="border border-primary-300 rounded px-3 py-1">
              <option value="">Action groupée...</option>
              <option value="verify">Vérifier</option>
              <option value="unverify">Dévérifier</option>
              <option value="kyc_approve">KYC Approuver</option>
              <option value="kyc_reject">KYC Rejeter</option>
              <option value="activate">Activer</option>
              <option value="suspend">Suspendre</option>
              <option value="ban">Bannir</option>
              <option value="delete">Supprimer</option>
            </select>
            <button
              @click="executeBulkAction"
              :disabled="!bulkAction"
              class="bg-primary-600 text-white px-4 py-1 rounded hover:bg-primary-700 disabled:opacity-50"
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
                  KYC
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
                      'bg-gray-100 text-gray-900': user.role === 'admin',
                      'bg-primary-100 text-primary-800': user.role === 'manager',
                      'bg-green-100 text-green-800': user.role === 'analyst',
                      'bg-gray-100 text-gray-800': user.role === 'moderator',
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
                        'bg-gray-100 text-gray-900': !user.is_verified
                      }"
                      class="px-2 py-1 text-xs font-medium rounded-full"
                    >
                      {{ user.is_verified ? 'Vérifié' : 'Non vérifié' }}
                    </span>
                    <span
                      v-if="user.is_admin"
                      class="bg-primary-100 text-primary-800 px-2 py-1 text-xs font-medium rounded-full"
                    >
                      Admin
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <span
                      :class="{
                        'bg-green-100 text-green-800': user.kyc_status === 'verified',
                        'bg-yellow-100 text-yellow-800': user.kyc_status === 'pending',
                        'bg-red-100 text-red-700': user.kyc_status === 'rejected',
                        'bg-gray-100 text-gray-800': !user.kyc_status || user.kyc_status === 'none'
                      }"
                      class="px-2.5 py-1 text-xs font-medium rounded-full"
                    >
                      {{ user.kyc_status || 'none' }}
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
                      class="text-primary-600 hover:text-primary-900"
                    >
                      Modifier
                    </button>
                    <button
                      @click="viewUser(user)"
                      class="text-primary-600 hover:text-primary-900"
                    >
                      Voir
                    </button>
                    <button
                      v-if="user.id !== authStore.user?.id"
                      @click="deleteUser(user)"
                      class="text-gray-700 hover:text-gray-900"
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
              class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm hover:bg-primary-700"
            >
              {{ showEditModal ? 'Modifier' : 'Créer' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View User Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Détail utilisateur</h3>
            <p v-if="viewedUserDetail?.user" class="text-sm text-gray-500">
              {{ viewedUserDetail.user.name }} · @{{ viewedUserDetail.user.username }}
            </p>
          </div>
          <button
            @click="closeViewModal"
            class="text-gray-500 hover:text-gray-700 text-sm"
          >
            Fermer
          </button>
        </div>

        <div v-if="viewLoading" class="p-6 text-sm text-gray-500">
          Chargement des informations utilisateur...
        </div>

        <div v-else-if="viewError" class="p-6">
          <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ viewError }}
          </div>
        </div>

        <div v-else-if="viewedUserDetail?.user" class="overflow-y-auto max-h-[calc(90vh-73px)]">
          <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-4">
              <div class="rounded-xl border border-gray-200 p-5">
                <div class="flex items-center gap-4">
                  <div class="h-14 w-14 rounded-full bg-gray-200 flex items-center justify-center text-lg font-semibold text-gray-700">
                    {{ viewedUserDetail.user.name?.charAt(0)?.toUpperCase() }}
                  </div>
                  <div>
                    <div class="text-lg font-semibold text-gray-900">{{ viewedUserDetail.user.name }}</div>
                    <div class="text-sm text-gray-500">{{ viewedUserDetail.user.email }}</div>
                    <div class="text-xs text-gray-400">@{{ viewedUserDetail.user.username }}</div>
                  </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                  <span class="px-2.5 py-1 text-xs font-medium rounded-full" :class="getRoleClass(viewedUserDetail.user.role)">
                    {{ viewedUserDetail.user.role || 'user' }}
                  </span>
                  <span class="px-2.5 py-1 text-xs font-medium rounded-full" :class="viewedUserDetail.user.is_verified ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-900'">
                    {{ viewedUserDetail.user.is_verified ? 'Vérifié' : 'Non vérifié' }}
                  </span>
                  <span class="px-2.5 py-1 text-xs font-medium rounded-full" :class="getStatusClass(viewedUserDetail.user.status)">
                    {{ viewedUserDetail.user.status || 'active' }}
                  </span>
                  <span v-if="viewedUserDetail.user.is_admin" class="px-2.5 py-1 text-xs font-medium rounded-full bg-primary-100 text-primary-800">
                    Admin
                  </span>
                </div>
              </div>

              <div class="rounded-xl border border-gray-200 p-5">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Informations</h4>
                <dl class="space-y-3 text-sm">
                  <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">ID</dt>
                    <dd class="text-gray-900 font-medium">{{ viewedUserDetail.user.id }}</dd>
                  </div>
                  <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Téléphone</dt>
                    <dd class="text-gray-900 font-medium">{{ viewedUserDetail.user.phone || 'Non renseigné' }}</dd>
                  </div>
                  <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Inscrit le</dt>
                    <dd class="text-gray-900 font-medium">{{ formatDate(viewedUserDetail.user.created_at) }}</dd>
                  </div>
                  <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Dernière mise à jour</dt>
                    <dd class="text-gray-900 font-medium">{{ formatDate(viewedUserDetail.user.updated_at) }}</dd>
                  </div>
                </dl>
              </div>

              <div class="rounded-xl border border-gray-200 p-5">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">KYC</h4>
                <div class="flex items-center gap-2 mb-3">
                  <span class="px-2.5 py-1 text-xs font-medium rounded-full" :class="getKycClass(viewedUserDetail.user.kyc_status)">
                    {{ viewedUserDetail.user.kyc_status || 'none' }}
                  </span>
                </div>
                <p v-if="viewedUserDetail.user.kyc_rejection_reason" class="text-sm text-red-700 mb-3">
                  Motif de rejet : {{ viewedUserDetail.user.kyc_rejection_reason }}
                </p>
                <div class="flex flex-wrap gap-2">
                  <button
                    v-if="viewedUserDetail.user.kyc_document_path"
                    @click="openKycDocument(viewedUserDetail.user)"
                    class="px-3 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50"
                  >
                    Voir document
                  </button>
                  <button
                    v-if="viewedUserDetail.user.kyc_selfie_path"
                    @click="openKycSelfie(viewedUserDetail.user)"
                    class="px-3 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50"
                  >
                    Voir selfie
                  </button>
                  <button
                    v-if="viewedUserDetail.user.kyc_status === 'pending'"
                    @click="approveKyc(viewedUserDetail.user)"
                    class="px-3 py-2 text-sm rounded-lg border border-green-200 bg-green-50 text-green-700 hover:bg-green-100"
                  >
                    Approuver
                  </button>
                  <button
                    v-if="viewedUserDetail.user.kyc_status === 'pending'"
                    @click="rejectKyc(viewedUserDetail.user)"
                    class="px-3 py-2 text-sm rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100"
                  >
                    Rejeter
                  </button>
                </div>
              </div>
            </div>

            <div class="lg:col-span-2 space-y-4">
              <div class="grid grid-cols-2 xl:grid-cols-3 gap-4">
                <div class="rounded-xl border border-gray-200 p-4">
                  <div class="text-xs uppercase tracking-wide text-gray-500">Produits</div>
                  <div class="mt-2 text-2xl font-semibold text-gray-900">{{ viewedUserDetail.stats.total_products }}</div>
                  <div class="text-sm text-gray-500">{{ viewedUserDetail.stats.active_products }} actifs</div>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                  <div class="text-xs uppercase tracking-wide text-gray-500">Commandes</div>
                  <div class="mt-2 text-2xl font-semibold text-gray-900">{{ viewedUserDetail.stats.total_orders }}</div>
                  <div class="text-sm text-gray-500">{{ viewedUserDetail.stats.completed_orders }} finalisées</div>
                </div>
                <div class="rounded-xl border border-gray-200 p-4">
                  <div class="text-xs uppercase tracking-wide text-gray-500">Réseau</div>
                  <div class="mt-2 text-2xl font-semibold text-gray-900">{{ viewedUserDetail.stats.followers_count }}</div>
                  <div class="text-sm text-gray-500">{{ viewedUserDetail.stats.following_count }} suivis</div>
                </div>
              </div>

              <div class="rounded-xl border border-gray-200 p-5">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Permissions</h4>
                <div v-if="normalizedPermissions.length" class="flex flex-wrap gap-2">
                  <span
                    v-for="permission in normalizedPermissions"
                    :key="permission"
                    class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-800 text-xs font-medium"
                  >
                    {{ permission }}
                  </span>
                </div>
                <p v-else class="text-sm text-gray-500">Aucune permission spécifique.</p>
              </div>

              <div class="rounded-xl border border-gray-200 p-5">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Produits récents</h4>
                <div v-if="viewedUserDetail.user.products?.length" class="space-y-3">
                  <div
                    v-for="product in viewedUserDetail.user.products.slice(0, 5)"
                    :key="product.id"
                    class="flex items-center justify-between gap-4 rounded-lg border border-gray-100 px-4 py-3"
                  >
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ product.title || `Produit #${product.id}` }}</div>
                      <div class="text-xs text-gray-500">{{ formatDate(product.created_at) }}</div>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getStatusClass(product.status)">
                      {{ product.status || 'n/a' }}
                    </span>
                  </div>
                </div>
                <p v-else class="text-sm text-gray-500">Aucun produit.</p>
              </div>

              <div class="rounded-xl border border-gray-200 p-5">
                <h4 class="text-sm font-semibold text-gray-900 mb-3">Commandes récentes</h4>
                <div v-if="viewedUserDetail.user.orders?.length" class="space-y-3">
                  <div
                    v-for="order in viewedUserDetail.user.orders.slice(0, 5)"
                    :key="order.id"
                    class="flex items-center justify-between gap-4 rounded-lg border border-gray-100 px-4 py-3"
                  >
                    <div>
                      <div class="text-sm font-medium text-gray-900">Commande #{{ order.id }}</div>
                      <div class="text-xs text-gray-500">{{ formatDate(order.created_at) }}</div>
                    </div>
                    <span class="px-2 py-1 rounded-full text-xs font-medium" :class="getStatusClass(order.status)">
                      {{ order.status || 'n/a' }}
                    </span>
                  </div>
                </div>
                <p v-else class="text-sm text-gray-500">Aucune commande.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="previewModal.open" class="fixed inset-0 z-[60] bg-black/70 p-4 flex items-center justify-center">
      <div class="w-full max-w-5xl max-h-[92vh] bg-white rounded-2xl shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ previewModal.title }}</h3>
            <p class="text-sm text-gray-500">{{ previewModal.mimeType || 'Fichier KYC' }}</p>
          </div>
          <div class="flex items-center gap-3">
            <a
              v-if="previewModal.url"
              :href="previewModal.url"
              :download="previewModal.downloadName"
              class="text-sm px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50"
            >
              Télécharger
            </a>
            <button
              @click="closePreviewModal"
              class="text-sm px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50"
            >
              Fermer
            </button>
          </div>
        </div>

        <div class="bg-gray-950 min-h-[70vh] max-h-[calc(92vh-73px)] overflow-auto flex items-center justify-center">
          <object
            v-if="previewModal.url && (previewModal.kind === 'image' || previewModal.kind === 'pdf')"
            :data="previewModal.url"
            :type="previewModal.mimeType"
            :aria-label="previewModal.title"
            class="w-full h-[80vh] bg-white"
          >
            <div class="text-center px-6">
              <p class="text-lg font-medium text-white">Prévisualisation indisponible</p>
              <p class="text-sm text-gray-300 mt-2">Téléchargez le fichier pour le consulter.</p>
            </div>
          </object>
          <div v-else class="text-center text-white px-6">
            <p class="text-lg font-medium">Prévisualisation indisponible</p>
            <p class="text-sm text-gray-300 mt-2">Téléchargez le fichier pour le consulter.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useRealtime } from '@/composables/useRealtime'

const authStore = useAuthStore()
const { subscribeToRealtime } = useRealtime()

const users = ref([])
const selectedUsers = ref([])
const loading = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const editingUser = ref(null)
const viewedUserDetail = ref(null)
const viewLoading = ref(false)
const viewError = ref('')
const previewModal = ref({
  open: false,
  title: '',
  url: '',
  mimeType: '',
  kind: '',
  downloadName: '',
})
const bulkAction = ref('')

const blobToDataUrl = (blob) => new Promise((resolve, reject) => {
  const reader = new FileReader()
  reader.onload = () => resolve(reader.result)
  reader.onerror = () => reject(new Error('Impossible de lire le fichier'))
  reader.readAsDataURL(blob)
})

const trimLeadingBinaryNoise = (bytes, mimeType) => {
  const signatures = {
    'image/jpeg': [0xff, 0xd8, 0xff],
    'image/png': [0x89, 0x50, 0x4e, 0x47],
    'application/pdf': [0x25, 0x50, 0x44, 0x46],
  }

  const signature = signatures[mimeType]
  if (!signature) return bytes

  const startIndex = bytes.findIndex((_, index) =>
    signature.every((byte, offset) => bytes[index + offset] === byte)
  )

  if (startIndex <= 0) return bytes
  return bytes.slice(startIndex)
}

const filters = ref({
  search: '',
  role: '',
  status: '',
  admin: '',
  kyc: ''
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
  { value: 'orders:view', label: 'Voir les commandes' },
  { value: 'analytics:view', label: 'Voir les analytics' }
]

const normalizedPermissions = computed(() => {
  const permissions = viewedUserDetail.value?.user?.permissions
  if (!permissions) return []
  if (Array.isArray(permissions)) return permissions
  if (typeof permissions === 'object') {
    return Object.entries(permissions)
      .filter(([, enabled]) => !!enabled)
      .map(([permission]) => permission)
  }
  return []
})

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

const openBlobPreview = async (requestUrl, fallbackName, title) => {
  try {
    const res = await window.axios.get(requestUrl, { responseType: 'arraybuffer' })
    const mimeType = res.headers['content-type'] || 'application/octet-stream'
    const bytes = trimLeadingBinaryNoise(new Uint8Array(res.data), mimeType)
    const blob = new Blob([bytes], { type: mimeType })
    const previewUrl = mimeType.startsWith('image/')
      ? await blobToDataUrl(blob)
      : window.URL.createObjectURL(blob)
    closePreviewModal()
    previewModal.value = {
      open: true,
      title,
      url: previewUrl,
      mimeType,
      kind: mimeType.startsWith('image/') ? 'image' : (mimeType === 'application/pdf' ? 'pdf' : 'file'),
      downloadName: fallbackName,
    }
  } catch (error) {
    alert('Impossible d’ouvrir ce fichier.')
    console.error('Erreur ouverture fichier KYC:', error)
  }
}

const openKycDocument = async (user) => {
  try {
    await openBlobPreview(`/admin/users/${user.id}/kyc/document`, 'document-kyc', 'Document KYC')
  } catch (error) {
    console.error('Erreur ouverture document KYC:', error)
  }
}

const openKycSelfie = async (user) => {
  try {
    await openBlobPreview(`/admin/users/${user.id}/kyc/selfie`, 'selfie-kyc', 'Selfie KYC')
  } catch (error) {
    console.error('Erreur ouverture selfie KYC:', error)
  }
}

const closePreviewModal = () => {
  if (previewModal.value.url && previewModal.value.url.startsWith('blob:')) {
    window.URL.revokeObjectURL(previewModal.value.url)
  }

  previewModal.value = {
    open: false,
    title: '',
    url: '',
    mimeType: '',
    kind: '',
    downloadName: '',
  }
}

const refreshViewedUser = async (userId) => {
  if (!showViewModal.value || !userId) return

  try {
    const response = await window.axios.get(`/admin/users/${userId}`)
    viewedUserDetail.value = response.data.data
  } catch (error) {
    console.error('Erreur rafraîchissement détail utilisateur:', error)
  }
}

const approveKyc = async (user) => {
  try {
    await window.axios.put(`/admin/users/${user.id}/kyc/approve`)
    await loadUsers(pagination.value.current_page)
    await refreshViewedUser(user.id)
  } catch (error) {
    console.error('Erreur approbation KYC:', error)
  }
}

const rejectKyc = async (user) => {
  const reason = prompt('Raison du rejet (optionnel) ?') || ''
  try {
    await window.axios.put(`/admin/users/${user.id}/kyc/reject`, { reason })
    await loadUsers(pagination.value.current_page)
    await refreshViewedUser(user.id)
  } catch (error) {
    console.error('Erreur rejet KYC:', error)
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

const viewUser = async (user) => {
  showViewModal.value = true
  viewLoading.value = true
  viewError.value = ''

  try {
    const response = await window.axios.get(`/admin/users/${user.id}`)
    viewedUserDetail.value = response.data.data
  } catch (error) {
    console.error('Erreur lors du chargement du détail utilisateur:', error)
    viewError.value = 'Impossible de charger les informations de cet utilisateur.'
    viewedUserDetail.value = null
  } finally {
    viewLoading.value = false
  }
}

const closeViewModal = () => {
  showViewModal.value = false
  viewedUserDetail.value = null
  viewError.value = ''
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

const formatDate = (value) => {
  if (!value) return 'N/A'
  return new Date(value).toLocaleString('fr-FR')
}

const getRoleClass = (role) => ({
  'bg-gray-100 text-gray-900': role === 'admin',
  'bg-primary-100 text-primary-800': role === 'manager',
  'bg-green-100 text-green-800': role === 'analyst',
  'bg-gray-100 text-gray-800': role === 'moderator' || role === 'user' || !role
})

const getStatusClass = (status) => {
  if (status === 'completed' || status === 'active') return 'bg-green-100 text-green-800'
  if (status === 'pending') return 'bg-yellow-100 text-yellow-800'
  if (status === 'suspended') return 'bg-orange-100 text-orange-800'
  if (status === 'banned' || status === 'rejected') return 'bg-red-100 text-red-700'
  return 'bg-gray-100 text-gray-800'
}

const getKycClass = (status) => {
  if (status === 'verified') return 'bg-green-100 text-green-800'
  if (status === 'pending') return 'bg-yellow-100 text-yellow-800'
  if (status === 'rejected') return 'bg-red-100 text-red-700'
  return 'bg-gray-100 text-gray-800'
}

const refreshAdminKycState = async () => {
  await loadUsers(pagination.value.current_page)

  if (showViewModal.value && viewedUserDetail.value?.user?.id) {
    await refreshViewedUser(viewedUserDetail.value.user.id)
  }
}

onMounted(() => {
  loadUsers()
  subscribeToRealtime('notifications', refreshAdminKycState, 3000)
})
</script>
