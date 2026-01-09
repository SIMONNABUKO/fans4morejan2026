import { defineStore } from 'pinia'
import axios from '@/plugins/axios'

export interface DashboardStats {
  totalUsers: number
  activePosts: number
  totalComments: number
  platformBalance: number
}

export interface ActivityItem {
  id: number
  type: 'user_registration' | 'subscription' | 'post_published' | 'other'
  title: string
  description: string
  timestamp: string
}

interface DashboardState {
  stats: DashboardStats | null
  recentActivity: ActivityItem[]
  isLoading: boolean
  error: string | null
}

export const useDashboardStore = defineStore('dashboard', {
  state: (): DashboardState => ({
    stats: null,
    recentActivity: [],
    isLoading: false,
    error: null
  }),

  actions: {
    async fetchDashboardStats() {
      this.isLoading = true
      this.error = null
      try {
        const response = await axios.get<DashboardStats>('/admin/dashboard/stats')
        this.stats = response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch dashboard stats'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async fetchRecentActivity() {
      this.isLoading = true
      this.error = null
      try {
        const response = await axios.get<ActivityItem[]>('/admin/dashboard/activity')
        this.recentActivity = response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch recent activity'
        throw error
      } finally {
        this.isLoading = false
      }
    }
  }
}) 