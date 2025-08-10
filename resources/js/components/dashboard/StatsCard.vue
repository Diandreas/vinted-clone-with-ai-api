<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <p class="text-2xl font-bold text-gray-900 mt-2">{{ formattedValue }}</p>
        <div v-if="trend !== undefined" class="flex items-center mt-2">
          <TrendingUpIcon
            v-if="trend > 0"
            class="w-4 h-4 text-green-500 mr-1"
          />
          <TrendingDownIcon
            v-else-if="trend < 0"
            class="w-4 h-4 text-red-500 mr-1"
          />
          <MinusIcon
            v-else
            class="w-4 h-4 text-gray-400 mr-1"
          />
          <span
            :class="{
              'text-green-600': trend > 0,
              'text-red-600': trend < 0,
              'text-gray-500': trend === 0
            }"
            class="text-sm font-medium"
          >
            {{ Math.abs(trend) }}%
          </span>
          <span class="text-gray-500 text-sm ml-1">vs mois dernier</span>
        </div>
      </div>
      <div
        :class="iconColorClasses"
        class="p-3 rounded-lg"
      >
        <component :is="iconComponent" class="w-6 h-6" />
      </div>
    </div>
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
  ShoppingBagIcon,
  HeartIcon,
  MessageCircleIcon
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
  }
})

const iconMapping = {
  'package': PackageIcon,
  'dollar-sign': DollarSignIcon,
  'users': UsersIcon,
  'eye': EyeIcon,
  'shopping-bag': ShoppingBagIcon,
  'heart': HeartIcon,
  'message-circle': MessageCircleIcon
}

const colorMapping = {
  blue: {
    bg: 'bg-blue-50',
    text: 'text-blue-600'
  },
  green: {
    bg: 'bg-green-50',
    text: 'text-green-600'
  },
  purple: {
    bg: 'bg-purple-50',
    text: 'text-purple-600'
  },
  orange: {
    bg: 'bg-orange-50',
    text: 'text-orange-600'
  },
  red: {
    bg: 'bg-red-50',
    text: 'text-red-600'
  },
  yellow: {
    bg: 'bg-yellow-50',
    text: 'text-yellow-600'
  }
}

const iconComponent = computed(() => iconMapping[props.icon] || PackageIcon)

const iconColorClasses = computed(() => {
  const colors = colorMapping[props.color] || colorMapping.blue
  return `${colors.bg} ${colors.text}`
})

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    // Format numbers with proper thousand separators
    return new Intl.NumberFormat('fr-FR').format(props.value)
  }
  return props.value
})
</script>

