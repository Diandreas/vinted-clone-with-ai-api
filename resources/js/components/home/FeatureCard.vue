<template>
  <div class="text-center p-6">
    <div :class="iconClasses" class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4">
      <component :is="iconComponent" class="w-8 h-8" />
    </div>
    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ title }}</h3>
    <p class="text-gray-600">{{ description }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  ShoppingBagIcon,
  VideoIcon,
  UsersIcon
} from 'lucide-vue-next'

const props = defineProps({
  icon: {
    type: String,
    required: true
  },
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  color: {
    type: String,
    default: 'blue'
  }
})

const iconMapping = {
  'shopping-bag': ShoppingBagIcon,
  'video': VideoIcon,
  'users': UsersIcon
}

const colorMapping = {
  blue: 'bg-primary-100 text-primary-600',
  red: 'bg-gray-100 text-gray-700',
  purple: 'bg-primary-100 text-primary-600'
}

const iconComponent = computed(() => iconMapping[props.icon] || ShoppingBagIcon)
const iconClasses = computed(() => colorMapping[props.color] || colorMapping.blue)
</script>



