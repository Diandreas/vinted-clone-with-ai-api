<template>
  <div id="app" class="min-h-screen bg-gradient-to-br from-gray-50 via-primary-50/20 to-green-50/30">
    <!-- Navigation -->
    <NavBar v-if="!isAuthPage" />
    
    <!-- Main Content -->
    <main class="flex-1" :class="{ 'pb-20': !isAuthPage }">
      <RouterView />
    </main>
    
    <!-- Mobile Tab Bar -->
    <MobileTabBar v-if="!isAuthPage" />
    
    <!-- Toast Notifications -->
    <NotificationToast />
    
    <!-- PWA Install Prompt -->
    <PWAInstallPrompt />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import NavBar from '@/components/layout/NavBar.vue'
import MobileTabBar from '@/components/layout/MobileTabBar.vue'
import NotificationToast from '@/components/ui/NotificationToast.vue'
import PWAInstallPrompt from '@/components/ui/PWAInstallPrompt.vue'

const route = useRoute()

// Hide navbar on auth pages
const isAuthPage = computed(() => {
  return ['login', 'register', 'forgot-password'].includes(route.name)
})
</script>

<style scoped>
/* Custom scrollbar avec thème vert */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
  background: #86efac;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #4ade80;
}

/* Smooth transitions globales */
* {
  transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
}
</style>

