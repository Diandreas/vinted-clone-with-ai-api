<template>
  <div class="group relative overflow-hidden bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/50 p-6 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1">
    <!-- Background gradient on hover -->
    <div class="absolute inset-0 bg-gradient-to-br from-primary-50/50 via-primary-50/30 to-primary-50/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    
    <!-- Content -->
    <div class="relative">
      <!-- Header with icon and trend -->
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 flex items-center justify-center text-white shadow-lg group-hover:shadow-xl transition-shadow duration-300">
            <component :is="iconComponent" class="w-6 h-6" />
          </div>
          <div>
            <h3 class="text-sm font-medium text-gray-600 group-hover:text-gray-700 transition-colors duration-200">
              {{ title }}
            </h3>
          </div>
        </div>
        
        <!-- Trend indicator -->
        <div v-if="trend !== undefined" class="flex items-center space-x-1">
          <div 
            :class="trendClasses"
            class="px-2 py-1 rounded-lg text-xs font-medium flex items-center space-x-1"
          >
            <component :is="trendIcon" class="w-3 h-3" />
            <span>{{ Math.abs(trend) }}%</span>
          </div>
        </div>
      </div>
      
      <!-- Main value -->
      <div class="mb-2">
        <div class="text-3xl font-bold text-gray-900 group-hover:text-primary-900 transition-colors duration-200">
          {{ formattedValue }}
        </div>
      </div>
      
      <!-- Additional info or description -->
      <div v-if="description" class="text-sm text-gray-500 group-hover:text-gray-600 transition-colors duration-200">
        {{ description }}
      </div>
      
      <!-- Hover effect line -->
      <div class="absolute bottom-0 left-0 w-0 h-1 bg-gradient-to-r from-primary-500 to-primary-600 group-hover:w-full transition-all duration-500"></div>
    </div>
    
    <!-- Decorative elements -->
    <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-primary-100/20 to-primary-100/20 rounded-full -translate-y-10 translate-x-10 group-hover:scale-110 transition-transform duration-300"></div>
    <div class="absolute bottom-0 left-0 w-16 h-16 bg-gradient-to-br from-primary-100/20 to-gray-100/20 rounded-full translate-y-8 -translate-x-8 group-hover:scale-110 transition-transform duration-300"></div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  PackageIcon,
  DollarSignIcon,
  UsersIcon,
  EyeIcon,
  TrendingUpIcon,
  TrendingDownIcon,
  MinusIcon,
  ShoppingCartIcon,
  HeartIcon,
  StarIcon,
  MessageCircleIcon,
  BellIcon,
  ClockIcon,
  CheckCircleIcon
} from 'lucide-vue-next'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  color: {
    type: String,
    default: 'blue'
  },
  trend: {
    type: Number,
    default: undefined
  },
  description: {
    type: String,
    default: ''
  }
})

// Map icon names to components
const iconComponent = computed(() => {
  const iconMap = {
    'package': PackageIcon,
    'dollar-sign': DollarSignIcon,
    'users': UsersIcon,
    'eye': EyeIcon,
    'shopping-cart': ShoppingCartIcon,
    'heart': HeartIcon,
    'star': StarIcon,
    'message-circle': MessageCircleIcon,
    'bell': BellIcon,
    'clock': ClockIcon,
    'check-circle': CheckCircleIcon
  }
  
  return iconMap[props.icon] || PackageIcon
})

// Format the value
const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return props.value.toLocaleString('fr-FR')
  }
  return props.value
})

// Trend indicator
const trendIcon = computed(() => {
  if (props.trend === undefined) return MinusIcon
  if (props.trend > 0) return TrendingUpIcon
  if (props.trend < 0) return TrendingDownIcon
  return MinusIcon
})

const trendClasses = computed(() => {
  if (props.trend === undefined) return 'bg-gray-100 text-gray-600'
  if (props.trend > 0) return 'bg-green-100 text-green-700'
  if (props.trend < 0) return 'bg-gray-100 text-gray-800'
  return 'bg-gray-100 text-gray-600'
})
</script>

<style scoped>
/* Custom animations */
@keyframes float {
  0%, 100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.group:hover .group-hover\:animate-float {
  animation: float 3s ease-in-out infinite;
}

/* Glassmorphism effect */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
</style>

