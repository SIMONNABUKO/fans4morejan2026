import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    error: null,
    loading: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.user,
  },
  actions: {
    async register(userData) {
      this.loading = true
      this.error = null
      try {
        // Get tracking ID from localStorage
        const trackingId = localStorage.getItem('tracking_link_id')
        console.log('Registration with tracking ID:', trackingId)

        // Add tracking ID to the request if it exists
        const requestData = {
          ...userData,
          tracking_link_id: trackingId
        }

        const response = await axiosInstance.post(`/register`, requestData)
        console.log("Registered user:", response.data)

        // Check if the response contains user and token directly
        if (response.data.user && response.data.token) {
          this.user = response.data.user
          this.setToken(response.data.token)
          return { success: true, user: this.user, message: "Registration successful" }
        } else {
          throw new Error("Invalid response format")
        }
      } catch (error) {
        if (error.response && error.response.data) {
          this.error = error.response.data.message || "Registration failed"
          return { success: false, errors: error.response.data.errors || { general: [this.error] } }
        } else {
          this.error = "An unexpected error occurred. Please try again."
          return { success: false, errors: { general: [this.error] } }
        }
      } finally {
        this.loading = false
      }
    },
    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post("/login", credentials)
        this.user = response.data.user
        this.setToken(response.data.token)
        return { success: true }
      } catch (error) {
        if (error.response && error.response.data) {
          this.error = error.response.data.message
          return { success: false, error: this.error }
        } else {
          this.error = "An unexpected error occurred. Please try again."
          return { success: false, error: this.error }
        }
      } finally {
        this.loading = false
      }
    },
    async requestPasswordReset(email) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post("/password/forgot", { email })
        return { success: true, message: response.data?.message }
      } catch (error) {
        if (error.response && error.response.data) {
          this.error = error.response.data.message
          return { success: false, error: this.error }
        }
        this.error = "An unexpected error occurred. Please try again."
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },
    async resetPassword(payload) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post("/password/reset", payload)
        return { success: true, message: response.data?.message }
      } catch (error) {
        if (error.response && error.response.data) {
          this.error = error.response.data.message
          return { success: false, error: this.error, errors: error.response.data.errors }
        }
        this.error = "An unexpected error occurred. Please try again."
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },
    logout() {
      this.user = null
      this.removeToken()
    },
    setToken(token) {
      localStorage.setItem("auth_token", token)
    },
    removeToken() {
      localStorage.removeItem("auth_token")
    },
    async fetchCurrentUser() {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get("/me")
        console.log("Current user data:", response.data)
        this.user = response.data
        return { success: true }
      } catch (error) {
        if (error.response && error.response.data) {
          this.error = error.response.data.message
          return { success: false, error: this.error }
        } else {
          this.error = "Failed to fetch user data. Please try again."
          return { success: false, error: this.error }
        }
      } finally {
        this.loading = false
      }
    },
    async checkAuth() {
      console.log('🔐 checkAuth called')
      const token = localStorage.getItem("auth_token")
      console.log('🔐 Token found in localStorage:', !!token)
      
      if (token) {
        console.log('🔐 Attempting to validate token by fetching current user')
        // Validate the token by fetching the current user
        try {
          const result = await this.fetchCurrentUser()
          console.log('🔐 fetchCurrentUser result:', result)
          return result
        } catch (error) {
          console.error('🔐 Error in checkAuth:', error)
          console.error('🔐 Error details:', error.response?.data)
          // Don't clear user if token exists - just return failure
          return { success: false, error: "Token validation failed" }
        }
      } else {
        console.log('🔐 No auth token found')
        this.user = null
        return { success: false, error: "No auth token found" }
      }
    },
  },
})
