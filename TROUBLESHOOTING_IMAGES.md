# Guide de dépannage - Affichage des images

## Problème : Les images ne s'affichent pas

### Symptômes
- Les images ne se chargent pas dans ProductDetail
- Erreur 404 pour les images
- Images cassées ou non visibles

### Causes possibles

1. **Lien symbolique storage manquant**
   - Le lien `storage/app/public` vers `public/storage` n'existe pas
   - Les images sont stockées mais pas accessibles publiquement

2. **Permissions incorrectes**
   - Le dossier `storage/app/public/products` n'a pas les bonnes permissions
   - Le serveur web ne peut pas accéder aux fichiers

3. **URLs incorrectes**
   - Les chemins d'images ne sont pas corrects
   - Les accesseurs du modèle ne fonctionnent pas

4. **Image par défaut manquante**
   - Le fichier `public/images/product-placeholder.jpg` n'existe pas

### Solutions

#### 1. Vérifier le lien symbolique storage

```bash
# Dans le dossier racine du projet
php artisan storage:link

# Vérifier que le lien existe
ls -la public/
# Doit afficher : storage -> ../storage/app/public
```

#### 2. Vérifier les permissions

```bash
# Donner les bonnes permissions au dossier storage
chmod -R 755 storage/
chmod -R 755 public/storage/

# Si vous utilisez Apache/Nginx, vérifiez que l'utilisateur du serveur peut accéder
sudo chown -R www-data:www-data storage/
sudo chown -R www-data:www-data public/storage/
```

#### 3. Vérifier la structure des dossiers

```bash
# Vérifier que les dossiers existent
ls -la storage/app/public/
ls -la storage/app/public/products/
ls -la storage/app/public/products/thumbnails/

# Créer les dossiers s'ils n'existent pas
mkdir -p storage/app/public/products
mkdir -p storage/app/public/products/thumbnails
```

#### 4. Tester l'accès aux images

```bash
# Vérifier qu'une image existe
ls -la storage/app/public/products/

# Tester l'URL d'une image
curl -I http://votre-site.com/storage/products/nom-image.jpg
```

#### 5. Vérifier la configuration Laravel

Dans `config/filesystems.php`, vérifiez que le disque `public` est bien configuré :

```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

#### 6. Créer une image par défaut

Si `public/images/product-placeholder.jpg` n'existe pas :

```bash
# Créer le dossier
mkdir -p public/images

# Copier une image par défaut ou en créer une
cp chemin/vers/image.jpg public/images/product-placeholder.jpg
```

### Test de diagnostic

1. **Vérifier la console du navigateur** :
   - Onglet Network
   - Regarder les requêtes d'images
   - Vérifier les codes de statut (200, 404, 403)

2. **Vérifier les URLs des images** :
   - Dans la console, taper : `console.log(product.main_image_url)`
   - Vérifier que l'URL est correcte

3. **Tester une image directement** :
   - Copier l'URL d'une image dans un nouvel onglet
   - Voir si l'image se charge

### Debug côté serveur

Ajouter des logs dans le contrôleur ProductController :

```php
public function show(Product $product, Request $request)
{
    $product->load([
        'user',
        'category', 
        'brand', 
        'condition', 
        'images',
        'comments.user'
    ]);

    // Debug des images
    \Log::info('Product images:', [
        'product_id' => $product->id,
        'images_count' => $product->images->count(),
        'main_image_url' => $product->main_image_url,
        'images' => $product->images->map(function($img) {
            return [
                'id' => $img->id,
                'filename' => $img->filename,
                'url' => $img->url
            ];
        })
    ]);

    // ... reste du code
}
```

### Vérifications rapides

1. **Le lien symbolique existe-t-il ?**
   ```bash
   ls -la public/storage
   ```

2. **Les images sont-elles stockées ?**
   ```bash
   ls -la storage/app/public/products/
   ```

3. **Les URLs sont-elles correctes ?**
   - Vérifier dans la console du navigateur
   - Tester une URL d'image directement

4. **Les permissions sont-elles correctes ?**
   ```bash
   ls -la storage/app/public/
   ```

### Support

Si le problème persiste :
1. Vérifiez les logs Laravel : `storage/logs/laravel.log`
2. Vérifiez les logs du serveur web (Apache/Nginx)
3. Testez avec une image simple
4. Vérifiez la configuration du serveur web
