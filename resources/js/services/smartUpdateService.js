/**
 * Service de mise à jour intelligente
 * Compare les données et ne met à jour que ce qui a changé
 */
class SmartUpdateService {
  constructor() {
    this.previousData = new Map()
    this.updateCallbacks = new Map()
    this.animationCallbacks = new Map()
  }
  
  /**
   * Enregistrer des données pour comparaison
   * @param {string} key - Clé unique pour identifier les données
   * @param {any} data - Données à comparer
   */
  registerData(key, data) {
    this.previousData.set(key, this.deepClone(data))
  }
  
  /**
   * Comparer et mettre à jour les données intelligemment
   * @param {string} key - Clé des données
   * @param {any} newData - Nouvelles données
   * @param {Function} updateCallback - Callback pour mettre à jour l'UI
   * @param {Function} animationCallback - Callback pour les animations
   */
  smartUpdate(key, newData, updateCallback, animationCallback = null) {
    const previousData = this.previousData.get(key)
    
    if (!previousData) {
      // Première fois, enregistrer et mettre à jour
      this.registerData(key, newData)
      updateCallback(newData, [], [], []) // new, updated, removed, unchanged
      return
    }
    
    // Comparer les données
    const comparison = this.compareData(previousData, newData)
    
    if (comparison.hasChanges) {
      // Mettre à jour l'interface intelligemment
      updateCallback(
        comparison.newItems,
        comparison.updatedItems,
        comparison.removedItems,
        comparison.unchangedItems
      )
      
      // Jouer les animations si configurées
      if (animationCallback) {
        this.playAnimations(comparison, animationCallback)
      }
      
      // Enregistrer les nouvelles données
      this.registerData(key, newData)
    }
  }
  
  /**
   * Comparer deux ensembles de données
   * @param {any} oldData - Anciennes données
   * @param {any} newData - Nouvelles données
   * @returns {Object} Résultat de la comparaison
   */
  compareData(oldData, newData) {
    // Si ce sont des tableaux, comparer élément par élément
    if (Array.isArray(oldData) && Array.isArray(newData)) {
      return this.compareArrays(oldData, newData)
    }
    
    // Si ce sont des objets, comparer les propriétés
    if (typeof oldData === 'object' && typeof newData === 'object') {
      return this.compareObjects(oldData, newData)
    }
    
    // Comparaison simple
    return {
      hasChanges: oldData !== newData,
      newItems: oldData !== newData ? [newData] : [],
      updatedItems: [],
      removedItems: [],
      unchangedItems: oldData === newData ? [oldData] : []
    }
  }
  
  /**
   * Comparer deux tableaux
   * @param {Array} oldArray - Ancien tableau
   * @param {Array} newArray - Nouveau tableau
   * @returns {Object} Résultat de la comparaison
   */
  compareArrays(oldArray, newArray) {
    const result = {
      hasChanges: false,
      newItems: [],
      updatedItems: [],
      removedItems: [],
      unchangedItems: []
    }
    
    // Créer des maps pour une recherche rapide
    const oldMap = new Map(oldArray.map(item => [this.getItemId(item), item]))
    const newMap = new Map(newArray.map(item => [this.getItemId(item), item]))
    
    // Trouver les éléments supprimés
    for (const [id, item] of oldMap) {
      if (!newMap.has(id)) {
        result.removedItems.push(item)
        result.hasChanges = true
      }
    }
    
    // Trouver les nouveaux et modifiés
    for (const [id, newItem] of newMap) {
      if (!oldMap.has(id)) {
        result.newItems.push(newItem)
        result.hasChanges = true
      } else {
        const oldItem = oldMap.get(id)
        if (this.hasItemChanged(oldItem, newItem)) {
          result.updatedItems.push({ old: oldItem, new: newItem })
          result.hasChanges = true
        } else {
          result.unchangedItems.push(newItem)
        }
      }
    }
    
    return result
  }
  
  /**
   * Comparer deux objets
   * @param {Object} oldObj - Ancien objet
   * @param {Object} newObj - Nouvel objet
   * @returns {Object} Résultat de la comparaison
   */
  compareObjects(oldObj, newObj) {
    const result = {
      hasChanges: false,
      newItems: [],
      updatedItems: [],
      removedItems: [],
      unchangedItems: []
    }
    
    const allKeys = new Set([...Object.keys(oldObj), ...Object.keys(newObj)])
    
    for (const key of allKeys) {
      const oldValue = oldObj[key]
      const newValue = newObj[key]
      
      if (!(key in oldObj)) {
        // Nouvelle propriété
        result.newItems.push({ key, value: newValue })
        result.hasChanges = true
      } else if (!(key in newObj)) {
        // Propriété supprimée
        result.removedItems.push({ key, value: oldValue })
        result.hasChanges = true
      } else if (oldValue !== newValue) {
        // Propriété modifiée
        result.updatedItems.push({ key, old: oldValue, new: newValue })
        result.hasChanges = true
      } else {
        // Propriété inchangée
        result.unchangedItems.push({ key, value: oldValue })
      }
    }
    
    return result
  }
  
  /**
   * Obtenir l'ID unique d'un élément
   * @param {any} item - Élément
   * @returns {string|number} ID unique
   */
  getItemId(item) {
    if (typeof item === 'object' && item !== null) {
      return item.id || item.uuid || item._id || JSON.stringify(item)
    }
    return item
  }
  
  /**
   * Vérifier si un élément a changé
   * @param {any} oldItem - Ancien élément
   * @param {any} newItem - Nouvel élément
   * @returns {boolean} True si modifié
   */
  hasItemChanged(oldItem, newItem) {
    if (typeof oldItem !== typeof newItem) return true
    
    if (typeof oldItem === 'object' && oldItem !== null) {
      // Comparer les propriétés importantes
      const importantKeys = ['unread_count', 'last_message', 'updated_at', 'status']
      
      for (const key of importantKeys) {
        if (oldItem[key] !== newItem[key]) {
          return true
        }
      }
      
      return false
    }
    
    return oldItem !== newItem
  }
  
  /**
   * Jouer les animations pour les changements
   * @param {Object} comparison - Résultat de la comparaison
   * @param {Function} animationCallback - Callback pour les animations
   */
  playAnimations(comparison, animationCallback) {
    // Animer les nouveaux éléments
    if (comparison.newItems.length > 0) {
      animationCallback('new', comparison.newItems)
    }
    
    // Animer les éléments modifiés
    if (comparison.updatedItems.length > 0) {
      animationCallback('updated', comparison.updatedItems)
    }
    
    // Animer les éléments supprimés
    if (comparison.removedItems.length > 0) {
      animationCallback('removed', comparison.removedItems)
    }
  }
  
  /**
   * Cloner profondément un objet
   * @param {any} obj - Objet à cloner
   * @returns {any} Clone profond
   */
  deepClone(obj) {
    if (obj === null || typeof obj !== 'object') return obj
    if (obj instanceof Date) return new Date(obj.getTime())
    if (obj instanceof Array) return obj.map(item => this.deepClone(item))
    if (typeof obj === 'object') {
      const cloned = {}
      for (const key in obj) {
        if (obj.hasOwnProperty(key)) {
          cloned[key] = this.deepClone(obj[key])
        }
      }
      return cloned
    }
    return obj
  }
  
  /**
   * Nettoyer les données enregistrées
   * @param {string} key - Clé à nettoyer (optionnel)
   */
  cleanup(key = null) {
    if (key) {
      this.previousData.delete(key)
    } else {
      this.previousData.clear()
    }
  }
}

// Créer une instance singleton
const smartUpdateService = new SmartUpdateService()

// Exporter le service
export default smartUpdateService

// Exporter aussi la classe pour les tests
export { SmartUpdateService }
