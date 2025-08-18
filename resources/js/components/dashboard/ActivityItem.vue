<template>
  <div class="flex items-start space-x-3">
    <!-- Icon -->
    <div :class="iconClasses">
      <component :is="iconComponent" class="w-4 h-4" />
    </div>
    
    <!-- Content -->
    <div class="flex-1 min-w-0">
      <p class="text-sm text-gray-900">
        <span class="font-medium">{{ activity.actor_name }}</span>
        {{ getActivityMessage(activity) }}
        <span v-if="activity.target_name" class="font-medium">{{ activity.target_name }}</span>
      </p>
      <p class="text-xs text-gray-500 mt-1">
        {{ formatDate(activity.created_at) }}
      </p>
    </div>
    
    <!-- Action -->
    <div v-if="hasAction" class="flex-shrink-0">
      <RouterLink
        :to="getActivityUrl(activity)"
        class="text-xs text-primary-600 hover:text-primary-700 font-medium"
      >
        Voir
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'
import {
  HeartIcon,
  ShoppingBagIcon,
  UserPlusIcon,
  PackageIcon,
  EyeIcon,
  StarIcon
} from 'lucide-vue-next'

const props = defineProps({
  activity: {
    type: Object,
    required: true
  }
})

const iconMapping = {
  like: HeartIcon,
  comment: PackageIcon,
  order: ShoppingBagIcon,
  follow: UserPlusIcon,
  product_created: PackageIcon,
  product_viewed: EyeIcon,
  review: StarIcon
}

const iconColorMapping = {
  like: 'bg-gray-50 text-gray-700',
  comment: 'bg-primary-50 text-primary-600',
  order: 'bg-green-50 text-green-600',
  follow: 'bg-primary-50 text-primary-600',
  product_created: 'bg-primary-50 text-primary-600',
  product_viewed: 'bg-gray-50 text-gray-600',
  review: 'bg-gray-50 text-gray-600'
}

const iconComponent = computed(() => iconMapping[props.activity.type] || PackageIcon)

const iconClasses = computed(() => {
  const baseClasses = 'p-2 rounded-full'
  const colorClasses = iconColorMapping[props.activity.type] || 'bg-gray-50 text-gray-600'
  return `${baseClasses} ${colorClasses}`
})

const hasAction = computed(() => {
  return ['like', 'comment', 'order', 'product_created', 'review'].includes(props.activity.type)
})

const getActivityMessage = (activity) => {
  const messages = {
    like: ' a aimé votre produit ',
    comment: ' a commenté votre produit ',
    order: ' a commandé votre produit ',
    follow: ' a commencé à vous suivre',
    product_created: ' a ajouté un nouveau produit ',
    product_viewed: ' a consulté votre produit ',
    review: ' a laissé un avis sur '
  }
  
  return messages[activity.type] || ' a effectué une action sur '
}

const getActivityUrl = (activity) => {
  switch (activity.type) {
    case 'like':
    case 'comment':
    case 'product_created':
    case 'product_viewed':
      return `/products/${activity.target_id}`
    case 'order':
      return `/orders/${activity.target_id}`
    case 'follow':
      return `/users/${activity.actor_id}`
    case 'review':
      return `/reviews/${activity.target_id}`
    default:
      return '#'
  }
}

const formatDate = (date) => {
  return formatDistanceToNow(new Date(date), {
    addSuffix: true,
    locale: fr
  })
}
</script>



