import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useListStore = defineStore("list", {
  state: () => ({
    lists: [],
    listMembers: {},
    loading: false,
    error: null,
    lastFetchTime: null,
    cacheDuration: 5 * 60 * 1000, // 5 minutes in milliseconds
  }),

  getters: {
    getListByName: (state) => (name) => {
      return state.lists.find(list => list.name === name)
    },
    getDefaultList: (state) => (name) => {
      return state.lists.find(list => list.name === name && list.is_default)
    },
    getListById: (state) => (id) => {
      return state.lists.find((list) => list.id === parseInt(id))
    },
    getListMembers: (state) => (id) => {
      return state.listMembers[id] || []
    },
  },

  actions: {
    async fetchLists(force = false) {
      const token = localStorage.getItem("auth_token")
      if (!token) {
        this.lists = []
        this.lastFetchTime = null
        return []
      }

      // Check if we have cached data and it's still valid
      const now = Date.now()
      if (!force && this.lastFetchTime && (now - this.lastFetchTime < this.cacheDuration)) {
        console.log("Using cached lists data")
        return this.lists
      }

      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get("/lists")
        console.log("Fetched lists:", response.data)
        this.lists = response.data.data
        this.lastFetchTime = now
        return response.data.data
      } catch (error) {
        console.error("Error fetching lists:", error)
        this.error = error.response?.data?.message || "An error occurred while fetching lists"
        throw error
      } finally {
        this.loading = false
      }
    },

    async getListDetails(listId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get(`/lists/${listId}`)
        console.log("Fetched list details:", response.data)
        
        // Update the local state
        const listIndex = this.lists.findIndex(list => list.id === parseInt(listId))
        if (listIndex !== -1) {
          this.lists[listIndex] = { ...this.lists[listIndex], ...response.data.list }
        } else {
          this.lists.push(response.data.list)
        }
        this.listMembers[listId] = response.data.members
        
        return response.data
      } catch (error) {
        console.error("Error fetching list details:", error)
        this.error = error.response?.data?.message || "An error occurred while fetching list details"
        throw error
      } finally {
        this.loading = false
      }
    },

    async createList(listData) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post("/lists", listData)
        console.log("Created list:", response.data)
        this.lists.push(response.data.data)
        return response.data.data
      } catch (error) {
        console.error("Error creating list:", error)
        this.error = error.response?.data?.message || "An error occurred while creating the list"
        throw error
      } finally {
        this.loading = false
      }
    },

    async addMemberToList(listId, userId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post(`/lists/${listId}/members/${userId}`)
        console.log("Added member to list:", response.data)
        
        // Only refresh list details if listId is numeric (not a string name)
        if (!isNaN(listId)) {
          await this.getListDetails(listId)
        }
        
        return response.data
      } catch (error) {
        console.error("Error adding member to list:", error)
        this.error = error.response?.data?.message || "An error occurred while adding member to the list"
        throw error
      } finally {
        this.loading = false
      }
    },

    async removeMemberFromList(listId, userId) {
      this.loading = true
      this.error = null
      try {
        // Handle both string names (like 'Muted Accounts') and numeric IDs
        let listIdentifier = listId
        let list = null
        
        if (isNaN(listId)) {
          // It's a string name (like 'Muted Accounts')
          listIdentifier = listId
          list = this.lists.find(l => l.name === listId)
        } else {
          // It's a numeric ID
          list = this.getListById(listId)
          listIdentifier = list?.is_default ? list.name : list?.id
        }
        
        const response = await axiosInstance.delete(`/lists/${listIdentifier}/members/${userId}`)
        console.log("Removed member from list:", response.data)
        
        if (response.data.message === 'Member removed successfully') {
          if (this.listMembers[listId]) {
            this.listMembers[listId] = this.listMembers[listId].filter(member => member.id !== userId)
          }
          
          const listIndex = this.lists.findIndex(l => l.id === parseInt(listId) || l.name === listId)
          if (listIndex !== -1) {
            this.lists[listIndex].count -= 1
          }
        } else {
          throw new Error(response.data.message)
        }
        
        return response.data
      } catch (error) {
        console.error("Error removing member from list:", error)
        this.error = error.response?.data?.message || error.message || "An error occurred while removing member from the list"
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchListMembers(listId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get(`/lists/${listId}/members`)
        console.log("Fetched list members:", response.data)
        this.listMembers[listId] = response.data.data
        return response.data.data
      } catch (error) {
        console.error("Error fetching list members:", error)
        this.error = error.response?.data?.message || "An error occurred while fetching list members"
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteList(listId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.delete(`/lists/${listId}`)
        console.log("Deleted list:", response.data)
        this.lists = this.lists.filter(list => list.id !== parseInt(listId))
        delete this.listMembers[listId]
        return response.data
      } catch (error) {
        console.error("Error deleting list:", error)
        this.error = error.response?.data?.message || "An error occurred while deleting the list"
        throw error
      } finally {
        this.loading = false
      }
    },

    async addToVipList(userId) {
      return this.addMemberToList('VIP', userId)
    },

    async addToBlockedList(userId) {
      return this.addMemberToList('Blocked Accounts', userId)
    },

    async addToMutedList(userId) {
      return this.addMemberToList('Muted Accounts', userId)
    },

    async blockUser(userId) {
      return this.addToBlockedList(userId)
    },

    async muteUser(userId) {
      return this.addToMutedList(userId)
    }
  }
})
