<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <NavBar v-if="!isAuthPage" />
    
    <!-- Main Content -->
    <main class="flex-1">
      <RouterView />
    </main>
    
    <!-- Toast Notifications -->
    <NotificationToast />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import NavBar from '@/components/layout/NavBar.vue'
import NotificationToast from '@/components/ui/NotificationToast.vue'

const route = useRoute()

// Hide navbar on auth pages
const isAuthPage = computed(() => {
  return ['login', 'register', 'forgot-password'].includes(route.name)
})
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>

