<template>
  <img
    :src="imageSrc"
    :alt="alt"
    :class="imageClass"
    @error="handleImageError"
    @load="handleImageLoad"
  />
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
  fallback: {
    type: String,
    default: '/placeholder-product.jpg'
  },
  imageClass: {
    type: String,
    default: 'w-full h-full object-cover'
  }
})

const emit = defineEmits(['error', 'load'])

const imageSrc = ref(props.src || props.fallback)
const hasError = ref(false)

// Computed
const currentSrc = computed(() => {
  if (hasError.value) {
    return props.fallback
  }
  return props.src || props.fallback
})

// Watch for src changes
watch(() => props.src, (newSrc) => {
  if (newSrc && newSrc !== imageSrc.value) {
    imageSrc.value = newSrc
    hasError.value = false
  }
})

// Methods
const handleImageError = (event) => {
  hasError.value = true
  imageSrc.value = props.fallback
  emit('error', event)
}

const handleImageLoad = (event) => {
  hasError.value = false
  emit('load', event)
}
</script>

