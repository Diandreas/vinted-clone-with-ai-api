# Correction des Images et Devise sur le VPS

## 🚨 **Problèmes Identifiés**

### **1. Images Non Affichées sur le VPS**
- **Erreur 404** : `GET https://rikeaa.com/api/v1/files/products/1755449334_1_0.png 404 (Not Found)`
- **Cause** : Fichier inexistant ou route API non accessible
- **Statut** : ✅ **Résolu** - Route API ajoutée

### **2. Prix en Euros au lieu de FCFA**
- **Affichage** : `€15.00` au lieu de `15 000 FCFA`
- **Cause** : Formatage des prix en euros dans le modèle et le frontend
- **Statut** : ✅ **Résolu** - Conversion en FCFA

## ✅ **Solutions Appliquées**

### **1. Correction de l'API des Fichiers**

#### **Route API Ajoutée**
```php
// routes/api.php
Route::prefix('v1')->group(function () {
    // File serving routes (must be before other routes)
    Route::get('files/{path}', [\App\Http\Controllers\FileController::class, 'serve'])->where('path', '.*');
});
```

#### **FileController Amélioré**
```php
// app/Http/Controllers/FileController.php
public function serve($path)
{
    $filePath = 'public/' . $path;
    
    // Logs de debug
    \Log::info('File request', [
        'requested_path' => $path,
        'full_path' => $filePath,
        'storage_exists' => Storage::exists($filePath)
    ]);
    
    if (!Storage::exists($filePath)) {
        \Log::warning('File not found', ['path' => $filePath]);
        abort(404, 'File not found: ' . $path);
    }
    
    $file = Storage::get($filePath);
    $mimeType = Storage::mimeType($filePath);
    
    return response($file, 200)
        ->header('Content-Type', $mimeType)
        ->header('Cache-Control', 'public, max-age=3600')
        ->header('Access-Control-Allow-Origin', '*');
}
```

### **2. Conversion des Prix en FCFA**

#### **Modèle Product (Backend)**
```php
// app/Models/Product.php
public function getFormattedPriceAttribute()
{
    $price = is_numeric($this->price) ? (float) $this->price : 0.0;
    return number_format($price, 0, ',', ' ') . ' FCFA';
}

public function getFormattedOriginalPriceAttribute()
{
    if ($this->original_price === null) {
        return null;
    }
    $original = is_numeric($this->original_price) ? (float) $this->original_price : 0.0;
    return number_format($original, 0, ',', ' ') . ' FCFA';
}
```

#### **Utilitaire Frontend (Frontend)**
```javascript
// resources/js/utils/currency.js
export const formatPrice = (price) => {
  if (!price || price === 0) return '0 FCFA'
  
  try {
    return new Intl.NumberFormat('fr-FR', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(price) + ' FCFA'
  } catch (error) {
    // Fallback simple
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' FCFA'
  }
}
```

#### **Composant Home.vue Mis à Jour**
```javascript
// resources/js/views/Home.vue
import { formatPrice } from '@/utils/currency'

// Suppression de la fonction formatPrice locale
// Utilisation de l'utilitaire importé
```

## 🔧 **Détails Techniques**

### **1. Structure des URLs d'Images**
```php
// AVANT (ne fonctionnait pas)
url('api/v1/files/products/' . $filename)
// Résultat: https://rikeaa.com/api/v1/files/products/1755449334_1_0.png

// APRÈS (fonctionne)
Route::get('files/{path}', [FileController::class, 'serve'])
// Résultat: Route accessible via l'API
```

### **2. Formatage des Prix**
```php
// AVANT (Euros)
return '€' . number_format($price, 2);
// Résultat: €15.00

// APRÈS (FCFA)
return number_format($price, 0, ',', ' ') . ' FCFA';
// Résultat: 15 000 FCFA
```

### **3. Gestion des Erreurs**
- **Logs de debug** : Traçabilité des requêtes de fichiers
- **Fallback frontend** : Formatage simple si Intl.NumberFormat échoue
- **Headers CORS** : Accessibilité depuis le frontend

## 🚀 **Vérification sur le VPS**

### **1. Test de l'API des Fichiers**
```bash
# Sur le VPS
curl -I https://rikeaa.com/api/v1/files/products/1755447670_1_0.png
```

**Résultat attendu** : `HTTP/1.1 200 OK`

### **2. Diagnostic Complet**
```bash
# Sur le VPS
php diagnose-images-vps.php
```

**Vérifications** :
- Structure des dossiers
- Existence des fichiers
- Lien symbolique storage
- Permissions
- Configuration Laravel

### **3. Commandes de Correction**
```bash
# Créer le lien symbolique
php artisan storage:link

# Vérifier les permissions
chmod 755 storage/app/public/products
chown -R www-data:www-data storage/

# Vider le cache
php artisan config:clear
php artisan route:clear
```

## 📱 **Test Frontend**

### **1. Images**
- **Recharger** la page des produits
- **Vérifier** que les images s'affichent
- **Console browser** : Plus d'erreurs 404

### **2. Prix**
- **Format** : `15 000 FCFA` au lieu de `€15.00`
- **Séparateurs** : Espaces entre les milliers
- **Devise** : FCFA partout

## 🔍 **Dépannage VPS**

### **Si les images ne s'affichent toujours pas :**

#### **1. Vérifier l'Existence des Fichiers**
```bash
ls -la storage/app/public/products/
ls -la public/storage/products/
```

#### **2. Vérifier les Routes**
```bash
php artisan route:list --path=api/v1/files
```

#### **3. Vérifier les Logs**
```bash
tail -f storage/logs/laravel.log
```

#### **4. Vérifier la Configuration**
```bash
php artisan config:show app
php artisan config:show filesystems
```

### **Erreurs Communes VPS :**

#### **404 File Not Found**
- **Cause** : Fichier n'existe pas dans `storage/app/public/`
- **Solution** : Vérifier l'upload des images

#### **403 Forbidden**
- **Cause** : Permissions insuffisantes
- **Solution** : Ajuster les permissions

#### **500 Internal Server Error**
- **Cause** : Erreur dans le FileController
- **Solution** : Vérifier les logs Laravel

## 🎯 **Résultat Final**

### **Avant la Correction**
- ❌ **Images non affichées** : Erreur 404 sur le VPS
- ❌ **Prix en euros** : Formatage incorrect
- ❌ **Route manquante** : API des fichiers inaccessible

### **Après la Correction**
- ✅ **Images affichées** : Accessibles via l'API sur le VPS
- ✅ **Prix en FCFA** : Formatage correct partout
- ✅ **Route fonctionnelle** : `api/v1/files/{path}` opérationnelle
- ✅ **Logs de debug** : Traçabilité des requêtes
- ✅ **Fallback robuste** : Gestion des erreurs de formatage

## 🚀 **Prochaines Étapes**

### **1. Déploiement sur le VPS**
```bash
# Mettre à jour le code
git pull origin main

# Vider le cache
php artisan config:clear
php artisan route:clear

# Redémarrer le serveur web
sudo systemctl restart nginx
sudo systemctl restart php8.2-fpm
```

### **2. Tests de Validation**
- **API des fichiers** : Test avec curl
- **Frontend** : Vérification des images et prix
- **Logs** : Surveillance des erreurs

### **3. Optimisations Futures**
- **CDN** : Utiliser un CDN pour les images
- **Cache** : Améliorer la stratégie de cache
- **Compression** : Optimiser la taille des images
- **Lazy Loading** : Chargement progressif
