import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useSubscribersStore = defineStore("subscribers", {
  state: () => ({
    subscribers: [],
    loading: false,
    error: null,
    pagination: {
      currentPage: 1,
      totalPages: 1,
      totalItems: 0,
      perPage: 8,
    },
    counts: {
      active: 0,
      expired: 0,
      all: 0,
    },
    activeStatus: "all", // 'active', 'expired', 'all'
    searchQuery: "",
  }),

  getters: {
    hasSubscribers: (state) => state.subscribers.length > 0,
    activeSubscribers: (state) => state.subscribers.filter((sub) => sub.renew === "On"),
    expiredSubscribers: (state) => state.subscribers.filter((sub) => sub.renew === "Off"),
    vipSubscribers: (state) => state.subscribers.filter((sub) => sub.isVip),
    mutedSubscribers: (state) => state.subscribers.filter((sub) => sub.isMuted),
  },

  actions: {
    async fetchSubscribers(page = 1, status = null, search = null) {
      this.loading = true
      this.error = null

      // Use provided values or current state
      const currentStatus = status || this.activeStatus
      const currentSearch = search !== undefined ? search : this.searchQuery

      try {
        const response = await axiosInstance.get("/creator/subscribers", {
          params: {
            page,
            per_page: this.pagination.perPage,
            status: currentStatus,
            search: currentSearch,
          },
        })

        if (response.data.success) {
          this.subscribers = response.data.data
          this.pagination = {
            currentPage: response.data.pagination.current_page,
            totalPages: response.data.pagination.total_pages,
            totalItems: response.data.pagination.total,
            perPage: response.data.pagination.per_page,
          }

          // Update state with provided values
          if (status) this.activeStatus = status
          if (search !== undefined) this.searchQuery = search

          return { success: true, data: response.data.data }
        } else {
          throw new Error(response.data.message || "Failed to fetch subscribers")
        }
      } catch (error) {
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchSubscriberCounts() {
      try {
        const response = await axiosInstance.get("/creator/subscribers/counts")

        if (response.data.success) {
          this.counts = response.data.counts
          return { success: true, counts: response.data.counts }
        } else {
          throw new Error(response.data.message || "Failed to fetch subscriber counts")
        }
      } catch (error) {
        this.handleError(error)
        return { success: false, error: this.error }
      }
    },

    async toggleVipStatus(subscriberId) {
      this.loading = true
      this.error = null

      try {
        const response = await axiosInstance.post(`/creator/subscribers/${subscriberId}/vip`)

        if (response.data.success) {
          // Update the subscriber in the store
          const index = this.subscribers.findIndex((sub) => sub.subscriberId === subscriberId)
          if (index !== -1) {
            this.subscribers[index].isVip = response.data.isVip
          }

          return {
            success: true,
            message: response.data.message,
            isVip: response.data.isVip,
          }
        } else {
          throw new Error(response.data.message || "Failed to update VIP status")
        }
      } catch (error) {
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async toggleMuteStatus(subscriberId) {
      this.loading = true
      this.error = null

      try {
        const response = await axiosInstance.post(`/creator/subscribers/${subscriberId}/mute`)

        if (response.data.success) {
          // Update the subscriber in the store
          const index = this.subscribers.findIndex((sub) => sub.subscriberId === subscriberId)
          if (index !== -1) {
            this.subscribers[index].isMuted = response.data.isMuted
          }

          return {
            success: true,
            message: response.data.message,
            isMuted: response.data.isMuted,
          }
        } else {
          throw new Error(response.data.message || "Failed to update mute status")
        }
      } catch (error) {
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async blockSubscriber(subscriberId) {
      this.loading = true
      this.error = null

      try {
        const response = await axiosInstance.post(`/creator/subscribers/${subscriberId}/block`)

        if (response.data.success) {
          // Remove the subscriber from the list
          this.subscribers = this.subscribers.filter((sub) => sub.subscriberId !== subscriberId)

          // Update counts
          if (this.counts.all > 0) this.counts.all--

          return {
            success: true,
            message: response.data.message,
          }
        } else {
          throw new Error(response.data.message || "Failed to block subscriber")
        }
      } catch (error) {
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async getSubscriberEarnings(subscriberId) {
      this.loading = true
      this.error = null

      try {
        const response = await axiosInstance.get(`/creator/subscribers/${subscriberId}/earnings`)

        if (response.data.success) {
          return {
            success: true,
            data: response.data.data,
          }
        } else {
          throw new Error(response.data.message || "Failed to fetch subscriber earnings")
        }
      } catch (error) {
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    // Helper method to handle errors
    handleError(error) {
      console.error("Subscribers store error:", error)
      if (error.response && error.response.data) {
        this.error = error.response.data.message || "An error occurred"
      } else {
        this.error = error.message || "An unexpected error occurred"
      }
    },

    // Reset search and filters
    resetFilters() {
      this.searchQuery = ""
      this.activeStatus = "all"
      this.pagination.currentPage = 1
    },
  },
})

