<template>
  <div class="bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200 p-4 text-center">
    <!-- Category Icon/Image -->
    <div class="w-12 h-12 mx-auto mb-3 bg-gray-100 rounded-lg flex items-center justify-center">
      <img
        v-if="category.icon"
        :src="category.icon"
        :alt="category.name"
        class="w-8 h-8 object-cover"
        @error="handleImageError"
      />
      <TagIcon v-else class="w-6 h-6 text-gray-400" />
    </div>
    
    <!-- Category Name -->
    <h3 class="font-medium text-gray-900 text-sm">{{ category.name }}</h3>
    
    <!-- Product Count -->
    <p v-if="category.products_count" class="text-xs text-gray-500 mt-1">
      {{ category.products_count }} {{ category.products_count > 1 ? 'produits' : 'produit' }}
    </p>
  </div>
</template>

<script setup>
import { TagIcon } from 'lucide-vue-next'

const props = defineProps({
  category: {
    type: Object,
    required: true
  }
})

const handleImageError = (event) => {
  event.target.style.display = 'none'
}
</script>



