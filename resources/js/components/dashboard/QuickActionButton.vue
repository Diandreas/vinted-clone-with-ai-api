<template>
  <RouterLink
    :to="to"
    class="flex items-center justify-between p-4 sm:p-5 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-200 group hover:shadow-sm"
  >
    <div class="flex items-center space-x-4">
      <div :class="iconClasses">
        <component :is="iconComponent" class="w-5 h-5 sm:w-6 sm:h-6" />
      </div>
      <div class="flex flex-col">
        <span class="font-semibold text-gray-900 text-sm sm:text-base">{{ title }}</span>
        <span v-if="description" class="text-gray-500 text-xs sm:text-sm">{{ description }}</span>
      </div>
    </div>
    
    <div class="flex items-center space-x-3">
      <span
        v-if="badge && badge > 0"
        :class="badgeClasses"
        class="px-3 py-1 text-xs font-medium rounded-full"
      >
        {{ badge > 99 ? '99+' : badge }}
      </span>
      <ChevronRightIcon class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400 group-hover:text-gray-600 transition-colors" />
    </div>
  </RouterLink>
</template>

<script setup>
import { computed } from 'vue'
import {
  MessageCircleIcon,
  ShoppingBagIcon,
  BellIcon,
  CameraIcon,
  ChevronRightIcon,
  PlusIcon,
  VideoIcon,
  UsersIcon,
  UserIcon
} from 'lucide-vue-next'

const props = defineProps({
  to: {
    type: String,
    required: true
  },
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
    default: ''
  },
  badge: {
    type: Number,
    default: 0
  },
  color: {
    type: String,
    default: 'gray'
  }
})

const iconMapping = {
  'message-circle': MessageCircleIcon,
  'shopping-bag': ShoppingBagIcon,
  'bell': BellIcon,
  'camera': CameraIcon,
  'plus': PlusIcon,
  'video': VideoIcon,
  'users': UsersIcon,
  'user': UserIcon
}

const colorMapping = {
  blue: {
    icon: 'bg-blue-50 text-blue-600',
    badge: 'bg-blue-100 text-blue-800'
  },
  green: {
    icon: 'bg-green-50 text-green-600',
    badge: 'bg-green-100 text-green-800'
  },
  yellow: {
    icon: 'bg-yellow-50 text-yellow-600',
    badge: 'bg-yellow-100 text-yellow-800'
  },
  purple: {
    icon: 'bg-purple-50 text-purple-600',
    badge: 'bg-purple-100 text-purple-800'
  },
  red: {
    icon: 'bg-red-50 text-red-600',
    badge: 'bg-red-100 text-red-800'
  },
  gray: {
    icon: 'bg-gray-50 text-gray-600',
    badge: 'bg-gray-100 text-gray-800'
  }
}

const iconComponent = computed(() => iconMapping[props.icon] || MessageCircleIcon)

const iconClasses = computed(() => {
  const colors = colorMapping[props.color] || colorMapping.gray
  return `p-2 rounded-lg ${colors.icon}`
})

const badgeClasses = computed(() => {
  const colors = colorMapping[props.color] || colorMapping.gray
  return colors.badge
})
</script>



