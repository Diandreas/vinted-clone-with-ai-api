# 🚀 Configuration Messagerie Ultra-Rapide

## Architecture Hybride Redis + MySQL + Pusher

### ✅ Ce qui a été implémenté :

1. **MessageCacheService** - Cache intelligent Redis
   - 50 derniers messages en cache (TTL 24h)
   - Online/offline status (TTL 5min)
   - Typing indicators (TTL 10s)
   - Compteurs non-lus temps réel

2. **MessageController Optimisé**
   - Lecture depuis Redis (ultra-rapide)
   - Fallback MySQL pour historique
   - Broadcasting Pusher activé
   - Endpoints typing/online status

3. **Events Pusher**
   - `MessageSent` - Nouveaux messages
   - `UserTyping` - Indicateurs de frappe
   - Channels privés par conversation

---

## 📋 Configuration Requise

### 1️⃣ **Installer Redis** (si pas déjà fait)

**Windows :**
```bash
# Via Chocolatey
choco install redis-64

# Démarrer Redis
redis-server
```

**Linux/Mac :**
```bash
sudo apt install redis-server
redis-server
```

**Docker :**
```bash
docker run -d -p 6379:6379 redis:alpine
```

### 2️⃣ **Configurer Pusher** (Gratuit jusqu'à 200k messages/jour)

1. Créer compte sur https://pusher.com (gratuit)
2. Créer une nouvelle app "Channels"
3. Copier vos credentials dans `.env` :

```env
BROADCAST_DRIVER=pusher
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

PUSHER_APP_ID=votre_app_id
PUSHER_APP_KEY=votre_app_key
PUSHER_APP_SECRET=votre_app_secret
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=eu
```

### 3️⃣ **Routes API à ajouter**

Ajouter dans `routes/api.php` :

```php
// Messages avec cache Redis
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::post('/messages/{message}/read', [MessageController::class, 'markAsRead']);

    // Typing indicators & Online status
    Route::post('/conversations/{conversation}/typing', [MessageController::class, 'typing']);
    Route::get('/conversations/{conversation}/typing', [MessageController::class, 'getTyping']);
    Route::post('/users/online', [MessageController::class, 'setOnline']);
    Route::post('/users/check-online', [MessageController::class, 'checkOnline']);

    // Cache stats (admin)
    Route::get('/messages/cache-stats', [MessageController::class, 'cacheStats']);
});
```

### 4️⃣ **Frontend Vue.js - Laravel Echo**

Installer Laravel Echo + Pusher :

```bash
npm install --save laravel-echo pusher-js
```

Configurer dans `resources/js/bootstrap.js` :

```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});
```

Ajouter dans `.env` :

```env
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

### 5️⃣ **Utilisation Frontend**

```js
// Écouter nouveaux messages
Echo.channel(`conversation.${conversationId}`)
    .listen('.message.sent', (e) => {
        console.log('Nouveau message:', e.message);
        // Ajouter à la liste
    })
    .listen('.user.typing', (e) => {
        console.log(`User ${e.user_id} is typing...`);
        // Afficher "... est en train d'écrire"
    });

// Envoyer typing indicator
const sendTypingIndicator = () => {
    axios.post(`/api/conversations/${conversationId}/typing`);
};

// Vérifier si user est en ligne
const checkOnline = async (userId) => {
    const { data } = await axios.post('/api/users/check-online', { user_id: userId });
    return data.data.is_online;
};

// Mettre à jour status online (toutes les 4 min)
setInterval(() => {
    axios.post('/api/users/online');
}, 240000);
```

---

## 📊 Performance Attendue

| Métrique | Avant | Après |
|----------|-------|-------|
| Chargement messages | 200-500ms | 5-20ms (Redis) |
| Envoi message | 150-300ms | 30-80ms |
| Temps réel | ❌ Polling | ✅ WebSocket |
| Scalabilité | ~1,000 users | ~100,000+ users |

---

## 🧪 Tester la Configuration

```bash
# 1. Vérifier Redis
redis-cli ping
# Doit retourner: PONG

# 2. Tester cache
php artisan tinker
>>> Redis::set('test', 'hello');
>>> Redis::get('test');
# Doit retourner: "hello"

# 3. Tester Pusher (via dashboard Pusher)
# Aller sur https://dashboard.pusher.com
# Debug Console > Envoyer un message test

# 4. Clear cache
php artisan cache:clear
php artisan config:clear
```

---

## 🔥 Optimisations Supplémentaires

### Pour 1M+ utilisateurs :

1. **Redis Cluster** (Sharding)
```bash
# Répartir la charge sur plusieurs Redis
REDIS_CLUSTER=true
REDIS_NODES="127.0.0.1:6379,127.0.0.1:6380,127.0.0.1:6381"
```

2. **Compression Messages**
```php
// Dans MessageCacheService
$compressed = gzencode(json_encode($messages), 9);
Redis::setex($key, $ttl, $compressed);
```

3. **CDN pour Médias**
```env
# Cloudflare R2, AWS S3, etc.
FILESYSTEM_DISK=s3
AWS_BUCKET=your-bucket
```

4. **Queue Jobs**
```bash
# Déporter broadcast vers queue
php artisan queue:work --queue=broadcasts
```

---

## 🎯 Capacité Finale

- **Redis gratuit (25MB)** : ~400 conversations actives
- **Redis $10 (250MB)** : ~4,500 conversations actives
- **Redis $30 (1GB)** : ~18,000 conversations actives
- **Redis Cluster** : Illimité (scale horizontalement)

**MySQL** : Historique complet (millards de messages possibles)

---

## 📱 Prochaine Étape : Push Notifications Mobile

Pour notifications mobiles (Android/iOS), installer Firebase :

```bash
composer require kreait/firebase-php
```

Voir `FIREBASE_SETUP.md` (à créer si besoin)
