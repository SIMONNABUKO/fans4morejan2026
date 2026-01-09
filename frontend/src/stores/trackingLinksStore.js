import { defineStore } from "pinia"
import axiosInstance from "@/axios"
import { useToast } from 'vue-toast-notification'

export const useTrackingLinksStore = defineStore("trackingLinks", {
  state: () => ({
    trackingLinks: [],
    loading: false,
    error: null,
    currentLink: null,
    stats: null,
    statsLoading: false,
    statsError: null
  }),

  getters: {
    hasTrackingLinks: (state) => {
      console.log('Checking hasTrackingLinks:', state.trackingLinks.length > 0)
      return state.trackingLinks.length > 0
    },
    activeTrackingLinks: (state) => state.trackingLinks.filter((link) => link.is_active),
    inactiveTrackingLinks: (state) => state.trackingLinks.filter((link) => !link.is_active),
  },

  actions: {
    async fetchTrackingLinks() {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get('/tracking-links')
        this.trackingLinks = response.data.map(link => ({
          ...link,
          tips_count: link.tips_count || 0
        }))
        console.log('Fetched tracking links:', this.trackingLinks)
      } catch (error) {
        console.error('Error fetching tracking links:', error)
        this.error = error.response?.data?.message || 'Failed to fetch tracking links'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchTrackingLinkStats(id) {
      this.statsLoading = true
      this.statsError = null
      try {
        const response = await axiosInstance.get(`/tracking-links/${id}/stats`)
        this.stats = response.data
        return response.data
      } catch (error) {
        console.error('Error fetching tracking link stats:', error)
        this.statsError = error.response?.data?.message || 'Failed to fetch stats'
        throw error
      } finally {
        this.statsLoading = false
      }
    },

    async createTrackingLink(data) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post('/tracking-links', data)
        const newLink = {
          ...response.data,
          clicks_count: 0,
          subscriptions_count: 0,
          purchases_count: 0,
          signups_count: 0,
          tips_count: 0
        }
        this.trackingLinks.unshift(newLink)
        return response.data
      } catch (error) {
        console.error('Error creating tracking link:', error)
        this.error = error.response?.data?.message || 'Failed to create tracking link'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateTrackingLink(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.put(`/tracking-links/${id}`, data)
        const index = this.trackingLinks.findIndex(link => link.id === id)
        if (index !== -1) {
          this.trackingLinks[index] = { ...this.trackingLinks[index], ...response.data }
        }
        return response.data
      } catch (error) {
        console.error('Error updating tracking link:', error)
        this.error = error.response?.data?.message || 'Failed to update tracking link'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteTrackingLink(id) {
      this.loading = true
      this.error = null
      try {
        await axiosInstance.delete(`/tracking-links/${id}`)
        this.trackingLinks = this.trackingLinks.filter(link => link.id !== id)
        return true
      } catch (error) {
        console.error('Error deleting tracking link:', error)
        this.error = error.response?.data?.message || 'Failed to delete tracking link'
        throw error
      } finally {
        this.loading = false
      }
    },

    async toggleTrackingLinkStatus(id) {
      try {
        const link = this.trackingLinks.find(l => l.id === id)
        if (!link) return

        const response = await axiosInstance.put(`/tracking-links/${id}`, {
          ...link,
          is_active: !link.is_active
        })

        const index = this.trackingLinks.findIndex(l => l.id === id)
        if (index !== -1) {
          this.trackingLinks[index] = {
            ...this.trackingLinks[index],
            is_active: response.data.is_active
          }
        }
      } catch (error) {
        console.error('Error toggling tracking link status:', error)
        throw error
      }
    },

    copyTrackingLinkUrl(url) {
      navigator.clipboard.writeText(url).then(() => {
        const toast = useToast()
        toast.success('Link copied to clipboard!')
      }).catch(() => {
        const toast = useToast()
        toast.error('Failed to copy link')
      })
    },

    // Close stats modal
    closeStatsModal() {
      this.stats = null
      this.statsError = null
    },

    showLinkStats(link) {
      this.currentLink = link
    },

    hideLinkStats() {
      this.currentLink = null
    }
  }
}) 