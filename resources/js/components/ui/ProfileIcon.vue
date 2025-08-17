<template>
  <svg 
    :class="`${size} ${color}`"
    fill="none" 
    stroke="currentColor" 
    viewBox="0 0 24 24"
  >
    <path 
      stroke-linecap="round" 
      stroke-linejoin="round" 
      :stroke-width="strokeWidth" 
      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
    />
  </svg>
</template>

<script setup>
defineProps({
  size: {
    type: String,
    default: 'w-5 h-5'
  },
  color: {
    type: String,
    default: 'text-gray-600'
  },
  strokeWidth: {
    type: String,
    default: '2'
  }
})
</script>
