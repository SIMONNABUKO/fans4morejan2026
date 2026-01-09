import { defineStore } from 'pinia'
import axiosInstance from '@/axios'
import { useListStore } from './listStore'

export const useMessagesFilterStore = defineStore('messagesFilter', {
  state: () => ({
    // All filters will come from user lists - no hardcoded filters
    availableFilters: [],
    activeFilter: 'all', // Default to 'all' which shows all conversations
    loading: false,
    error: null,
    isEditing: false
  }),

  getters: {
    allFilters: (state) => {
      // Always include 'all' filter first, then user's enabled list filters
      const allFilter = { id: 'all', label: 'all', type: 'system', enabled: true, order: 0, required: true }
      const enabledListFilters = state.availableFilters.filter(filter => filter.enabled)
      return [allFilter, ...enabledListFilters].sort((a, b) => a.order - b.order)
    },

    enabledFilters: (state) => {
      return state.allFilters.filter(filter => filter.enabled)
    },

    hasCustomFilters: (state) => {
      return state.availableFilters.length > 0
    }
  },

  actions: {
    async fetchUserFilterPreferences() {
      console.log('📋 fetchUserFilterPreferences called')
      
      this.loading = true
      this.error = null
      
      try {
        console.log('📋 Making API call to /user/message-filters')
        
        // Get user's list-based filter preferences from backend
        const response = await axiosInstance.get('/user/message-filters')
        const preferences = response.data
        
        console.log('📋 Message filter preferences received:', preferences)
        
        // Set list-based filters from backend
        this.availableFilters = preferences.list_filters || []
        console.log('📋 Available filters set:', this.availableFilters)
        
        // Set active filter
        this.activeFilter = preferences.active_filter || 'all'
        console.log('📋 Active filter set to:', this.activeFilter)
        
        // Sync with current user lists to ensure all lists are available as filters
        console.log('📋 Syncing filters from lists...')
        await this.syncFiltersFromLists()
        
        console.log('📋 Final available filters:', this.availableFilters)
        console.log('📋 Final enabled filters:', this.enabledFilters)
        
        return preferences
      } catch (error) {
        console.error('📋 Error fetching message filter preferences:', error)
        this.error = error.response?.data?.message || 'Failed to load message filter preferences'
        throw error
      } finally {
        this.loading = false
        console.log('📋 fetchUserFilterPreferences completed')
      }
    },

    async updateFilterPreferences() {
      this.loading = true
      this.error = null
      
      try {
        // Remove duplicates before sending
        this.removeDuplicateFilters()
        
        // Ensure list filters have proper boolean and numeric values
        const cleanedListFilters = this.availableFilters.map(filter => ({
          ...filter,
          enabled: Boolean(filter.enabled),
          order: Number(filter.order)
        }))

        const preferences = {
          list_filters: cleanedListFilters,
          active_filter: this.activeFilter
        }

        const response = await axiosInstance.put('/user/message-filters', preferences)
        return response.data
      } catch (error) {
        console.error('Error updating message filter preferences:', error)
        this.error = error.response?.data?.message || 'Failed to update message filter preferences'
        throw error
      } finally {
        this.loading = false
      }
    },

    async setActiveFilter(filterId) {
      console.log('🎯 Setting active messages filter:', filterId)
      this.activeFilter = filterId
      await this.updateFilterPreferences()
    },

    async toggleFilter(filterId) {
      console.log('🎯 Messages toggleFilter called with:', filterId)
      
      // Handle 'all' filter specially
      if (filterId === 'all') {
        console.log('🎯 Cannot disable the "all" filter')
        return
      }
      
      // Find the filter in available filters
      const filter = this.availableFilters.find(f => f.id === filterId)
      console.log('🎯 Found filter:', filter)
      
      if (filter) {
        console.log('🎯 Filter before toggle - enabled:', filter.enabled)
        filter.enabled = !filter.enabled
        console.log('🎯 Filter after toggle - enabled:', filter.enabled)
        
        // If the active filter was disabled, switch to 'all'
        if (!filter.enabled && this.activeFilter === filterId) {
          console.log('🎯 Active filter was disabled, switching to "all"')
          this.activeFilter = 'all'
        }
        
        await this.updateFilterPreferences()
        console.log('🎯 Toggle completed successfully')
      } else {
        console.warn(`🎯 Messages filter with ID ${filterId} not found`)
        console.log('🎯 Available filter IDs:', this.availableFilters.map(f => f.id))
      }
    },

    clearError() {
      this.error = null
    },

    startEditing() {
      this.isEditing = true
    },

    stopEditing() {
      this.isEditing = false
    },

    removeDuplicateFilters() {
      const uniqueFilters = []
      const seenIds = new Set()
      const seenListIds = new Set()

      for (const filter of this.availableFilters) {
        // Check both filter ID and list_id for duplicates
        if (!seenIds.has(filter.id) && !seenListIds.has(filter.list_id)) {
          uniqueFilters.push(filter)
          seenIds.add(filter.id)
          if (filter.list_id) {
            seenListIds.add(filter.list_id)
          }
        }
      }

      this.availableFilters = uniqueFilters
    },

    async syncFiltersFromLists() {
      const listStore = useListStore()
      
      // Get all user lists (both default and custom)
      await listStore.fetchLists()
      const userLists = listStore.lists
      
      // Remove any duplicate filters first
      this.removeDuplicateFilters()
      
      // Create filters from lists that don't already exist as filters
      const existingListIds = this.availableFilters.map(filter => filter.list_id)
      const existingFilterIds = this.availableFilters.map(filter => filter.id)
      
      userLists.forEach(list => {
        const filterId = `list_${list.id}`
        
        // Check if this list is already added as a filter (by list_id or filter_id)
        if (!existingListIds.includes(list.id) && !existingFilterIds.includes(filterId)) {
          const maxOrder = Math.max(...this.allFilters.map(f => f.order), 0)
          
          const newFilter = {
            id: filterId,
            label: list.name,
            type: 'list',
            list_id: list.id,
            is_default: list.is_default || false, // Preserve the is_default property
            enabled: false, // Start disabled, user can enable if they want
            order: maxOrder + 1
          }
          
          this.availableFilters.push(newFilter)
        }
      })
      
      // Update preferences if any new filters were added
      if (userLists.length > 0) {
        await this.updateFilterPreferences()
      }
    }
  }
}) 