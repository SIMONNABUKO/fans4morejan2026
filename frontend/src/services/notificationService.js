// src/services/notificationService.js
import { ref, reactive } from 'vue'
import axiosInstance from '../axios'
import { useWebSocketService } from './websocketService'

class NotificationService {
  constructor() {
    this.notifications = reactive([])
    this.unreadCount = ref(0)
    this.loading = ref(false)
    this.error = ref(null)
    this.latestNotification = ref(null)
    this.showToast = ref(false)
    this.toastTimeout = null
    this.websocketService = useWebSocketService() // Initialize websocket service here
    this.initialized = false
  }

  initialize() {
    if (this.initialized) {
      console.log("⚠️ Notification service already initialized")
      return
    }

    console.log("🔔 Initializing notification service")
    this.initialized = true

    // Initialize WebSocket service if not already connected
    if (!this.websocketService.isConnected()) {
      this.websocketService.initialize()
    }

    // Listen for WebSocket connection events
    this.websocketService.on('connected', () => {
      console.log("🔔 WebSocket connected, setting up notification listeners")
      this.setupNotificationListeners()
      this.fetchNotifications()
    })

    // If already connected, set up listeners immediately
    if (this.websocketService.isConnected()) {
      console.log("🔔 WebSocket already connected, setting up notification listeners")
      this.setupNotificationListeners()
      this.fetchNotifications()
    }
  }

  setupNotificationListeners() {
    // Get the user ID
    const userId = this.getUserId()
    if (!userId) {
      console.warn("⚠️ Cannot set up notification listeners: User ID not available")
      return
    }

    // Use the correct channel name format from your channel.php
    // Based on your channel.php, it should be 'private-user.{id}'
    const userChannel = `private-user.${userId}`
    console.log(`🔔 Setting up notification listener for channel: ${userChannel}`)

    // Subscribe to the user's private channel
    this.websocketService.subscribeToChannel(userChannel)

    // Listen for notification events
    this.websocketService.on('notification', (notification) => {
      console.log("🔔 Received new notification:", notification)
      this.handleNewNotification(notification)
    })

    // Set up Echo to listen for Laravel's notification broadcasting
    if (this.websocketService.echo) {
      // For private channels, use the 'private' method
      this.websocketService.echo.private(userChannel)
        .listen('.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', (notification) => {
          console.log("🔔 Received Laravel notification:", notification)
          this.handleNewNotification(notification)
        });
    }

    // Also listen for notifications on the public posts channel
    this.websocketService.subscribeToChannel('posts');
    
    // Log all channels we're subscribed to
    if (this.websocketService.echo && this.websocketService.echo.connector && 
        this.websocketService.echo.connector.pusher && 
        this.websocketService.echo.connector.pusher.channels) {
      const channels = this.websocketService.echo.connector.pusher.channels.channels;
      console.log("📡 Subscribed channels:", Object.keys(channels));
    }
  }

  handleNewNotification(notification) {
    // Add the new notification to the list
    this.notifications.unshift(notification)
    
    // Increment unread count
    this.unreadCount.value++
    
    // Set the latest notification for the toast
    this.latestNotification.value = notification
    
    // Show the toast notification
    this.showToast.value = true
    
    // Auto-dismiss the toast after 5 seconds
    if (this.toastTimeout) {
      clearTimeout(this.toastTimeout)
    }
    
    this.toastTimeout = setTimeout(() => {
      this.dismissToast()
    }, 5000)
  }

  dismissToast() {
    this.showToast.value = false
    if (this.toastTimeout) {
      clearTimeout(this.toastTimeout)
      this.toastTimeout = null
    }
  }

  async fetchNotifications() {
    try {
      this.loading.value = true
      this.error.value = null
      
      console.log("🔔 Fetching notifications from API")
      const response = await axiosInstance.get('/notifications')
      
      // Replace the notifications array with the fetched data
      this.notifications.length = 0
      response.data.forEach(notification => {
        this.notifications.push(notification)
      })
      
      console.log(`🔔 Fetched ${this.notifications.length} notifications`)
      
      // Fetch unread count
      await this.fetchUnreadCount()
    } catch (error) {
      console.error("❌ Error fetching notifications:", error)
      this.error.value = "Failed to load notifications"
    } finally {
      this.loading.value = false
    }
  }

  async fetchUnreadCount() {
    try {
      console.log("🔔 Fetching unread notification count")
      const response = await axiosInstance.get('/notifications/unread-count')
      this.unreadCount.value = response.data.count
      console.log(`🔔 Unread notification count: ${this.unreadCount.value}`)
      return this.unreadCount.value
    } catch (error) {
      console.error("❌ Error fetching unread count:", error)
      return 0
    }
  }

  async markAsRead(notificationId) {
    try {
      console.log(`🔔 Marking notification ${notificationId} as read`)
      await axiosInstance.post(`/notifications/${notificationId}/read`)
      
      // Update the local notification to mark it as read
      const index = this.notifications.findIndex(n => n.id === notificationId)
      if (index !== -1) {
        this.notifications[index].read = true
      }
      
      // Refresh unread count
      await this.fetchUnreadCount()
    } catch (error) {
      console.error(`❌ Error marking notification ${notificationId} as read:`, error)
    }
  }

  async markAllAsRead() {
    try {
      console.log("🔔 Marking all notifications as read")
      await axiosInstance.post('/notifications/read-all')
      
      // Update all local notifications to mark them as read
      this.notifications.forEach(notification => {
        notification.read = true
      })
      
      // Reset unread count
      this.unreadCount.value = 0
    } catch (error) {
      console.error("❌ Error marking all notifications as read:", error)
    }
  }

  getUserId() {
    // Get the user ID from your auth store or localStorage
    // This is a placeholder - replace with your actual implementation
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    return user.id
  }

  cleanup() {
    console.log("🔔 Cleaning up notification service")
    
    // Clear any pending timeouts
    if (this.toastTimeout) {
      clearTimeout(this.toastTimeout)
      this.toastTimeout = null
    }
    
    // Remove WebSocket listeners
    if (this.websocketService) {
      this.websocketService.off('notification')
      
      // Unsubscribe from the user's channel
      const userId = this.getUserId()
      if (userId) {
        this.websocketService.unsubscribeFromChannel(`private-user.${userId}`)
      }
      
      // Unsubscribe from the posts channel
      this.websocketService.unsubscribeFromChannel('posts')
    }
    
    this.initialized = false
  }
}

// Create a singleton instance
const notificationService = new NotificationService()

// Create a hook-like function to use the service
export function useNotificationService() {
  return notificationService
}

// Also export the singleton for backward compatibility
export default notificationService