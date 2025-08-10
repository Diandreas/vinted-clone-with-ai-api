import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationStore = defineStore('notification', () => {
  // State
  const notifications = ref([])
  
  // Actions
  const addNotification = (notification) => {
    const id = Date.now().toString()
    const newNotification = {
      id,
      type: 'info',
      title: '',
      message: '',
      duration: 5000,
      ...notification
    }
    
    notifications.value.push(newNotification)
    
    // Auto-remove after duration
    if (newNotification.duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, newNotification.duration)
    }
    
    return id
  }
  
  const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }
  
  const clearAll = () => {
    notifications.value = []
  }
  
  // Helper methods
  const success = (message, title = 'Succès', options = {}) => {
    return addNotification({
      type: 'success',
      title,
      message,
      ...options
    })
  }
  
  const error = (message, title = 'Erreur', options = {}) => {
    return addNotification({
      type: 'error',
      title,
      message,
      duration: 0, // Don't auto-remove error notifications
      ...options
    })
  }
  
  const info = (message, title = 'Information', options = {}) => {
    return addNotification({
      type: 'info',
      title,
      message,
      ...options
    })
  }
  
  const warning = (message, title = 'Attention', options = {}) => {
    return addNotification({
      type: 'warning',
      title,
      message,
      ...options
    })
  }
  
  return {
    // State
    notifications,
    
    // Actions
    addNotification,
    removeNotification,
    clearAll,
    
    // Helper methods
    success,
    error,
    info,
    warning
  }
})

