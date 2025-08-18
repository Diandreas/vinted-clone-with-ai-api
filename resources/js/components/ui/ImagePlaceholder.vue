<template>
  <div class="relative">
    <!-- Image principale -->
    <img
      v-if="shouldShowImage"
      :key="`image-${imageId}`"
      :src="imageSrc"
      :alt="alt"
      :class="imageClasses"
      @error="handleImageError"
      @load="handleImageLoad"
      loading="lazy"
      decoding="async"
      :style="{ opacity: isLoading ? 0 : 1 }"
    />
    
    <!-- Loading placeholder -->
    <div 
      v-if="isLoading" 
      class="absolute inset-0 bg-gray-200 animate-pulse rounded-lg"
    ></div>
    
    <!-- Error placeholder avec SVG inline -->
    <div 
      v-if="hasError || !shouldShowImage" 
      class="absolute inset-0 bg-gray-100 rounded-lg flex items-center justify-center"
    >
      <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
      </svg>
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
  alt: {
    type: String,
    default: 'Image'
  },
  imageId: {
    type: [String, Number],
    default: 'unknown'
  },
  imageClasses: {
    type: String,
    default: 'w-full h-full object-cover rounded-lg'
  },
  showPlaceholder: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['load', 'error'])

// State
const isLoading = ref(true)
const hasError = ref(false)
const imageAttempted = ref(false)

// Computed
const shouldShowImage = computed(() => {
  return props.src && props.src.trim() !== '' && !hasError.value
})

const imageSrc = computed(() => {
  if (!shouldShowImage.value) {
    return ''
  }
  return props.src
})

// Methods
function handleImageError(event) {
  if (!hasError.value) {
    hasError.value = true
    isLoading.value = false
    imageAttempted.value = true
    emit('error', event)
  }
}

function handleImageLoad(event) {
  isLoading.value = false
  hasError.value = false
  imageAttempted.value = true
  emit('load', event)
}

// Reset state when image changes
watch(() => props.src, (newSrc, oldSrc) => {
  if (newSrc !== oldSrc) {
    // Reset state only if we have a new valid source
    if (newSrc && newSrc.trim() !== '') {
      isLoading.value = true
      hasError.value = false
      imageAttempted.value = false
    }
  }
}, { immediate: true })

// Reset state when image ID changes
watch(() => props.imageId, (newId, oldId) => {
  if (newId !== oldId) {
    isLoading.value = true
    hasError.value = false
    imageAttempted.value = false
  }
})
</script>

<style scoped>
/* Optimisations CSS */
img {
  transition: opacity 0.2s ease-in-out;
}

.relative {
  contain: layout style paint;
}

/* Éviter les reflows */
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>
