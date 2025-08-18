<template>
  <button
    @click="$emit('click')"
    class="w-full group relative overflow-hidden bg-white border-2 border-gray-200 rounded-xl p-4 text-left hover:border-blue-300 hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1"
  >
    <!-- Background gradient on hover -->
    <div class="absolute inset-0 bg-gradient-to-r from-blue-50 to-indigo-50 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
    
    <!-- Content -->
    <div class="relative flex items-start space-x-3">
      <!-- Icon -->
      <div class="flex-shrink-0">
        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow-md group-hover:shadow-lg transition-shadow duration-200">
          <component :is="iconComponent" class="w-5 h-5" />
        </div>
      </div>
      
      <!-- Text content -->
      <div class="flex-1 min-w-0">
        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-blue-900 transition-colors duration-200">
          {{ title }}
        </h3>
        <p class="text-xs text-gray-600 group-hover:text-blue-700 transition-colors duration-200 mt-1">
          {{ description }}
        </p>
      </div>
      
      <!-- Arrow indicator -->
      <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transform translate-x-2 group-hover:translate-x-0 transition-all duration-200">
        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </div>
    </div>
    
    <!-- Hover effect line -->
    <div class="absolute bottom-0 left-0 w-0 h-0.5 bg-gradient-to-r from-blue-500 to-indigo-600 group-hover:w-full transition-all duration-300"></div>
  </button>
</template>

<script setup>
import { computed } from 'vue'
import {
  PlusIcon,
  ShoppingCartIcon,
  BarChart3Icon,
  PackageIcon,
  UserIcon,
  SettingsIcon,
  BellIcon,
  HeartIcon,
  StarIcon,
  TrendingUpIcon,
  DollarSignIcon,
  EyeIcon,
  UsersIcon
} from 'lucide-vue-next'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  color: {
    type: String,
    default: 'blue'
  }
})

const emit = defineEmits(['click'])

// Map icon names to components
const iconComponent = computed(() => {
  const iconMap = {
    'plus': PlusIcon,
    'shopping-cart': ShoppingCartIcon,
    'bar-chart': BarChart3Icon,
    'package': PackageIcon,
    'user': UserIcon,
    'settings': SettingsIcon,
    'bell': BellIcon,
    'heart': HeartIcon,
    'star': StarIcon,
    'trending-up': TrendingUpIcon,
    'dollar-sign': DollarSignIcon,
    'eye': EyeIcon,
    'users': UsersIcon
  }
  
  return iconMap[props.icon] || PlusIcon
})
</script>

<style scoped>
/* Custom hover effects */
.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

.group:hover .group-hover\:shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>



