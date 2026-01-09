import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useSupportersStore = defineStore("supporters", {
  state: () => ({
    topSupporters: [],
    loading: false,
    loadingMore: false,
    error: null,
    currentPage: 1,
    hasMoreSupporters: false,
    perPage: 10,
    currentUserId: null
  }),

  actions: {
    setCurrentUserId(userId) {
      this.currentUserId = userId
    },
    
    async fetchTopSupporters(params = {}) {
      this.loading = true
      this.error = null
      this.currentPage = 1
      
      try {
        // Build query parameters
        const queryParams = new URLSearchParams()
        
        if (params.period) queryParams.append('period', params.period)
        
        if (params.period !== 'all') {
          if (params.start_date) queryParams.append('start_date', params.start_date)
          if (params.end_date) queryParams.append('end_date', params.end_date)
        }
        
        queryParams.append('page', this.currentPage)
        queryParams.append('per_page', this.perPage)
        
        const queryString = queryParams.toString() ? `?${queryParams.toString()}` : ''
        console.log(`Fetching top supporters with: ${queryString}`)
        
        const response = await axiosInstance.get(`/supporters/top${queryString}`)
        
        if (response.data.success) {
          // Log the raw data to see what's coming from the API
          console.log('Raw supporter data:', JSON.stringify(response.data.data.data))
          
          let supporters = response.data.data.data || []
          
          // Map each supporter with proper debugging
          supporters = supporters.map((supporter, index) => {
            console.log(`Processing supporter ${index}:`, supporter)
            
            // Check what name values we're getting
            console.log(`Supporter ${index} name values:`, {
              name: supporter.name,
              display_name: supporter.display_name,
              username: supporter.username,
              typeofName: typeof supporter.name,
              typeofDisplayName: typeof supporter.display_name
            })
            
            // Use a more explicit name resolution approach
            let displayName = supporter.name
            
            // If we have a display_name field, prioritize it
            if (supporter.display_name && typeof supporter.display_name === 'string') {
              displayName = supporter.display_name
            }
            
            // Fallback to username if name is missing or invalid
            if (!displayName || typeof displayName !== 'string') {
              displayName = supporter.username || 'Unknown User'
            }
            
            return {
              ...supporter,
              name: displayName
            }
          })
          
          if (this.currentUserId) {
            supporters = supporters.filter(supporter => supporter.id !== this.currentUserId)
          }
          
          this.topSupporters = supporters
          this.hasMoreSupporters = response.data.data.current_page < response.data.data.last_page
        } else {
          throw new Error(response.data.message || 'Failed to load top supporters')
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while fetching top supporters"
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async loadMoreSupporters(params = {}) {
      if (this.loadingMore || !this.hasMoreSupporters) return
      
      this.loadingMore = true
      this.currentPage++
      
      try {
        // Build query parameters
        const queryParams = new URLSearchParams()
        
        // Only add period parameter if it's provided
        if (params.period) queryParams.append('period', params.period)
        
        // Only add date parameters if they're provided AND period is not 'all'
        if (params.period !== 'all') {
          if (params.start_date) queryParams.append('start_date', params.start_date)
          if (params.end_date) queryParams.append('end_date', params.end_date)
        }
        
        queryParams.append('page', this.currentPage)
        queryParams.append('per_page', this.perPage)
        
        const queryString = queryParams.toString() ? `?${queryParams.toString()}` : ''
        console.log(`Loading more supporters with: ${queryString}`)
        
        const response = await axiosInstance.get(`/supporters/top${queryString}`)
        
        if (response.data.success) {
          // Filter out the current user from the supporters list
          let supporters = response.data.data.data || []
          
          // Ensure each supporter has the correct name
          supporters = supporters.map(supporter => {
            // Fix for boolean name values
            const displayName = typeof supporter.display_name === 'boolean' ? 
              (supporter.username || 'Unknown User') : 
              supporter.display_name;
              
            const name = typeof supporter.name === 'boolean' ? 
              (supporter.username || 'Unknown User') : 
              supporter.name;
              
            return {
              ...supporter,
              // Ensure name is properly set and not a boolean
              name: displayName || name || supporter.username || 'Unknown User'
            }
          })
          
          // Filter out the current user (if ID is available)
          if (this.currentUserId) {
            supporters = supporters.filter(supporter => supporter.id !== this.currentUserId)
          }
          
          this.topSupporters = [...this.topSupporters, ...supporters]
          this.hasMoreSupporters = response.data.data.current_page < response.data.data.last_page
        } else {
          throw new Error(response.data.message || 'Failed to load more supporters')
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "An error occurred while fetching more supporters"
        throw error
      } finally {
        this.loadingMore = false
      }
    },
    
    async getSupporterDetails(supporterId, params = {}) {
      try {
        // Build query parameters
        const queryParams = new URLSearchParams()
        
        // Only add date parameters if they're provided AND period is not 'all'
        if (params.period !== 'all') {
          if (params.start_date) queryParams.append('start_date', params.start_date)
          if (params.end_date) queryParams.append('end_date', params.end_date)
        }
        
        const queryString = queryParams.toString() ? `?${queryParams.toString()}` : ''
        console.log(`Getting supporter details with: ${queryString}`)
        
        const response = await axiosInstance.get(`/supporters/${supporterId}/details${queryString}`)
        
        if (response.data.success) {
          // Update the supporter in the list with detailed data
          const index = this.topSupporters.findIndex(s => s.id === supporterId)
          if (index !== -1) {
            this.topSupporters[index] = {
              ...this.topSupporters[index],
              ...response.data.data,
              hasDetails: true
            }
          }
          
          return response.data.data
        } else {
          throw new Error(response.data.message || 'Failed to load supporter details')
        }
      } catch (error) {
        console.error("Error fetching supporter details:", error)
        throw error
      }
    }
  }
})