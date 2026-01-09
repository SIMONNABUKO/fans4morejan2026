import { defineStore } from 'pinia'
import axiosInstance from '@/axios'

export const useReachStore = defineStore('reach', {
  state: () => ({
    loading: false,
    error: null,
    reachData: {
      profile_visitors: [],
      view_duration: [],
      top_countries: [],
      reach_summary: {
        total_reach: 0,
        engagement_rate: 0
      },
      period: {
        start: '',
        end: ''
      }
    }
  }),

  getters: {
    profileVisitors: (state) => state.reachData.profile_visitors,
    viewDuration: (state) => state.reachData.view_duration,
    topCountries: (state) => state.reachData.top_countries,
    reachSummary: (state) => state.reachData.reach_summary,
    period: (state) => state.reachData.period
  },

  actions: {
    async fetchReachStatistics(params = {}) {
      this.loading = true
      this.error = null
      
      try {
        // Build query parameters
        const queryParams = new URLSearchParams()
        if (params.period) queryParams.append('period', params.period)
        if (params.start_date) queryParams.append('start_date', params.start_date)
        if (params.end_date) queryParams.append('end_date', params.end_date)
        
        const queryString = queryParams.toString() ? `?${queryParams.toString()}` : ''
        const response = await axiosInstance.get(`/statistics/reach${queryString}`)
        
        if (response.data.success) {
          this.reachData = response.data.data
        } else {
          throw new Error(response.data.message || 'Failed to load reach statistics')
        }
        
        return this.reachData
      } catch (error) {
        this.error = error.response?.data?.message || error.message || 'An error occurred while fetching reach statistics'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
}) 