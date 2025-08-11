# 🚀 Checklist de Déploiement VPS

## ✅ **Problème identifié et résolu :**
Le frontend utilisait une configuration Axios qui ne détectait pas correctement l'environnement de production sur le VPS.

## 🔧 **Solutions appliquées :**

### 1. **Configuration Axios intelligente** (`resources/js/bootstrap.js`)
- ✅ Détection automatique de l'environnement
- ✅ URL relative `/api/v1` pour le VPS
- ✅ URL complète `http://localhost:8000/api/v1` pour le développement local

### 2. **Fichier de configuration d'environnement** (`resources/js/config/env.js`)
- ✅ Configuration centralisée
- ✅ Gestion des environnements
- ✅ Debug en développement uniquement

### 3. **Script de build VPS** (`build-vps.sh`)
- ✅ Build optimisé pour la production
- ✅ Vérifications automatiques
- ✅ Instructions de déploiement

## 📋 **Étapes de déploiement sur VPS :**

### **Étape 1 : Build local**
```bash
# Exécuter le script de build
./build-vps.sh

# Ou manuellement
npm run build
```

### **Étape 2 : Upload sur VPS**
```bash
# Uploader le dossier build
scp -r public/build/ user@votre-vps:/path/to/laravel/public/

# Ou utiliser rsync
rsync -avz public/build/ user@votre-vps:/path/to/laravel/public/
```

### **Étape 3 : Vérifications sur VPS**

#### **A. Vérifier la structure des fichiers**
```bash
# Sur le VPS
ls -la /path/to/laravel/public/
# Doit contenir : build/, index.php, .htaccess
```

#### **B. Vérifier les permissions**
```bash
# Sur le VPS
chmod -R 755 /path/to/laravel/public/build/
chown -R www-data:www-data /path/to/laravel/public/build/
```

#### **C. Vérifier la configuration Laravel**
```bash
# Sur le VPS
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Étape 4 : Test de l'API**
```bash
# Sur le VPS
curl -s "http://votre-domaine.com/api/v1/products" | head -5
# Doit retourner du JSON avec les produits
```

### **Étape 5 : Test du frontend**
1. **Ouvrir** `http://votre-domaine.com/products`
2. **Vérifier** que les produits s'affichent
3. **Ouvrir la console** et vérifier :
   - `API Base URL: /api/v1`
   - `Environment: production`
   - Pas d'erreurs CORS ou 404

## 🐛 **Diagnostic des problèmes courants :**

### **Problème : Produits ne s'affichent pas**
**Solutions :**
1. ✅ Vérifier que l'API fonctionne : `curl /api/v1/products`
2. ✅ Vérifier la console du navigateur pour les erreurs
3. ✅ Vérifier que `window.axios.defaults.baseURL` est `/api/v1`
4. ✅ Vérifier que le build est bien uploadé

### **Problème : Erreurs CORS**
**Solutions :**
1. ✅ Vérifier `config/cors.php` sur le VPS
2. ✅ Vérifier que les headers sont bien configurés
3. ✅ Redémarrer le serveur web (nginx/apache)

### **Problème : Erreurs 404 sur l'API**
**Solutions :**
1. ✅ Vérifier `.htaccess` sur le VPS
2. ✅ Vérifier la configuration du serveur web
3. ✅ Vérifier que les routes Laravel sont bien chargées

## 🔍 **Commandes de diagnostic :**

### **Sur le VPS :**
```bash
# Vérifier les logs Laravel
tail -f storage/logs/laravel.log

# Vérifier les routes
php artisan route:list | grep api

# Vérifier la configuration
php artisan config:show app
php artisan config:show cors

# Vérifier les permissions
ls -la storage/
ls -la bootstrap/cache/
```

### **Dans le navigateur :**
```javascript
// Vérifier la configuration Axios
console.log('Base URL:', window.axios.defaults.baseURL);
console.log('Environment:', window.location.hostname);

// Tester une requête API
window.axios.get('/products').then(r => console.log(r)).catch(e => console.error(e));
```

## 📞 **Support :**
Si les problèmes persistent après avoir suivi cette checklist :
1. ✅ Vérifier les logs Laravel sur le VPS
2. ✅ Vérifier la console du navigateur
3. ✅ Tester l'API directement avec curl
4. ✅ Vérifier la configuration du serveur web

---

**🎯 Objectif :** Les produits doivent s'afficher sur `http://votre-domaine.com/products` sans erreurs dans la console.
