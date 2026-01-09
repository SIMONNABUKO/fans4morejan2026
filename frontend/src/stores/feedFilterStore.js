import { defineStore } from 'pinia'
import axiosInstance from '@/axios'
import { useListStore } from './listStore'

export const useFeedFilterStore = defineStore('feedFilter', {
  state: () => ({
    // All filters will come from user lists - no hardcoded filters
    availableFilters: [],
    activeFilter: 'all', // Default to 'all' which shows all posts
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
        console.log('📋 Making API call to /user/feed-filters')
        
        // Get user's list-based filter preferences from backend
        const response = await axiosInstance.get('/user/feed-filters')
        const preferences = response.data
        
        console.log('📋 Feed filter preferences received:', preferences)
        
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
        console.error('📋 Error fetching feed filter preferences:', error)
        this.error = error.response?.data?.message || 'Failed to load feed filter preferences'
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

        const response = await axiosInstance.put('/user/feed-filters', preferences)
        return response.data
      } catch (error) {
        console.error('Error updating feed filter preferences:', error)
        this.error = error.response?.data?.message || 'Failed to update feed filter preferences'
        throw error
      } finally {
        this.loading = false
      }
    },

    async addListAsFilter(listId, listName) {
      // Check if list is already added as a filter
      const existingFilter = this.availableFilters.find(filter => filter.list_id === listId)
      if (existingFilter) {
        throw new Error('List is already added as a filter')
      }

      // Get the list details to check if it's a default list
      const listStore = useListStore()
      const list = listStore.getListById(listId)
      const isDefault = list ? list.is_default : false

      // Get the highest order number
      const maxOrder = Math.max(...this.allFilters.map(f => f.order), 0)
      
      const newFilter = {
        id: `list_${listId}`,
        label: listName,
        type: 'list',
        list_id: listId,
        is_default: isDefault, // Preserve the is_default property
        enabled: true,
        order: maxOrder + 1
      }

      this.availableFilters.push(newFilter)
      await this.updateFilterPreferences()
      
      return newFilter
    },

    async removeListFilter(filterId) {
      this.availableFilters = this.availableFilters.filter(filter => filter.id !== filterId)
      
      // If the active filter was removed, switch to 'all'
      if (this.activeFilter === filterId) {
        this.activeFilter = 'all'
      }
      
      await this.updateFilterPreferences()
    },

    async toggleFilter(filterId) {
      // Handle 'all' filter specially
      if (filterId === 'all') {
        // Cannot disable the 'all' filter
        return
      }
      
      // Find the filter in available filters
      const filter = this.availableFilters.find(f => f.id === filterId)
      
      if (filter) {
        filter.enabled = !filter.enabled
        
        // If the active filter was disabled, switch to 'all'
        if (!filter.enabled && this.activeFilter === filterId) {
          this.activeFilter = 'all'
        }
        
        await this.updateFilterPreferences()
      } else {
        console.warn(`Filter with ID ${filterId} not found`)
      }
    },

    async reorderFilters(filterIds) {
      // Update order based on the new array order
      filterIds.forEach((filterId, index) => {
        if (filterId === 'all') return // Skip 'all' filter
        
        const filter = this.availableFilters.find(f => f.id === filterId)
        if (filter) {
          filter.order = index
        }
      })
      
      await this.updateFilterPreferences()
    },

    async setActiveFilter(filterId) {
      console.log('🔄 Setting active filter:', filterId)
      console.log('🔄 Current active filter:', this.activeFilter)
      
      this.activeFilter = filterId
      console.log('🔄 Active filter updated to:', this.activeFilter)
      
      await this.updateFilterPreferences()
      console.log('🔄 Filter preferences updated successfully')
    },

    toggleEditMode() {
      this.isEditing = !this.isEditing
    },

    async refreshAvailableLists() {
      const listStore = useListStore()
      await listStore.fetchLists(true) // Force refresh
      
      // Auto-create filters from user lists
      await this.syncFiltersFromLists()
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
    },

    removeDuplicateFilters() {
      // Remove duplicates based on list_id and filter.id
      const seenListIds = new Set()
      const seenFilterIds = new Set()
      
      this.availableFilters = this.availableFilters.filter(filter => {
        // Check for duplicate list_id
        if (filter.list_id && seenListIds.has(filter.list_id)) {
          return false // Remove duplicate
        }
        
        // Check for duplicate filter.id
        if (filter.id && seenFilterIds.has(filter.id)) {
          return false // Remove duplicate
        }
        
        // Add to seen sets
        if (filter.list_id) seenListIds.add(filter.list_id)
        if (filter.id) seenFilterIds.add(filter.id)
        
        return true
      })
    }
  }
}) 