# Configuration de la Devise - Fcfa

## Vue d'ensemble

L'application Vinted Clone utilise maintenant le **Franc CFA (XAF)** comme devise par défaut au lieu de l'Euro.

## Configuration

### Côté Serveur (PHP/Laravel)

La configuration de devise se trouve dans `config/currency.php` :

```php
'code' => 'XAF',        // Code ISO 4217
'symbol' => 'Fcfa',     // Symbole affiché
'name' => 'Franc CFA',  // Nom complet
'locale' => 'fr_FR',    // Locale pour le formatage
'decimals' => 2,        // Nombre de décimales
```

### Côté Client (JavaScript/Vue.js)

La configuration se trouve dans `resources/js/config/currency.js` :

```javascript
export const CURRENCY_CONFIG = {
    code: 'XAF',
    symbol: 'Fcfa',
    name: 'Franc CFA',
    locale: 'fr-FR',
    decimals: 2
}
```

## Fonctions utilitaires

### PHP

```php
// Formater un prix
format_price(1000) // Retourne "1 000 Fcfa"

// Obtenir le symbole
get_currency_symbol() // Retourne "Fcfa"

// Obtenir le code
get_currency_code() // Retourne "XAF"
```

### JavaScript

```javascript
import { formatPrice } from '@/utils/currency.js'

// Formater un prix
formatPrice(1000) // Retourne "1 000 Fcfa"
```

## Variables d'environnement

Ajoutez ces variables dans votre fichier `.env` :

```bash
CURRENCY_CODE=XAF
CURRENCY_SYMBOL=Fcfa
CURRENCY_NAME="Franc CFA"
CURRENCY_LOCALE=fr_FR
CURRENCY_DECIMALS=2
```

## Migration

Lors du changement de devise :

1. **Base de données** : Les prix sont stockés en valeurs numériques, aucune migration nécessaire
2. **Interface** : Mise à jour automatique via les fonctions de formatage
3. **Tests** : Les tests utilisent des valeurs numériques, aucune modification nécessaire

## Formatage des prix

- **Affichage** : Les prix sont affichés avec le symbole "Fcfa"
- **Saisie** : Les champs de saisie montrent "Fcfa" comme indicateur
- **API** : Les valeurs restent numériques dans les réponses JSON

## Exemple d'utilisation

### Dans un composant Vue.js

```vue
<template>
  <div>
    <span>{{ formatPrice(product.price) }}</span>
    <input :placeholder="getCurrencySymbol()" />
  </div>
</template>

<script>
import { formatPrice } from '@/utils/currency.js'
import { getCurrencySymbol } from '@/config/currency.js'

export default {
  methods: {
    formatPrice,
    getCurrencySymbol
  }
}
</script>
```

### Dans un contrôleur Laravel

```php
<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        return response()->json([
            'price' => $product->price,
            'formatted_price' => format_price($product->price),
            'currency' => get_currency_code()
        ]);
    }
}
```

## Notes importantes

- Le code de devise XAF suit la norme ISO 4217
- Le formatage suit les conventions françaises (espace comme séparateur de milliers)
- Les prix sont toujours stockés comme valeurs numériques en base de données
- Le changement de devise n'affecte que l'affichage, pas les calculs
