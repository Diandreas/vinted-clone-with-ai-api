# Système de Grisage des Produits - Vinted Clone

## Vue d'ensemble

Ce système permet de griser automatiquement les produits et conversations lorsque les produits sont vendus, supprimés ou désactivés. Cela améliore l'expérience utilisateur en indiquant clairement quels produits ne sont plus disponibles.

## Fonctionnalités

### 🎯 Grisage Automatique
- **Produits vendus** : Grisage avec badge "Vendu"
- **Produits supprimés** : Grisage avec badge "Supprimé"  
- **Produits désactivés** : Grisage avec badge "Désactivé"
- **Produits réservés** : Badge "Réservé" sans grisage

### 🎨 Effets Visuels
- **Opacité réduite** : 60% d'opacité pour les produits indisponibles
- **Filtre grayscale** : Images en noir et blanc
- **Curseur désactivé** : `cursor: not-allowed`
- **Overlay informatif** : Message d'indisponibilité sur l'image

## Composants Modifiés

### 1. ProductCard.vue
```vue
<template>
  <div 
    class="bg-white rounded-lg border border-gray-200"
    :class="{
      'opacity-60 grayscale': isProductUnavailable,
      'cursor-not-allowed': isProductUnavailable
    }"
  >
    <!-- Image avec grisage conditionnel -->
    <ProductImage
      :class="{ 'grayscale': isProductUnavailable }"
    />
    
    <!-- Overlay d'indisponibilité -->
    <div v-if="isProductUnavailable" class="unavailable-overlay">
      <div class="overlay-content">
        <div class="overlay-text">{{ getUnavailableText() }}</div>
        <div class="overlay-description">{{ getUnavailableDescription() }}</div>
      </div>
    </div>
  </div>
</template>
```

### 2. ProductDiscussions.vue
```vue
<template>
  <div 
    class="conversation-card"
    :class="{
      'opacity-60 grayscale': isProductUnavailable(conversation.product),
      'cursor-not-allowed': isProductUnavailable(conversation.product)
    }"
  >
    <!-- Image avec grisage -->
    <img :class="{ 'grayscale': isProductUnavailable(conversation.product) }" />
    
    <!-- Badge de statut -->
    <div v-if="isProductUnavailable(conversation.product)" class="product-status-badge">
      <span :class="['status-indicator', getProductStatusClass(conversation.product)]">
        {{ getProductStatusText(conversation.product) }}
      </span>
    </div>
  </div>
</template>
```

### 3. ConversationDetail.vue
```vue
<template>
  <div class="product-image-container relative">
    <ProductImage
      :class="{ 'grayscale': isProductUnavailable }"
    />
    
    <!-- Overlay d'indisponibilité -->
    <div v-if="isProductUnavailable" class="unavailable-overlay">
      <div class="overlay-content">
        <div class="overlay-text">{{ getUnavailableText() }}</div>
      </div>
    </div>
  </div>
</template>
```

## Utilisation des Utilitaires

### Import des fonctions
```javascript
import { 
  isProductUnavailable, 
  getProductStatusClass, 
  getProductStatusText,
  getProductGrayscaleClasses 
} from '@/utils/productStatusUtils'
```

### Vérification du statut
```javascript
// Vérifier si un produit est indisponible
if (isProductUnavailable(product)) {
  // Appliquer le grisage
}

// Obtenir les classes CSS pour le grisage
const grayscaleClasses = getProductGrayscaleClasses(product)
// Retourne: { 'opacity-60': true, 'grayscale': true, 'cursor-not-allowed': true }
```

### Classes CSS conditionnelles
```vue
<template>
  <div 
    class="product-card"
    :class="getProductGrayscaleClasses(product)"
  >
    <!-- Contenu du produit -->
  </div>
</template>
```

## Styles CSS

### Classes de base
```css
/* Grisage des produits */
.product-unavailable {
  opacity: 0.6;
  filter: grayscale(100%);
  cursor: not-allowed;
}

/* Overlay d'indisponibilité */
.product-unavailable-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  z-index: 1;
}
```

### Badges de statut
```css
.status-sold {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.status-removed {
  background: #fef2f2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.status-inactive {
  background: #f3e8ff;
  color: #7c3aed;
  border: 1px solid #c4b5fd;
}
```

## Configuration

### Statuts supportés
```javascript
export const PRODUCT_STATUS = {
  DRAFT: 'draft',      // Brouillon
  ACTIVE: 'active',    // Actif
  SOLD: 'sold',        // Vendu
  RESERVED: 'reserved', // Réservé
  REMOVED: 'removed',   // Supprimé
  INACTIVE: 'inactive'  // Désactivé
}
```

### Personnalisation des couleurs
```javascript
export const getProductStatusColor = (product) => {
  switch (product.status) {
    case 'sold':
      return { bg: '#fef2f2', text: '#dc2626' }
    case 'removed':
      return { bg: '#fef2f2', text: '#dc2626' }
    case 'inactive':
      return { bg: '#f3e8ff', text: '#7c3aed' }
    // ... autres statuts
  }
}
```

## Tests

### Page de test
Ouvrez `test-product-grayscale.html` dans votre navigateur pour tester :
- Changement de statut des produits
- Effet de grisage
- Overlays d'indisponibilité
- Badges de statut

### Tests automatisés
```javascript
// Test de grisage
test('should gray unavailable products', () => {
  const soldProduct = { status: 'sold' }
  expect(isProductUnavailable(soldProduct)).toBe(true)
  
  const activeProduct = { status: 'active' }
  expect(isProductUnavailable(activeProduct)).toBe(false)
})
```

## Maintenance

### Ajout de nouveaux statuts
1. Ajouter le statut dans `PRODUCT_STATUS`
2. Mettre à jour les fonctions de vérification
3. Ajouter les styles CSS correspondants
4. Tester avec la page de test

### Personnalisation des couleurs
1. Modifier `getProductStatusColor()` dans `productStatusUtils.js`
2. Mettre à jour les variables CSS dans `product-status.css`
3. Vérifier la cohérence visuelle

## Dépannage

### Problèmes courants
- **Grisage ne s'applique pas** : Vérifier que `product.status` est défini
- **Styles manquants** : S'assurer que `product-status.css` est importé
- **Overlay invisible** : Vérifier le z-index et la position relative

### Debug
```javascript
// Activer les logs de debug
console.log('Product status:', product.status)
console.log('Is unavailable:', isProductUnavailable(product))
console.log('Grayscale classes:', getProductGrayscaleClasses(product))
```

## Support

Pour toute question ou problème :
1. Vérifier la documentation
2. Consulter les tests
3. Vérifier la console du navigateur
4. Contacter l'équipe de développement




