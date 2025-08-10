# 🚀 Instructions de Connexion - VintedClone

## 📍 URLs d'Accès

### 🖥️ **Application Principale (Production)**
```
http://localhost:8000
```
> ✅ Utiliser cette URL pour tester l'application complète

### ⚡ **Serveur de Développement (Vite)**
```
http://localhost:5173
```
> ⚠️ Cette URL est pour le développement Frontend uniquement

---

## 🔐 **Identifiants de Test**

### Admin
- **Email :** `admin@example.com`
- **Mot de passe :** `password`

---

## 🛠️ **URLs de Gestion Admin**

Une fois connecté, accédez aux interfaces d'administration :

- **Dashboard :** http://localhost:8000/dashboard
- **Gestion Produits :** http://localhost:8000/admin/products
- **Gestion Utilisateurs :** http://localhost:8000/admin/users

---

## 🐛 **En cas de Problème**

### Erreur 422 lors de la connexion
- ✅ Assurez-vous d'utiliser `http://localhost:8000`
- ✅ Vérifiez que les serveurs tournent :
  ```bash
  # Serveur Laravel
  php artisan serve --port=8000
  
  # Serveur Vite (optionnel)
  npm run dev
  ```

### CORS Error
- ✅ Les paramètres CORS sont configurés
- ✅ L'application détecte automatiquement l'environnement

---

## 🎯 **Fonctionnalités Disponibles**

- ✅ **Authentification complète** (Login/Register/Forgot Password)
- ✅ **Dashboard interactif** avec statistiques
- ✅ **CRUD Produits** complet avec filtres
- ✅ **CRUD Utilisateurs** avec gestion des profils
- ✅ **Interface responsive** mobile-friendly
- ✅ **API REST** documentée et fonctionnelle

---

## 🔄 **Commandes Utiles**

```bash
# Redémarrer les serveurs
php artisan serve --port=8000 &
npm run dev &

# Reconstruire les assets
npm run build

# Réinitialiser la DB
php artisan migrate:fresh --seed
```





## 📍 URLs d'Accès

### 🖥️ **Application Principale (Production)**
```
http://localhost:8000
```
> ✅ Utiliser cette URL pour tester l'application complète

### ⚡ **Serveur de Développement (Vite)**
```
http://localhost:5173
```
> ⚠️ Cette URL est pour le développement Frontend uniquement

---

## 🔐 **Identifiants de Test**

### Admin
- **Email :** `admin@example.com`
- **Mot de passe :** `password`

---

## 🛠️ **URLs de Gestion Admin**

Une fois connecté, accédez aux interfaces d'administration :

- **Dashboard :** http://localhost:8000/dashboard
- **Gestion Produits :** http://localhost:8000/admin/products
- **Gestion Utilisateurs :** http://localhost:8000/admin/users

---

## 🐛 **En cas de Problème**

### Erreur 422 lors de la connexion
- ✅ Assurez-vous d'utiliser `http://localhost:8000`
- ✅ Vérifiez que les serveurs tournent :
  ```bash
  # Serveur Laravel
  php artisan serve --port=8000
  
  # Serveur Vite (optionnel)
  npm run dev
  ```

### CORS Error
- ✅ Les paramètres CORS sont configurés
- ✅ L'application détecte automatiquement l'environnement

---

## 🎯 **Fonctionnalités Disponibles**

- ✅ **Authentification complète** (Login/Register/Forgot Password)
- ✅ **Dashboard interactif** avec statistiques
- ✅ **CRUD Produits** complet avec filtres
- ✅ **CRUD Utilisateurs** avec gestion des profils
- ✅ **Interface responsive** mobile-friendly
- ✅ **API REST** documentée et fonctionnelle

---

## 🔄 **Commandes Utiles**

```bash
# Redémarrer les serveurs
php artisan serve --port=8000 &
npm run dev &

# Reconstruire les assets
npm run build

# Réinitialiser la DB
php artisan migrate:fresh --seed
```



