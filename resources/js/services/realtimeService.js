/**
 * Service de mise à jour en temps réel intelligent
 * Gère le polling automatique avec différentes fréquences
 */
class RealtimeService {
  constructor() {
    this.intervals = new Map()
    this.callbacks = new Map()
    this.isActive = false
    this.userActivity = {
      lastActivity: Date.now(),
      isActive: true
    }
    
    // Fréquences de mise à jour (en millisecondes)
    this.frequencies = {
      messages: 5000,        // 5 secondes
      likes: 30000,          // 30 secondes
      views: 30000,          // 30 secondes
      followers: 60000,      // 1 minute
      notifications: 1000    // 1 seconde
    }
    
    this.init()
  }
  
  /**
   * Initialise le service
   */
  init() {
    // Détecter l'activité de l'utilisateur
    this.setupActivityDetection()
    
    // Démarrer le service
    this.start()
  }
  
  /**
   * Configure la détection d'activité utilisateur
   */
  setupActivityDetection() {
    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click']
    
    events.forEach(event => {
      document.addEventListener(event, () => {
        this.userActivity.lastActivity = Date.now()
        this.userActivity.isActive = true
      }, { passive: true })
    })
    
    // Vérifier l'activité toutes les 30 secondes
    setInterval(() => {
      const now = Date.now()
      const timeSinceLastActivity = now - this.userActivity.lastActivity
      
      // Considérer l'utilisateur inactif après 5 minutes
      if (timeSinceLastActivity > 5 * 60 * 1000) {
        this.userActivity.isActive = false
        this.pauseInactiveUpdates()
      } else {
        this.userActivity.isActive = true
        this.resumeUpdates()
      }
    }, 30000)
  }
  
  /**
   * Démarrer le service
   */
  start() {
    this.isActive = true
  }
  
  /**
   * Arrêter le service
   */
  stop() {
    this.isActive = false
    this.clearAllIntervals()
  }
  
  /**
   * Mettre en pause les mises à jour pour utilisateur inactif
   */
  pauseInactiveUpdates() {
    if (!this.userActivity.isActive) {
      this.clearAllIntervals()
    }
  }
  
  /**
   * Reprendre les mises à jour
   */
  resumeUpdates() {
    if (this.userActivity.isActive && this.isActive) {
      this.restartAllIntervals()
    }
  }
  
  /**
   * S'abonner aux mises à jour temps réel
   * @param {string} type - Type de données (messages, likes, views, followers)
   * @param {Function} callback - Fonction à appeler lors des mises à jour
   * @param {number} customFrequency - Fréquence personnalisée (optionnel)
   */
  subscribe(type, callback, customFrequency = null) {
    if (!this.frequencies[type]) {
      console.warn(`⚠️ Type de mise à jour non reconnu: ${type}`)
      return
    }
    
    const frequency = customFrequency || this.frequencies[type]
    
    // Stocker le callback
    if (!this.callbacks.has(type)) {
      this.callbacks.set(type, [])
    }
    this.callbacks.get(type).push(callback)
    
    // Démarrer l'intervalle si pas déjà actif
    if (!this.intervals.has(type)) {
      this.startInterval(type, frequency)
    }
  }
  
  /**
   * Se désabonner des mises à jour temps réel
   * @param {string} type - Type de données
   * @param {Function} callback - Callback à retirer (optionnel)
   */
  unsubscribe(type, callback = null) {
    if (callback) {
      // Retirer un callback spécifique
      const callbacks = this.callbacks.get(type)
      if (callbacks) {
        const index = callbacks.indexOf(callback)
        if (index > -1) {
          callbacks.splice(index, 1)
        }
        
        // Si plus de callbacks, arrêter l'intervalle
        if (callbacks.length === 0) {
          this.stopInterval(type)
        }
      }
    } else {
      // Retirer tous les callbacks pour ce type
      this.callbacks.delete(type)
      this.stopInterval(type)
    }
  }
  
  /**
   * Démarrer un intervalle pour un type spécifique
   * @param {string} type - Type de données
   * @param {number} frequency - Fréquence en millisecondes
   */
  startInterval(type, frequency) {
    if (this.intervals.has(type)) {
      return // Déjà actif
    }
    
    const interval = setInterval(() => {
      if (this.isActive && this.userActivity.isActive) {
        this.executeCallbacks(type)
      }
    }, frequency)
    
    this.intervals.set(type, interval)
  }
  
  /**
   * Arrêter un intervalle spécifique
   * @param {string} type - Type de données
   */
  stopInterval(type) {
    const interval = this.intervals.get(type)
    if (interval) {
      clearInterval(interval)
      this.intervals.delete(type)
    }
  }
  
  /**
   * Exécuter tous les callbacks pour un type
   * @param {string} type - Type de données
   */
  executeCallbacks(type) {
    const callbacks = this.callbacks.get(type)
    if (callbacks) {
      callbacks.forEach(callback => {
        try {
          callback()
        } catch (error) {
          console.error(`❌ Erreur dans le callback ${type}:`, error)
        }
      })
    }
  }
  
  /**
   * Arrêter tous les intervalles
   */
  clearAllIntervals() {
    this.intervals.forEach((interval, type) => {
      clearInterval(interval)
    })
    this.intervals.clear()
  }
  
  /**
   * Redémarrer tous les intervalles
   */
  restartAllIntervals() {
    this.clearAllIntervals()
    
    // Redémarrer avec les callbacks existants
    this.callbacks.forEach((callbacks, type) => {
      if (callbacks.length > 0) {
        this.startInterval(type, this.frequencies[type])
      }
    })
  }
  
  /**
   * Forcer une mise à jour immédiate
   * @param {string} type - Type de données
   */
  forceUpdate(type) {
    this.executeCallbacks(type)
  }
  
  /**
   * Obtenir le statut du service
   */
  getStatus() {
    return {
      isActive: this.isActive,
      userActivity: this.userActivity,
      activeIntervals: Array.from(this.intervals.keys()),
      activeCallbacks: Object.fromEntries(
        Array.from(this.callbacks.entries()).map(([type, callbacks]) => [
          type, 
          callbacks.length
        ])
      )
    }
  }
}

// Créer une instance singleton
const realtimeService = new RealtimeService()

// Exporter le service
export default realtimeService

// Exporter aussi la classe pour les tests
export { RealtimeService }
