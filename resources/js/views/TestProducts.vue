<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-3xl font-bold text-gray-900 mb-8">Test Produits</h1>
      
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold mb-4">Test de l'API</h2>
        
        <button 
          @click="testAPI" 
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Tester l'API
        </button>
        
        <div v-if="loading" class="mt-4 text-gray-600">
          Chargement...
        </div>
        
        <div v-if="error" class="mt-4 text-red-600">
          Erreur: {{ error }}
        </div>
        
        <div v-if="products.length > 0" class="mt-6">
          <h3 class="text-lg font-semibold mb-3">Produits trouvés ({{ products.length }})</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="product in products" 
              :key="product.id"
              class="border border-gray-200 rounded-lg p-4"
            >
              <h4 class="font-semibold">{{ product.title }}</h4>
              <p class="text-gray-600">{{ product.price }}€</p>
              <p class="text-sm text-gray-500">{{ product.category?.name }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const loading = ref(false)
const error = ref('')
const products = ref([])

const testAPI = async () => {
  loading.value = true
  error.value = ''
  products.value = []
  
  try {
    console.log('Test de l\'API...')
    const response = await window.axios.get('/products')
    console.log('Réponse API:', response)
    
    if (response.data && response.data.data) {
      products.value = response.data.data.data || []
      console.log('Produits chargés:', products.value)
    } else {
      error.value = 'Structure de données inattendue'
      console.error('Structure inattendue:', response.data)
    }
  } catch (err) {
    error.value = err.message
    console.error('Erreur API:', err)
  } finally {
    loading.value = false
  }
}
</script>
