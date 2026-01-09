import { defineStore } from "pinia"
import axiosInstance from "@/axios"
import { useAuthStore } from "@/stores/authStore"
import Echo from "laravel-echo"
import Pusher from "pusher-js"

export const useNotificationsStore = defineStore("notifications", {
    state: () => ({
        notifications: [],
        unreadCount: 0,
        error: null,
        loading: false,
        echo: null,
        socketConnected: false,
        activeFilter: 'all',
        showFilters: false,
        markingAllAsRead: false,
        toasts: [], // For toast notifications
        processingTagId: null, // Track which tag is being processed
        processingAction: null, // Track which action is being performed
    }),

    getters: {
        filteredNotifications: (state) => {
            if (state.activeFilter === 'all') {
                return state.notifications
            } else if (state.activeFilter === 'unread') {
                return state.notifications.filter(notification => !notification.read)
            } else if (state.activeFilter === 'follow') {
                return state.notifications.filter(notification =>
                    notification.data.type === 'follow'
                )
            } else if (state.activeFilter === 'like') {
                return state.notifications.filter(notification =>
                    notification.data.type === 'like'
                )
            } else if (state.activeFilter === 'tag') {
                return state.notifications.filter(notification =>
                    ['tag_request', 'tag_approved', 'tag_rejected'].includes(notification.data.type)
                )
            }

            return state.notifications
        },

        hasUnreadNotifications: (state) => {
            return state.notifications.some(notification => !notification.read)
        }
    },

    actions: {
        // Respond to a tag request (approve or reject)
        async respondToTagRequest(notification, response) {
            console.log('Processing tag response:', { notification, response });
            const tagId = this.getTagId(notification);
            
            if (!tagId) {
                console.error('Tag ID not found in notification:', notification);
                return { success: false, error: 'Tag ID not found' };
            }

            this.processingTagId = tagId;
            this.processingAction = response;

            try {
                // Send tag_id as required by the API
                const apiResponse = await axiosInstance.post('/tags/respond', {
                    tag_id: tagId,
                    response: response
                });

                // Update the notification data immediately in the store's notifications array
                const index = this.notifications.findIndex(n => n.id === notification.id);
                if (index !== -1) {
                    console.log('Before update - Tag status:', this.getTagStatus(this.notifications[index]));
                    
                    const newStatus = response === 'approve' ? 'approved' : 'rejected';
                    
                    // Create or update the tag object with the new status
                    this.notifications[index].data.tag = {
                        id: tagId,
                        status: newStatus,
                        updated_at: new Date().toISOString()
                    };
                    
                    console.log('After update - Tag status:', this.getTagStatus(this.notifications[index]));
                }

                // Mark the notification as read
                await this.markAsRead(notification);

                // Show success toast
                this.addToast({
                    type: 'success',
                    title: 'Success',
                    message: `Tag request ${response === 'approve' ? 'approved' : 'rejected'} successfully`,
                    duration: 3000
                });

                // Fetch fresh notifications to ensure everything is in sync
                await this.fetchNotifications();

                this.processingTagId = null;
                this.processingAction = null;

                return { success: true };
            } catch (error) {
                console.error('Error processing tag response:', error);
                
                // Show error toast
                this.addToast({
                    type: 'error',
                    title: 'Error',
                    message: error.response?.data?.message || 'Failed to process tag response',
                    duration: 5000
                });

                this.processingTagId = null;
                this.processingAction = null;

                return { success: false, error: error.response?.data?.message || 'Failed to process tag response' };
            }
        },

        // Helper method to get tag ID from notification
        getTagId(notification) {
            // First check if we have a tag object with an ID
            if (notification.data.tag && notification.data.tag.id) {
                return notification.data.tag.id;
            }
            
            // Then check for a direct tag_id property
            if (notification.data.tag_id) {
                return notification.data.tag_id;
            }
            
            // Check other possible locations
            if (notification.data.post_tag_id) {
                return notification.data.post_tag_id;
            }
            
            // If we have a post_id, we might need to use that to find the tag
            if (notification.data.post_id) {
                // This is a fallback, but ideally we should have the tag ID directly
                return notification.data.post_id;
            }
            
            return null;
        },

        // Helper method to process the tag response once we have the tag IDß
        async processTagResponse(tag_id, response, notification) {
            this.processingTagId = tag_id;
            this.processingAction = response;

            try {
                console.log(`🔍 Responding to tag ${tag_id} with: ${response}`);

                // Send tag_id as required by the API
                const apiResponse = await axiosInstance.post('/tags/respond', {
                    tag_id: tag_id,
                    response: response
                });

                console.log(`✅ Tag ${tag_id} ${response}d successfully:`, apiResponse.data);

                // Update the notification data directly to reflect the new status
                if (notification.data.tag) {
                    notification.data.tag.status = response === 'approve' ? 'approved' : 'rejected';
                    notification.data.tag.updated_at = new Date().toISOString();
                } else {
                    // If there's no tag object, create one
                    notification.data.tag = {
                        id: tag_id,
                        status: response === 'approve' ? 'approved' : 'rejected',
                        updated_at: new Date().toISOString()
                    };
                }

                // Mark notification as read
                await this.markAsRead(notification);

                // Fetch fresh notifications to ensure everything is up to date
                await this.fetchNotifications();

                // Show success notification
                this.addToast({
                    type: 'success',
                    title: response === 'approve' ? 'Tag Approved' : 'Tag Rejected',
                    message: apiResponse.data.message || `Tag request ${response === 'approve' ? 'approved' : 'rejected'} successfully`
                });

                return { success: true, data: apiResponse.data };
            } catch (err) {
                console.error(`❌ Error ${response}ing tag:`, err);
                const errorMessage = err.response?.data?.message || `Failed to ${response} tag request`;

                // Show error notification
                this.addToast({
                    type: 'error',
                    title: 'Error',
                    message: errorMessage
                });

                return { success: false, error: errorMessage };
            } finally {
                this.processingTagId = null;
                this.processingAction = null;
            }
        },

        // Helper method to get tag status from notification
        getTagStatus(notification) {
            // For tag requests, if there's no status, it means it's pending
            if (notification.data.type === 'tag_request') {
                // If there's a tag object with status, use that
                if (notification.data.tag && notification.data.tag.status) {
                    return notification.data.tag.status.toLowerCase();
                }
                // If there's no tag object or status, it means the request is pending
                return 'pending';
            }
            return null; // For non-tag request notifications
        },

        // Initialize Echo for WebSockets
        initializeEcho() {
            const authStore = useAuthStore()
            const token = localStorage.getItem("auth_token")

            // Check if token exists before initializing Echo
            if (!token) {
                console.warn("⚠️ Cannot initialize WebSocket: No auth token found")
                return
            }

            if (!this.echo) {
                console.log("🔌 Initializing WebSocket connection for notifications...")

                // Set up Pusher globally as in your example
                window.Pusher = Pusher

                const wsHost = import.meta.env.VITE_WS_HOST
                const wsKey = import.meta.env.VITE_WS_KEY
                if (!wsHost || !wsKey) {
                    console.warn("⚠️ WebSocket config missing (VITE_WS_HOST/VITE_WS_KEY). Skipping notifications socket.")
                    return
                }

                const wsCluster = import.meta.env.VITE_WS_CLUSTER || "mt1"
                const wsPort = Number(import.meta.env.VITE_WS_PORT || 6001)
                const wssPort = Number(import.meta.env.VITE_WSS_PORT || 443)
                const forceTLS = (import.meta.env.VITE_WS_FORCE_TLS || "false") === "true"
                const authEndpoint = import.meta.env.VITE_WS_AUTH_ENDPOINT || `${import.meta.env.VITE_API_URL}/broadcasting/auth`

                // Create Echo instance with the same configuration pattern as your example
                window.Echo = new Echo({
                    broadcaster: "pusher",
                    key: wsKey,
                    cluster: wsCluster,
                    wsHost,
                    wsPort,
                    wssPort,
                    forceTLS,
                    disableStats: true,
                    enabledTransports: ["ws", "wss"], // Explicitly enable WebSocket transports
                    auth: {
                        headers: {
                            Authorization: `Bearer ${token}`,
                            Accept: "application/json", // Add Accept header for proper content negotiation
                        },
                    },
                    authEndpoint,
                })

                // Store Echo instance for later use
                this.echo = window.Echo

                console.log("📡 WebSocket config for notifications:", {
                    key: wsKey,
                    cluster: wsCluster,
                    wsHost,
                    wsPort,
                    wssPort,
                    forceTLS,
                    authEndpoint,
                })

                // Add connection status logging with more details
                this.echo.connector.pusher.connection.bind("connecting", () => {
                    console.log("🔄 WebSocket connecting...")
                    this.socketConnected = false
                })

                this.echo.connector.pusher.connection.bind("connected", () => {
                    console.log("✅ WebSocket connected successfully!")
                    console.log("📡 Socket ID:", this.echo.socketId())
                    this.socketConnected = true

                    // Log the channels we're subscribed to
                    const channels = this.echo.connector.pusher.channels.channels
                    console.log("📡 Subscribed channels:", Object.keys(channels))

                    // Re-subscribe to channels if needed
                    if (authStore.user) {
                        this.listenForNotifications(authStore)
                    } else {
                        console.log("⚠️ User not authenticated yet, will listen for notifications when user is available")
                    }
                })

                this.echo.connector.pusher.connection.bind("error", (error) => {
                    console.error("❌ WebSocket connection error:", error)
                    this.socketConnected = false
                })

                this.echo.connector.pusher.connection.bind("disconnected", () => {
                    console.log("🔌 WebSocket disconnected")
                    this.socketConnected = false
                })

                this.echo.connector.pusher.connection.bind("state_change", (states) => {
                    console.log(`📡 WebSocket state changed from ${states.previous} to ${states.current}`)
                })

                // Listen for subscription errors
                this.echo.connector.pusher.bind("subscription_error", (status) => {
                    console.error("❌ Channel subscription error:", status)
                })
            }
        },

        // Listen for new notifications
        listenForNotifications(authStore) {
            if (!this.echo) {
                console.warn("⚠️ Cannot listen for notifications: Echo not initialized")
                return
            }

            if (!authStore.user) {
                console.warn("⚠️ Cannot listen for notifications: User not authenticated")
                return
            }

            const userId = authStore.user.id
            console.log(`👂 Setting up notification channel for user: ${userId}`)

            try {
                // Subscribe to private channel for the user's notifications
                // Using Laravel's default notification channel format
                const channel = this.echo.private(`App.Models.User.${userId}`)

                if (!channel) {
                    console.error(`❌ Failed to subscribe to notification channel for user ${userId}`)
                    return
                }

                // Log when the subscription succeeds
                channel.subscribed(() => {
                    console.log(`✅ Successfully subscribed to notification channel for user ${userId}`)
                })

                // Listen for notification events using Laravel's default notification format
                channel.notification((notification) => {
                    console.log("🔔 Received new notification via WebSocket:", notification)
                    this.handleNewNotification(notification)
                })

                // Listen for all events on this channel (for debugging)
                channel.listenToAll((eventName, data) => {
                    console.log(`📡 Received event on App.Models.User.${userId}: ${eventName}`, data)
                })
            } catch (error) {
                console.error("❌ Error setting up notification channel listeners:", error)
            }
        },

        // Handle new notification received via WebSocket
        handleNewNotification(notification) {
            console.log("🔍 Processing received notification:", notification)

            // Format the notification to match our expected structure
            const formattedNotification = {
                id: notification.id,
                type: notification.type,
                data: {
                    ...notification.data,
                    platform_name: 'Fans4more', // Set platform name
                    message: this.formatNotificationMessage(notification)
                },
                read: false,
                created_at: notification.created_at || new Date().toISOString()
            }

            // Add the notification to the top of our list
            this.notifications.unshift(formattedNotification)

            // Increment unread count
            this.unreadCount++
        },

        // Format notification message based on type and status
        formatNotificationMessage(notification) {
            const type = notification.data?.type;
            const status = notification.data?.tag?.status?.toLowerCase();
            
            switch (type) {
                case 'tag_request':
                    if (status === 'approved') {
                        return 'Your tag request has been approved';
                    } else if (status === 'rejected') {
                        return 'Your tag request has been rejected';
                    } else {
                        return 'Requested to tag you in a post';
                    }
                case 'follow':
                    return 'Started following you';
                case 'like':
                    return 'Liked your post';
                default:
                    return notification.data?.message || 'New notification from Fans4more';
            }
        },

        // Fetch all notifications
        async fetchNotifications() {
            this.loading = true;
            this.error = null;

            try {
                console.log("🔍 Fetching notifications");
                const response = await axiosInstance.get("/notifications");
                
                this.notifications = response.data.map(notification => {
                    // Create a new notification object
                    const newNotification = {
                        id: notification.id,
                        type: notification.type,
                        data: {
                            ...notification.data
                        },
                        read: notification.read,
                        created_at: notification.created_at
                    };
                    
                    return newNotification;
                });

                // Update unread count
                this.unreadCount = this.notifications.filter(notification => !notification.read).length;

                console.log(`✅ Fetched ${this.notifications.length} notifications (${this.unreadCount} unread)`);
                
                return { success: true, notifications: this.notifications };
            } catch (error) {
                console.error("❌ Error fetching notifications:", error);
                this.error = error.response?.data?.message || "Failed to fetch notifications";
                return { success: false, error: this.error };
            } finally {
                this.loading = false;
            }
        },

        // Get unread notification count
        async fetchUnreadCount() {
            try {
                console.log("🔍 Fetching unread notification count")
                const response = await axiosInstance.get("/notifications/unread-count")
                this.unreadCount = response.data.count
                console.log(`✅ Unread notification count: ${this.unreadCount}`)
                return { success: true, count: this.unreadCount }
            } catch (error) {
                console.error("❌ Error fetching unread notification count:", error)
                return { success: false, error: error.response?.data?.message || "Failed to fetch unread count" }
            }
        },

        // Mark a notification as read
        async markAsRead(notification) {
            if (notification.read) return { success: true }

            try {
                console.log(`✓ Marking notification ${notification.id} as read`)
                await axiosInstance.post(`/notifications/${notification.id}/read`)

                // Update local state
                const index = this.notifications.findIndex(n => n.id === notification.id)
                if (index !== -1) {
                    this.notifications[index].read = true

                    // Update unread count
                    this.unreadCount = Math.max(0, this.unreadCount - 1)
                }

                return { success: true }
            } catch (error) {
                console.error("❌ Error marking notification as read:", error)
                return { success: false, error: error.response?.data?.message || "Failed to mark notification as read" }
            }
        },

        // Mark all notifications as read
        async markAllAsRead() {
            if (this.markingAllAsRead) return { success: false, error: "Already processing" }

            this.markingAllAsRead = true

            try {
                console.log("✓ Marking all notifications as read")
                await axiosInstance.post("/notifications/read-all")

                // Update local state
                this.notifications = this.notifications.map(notification => ({
                    ...notification,
                    read: true
                }))

                // Reset unread count
                this.unreadCount = 0

                return { success: true }
            } catch (error) {
                console.error("❌ Error marking all notifications as read:", error)
                return { success: false, error: error.response?.data?.message || "Failed to mark all notifications as read" }
            } finally {
                this.markingAllAsRead = false
            }
        },

        // Remove a notification from the list
        removeNotification(notificationId) {
            const index = this.notifications.findIndex(n => n.id === notificationId)
            if (index !== -1) {
                // If the notification was unread, decrement the count
                if (!this.notifications[index].read) {
                    this.unreadCount = Math.max(0, this.unreadCount - 1)
                }

                // Remove the notification
                this.notifications.splice(index, 1)
                console.log(`✓ Removed notification ${notificationId}`)
            }
        },

        // Set active filter
        setFilter(filter) {
            this.activeFilter = filter
        },

        // Toggle filters visibility
        toggleFilters() {
            this.showFilters = !this.showFilters
        },

        // Add a toast notification
        addToast(toast) {
            const id = Date.now()
            this.toasts.push({
                id,
                type: toast.type || 'info',
                title: toast.title || '',
                message: toast.message,
                duration: toast.duration || 5000
            })

            // Auto-remove toast after duration
            setTimeout(() => {
                this.removeToast(id)
            }, toast.duration || 5000)

            return id
        },

        // Remove a toast notification
        removeToast(id) {
            const index = this.toasts.findIndex(t => t.id === id)
            if (index !== -1) {
                this.toasts.splice(index, 1)
            }
        },

        // Decrement unread count (used when handling notifications outside of this store)
        decrementUnreadCount() {
            if (this.unreadCount > 0) {
                this.unreadCount--
            }
        },

        // Cleanup method to remove Echo listeners when component is unmounted
        cleanup() {
            const authStore = useAuthStore()
            if (this.echo && authStore.user) {
                console.log(`🧹 Cleaning up notification WebSocket listeners for user ${authStore.user.id}`)
                this.echo.leave(`App.Models.User.${authStore.user.id}`)
            }
        },
    },
})
