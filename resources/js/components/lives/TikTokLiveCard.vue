<template>
  <div 
    class="live-card group relative bg-gray-900 rounded-xl overflow-hidden cursor-pointer transform transition-all duration-300 hover:scale-105 hover:shadow-2xl"
    @click="goToLive"
  >
    <!-- Live Thumbnail -->
    <div class="relative aspect-video overflow-hidden">
      <img
        :src="live.thumbnail_url || live.cover_image || '/placeholder-live.jpg'"
        :alt="live.title"
        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
        @error="onImageError"
      />
      
      <!-- Live Badge -->
      <div class="absolute top-3 left-3">
        <div class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg flex items-center space-x-2">
          <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
          <span>EN DIRECT</span>
        </div>
      </div>
      
      <!-- Viewers Count -->
      <div class="absolute top-3 right-3">
        <div class="bg-black/80 backdrop-blur-sm rounded-full px-3 py-1 text-white text-xs font-medium flex items-center space-x-1">
          <EyeIcon class="w-3 h-3" />
          <span>{{ formatNumber(live.viewers_count || 0) }}</span>
        </div>
      </div>
      
      <!-- Duration -->
      <div class="absolute bottom-3 left-3">
        <div class="bg-black/80 backdrop-blur-sm rounded-full px-2 py-1 text-white text-xs font-medium">
          {{ formatDuration(live.duration || 0) }}
        </div>
      </div>
      
      <!-- Gradient Overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
    </div>

    <!-- Live Info -->
    <div class="p-4">
      <h3 class="font-bold text-white text-sm line-clamp-2 mb-2 group-hover:text-red-400 transition-colors duration-200">
        {{ live.title }}
      </h3>
      
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-2">
          <span class="text-xs text-gray-400 bg-gray-800 px-2 py-1 rounded-full">
            {{ live.category?.name || 'Général' }}
          </span>
          <span v-if="live.is_featured" class="text-xs text-yellow-400 bg-yellow-900/20 px-2 py-1 rounded-full">
            ⭐ Mis en avant
          </span>
        </div>
        
        <div class="flex items-center space-x-2">
          <span class="text-xs text-gray-400">{{ formatDate(live.scheduled_at || live.created_at) }}</span>
        </div>
      </div>
      
      <!-- Live Meta -->
      <div class="flex items-center justify-between text-xs text-gray-400">
        <div class="flex items-center space-x-3">
          <span v-if="live.location" class="flex items-center">
            <MapPinIcon class="w-3 h-3 mr-1" />
            {{ live.location }}
          </span>
          <span v-if="live.max_viewers" class="flex items-center">
            <UsersIcon class="w-3 h-3 mr-1" />
            Max: {{ formatNumber(live.max_viewers) }}
          </span>
        </div>
        
        <div class="flex items-center space-x-2">
          <span v-if="live.likes_count" class="flex items-center">
            <HeartIcon class="w-3 h-3 mr-1 text-red-400" />
            {{ formatNumber(live.likes_count) }}
          </span>
          <span v-if="live.comments_count" class="flex items-center">
            <MessageCircleIcon class="w-3 h-3 mr-1 text-blue-400" />
            {{ formatNumber(live.comments_count) }}
          </span>
        </div>
      </div>
    </div>

    <!-- Hover Effect Border -->
    <div class="absolute inset-0 border-2 border-transparent group-hover:border-red-500 rounded-xl transition-all duration-300 opacity-0 group-hover:opacity-100"></div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import {
  EyeIcon,
  MapPinIcon,
  UsersIcon,
  HeartIcon,
  MessageCircleIcon
} from 'lucide-vue-next'

const props = defineProps({
  live: {
    type: Object,
    required: true
  }
})

const router = useRouter()

// Methods
function onImageError(event) {
  event.target.src = '/placeholder-live.jpg'
}

function goToLive() {
  router.push(`/lives/${props.live.id}`)
}

function formatNumber(num) {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  } else if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num.toString()
}

function formatDuration(seconds) {
  if (!seconds) return '--'
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  
  if (hours > 0) {
    return `${hours}h ${minutes}m`
  }
  return `${minutes}m`
}

function formatDate(dateString) {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) return 'Aujourd\'hui'
  if (diffDays === 2) return 'Hier'
  if (diffDays <= 7) return `Il y a ${diffDays - 1} jours`
  if (diffDays <= 30) return `Il y a ${Math.floor(diffDays / 7)} semaines`
  if (diffDays <= 365) return `Il y a ${Math.floor(diffDays / 30)} mois`
  return `Il y a ${Math.floor(diffDays / 365)} ans`
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Custom animations */
@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);
  }
  50% {
    box-shadow: 0 0 30px rgba(239, 68, 68, 0.6);
  }
}

.live-card:hover {
  animation: pulse-glow 2s ease-in-out infinite;
}
</style>
