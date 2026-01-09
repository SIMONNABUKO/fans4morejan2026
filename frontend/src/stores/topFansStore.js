import { defineStore } from 'pinia'
import axiosInstance from '@/axios'

export const useTopFansStore = defineStore('topFans', {
  state: () => ({
    loading: false,
    error: null,
    fansData: {
      top_fans: [],
      fans_summary: {
        active_fans: 0,
        retention_rate: 0
      },
      fans_chart_data: [],
      period: {
        start: '',
        end: ''
      }
    }
  }),

  getters: {
    topFans: (state) => state.fansData.top_fans,
    fansSummary: (state) => state.fansData.fans_summary,
    fansChartData: (state) => state.fansData.fans_chart_data,
    period: (state) => state.fansData.period
  },

  actions: {
    async fetchTopFansStatistics(params = {}) {
      this.loading = true
      this.error = null
      
      try {
        // Build query parameters
        const queryParams = new URLSearchParams()
        if (params.period) queryParams.append('period', params.period)
        if (params.filter) queryParams.append('filter', params.filter)
        if (params.start_date) queryParams.append('start_date', params.start_date)
        if (params.end_date) queryParams.append('end_date', params.end_date)
        
        const queryString = queryParams.toString() ? `?${queryParams.toString()}` : ''
        const response = await axiosInstance.get(`/statistics/top-fans${queryString}`)
        
        if (response.data.success) {
          this.fansData = response.data.data
        } else {
          throw new Error(response.data.message || 'Failed to load top fans statistics')
        }
        
        return this.fansData
      } catch (error) {
        this.error = error.response?.data?.message || error.message || 'An error occurred while fetching top fans statistics'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
}) 