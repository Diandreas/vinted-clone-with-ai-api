# Thème Monochrome Total - RIKEAA

## Vue d'ensemble

Ce document décrit le système de couleurs **totalement monochrome** de l'application RIKEAA, utilisant **uniquement** des tons de vert et de gris, inspiré du design simpliste et épuré de Vinted.

## 🎨 Principe Monochrome Total

**Aucune autre couleur n'est utilisée** dans l'application. Tout est basé sur :
- **Tons de vert** : Pour les éléments principaux, actions et accents
- **Tons de gris** : Pour le contenu, les bordures et les éléments neutres

## Palette de Couleurs

### Couleurs Principales (Vert)
- **primary-50**: `#f0f9f4` - Très clair, arrière-plans subtils
- **primary-100**: `#dcf2e3` - Clair, arrière-plans de cartes
- **primary-200**: `#b8e4c8` - Moyen-clair, bordures et séparateurs
- **primary-300**: `#8dd3a8` - Moyen, icônes et éléments secondaires
- **primary-400**: `#5bbd85` - Moyen-foncé, éléments d'accent
- **primary-500**: `#3da066` - **Couleur principale** (Vert Vinted)
- **primary-600**: `#2f7f52` - Hover et états actifs
- **primary-700**: `#276543` - Texte sur fond clair
- **primary-800**: `#225138` - Titres et éléments importants
- **primary-900**: `#1e4330` - Texte principal sur fond blanc

### Couleurs Neutres (Gris)
- **gray-50**: `#fafafa` - Arrière-plans très clairs
- **gray-100**: `#f5f5f5` - Arrière-plans de cartes
- **gray-200**: `#e5e5e5` - Bordures et séparateurs
- **gray-300**: `#d4d4d4` - Bordures de formulaires
- **gray-400**: `#a3a3a3` - Texte secondaire
- **gray-500**: `#737373` - Texte de corps
- **gray-600**: `#525252` - Texte de sous-titres
- **gray-700**: `#404040` - Texte de titres
- **gray-800**: `#262626` - Texte principal
- **gray-900**: `#171717` - Texte très foncé

### Couleurs d'Accent (Monochrome)
- **success**: `#3da066` - Succès (vert primaire)
- **warning**: `#737373` - Attention (gris moyen)
- **error**: `#525252` - Erreur (gris foncé)

## Classes Utilitaires

### Boutons
```css
.rikeaa-btn-primary    /* Bouton principal vert */
.rikeaa-btn-secondary  /* Bouton secondaire blanc */
.rikeaa-btn-outline    /* Bouton contour vert */
```

### Cartes
```css
.rikeaa-card           /* Carte standard avec ombre douce */
.rikeaa-card-elevated  /* Carte avec ombre plus prononcée */
```

### Formulaires
```css
.rikeaa-input          /* Champ de saisie standard */
.rikeaa-input-error    /* Champ de saisie en erreur (gris) */
```

### Typographie
```css
.rikeaa-text-heading   /* Titres principaux */
.rikeaa-text-subheading /* Sous-titres */
.rikeaa-text-body      /* Texte de corps */
.rikeaa-text-caption   /* Légendes et textes secondaires */
.rikeaa-text-link      /* Liens avec couleur primaire */
```

### Badges et Étiquettes (Monochrome)
```css
.rikeaa-badge-primary  /* Badge vert principal */
.rikeaa-badge-neutral  /* Badge gris neutre */
.rikeaa-badge-success  /* Badge vert (succès) */
.rikeaa-badge-warning  /* Badge gris (attention) */
.rikeaa-badge-error    /* Badge gris (erreur) */
```

## Ombres

- **shadow-soft**: `0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)`
- **shadow-medium**: `0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)`
- **shadow-strong**: `0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)`

## Border Radius

- **rounded-sm**: `0.375rem`
- **rounded-md**: `0.5rem`
- **rounded-lg**: `0.75rem`
- **rounded-xl**: `1rem`

## Transitions

- **fast**: `transition-all duration-150 ease-in-out`
- **normal**: `transition-all duration-200 ease-in-out`
- **slow**: `transition-all duration-300 ease-in-out`

## Utilisation

### Dans les composants Vue
```vue
<template>
  <button class="rikeaa-btn-primary">
    Bouton Principal
  </button>
  
  <div class="rikeaa-card p-4">
    <h3 class="rikeaa-text-heading">Titre</h3>
    <p class="rikeaa-text-body">Contenu</p>
  </div>
  
  <input type="text" class="rikeaa-input" placeholder="Saisie..." />
</template>
```

### Dans Tailwind CSS
```css
/* Utilisation directe des couleurs monochromes */
.bg-primary-500
.text-primary-600
.border-primary-200

/* Utilisation des ombres */
.shadow-soft
.shadow-medium
.shadow-strong
```

## Migration vers Monochrome Total

### Remplacer TOUTES les autres couleurs
- `indigo-500` → `primary-500`
- `indigo-600` → `primary-600`
- `blue-500` → `primary-500`
- `green-500` → `primary-500`
- `red-500` → `gray-600`
- `yellow-500` → `gray-500`
- `purple-500` → `primary-500`

### Remplacer les anciennes classes
- `focus:ring-indigo-500` → `focus:ring-primary-500`
- `focus:border-indigo-500` → `focus:border-primary-500`
- `bg-indigo-600` → `bg-primary-500`
- `text-indigo-600` → `text-primary-600`
- `bg-red-500` → `bg-gray-600`
- `bg-yellow-500` → `bg-gray-500`

## Avantages du Monochrome Total

1. **Cohérence visuelle parfaite** : Aucune couleur parasite
2. **Design épuré** : Style minimaliste et professionnel
3. **Maintenabilité** : Changement centralisé des couleurs
4. **Accessibilité** : Contraste optimal entre les tons
5. **Performance** : Classes CSS optimisées et réutilisables
6. **Identité de marque** : Couleur verte distinctive de RIKEAA

## Exemples d'Application Monochrome

### Navigation
- Logo et marque : `bg-primary-500`
- Liens actifs : `text-primary-600`
- Bouton "Vendre" : `bg-primary-500 hover:bg-primary-600`

### Produits
- Cartes : `rikeaa-card`
- Badges de statut : `rikeaa-badge-primary` (vert) ou `rikeaa-badge-neutral` (gris)
- Boutons d'action : `rikeaa-btn-primary`

### Formulaires
- Champs de saisie : `rikeaa-input`
- Boutons de soumission : `rikeaa-btn-primary`
- Messages d'erreur : `rikeaa-badge-error` (gris)

### Statuts et Notifications
- Succès : `bg-primary-100 text-primary-800`
- Attention : `bg-gray-100 text-gray-800`
- Erreur : `bg-gray-100 text-gray-800`

## Maintenance

Pour modifier les couleurs du thème monochrome :
1. Mettre à jour `tailwind.config.js`
2. Mettre à jour `resources/css/rikeaa.css`
3. Mettre à jour `resources/js/config/theme.js`
4. **Vérifier qu'aucune autre couleur n'est utilisée**
5. Tester la cohérence visuelle

## Vérification Monochrome

Pour s'assurer que l'application reste totalement monochrome :

```bash
# Rechercher les couleurs non monochromes
grep -r "bg-red\|bg-yellow\|bg-blue\|bg-purple\|bg-pink" resources/js/
grep -r "text-red\|text-yellow\|text-blue\|text-purple\|text-pink" resources/js/
grep -r "border-red\|border-yellow\|border-blue\|border-purple\|border-pink" resources/js/
```

## Support

Pour toute question sur l'implémentation du thème monochrome total, consulter :
- La configuration Tailwind
- Les classes CSS personnalisées
- La documentation des composants
- Le composant de démonstration `MonochromeThemeDemo.vue`

## Composant de Démonstration

Utilisez le composant `MonochromeThemeDemo.vue` pour visualiser tous les éléments du thème monochrome total en action.
