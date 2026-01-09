import { defineStore } from 'pinia'
import axiosInstance from '@/axios'

export const useAnalyticsStore = defineStore('analytics', {
  state: () => ({
    loading: false,
    initialized: false,
    analyticsData: {
      table_data: [],
      chart_data: [],
      summary: {
        total: 0,
        growth: '0%',
      },
    },
    filters: {
      contentType: 'posts',
      period: 'last30',
      metric: 'purchases',
    },
  }),
  getters: {
    tableData: (state) => state.analyticsData.table_data,
    chartData: (state) => state.analyticsData.chart_data,
    summary: (state) => state.analyticsData.summary,
  },
  actions: {
    async initialize() {
      if (!this.initialized) {
        await this.fetchAnalyticsData()
        this.initialized = true
      }
    },
    async fetchAnalyticsData() {
      this.loading = true
      try {
        const params = {
          content_type: this.filters.contentType,
          period: this.filters.period,
          metric: this.filters.metric,
        }
        
        console.log('Fetching analytics data with params:', params)
        
        const response = await axiosInstance.get('/statistics/analytics', { params })
        
        console.log('Analytics response:', response.data)
        console.log('Response status:', response.status)
        console.log('Response headers:', response.headers)
        
        this.analyticsData = response.data
      } catch (error) {
        console.error('Error fetching analytics data:', error)
        console.error('Error response:', error.response?.data)
        console.error('Error status:', error.response?.status)
        // Reset to default structure on error
        this.analyticsData = {
          table_data: [],
          chart_data: [],
          summary: { total: 0, growth: '0%' },
        }
      } finally {
        this.loading = false
      }
    },
    async setFilter(filter, value) {
      if (this.filters[filter] !== undefined) {
        this.filters[filter] = value
        // Automatically fetch new data when filter changes
        await this.fetchAnalyticsData()
      }
    },
    async setFilters(filters) {
      this.filters = { ...this.filters, ...filters }
      // Automatically fetch new data when filters change
      await this.fetchAnalyticsData()
    },
  },
}) 