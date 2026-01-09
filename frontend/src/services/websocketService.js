// src/services/websocketService.js
import Echo from "laravel-echo"
import Pusher from "pusher-js"

class WebSocketService {
  constructor() {
    this.echo = null
    this.connected = false
    this.connecting = false
    this.listeners = {}
    this.channels = {}
    this.eventCallbacks = {} // Store callbacks for specific events
  }

  initialize() {
    const wsHost = import.meta.env.VITE_WS_HOST
    const wsKey = import.meta.env.VITE_WS_KEY
    if (!wsHost || !wsKey) {
      console.warn("⚠️ WebSocket config missing (VITE_WS_HOST/VITE_WS_KEY). Skipping initialization.")
      return
    }

    // Check if already connected or connecting
    if (this.echo && this.connecting) {
      console.log("⚠️ WebSocket connection already in progress, skipping initialization")
      return
    }
    
    if (this.echo && this.connected) {
      console.log("⚠️ WebSocket already connected, skipping initialization")
      return
    }

    console.log("🔌 Initializing WebSocket connection...")
    this.connecting = true

    // Set up Pusher globally
    window.Pusher = Pusher

    // Create Echo instance without authentication
    window.Echo = new Echo({
      broadcaster: "pusher",
      key: wsKey,
      cluster: import.meta.env.VITE_WS_CLUSTER || "mt1",
      wsHost,
      wsPort: Number(import.meta.env.VITE_WS_PORT || 6001),
      wssPort: Number(import.meta.env.VITE_WSS_PORT || 443),
      forceTLS: (import.meta.env.VITE_WS_FORCE_TLS || "false") === "true",
      disableStats: true,
      enabledTransports: ["ws", "wss"],
    })

    // Store Echo instance
    this.echo = window.Echo

    console.log("📡 WebSocket config:", {
      key: wsKey,
      cluster: import.meta.env.VITE_WS_CLUSTER || "mt1",
      wsHost,
      wsPort: Number(import.meta.env.VITE_WS_PORT || 6001),
      wssPort: Number(import.meta.env.VITE_WSS_PORT || 443),
      forceTLS: (import.meta.env.VITE_WS_FORCE_TLS || "false") === "true",
    })

    // Add connection status logging
    this.echo.connector.pusher.connection.bind("connecting", () => {
      console.log("🔄 WebSocket connecting...")
      this.connected = false
      this.triggerEvent('connecting', null)
    })

    this.echo.connector.pusher.connection.bind("connected", () => {
      console.log("✅ WebSocket connected successfully!")
      console.log("📡 Socket ID:", this.echo.socketId())
      this.connected = true
      this.connecting = false

      // Log the channels we're subscribed to
      const channels = this.echo.connector.pusher.channels.channels
      console.log("📡 Subscribed channels:", Object.keys(channels))
      
      // Trigger connected event for any listeners
      this.triggerEvent('connected', { socketId: this.echo.socketId() })
      
      // Subscribe to the posts channel by default
      this.subscribeToChannel('posts')
    })

    this.echo.connector.pusher.connection.bind("error", (error) => {
      console.error("❌ WebSocket connection error:", error)
      this.connected = false
      this.connecting = false
      
      // Trigger error event for any listeners
      this.triggerEvent('error', error)
    })

    this.echo.connector.pusher.connection.bind("disconnected", () => {
      console.log("🔌 WebSocket disconnected")
      this.connected = false
      this.connecting = false
      
      // Trigger disconnected event for any listeners
      this.triggerEvent('disconnected', null)
      
      // Try to reconnect after a delay
      setTimeout(() => {
        if (!this.connected && !this.connecting) {
          console.log("🔄 Attempting to reconnect WebSocket...")
          this.echo.connector.pusher.connect()
        }
      }, 5000)
    })

    this.echo.connector.pusher.connection.bind("state_change", (states) => {
      console.log(`📡 WebSocket state changed from ${states.previous} to ${states.current}`)
      
      // Trigger state_change event for any listeners
      this.triggerEvent('state_change', states)
    })

    // Listen for subscription errors
    this.echo.connector.pusher.bind("subscription_error", (status) => {
      console.error("❌ Channel subscription error:", status)
      
      // Trigger subscription_error event for any listeners
      this.triggerEvent('subscription_error', status)
    })
  }

  subscribeToChannel(channelName) {
    if (!this.echo) {
      console.warn(`⚠️ Cannot subscribe to channel ${channelName}: Echo not initialized`)
      return null
    }

    // Check if already subscribed
    if (this.channels[channelName]) {
      console.log(`📡 Already subscribed to channel: ${channelName}`)
      return this.channels[channelName]
    }

    console.log(`👂 Subscribing to channel: ${channelName}`)
    
    try {
      const channel = this.echo.channel(channelName)
      
      if (!channel) {
        console.error(`❌ Failed to subscribe to channel: ${channelName}`)
        return null
      }

      // Log when the subscription succeeds
      channel.subscribed(() => {
        console.log(`✅ Successfully subscribed to channel: ${channelName}`)
        
        // If this is the posts channel, set up the event listeners
        if (channelName === 'posts') {
          this.setupPostsChannelListeners(channel)
        }
      })

      // Store the channel reference
      this.channels[channelName] = channel
      
      return channel
    } catch (error) {
      console.error(`❌ Error subscribing to channel ${channelName}:`, error)
      return null
    }
  }
  
  // Updated method to set up listeners for the posts channel
  setupPostsChannelListeners(channel) {
    console.log("🔄 Setting up listeners for posts channel")
    
    // Listen for post.created event
    channel.listen('.post.created', (data) => {
      console.log('📩 Received post.created event:', data)
      
      // Check if data is already in notification format
      if (data.post && data.post.type === 'new_post_available') {
        console.log('🔄 Data already in notification format, using directly')
        this.triggerEvent('PostCreated', data.post)
        return
      }
      
      // If not in notification format, extract post data and transform it
      const post = data.post
      
      if (!post) {
        console.error('❌ Invalid post data received:', data)
        return
      }
      
      // Create notification data format
      const notificationData = {
        type: 'new_post_available',
        post_id: post.id,
        user_id: post.user_id,
        username: post.user ? post.user.username : 'Unknown',
        avatar: post.user ? post.user.avatar : '/default-avatar.png',
        timestamp: Math.floor(Date.now() / 1000),
        has_media: post.media && post.media.length > 0
      }
      
      console.log('🔄 Transforming to notification format:', notificationData)
      
      // Trigger our internal PostCreated event with the formatted data
      this.triggerEvent('PostCreated', notificationData)
    })
    
    // Listen for post.updated event
    channel.listen('.post.updated', (data) => {
      console.log('📩 Received post.updated event:', data)
      
      // Check if data is already in notification format
      if (data.post && data.post.type === 'post_updated') {
        console.log('🔄 Data already in notification format, using directly')
        this.triggerEvent('PostUpdated', data.post)
        return
      }
      
      // If not in notification format, extract post data and transform it
      const post = data.post
      
      if (!post) {
        console.error('❌ Invalid post data received:', data)
        return
      }
      
      // Create notification data format
      const notificationData = {
        type: 'post_updated',
        post_id: post.id,
        user_id: post.user_id,
        username: post.user ? post.user.username : 'Unknown',
        avatar: post.user ? post.user.avatar : '/default-avatar.png',
        timestamp: Math.floor(Date.now() / 1000)
      }
      
      console.log('🔄 Transforming to notification format:', notificationData)
      
      // Trigger our internal PostUpdated event with the formatted data
      this.triggerEvent('PostUpdated', notificationData)
    })
    
    // Listen for post.deleted event
    channel.listen('.post.deleted', (data) => {
      console.log('📩 Received post.deleted event:', data)
      
      // Check if we have a post_id directly or need to extract it
      const postId = data.post_id || (data.post ? data.post.id : null)
      
      if (!postId) {
        console.error('❌ Invalid post deletion data received:', data)
        return
      }
      
      // Trigger our internal PostDeleted event with the post ID
      this.triggerEvent('PostDeleted', postId)
    })
  }

  listenToEvent(channelName, eventName, callback) {
    if (!this.echo) {
      console.warn(`⚠️ Cannot listen to event ${eventName}: Echo not initialized`)
      return
    }

    let channel = this.channels[channelName]
    
    if (!channel) {
      channel = this.subscribeToChannel(channelName)
      if (!channel) return
    }

    console.log(`👂 Listening to event ${eventName} on channel ${channelName}`)
    
    channel.listen(eventName, (data) => {
      console.log(`📩 Received event ${eventName} on channel ${channelName}:`, data)
      // Parse data if it's a string
      const parsedData = typeof data.data === "string" ? JSON.parse(data.data) : data
      callback(parsedData)
    })

    // Store the listener for cleanup
    if (!this.listeners[channelName]) {
      this.listeners[channelName] = {}
    }
    
    if (!this.listeners[channelName][eventName]) {
      this.listeners[channelName][eventName] = []
    }
    
    this.listeners[channelName][eventName].push(callback)
  }
  
  // Method to register a callback for a specific event
  on(eventName, callback) {
    if (!this.eventCallbacks) {
      this.eventCallbacks = {}
    }
    
    if (!this.eventCallbacks[eventName]) {
      this.eventCallbacks[eventName] = []
    }
    
    this.eventCallbacks[eventName].push(callback)
    console.log(`👂 Registered callback for event: ${eventName}`)
    
    // If this is a post event, make sure we're subscribed to the posts channel
    if (['PostCreated', 'PostUpdated', 'PostDeleted'].includes(eventName)) {
      this.subscribeToChannel('posts')
    }
  }
  
  // Method to remove a callback for a specific event
  off(eventName, callback) {
    if (!this.eventCallbacks || !this.eventCallbacks[eventName]) {
      return
    }
    
    this.eventCallbacks[eventName] = this.eventCallbacks[eventName].filter(cb => cb !== callback)
    console.log(`🔕 Removed callback for event: ${eventName}`)
  }
  
  // Method to trigger callbacks for a specific event
  triggerEvent(eventName, data) {
    if (!this.eventCallbacks || !this.eventCallbacks[eventName]) {
      return
    }
    
    console.log(`🔔 Triggering ${this.eventCallbacks[eventName].length} callbacks for event: ${eventName}`)
    this.eventCallbacks[eventName].forEach(callback => {
      try {
        callback(data)
      } catch (error) {
        console.error(`❌ Error in callback for event ${eventName}:`, error)
      }
    })
  }

  unsubscribeFromChannel(channelName) {
    if (!this.echo || !this.channels[channelName]) {
      return;
    }

    try {
      console.log(`🔌 Unsubscribing from channel: ${channelName}`);
      this.echo.leave(channelName);
    } catch (error) {
      console.warn(`Error unsubscribing from channel ${channelName}:`, error);
    } finally {
      // Always clean up our internal references
      delete this.channels[channelName];
      delete this.listeners[channelName];
    }
  }

  disconnect() {
    if (!this.echo) {
      console.log("⚠️ WebSocket not initialized, nothing to disconnect")
      return
    }
    
    if (!this.connected && !this.connecting) {
      console.log("⚠️ WebSocket already disconnected")
      return
    }

    console.log("🔌 Disconnecting WebSocket")
    
    // Unsubscribe from all channels
    Object.keys(this.channels).forEach(channelName => {
      this.unsubscribeFromChannel(channelName)
    })
    
    // Clear all listeners
    this.listeners = {}
    this.channels = {}
    this.eventCallbacks = {}
    
    // Disconnect Echo
    this.echo.disconnect()
    this.connected = false
    this.connecting = false
  }
  
  // Check if connected
  isConnected() {
    return this.connected
  }
  
  // Get socket ID
  getSocketId() {
    if (!this.echo || !this.connected) {
      return null
    }
    return this.echo.socketId()
  }
}

// Create a singleton instance
const websocketService = new WebSocketService()

// Create a hook-like function to use the service
export function useWebSocketService() {
  return websocketService
}

// Also export the singleton for backward compatibility
export default websocketService
