import { ref, onUnmounted } from 'vue'

// Singleton : un seul interval partagé par toutes les instances
const now = ref(Date.now())
let instanceCount = 0
let intervalId = null

export function useNow(intervalMs = 60000) {
  instanceCount++

  if (!intervalId) {
    intervalId = setInterval(() => {
      now.value = Date.now()
    }, intervalMs)
  }

  onUnmounted(() => {
    instanceCount--
    if (instanceCount === 0 && intervalId) {
      clearInterval(intervalId)
      intervalId = null
    }
  })

  return { now }
}
