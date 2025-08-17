<template>
  <div class="relative">
    <img
      :key="`product-image-${productId}`"
      :src="imageSrc"
      :alt="alt"
      :class="imageClasses"
      @error="handleImageError"
      @load="handleImageLoad"
      loading="lazy"
      decoding="async"
    />
    
    <!-- Loading placeholder -->
    <div 
      v-if="isLoading" 
      class="absolute inset-0 bg-gray-200 animate-pulse rounded-lg"
    ></div>
    
    <!-- Error placeholder -->
    <div 
      v-if="hasError" 
      class="absolute inset-0 bg-gray-100 rounded-lg flex items-center justify-center"
    >
      <div class="text-center text-gray-400">
        <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span class="text-xs">Image non disponible</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const props = defineProps({
  src: {
    type: String,
    default: ''
  },
  fallback: {
    type: String,
    default: '/placeholder-product.jpg'
  },
  alt: {
    type: String,
    default: 'Image produit'
  },
  productId: {
    type: [String, Number],
    default: 'unknown'
  },
  imageClasses: {
    type: String,
    default: 'w-full h-full object-cover rounded-lg'
  }
})

const emit = defineEmits(['load', 'error'])

// State
const isLoading = ref(true)
const hasError = ref(false)
const currentSrc = ref('')

// Computed
const imageSrc = computed(() => {
  if (hasError.value) {
    return props.fallback
  }
  return props.src || props.fallback
})

// Methods
function handleImageError(event) {
  // Éviter les rechargements en boucle
  if (event.target.src !== props.fallback) {
    hasError.value = true
    isLoading.value = false
    emit('error', event)
  }
}

function handleImageLoad(event) {
  isLoading.value = false
  hasError.value = false
  emit('load', event)
}

// Watch pour éviter les rechargements inutiles
watch(() => props.src, (newSrc) => {
  if (newSrc && newSrc !== currentSrc.value) {
    currentSrc.value = newSrc
    isLoading.value = true
    hasError.value = false
  }
}, { immediate: true })

// Reset state when product changes
watch(() => props.productId, () => {
  isLoading.value = true
  hasError.value = false
  currentSrc.value = ''
})
</script>

