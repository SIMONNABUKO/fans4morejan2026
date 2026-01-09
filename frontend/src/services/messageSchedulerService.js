import axiosInstance from '@/axios'

/**
 * Message Scheduler Service
 * Handles mass messaging, scheduling, and related operations
 */
class MessageSchedulerService {
  
  /**
   * Send a mass message immediately
   * @param {Object} messageData - The message data including content, recipients, media
   * @returns {Promise<Object>} - API response
   */
  async sendMassMessage(messageData) {
    try {
      const response = await axiosInstance.post('/messages/mass', {
        subject: messageData.subject,
        content: messageData.content,
        recipients: messageData.recipients,
        media: messageData.media,
        mediaPermissions: messageData.mediaPermissions,
        options: messageData.delivery?.options || {}
      })
      
      return {
        success: true,
        data: response.data,
        messageId: response.data.message_id
      }
    } catch (error) {
      console.error('Failed to send mass message:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to send message',
        details: error.response?.data
      }
    }
  }

  /**
   * Schedule a mass message for later delivery
   * @param {Object} messageData - The message data including schedule info
   * @returns {Promise<Object>} - API response
   */
  async scheduleMassMessage(messageData) {
    try {
      const scheduleData = {
        subject: messageData.subject,
        content: messageData.content,
        recipients: messageData.recipients,
        media: messageData.media,
        mediaPermissions: messageData.mediaPermissions,
        scheduled_for: messageData.delivery.scheduledFor,
        timezone: messageData.delivery.timezone,
        options: messageData.delivery.options || {}
      }

      // Handle recurring messages
      if (messageData.delivery.recurring) {
        scheduleData.recurring = {
          type: messageData.delivery.recurring.type,
          end_date: messageData.delivery.recurring.endDate
        }
      }

      const response = await axiosInstance.post('/messages/schedule', scheduleData)
      
      return {
        success: true,
        data: response.data,
        scheduledId: response.data.scheduled_id
      }
    } catch (error) {
      console.error('Failed to schedule message:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to schedule message',
        details: error.response?.data
      }
    }
  }

  /**
   * Save message as draft
   * @param {Object} draftData - Draft message data
   * @returns {Promise<Object>} - API response
   */
  async saveDraft(draftData) {
    try {
      // Transform media data to remove file objects and blob URLs
      const transformedMedia = draftData.media ? draftData.media.map(media => ({
        // Store only metadata, not file objects or blob URLs
        name: media.name || 'Unknown',
        type: media.type || 'image',
        size: media.file?.size || 0,
        mime_type: media.file?.type || 'image/jpeg',
        // Store permissions if they exist
        permissions: media.permissions || draftData.mediaPermissions || [],
        // Don't store file objects, blob URLs, or preview data
        // file: undefined,
        // preview: undefined,
        // id: undefined,
        isDraft: true // Mark as draft media
      })) : []

      const response = await axiosInstance.post('/messages/drafts', {
        subject: draftData.subject || '',
        content: draftData.content,
        recipients: draftData.recipients,
        media: transformedMedia,
        delivery_settings: draftData.deliverySettings,
        draft_name: draftData.draftName || `Draft ${new Date().toLocaleDateString()}`
      })
      
      return {
        success: true,
        data: response.data,
        draftId: response.data.draft_id
      }
    } catch (error) {
      console.error('Failed to save draft:', error)
      
      // Enhanced error logging for debugging
      if (error.response?.status === 422) {
        console.error('Validation errors:', error.response.data.errors)
      }
      
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to save draft',
        validationErrors: error.response?.data?.errors || null
      }
    }
  }

  /**
   * Get user's message drafts
   * @returns {Promise<Object>} - API response with drafts
   */
  async getDrafts() {
    try {
      const response = await axiosInstance.get('/messages/drafts')
      return {
        success: true,
        drafts: response.data.data || []
      }
    } catch (error) {
      console.error('Failed to get drafts:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to load drafts',
        drafts: []
      }
    }
  }

  /**
   * Get scheduled messages
   * @returns {Promise<Object>} - API response with scheduled messages
   */
  async getScheduledMessages() {
    try {
      const response = await axiosInstance.get('/messages/scheduled')
      return {
        success: true,
        scheduled: response.data.data || []
      }
    } catch (error) {
      console.error('Failed to get scheduled messages:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to load scheduled messages',
        scheduled: []
      }
    }
  }

  /**
   * Get mass message campaigns
   * @param {Object} params - Query params for filtering/pagination
   * @returns {Promise<Object>} - API response with campaigns
   */
  async getCampaigns(params = {}) {
    try {
      const response = await axiosInstance.get('/messages/campaigns', { params })
      const payload = response.data?.data
      const campaigns = Array.isArray(payload?.data) ? payload.data : (payload || [])

      return {
        success: true,
        campaigns,
        pagination: payload && payload.data ? payload : null
      }
    } catch (error) {
      console.error('Failed to load campaigns:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to load campaigns',
        campaigns: []
      }
    }
  }

  /**
   * Get analytics for a specific campaign
   * @param {string|number} campaignId - Campaign ID
   * @returns {Promise<Object>} - API response with analytics
   */
  async getCampaignAnalytics(campaignId) {
    try {
      const response = await axiosInstance.get(`/messages/campaigns/${campaignId}/analytics`)
      return {
        success: true,
        analytics: response.data?.data || null
      }
    } catch (error) {
      console.error('Failed to load campaign analytics:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to load analytics',
        analytics: null
      }
    }
  }

  /**
   * Cancel a scheduled message
   * @param {string|number} scheduledId - The scheduled message ID
   * @returns {Promise<Object>} - API response
   */
  async cancelScheduledMessage(scheduledId) {
    try {
      const response = await axiosInstance.delete(`/messages/scheduled/${scheduledId}`)
      return {
        success: true,
        data: response.data
      }
    } catch (error) {
      console.error('Failed to cancel scheduled message:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to cancel scheduled message'
      }
    }
  }

  /**
   * Update a scheduled message
   * @param {string|number} scheduledId - The scheduled message ID
   * @param {Object} updateData - Updated message data
   * @returns {Promise<Object>} - API response
   */
  async updateScheduledMessage(scheduledId, updateData) {
    try {
      const response = await axiosInstance.put(`/messages/scheduled/${scheduledId}`, updateData)
      return {
        success: true,
        data: response.data
      }
    } catch (error) {
      console.error('Failed to update scheduled message:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to update scheduled message'
      }
    }
  }

  /**
   * Get message templates
   * @returns {Promise<Object>} - API response with templates
   */
  async getMessageTemplates() {
    try {
      const response = await axiosInstance.get('/messages/templates')
      return {
        success: true,
        templates: response.data.data || []
      }
    } catch (error) {
      console.error('Failed to get message templates:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to load templates',
        templates: []
      }
    }
  }

  /**
   * Save message as template
   * @param {Object} templateData - Template data
   * @returns {Promise<Object>} - API response
   */
  async saveMessageTemplate(templateData) {
    try {
      const response = await axiosInstance.post('/messages/templates', {
        name: templateData.name,
        content: templateData.content,
        subject: templateData.subject
      })
      
      return {
        success: true,
        data: response.data,
        templateId: response.data.template_id
      }
    } catch (error) {
      console.error('Failed to save template:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to save template'
      }
    }
  }

  /**
   * Get message analytics/statistics
   * @param {string|number} messageId - The message ID
   * @returns {Promise<Object>} - API response with analytics
   */
  async getMessageAnalytics(messageId) {
    try {
      const response = await axiosInstance.get(`/messages/${messageId}/analytics`)
      return {
        success: true,
        analytics: response.data.data
      }
    } catch (error) {
      console.error('Failed to get message analytics:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to load analytics',
        analytics: null
      }
    }
  }

  /**
   * Upload media files for messages
   * @param {Array} files - Array of File objects
   * @returns {Promise<Object>} - API response with uploaded file URLs
   */
  async uploadMessageMedia(files) {
    try {
      const formData = new FormData()
      
      files.forEach((file, index) => {
        formData.append(`media[${index}]`, file)
      })
      
      const response = await axiosInstance.post('/messages/upload-media', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        },
        timeout: 30000 // 30 second timeout for file uploads
      })
      
      return {
        success: true,
        uploadedFiles: response.data.files || []
      }
    } catch (error) {
      console.error('Failed to upload media:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to upload media files'
      }
    }
  }

  /**
   * Get optimal send times for the user's audience
   * @returns {Promise<Object>} - API response with optimal times
   */
  async getOptimalSendTimes() {
    try {
      const response = await axiosInstance.get('/messages/optimal-send-times')
      return {
        success: true,
        optimalTimes: response.data.data || []
      }
    } catch (error) {
      console.error('Failed to get optimal send times:', error)
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to load optimal send times',
        optimalTimes: []
      }
    }
  }

  /**
   * Process and send a complete mass message
   * @param {Object} messageData - Complete message data
   * @returns {Promise<Object>} - Final result
   */
  async processAndSendMessage(messageData) {
    try {
      // Upload media files first if any
      if (messageData.media && messageData.media.length > 0) {
        const files = messageData.media.map(media => media.file).filter(Boolean)
        
        if (files.length > 0) {
          const uploadResult = await this.uploadMessageMedia(files)
          
          if (!uploadResult.success) {
            return {
              success: false,
              error: 'Failed to upload media files',
              details: uploadResult.error
            }
          }
          
          // Replace local file references with uploaded URLs
          messageData.media = uploadResult.uploadedFiles
        }
      }

      // Send immediately or schedule
      if (messageData.delivery.immediate) {
        return await this.sendMassMessage(messageData)
      } else {
        return await this.scheduleMassMessage(messageData)
      }
      
    } catch (error) {
      console.error('Failed to process message:', error)
      return {
        success: false,
        error: 'Failed to process and send message',
        details: error.message
      }
    }
  }
}

// Export a singleton instance
export default new MessageSchedulerService() 
