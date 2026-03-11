// ─── Firebase Messaging (notifications en arrière-plan) ─────────────────────
// Le SDK Firebase compat est chargé via CDN pour fonctionner dans le Service Worker.
// Ces valeurs sont publiques — elles identifient le projet Firebase côté client.
// Remplissez-les avec vos vraies valeurs depuis la console Firebase.
importScripts('https://www.gstatic.com/firebasejs/10.11.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.11.0/firebase-messaging-compat.js');

// ⚠️  Remplacez ces valeurs par celles de votre projet Firebase
//    Console Firebase → Paramètres du projet → Vos applications → Web
const FIREBASE_CONFIG = {
  apiKey:            'AIzaSyDohobRkc4XT_TNOC4UtYv-N107GDXp-jg',
  authDomain:        'rikeaa-a166c.firebaseapp.com',
  projectId:         'rikeaa-a166c',
  storageBucket:     'rikeaa-a166c.firebasestorage.app',
  messagingSenderId: '970678640320',
  appId:             '1:970678640320:web:c0c660a2d2bd597f7f5725',
};

if (FIREBASE_CONFIG.apiKey && FIREBASE_CONFIG.apiKey !== 'VOTRE_API_KEY') {
  firebase.initializeApp(FIREBASE_CONFIG);
  const messaging = firebase.messaging();

  // Notifications reçues quand l'app est en arrière-plan / fermée
  messaging.onBackgroundMessage((payload) => {
    const { title, body } = payload.notification || {};
    const data = payload.data || {};

    // Détermine l'URL de redirection selon le type de notification
    let url = '/';
    if (data.type === 'product_liked' || data.type === 'product_commented' || data.type === 'product_published') {
      url = `/products/${data.product_id}`;
    } else if (data.type === 'new_message') {
      url = `/conversations/${data.conversation_id}`;
    } else if (data.type === 'new_follower') {
      url = `/profile/${data.follower_id}`;
    }

    // Tag stable : une nouvelle notif du même type remplace l'ancienne
    // au lieu de s'empiler → évite le "Spam potentiel" Chrome
    let tag = 'rikeaa-notification';
    if (data.type === 'product_liked' || data.type === 'product_commented') {
      tag = `rikeaa-product-${data.product_id}`;
    } else if (data.type === 'new_message') {
      tag = `rikeaa-conv-${data.conversation_id}`;
    } else if (data.type === 'new_follower') {
      tag = `rikeaa-follower-${data.follower_id}`;
    }

    self.registration.showNotification(title || 'RIKEAA', {
      body: body || '',
      icon: '/logo.png',
      badge: '/logo.png',
      tag,
      renotify: true,
      vibrate: [100, 50, 100],
      data: { url, ...data },
      actions: [
        { action: 'open', title: 'Voir' },
        { action: 'close', title: 'Ignorer' },
      ],
    });
  });
}
// ─────────────────────────────────────────────────────────────────────────────

const CACHE_NAME = 'rikeaa-v2';
const STATIC_CACHE = 'rikeaa-static-v2';
const DYNAMIC_CACHE = 'rikeaa-dynamic-v2';

const urlsToCache = [
  '/',
  '/logo.png',
  '/manifest.json',
  '/apple-touch-icon.png',
  '/apple-touch-icon-152x152.png',
  '/apple-touch-icon-167x167.png'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
      .then(() => {
        return self.skipWaiting();
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      return self.clients.claim();
    })
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip chrome-extension and other non-http requests
  if (!url.protocol.startsWith('http')) {
    return;
  }

  // Skip cross-origin requests (CDN fonts, external scripts, etc.)
  // These can fail due to CORS when intercepted by the SW
  if (url.origin !== self.location.origin) {
    return;
  }

  // Handle API requests differently
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Clone the response for caching
          const responseClone = response.clone();
          caches.open(DYNAMIC_CACHE).then((cache) => {
            cache.put(request, responseClone);
          });
          return response;
        })
        .catch(() => {
          // Fallback to cache if network fails
          return caches.match(request);
        })
    );
    return;
  }

  // Handle static assets
  if (request.destination === 'image' ||
    request.destination === 'style' ||
    request.destination === 'script' ||
    request.destination === 'font') {
    event.respondWith(
      caches.match(request)
        .then((response) => {
          if (response) {
            return response;
          }
          return fetch(request)
            .then((response) => {
              // Cache the fetched resource
              if (response.status === 200) {
                const responseClone = response.clone();
                caches.open(DYNAMIC_CACHE).then((cache) => {
                  cache.put(request, responseClone);
                });
              }
              return response;
            });
        })
    );
    return;
  }

  // Handle navigation requests
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Clone the response for caching
          const responseClone = response.clone();
          caches.open(DYNAMIC_CACHE).then((cache) => {
            cache.put(request, responseClone);
          });
          return response;
        })
        .catch(() => {
          // Fallback to cached version
          return caches.match(request);
        })
    );
    return;
  }

  // Default strategy: cache first, then network
  event.respondWith(
    caches.match(request)
      .then((response) => {
        if (response) {
          return response;
        }
        return fetch(request);
      })
  );
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
  if (event.tag === 'background-sync') {
    event.waitUntil(doBackgroundSync());
  }
});

// Notification click — ouvre la bonne page selon le type
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  if (event.action === 'close') return;

  const data = event.notification.data || {};
  const url = data.url || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      // Si l'app est déjà ouverte, focus + navigation
      for (const client of clientList) {
        if ('focus' in client) {
          client.focus();
          client.postMessage({ type: 'NOTIFICATION_CLICK', url, data });
          return;
        }
      }
      // Sinon ouvre une nouvelle fenêtre
      return clients.openWindow(url);
    })
  );
});

// Background sync function
async function doBackgroundSync() {
  try {
    // Implement background sync logic here
    console.log('Background sync completed');
  } catch (error) {
    console.error('Background sync failed:', error);
  }
}

// Handle offline/online events
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
