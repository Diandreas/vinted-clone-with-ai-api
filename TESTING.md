# 🧪 Testing Guide - Vinted Clone API

Ce guide explique comment exécuter et comprendre la suite de tests complète de votre clone Vinted.

## 📋 Vue d'ensemble des tests

Notre suite de tests couvre **toutes les fonctionnalités** implémentées :

### 🔧 Tests Unitaires (`tests/Unit/`)
- **UserTest.php** - Tests du modèle User (relations, méthodes, scopes)
- **ProductTest.php** - Tests du modèle Product (lifecycle, attributs, recherche)
- **LiveTest.php** - Tests du modèle Live (streaming, état, engagement)

### 🌐 Tests d'API (`tests/Feature/API/`)
- **ProductControllerTest.php** - Tests complets de l'API produits
- **LiveControllerTest.php** - Tests complets de l'API lives
- **IntegrationTest.php** - Tests de workflows complets

### ⚙️ Tests de Configuration (`tests/Feature/`)
- **ConfigurationTest.php** - Vérification de la configuration système

## 🚀 Lancer les tests

### Option 1: Script automatisé (Recommandé)
```bash
./run-tests.sh
```

### Option 2: Commandes manuelles
```bash
# Tous les tests
php artisan test

# Tests unitaires seulement
php artisan test tests/Unit

# Tests d'API seulement
php artisan test tests/Feature/API

# Un test spécifique
php artisan test tests/Feature/API/ProductControllerTest.php

# Avec couverture de code
php artisan test --coverage
```

## 📊 Types de tests inclus

### 1. **Tests du modèle User**
```php
✅ Création d'utilisateur
✅ Relations (products, followers, following)
✅ Follow/Unfollow workflow
✅ Attributs calculés (followers_count, average_rating)
✅ Scopes (verified, live, recently_active)
✅ Sécurité (attributs cachés)
✅ Recherche (toSearchableArray)
```

### 2. **Tests du modèle Product**
```php
✅ CRUD de produits
✅ Relations (user, category, brand, condition)
✅ Like/Unlike système
✅ Favoris système
✅ Enregistrement des vues
✅ Statuts (active, sold, boosted)
✅ Scopes et filtres
✅ Calculs (discount_percentage, similarity)
✅ Recherche et indexation
```

### 3. **Tests du modèle Live**
```php
✅ Création de live
✅ Start/End workflow
✅ Gestion des spectateurs
✅ Like système
✅ Calculs (duration, engagement_rate)
✅ Statuts (scheduled, live, ended)
```

### 4. **Tests API Produits**
```php
✅ Listing avec filtres (catégorie, prix, taille, couleur)
✅ Recherche textuelle
✅ Tri (prix, popularité, récent)
✅ Création avec validation
✅ Affichage avec enregistrement de vue
✅ Like/Unlike
✅ Favoris
✅ Commentaires
✅ Mise à jour (propriétaire seulement)
✅ Suppression (propriétaire seulement)
✅ Boost de produit
✅ Listes personnalisées (mes produits, favoris)
✅ Produits tendance
✅ Autorisation (propriétaire vs autres)
```

### 5. **Tests API Lives**
```php
✅ Listing avec filtres de statut
✅ Création avec validation
✅ Affichage avec commentaires
✅ Mise à jour (propriétaire seulement)
✅ Start/End workflow
✅ Join/Leave viewers
✅ Like système
✅ Commentaires en temps réel
✅ Autorisation
```

### 6. **Tests d'intégration complets**
```php
✅ Workflow produit complet (création → vue → like → favoris → commentaire → boost → mise à jour)
✅ Workflow live streaming complet (création → start → viewers → commentaires → likes → end)
✅ Workflow social (follow → produits → unfollop)
✅ Filtrage et recherche avancés
```

### 7. **Tests de configuration**
```php
✅ Santé de l'application
✅ Endpoints API
✅ Gestion d'erreurs 404
✅ Authentification (401 pour routes protégées)
✅ Variables d'environnement
✅ Cache fonctionnel
✅ Permissions de stockage
```

## 🎯 Fonctionnalités testées

### ✅ **E-commerce Core**
- Gestion produits (CRUD complet)
- Système de catégories/marques/conditions
- Filtrage et recherche avancés
- Système de favoris et likes
- Commentaires sur produits
- Boost de produits

### ✅ **Live Shopping**
- Création et planification de lives
- Workflow start/end complet
- Gestion des spectateurs en temps réel
- Commentaires live
- Likes en direct
- Statistiques d'engagement

### ✅ **Social Features**
- Système de follow/unfollow
- Profils utilisateurs
- Interactions sociales
- Feeds personnalisés

### ✅ **API & Sécurité**
- Authentification Sanctum
- Autorisations (policies)
- Validation des données
- Gestion d'erreurs
- Rate limiting

### ✅ **Performance & Recherche**
- Indexation pour recherche
- Scopes optimisés
- Relations eager loading
- Caching

## 📈 Métriques de couverture

Notre suite de tests vise **100% de couverture** pour :
- Tous les modèles
- Tous les contrôleurs API
- Toutes les relations
- Toutes les méthodes métier

## 🔍 Exemple de sortie de test

```bash
🧪 Vinted Clone API - Test Suite
==================================
📊 Setting up test database...
🚀 Running Tests...

✅ Configuration Tests passed!
✅ Unit Tests passed!
✅ Feature Tests passed!
✅ Integration Tests passed!

🎉 All tests passed! Your Vinted Clone API is working perfectly!
```

## 🐛 Debugging des tests

### Si un test échoue :

1. **Vérifiez la base de données de test**
```bash
php artisan migrate:fresh --env=testing
```

2. **Lancez un test spécifique avec plus de détails**
```bash
php artisan test --filter=test_method_name -v
```

3. **Vérifiez les logs**
```bash
tail -f storage/logs/laravel.log
```

## 🎯 Prochaines étapes

Les tests couvrent actuellement **toutes les fonctionnalités implémentées**. Lorsque vous ajouterez de nouvelles fonctionnalités :

1. Ajoutez des tests unitaires pour les nouveaux modèles
2. Ajoutez des tests d'API pour les nouveaux endpoints
3. Mettez à jour les tests d'intégration pour les nouveaux workflows
4. Maintenez 100% de couverture de code

## 📝 Notes importantes

- Les tests utilisent une base de données séparée (`testing`)
- Les fichiers uploads sont simulés avec `UploadedFile::fake()`
- L'authentification utilise `Sanctum::actingAs()`
- Les factories génèrent des données réalistes
- Tous les tests sont isolés et peuvent être lancés indépendamment

---

Votre **Vinted Clone API** est maintenant entièrement testée et prête pour la production ! 🚀
