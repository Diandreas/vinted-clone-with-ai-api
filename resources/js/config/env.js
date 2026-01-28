// Configuration d'environnement pour le frontend
export const config = {
  // URLs de l'API selon l'environnement
  api: {
    development: 'http://localhost:8000/api/v1',
    production: '/api/v1', // URL relative pour le VPS
    staging: '/api/v1'
  },

  // Détection automatique de l'environnement
  get current() {
    if (window.location.hostname === 'localhost') {
      return 'development';
    }
    return 'production';
  },

  // URL de base de l'API
  get baseURL() {
    return this.api[this.current];
  },

  // Debug
  debug: window.location.hostname === 'localhost'
};

// Log de la configuration (uniquement en développement)
if (config.debug) {

}
