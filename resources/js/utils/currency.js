/**
 * Utilitaires pour le formatage des devises
 */

/**
 * Formate un prix en FCFA
 * @param {number} price - Le prix à formater
 * @returns {string} Le prix formaté en FCFA
 */
export const formatPrice = (price) => {
  if (!price || price === 0) return '0 FCFA'

  try {
    // Utiliser Intl.NumberFormat si disponible
    return new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(price) + ' FCFA'
  } catch (error) {
    // Fallback simple si Intl.NumberFormat n'est pas supporté
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA'
  }
}

/**
 * Formate un prix avec décimales (pour les montants précis)
 * @param {number} price - Le prix à formater
 * @param {number} decimals - Nombre de décimales (défaut: 0)
 * @returns {string} Le prix formaté en FCFA
 */
export const formatPriceWithDecimals = (price, decimals = 0) => {
  if (!price || price === 0) return '0 FCFA'

  try {
    return new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    }).format(price) + ' FCFA'
  } catch (error) {
    // Fallback simple
    const formatted = price.toFixed(decimals)
    return formatted.replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA'
  }
}

/**
 * Formate un prix pour l'affichage court (sans "FCFA")
 * @param {number} price - Le prix à formater
 * @returns {string} Le prix formaté sans devise
 */
export const formatPriceShort = (price) => {
  if (!price || price === 0) return '0'

  try {
    return new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(price)
  } catch (error) {
    // Fallback simple
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
  }
}

/**
 * Convertit un prix d'une devise vers FCFA
 * @param {number} price - Le prix dans la devise source
 * @param {string} fromCurrency - La devise source (ex: 'EUR', 'USD')
 * @param {number} rate - Le taux de change vers FCFA
 * @returns {string} Le prix converti et formaté en FCFA
 */
export const convertAndFormatPrice = (price, fromCurrency, rate) => {
  if (!price || !rate) return '0 FCFA'

  const convertedPrice = price * rate
  return formatPrice(convertedPrice)
}

/**
 * Vérifie si un prix est valide
 * @param {any} price - Le prix à vérifier
 * @returns {boolean} True si le prix est valide
 */
export const isValidPrice = (price) => {
  return price !== null &&
         price !== undefined &&
         !isNaN(price) &&
         price >= 0
}

/**
 * Extrait la valeur numérique d'un prix formaté
 * @param {string} formattedPrice - Le prix formaté (ex: "15 000 FCFA")
 * @returns {number} La valeur numérique
 */
export const extractPriceValue = (formattedPrice) => {
  if (!formattedPrice) return 0

  // Extraire les chiffres et espaces
  const numericPart = formattedPrice.replace(/[^\d\s]/g, '').trim()

  if (!numericPart) return 0

  // Convertir en nombre
  return parseInt(numericPart.replace(/\s/g, ''), 10) || 0
}


