// Import your axios instance
import axiosInstance from "@/axios"
import { defineStore } from "pinia"
import { useAuthStore } from "@/stores/authStore"
import websocketService from "@/services/websocketService"

export const useMessagesStore = defineStore("messages", {
  state: () => ({
    conversations: [],
    filteredConversations: [], // Store filtered conversations from API
    currentConversation: null,
    searchResults: [],
    loading: false,
    error: null,
    unreadCount: 0,
    webSocketInitialized: false,
    searchLoading: false,
    // other state properties...
  }),

  getters: {
    getConversationById: (state) => (userId) => {
      return state.conversations.find((conv) => conv.user.id === userId)
    },

    sortedConversations: (state) => {
      return [...state.conversations].sort((a, b) => {
        const aDate = a.last_message ? new Date(a.last_message.created_at) : new Date(0)
        const bDate = b.last_message ? new Date(b.last_message.created_at) : new Date(0)
        return bDate - aDate // Sort by most recent message
      })
    },

    hasUnreadMessages: (state) => {
      return state.unreadCount > 0
    },
  },

  actions: {
    // Initialize WebSocket listeners for messages
    initializeWebSocketListeners() {
      if (this.webSocketInitialized) {
        console.log('🛑 WebSocket listeners already initialized, skipping.');
        return;
      }
      this.webSocketInitialized = true;
      const authStore = useAuthStore();
      const token = localStorage.getItem("auth_token");
      // Check if token exists before initializing
      if (!token) {
        return;
      }
      if (!authStore.user) {
        return;
      }
      // Initialize the WebSocket service
      websocketService.initialize(token);
      const userId = authStore.user.id;
      try {
        // Subscribe to user channel
        websocketService.listenToEvent(`user.${userId}`, ".new.message", (data) => {
          // Parse data if it's a string
          const message = typeof data.data === "string" ? JSON.parse(data.data) : data;
          this.handleNewMessage(message, authStore);
        });
        // Listen for read receipt events
        websocketService.listenToEvent(`user.${userId}`, ".message.read", (data) => {
          // Parse data if it's a string
          const readData = typeof data.data === "string" ? JSON.parse(data.data) : data;
          this.handleMessageRead(readData);
        });
      } catch (error) {}
    },

    // Handle new message received via WebSocket
    handleNewMessage(event, authStore) {
      const message = event;
      console.log('📩 handleNewMessage fired for message ID:', message.id);
      // If this is a message in the current conversation, add it
      if (this.currentConversation) {
        const otherUserId = message.sender_id === authStore.user.id ? message.receiver_id : message.sender_id;
        if (otherUserId === this.currentConversation.user.id) {
          // Prevent duplicate messages by checking if the message already exists
          const exists = this.currentConversation.messages.some((m) => m.id === message.id);
          if (!exists) {
            this.currentConversation.messages.push(message);
          }
          // If the user is the receiver, mark as read
          if (message.receiver_id === authStore.user.id) {
            this.markMessageAsRead(message.id);
          }
        } else {
        }
      } else {
      }
      // Update the conversations list with the latest message
      const otherUserId = message.sender_id === authStore.user.id ? message.receiver_id : message.sender_id;
      const conversationIndex = this.conversations.findIndex((conv) => conv.user.id === otherUserId);
      if (conversationIndex !== -1) {
        // Update existing conversation
        this.conversations[conversationIndex].lastMessage = {
          content: message.content,
          created_at: message.created_at,
          media: message.media || [],
        };
        // Move this conversation to the top
        const conversation = this.conversations.splice(conversationIndex, 1)[0];
        this.conversations.unshift(conversation);
      } else {
        // Fetch the updated list to get the new conversation
        this.fetchRecentConversations();
      }
    },

    // Handle message read receipt via WebSocket
    handleMessageRead(event) {
      const { id, read_at } = event

      // Update the message in the current conversation if it exists
      if (this.currentConversation) {
        const messageIndex = this.currentConversation.messages.findIndex((m) => m.id === id)

        if (messageIndex !== -1) {
          this.currentConversation.messages[messageIndex].read_at = read_at
        } else {
        }
      } else {
      }
    },

    // Mark a message as read and notify the server
    async markMessageAsRead(messageId) {
      try {
        // Call the backend endpoint to mark the message as read
        const response = await axiosInstance.post(`/messages/${messageId}/read`)

        // Update the message locally
        if (this.currentConversation) {
          const messageIndex = this.currentConversation.messages.findIndex((m) => m.id === messageId)
          if (messageIndex !== -1 && !this.currentConversation.messages[messageIndex].read_at) {
            this.currentConversation.messages[messageIndex].read_at = new Date().toISOString()
          }
        }

        return true
      } catch (error) {
        return false
      }
    },

    // Update the fetchRecentConversations method to add more detailed logging
    async fetchRecentConversations() {
      this.loading = true
      this.error = null
      console.log("🔄 Starting fetchRecentConversations")

      try {
        console.log("🔍 Sending API request to /messages")
        const response = await axiosInstance.get("/messages")

        console.log("📊 COMPLETE CONVERSATIONS API RESPONSE:", {
          status: response.status,
          statusText: response.statusText,
          headers: response.headers,
          config: response.config,
          url: response.config?.url,
          method: response.config?.method,
        })

        console.log("📊 RAW CONVERSATIONS DATA:", response.data)

        // Process each conversation and log details
        this.conversations = response.data.map((conversation) => {
          console.log(`🔍 Processing conversation with user ID: ${conversation.user.id}`)
          console.log(`🔍 User role from API: ${conversation.user.role || "undefined"}`)

          const processedConversation = {
            id: conversation.id,
            user: {
              id: conversation.user.id,
              name: conversation.user.name,
              avatar: conversation.user.avatar,
              username: conversation.user.username,
              isSubscriber: conversation.user.isSubscriber || false,
              handle: conversation.user.handle || "",
              role: conversation.user.role || "user", // Make sure role is included
            },
            lastMessage: {
              content: conversation.lastMessage?.content || "",
              created_at: conversation.lastMessage?.created_at || null,
              media: conversation.lastMessage?.media || [],
            },
          }

          console.log(`✅ Processed conversation:`, processedConversation)
          return processedConversation
        })

        console.log(`✅ Fetched ${this.conversations.length} conversations successfully`)
        return { success: true, conversations: this.conversations }
      } catch (error) {
        console.error("❌ Error in fetchRecentConversations:", error)
        console.error("❌ Error details:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status,
        })

        this.error = error.response?.data?.message || "Failed to fetch recent conversations"
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    // Update the getOrCreateConversation method to add more detailed logging
    async getOrCreateConversation(receiverId) {
      this.loading = true
      this.error = null
      const authStore = useAuthStore()

      console.log(`🔄 Starting getOrCreateConversation for receiverId: ${receiverId}`)

      // Check if user is authenticated before proceeding
      if (!authStore.user) {
        console.error("❌ User not authenticated")
        this.error = "User not authenticated"
        this.loading = false
        return { success: false, error: this.error }
      }

      try {
        console.log(`🔍 Sending API request to /messages/${receiverId}`)
        const response = await axiosInstance.get(`/messages/${receiverId}`)

        // Log the raw backend response for debugging
        console.log('🪵 Raw backend response for getOrCreateConversation:', response.data)

        // Log the processed conversation object for debugging
        const conversationData = response.data
        console.log('🪵 Processed conversationData:', conversationData)

        console.log("📊 COMPLETE CONVERSATION API RESPONSE:", {
          status: response.status,
          statusText: response.statusText,
          headers: response.headers,
          config: response.config,
          url: response.config?.url,
          method: response.config?.method,
        })

        console.log("🔍 COMPLETE USER OBJECT FROM API:", conversationData.user)
        console.log("🔍 USER ROLE FROM API:", conversationData.user?.role)
        console.log("🔍 FULL CONVERSATION DATA:", conversationData)

        if (!conversationData || !conversationData.user) {
          console.error("❌ Invalid conversation data received")
          throw new Error("Invalid conversation data received")
        }

        // Process messages to include media and permission info
        console.log(`🔍 Processing ${conversationData.messages?.length || 0} messages`)
        const processedMessages = Array.isArray(conversationData.messages)
          ? conversationData.messages.map((message) => {
              console.log(`🔍 Processing message ID: ${message.id}`)
              return {
                ...message,
                media: message.media || [],
                // Don't override the user_has_permission value from the backend
                user_has_permission:
                  message.user_has_permission === false ? false : message.user_has_permission || true,
                media_previews: message.media_previews || [],
              }
            })
          : []

        // Ensure we capture and log the user's role
        const userRole = conversationData.user.role || "user"
        console.log(`🔍 Final determined user role: ${userRole}`)

        this.currentConversation = {
          user: conversationData.user
            ? {
                id: conversationData.user.id,
                name: conversationData.user.name,
                avatar: conversationData.user.avatar,
                username: conversationData.user.username,
                isSubscriber: conversationData.user.isSubscriber || false,
                handle: conversationData.user.handle || "",
                role: userRole, // Make sure role is included
              }
            : null,
          messages: processedMessages,
        }

        console.log("✅ Current conversation set:", this.currentConversation)

        // Update or add the conversation to the conversations list
        const existingIndex = this.conversations.findIndex((conv) => conv.user && conv.user.id === receiverId)
        if (existingIndex !== -1) {
          console.log(`🔄 Updating existing conversation at index ${existingIndex}`)
          this.conversations[existingIndex] = this.currentConversation
        } else {
          console.log(`➕ Adding new conversation to list`)
          this.conversations.push(this.currentConversation)
        }

        // Mark unread messages as read
        this.markUnreadMessagesAsRead(processedMessages, authStore)

        console.log(`✅ getOrCreateConversation completed successfully`)
        return { success: true, conversation: this.currentConversation }
      } catch (error) {
        console.error("❌ Error in getOrCreateConversation:", error)
        console.error("❌ Error details:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status,
        })

        this.error = error.message || "Failed to fetch or create conversation"
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    // Mark all unread messages from the other user as read
    markUnreadMessagesAsRead(messages, authStore) {
      // Check if authStore.user exists before proceeding
      if (!authStore || !authStore.user) {
        return
      }

      // Find messages where the current user is the receiver and read_at is null
      const unreadMessages = messages.filter((message) => message.receiver_id === authStore.user.id && !message.read_at)

      if (unreadMessages.length > 0) {
        // Mark each unread message as read
        unreadMessages.forEach((message) => {
          this.markMessageAsRead(message.id)
        })
      }
    },

    // Updated sendMessage method to preserve the nested array structure of permissions
    async sendMessage(receiverId, content, mediaFiles = [], visibility = false, permissions = null) {
      if (!receiverId) {
        this.error = "Receiver ID is required";
        return { success: false, error: this.error };
      }

      if (!content && (!mediaFiles || mediaFiles.length === 0)) {
        this.error = "Message content or media is required";
        return { success: false, error: this.error };
      }

      this.loading = true;
      this.error = null;

      // Initialize FormData
      const formData = new FormData();

      try {
        // Add content
        if (content) {
          formData.append("content", content.trim());
        }

        // Add receiver ID
        formData.append("receiver_id", receiverId);

        // Add visibility flag if needed
        if (visibility) {
          formData.append("visibility", visibility.toString());
        }

        // Add media files
        if (mediaFiles && mediaFiles.length > 0) {
          mediaFiles.forEach((media, index) => {
            if (media.file instanceof File) {
              formData.append(`media[${index}][file]`, media.file);
              formData.append(`media[${index}][type]`, media.type);
            }

            // Add preview versions if they exist
            if (media.previewVersions && media.previewVersions.length > 0) {
              media.previewVersions.forEach((preview, previewIndex) => {
                if (preview.file instanceof File) {
                  formData.append(`media[${index}][previewVersions][${previewIndex}]`, preview.file);
                }
              });
            }
          });
        }

        // IMPORTANT: Use the provided permissions or get from mediaUploadStore
        // Only add permissions if they exist and are not empty
        console.log("🔍 PERMISSIONS RECEIVED IN STORE:", permissions ? JSON.stringify(permissions) : "null");
        
        if (permissions && Array.isArray(permissions)) {
          console.log("🔍 PERMISSIONS IS ARRAY WITH LENGTH:", permissions.length);
          
          if (permissions.length > 0) {
            // IMPORTANT: Preserve the nested array structure - don't flatten
            console.log("🔍 PERMISSIONS STRUCTURE BEING PRESERVED:", JSON.stringify(permissions));
            formData.append("permissions", JSON.stringify(permissions));
            
            // Log what's actually being sent to the API
            const permissionsJson = formData.get("permissions");
            console.log("🔍 FINAL PERMISSIONS ADDED TO FORM DATA:", permissionsJson);
            
            try {
              // Parse it back to verify structure
              const parsedPermissions = JSON.parse(permissionsJson);
              console.log("🔍 PARSED PERMISSIONS STRUCTURE:", 
                Array.isArray(parsedPermissions) ? "Array" : typeof parsedPermissions,
                "Length:", Array.isArray(parsedPermissions) ? parsedPermissions.length : "N/A"
              );
              
              // Check if first item is an object with type property or an array
              if (Array.isArray(parsedPermissions) && parsedPermissions.length > 0) {
                console.log("🔍 FIRST PERMISSION ITEM TYPE:", 
                  typeof parsedPermissions[0],
                  "Is Array:", Array.isArray(parsedPermissions[0]),
                  "Has type property:", !Array.isArray(parsedPermissions[0]) && 
                    typeof parsedPermissions[0] === 'object' ? 
                    'type' in parsedPermissions[0] : false
                );
              }
            } catch (e) {
              console.error("❌ Error parsing permissions JSON:", e);
            }
          } else {
            console.log("🔍 PERMISSIONS ARRAY IS EMPTY, NOT ADDING TO REQUEST");
          }
        } else {
          console.log("🔍 NO VALID PERMISSIONS TO ADD");
        }

        // We'll use the route with receiverId in the URL as it's more RESTful
        const response = await axiosInstance.post(`/messages/${receiverId}`, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        });

        const newMessage = response.data;

        return { success: true, message: newMessage };
      } catch (error) {
        console.error('❌ Error in messagesStore.sendMessage:', error);
        console.error('❌ Error response data:', error.response?.data);
        
        this.error = error.response?.data?.message || "Failed to send message";
        
        // Check if this is a tipping requirement error
        if (error.response?.data?.error === 'tip_required' || 
            error.response?.data?.message?.includes('tip is required')) {
          console.log('💰 Tip required error detected, re-throwing error');
          // Re-throw the error so the frontend can handle it and show the tip modal
          throw error;
        }
        
        console.log('❌ Non-tipping error, returning error object');
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    // Add new methods for media interactions and unlocking
    async likeMedia(mediaId) {
      try {
        const response = await axiosInstance.post(`/media/${mediaId}/like`)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async bookmarkMedia(mediaId) {
      try {
        const response = await axiosInstance.post(`/media/${mediaId}/bookmark`)
        return response.data
      } catch (error) {
        throw error
      }
    },

    async viewMedia(mediaId) {
      try {
        const response = await axiosInstance.post(`/media/${mediaId}/view`)
        return response.data
      } catch (error) {
        throw error
      }
    },

    // Unlock a message
    async unlockMessage({ messageId, permissions }) {
      this.loading = true
      try {
        // Call the backend endpoint to unlock the message
        const response = await axiosInstance.post(`/messages/${messageId}/unlock`, {
          permissions: permissions,
        })

        // Update the message in the current conversation
        if (this.currentConversation) {
          const messageIndex = this.currentConversation.messages.findIndex((m) => m.id === messageId)
          if (messageIndex !== -1) {
            // Create a new message object with the updated data
            this.currentConversation.messages[messageIndex] = {
              ...this.currentConversation.messages[messageIndex],
              user_has_permission: true,
              media: response.data.media || this.currentConversation.messages[messageIndex].media,
            }

            // Remove required_permissions since the user now has permission
            delete this.currentConversation.messages[messageIndex].required_permissions
          }
        }

        this.error = null
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || "Failed to unlock message"
        throw error
      } finally {
        this.loading = false
      }
    },

    // New methods for messaging permissions and tip functionality
    // Update the checkMessagePermissions method to ensure it properly handles creator checks
    async checkMessagePermissions(receiverId) {
      console.log(`🔍 checkMessagePermissions called for receiverId: ${receiverId}`)
      const authStore = useAuthStore()

      try {
        // First, check if the receiver exists in our conversations or fetch their data
        let receiver = null
        let receiverRole = null

        // Try to find the user in conversations
        for (const conv of this.conversations) {
          if (conv.user && conv.user.id === receiverId) {
            receiver = conv.user
            break
          }
        }

        // If not found in conversations, check currentConversation
        if (!receiver && this.currentConversation && this.currentConversation.user.id === receiverId) {
          receiver = this.currentConversation.user
        }

        console.log(`🔍 Receiver found in store:`, receiver)

        // IMPORTANT: First check if we have a currentConversation with a role from the API
        if (
          this.currentConversation &&
          this.currentConversation.user &&
          this.currentConversation.user.id === receiverId
        ) {
          receiverRole = this.currentConversation.user.role
          console.log(`🔍 Using role from currentConversation:`, receiverRole)
        } else if (receiver) {
          receiverRole = receiver.role
          console.log(`🔍 Using role from store:`, receiverRole)
        }

        console.log(`🔍 Final determined receiver role:`, receiverRole)

        // If we found the user and their role is not 'creator', return default settings
        if (receiverRole && receiverRole !== "creator") {
          console.log(`🔍 Receiver is not a creator, returning default settings`)
          return {
            success: true,
            data: {
              show_read_receipts: true,
              require_tip_for_messages: false,
              accept_messages_from_followed: true,
            },
          }
        }

        // If we don't know the user's role yet, or they are a creator, fetch their settings
        console.log(`🔍 Fetching message settings from API for receiverId: ${receiverId}`)
        const response = await axiosInstance.get(`/users/${receiverId}/settings/messaging`)

        // Log the complete raw response with headers
        console.log(`🔍 COMPLETE API RESPONSE FOR MESSAGE SETTINGS:`, {
          status: response.status,
          statusText: response.statusText,
          headers: response.headers,
          config: response.config,
          url: response.config?.url,
          method: response.config?.method,
        })
        console.log(`📊 FULL API RESPONSE:`, response)
        console.log(`📊 RESPONSE DATA:`, response.data)

        // Check if the response has a success property and it's true
        if (response.data && response.data.success === true) {
          console.log(`✅ API returned success with data:`, response.data.data)
          return {
            success: true,
            data: response.data.data || {
              show_read_receipts: true,
              require_tip_for_messages: receiverRole === "creator",
              accept_messages_from_followed: true,
            },
          }
        }
        // If response has data but no success property, assume it's successful
        else if (response.data && response.data.data) {
          console.log(`✅ API returned data without success property:`, response.data.data)
          return {
            success: true,
            data: response.data.data,
          }
        }
        // If response has direct properties that match what we need
        else if (
          response.data &&
          (typeof response.data.show_read_receipts !== "undefined" ||
            typeof response.data.require_tip_for_messages !== "undefined" ||
            typeof response.data.accept_messages_from_followed !== "undefined")
        ) {
          console.log(`✅ API returned direct properties:`, response.data)
          return {
            success: true,
            data: response.data,
          }
        }
        // Otherwise use default values based on user role
        else {
          console.log(`⚠️ API returned unexpected format, using defaults for creator:`, response.data)
          return {
            success: true,
            data: {
              show_read_receipts: true,
              require_tip_for_messages: receiverRole === "creator",
              accept_messages_from_followed: true,
            },
          }
        }
      } catch (error) {
        // Always return a valid result with default values rather than throwing
        console.error(`❌ Error fetching message settings:`, error)
        console.log(`⚠️ Using safe default settings due to error`)
        return {
          success: false,
          message: error.response?.data?.message || error.message || "Failed to check message permissions",
          // Default values if we can't fetch settings - ALWAYS default require_tip_for_messages to true for safety
          data: {
            show_read_receipts: true,
            require_tip_for_messages: true, // Safer to default to true
            accept_messages_from_followed: true,
          },
        }
      }
    },

    // Method to check if the current user follows the receiver
    async checkFollowStatus(receiverId) {
      try {
        const authStore = useAuthStore()
        const response = await axiosInstance.get(`/users/${receiverId}/followers/${authStore.user.id}`)

        // Safely extract the is_following value with proper error handling
        let isFollowing = false

        if (response.data && response.data.data && typeof response.data.data.is_following === "boolean") {
          isFollowing = response.data.data.is_following
          console.log("✅ Follow status retrieved:", response.data.data)
        } else if (response.data && typeof response.data.is_following === "boolean") {
          isFollowing = response.data.is_following
          console.log("✅ Follow status retrieved:", response.data)
        } else {
          console.log("⚠️ Follow status not found in response:", response.data)
        }

        return {
          success: true,
          isFollowing: isFollowing,
        }
      } catch (error) {
        console.error("❌ Error checking follow status:", error)
        return {
          success: false,
          isFollowing: false,
        }
      }
    },

    // Method to check mutual follow status between current user and another user
    async checkMutualFollowStatus(otherUserId) {
      try {
        const authStore = useAuthStore()
        const response = await axiosInstance.get(`/users/${authStore.user.id}/mutual-follow/${otherUserId}`)

        // Safely extract the mutual follow data with proper error handling
        let mutualFollowData = {
          userFollowsOther: false,
          otherFollowsUser: false,
          mutualFollow: false
        }

        if (response.data && response.data.data) {
          mutualFollowData = {
            userFollowsOther: response.data.data.user_follows_other || false,
            otherFollowsUser: response.data.data.other_follows_user || false,
            mutualFollow: response.data.data.mutual_follow || false
          }
          console.log("✅ Mutual follow status retrieved:", response.data.data)
        } else if (response.data) {
          mutualFollowData = {
            userFollowsOther: response.data.user_follows_other || false,
            otherFollowsUser: response.data.other_follows_user || false,
            mutualFollow: response.data.mutual_follow || false
          }
          console.log("✅ Mutual follow status retrieved:", response.data)
        } else {
          console.log("⚠️ Mutual follow status not found in response:", response.data)
        }

        return {
          success: true,
          ...mutualFollowData
        }
      } catch (error) {
        console.error("❌ Error checking mutual follow status:", error)
        return {
          success: false,
          userFollowsOther: false,
          otherFollowsUser: false,
          mutualFollow: false
        }
      }
    },

    // UPDATED: Use the subscription store to purchase a message
    async purchaseMessage(messageId, receiverId, amount) {
      console.log(
        `🔄 Starting purchaseMessage for messageId: ${messageId}, receiverId: ${receiverId}, amount: ${amount}`,
      )
      try {
        const { useSubscriptionStore } = await import('@/stores/subscriptionStore')
        const subscriptionStore = useSubscriptionStore()

        // Use the subscription store's purchaseMessage method
        const response = await subscriptionStore.purchaseMessage(messageId, receiverId, amount)

        console.log("📊 PURCHASE MESSAGE RESPONSE:", response)

        return response
      } catch (error) {
        console.error("❌ Error purchasing message:", error)
        console.error("❌ Error details:", {
          message: error.message,
          response: error.response?.data,
          status: error.response?.status,
        })

        return {
          success: false,
          error: error.response?.data?.message || "Failed to purchase message",
        }
      }
    },

    /**
     * Link a tip transaction to a message
     * @param {number} transactionId - The ID of the transaction
     * @param {number} messageId - The ID of the message
     * @returns {Promise<Object>} - Result of the operation
     */
    async linkTipToMessage(transactionId, messageId) {
      try {
        // Call the API to link the transaction to the message
        const response = await axiosInstance.post(`/transactions/${transactionId}/link`, {
          tippable_type: "message",
          tippable_id: messageId,
        })

        if (response.data.success) {
          // Update the message in the current conversation to show it's tipped
          if (this.currentConversation) {
            const messageIndex = this.currentConversation.messages.findIndex((m) => m.id === messageId)
            if (messageIndex !== -1) {
              this.currentConversation.messages[messageIndex].is_tipped = true
              // If we have the tip amount in the response, add it
              if (response.data.data && response.data.data.amount) {
                this.currentConversation.messages[messageIndex].tip_amount = response.data.data.amount
              }
            }
          }

          return {
            success: true,
            message: "Transaction linked to message successfully",
          }
        } else {
          return {
            success: false,
            error: response.data.message,
            details: response.data.error,
          }
        }
      } catch (error) {
        // Log more detailed error information
        if (error.response) {
        } else if (error.request) {
        } else {
        }

        return {
          success: false,
          error: error.response?.data?.message || error.message || "Failed to link transaction to message",
          details: error.response?.data?.error || null,
        }
      }
    },

    // Follow a user
    async followUser(userId) {
      try {
        const response = await axiosInstance.post(`/follow/${userId}`)

        return response.data
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || "Failed to follow user",
        }
      }
    },

    // Subscribe to a tier
    async subscribeTier(tierId, duration, creatorId) {
      try {
        const { useSubscriptionStore } = await import('@/stores/subscriptionStore')
        const subscriptionStore = useSubscriptionStore()
        const response = await subscriptionStore.subscribeTier(tierId, duration)

        return response
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || "Failed to subscribe to tier",
        }
      }
    },

    // Get a specific message by ID
    async getMessageById(messageId) {
      try {
        const response = await axiosInstance.get(`/messages/${messageId}`)

        if (response.data.success) {
          return {
            success: true,
            message: response.data.message,
          }
        } else {
          return {
            success: false,
            error: response.data.message || "Failed to fetch message",
          }
        }
      } catch (error) {
        return {
          success: false,
          error: error.response?.data?.message || "Failed to fetch message",
        }
      }
    },

    // Clear store state
    clearStore() {
      this.conversations = []
      this.currentConversation = null
      this.loading = false
      this.error = null
      this.unreadCount = 0
      this.webSocketInitialized = false
      this.searchResults = []
    },

    // Search users for messaging (copied from uploadStore.js)
    async searchUsers(query = "") {
      try {
        const response = await axiosInstance.get("/users/search", {
          params: { query },
        })
        console.log('User search results:', response.data) // Log results
        this.searchResults = response.data // Store results in state
        return response.data
      } catch (error) {
        console.error("Error searching users:", error)
        this.searchResults = [] // Clear on error
        return []
      }
    },

    // Clear search results
    clearSearchResults() {
      this.searchResults = []
    },

    // Cleanup method to remove WebSocket listeners when component is unmounted
    cleanup() {
      try {
        const authStore = useAuthStore();
        if (authStore.user && websocketService.echo) {
          console.log(`🧹 Cleaning up WebSocket listeners for user ${authStore.user.id}`);
          websocketService.unsubscribeFromChannel(`user.${authStore.user.id}`);
        }
      } catch (error) {
        console.warn('Error during WebSocket cleanup:', error);
      } finally {
        this.webSocketInitialized = false;
      }
    },

    // Create or get a conversation with a single user (already exists as getOrCreateConversation)
    // Add a method to create group conversation by sending a message to all users in a list
    async createGroupConversation(listId, content = '') {
      // Import the list store here to avoid circular dependency at the top
      const { useListStore } = await import('@/stores/listStore')
      const listStore = useListStore()
      // Fetch members if not already loaded
      let members = listStore.getListMembers(listId)
      if (!members || members.length === 0) {
        await listStore.fetchListMembers(listId)
        members = listStore.getListMembers(listId)
      }
      if (!members || members.length === 0) {
        throw new Error('No members found in the selected list')
      }
      // Send a message to each user in the list
      const results = []
      for (const user of members) {
        try {
          // You may want to customize the content or permissions per user
          const result = await this.sendMessage(user.id, content || 'Hello!')
          results.push({ userId: user.id, success: result.success, message: result.message })
        } catch (error) {
          results.push({ userId: user.id, success: false, error })
        }
      }
      return results
    },
  },
})