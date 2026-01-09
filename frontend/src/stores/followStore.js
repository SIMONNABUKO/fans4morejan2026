import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useFollowStore = defineStore("follow", {
  state: () => ({
    followers: [],
    following: [],
    loading: false,
    error: null,
  }),

  actions: {
    async fetchFollowers(userId, page = 1) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get(`/users/${userId}/followers?page=${page}`)
        let users = Array.isArray(response.data) ? response.data : response.data.data || []
        users = users.map(user => ({ ...user, is_following: user.is_following ?? false }))
        this.followers = users
        return response.data
      } catch (error) {
        console.error("Error fetching followers:", error)
        this.error = error.response?.data?.message || "An error occurred while fetching followers"
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchFollowing(userId, page = 1) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get(`/users/${userId}/following?page=${page}`)
        let users = Array.isArray(response.data) ? response.data : response.data.data || []
        users = users.map(user => ({ ...user, is_following: user.is_following ?? true }))
        this.following = users
        return response.data
      } catch (error) {
        console.error("Error fetching following:", error)
        this.error = error.response?.data?.message || "An error occurred while fetching following"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Remove user from following list when unfollowed
    removeFromFollowing(userId) {
      this.following = this.following.filter(user => user.id !== userId)
    },

    // Update user's following status in the list
    updateFollowingStatus(userId, isFollowing) {
      const userIndex = this.following.findIndex(user => user.id === userId)
      if (userIndex !== -1) {
        this.following[userIndex].is_following = isFollowing
      }
    },
  },
})

