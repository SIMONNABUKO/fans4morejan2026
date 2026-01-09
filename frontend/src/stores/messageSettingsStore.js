// stores/messageSettingsStore.js

import { defineStore } from 'pinia'
import axiosInstance from '@/axios'

export const useMessageSettingsStore = defineStore('messageSettings', {
  state: () => ({
    permissionSets: [], 
    automatedMessages: [],
    messagingSettings: {
      show_read_receipts: false,
      require_tip_for_messages: false,
      accept_messages_from_followed: true
    },
    loading: false,
    saving: false,
    error: null
  }),

  getters: {
    getMessagingSettings: (state) => state.messagingSettings
  },

  actions: {
    // DM Permissions actions
    async fetchDMPermissions() {
      try {
        this.loading = true
        const response = await axiosInstance.get('/dm-permissions')
        this.permissionSets = response.data.permissionSets
        return response.data.permissionSets
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch DM permissions'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateDMPermissions(permissions) {
      try {
        this.loading = true
        const response = await axiosInstance.post('/dm-permissions', { permissions })
        await this.fetchDMPermissions() // Refresh the permissions after update
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update DM permissions'
        throw error
      } finally {
        this.loading = false
      }
    },

    // New messaging settings actions
    async fetchMessagingSettings() {
      try {
        this.loading = true
        const response = await axiosInstance.get('/user/settings/messaging')
        this.messagingSettings = response.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch messaging settings'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateMessagingSettings(settings) {
      try {
        this.saving = true
        const response = await axiosInstance.put('/user/settings/messaging', settings)
        // Update the store state with the response data
        this.messagingSettings = { ...response.data }
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update messaging settings'
        throw error
      } finally {
        this.saving = false
      }
    },

    // Helper methods for formatting permission data
    formatPermissionName(type) {
      switch (type) {
        case 'price_per_message':
          return 'Price Per Message'
        case 'following':
          return 'Must Be Following'
        case 'subscriber':
          return 'Must Be Subscriber'
        case 'tipped':
          return 'Must Have Tipped'
        case 'followed_by_me':
          return 'Must Be Followed By Me'
        case 'media_purchases':
          return 'Must Have Media Purchases'
        default:
          return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
      }
    },

    getPermissionDescription(type) {
      switch (type) {
        case 'price_per_message':
          return 'Set the price users must pay to send you a direct message'
        case 'following':
          return 'Users must be following you to send direct messages'
        case 'subscriber':
          return 'Users must be subscribed to you to send direct messages'
        case 'tipped':
          return 'Users must have tipped you to send direct messages'
        case 'followed_by_me':
          return 'You must be following the user to receive direct messages'
        case 'media_purchases':
          return 'Users must have purchased media from you to send direct messages'
        default:
          return 'Configure who can send you direct messages'
      }
    },

    // Automated Messages actions
    async fetchAutomatedMessages() {
      try {
        this.loading = true
        const response = await axiosInstance.get('/automated-messages')
        this.automatedMessages = response.data.data
        return response.data.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch automated messages'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createAutomatedMessage(messageData, mediaFiles = []) {
      try {
        this.saving = true
        this.error = null
        const formData = new FormData()

        // Append text data
        formData.append('trigger', messageData.trigger)
        formData.append('content', messageData.content)
        formData.append('sent_delay', messageData.sent_delay || 0)
        formData.append('cooldown', messageData.cooldown || 0)

        // Append permissions if they exist
        if (messageData.permissions) {
          formData.append('permissions', JSON.stringify(messageData.permissions))
        }

        // Append media files
        if (mediaFiles && mediaFiles.length > 0) {
          mediaFiles.forEach((media, index) => {
            if (media.file instanceof File) {
              formData.append(`media[${index}][file]`, media.file)
              formData.append(`media[${index}][type]`, media.type)
            }

            // Add preview versions if they exist
            if (media.previewVersions && media.previewVersions.length > 0) {
              media.previewVersions.forEach((preview, previewIndex) => {
                if (preview.file instanceof File) {
                  formData.append(`media[${index}][previewVersions][${previewIndex}]`, preview.file)
                }
              })
            }
          })
        }

        const response = await axiosInstance.post('/automated-messages', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        // Add the new message to the store
        this.automatedMessages.unshift(response.data)

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create automated message'
        throw error
      } finally {
        this.saving = false
      }
    },

    async updateAutomatedMessage(messageId, messageData, mediaFiles = []) {
      try {
        this.saving = true
        this.error = null
        const formData = new FormData()

        // Append text data
        formData.append('trigger', messageData.trigger)
        formData.append('content', messageData.content)
        formData.append('sent_delay', messageData.sent_delay || 0)
        formData.append('cooldown', messageData.cooldown || 0)
        formData.append('_method', 'PUT') // For Laravel to handle PUT request

        // Append permissions if they exist
        if (messageData.permissions) {
          formData.append('permissions', JSON.stringify(messageData.permissions))
        }

        // Append media files
        if (mediaFiles && mediaFiles.length > 0) {
          mediaFiles.forEach((media, index) => {
            if (media.file instanceof File) {
              formData.append(`media[${index}][file]`, media.file)
              formData.append(`media[${index}][type]`, media.type)
            }

            // Add preview versions if they exist
            if (media.previewVersions && media.previewVersions.length > 0) {
              media.previewVersions.forEach((preview, previewIndex) => {
                if (preview.file instanceof File) {
                  formData.append(`media[${index}][previewVersions][${previewIndex}]`, preview.file)
                }
              })
            }
          })
        }

        const response = await axiosInstance.post(`/automated-messages/${messageId}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        // Update the message in the store
        const index = this.automatedMessages.findIndex(msg => msg.id === messageId)
        if (index !== -1) {
          this.automatedMessages[index] = response.data
        }

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update automated message'
        throw error
      } finally {
        this.saving = false
      }
    },

    // In messageSettingsStore.js
    async deleteAutomatedMessage(messageId) {
      try {
        this.loading = true;
        console.log(`Deleting automated message with ID: ${messageId}`);

        // Make the API call to delete the message
        await axiosInstance.delete(`/automated-messages/${messageId}`);

        // Remove the message from the store
        this.automatedMessages = this.automatedMessages.filter(msg => msg.id !== messageId);

        return true;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete automated message';
        console.error('Error deleting automated message:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async toggleAutomatedMessageStatus(messageId) {
      try {
        this.loading = true
        const response = await axiosInstance.patch(`/automated-messages/${messageId}/toggle`)

        // Update the message status in the store
        const index = this.automatedMessages.findIndex(msg => msg.id === messageId)
        if (index !== -1) {
          this.automatedMessages[index] = response.data
        }

        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to toggle message status'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})