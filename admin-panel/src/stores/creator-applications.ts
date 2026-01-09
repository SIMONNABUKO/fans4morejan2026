import { defineStore } from 'pinia'
import axiosInstance from '@/plugins/axios'

interface CreatorApplication {
  id: number
  user_id: number
  first_name: string
  last_name: string
  birthday: string
  address: string
  city: string
  country: string
  state: string
  zip_code: string
  document_type: 'driving_license' | 'state_id'
  front_id: string
  back_id: string | null
  holding_id: string
  verification_sign: string | null
  verification_video: string | null
  status: 'pending' | 'approved' | 'rejected'
  feedback?: string
  processed_at?: string
  created_at: string
  updated_at: string
  user?: {
    name: string
    email: string
    avatar: string
  }
}

interface ApplicationHistory {
  id: number
  status: string
  feedback: string
  processed_at: string
  admin: {
    name: string
    email: string
  }
}

interface ApplicationFilters {
  status: 'pending' | 'approved' | 'rejected'
  dateRange: [string, string] | null
  search: string
}

interface CreatorApplicationsState {
  applications: CreatorApplication[]
  loading: boolean
  error: string | null
  totalApplications: number
  currentPage: number
  perPage: number
  filters: ApplicationFilters
}

export const useCreatorApplicationsStore = defineStore('creator-applications', {
  state: (): CreatorApplicationsState => ({
    applications: [],
    loading: false,
    error: null,
    totalApplications: 0,
    currentPage: 1,
    perPage: 10,
    filters: {
      status: 'pending',
      dateRange: null,
      search: ''
    }
  }),

  getters: {
    pendingApplicationsCount: (state: CreatorApplicationsState): number => 
      state.applications.filter(app => app.status === 'pending').length,
  },

  actions: {
    async fetchApplications(): Promise<void> {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get<{ data: CreatorApplication[], total: number }>('/admin/creator-applications', {
          params: {
            page: this.currentPage,
            per_page: this.perPage,
            ...this.filters
          }
        })
        this.applications = response.data.data
        this.totalApplications = response.data.total
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch applications'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateApplicationStatus(applicationId: number, status: CreatorApplication['status'], notes: string = ''): Promise<void> {
      try {
        await axiosInstance.put(`/admin/creator-applications/${applicationId}/status`, {
          status,
          feedback: notes,
          processed_at: new Date().toISOString()
        })
        // Refresh the applications list
        await this.fetchApplications()
      } catch (error: any) {
        throw error.response?.data?.message || 'Failed to update application status'
      }
    },

    async getApplicationHistory(applicationId: number): Promise<ApplicationHistory[]> {
      try {
        console.log('Making API request for history:', applicationId)
        const response = await axiosInstance.get<ApplicationHistory[]>(`/admin/creator-applications/${applicationId}/history`)
        console.log('Raw API response:', response)
        
        // Check if the response has a data property
        const historyData = Array.isArray(response.data) ? response.data : []
        console.log('Processed history data:', historyData)
        
        return historyData
      } catch (error: any) {
        console.error('API Error:', error.response || error)
        throw error.response?.data?.message || 'Failed to fetch application history'
      }
    },

    setFilters(newFilters: Partial<ApplicationFilters>): void {
      this.filters = { ...this.filters, ...newFilters }
      this.currentPage = 1 // Reset to first page when filters change
    },

    setPage(page: number): void {
      this.currentPage = page
    }
  }
}) 