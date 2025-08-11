import axios from 'axios';
import { config } from './config/env.js';

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

// Configure base URL for API using environment config
window.axios.defaults.baseURL = config.baseURL;

// Debug: Afficher l'URL de base en console (uniquement en développement)
if (config.debug) {
    console.log('API Base URL:', window.axios.defaults.baseURL);
    console.log('Environment:', config.current);
}
