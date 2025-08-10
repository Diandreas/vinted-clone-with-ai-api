<template>
  <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow duration-200">
    <!-- Live Thumbnail -->
    <div class="relative aspect-video overflow-hidden rounded-t-lg">
      <img
        :src="live.thumbnail || '/placeholder-live.jpg'"
        :alt="live.title"
        class="w-full h-full object-cover"
        @error="handleImageError"
      />
      
      <!-- Live Status -->
      <div class="absolute top-2 left-2">
        <span
          v-if="live.status === 'live'"
          class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full flex items-center space-x-1"
        >
          <div class="w-2 h-2 bg-red-600 rounded-full animate-pulse"></div>
          <span>EN DIRECT</span>
        </span>
        <span
          v-else-if="live.status === 'scheduled'"
          class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full"
        >
          Programmé
        </span>
      </div>
      
      <!-- Viewers Count -->
      <div v-if="live.status === 'live'" class="absolute top-2 right-2">
        <span class="bg-black bg-opacity-60 text-white text-xs px-2 py-1 rounded flex items-center space-x-1">
          <EyeIcon class="w-3 h-3" />
          <span>{{ formatCount(live.viewers_count) }}</span>
        </span>
      </div>
    </div>

    <!-- Live Info -->
    <div class="p-4">
      <div class="flex items-start space-x-3">
        <!-- User Avatar -->
        <img
          :src="live.user?.avatar || '/default-avatar.png'"
          :alt="live.user?.name"
          class="w-10 h-10 rounded-full object-cover flex-shrink-0"
        />
        
        <!-- Content -->
        <div class="flex-1 min-w-0">
          <h3 class="font-semibold text-gray-900 text-sm line-clamp-2 mb-1">
            {{ live.title }}
          </h3>
          <p class="text-sm text-gray-600 mb-2">{{ live.user?.name }}</p>
          
          <!-- Stats -->
          <div class="flex items-center space-x-4 text-xs text-gray-500">
            <div class="flex items-center space-x-1">
              <HeartIcon class="w-3 h-3" />
              <span>{{ formatCount(live.likes_count) }}</span>
            </div>
            <div class="flex items-center space-x-1">
              <MessageCircleIcon class="w-3 h-3" />
              <span>{{ formatCount(live.comments_count) }}</span>
            </div>
            <div v-if="live.status !== 'live'" class="flex items-center space-x-1">
              <ClockIcon class="w-3 h-3" />
              <span>{{ formatDate(live.scheduled_at || live.started_at) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { formatDistanceToNow } from 'date-fns'
import { fr } from 'date-fns/locale'
import {
  EyeIcon,
  HeartIcon,
  MessageCircleIcon,
  ClockIcon
} from 'lucide-vue-next'

const props = defineProps({
  live: {
    type: Object,
    required: true
  }
})

const formatCount = (count) => {
  if (count >= 1000) {
    return Math.floor(count / 1000) + 'k'
  }
  return count || 0
}

const formatDate = (date) => {
  if (!date) return ''
  return formatDistanceToNow(new Date(date), {
    addSuffix: true,
    locale: fr
  })
}

const handleImageError = (event) => {
  event.target.src = '/placeholder-live.jpg'
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



