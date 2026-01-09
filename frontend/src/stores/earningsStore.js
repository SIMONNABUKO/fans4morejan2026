import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useEarningsStore = defineStore("earnings", {
  state: () => ({
    // Statistics data
    currentPeriodStats: {
      period: { start: '', end: '' },
      daily_data: [],
      stats: [],
      gross_income: 0,
      net_income: 0,
      total: 0
    },
    monthlyStats: [],
    
    // Wallet data
    walletData: {
      total_balance: 0,
      pending_balance: 0,
      available_for_payout: 0
    },
    walletHistory: [],
    payoutMethods: [],
    payoutRequests: [],
    
    // UI state
    loading: false,
    walletLoading: false,
    error: null
  }),

  actions: {
    // Statistics
    async fetchEarningsStatistics(params = {}) {
      this.loading = true
      this.error = null
      
      try {
        console.log('Fetching earnings statistics with params:', params)
        
        // Build query parameters
        const queryParams = new URLSearchParams()
        if (params.period) queryParams.append('period', params.period)
        if (params.start_date) queryParams.append('start_date', params.start_date)
        if (params.end_date) queryParams.append('end_date', params.end_date)
        
        const queryString = queryParams.toString() ? `?${queryParams.toString()}` : ''
        console.log('Request URL:', `/earnings/statistics${queryString}`)
        
        const response = await axiosInstance.get(`/earnings/statistics${queryString}`)
        console.log('Statistics API Response:', response)
        
        if (response.data.success) {
          this.currentPeriodStats = response.data.data.current_period
          this.monthlyStats = response.data.data.monthly_stats
          console.log('Updated store state:', {
            currentPeriodStats: this.currentPeriodStats,
            monthlyStats: this.monthlyStats
          })
        } else {
          throw new Error(response.data.message || 'Failed to load statistics')
        }
        
        return response.data
      } catch (error) {
        console.error('Error in fetchEarningsStatistics:', {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        })
        this.error = error.response?.data?.message || "An error occurred while fetching statistics"
        throw error
      } finally {
        this.loading = false
      }
    },
    
    // Wallet
    async fetchWalletData() {
      this.walletLoading = true
      this.error = null
      
      try {
        console.log('Fetching wallet data from API...')
        const response = await axiosInstance.get('/wallet/balance')
        console.log('Wallet API Response:', response)
        
        if (!response.data) {
          throw new Error('No data received from wallet balance API')
        }
        
        this.walletData = response.data
        return response.data
      } catch (error) {
        console.error('Error in fetchWalletData:', {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status
        })
        this.error = error.response?.data?.message || "An error occurred while fetching wallet data"
        throw error
      } finally {
        this.walletLoading = false
      }
    },
    
    async fetchWalletHistory(params = {}) {
      this.walletLoading = true
      this.error = null
      
      try {
        // Build query parameters
        const queryParams = new URLSearchParams()
        if (params.per_page) queryParams.append('per_page', params.per_page)
        if (params.page) queryParams.append('page', params.page)
        if (params.start_date) queryParams.append('start_date', params.start_date)
        if (params.end_date) queryParams.append('end_date', params.end_date)
        if (params.transaction_type) queryParams.append('transaction_type', params.transaction_type)
        if (params.status) queryParams.append('status', params.status)
        
        const queryString = queryParams.toString() ? `?${queryParams.toString()}` : ''
        const response = await axiosInstance.get(`/wallet/history${queryString}`)
        
        if (response.data.success) {
          this.walletHistory = response.data.data.data // Paginated data
        } else {
          throw new Error(response.data.message || 'Failed to load wallet history')
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while fetching wallet history"
        throw error
      } finally {
        this.walletLoading = false
      }
    },
    
    // Payout Methods
    async fetchPayoutMethods() {
      this.walletLoading = true
      this.error = null
      
      try {
        const response = await axiosInstance.get('/payout-methods')
        
        if (response.data.success) {
          this.payoutMethods = response.data.data
        } else {
          throw new Error(response.data.message || 'Failed to load payout methods')
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while fetching payout methods"
        throw error
      } finally {
        this.walletLoading = false
      }
    },
    
    async addPayoutMethod(payoutMethodData) {
      this.walletLoading = true
      this.error = null
      
      try {
        const response = await axiosInstance.post('/payout-methods', payoutMethodData)
        
        if (response.data.success) {
          // Add the new method to the list
          this.payoutMethods.push(response.data.data)
        } else {
          throw new Error(response.data.message || 'Failed to add payout method')
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while adding payout method"
        throw error
      } finally {
        this.walletLoading = false
      }
    },
    
    async deletePayoutMethod(payoutMethodId) {
      this.walletLoading = true
      this.error = null
      
      try {
        const response = await axiosInstance.delete(`/payout-methods/${payoutMethodId}`)
        
        if (response.data.success) {
          // Remove the method from the list
          this.payoutMethods = this.payoutMethods.filter(method => method.id !== payoutMethodId)
        } else {
          throw new Error(response.data.message || 'Failed to delete payout method')
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while deleting payout method"
        throw error
      } finally {
        this.walletLoading = false
      }
    },
    
    async setDefaultPayoutMethod(payoutMethodId) {
      this.walletLoading = true
      this.error = null
      
      try {
        const response = await axiosInstance.post(`/payout-methods/${payoutMethodId}/set-default`)
        
        if (response.data.success) {
          // Update the default status for all methods
          this.payoutMethods = this.payoutMethods.map(method => ({
            ...method,
            is_default: method.id === payoutMethodId
          }))
        } else {
          throw new Error(response.data.message || 'Failed to set default payout method')
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while setting default payout method"
        throw error
      } finally {
        this.walletLoading = false
      }
    },
    
    // Payout Requests
    async fetchPayoutRequests() {
      this.walletLoading = true
      this.error = null
      
      try {
        const response = await axiosInstance.get('/payout-requests')
        
        if (response.data.success) {
          this.payoutRequests = response.data.data
        } else {
          throw new Error(response.data.message || 'Failed to load payout requests')
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while fetching payout requests"
        throw error
      } finally {
        this.walletLoading = false
      }
    },
    
    async requestPayout(payoutData) {
      this.walletLoading = true
      this.error = null
      
      try {
        const response = await axiosInstance.post('/payout-requests', payoutData)
        
        if (response.data.success) {
          // Add the new request to the list
          this.payoutRequests.unshift(response.data.data)
          
          // Update wallet balances
          await this.fetchWalletData()
        } else {
          throw new Error(response.data.message || 'Failed to request payout')
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while requesting payout"
        throw error
      } finally {
        this.walletLoading = false
      }
    },
    
    async cancelPayoutRequest(payoutRequestId) {
      this.walletLoading = true
      this.error = null
      
      try {
        const response = await axiosInstance.post(`/payout-requests/${payoutRequestId}/cancel`)
        
        if (response.data.success) {
          // Update the request in the list
          this.payoutRequests = this.payoutRequests.map(request => 
            request.id === payoutRequestId ? response.data.data : request
          )
          
          // Update wallet balances
          await this.fetchWalletData()
        } else {
          throw new Error(response.data.message || 'Failed to cancel payout request')
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while cancelling payout request"
        throw error
      } finally {
        this.walletLoading = false
      }
    },
    
    // Load all wallet-related data at once
    async loadWalletDashboard() {
      this.walletLoading = true
      this.error = null
      
      try {
        await Promise.all([
          this.fetchWalletData(),
          this.fetchPayoutMethods(),
          this.fetchPayoutRequests(),
          this.fetchWalletHistory({ per_page: 10 })
        ])
        
        return {
          wallet: this.walletData,
          payoutMethods: this.payoutMethods,
          payoutRequests: this.payoutRequests,
          walletHistory: this.walletHistory
        }
      } catch (error) {
        this.error = error.message || "An error occurred while loading wallet dashboard"
        throw error
      } finally {
        this.walletLoading = false
      }
    }
  }
})