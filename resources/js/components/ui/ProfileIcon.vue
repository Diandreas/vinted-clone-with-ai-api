<template>
  <div class="relative">
    <!-- Avatar avec image -->
    <img
      v-if="showImage && imageSrc && !hasError"
      :key="`avatar-${userId}-${imageSrc}`"
      :src="imageSrc"
      :alt="alt"
      :class="imageClasses"
      @error="handleImageError"
      @load="handleImageLoad"
      loading="lazy"
      decoding="async"
    />
    
    <!-- Avatar avec initiales (fallback) -->
    <div
      v-else
      :class="[
        'flex items-center justify-center text-white font-semibold',
        sizeClasses,
        'rounded-full bg-gradient-to-br from-primary-500 to-primary-600'
      ]"
    >
      {{ userInitials }}
    </div>
    
    <!-- Badge de vérification -->
    <div
      v-if="verified"
      class="absolute -bottom-0.5 sm:-bottom-1 -right-0.5 sm:-right-1 bg-primary-500 rounded-full p-0.5 sm:p-1 shadow-lg"
    >
      <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
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
    default: 'Avatar utilisateur'
  },
  userId: {
    type: [String, Number],
    default: 'unknown'
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value)
  },
  verified: {
    type: Boolean,
    default: false
  },
  fallbackToInitials: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['load', 'error'])

// State
const hasError = ref(false)
const isLoading = ref(false)

// Computed
const imageSrc = computed(() => {
  if (!props.src || hasError.value) return ''
  return props.src
})

const showImage = computed(() => {
  return props.fallbackToInitials && imageSrc.value && !hasError.value
})

const userInitials = computed(() => {
  if (!props.alt || props.alt === 'Avatar utilisateur') return '?'
  
  const names = props.alt.trim().split(' ')
  if (names.length === 1) {
    return names[0].substring(0, 2).toUpperCase()
  } else {
    return names.slice(0, 2).map(name => name.charAt(0).toUpperCase()).join('')
  }
})

const sizeClasses = computed(() => {
  const sizes = {
    xs: 'w-4 h-4 sm:w-6 sm:h-6 text-xs',
    sm: 'w-5 h-5 sm:w-8 sm:h-8 text-xs sm:text-sm',
    md: 'w-6 h-6 sm:w-10 sm:h-10 text-sm',
    lg: 'w-8 h-8 sm:w-12 sm:h-12 text-sm sm:text-base',
    xl: 'w-10 h-10 sm:w-16 sm:h-16 text-base sm:text-lg',
    '2xl': 'w-12 h-12 sm:w-20 sm:h-20 text-lg sm:text-xl'
  }
  return sizes[props.size] || sizes.md
})

const imageClasses = computed(() => {
  return `${sizeClasses.value} rounded-full object-cover`
})

// Methods
function handleImageError(event) {
  hasError.value = true
  isLoading.value = false
  emit('error', event)
}

function handleImageLoad(event) {
  isLoading.value = false
  hasError.value = false
  emit('load', event)
}

// Watch pour éviter les rechargements inutiles
watch(() => props.src, (newSrc) => {
  if (newSrc && newSrc !== imageSrc.value) {
    hasError.value = false
    isLoading.value = true
  }
}, { immediate: true })

// Reset state when user changes
watch(() => props.userId, () => {
  hasError.value = false
  isLoading.value = false
})
</script>
