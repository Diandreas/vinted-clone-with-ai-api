import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token'))
  const initialized = ref(false)
  const loading = ref(false)
  
  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isVerified = computed(() => user.value?.is_verified || false)
  
  // Actions
  const initialize = async () => {
    if (token.value) {
      try {
        setAuthToken(token.value)
        await fetchUser()
      } catch (error) {
        console.error('Failed to initialize auth:', error)
        logout()
      }
    }
    initialized.value = true
  }
  
  const setAuthToken = (authToken) => {
    token.value = authToken
    localStorage.setItem('auth_token', authToken)
    axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
  }
  
  const clearAuthToken = () => {
    token.value = null
    localStorage.removeItem('auth_token')
    delete axios.defaults.headers.common['Authorization']
  }
  
  const fetchUser = async () => {
    try {
      const response = await axios.get('/auth/user')
      user.value = response.data.user
      return response.data.user
    } catch (error) {
      console.error('Failed to fetch user:', error)
      throw error
    }
  }
  
  const login = async (credentials) => {
    loading.value = true
    try {
      const response = await axios.post('/auth/login', credentials)
      
      setAuthToken(response.data.token)
      user.value = response.data.user
      
      // Redirect to intended page or dashboard
      const redirect = router.currentRoute.value.query.redirect || '/dashboard'
      router.push(redirect)
      
      return response.data
    } catch (error) {
      console.error('Login failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }
  
  const register = async (userData) => {
    loading.value = true
    try {
      const response = await axios.post('/auth/register', userData)
      
      setAuthToken(response.data.token)
      user.value = response.data.user
      
      router.push('/dashboard')
      
      return response.data
    } catch (error) {
      console.error('Registration failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }
  
  const logout = async () => {
    loading.value = true
    try {
      if (token.value) {
        await axios.post('/auth/logout')
      }
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      user.value = null
      clearAuthToken()
      loading.value = false
      router.push('/')
    }
  }
  
  const forgotPassword = async (email) => {
    loading.value = true
    try {
      const response = await axios.post('/auth/forgot-password', { email })
      return response.data
    } catch (error) {
      console.error('Forgot password failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }
  
  const resetPassword = async (data) => {
    loading.value = true
    try {
      const response = await axios.post('/auth/reset-password', data)
      return response.data
    } catch (error) {
      console.error('Reset password failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }
  
  const updateProfile = async (profileData) => {
    loading.value = true
    try {
      const response = await axios.put('/auth/update-profile', profileData)
      user.value = { ...user.value, ...response.data.user }
      return response.data
    } catch (error) {
      console.error('Profile update failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }
  
  const changePassword = async (passwordData) => {
    loading.value = true
    try {
      const response = await axios.post('/auth/change-password', passwordData)
      return response.data
    } catch (error) {
      console.error('Password change failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }
  
  const deleteAccount = async () => {
    loading.value = true
    try {
      await axios.delete('/auth/delete-account')
      logout()
    } catch (error) {
      console.error('Account deletion failed:', error)
      throw error
    } finally {
      loading.value = false
    }
  }
  
  return {
    // State
    user,
    token,
    initialized,
    loading,
    
    // Getters
    isAuthenticated,
    isVerified,
    
    // Actions
    initialize,
    login,
    register,
    logout,
    forgotPassword,
    resetPassword,
    updateProfile,
    changePassword,
    deleteAccount,
    fetchUser
  }
})

