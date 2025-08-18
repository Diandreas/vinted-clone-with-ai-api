# Configuration du Thème Monochrome Total - RIKEAA

## 🎯 Objectif

Créer une application **totalement monochrome** utilisant uniquement des tons de vert et de gris, inspirée du design épuré de Vinted.

## 📋 Règles Strictes

### ✅ Couleurs Autorisées

#### Tons de Vert (Primary)
- `primary-50` à `primary-900` : Palette complète de verts
- `success` : Alias pour `primary-500`

#### Tons de Gris
- `gray-50` à `gray-900` : Palette complète de gris
- `neutral-50` à `neutral-900` : Alias pour les gris

#### Couleurs de Base
- `white` : Blanc pur
- `black` : Noir pur
- `transparent` : Transparence
- `current` : Couleur courante

### ❌ Couleurs Interdites

**Aucune de ces couleurs ne doit être utilisée :**
- `red`, `yellow`, `blue`, `purple`, `pink`
- `orange`, `indigo`, `teal`, `cyan`
- `emerald`, `lime`, `amber`, `rose`
- `violet`, `fuchsia`, `sky`, `slate`

## 🔧 Configuration des Fichiers

### 1. Tailwind Config (`tailwind.config.js`)
```javascript
colors: {
  primary: {
    50: '#f0f9f4',
    100: '#dcf2e3',
    200: '#b8e4c8',
    300: '#8dd3a8',
    400: '#5bbd85',
    500: '#3da066', // Vert principal Vinted
    600: '#2f7f52',
    700: '#276543',
    800: '#225138',
    900: '#1e4330',
  },
  gray: {
    50: '#fafafa',
    100: '#f5f5f5',
    200: '#e5e5e5',
    300: '#d4d4d4',
    400: '#a3a3a3',
    500: '#737373',
    600: '#525252',
    700: '#404040',
    800: '#262626',
    900: '#171717',
  },
  success: '#3da066',    // Vert primaire
  warning: '#737373',    // Gris moyen
  error: '#525252',      // Gris foncé
}
```

### 2. CSS Personnalisé (`resources/css/rikeaa.css`)
```css
:root {
  --rikeaa-primary-50: #f0f9f4;
  --rikeaa-primary-100: #dcf2e3;
  --rikeaa-primary-200: #b8e4c8;
  --rikeaa-primary-300: #8dd3a8;
  --rikeaa-primary-400: #5bbd85;
  --rikeaa-primary-500: #3da066;
  --rikeaa-primary-600: #2f7f52;
  --rikeaa-primary-700: #276543;
  --rikeaa-primary-800: #225138;
  --rikeaa-primary-900: #1e4330;
}
```

### 3. Configuration JavaScript (`resources/js/config/theme.js`)
```javascript
export const theme = {
  colors: {
    primary: { /* Palette verte complète */ },
    gray: { /* Palette grise complète */ },
    status: {
      success: '#3da066',  // Vert
      warning: '#737373',  // Gris
      error: '#525252',    // Gris
      info: '#3da066'      // Vert
    }
  }
}
```

## 🎨 Classes CSS Utilitaires

### Boutons
```css
.rikeaa-btn-primary    /* Vert principal */
.rikeaa-btn-secondary  /* Blanc avec bordure grise */
.rikeaa-btn-outline    /* Contour vert */
```

### Cartes
```css
.rikeaa-card           /* Ombre douce */
.rikeaa-card-elevated  /* Ombre prononcée */
```

### Formulaires
```css
.rikeaa-input          /* Bordure grise, focus vert */
.rikeaa-input-error    /* Bordure grise foncée */
```

### Typographie
```css
.rikeaa-text-heading   /* Gris très foncé */
.rikeaa-text-subheading /* Gris foncé */
.rikeaa-text-body      /* Gris moyen */
.rikeaa-text-caption   /* Gris clair */
.rikeaa-text-link      /* Vert primaire */
```

### Badges
```css
.rikeaa-badge-primary  /* Vert clair sur vert foncé */
.rikeaa-badge-neutral  /* Gris clair sur gris foncé */
.rikeaa-badge-success  /* Vert clair sur vert foncé */
.rikeaa-badge-warning  /* Gris clair sur gris foncé */
.rikeaa-badge-error    /* Gris clair sur gris foncé */
```

## 🔍 Vérification Automatique

### Script de Vérification
```bash
node check-monochrome.js
```

### Commandes de Recherche
```bash
# Rechercher les couleurs interdites
grep -r "bg-red\|bg-yellow\|bg-blue" resources/js/
grep -r "text-red\|text-yellow\|text-blue" resources/js/
grep -r "border-red\|border-yellow\|border-blue" resources/js/

# Rechercher les couleurs hexadécimales
grep -r "#[0-9a-fA-F]{6}" resources/js/
```

## 📱 Composants de Démonstration

### MonochromeThemeDemo.vue
Composant complet montrant tous les éléments du thème monochrome.

### Utilisation
```vue
<template>
  <MonochromeThemeDemo />
</template>

<script>
import MonochromeThemeDemo from '@/components/ui/MonochromeThemeDemo.vue'

export default {
  components: {
    MonochromeThemeDemo
  }
}
</script>
```

## 🚀 Migration

### Étape 1 : Remplacer les Couleurs
```bash
# Rouge → Gris
sed -i 's/bg-red-500/bg-gray-600/g' **/*.vue
sed -i 's/text-red-500/text-gray-600/g' **/*.vue

# Jaune → Gris
sed -i 's/bg-yellow-500/bg-gray-500/g' **/*.vue
sed -i 's/text-yellow-500/text-gray-500/g' **/*.vue

# Bleu → Vert
sed -i 's/bg-blue-500/bg-primary-500/g' **/*.vue
sed -i 's/text-blue-500/text-primary-500/g' **/*.vue
```

### Étape 2 : Vérifier la Cohérence
```bash
node check-monochrome.js
```

### Étape 3 : Tester Visuellement
- Utiliser le composant de démonstration
- Vérifier tous les écrans de l'application
- S'assurer qu'aucune couleur parasite n'apparaît

## 🎯 Bonnes Pratiques

### ✅ À Faire
- Utiliser les classes utilitaires `rikeaa-*`
- Respecter la hiérarchie des couleurs
- Maintenir le contraste pour l'accessibilité
- Tester sur différents appareils

### ❌ À Éviter
- Ajouter de nouvelles couleurs
- Utiliser des couleurs hexadécimales personnalisées
- Mélanger les palettes de couleurs
- Ignorer les règles de contraste

## 🔧 Maintenance

### Mise à Jour des Couleurs
1. Modifier `tailwind.config.js`
2. Modifier `resources/css/rikeaa.css`
3. Modifier `resources/js/config/theme.js`
4. Exécuter `node check-monochrome.js`
5. Tester visuellement

### Ajout de Nouvelles Classes
1. Respecter la nomenclature `rikeaa-*`
2. Utiliser uniquement la palette monochrome
3. Documenter la nouvelle classe
4. Ajouter des exemples dans la démo

## 📚 Ressources

- [Documentation Tailwind CSS](https://tailwindcss.com/docs)
- [Guide des Couleurs Monochromes](https://www.smashingmagazine.com/2016/04/web-development-reading-list-april-2016/)
- [Principes de Design Vinted](https://www.vinted.com/)
- [Composant de Démonstration](./MonochromeThemeDemo.vue)
- [Script de Vérification](./check-monochrome.js)
