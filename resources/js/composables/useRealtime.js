/**
 * Composable pour intégrer le service temps réel
 * Compatible avec la Composition API de Vue 3
 */
import { ref, onUnmounted } from 'vue'
import realtimeService from '@/services/realtimeService'

export function useRealtime() {
  const realtimeSubscriptions = ref([])
  
  /**
   * S'abonner aux mises à jour temps réel
   * @param {string} type - Type de données (messages, likes, views, followers)
   * @param {Function} callback - Fonction à appeler lors des mises à jour
   * @param {number} customFrequency - Fréquence personnalisée (optionnel)
   */
  const subscribeToRealtime = (type, callback, customFrequency = null) => {
    const subscription = { type, callback }
    realtimeSubscriptions.value.push(subscription)
    
    realtimeService.subscribe(type, callback, customFrequency)
  }
  
  /**
   * Se désabonner des mises à jour temps réel
   * @param {string} type - Type de données
   * @param {Function} callback - Callback spécifique (optionnel)
   */
  const unsubscribeFromRealtime = (type, callback = null) => {
    if (callback) {
      // Retirer un callback spécifique
      const index = realtimeSubscriptions.value.findIndex(
        sub => sub.type === type && sub.callback === callback
      )
      if (index > -1) {
        realtimeSubscriptions.value.splice(index, 1)
      }
    } else {
      // Retirer tous les callbacks pour ce type
      realtimeSubscriptions.value = realtimeSubscriptions.value.filter(
        sub => sub.type !== type
      )
    }
    
    realtimeService.unsubscribe(type, callback)
  }
  
  /**
   * Se désabonner de toutes les mises à jour
   */
  const unsubscribeFromAllRealtime = () => {
    realtimeSubscriptions.value.forEach(subscription => {
      realtimeService.unsubscribe(subscription.type, subscription.callback)
    })
    realtimeSubscriptions.value = []
  }
  
  /**
   * Forcer une mise à jour immédiate
   * @param {string} type - Type de données
   */
  const forceRealtimeUpdate = (type) => {
    realtimeService.forceUpdate(type)
  }
  
  /**
   * Obtenir le statut du service temps réel
   */
  const getRealtimeStatus = () => {
    return realtimeService.getStatus()
  }
  
  // Nettoyage automatique à la destruction du composant
  onUnmounted(() => {
    unsubscribeFromAllRealtime()
  })
  
  return {
    subscribeToRealtime,
    unsubscribeFromRealtime,
    unsubscribeFromAllRealtime,
    forceRealtimeUpdate,
    getRealtimeStatus,
    realtimeSubscriptions
  }
}
