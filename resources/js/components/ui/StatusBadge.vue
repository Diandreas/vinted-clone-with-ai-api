<template>
  <span :class="badgeClasses" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
    <span :class="dotClasses" class="w-1.5 h-1.5 rounded-full mr-1.5"></span>
    {{ statusText }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    required: true
  }
})

const statusConfig = {
  draft: {
    text: 'Brouillon',
    badge: 'bg-gray-100 text-gray-800',
    dot: 'bg-gray-500'
  },
  active: {
    text: 'Actif',
    badge: 'bg-green-100 text-green-800',
    dot: 'bg-green-500'
  },
  sold: {
    text: 'Vendu',
    badge: 'bg-blue-100 text-blue-800',
    dot: 'bg-blue-500'
  },
  reserved: {
    text: 'Réservé',
    badge: 'bg-yellow-100 text-yellow-800',
    dot: 'bg-yellow-500'
  },
  removed: {
    text: 'Supprimé',
    badge: 'bg-red-100 text-red-800',
    dot: 'bg-red-500'
  }
}

const config = computed(() => statusConfig[props.status] || statusConfig.draft)
const statusText = computed(() => config.value.text)
const badgeClasses = computed(() => config.value.badge)
const dotClasses = computed(() => config.value.dot)
</script>



