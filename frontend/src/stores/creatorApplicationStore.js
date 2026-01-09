import { defineStore } from 'pinia'
import axiosInstance from '@/axios'

export const useCreatorApplicationStore = defineStore('creatorApplication', {
  state: () => ({
    application: null,
    loading: false,
    error: null,
  }),

  getters: {
    applicationStatus: (state) => state.application?.status || null,
    applicationFeedback: (state) => state.application?.feedback || null,
    isRejected: (state) => state.application?.status === 'rejected',
    isPending: (state) => state.application?.status === 'pending',
    isApproved: (state) => state.application?.status === 'approved',
  },

  actions: {
    async submitApplication(formData) {
      this.loading = true
      this.error = null
      try {
        console.log('Submitting application with FormData')
        
        const response = await axiosInstance.post('/creator-applications', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
        
        console.log('Application submission response:', response.data)
        this.application = response.data
        return { 
          success: true, 
          message: this.isRejected 
            ? 'Application updated. Please review the feedback and make necessary changes.'
            : 'Application submitted successfully'
        }
      } catch (error) {
        console.error('Application submission error:', error.response?.data || error)
        
        this.error = error.response?.data?.message || 'An error occurred while submitting the application'
        return { 
          success: false, 
          message: this.error,
          errors: error.response?.data?.errors || null
        }
      } finally {
        this.loading = false
      }
    },

    async getApplicationByUserId() {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get('/creator-applications/user')
        console.log('Fetched application:', response.data)
        this.application = response.data
        return this.application
      } catch (error) {
        if (error.response && error.response.status === 404) {
          // No application found, which is not an error
          this.application = null
          return null
        }
        console.error('Error fetching application:', error.response?.data || error)
        this.error = error.response?.data?.message || 'An error occurred while fetching the application'
        throw error
      } finally {
        this.loading = false
      }
    },
  },
})