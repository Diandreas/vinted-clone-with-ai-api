<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeModal"></div>
    
    <!-- Modal -->
    <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h2 class="text-xl font-semibold text-gray-900">
          {{ mode === 'single' ? 'Frais de Publication' : 'Package de Publication' }}
        </h2>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6">
        <!-- Mode Selection -->
        <div class="mb-6">
          <div class="flex space-x-4 bg-gray-100 rounded-lg p-1">
            <button 
              @click="mode = 'single'"
              :class="[
                'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors',
                mode === 'single' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              Produit Unique
            </button>
            <button 
              @click="mode = 'package'"
              :class="[
                'flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors',
                mode === 'package' ? 'bg-white text-green-600 shadow-sm' : 'text-gray-600 hover:text-gray-900'
              ]"
            >
              Package Multiple
            </button>
          </div>
        </div>

        <!-- Fee Structure Info -->
        <div class="mb-6 p-4 bg-blue-50 rounded-lg">
          <h3 class="text-sm font-semibold text-blue-900 mb-2">Structure des Frais</h3>
          <p class="text-sm text-blue-700">
            {{ feeStructure.description || '100 FCFA + 10% du prix du produit' }}
          </p>
          
          <div v-if="feeStructure.examples" class="mt-3">
            <p class="text-xs text-blue-600 mb-2">Exemples :</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
              <div v-for="example in feeStructure.examples" :key="example.product_price" 
                   class="text-xs bg-white rounded px-2 py-1">
                <span class="text-gray-600">{{ example.formatted_price }}</span>
                <span class="text-green-600 font-medium"> → {{ example.formatted_fee }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Single Product Mode -->
        <div v-if="mode === 'single'" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Prix du produit (FCFA)
            </label>
            <input
              v-model.number="singleProduct.price"
              type="number"
              min="0"
              step="1"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
              placeholder="Ex: 5000"
              @input="calculateSingleFee"
            />
          </div>

          <div v-if="singleCalculation" class="p-4 bg-gray-50 rounded-lg">
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm text-gray-600">Frais de base:</span>
              <span class="font-medium">100 FCFA</span>
            </div>
            <div class="flex justify-between items-center mb-2">
              <span class="text-sm text-gray-600">Frais en % (10%):</span>
              <span class="font-medium">{{ formatPrice(singleCalculation.percentage_fee) }}</span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-gray-200">
              <span class="font-semibold">Total à payer:</span>
              <span class="font-bold text-green-600">{{ singleCalculation.formatted_fee }}</span>
            </div>
          </div>
        </div>

        <!-- Package Mode -->
        <div v-if="mode === 'package'" class="space-y-4">
          <div class="flex space-x-2 mb-4">
            <button 
              @click="packageMode = 'estimated'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                packageMode === 'estimated' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
              ]"
            >
              Estimation
            </button>
            <button 
              @click="packageMode = 'exact'"
              :class="[
                'px-4 py-2 rounded-md text-sm font-medium',
                packageMode === 'exact' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
              ]"
            >
              Prix Exacts
            </button>
          </div>

          <!-- Estimated Package -->
          <div v-if="packageMode === 'estimated'" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nombre de produits
                </label>
                <input
                  v-model.number="estimatedPackage.productCount"
                  type="number"
                  min="1"
                  max="50"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                  @input="calculateEstimatedPackageFee"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Valeur totale estimée (FCFA)
                </label>
                <input
                  v-model.number="estimatedPackage.totalValue"
                  type="number"
                  min="0"
                  step="1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                  @input="calculateEstimatedPackageFee"
                />
              </div>
            </div>

            <div v-if="estimatedCalculation" class="p-4 bg-gray-50 rounded-lg">
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm">Prix moyen par produit:</span>
                  <span class="font-medium">{{ estimatedCalculation.formatted_average_price }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm">Frais de base total:</span>
                  <span class="font-medium">{{ formatPrice(estimatedCalculation.base_fee_total) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm">Frais en % total:</span>
                  <span class="font-medium">{{ formatPrice(estimatedCalculation.percentage_fee_total) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t font-bold">
                  <span>Total estimé:</span>
                  <span class="text-green-600">{{ estimatedCalculation.formatted_estimated_fee }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Exact Package -->
          <div v-if="packageMode === 'exact'" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Prix des produits (FCFA)
              </label>
              <div class="space-y-2">
                <div v-for="(price, index) in exactPackage.prices" :key="index" 
                     class="flex items-center space-x-2">
                  <span class="text-sm text-gray-600 w-20">Produit {{ index + 1 }}:</span>
                  <input
                    v-model.number="exactPackage.prices[index]"
                    type="number"
                    min="0"
                    step="1"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
                    @input="calculateExactPackageFee"
                  />
                  <button 
                    v-if="exactPackage.prices.length > 1"
                    @click="removeProduct(index)"
                    class="text-red-500 hover:text-red-700"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                </div>
              </div>
              
              <button 
                v-if="exactPackage.prices.length < 50"
                @click="addProduct"
                class="mt-2 text-sm text-green-600 hover:text-green-700 font-medium"
              >
                + Ajouter un produit
              </button>
            </div>

            <div v-if="exactCalculation" class="p-4 bg-gray-50 rounded-lg">
              <div class="space-y-2">
                <div class="flex justify-between">
                  <span class="text-sm">Nombre de produits:</span>
                  <span class="font-medium">{{ exactCalculation.product_count }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm">Frais de base total:</span>
                  <span class="font-medium">{{ formatPrice(exactCalculation.base_fee_per_product * exactCalculation.product_count) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-sm">Frais en % total:</span>
                  <span class="font-medium">{{ formatPrice(exactCalculation.total_fee - (exactCalculation.base_fee_per_product * exactCalculation.product_count)) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t font-bold">
                  <span>Total exact:</span>
                  <span class="text-green-600">{{ exactCalculation.formatted_total_fee }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Affordability Check -->
        <div v-if="currentCalculation && currentCalculation.affordability" 
             class="mt-4 p-4 rounded-lg"
             :class="currentCalculation.affordability.can_afford ? 'bg-green-50' : 'bg-red-50'">
          <div class="flex items-center space-x-2">
            <svg v-if="currentCalculation.affordability.can_afford" class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <svg v-else class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
            
            <div>
              <p class="text-sm font-medium" 
                 :class="currentCalculation.affordability.can_afford ? 'text-green-800' : 'text-red-800'">
                {{ currentCalculation.affordability.can_afford ? 'Solde suffisant' : 'Solde insuffisant' }}
              </p>
              <p class="text-xs" 
                 :class="currentCalculation.affordability.can_afford ? 'text-green-600' : 'text-red-600'">
                Solde: {{ currentCalculation.affordability.formatted_balance }} | 
                Requis: {{ currentCalculation.affordability.formatted_required }}
                <span v-if="!currentCalculation.affordability.can_afford">
                  | Manque: {{ currentCalculation.affordability.formatted_shortfall }}
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
        <button
          @click="closeModal"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
        >
          Annuler
        </button>
        
        <button
          @click="proceedWithPayment"
          :disabled="!canProceed || loading"
          :class="[
            'px-6 py-2 text-sm font-medium rounded-md',
            canProceed && !loading
              ? 'text-white bg-green-600 hover:bg-green-700'
              : 'text-gray-400 bg-gray-200 cursor-not-allowed'
          ]"
        >
          <span v-if="loading">Création en cours...</span>
          <span v-else>Procéder au paiement</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { formatPrice } from '../../utils/currency.js'
import api from '../../services/api.js'

export default {
  name: 'PublishingFeeModal',
  props: {
    isOpen: {
      type: Boolean,
      default: false
    },
    productPrice: {
      type: Number,
      default: null
    }
  },
  emits: ['close', 'payment-created'],
  data() {
    return {
      mode: 'single', // single, package
      packageMode: 'estimated', // estimated, exact
      loading: false,
      
      // Fee structure
      feeStructure: {},
      
      // Single product
      singleProduct: {
        price: this.productPrice || 0
      },
      singleCalculation: null,
      
      // Estimated package
      estimatedPackage: {
        productCount: 1,
        totalValue: 0
      },
      estimatedCalculation: null,
      
      // Exact package
      exactPackage: {
        prices: [0]
      },
      exactCalculation: null
    }
  },
  computed: {
    currentCalculation() {
      if (this.mode === 'single') {
        return this.singleCalculation
      } else if (this.packageMode === 'estimated') {
        return this.estimatedCalculation
      } else {
        return this.exactCalculation
      }
    },
    
    canProceed() {
      if (this.loading) return false
      
      if (this.mode === 'single') {
        return this.singleProduct.price > 0 && this.singleCalculation
      } else if (this.packageMode === 'estimated') {
        return this.estimatedPackage.productCount > 0 && 
               this.estimatedPackage.totalValue > 0 && 
               this.estimatedCalculation
      } else {
        return this.exactPackage.prices.length > 0 && 
               this.exactPackage.prices.every(p => p > 0) && 
               this.exactCalculation
      }
    }
  },
  watch: {
    isOpen(newVal) {
      if (newVal) {
        this.loadFeeStructure()
        if (this.productPrice) {
          this.singleProduct.price = this.productPrice
          this.calculateSingleFee()
        }
      }
    },
    
    productPrice(newVal) {
      if (newVal) {
        this.singleProduct.price = newVal
        this.calculateSingleFee()
      }
    }
  },
  methods: {
    formatPrice,
    
    closeModal() {
      this.$emit('close')
    },
    
    async loadFeeStructure() {
      try {
        const response = await api.get('/publishing/fee-structure')
        if (response.data.success) {
          this.feeStructure = response.data.data
        }
      } catch (error) {
        console.error('Failed to load fee structure:', error)
      }
    },
    
    async calculateSingleFee() {
      if (!this.singleProduct.price || this.singleProduct.price <= 0) {
        this.singleCalculation = null
        return
      }
      
      try {
        const response = await api.post('/publishing/calculate-single-fee', {
          product_price: this.singleProduct.price
        })
        
        if (response.data.success) {
          this.singleCalculation = response.data.data
        }
      } catch (error) {
        console.error('Failed to calculate single fee:', error)
      }
    },
    
    async calculateEstimatedPackageFee() {
      if (!this.estimatedPackage.productCount || !this.estimatedPackage.totalValue) {
        this.estimatedCalculation = null
        return
      }
      
      try {
        const response = await api.post('/publishing/calculate-estimated-package-fee', {
          product_count: this.estimatedPackage.productCount,
          estimated_total_value: this.estimatedPackage.totalValue
        })
        
        if (response.data.success) {
          this.estimatedCalculation = response.data.data
        }
      } catch (error) {
        console.error('Failed to calculate estimated package fee:', error)
      }
    },
    
    async calculateExactPackageFee() {
      const validPrices = this.exactPackage.prices.filter(p => p > 0)
      if (validPrices.length === 0) {
        this.exactCalculation = null
        return
      }
      
      try {
        const response = await api.post('/publishing/calculate-exact-package-fee', {
          product_prices: validPrices
        })
        
        if (response.data.success) {
          this.exactCalculation = response.data.data
        }
      } catch (error) {
        console.error('Failed to calculate exact package fee:', error)
      }
    },
    
    addProduct() {
      this.exactPackage.prices.push(0)
    },
    
    removeProduct(index) {
      this.exactPackage.prices.splice(index, 1)
      this.calculateExactPackageFee()
    },
    
    async proceedWithPayment() {
      if (!this.canProceed) return
      
      this.loading = true
      
      try {
        let response
        
        if (this.mode === 'single') {
          // For single products, we still create a package with 1 product
          response = await api.post('/publishing/create-exact-package', {
            product_prices: [this.singleProduct.price]
          })
        } else if (this.packageMode === 'estimated') {
          response = await api.post('/publishing/create-estimated-package', {
            product_count: this.estimatedPackage.productCount,
            estimated_total_value: this.estimatedPackage.totalValue
          })
        } else {
          const validPrices = this.exactPackage.prices.filter(p => p > 0)
          response = await api.post('/publishing/create-exact-package', {
            product_prices: validPrices
          })
        }
        
        if (response.data.success) {
          this.$emit('payment-created', response.data.data)
          
          // Redirect to payment
          if (response.data.data.payment_link) {
            window.open(response.data.data.payment_link, '_blank')
          }
        } else {
          throw new Error(response.data.message || 'Failed to create payment')
        }
      } catch (error) {
        console.error('Failed to create payment:', error)
        // Show error message
        alert('Erreur lors de la création du paiement: ' + (error.response?.data?.message || error.message))
      } finally {
        this.loading = false
      }
    }
  }
}
</script>