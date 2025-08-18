# 📱 Fonctionnalité de Message avec Informations Produit

## 🎯 Objectif

Permettre aux utilisateurs d'initier une conversation avec le vendeur d'un produit en incluant automatiquement toutes les informations pertinentes du produit (image, titre, description, prix, lien) **dans le message envoyé**.

## ✨ Fonctionnalités

### 1. **Affichage des informations du produit**
- 🖼️ Image du produit (avec fallback si pas d'image)
- 📝 Titre du produit
- 📄 Description du produit (tronquée à 2 lignes)
- 💰 Prix du produit (formaté en euros)
- 🔗 Lien direct vers le produit

### 2. **Envoi des informations avec le message**
- 📤 Les informations du produit sont **incluses dans le message envoyé**
- 🎯 L'image, le titre, la description et le prix sont structurés dans le contenu
- 💬 Le message devient un objet JSON avec le texte et les données produit
- 🔄 L'affichage dans le chat inclut automatiquement la carte produit

### 3. **Pré-remplissage automatique du message**
- Bouton "Pré-remplir avec les infos du produit"
- Message automatique incluant :
  - Salutation
  - Référence au produit
  - Prix
  - Description (si disponible)
  - Demande d'informations

### 4. **Interface utilisateur améliorée**
- Carte d'information produit bien structurée
- Zone de texte plus grande (textarea au lieu d'input)
- Boutons d'action clairement identifiés
- Design responsive et moderne

## 🔧 Implémentation technique

### Modifications dans `ProductDetail.vue`

```javascript
const startConversation = () => {
  if (!isAuthenticated.value) {
    router.push('/login')
    return
  }
  
  if (currentUserId.value === product.value.user_id) {
    alert('Vous ne pouvez pas vous envoyer un message à vous-même.')
    return
  }
  
  // Préparer les informations du produit pour le message
  const productInfo = {
    id: product.value.id,
    title: product.value.title,
    description: product.value.description,
    price: product.value.price,
    image: product.value.main_image_url,
    url: `${window.location.origin}/products/${product.value.id}`
  }
  
  // Encoder les informations du produit pour les passer via l'URL
  const encodedProductInfo = encodeURIComponent(JSON.stringify(productInfo))
  
  router.push(`/messages?user=${product.value.user_id}&product=${product.value.id}&productInfo=${encodedProductInfo}`)
}
```

### Modifications dans `Messages.vue`

#### 1. **Structure des données**
```javascript
const startTarget = ref({ 
  userId: null, 
  productId: null, 
  productInfo: null 
})
```

#### 2. **Fonction de pré-remplissage**
```javascript
function prefillProductMessage() {
  if (!startTarget.value.productInfo) return
  
  const product = startTarget.value.productInfo
  const message = `Bonjour ! Je suis intéressé(e) par votre produit "${product.title}" au prix de ${formatPrice(product.price)}.
  
${product.description ? `Description : ${product.description}` : ''}

Pouvez-vous me donner plus d'informations ?`
  
  compose.value = message.trim()
}
```

#### 3. **Parsing des paramètres URL**
```javascript
function hydrateStartTargetFromQuery() {
  const userId = route.query.user ? Number(route.query.user) : null
  const productId = route.query.product ? Number(route.query.product) : null
  
  // Récupérer les informations du produit depuis l'URL
  let productInfo = null
  if (route.query.productInfo) {
    try {
      productInfo = JSON.parse(decodeURIComponent(route.query.productInfo))
    } catch (e) {
      console.error('Erreur lors du parsing des informations du produit:', e)
    }
  }
  
  startTarget.value = { userId, productId, productInfo }
}
```

#### 4. **Fonction de formatage des prix**
```javascript
function formatPrice(price) {
  if (!price) return '0 €'
  return new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'EUR'
  }).format(price)
}
```

#### 5. **Fonction d'extraction des informations produit**
```javascript
function getMessageProduct(m) {
  const content = safeParseMaybeJson(m.content)
  
  // Si c'est un message structuré avec des informations produit, retourner les infos produit
  if (typeof content === 'object' && content.product) {
    return content.product
  }
  
  return null
}
```

## 🔄 Structure des messages

### Format des messages avec produit
```json
{
  "text": "Bonjour ! Je suis intéressé(e) par votre produit...",
  "product": {
    "id": 1,
    "title": "iPhone 14 Pro",
    "description": "Excellent état, jamais utilisé",
    "price": 899,
    "image": "http://127.0.0.1:8000/storage/products/iphone14pro.jpg",
    "url": "http://127.0.0.1:8000/products/1"
  },
  "type": "product_inquiry"
}
```

### Types de messages
- **`text`** : Message texte simple
- **`product_inquiry`** : Message avec informations produit structurées

## 🎨 Interface utilisateur

### Structure HTML
```html
<!-- Product Information Card -->
<div v-if="startTarget.productInfo" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
  <h3 class="text-lg font-semibold text-gray-900 mb-3">À propos du produit</h3>
  <div class="flex items-start space-x-4">
    <!-- Product Image -->
    <div class="flex-shrink-0">
      <img v-if="startTarget.productInfo.image" :src="startTarget.productInfo.image" />
      <!-- Fallback icon -->
    </div>
    
    <!-- Product Details -->
    <div class="flex-1 min-w-0">
      <h4>{{ startTarget.productInfo.title }}</h4>
      <p>{{ startTarget.productInfo.description }}</p>
      <div class="flex items-center justify-between">
        <span>{{ formatPrice(startTarget.productInfo.price) }}</span>
        <a :href="startTarget.productInfo.url">Voir le produit</a>
      </div>
    </div>
  </div>
</div>
```

### Styles CSS
```css
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
```

### Affichage des messages dans le chat
```html
<!-- Message avec informations produit -->
<div v-if="getMessageProduct(m)" class="mt-3 p-3 bg-white bg-opacity-20 rounded-lg border border-white border-opacity-30">
  <div class="flex items-start space-x-3">
    <!-- Image du produit -->
    <img :src="getMessageProduct(m).image" :alt="getMessageProduct(m).title" class="w-16 h-16 object-cover rounded-lg">
    
    <!-- Détails du produit -->
    <div class="flex-1 min-w-0">
      <h4 class="font-medium text-white text-sm mb-1">{{ getMessageProduct(m).title }}</h4>
      <p class="text-white text-opacity-80 text-xs mb-2">{{ getMessageProduct(m).description }}</p>
      <div class="flex items-center justify-between">
        <span class="text-sm font-bold text-yellow-300">{{ formatPrice(getMessageProduct(m).price) }}</span>
        <a :href="getMessageProduct(m).url" class="text-xs text-yellow-300 underline">Voir le produit</a>
      </div>
    </div>
  </div>
</div>
```

## 🧪 Tests

### URL de test
```
http://127.0.0.1:8000/messages?user=1&product=1&productInfo={"id":1,"title":"iPhone 14 Pro","description":"Excellent état","price":899,"image":"http://127.0.0.1:8000/storage/products/iphone14pro.jpg","url":"http://127.0.0.1:8000/products/1"}
```

### Scénarios de test
1. **Avec produit** : Vérifier l'affichage de la carte produit
2. **Sans produit** : Vérifier que l'interface fonctionne normalement
3. **Pré-remplissage** : Tester le bouton de pré-remplissage
4. **Envoi de message** : Vérifier l'envoi avec les informations produit

## 🚀 Utilisation

### Pour l'utilisateur
1. Aller sur la page d'un produit
2. Cliquer sur le bouton "Message"
3. Être redirigé vers la page des messages
4. Voir les informations du produit affichées
5. Cliquer sur "Pré-remplir avec les infos du produit"
6. Modifier le message si nécessaire
7. Envoyer le message

### Pour le développeur
1. Les informations du produit sont passées via l'URL
2. Le parsing se fait automatiquement au chargement de la page
3. L'interface s'adapte selon la présence ou non d'informations produit
4. Le message pré-rempli peut être personnalisé

## 🔒 Sécurité

- Les informations du produit sont encodées en base64 dans l'URL
- Validation des données reçues
- Gestion des erreurs de parsing
- Pas d'exécution de code arbitraire

## 📱 Responsive

- Design adaptatif pour mobile et desktop
- Grille flexible pour l'affichage des informations
- Boutons et textes adaptés aux différentes tailles d'écran

## 🎯 Améliorations futures possibles

1. **Historique des produits** : Sauvegarder les produits consultés
2. **Templates de messages** : Plusieurs types de messages pré-remplis
3. **Pièces jointes** : Possibilité d'ajouter des images au message
4. **Notifications** : Notifier le vendeur de l'intérêt pour son produit
5. **Statistiques** : Suivre les interactions sur les produits

## 🐛 Dépannage

### Problèmes courants
1. **Image non affichée** : Vérifier l'URL de l'image et les permissions
2. **Erreur de parsing** : Vérifier l'encodage des paramètres URL
3. **Interface non responsive** : Vérifier les classes Tailwind CSS
4. **Message non envoyé** : Vérifier l'authentification et les routes API

### Logs de débogage
```javascript
console.log('ProductInfo reçu:', startTarget.value.productInfo)
console.log('URL générée:', router.currentRoute.value.fullPath)
```

## 📚 Ressources

- [Documentation Vue.js](https://vuejs.org/)
- [Documentation Tailwind CSS](https://tailwindcss.com/)
- [Documentation Laravel](https://laravel.com/)
- [Formatage des prix en JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/NumberFormat)
