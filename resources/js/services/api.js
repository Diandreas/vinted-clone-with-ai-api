import axios from 'axios'
import { isNative } from '../utils/platform'

// Create axios instance
const api = axios.create({
  baseURL: isNative() ? 'https://rikeaa.com/api/v1' : (import.meta.env.VITE_API_URL || '/api/v1'),
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
    
    // Debug logs removed for production cleanliness
    
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
    // Debug logs removed for production cleanliness
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
      // Debug logs removed for production cleanliness
      // Note: ne pas importer useAuthStore ici car ça cause des problèmes circulaires
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    
    return Promise.reject(error)
  }
)

export default api
