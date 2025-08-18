<template>
  <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200 cursor-pointer">
    <!-- Product Image -->
    <div class="relative aspect-square overflow-hidden rounded-t-lg">
      <img
        :src="product.main_image || '/placeholder-product.jpg'"
        :alt="product.title"
        class="w-full h-full object-cover"
        @error="handleImageError"
      />
      
      <!-- Trending Badge -->
      <div class="absolute top-2 left-2">
        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-1 rounded-full flex items-center space-x-1">
          <TrendingUpIcon class="w-3 h-3" />
          <span>Tendance</span>
        </span>
      </div>
      
      <!-- Like Button -->
      <button
        @click.stop="toggleLike"
        :disabled="likingProduct"
        class="absolute top-2 right-2 bg-white bg-opacity-90 hover:bg-opacity-100 p-2 rounded-full shadow-sm transition-all"
      >
        <HeartIcon
          class="w-4 h-4"
          :class="isLiked ? 'text-gray-500 fill-current' : 'text-gray-600'"
        />
      </button>
    </div>

    <!-- Product Info -->
    <div class="p-3">
      <div class="flex items-start justify-between mb-2">
        <h3 class="font-medium text-gray-900 text-sm line-clamp-2 flex-1">
          {{ product.title }}
        </h3>
        <div class="ml-2">
          <div class="text-lg font-bold text-gray-900">
            {{ formatPrice(product.price) }}
          </div>
        </div>
      </div>

      <!-- Brand -->
      <div v-if="product.brand" class="text-xs text-gray-600 mb-2">
        {{ product.brand.name }}
      </div>

      <!-- Stats -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <img
            :src="product.user?.avatar || '/default-avatar.png'"
            :alt="product.user?.name"
            class="w-5 h-5 rounded-full object-cover"
          />
          <span class="text-xs text-gray-600">{{ product.user?.name }}</span>
        </div>
        
        <div class="flex items-center space-x-2 text-xs text-gray-500">
          <div class="flex items-center space-x-1">
            <EyeIcon class="w-3 h-3" />
            <span>{{ formatCount(product.views_count) }}</span>
          </div>
          <div class="flex items-center space-x-1">
            <HeartIcon class="w-3 h-3" />
            <span>{{ formatCount(product.likes_count) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import {
  HeartIcon,
  EyeIcon,
  TrendingUpIcon
} from 'lucide-vue-next'

const props = defineProps({
  product: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['like'])

const authStore = useAuthStore()

// Reactive state
const likingProduct = ref(false)

// Computed
const isLiked = computed(() => props.product.is_liked_by_user)

// Methods
const formatPrice = (price) => {
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
                            currency: 'XAF'
  }).format(price)
}

const formatCount = (count) => {
  if (count >= 1000) {
    return Math.floor(count / 1000) + 'k'
  }
  return count || 0
}

const handleImageError = (event) => {
  event.target.src = '/placeholder-product.jpg'
}

const toggleLike = async () => {
  if (!authStore.isAuthenticated) {
    // Redirect to login or show modal
    return
  }

  likingProduct.value = true
  try {
    emit('like', props.product)
  } finally {
    likingProduct.value = false
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>



