import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useWalletStore = defineStore('wallet', () => {
  // State
  const balance = ref(0)
  const balanceFormatted = ref('0 FCFA')
  const currency = ref('XAF')
  const lastUpdated = ref(null)
  const transactions = ref([])
  const loading = ref(false)
  const transactionLoading = ref(false)
  
  // Statistics
  const totalDeposits = ref(0)
  const totalWithdrawals = ref(0)
  const transactionCount = ref(0)
  const lastTransactionDate = ref(null)
  
  // Getters
  const hasBalance = computed(() => balance.value > 0)
  const balanceInCFA = computed(() => balance.value)
  const recentTransactions = computed(() => transactions.value.slice(0, 5))
  
  // Statistics getters
  const monthlyStats = computed(() => {
    const now = new Date()
    const currentMonth = now.getMonth()
    const currentYear = now.getFullYear()
    
    const monthlyTransactions = transactions.value.filter(t => {
      const transactionDate = new Date(t.created_at)
      return transactionDate.getMonth() === currentMonth && 
             transactionDate.getFullYear() === currentYear
    })
    
    const deposits = monthlyTransactions
      .filter(t => t.purpose === 'topup' && t.status === 'completed')
      .reduce((sum, t) => sum + t.amount_xaf, 0)
      
    const withdrawals = monthlyTransactions
      .filter(t => t.purpose === 'payout' && t.status === 'completed')
      .reduce((sum, t) => sum + t.amount_xaf, 0)
    
    return {
      deposits,
      withdrawals,
      net: deposits - withdrawals,
      transactionCount: monthlyTransactions.length
    }
  })
  
  // Actions
  const fetchBalance = async () => {
    try {
      loading.value = true
      const response = await axios.get('/wallet/balance')
      
      if (response.data.success) {
        const data = response.data.data
        balance.value = data.balance_xaf
        balanceFormatted.value = data.balance_formatted
        currency.value = data.currency
        lastUpdated.value = data.last_updated
      }
      
      return response.data
    } catch (error) {
      console.error('Failed to fetch wallet balance:', error)
      throw error
    } finally {
      loading.value = false
    }
  }
  
  const fetchTransactions = async (page = 1) => {
    try {
      transactionLoading.value = true
      const response = await axios.get(`/wallet/transactions?page=${page}`)
      
      if (response.data.success) {
        if (page === 1) {
          transactions.value = response.data.data.data
        } else {
          transactions.value.push(...response.data.data.data)
        }
        
        // Update statistics
        updateStatistics()
      }
      
      return response.data
    } catch (error) {
      console.error('Failed to fetch transactions:', error)
      throw error
    } finally {
      transactionLoading.value = false
    }
  }
  
  const topUp = async (amount, message = null) => {
    try {
      loading.value = true
      const response = await axios.post('/wallet/topup', {
        amount_xaf: amount,
        message
      })
      
      if (response.data.success) {
        // Return the authorization URL for NotchPay
        return response.data
      }
      
      return response.data
    } catch (error) {
      console.error('Failed to initiate topup:', error)
      throw error
    } finally {
      loading.value = false
    }
  }
  
  
  const updateStatistics = () => {
    const completedTransactions = transactions.value.filter(t => t.status === 'completed')
    
    totalDeposits.value = completedTransactions
      .filter(t => t.purpose === 'topup')
      .reduce((sum, t) => sum + t.amount_xaf, 0)
      
    totalWithdrawals.value = completedTransactions
      .filter(t => t.purpose === 'payout')
      .reduce((sum, t) => sum + t.amount_xaf, 0)
      
    transactionCount.value = completedTransactions.length
    
    if (completedTransactions.length > 0) {
      lastTransactionDate.value = completedTransactions[0].created_at
    }
  }
  
  const formatAmount = (amount) => {
    return `${amount.toLocaleString()} FCFA`
  }
  
  const getTransactionIcon = (transaction) => {
    switch (transaction.purpose) {
      case 'topup':
        return 'plus-circle'
      case 'payout':
        return 'minus-circle'
      case 'order_payment':
        return 'shopping-cart'
      case 'refund':
        return 'dollar-sign'
      default:
        return 'circle'
    }
  }
  
  const getTransactionColor = (transaction) => {
    if (transaction.status === 'failed') return 'text-gray-500'
    if (transaction.status === 'pending') return 'text-gray-500'
    
    switch (transaction.purpose) {
      case 'topup':
      case 'refund':
        return 'text-green-500'
      case 'payout':
      case 'order_payment':
        return 'text-gray-500'
      default:
        return 'text-gray-500'
    }
  }
  
  const refreshWallet = async () => {
    await Promise.all([
      fetchBalance(),
      fetchTransactions()
    ])
  }
  
  return {
    // State
    balance,
    balanceFormatted,
    currency,
    lastUpdated,
    transactions,
    loading,
    transactionLoading,
    totalDeposits,
    totalWithdrawals,
    transactionCount,
    lastTransactionDate,
    
    // Getters
    hasBalance,
    balanceInCFA,
    recentTransactions,
    monthlyStats,
    
    // Actions
    fetchBalance,
    fetchTransactions,
    topUp,
    refreshWallet,
    formatAmount,
    getTransactionIcon,
    getTransactionColor
  }
})