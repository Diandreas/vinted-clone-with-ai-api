/**
 * Utilitaires pour l'affichage des messages
 */

/**
 * Extrait le contenu lisible d'un message
 * @param {string} content - Le contenu du message (peut être du JSON)
 * @param {number} maxLength - Longueur maximale (optionnel)
 * @returns {string} - Le contenu lisible du message
 */
export function extractMessageContent(content, maxLength = null) {
  if (!content) return ''
  
  let messageText = content
  
  // Essayer de parser le JSON si c'est une chaîne JSON
  try {
    const parsed = JSON.parse(content)
    if (parsed && typeof parsed === 'object') {
      // Si c'est un objet avec une propriété 'content', l'utiliser
      if (parsed.content && typeof parsed.content === 'string') {
        messageText = parsed.content
      }
      // Si c'est un objet avec une propriété 'text', l'utiliser
      else if (parsed.text && typeof parsed.text === 'string') {
        messageText = parsed.text
      }
      // Si c'est un objet avec une propriété 'message', l'utiliser
      else if (parsed.message && typeof parsed.message === 'string') {
        messageText = parsed.message
      }
      // Sinon, essayer de convertir l'objet en chaîne lisible
      else {
        messageText = JSON.stringify(parsed, null, 2)
      }
    }
  } catch (e) {
    // Si ce n'est pas du JSON valide, utiliser le contenu tel quel
    messageText = content
  }
  
  // Appliquer la limite de longueur si spécifiée
  if (maxLength && messageText.length > maxLength) {
    messageText = messageText.substring(0, maxLength) + '...'
  }
  
  return messageText
}

/**
 * Vérifie si le contenu est du JSON
 * @param {string} content - Le contenu à vérifier
 * @returns {boolean} - True si c'est du JSON valide
 */
export function isJsonContent(content) {
  if (!content || typeof content !== 'string') return false
  
  try {
    const parsed = JSON.parse(content)
    return typeof parsed === 'object' && parsed !== null
  } catch (e) {
    return false
  }
}

/**
 * Nettoie le contenu JSON pour l'affichage
 * @param {string} content - Le contenu JSON
 * @returns {string} - Le contenu nettoyé
 */
export function cleanJsonContent(content) {
  if (!isJsonContent(content)) return content
  
  try {
    const parsed = JSON.parse(content)
    
    // Priorité aux propriétés de contenu
    if (parsed.content) return parsed.content
    if (parsed.text) return parsed.text
    if (parsed.message) return parsed.message
    
    // Si c'est un objet simple, essayer de le rendre lisible
    if (typeof parsed === 'object') {
      const values = Object.values(parsed).filter(v => typeof v === 'string')
      if (values.length > 0) return values[0]
    }
    
    return content
  } catch (e) {
    return content
  }
}

