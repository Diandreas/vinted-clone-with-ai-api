/**
 * Utilitaires pour le formatage du temps des messages
 */

/**
 * Formate une date en affichant le temps écoulé de manière précise
 * @param {string|Date} dateString - La date à formater
 * @param {boolean} short - Si true, utilise des versions courtes (ex: "2min" au lieu de "2 minutes")
 * @returns {string} Le temps formaté (ex: "il y a 2 minutes", "il y a 1 heure", "Hier")
 */
export const formatMessageTime = (dateString, short = false) => {
  if (!dateString) return ''
  
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = now - date
  const diffMinutes = Math.floor(diffTime / (1000 * 60))
  const diffHours = Math.floor(diffTime / (1000 * 60 * 60))
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffMinutes < 1) {
    return short ? 'À l\'instant' : 'À l\'instant'
  } else if (diffMinutes < 60) {
    if (short) {
      return `${diffMinutes}min`
    }
    return `il y a ${diffMinutes} minute${diffMinutes > 1 ? 's' : ''}`
  } else if (diffHours < 24) {
    if (short) {
      return `${diffHours}h`
    }
    return `il y a ${diffHours} heure${diffHours > 1 ? 's' : ''}`
  } else if (diffDays === 1) {
    return 'Hier'
  } else if (diffDays < 7) {
    if (short) {
      return `${diffDays}j`
    }
    return `il y a ${diffDays} jour${diffDays > 1 ? 's' : ''}`
  } else {
    return date.toLocaleDateString('fr-FR')
  }
}

/**
 * Formate une date pour l'activité récente (plus détaillée)
 * @param {string|Date} dateString - La date à formater
 * @returns {string} Le temps formaté pour l'activité
 */
export const formatActivityTime = (dateString) => {
  if (!dateString) return 'Aucune activité'
  return formatMessageTime(dateString, false)
}

/**
 * Formate une date pour l'affichage compact (versions courtes)
 * @param {string|Date} dateString - La date à formater
 * @returns {string} Le temps formaté en version compacte
 */
export const formatCompactTime = (dateString) => {
  if (!dateString) return ''
  return formatMessageTime(dateString, true)
}

/**
 * Vérifie si une date est récente (moins de 1 heure)
 * @param {string|Date} dateString - La date à vérifier
 * @returns {boolean} True si la date est récente
 */
export const isRecentMessage = (dateString) => {
  if (!dateString) return false
  
  const date = new Date(dateString)
  const now = new Date()
  const diffTime = now - date
  const diffHours = diffTime / (1000 * 60 * 60)
  
  return diffHours < 1
}

/**
 * Vérifie si une date est aujourd'hui
 * @param {string|Date} dateString - La date à vérifier
 * @returns {boolean} True si la date est aujourd'hui
 */
export const isToday = (dateString) => {
  if (!dateString) return false
  
  const date = new Date(dateString)
  const now = new Date()
  
  return date.toDateString() === now.toDateString()
}

/**
 * Vérifie si une date est hier
 * @param {string|Date} dateString - La date à vérifier
 * @returns {boolean} True si la date est hier
 */
export const isYesterday = (dateString) => {
  if (!dateString) return false
  
  const date = new Date(dateString)
  const yesterday = new Date()
  yesterday.setDate(yesterday.getDate() - 1)
  
  return date.toDateString() === yesterday.toDateString()
}
