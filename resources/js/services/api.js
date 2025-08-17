import axios from 'axios'

// Create axios instance
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api/v1',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    
    console.log('📡 API Request:', {
      method: config.method.toUpperCase(),
      url: config.url,
      fullURL: config.baseURL + config.url,
      hasToken: !!token,
      tokenPreview: token ? token.substring(0, 20) + '...' : 'None',
      data: config.data
    })
    
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    return config
  },
  (error) => {
    console.error('❌ API Request Error:', error)
    return Promise.reject(error)
  }
)

// Response interceptor to handle auth errors
api.interceptors.response.use(
  (response) => {
    console.log('✅ API Response:', {
      status: response.status,
      url: response.config.url,
      data: response.data
    })
    return response
  },
  (error) => {
    console.error('❌ API Response Error:', {
      status: error.response?.status,
      url: error.config?.url,
      message: error.message,
      data: error.response?.data
    })
    
    if (error.response?.status === 401) {
      console.log('🔓 Authentification expirée, redirection vers login')
      // Note: ne pas importer useAuthStore ici car ça cause des problèmes circulaires
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    
    return Promise.reject(error)
  }
)

export default api