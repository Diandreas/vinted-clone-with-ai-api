import axios from 'axios';

// Configure axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';
window.axios.defaults.headers.common['Content-Type'] = 'application/json';

// Add CSRF token if available
let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Add API token from localStorage if available
const apiToken = localStorage.getItem('auth_token');
if (apiToken) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${apiToken}`;
}

// Configure base URL for API
// Si on est en développement (Vite), pointer vers le serveur Laravel
// Si on est en production, utiliser les URLs relatives
const isDevelopment = window.location.port && window.location.port !== '8000'
window.axios.defaults.baseURL = isDevelopment ? 'http://localhost:8000/api/v1' : '/api/v1';
