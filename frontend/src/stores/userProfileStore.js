import { defineStore } from 'pinia'
import axiosInstance from '@/axios'
import { useAuthStore } from '@/stores/authStore'

export const useUserProfileStore = defineStore('userProfile', {
  state: () => ({
    loading: false,
    error: null
  }),

  actions: {
    async updateUserProfile(profileData) {
      this.loading = true
      this.error = null
      const authStore = useAuthStore()
      try {
        const formData = new FormData()
        
        // Generate unique request ID for tracking
        const requestId = `profile-update-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
        
        // Log the incoming profile data
        console.log('UserProfileStore: Incoming profile data with request ID:', requestId, profileData)
        
        Object.keys(profileData).forEach(key => {
          const value = profileData[key]
          
          if (key === 'avatar' || key === 'cover_photo') {
            if (value instanceof File) {
              formData.append(key, value)
              console.log(`Added file ${key}:`, value.name, 'Size:', value.size)
            }
          } else if (typeof value === 'boolean') {
            formData.append(key, value.toString())
            console.log(`Added boolean ${key}:`, value.toString())
          } else if (value !== null && value !== undefined) {
            // Include empty strings and other values
            formData.append(key, value)
            console.log(`Added field ${key}:`, value)
          }
        })

        // Log the form data being sent
        console.log('FormData contents before sending to API:')
        for (let pair of formData.entries()) {
          console.log(pair[0] + ': ' + pair[1])
        }

        const response = await axiosInstance.post('/user/profile', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'X-Request-ID': requestId
          },
          timeout: 60000 // 60 second timeout
        })

        console.log('UserProfileStore: Profile update successful with request ID:', requestId, response.data)
        authStore.user = response.data.data
        return authStore.user
      } catch (error) {
        console.error('UserProfileStore: Error updating user profile:', error)
        if (error.response?.data?.errors) {
          console.error('UserProfileStore: Validation errors:', error.response.data.errors)
        }
        
        // Handle specific error cases
        let errorMessage = 'An error occurred while updating the user profile'
        if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
          errorMessage = 'Request timeout - the update is taking longer than expected'
        } else if (error.response?.status === 429) {
          errorMessage = error.response.data?.message || 'Too many requests - please wait and try again'
        } else if (error.response?.data?.message) {
          errorMessage = error.response.data.message
        }
        
        this.error = errorMessage
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateAvatar(file) {
      this.loading = true
      this.error = null
      const authStore = useAuthStore()
      try {
        const formData = new FormData()
        formData.append('avatar', file)

        const response = await axiosInstance.post('/user/profile', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        console.log('Updated user avatar:', response.data)
        authStore.user = response.data.data
        return authStore.user
      } catch (error) {
        console.error('Error updating user avatar:', error)
        this.error = error.response?.data?.message || 'An error occurred while updating the user avatar'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateCoverPhoto(file) {
      this.loading = true
      this.error = null
      const authStore = useAuthStore()
      try {
        const formData = new FormData()
        formData.append('cover_photo', file)

        const response = await axiosInstance.post('/user/profile', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        console.log('Updated user cover photo:', response.data)
        authStore.user = response.data.data
        return authStore.user
      } catch (error) {
        console.error('Error updating user cover photo:', error)
        this.error = error.response?.data?.message || 'An error occurred while updating the user cover photo'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})