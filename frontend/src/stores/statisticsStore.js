import { defineStore } from 'pinia'
import axiosInstance from '@/axios'

export const useStatisticsStore = defineStore('statistics', {
  state: () => ({
    loading: false,
    error: null,
    overview: {
      period: { start: '', end: '' },
      chart: [],
      total: { gross: 0, net: 0 },
      breakdown: [],
      monthly_summary: { month: '', net: 0 },
      top_source: null,
      conversion_rate: 0
    },
    // For future: analytics, reach, fans, etc.
  }),

  actions: {
    async fetchOverviewStatistics(params = {}) {
      this.loading = true
      this.error = null
      try {
        // Build query parameters
        const queryParams = new URLSearchParams()
        if (params.period) queryParams.append('period', params.period)
        if (params.start_date) queryParams.append('start_date', params.start_date)
        if (params.end_date) queryParams.append('end_date', params.end_date)
        const queryString = queryParams.toString() ? `?${queryParams.toString()}` : ''
        const response = await axiosInstance.get(`/earnings/statistics${queryString}`)
        if (response.data.success && response.data.data && response.data.data.current_period) {
          this.overview = response.data.data.current_period
        } else {
          throw new Error(response.data.message || 'Failed to load statistics')
        }
        return this.overview
      } catch (error) {
        this.error = error.response?.data?.message || error.message || 'An error occurred while fetching statistics'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
}) 