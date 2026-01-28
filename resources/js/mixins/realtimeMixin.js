/**
 * Mixin pour intégrer le service temps réel
 * Permet aux composants de s'abonner facilement aux mises à jour
 */
import realtimeService from '@/services/realtimeService'

export default {
  data() {
    return {
      realtimeSubscriptions: []
    }
  },

  methods: {
    /**
     * S'abonner aux mises à jour temps réel
     * @param {string} type - Type de données (messages, likes, views, followers)
     * @param {Function} callback - Fonction à appeler lors des mises à jour
     * @param {number} customFrequency - Fréquence personnalisée (optionnel)
     */
    subscribeToRealtime(type, callback, customFrequency = null) {
      const subscription = { type, callback }
      this.realtimeSubscriptions.push(subscription)

      realtimeService.subscribe(type, callback, customFrequency)


    },

    /**
     * Se désabonner des mises à jour temps réel
     * @param {string} type - Type de données
     * @param {Function} callback - Callback spécifique (optionnel)
     */
    unsubscribeFromRealtime(type, callback = null) {
      if (callback) {
        // Retirer un callback spécifique
        const index = this.realtimeSubscriptions.findIndex(
          sub => sub.type === type && sub.callback === callback
        )
        if (index > -1) {
          this.realtimeSubscriptions.splice(index, 1)
        }
      } else {
        // Retirer tous les callbacks pour ce type
        this.realtimeSubscriptions = this.realtimeSubscriptions.filter(
          sub => sub.type !== type
        )
      }

      realtimeService.unsubscribe(type, callback)
    },

    /**
     * Se désabonner de toutes les mises à jour
     */
    unsubscribeFromAllRealtime() {
      this.realtimeSubscriptions.forEach(subscription => {
        realtimeService.unsubscribe(subscription.type, subscription.callback)
      })
      this.realtimeSubscriptions = []
    },

    /**
     * Forcer une mise à jour immédiate
     * @param {string} type - Type de données
     */
    forceRealtimeUpdate(type) {
      realtimeService.forceUpdate(type)
    },

    /**
     * Obtenir le statut du service temps réel
     */
    getRealtimeStatus() {
      return realtimeService.getStatus()
    }
  },

  beforeUnmount() {
    // Se désabonner automatiquement à la destruction du composant
    this.unsubscribeFromAllRealtime()
  }
}



