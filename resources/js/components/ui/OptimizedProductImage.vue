<template>
  <div class="relative">
    <!-- Image principale avec cache -->
    <img
      v-if="shouldShowImage"
      :key="`optimized-image-${productId}-${imageHash}`"
      :src="imageSrc"
      :alt="alt"
      :class="imageClasses"
      @error="handleImageError"
      @load="handleImageLoad"
      loading="lazy"
      decoding="async"
      :style="{ opacity: isLoading ? 0 : 1 }"
      crossorigin="anonymous"
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
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  src: {
    type: String,
    default: ''
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
  },
  enableCache: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['load', 'error'])

// State
const isLoading = ref(true)
const hasError = ref(false)
const imageHash = ref(0)
const imageCache = ref(new Map())
const isMounted = ref(false)

// Computed
const shouldShowImage = computed(() => {
  return props.src && props.src.trim() !== '' && !hasError.value
})

const imageSrc = computed(() => {
  if (!shouldShowImage.value) {
    return ''
  }
  
  // Utiliser le cache si activé
  if (props.enableCache && imageCache.value.has(props.src)) {
    return imageCache.value.get(props.src)
  }
  
  return props.src
})

// Methods
function handleImageError(event) {
  // Éviter les rechargements en boucle
  if (event.target.src !== props.src && !hasError.value) {
    hasError.value = true
    isLoading.value = false
    emit('error', event)
    
    // Retirer de la cache si l'image échoue
    if (props.enableCache) {
      imageCache.value.delete(props.src)
    }
  }
}

function handleImageLoad(event) {
  isLoading.value = false
  hasError.value = false
  
  // Ajouter à la cache si activé
  if (props.enableCache && props.src) {
    imageCache.value.set(props.src, props.src)
  }
  
  emit('load', event)
}

// Fonction pour précharger l'image
function preloadImage(src) {
  if (!src || !isMounted.value) return
  
  return new Promise((resolve, reject) => {
    const img = new Image()
    img.onload = () => {
      if (props.enableCache) {
        imageCache.value.set(src, src)
      }
      resolve(img)
    }
    img.onerror = reject
    img.src = src
  })
}

// Fonction pour réinitialiser l'état
function resetState() {
  isLoading.value = true
  hasError.value = false
  imageHash.value++
}

// Watchers optimisés
watch(() => props.src, (newSrc, oldSrc) => {
  // Seulement si la source change vraiment
  if (newSrc !== oldSrc && isMounted.value) {
    if (newSrc && newSrc.trim() !== '') {
      // Précharger l'image
      preloadImage(newSrc).catch(() => {
        // En cas d'erreur de préchargement, on continue normalement
      })
      
      // Réinitialiser l'état seulement si nécessaire
      if (hasError.value || oldSrc === '') {
        resetState()
      }
    } else {
      // Source vide, afficher le placeholder
      hasError.value = true
      isLoading.value = false
    }
  }
}, { immediate: false })

// Reset state when product changes
watch(() => props.productId, (newId, oldId) => {
  if (newId !== oldId && isMounted.value) {
    resetState()
  }
})

// Lifecycle
onMounted(() => {
  isMounted.value = true
  
  // Précharger l'image si elle existe
  if (props.src) {
    preloadImage(props.src).catch(() => {
      // En cas d'erreur, on continue normalement
    })
  }
})

onUnmounted(() => {
  isMounted.value = false
})

// Nettoyer la cache périodiquement pour éviter la surcharge mémoire
setInterval(() => {
  if (imageCache.value.size > 100) {
    const entries = Array.from(imageCache.value.entries())
    // Garder seulement les 50 dernières images
    imageCache.value.clear()
    entries.slice(-50).forEach(([key, value]) => {
      imageCache.value.set(key, value)
    })
  }
}, 60000) // Toutes les minutes
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

/* Optimisations de performance */
.relative {
  will-change: transform;
  transform: translateZ(0);
}
</style>
