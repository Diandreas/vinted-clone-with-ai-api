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
    badge: 'bg-primary-100 text-primary-800',
    dot: 'bg-primary-500'
  },
  reserved: {
    text: 'Réservé',
    badge: 'bg-gray-100 text-gray-800',
    dot: 'bg-gray-500'
  },
  removed: {
    text: 'Supprimé',
    badge: 'bg-gray-100 text-gray-900',
    dot: 'bg-gray-500'
  }
}

const config = computed(() => statusConfig[props.status] || statusConfig.draft)
const statusText = computed(() => config.value.text)
const badgeClasses = computed(() => config.value.badge)
const dotClasses = computed(() => config.value.dot)
</script>



