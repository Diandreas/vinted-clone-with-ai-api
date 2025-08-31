import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
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
  const isAdmin = computed(() => !!(user.value?.is_admin) || user.value?.role === 'admin')

  const hasPermission = (perm) => {
    const perms = user.value?.permissions
    if (!perms) return false
    if (Array.isArray(perms)) return perms.includes(perm)
    if (typeof perms === 'object') return !!perms[perm]
    return false
  }
  
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
    api.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
    // Also update the global axios instance for compatibility
    if (window.axios) {
      window.axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`
    }
  }
  
  const clearAuthToken = () => {
    token.value = null
    localStorage.removeItem('auth_token')
    delete api.defaults.headers.common['Authorization']
    // Also clear the global axios instance for compatibility
    if (window.axios) {
      delete window.axios.defaults.headers.common['Authorization']
    }
  }
  
  const fetchUser = async () => {
    try {
      const response = await api.get('/auth/user')
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
      const response = await api.post('/auth/login', credentials)
      
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
      const response = await api.post('/auth/register', userData)
      
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
        await api.post('/auth/logout')
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
      const response = await api.post('/auth/forgot-password', { email })
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
      const response = await api.post('/auth/reset-password', data)
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
      const response = await api.put('/auth/update-profile', profileData)
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
      const response = await api.post('/auth/change-password', passwordData)
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
      await api.delete('/auth/delete-account')
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
    isAdmin,
    
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
    fetchUser,
    hasPermission
  }
})

