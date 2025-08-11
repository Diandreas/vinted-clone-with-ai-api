# Routes Admin API - Vinted Clone

## Vue d'ensemble

Les routes admin sont protégées par le middleware `admin` et nécessitent une authentification via Sanctum. Elles sont accessibles via le préfixe `/api/v1/admin/`.

## Authentification

Toutes les routes admin nécessitent :
1. Un token d'authentification Sanctum valide
2. Des droits d'administration (rôle `admin` ou flag `is_admin`)

```bash
Authorization: Bearer {your_sanctum_token}
```

## Routes Admin

### 1. Gestion des Utilisateurs

#### Lister tous les utilisateurs
```http
GET /api/v1/admin/users
```

**Paramètres de requête :**
- `search` : Recherche par nom, email ou username
- `role` : Filtrer par rôle (user, admin, manager, analyst, moderator)
- `status` : Filtrer par statut (verified, unverified)
- `admin` : Filtrer par statut admin (true, false)
- `sort_by` : Champ de tri (created_at, name, email, etc.)
- `sort_direction` : Direction du tri (asc, desc)
- `per_page` : Nombre d'éléments par page (défaut: 15)

#### Afficher un utilisateur
```http
GET /api/v1/admin/users/{user_id}
```

#### Vérifier un utilisateur
```http
PUT /api/v1/admin/users/{user_id}/verify
```

#### Bannir un utilisateur
```http
PUT /api/v1/admin/users/{user_id}/ban
```

#### Débannir un utilisateur
```http
PUT /api/v1/admin/users/{user_id}/unban
```

#### Supprimer un utilisateur
```http
DELETE /api/v1/admin/users/{user_id}
```

### 2. Gestion des Produits

#### Lister tous les produits
```http
GET /api/v1/admin/products
```

#### Lister les produits en attente
```http
GET /api/v1/admin/products/pending
```

#### Approuver un produit
```http
PUT /api/v1/admin/products/{product_id}/approve
```

#### Rejeter un produit
```http
PUT /api/v1/admin/products/{product_id}/reject
```

#### Mettre en avant un produit
```http
PUT /api/v1/admin/products/{product_id}/feature
```

#### Supprimer un produit
```http
DELETE /api/v1/admin/products/{product_id}
```

### 3. Gestion des Signalements

#### Lister tous les signalements
```http
GET /api/v1/admin/reports
```

#### Afficher un signalement
```http
GET /api/v1/admin/reports/{report_id}
```

#### Résoudre un signalement
```http
PUT /api/v1/admin/reports/{report_id}/resolve
```

#### Rejeter un signalement
```http
PUT /api/v1/admin/reports/{report_id}/dismiss
```

### 4. Gestion des Catégories

#### Lister toutes les catégories
```http
GET /api/v1/admin/categories
```

**Paramètres de requête :**
- `search` : Recherche par nom ou description
- `status` : Filtrer par statut (active, inactive)
- `parent_id` : Filtrer par catégorie parente
- `sort_by` : Champ de tri (sort_order, name, created_at)
- `sort_direction` : Direction du tri (asc, desc)
- `per_page` : Nombre d'éléments par page (défaut: 15)

#### Créer une catégorie
```http
POST /api/v1/admin/categories
```

**Corps de la requête :**
```json
{
    "name": "Nom de la catégorie",
    "description": "Description de la catégorie",
    "parent_id": null,
    "icon": "icon-name",
    "color": "#FF5733",
    "is_active": true,
    "sort_order": 0
}
```

#### Afficher une catégorie
```http
GET /api/v1/admin/categories/{category_id}
```

#### Mettre à jour une catégorie
```http
PUT /api/v1/admin/categories/{category_id}
```

#### Supprimer une catégorie
```http
DELETE /api/v1/admin/categories/{category_id}
```

**Note :** Une catégorie ne peut être supprimée que si elle n'a pas de produits ni de sous-catégories.

### 5. Gestion des Marques

#### Créer une marque
```http
POST /api/v1/admin/brands
```

#### Mettre à jour une marque
```http
PUT /api/v1/admin/brands/{brand_id}
```

#### Supprimer une marque
```http
DELETE /api/v1/admin/brands/{brand_id}
```

### 6. Analytics et Statistiques

#### Vue d'ensemble
```http
GET /api/v1/admin/analytics/overview
```

#### Statistiques des utilisateurs
```http
GET /api/v1/admin/analytics/users
```

#### Statistiques des produits
```http
GET /api/v1/admin/analytics/products
```

#### Statistiques des ventes
```http
GET /api/v1/admin/analytics/sales
```

#### Statistiques des signalements
```http
GET /api/v1/admin/analytics/reports
```

### 7. Paramètres Système

#### Récupérer les paramètres
```http
GET /api/v1/admin/settings
```

#### Mettre à jour les paramètres
```http
PUT /api/v1/admin/settings
```

## Codes de Réponse

### Succès
- `200` : Requête réussie
- `201` : Ressource créée avec succès

### Erreurs Client
- `400` : Requête malformée
- `401` : Non authentifié
- `403` : Accès interdit (pas de droits admin)
- `404` : Ressource non trouvée
- `422` : Erreur de validation

### Erreurs Serveur
- `500` : Erreur interne du serveur

## Exemples d'Utilisation

### Créer une nouvelle catégorie
```bash
curl -X POST /api/v1/admin/categories \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Vêtements",
    "description": "Catégorie pour tous les vêtements",
    "color": "#3498db",
    "is_active": true
  }'
```

### Lister les utilisateurs avec filtres
```bash
curl -X GET "/api/v1/admin/users?role=user&status=verified&per_page=20" \
  -H "Authorization: Bearer {token}"
```

### Approuver un produit
```bash
curl -X PUT /api/v1/admin/products/123/approve \
  -H "Authorization: Bearer {token}"
```

## Sécurité

- Toutes les routes sont protégées par le middleware `admin`
- Vérification des permissions utilisateur
- Validation des données d'entrée
- Protection CSRF via Sanctum
- Rate limiting appliqué

## Middleware Admin

Le middleware `AdminMiddleware` vérifie :
1. L'authentification de l'utilisateur
2. Les droits d'administration (rôle `admin` ou flag `is_admin`)

## Permissions

Les utilisateurs admin peuvent avoir différents niveaux de permissions :
- `dashboard:view` : Accès au tableau de bord
- `users:manage` : Gestion des utilisateurs
- `products:moderate` : Modération des produits
- `lives:moderate` : Modération des lives
- `orders:view` : Consultation des commandes
- `analytics:view` : Accès aux analytics

## Notes Importantes

1. **Suppression en cascade** : Certaines ressources ne peuvent être supprimées si elles ont des dépendances
2. **Validation** : Toutes les données d'entrée sont validées selon les règles définies
3. **Pagination** : Les listes sont paginées par défaut (15 éléments par page)
4. **Recherche** : La plupart des listes supportent la recherche textuelle
5. **Filtres** : Filtrage par statut, rôle, date, etc.
6. **Tri** : Tri personnalisable sur différents champs
