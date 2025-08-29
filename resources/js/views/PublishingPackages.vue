<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Packages de Publication</h1>
        <p class="mt-2 text-gray-600">Gérez vos packages de publication et frais de mise en ligne</p>
      </div>

      <!-- Stats Cards -->
      <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Packages</p>
              <p class="text-2xl font-bold text-gray-900">{{ stats.total_packages }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Actifs</p>
              <p class="text-2xl font-bold text-green-600">{{ stats.active_packages }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Slots Disponibles</p>
              <p class="text-2xl font-bold text-yellow-600">{{ stats.available_slots }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Total Payé</p>
              <p class="text-2xl font-bold text-purple-600">{{ stats.formatted_total_fees }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-between items-center mb-6">
        <div class="flex space-x-4">
          <button
            @click="activeTab = 'active'"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium',
              activeTab === 'active' ? 'bg-green-100 text-green-700' : 'bg-white text-gray-700 hover:bg-gray-50'
            ]"
          >
            Packages Actifs
          </button>
          <button
            @click="activeTab = 'pending'"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium',
              activeTab === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-white text-gray-700 hover:bg-gray-50'
            ]"
          >
            En Attente
          </button>
          <button
            @click="activeTab = 'history'"
            :class="[
              'px-4 py-2 rounded-md text-sm font-medium',
              activeTab === 'history' ? 'bg-gray-100 text-gray-700' : 'bg-white text-gray-700 hover:bg-gray-50'
            ]"
          >
            Historique
          </button>
        </div>

        <button
          @click="showPublishingModal = true"
          class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
        >
          Nouveau Package
        </button>
      </div>

      <!-- Package List -->
      <div class="bg-white shadow rounded-lg">
        <div v-if="loading" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto"></div>
          <p class="mt-2 text-gray-600">Chargement des packages...</p>
        </div>

        <div v-else-if="currentPackages.length === 0" class="p-8 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun package</h3>
          <p class="mt-1 text-sm text-gray-500">
            {{ activeTab === 'active' ? 'Vous n\'avez pas de packages actifs.' : 
               activeTab === 'pending' ? 'Aucun package en attente de paiement.' :
               'Aucun historique de packages.' }}
          </p>
          <div class="mt-6">
            <button
              @click="showPublishingModal = true"
              class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
            >
              Créer un package
            </button>
          </div>
        </div>

        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="packageItem in currentPackages"
            :key="packageItem.package_id"
            class="p-6 hover:bg-gray-50"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-2 mb-2">
                  <h3 class="text-lg font-medium text-gray-900">
                    Package {{ packageItem.package_id }}
                  </h3>
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      getStatusClass(packageItem.status)
                    ]"
                  >
                    {{ getStatusText(packageItem.status) }}
                  </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
                  <div>
                    <span class="font-medium">Produits:</span>
                    <span class="ml-1">{{ packageItem.product_count }}</span>
                  </div>
                  <div>
                    <span class="font-medium">Utilisés:</span>
                    <span class="ml-1">{{ packageItem.used_slots }}/{{ packageItem.product_count }}</span>
                  </div>
                  <div>
                    <span class="font-medium">Frais:</span>
                    <span class="ml-1 font-semibold text-green-600">{{ packageItem.formatted_fee }}</span>
                  </div>
                  <div v-if="packageItem.time_until_expiration">
                    <span class="font-medium">Expire:</span>
                    <span class="ml-1">{{ packageItem.time_until_expiration }}</span>
                  </div>
                </div>

                <!-- Progress Bar for Active Packages -->
                <div v-if="packageItem.status === 'paid' && packageItem.product_count > 0" class="mt-3">
                  <div class="flex justify-between text-xs text-gray-600 mb-1">
                    <span>Utilisation</span>
                    <span>{{ packageItem.usage_percentage }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="h-2 rounded-full transition-all"
                      :class="packageItem.usage_percentage === 100 ? 'bg-gray-400' : 'bg-green-600'"
                      :style="`width: ${packageItem.usage_percentage}%`"
                    ></div>
                  </div>
                </div>
              </div>

              <div class="flex items-center space-x-2 ml-4">
                <!-- Pay Now Button for Pending -->
                <button
                  v-if="packageItem.status === 'pending' && packageItem.payment_link"
                  @click="openPaymentLink(packageItem.payment_link)"
                  class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm"
                >
                  Payer Maintenant
                </button>

                <!-- View Details -->
                <button
                  @click="viewPackageDetails(packageItem)"
                  class="text-gray-400 hover:text-gray-600"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </button>

                <!-- Cancel Button for Pending -->
                <button
                  v-if="packageItem.status === 'pending'"
                  @click="cancelPackage(packageItem.package_id)"
                  class="text-red-400 hover:text-red-600"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Publishing Fee Modal -->
      <PublishingFeeModal
        :isOpen="showPublishingModal"
        @close="showPublishingModal = false"
        @payment-created="onPaymentCreated"
      />

      <!-- Package Details Modal -->
      <div v-if="selectedPackage" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="selectedPackage = null"></div>
        
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
          <div class="flex items-center justify-between p-6 border-b">
            <h2 class="text-xl font-semibold">Détails du Package</h2>
            <button @click="selectedPackage = null" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div class="p-6">
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-600">ID du Package</label>
                  <p class="font-semibold">{{ selectedPackage.package_id }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-600">Statut</label>
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(selectedPackage.status)]">
                    {{ getStatusText(selectedPackage.status) }}
                  </span>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-600">Nombre de Produits</label>
                  <p class="font-semibold">{{ selectedPackage.product_count }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-600">Slots Utilisés</label>
                  <p class="font-semibold">{{ selectedPackage.used_slots }}/{{ selectedPackage.product_count }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-600">Frais Total</label>
                  <p class="font-semibold text-green-600">{{ selectedPackage.formatted_fee }}</p>
                </div>
                <div v-if="selectedPackage.time_until_expiration">
                  <label class="text-sm font-medium text-gray-600">Expire</label>
                  <p class="font-semibold">{{ selectedPackage.time_until_expiration }}</p>
                </div>
              </div>

              <!-- Payment Link for Pending -->
              <div v-if="selectedPackage.status === 'pending' && selectedPackage.payment_link" class="mt-6">
                <button
                  @click="openPaymentLink(selectedPackage.payment_link)"
                  class="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 font-medium"
                >
                  Procéder au Paiement
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../services/api.js'
import PublishingFeeModal from '../components/products/PublishingFeeModal.vue'

export default {
  name: 'PublishingPackages',
  components: {
    PublishingFeeModal
  },
  data() {
    return {
      loading: false,
      activeTab: 'active',
      showPublishingModal: false,
      selectedPackage: null,
      
      // Data
      stats: null,
      activePackages: [],
      pendingPackages: [],
      historyPackages: []
    }
  },
  computed: {
    currentPackages() {
      switch (this.activeTab) {
        case 'active': return this.activePackages
        case 'pending': return this.pendingPackages
        case 'history': return this.historyPackages
        default: return []
      }
    }
  },
  async mounted() {
    await this.loadData()
  },
  methods: {
    async loadData() {
      this.loading = true
      
      try {
        // Load stats and all packages
        const [statsResponse, packagesResponse] = await Promise.all([
          api.get('/publishing/packages?type=stats'),
          api.get('/publishing/packages?type=all')
        ])
        
        if (statsResponse.data.success && packagesResponse.data.success) {
          const packagesData = packagesResponse.data.data
          
          this.stats = packagesData.stats
          this.activePackages = packagesData.active_packages || []
          this.pendingPackages = packagesData.pending_packages || []
        }
        
        // Load history separately
        const historyResponse = await api.get('/publishing/packages?type=history&limit=50')
        if (historyResponse.data.success) {
          this.historyPackages = historyResponse.data.data.packages || []
        }
        
      } catch (error) {
        console.error('Failed to load publishing packages:', error)
      } finally {
        this.loading = false
      }
    },
    
    getStatusClass(status) {
      const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'paid': 'bg-green-100 text-green-800',
        'expired': 'bg-red-100 text-red-800',
        'cancelled': 'bg-gray-100 text-gray-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },
    
    getStatusText(status) {
      const texts = {
        'pending': 'En Attente',
        'paid': 'Payé',
        'expired': 'Expiré',
        'cancelled': 'Annulé'
      }
      return texts[status] || status
    },
    
    viewPackageDetails(packageData) {
      this.selectedPackage = packageData
    },
    
    openPaymentLink(paymentLink) {
      window.open(paymentLink, '_blank')
    },
    
    async cancelPackage(packageId) {
      if (!confirm('Êtes-vous sûr de vouloir annuler ce package ?')) {
        return
      }
      
      try {
        const response = await api.delete(`/publishing/packages/${packageId}`)
        
        if (response.data.success) {
          // Remove from pending packages
          this.pendingPackages = this.pendingPackages.filter(p => p.package_id !== packageId)
          
          // Reload data to update stats
          await this.loadData()
        }
      } catch (error) {
        console.error('Failed to cancel package:', error)
        alert('Erreur lors de l\'annulation du package')
      }
    },
    
    async onPaymentCreated(paymentData) {
      this.showPublishingModal = false
      
      // Add to pending packages
      if (paymentData.package) {
        this.pendingPackages.unshift({
          ...paymentData.package,
          payment_link: paymentData.payment_link
        })
      }
      
      // Reload data to update stats
      await this.loadData()
    }
  }
}
</script>