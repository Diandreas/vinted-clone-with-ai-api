<template>
  <div class="relative">
    <img
      :key="`product-image-${productId}-${imageHash}`"
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
    
    <!-- Error placeholder -->
    <div 
      v-if="hasError" 
      class="absolute inset-0 bg-gray-100 rounded-lg flex items-center justify-center"
    >
      <div class="text-center text-gray-400">
        <div class="w-8 h-8 mx-auto mb-2 bg-gray-300 rounded flex items-center justify-center">
          <span class="text-xs font-bold text-gray-500">IMG</span>
        </div>
        <span class="text-xs">Image non disponible</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  src: {
    type: String,
    default: ''
  },
  fallback: {
    type: String,
    default: '/images/placeholder-product.png'
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
const imageHash = ref(0)

// Computed
const imageSrc = computed(() => {
  // Si on a une erreur, retourner le fallback
  if (hasError.value) {
    console.log('🖼️ Utilisation du fallback suite à une erreur:', props.fallback)
    return props.fallback
  }
  
  // Si on a une source valide, l'utiliser
  if (props.src && props.src !== props.fallback && props.src.trim() !== '') {
    console.log('🖼️ Utilisation de l\'image source:', props.src)
    return props.src
  }
  
  // Sinon utiliser le fallback
  console.log('🖼️ Aucune source valide, utilisation du fallback:', props.fallback)
  return props.fallback
})

// Methods
function handleImageError(event) {
  console.log('🖼️ Erreur de chargement d\'image détectée:', {
    targetSrc: event.target.src,
    fallback: props.fallback,
    hasError: hasError.value,
    productId: props.productId
  })
  
  // Si c'est déjà le fallback qui échoue, ne rien faire
  if (event.target.src === props.fallback) {
    console.log('🖼️ Le fallback a aussi échoué, arrêt des tentatives')
    hasError.value = true
    isLoading.value = false
    return
  }
  
  // Éviter les rechargements en boucle
  if (!hasError.value) {
    hasError.value = true
    isLoading.value = false
    console.log('🖼️ Utilisation du fallback:', props.fallback)
    emit('error', event)
  }
}

function handleImageLoad(event) {
  isLoading.value = false
  hasError.value = false
  console.log('🖼️ Image chargée avec succès:', {
    src: event.target.src,
    productId: props.productId
  })
  emit('load', event)
}

// Fonction pour réinitialiser l'état
function resetState() {
  isLoading.value = true
  hasError.value = false
  currentSrc.value = ''
  imageHash.value++
}

// Watch pour éviter les rechargements inutiles
watch(() => props.src, (newSrc, oldSrc) => {
  // Seulement si la source change vraiment
  if (newSrc !== oldSrc) {
    // Si on passe d'une erreur à une nouvelle source, réinitialiser
    if (hasError.value && newSrc && newSrc !== props.fallback) {
      resetState()
    }
    // Si la source change et n'est pas vide, réinitialiser
    else if (newSrc && newSrc !== oldSrc && newSrc !== props.fallback) {
      currentSrc.value = newSrc
      resetState()
    }
  }
}, { immediate: true })

// Reset state when product changes
watch(() => props.productId, (newId, oldId) => {
  if (newId !== oldId) {
    resetState()
  }
})

// Optimisation : éviter les re-renders inutiles
watch(() => props.fallback, (newFallback, oldFallback) => {
  if (newFallback !== oldFallback && hasError.value) {
    // Seulement réinitialiser si on était en erreur
    resetState()
  }
})
</script>

<style scoped>
/* Optimisations CSS */
img {
  transition: opacity 0.2s ease-in-out;
}

/* Éviter les reflows */
.relative {
  contain: layout style paint;
}
</style>

