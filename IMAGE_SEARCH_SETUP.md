# Configuration de la Recherche par Image

## Vue d'ensemble
Ce document explique comment configurer et utiliser le système de recherche par image utilisant Google Vision API.

## Prérequis

### 1. Compte Google Cloud Platform
- Créer un projet sur [Google Cloud Console](https://console.cloud.google.com/)
- Activer l'API Vision AI
- Créer une clé de service au format JSON

### 2. Configuration des variables d'environnement
Ajouter ces variables dans votre fichier `.env`:

```env
# Google Cloud Vision API
GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_CLOUD_KEY_FILE=/path/to/your/service-account.json
GOOGLE_CLOUD_STORAGE_BUCKET=your-bucket-name
```

### 3. Installation des dépendances
Le package Google Cloud Vision est déjà ajouté dans composer.json:
```bash
composer install
```

## Configuration

### 1. Exécuter les migrations
```bash
php artisan migrate
```

### 2. Traiter les images existantes
```bash
# Traiter les 50 premiers produits
php artisan vision:process-products

# Traiter un nombre spécifique
php artisan vision:process-products --limit=100

# Retraiter même les produits déjà analysés
php artisan vision:process-products --force

# Traiter un produit spécifique
php artisan vision:process-products --product-id=123
```

## Utilisation

### API Endpoints

#### 1. Recherche par image
```http
POST /api/v1/search/image
Content-Type: multipart/form-data

image: [fichier image]
limit: 10 (optionnel, max 50)
```

Réponse:
```json
{
  "success": true,
  "data": {
    "results": [
      {
        "product": {
          "id": 123,
          "title": "T-shirt rouge",
          "price": 25.00,
          "main_image_url": "...",
          // ... autres données produit
        },
        "similarity_score": 87.5,
        "match_details": {
          "labels": [
            {"description": "Clothing", "score": 0.95},
            {"description": "T-shirt", "score": 0.87}
          ],
          "objects": [...],
          "dominant_colors": [...]
        }
      }
    ],
    "total_found": 5,
    "search_meta": {
      "limit": 10,
      "algorithm_version": "1.0"
    }
  },
  "message": "Found 5 similar products"
}
```

#### 2. Analyser une image (debug)
```http
POST /api/v1/search/analyze
Content-Type: multipart/form-data

image: [fichier image]
```

#### 3. Statistiques admin
```http
GET /api/v1/admin/image-search/stats
Authorization: Bearer {admin-token}
```

#### 4. Traitement par lot (admin)
```http
POST /api/v1/admin/image-search/process-products
Authorization: Bearer {admin-token}

{
  "limit": 50
}
```

### Interface utilisateur

#### Accès à la recherche par image
- URL: `/search/image`
- Accessible depuis l'icône d'image dans la barre de recherche
- Interface drag & drop pour upload d'images
- Affichage des résultats avec score de similarité

#### Fonctionnalités
- Upload par glisser-déposer ou sélection de fichier
- Validation des formats (JPEG, PNG, GIF, SVG, max 10MB)
- Affichage des résultats triés par pertinence
- Détails de correspondance (labels, objets, couleurs)
- Actions like/favorite sur les résultats

## Architecture technique

### 1. Modèles de données
- `ProductVisionData`: Stocke les données d'analyse Vision API
- Relations: `Product` → `hasMany` → `ProductVisionData`

### 2. Services
- `GoogleVisionService`: Interface avec l'API Google Vision
- Algorithmes de similarité:
  - Similarité cosinus sur les vecteurs de caractéristiques
  - Correspondance des labels (30%)
  - Correspondance des objets (20%)
  - Similarité des couleurs (10%)
  - Vecteur de caractéristiques (40%)

### 3. Base de données
- Table `product_vision_data` avec index optimisés
- Stockage JSON pour flexibilité
- Index sur `similarity_score` et `processed`

## Performance et optimisations

### 1. Index de base de données
```sql
-- Index existants créés par la migration
INDEX idx_product_processed (product_id, processed)
INDEX idx_similarity_score (similarity_score)
```

### 2. Cache et optimisations
- Traitement asynchrone recommandé pour nouveaux produits
- Cache des résultats de recherche fréquents
- Limitation à 50 résultats maximum par recherche

### 3. Monitoring
- Logs des erreurs dans `storage/logs/laravel.log`
- Statistiques admin disponibles via API
- Métriques de performance dans les réponses

## Sécurité

### 1. Validation des fichiers
- Types MIME autorisés: image/*
- Taille maximum: 10MB
- Validation côté serveur et client

### 2. Permissions
- Recherche par image: publique
- Administration: rôle admin requis
- Traitement par lot: admin uniquement

### 3. Rate limiting
- Même limitations que les autres endpoints API
- Considérer des limites spécifiques pour l'upload d'images

## Troubleshooting

### Erreurs communes

#### 1. "Google Vision API Error"
- Vérifier les credentials Google Cloud
- Vérifier que l'API Vision est activée
- Vérifier les quotas et limites

#### 2. "Image file not found"
- Vérifier les permissions du dossier storage
- Vérifier le chemin des images stockées

#### 3. "No similar products found"
- S'assurer que des produits ont été traités
- Exécuter `php artisan vision:process-products`
- Vérifier la qualité de l'image de recherche

### Commandes de diagnostic
```bash
# Vérifier le statut de traitement
php artisan tinker
>>> App\Models\ProductVisionData::where('processed', true)->count()

# Statistiques via API (admin requis)
curl -H "Authorization: Bearer TOKEN" http://localhost/api/v1/admin/image-search/stats
```

## Développement futur

### Améliorations possibles
1. **Cache intelligent**: Mise en cache des résultats fréquents
2. **Recherche hybride**: Combinaison texte + image
3. **Machine Learning**: Modèle personnalisé pour la mode
4. **Filtres avancés**: Combinaison avec filtres prix/taille/marque
5. **API externe**: Intégration avec d'autres services de reconnaissance

### Métriques à surveiller
- Temps de réponse des recherches
- Qualité des correspondances (feedback utilisateur)
- Utilisation de l'API Google Vision
- Taux de conversion recherche → achat

## Support
Pour tout problème, consulter:
1. Les logs Laravel: `storage/logs/laravel.log`
2. Les statistiques admin via l'interface web
3. La documentation Google Vision API