# 🚀 Système Temps Réel - Documentation

## 📋 Vue d'ensemble

Le système temps réel permet de mettre à jour automatiquement les données de l'application sans avoir besoin de rafraîchir la page. Il utilise un système de polling intelligent qui s'adapte à l'activité de l'utilisateur.

## ⚡ Fonctionnalités

- **🕐 Polling automatique** avec différentes fréquences selon le type de données
- **👁️ Détection d'activité** pour optimiser les performances
- **📱 Notifications visuelles** pour les nouvelles données
- **🔧 Composant de débogage** pour le développement
- **🔄 Mises à jour intelligentes** qui évitent les requêtes inutiles

## 🕐 Fréquences de Mise à Jour

| Type de Données | Fréquence | Description |
|----------------|-----------|-------------|
| **Messages** | 5 secondes | Conversations et nouveaux messages |
| **Likes/Vues** | 30 secondes | Interactions sur les produits |
| **Followers** | 1 minute | Nouveaux abonnés et abonnements |
| **Notifications** | 1 seconde | Notifications système |

## 🎯 **Utilisation Simple**

### **1. Dans un Composant Vue**

```vue
<template>
  <div>
    <!-- Votre contenu -->
  </div>
</template>

<script setup>
import { useRealtime } from '@/composables/useRealtime'

const { subscribeToRealtime, unsubscribeFromRealtime } = useRealtime()

// S'abonner aux mises à jour
onMounted(() => {
  subscribeToRealtime('messages', () => {
    // Recharger les conversations
    loadConversations()
  })
})

// Se désabonner automatiquement
onUnmounted(() => {
  unsubscribeFromRealtime('messages')
})
</script>
```

### **2. Mises à Jour Intelligentes**

Le système utilise maintenant des **mises à jour intelligentes** qui comparent les données et appliquent seulement les changements nécessaires, sans recharger toute la page.

## 🔧 **Configuration Avancée**

### **Fréquences Personnalisées**

Vous pouvez personnaliser les fréquences de mise à jour :

### **Gestion des Erreurs**

Le système gère automatiquement les erreurs et les reconnexions :

```javascript
// Le service reprend automatiquement après une erreur
// Pas besoin de gestion manuelle
```

### Désabonnement Conditionnel

```javascript
// Se désabonner quand l'utilisateur change de page
onBeforeUnmount(() => {
  unsubscribeFromRealtime('messages')
})
```

## 🚀 **Avantages du Système**

- ✅ **Mises à jour invisibles** - Pas de rechargement de page
- ✅ **Performance optimisée** - Seules les données changées sont mises à jour
- ✅ **Détection d'activité** - Mises à jour intelligentes selon l'activité utilisateur
- ✅ **Gestion automatique** - Pas de code de gestion manuelle
- ✅ **Animations subtiles** - Feedback visuel discret pour les changements

## 🚨 **Bonnes Pratiques**

### **1. Désabonnement Automatique**

```javascript
// Toujours se désabonner dans onUnmounted
onUnmounted(() => {
  unsubscribeFromRealtime('messages')
})
```

### **2. Utilisation des Mises à Jour Intelligentes**

```javascript
// Utiliser le service intelligent au lieu de recharger tout
import { smartUpdateService } from '@/services/smartUpdateService'

// Enregistrer les données actuelles
smartUpdateService.registerData('conversations', currentConversations)

// Mettre à jour intelligemment
smartUpdateService.smartUpdate('conversations', newConversations, {
  updateCallback: (changes) => {
    // Appliquer seulement les changements
    applyChanges(changes)
  }
})
```

### **3. Fréquences Appropriées**

- **Messages** : 5 secondes (urgent)
- **Likes/Vues** : 30 secondes (modéré)
- **Followers** : 1 minute (peu urgent)
- **Notifications** : 1 seconde (très urgent)

### **❌ À Éviter**

1. **Recharger toute la page** - Utilisez les mises à jour intelligentes
2. **Oublier de se désabonner** - Cela peut causer des fuites mémoire
3. **Fréquences trop élevées** - Respectez les fréquences recommandées
4. **Gestion manuelle des erreurs** - Le système gère automatiquement

## 📚 **Architecture du Système**

Le système temps réel est composé de plusieurs couches :

```
┌─────────────────────────────────────────────────────────────┐
│                    Composants Vue                           │
│  (ChatHub.vue, Profile.vue, etc.)                          │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                  Composables                                 │
│              (useRealtime.js)                               │
└─────────────────────┬───────────────────────────────────────┘
                      │
┌─────────────────────▼───────────────────────────────────────┐
│                  Services                                    │
│  (realtimeService.js + smartUpdateService.js)               │
└─────────────────────────────────────────────────────────────┘
```

### **Services Principaux**

- **`realtimeService.js`** : Gestion des intervalles et des abonnements
- **`smartUpdateService.js`** : Comparaison intelligente des données et mises à jour DOM

## 🔍 **Dépannage**

### **Problèmes Courants**

#### **1. Les mises à jour ne fonctionnent pas**

```javascript
// Vérifiez que vous êtes bien abonné
const { subscribeToRealtime } = useRealtime()

onMounted(() => {
  subscribeToRealtime('messages', () => {
    console.log('Mise à jour reçue!')
    // Votre logique ici
  })
})
```

#### **2. Mises à jour trop fréquentes**

```javascript
// Utilisez des fréquences personnalisées
subscribeToRealtime('messages', callback, 10000) // 10 secondes
```

#### **3. Page qui se recharge complètement**

```javascript
// Utilisez le service de mise à jour intelligente
import { smartUpdateService } from '@/services/smartUpdateService'

smartUpdateService.smartUpdate('conversations', newData, {
  updateCallback: (changes) => {
    // Appliquer seulement les changements
    applyChanges(changes)
  }
})
```

### **Debugging**

Pour déboguer, utilisez la console du navigateur :

```javascript
// Vérifier le statut du service
import realtimeService from '@/services/realtimeService'
console.log('Statut:', realtimeService.getStatus())
```

## 🎯 **Exemples d'Intégration**

### **ChatHub.vue - Mises à jour des conversations**

```vue
<script setup>
import { useRealtime } from '@/composables/useRealtime'
import { smartUpdateService } from '@/services/smartUpdateService'

const { subscribeToRealtime } = useRealtime()

onMounted(() => {
  // S'abonner aux mises à jour des messages
  subscribeToRealtime('messages', async () => {
    await loadBuyerConversations()
    await loadSellerProducts()
  })
  
  // S'abonner aux mises à jour des likes
  subscribeToRealtime('likes', async () => {
    await loadSellerProducts()
  })
})
</script>
```

## 🚀 **Conclusion**

Le système temps réel est maintenant **100% invisible** et **optimisé pour la performance** :

- ✅ **Aucune notification visible** - Expérience utilisateur propre
- ✅ **Mises à jour intelligentes** - Seules les données changées sont mises à jour
- ✅ **Performance optimisée** - Pas de rechargement de page
- ✅ **Code maintenable** - Architecture claire et documentée
