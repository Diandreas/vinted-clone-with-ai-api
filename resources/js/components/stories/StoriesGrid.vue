<template>
  <div class="stories-grid">
    <!-- Stories Header -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-white">Stories</h3>
      <button
        v-if="canCreateStory"
        @click="createStory"
        class="text-red-400 hover:text-red-300 text-sm font-medium transition-colors"
      >
        + Ajouter
      </button>
    </div>

    <!-- Stories Container -->
    <div v-if="stories.length === 0" class="text-center py-12">
      <div class="w-16 h-16 bg-gray-800 rounded-full mx-auto mb-4 flex items-center justify-center">
        <CameraIcon class="w-8 h-8 text-gray-400" />
      </div>
      <h4 class="text-gray-300 font-medium mb-2">Aucun story</h4>
      <p class="text-gray-500 text-sm">Partagez des moments de votre vie quotidienne</p>
    </div>

    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
      <div
        v-for="story in stories"
        :key="story.id"
        class="story-item group cursor-pointer"
        @click="viewStory(story)"
      >
        <!-- Story Circle -->
        <div class="relative">
          <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto">
            <img
              :src="story.media_url || story.thumbnail_url"
              :alt="story.caption || 'Story'"
              class="w-full h-full object-cover rounded-full border-2 border-gray-700 group-hover:border-red-500 transition-colors duration-200"
            />
            
            <!-- Story Status -->
            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-black"></div>
            
            <!-- Story Duration -->
            <div class="absolute top-1 left-1 bg-black/70 text-white text-xs px-1 py-0.5 rounded">
              {{ formatDuration(story.duration || 15) }}s
            </div>
          </div>
          
          <!-- Story Caption -->
          <p class="text-xs text-gray-400 text-center mt-2 line-clamp-2 group-hover:text-white transition-colors">
            {{ story.caption || 'Story' }}
          </p>
        </div>
      </div>
    </div>

    <!-- View More Button -->
    <div v-if="stories.length > 6" class="text-center mt-6">
      <button
        @click="viewAllStories"
        class="text-red-400 hover:text-red-300 text-sm font-medium transition-colors"
      >
        Voir tous les stories ({{ stories.length }})
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { CameraIcon } from 'lucide-vue-next'

const props = defineProps({
  stories: {
    type: Array,
    default: () => []
  },
  userId: {
    type: [Number, String],
    required: true
  }
})

const router = useRouter()
const authStore = useAuthStore()

// Computed
const canCreateStory = computed(() => {
  return authStore.isAuthenticated && authStore.user?.id === props.userId
})

// Methods
function createStory() {
  router.push('/stories/create')
}

function viewStory(story) {
  router.push(`/stories/${story.id}`)
}

function viewAllStories() {
  router.push(`/users/${props.userId}/stories`)
}

function formatDuration(seconds) {
  if (seconds < 60) return seconds
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.story-item:hover .story-item img {
  transform: scale(1.05);
}

.story-item img {
  transition: transform 0.2s ease-in-out;
}
</style>
