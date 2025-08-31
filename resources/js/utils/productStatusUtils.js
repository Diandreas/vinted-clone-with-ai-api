/**
 * Utilitaires pour gérer le statut des produits
 */

// Statuts des produits
export const PRODUCT_STATUS = {
  DRAFT: 'draft',
  ACTIVE: 'active',
  SOLD: 'sold',
  RESERVED: 'reserved',
  REMOVED: 'removed',
  INACTIVE: 'inactive'
}

// Vérifier si un produit est indisponible
export const isProductUnavailable = (product) => {
  if (!product || !product.status) return false
  
  return [
    PRODUCT_STATUS.SOLD,
    PRODUCT_STATUS.REMOVED,
    PRODUCT_STATUS.INACTIVE
  ].includes(product.status)
}

// Vérifier si un produit est vendu
export const isProductSold = (product) => {
  return product?.status === PRODUCT_STATUS.SOLD
}

// Vérifier si un produit est supprimé
export const isProductRemoved = (product) => {
  return product?.status === PRODUCT_STATUS.REMOVED
}

// Vérifier si un produit est désactivé
export const isProductInactive = (product) => {
  return product?.status === PRODUCT_STATUS.INACTIVE
}

// Vérifier si un produit est réservé
export const isProductReserved = (product) => {
  return product?.status === PRODUCT_STATUS.RESERVED
}

// Vérifier si un produit est actif
export const isProductActive = (product) => {
  return product?.status === PRODUCT_STATUS.ACTIVE
}

// Obtenir la classe CSS pour le statut du produit
export const getProductStatusClass = (product) => {
  if (!product?.status) return 'status-default'
  
  switch (product.status) {
    case PRODUCT_STATUS.SOLD:
      return 'status-sold'
    case PRODUCT_STATUS.REMOVED:
      return 'status-removed'
    case PRODUCT_STATUS.INACTIVE:
      return 'status-inactive'
    case PRODUCT_STATUS.RESERVED:
      return 'status-reserved'
    case PRODUCT_STATUS.ACTIVE:
      return 'status-active'
    default:
      return 'status-default'
  }
}

// Obtenir le texte du statut du produit
export const getProductStatusText = (product) => {
  if (!product?.status) return 'Inconnu'
  
  switch (product.status) {
    case PRODUCT_STATUS.SOLD:
      return 'Vendu'
    case PRODUCT_STATUS.REMOVED:
      return 'Supprimé'
    case PRODUCT_STATUS.INACTIVE:
      return 'Désactivé'
    case PRODUCT_STATUS.RESERVED:
      return 'Réservé'
    case PRODUCT_STATUS.ACTIVE:
      return 'Actif'
    case PRODUCT_STATUS.DRAFT:
      return 'Brouillon'
    default:
      return 'Inconnu'
  }
}

// Obtenir la description du statut du produit
export const getProductStatusDescription = (product) => {
  if (!product?.status) return 'Statut inconnu'
  
  switch (product.status) {
    case PRODUCT_STATUS.SOLD:
      return 'Ce produit a été vendu'
    case PRODUCT_STATUS.REMOVED:
      return 'Ce produit a été supprimé'
    case PRODUCT_STATUS.INACTIVE:
      return 'Ce produit est désactivé'
    case PRODUCT_STATUS.RESERVED:
      return 'Ce produit est réservé'
    case PRODUCT_STATUS.ACTIVE:
      return 'Ce produit est disponible'
    case PRODUCT_STATUS.DRAFT:
      return 'Ce produit est en brouillon'
    default:
      return 'Statut inconnu'
  }
}

// Obtenir la couleur du statut du produit
export const getProductStatusColor = (product) => {
  if (!product?.status) return { bg: '#f3f4f6', text: '#6b7280' }
  
  switch (product.status) {
    case PRODUCT_STATUS.SOLD:
      return { bg: '#fef2f2', text: '#dc2626' }
    case PRODUCT_STATUS.REMOVED:
      return { bg: '#fef2f2', text: '#dc2626' }
    case PRODUCT_STATUS.INACTIVE:
      return { bg: '#f3e8ff', text: '#7c3aed' }
    case PRODUCT_STATUS.RESERVED:
      return { bg: '#fffbeb', text: '#d97706' }
    case PRODUCT_STATUS.ACTIVE:
      return { bg: '#f0fdf4', text: '#16a34a' }
    case PRODUCT_STATUS.DRAFT:
      return { bg: '#f3f4f6', text: '#6b7280' }
    default:
      return { bg: '#f3f4f6', text: '#6b7280' }
  }
}

// Obtenir les classes CSS pour le grisage
export const getProductGrayscaleClasses = (product) => {
  if (!isProductUnavailable(product)) return {}
  
  return {
    'opacity-60': true,
    'grayscale': true,
    'cursor-not-allowed': true
  }
}

// Obtenir les classes CSS pour l'image du produit
export const getProductImageClasses = (product, baseClasses = '') => {
  const classes = [baseClasses]
  
  if (isProductUnavailable(product)) {
    classes.push('grayscale')
  }
  
  return classes.join(' ')
}

// Vérifier si une conversation doit être grisée
export const shouldGrayConversation = (conversation) => {
  if (!conversation?.product) return false
  return isProductUnavailable(conversation.product)
}

// Obtenir le message d'indisponibilité pour l'utilisateur
export const getUnavailableMessage = (product) => {
  if (!isProductUnavailable(product)) return null
  
  const status = product.status
  const title = getProductStatusText(product)
  const description = getProductStatusDescription(product)
  
  return {
    title,
    description,
    status,
    isUnavailable: true
  }
}

// Formater le statut pour l'affichage
export const formatProductStatus = (product) => {
  return {
    text: getProductStatusText(product),
    class: getProductStatusClass(product),
    color: getProductStatusColor(product),
    description: getProductStatusDescription(product),
    isUnavailable: isProductUnavailable(product)
  }
}




