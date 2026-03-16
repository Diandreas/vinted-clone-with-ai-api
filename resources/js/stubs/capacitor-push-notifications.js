// Stub web-only — @capacitor/push-notifications n'est utilisé que sur Android/iOS natif.
// Ce fichier est aliasé dans vite.config.js pour éviter l'erreur de résolution Rollup/Vite.
// Le code qui importe ce module est toujours protégé par isNative() donc jamais exécuté sur web.
export const PushNotifications = {}
