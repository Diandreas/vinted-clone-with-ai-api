import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './App.vue';
import { initIOSUtils } from './utils/iosUtils';
import { isAndroid } from './utils/platform';

// Initialize iOS utilities
initIOSUtils();

// Add Android class for safe area insets
if (isAndroid()) {
    document.body.classList.add('android-device');
}

// Create Vue app
const app = createApp(App);

// Use Pinia for state management
app.use(createPinia());

// Use Vue Router
app.use(router);

// Mount the app
app.mount('#app');
