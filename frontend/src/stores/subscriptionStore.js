import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useSubscriptionStore = defineStore("subscription", {
  state: () => ({
    tiers: [],
    currentTier: null,
    error: null,
    loading: false,
    enabledTiers: [],
    userSubscriptions: [],
    subscriptionCounts: {
      active: 0,
      expired: 0,
      all: 0,
    },
  }),

  getters: {
    getTierById: (state) => (id) => state.tiers.find((tier) => tier.id === id),
    getEnabledTiers: (state) => state.tiers.filter((tier) => tier.is_enabled && tier.subscriptions_enabled),
    disabledTiers: (state) => state.tiers.filter((tier) => !tier.is_enabled || !tier.subscriptions_enabled),

    // Subscription getters
    activeSubscriptions: (state) => state.userSubscriptions.filter((sub) => sub.status === "completed"),
    expiredSubscriptions: (state) => state.userSubscriptions.filter((sub) => sub.status === "expired"),
    allSubscriptions: (state) => state.userSubscriptions,
  },

  actions: {
    // Tier management actions
    async createTier(tierData) {
      this.loading = true
      this.error = null
      try {
        console.log('Creating tier with data:', tierData)
        const response = await axiosInstance.post("/tiers", tierData)
        console.log('Server response:', response.data)
        
        if (response.data.success) {
          const newTier = response.data.data
          // Update tiers array
          this.tiers = [...this.tiers, newTier]
          
          // Update enabledTiers if the new tier has subscriptions enabled
          if (newTier.subscriptions_enabled) {
            this.enabledTiers = [...this.enabledTiers, newTier]
          }
          
          return {
            success: true,
            message: response.data.message || 'Tier created successfully',
            data: newTier
          }
        } else {
          throw new Error(response.data.message || 'Failed to create tier')
        }
      } catch (error) {
        console.error('Error creating tier:', error)
        this.error = error.response?.data?.message || error.message || 'An error occurred while creating the tier'
        return {
          success: false,
          message: this.error,
          error: error.response?.data || error
        }
      } finally {
        this.loading = false
      }
    },

    async fetchTiers() {
      this.loading = true
      this.error = null
      try {
        console.log('Fetching tiers from API...')
        const response = await axiosInstance.get("/tiers")
        console.log('API Response:', response)
        
        if (response.data.success) {
          console.log('Setting tiers:', response.data.data)
          this.tiers = response.data.data
          // Update enabledTiers with tiers that have subscriptions_enabled set to true
          this.enabledTiers = this.tiers.filter(tier => tier.subscriptions_enabled)
          console.log('Updated store state:', {
            tiers: this.tiers,
            enabledTiers: this.enabledTiers,
            disabledTiers: this.tiers.filter(tier => !tier.subscriptions_enabled)
          })
          return { success: true, tiers: this.tiers }
        } else {
          console.error('API returned success: false', response.data)
          throw new Error("Failed to fetch tiers")
        }
      } catch (error) {
        console.error('Error fetching tiers:', error)
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchTier(tierId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get(`/tiers/${tierId}`)
        if (response.data.success) {
          this.currentTier = response.data.data
          return { success: true, tier: this.currentTier }
        } else {
          throw new Error("Failed to fetch tier")
        }
      } catch (error) {
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateTier(tierId, tierData) {
      this.loading = true
      this.error = null
      try {
        console.log('Updating tier:', tierId, tierData)
        const response = await axiosInstance.put(`/tiers/${tierId}`, tierData)
        console.log('Update response:', response.data)
        
        if (response.data.success) {
          const updatedTier = response.data.data
          // Update tiers array
          const index = this.tiers.findIndex((t) => t.id === tierId)
          if (index !== -1) {
            this.tiers = [
              ...this.tiers.slice(0, index),
              updatedTier,
              ...this.tiers.slice(index + 1)
            ]
          }
          
          // Update enabledTiers array
          const enabledIndex = this.enabledTiers.findIndex((t) => t.id === tierId)
          if (updatedTier.subscriptions_enabled) {
            if (enabledIndex === -1) {
              // Add to enabledTiers if not present
              this.enabledTiers = [...this.enabledTiers, updatedTier]
            } else {
              // Update existing entry
              this.enabledTiers = [
                ...this.enabledTiers.slice(0, enabledIndex),
                updatedTier,
                ...this.enabledTiers.slice(enabledIndex + 1)
              ]
            }
          } else if (enabledIndex !== -1) {
            // Remove from enabledTiers if present
            this.enabledTiers = [
              ...this.enabledTiers.slice(0, enabledIndex),
              ...this.enabledTiers.slice(enabledIndex + 1)
            ]
          }
          
          this.currentTier = updatedTier
          return { success: true, message: response.data.message, tier: updatedTier }
        } else {
          throw new Error(response.data.message || 'Failed to update tier')
        }
      } catch (error) {
        console.error('Error updating tier:', error)
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteTier(tierId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.delete(`/tiers/${tierId}`)
        if (response.data.success) {
          this.tiers = this.tiers.filter((t) => t.id !== tierId)
          if (this.currentTier && this.currentTier.id === tierId) {
            this.currentTier = null
          }
          return { success: true, message: response.data.message }
        } else {
          throw new Error(response.data.message)
        }
      } catch (error) {
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async toggleTierStatus(tierId) {
      console.log('toggleTierStatus called with tierId:', tierId)
      
      if (!tierId) {
        console.error('No tier ID provided to toggleTierStatus')
        return { success: false, error: "Tier ID is required" }
      }

      const tier = this.tiers.find((t) => t.id === tierId)
      console.log('Found tier:', tier)
      
      if (!tier) {
        console.error('Tier not found in store:', {
          tierId,
          availableTiers: this.tiers.map(t => ({ id: t.id, title: t.title }))
        })
        return { success: false, error: "Tier not found" }
      }

      try {
        console.log('Toggling tier status:', {
          tierId,
          currentStatus: tier.is_enabled,
          tier
        })

        // Pass the tier ID explicitly to both methods
        const result = tier.is_enabled
          ? await this.disableTier(Number(tierId))
          : await this.enableTier(Number(tierId))

        if (result.success) {
          // Update tiers based on the new status
          const index = this.tiers.findIndex(t => t.id === tierId)
          if (index !== -1) {
            this.tiers[index] = result.tier
          }
          
          // Update enabledTiers based on both is_enabled and subscriptions_enabled
          this.enabledTiers = this.tiers.filter(t => t.is_enabled && t.subscriptions_enabled)
          
          console.log('Store state after toggle:', {
            tier: this.tiers.find(t => t.id === tierId),
            allTiers: this.tiers.map(t => ({ id: t.id, title: t.title, enabled: t.is_enabled, subscriptions: t.subscriptions_enabled })),
            enabledTiersCount: this.enabledTiers.length,
            disabledTiersCount: this.disabledTiers.length
          })
        }

        return result
      } catch (error) {
        console.error('Error toggling tier status:', error)
        return { success: false, error: error.message }
      }
    },

    async getAvailablePlans(tierId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get(`/tiers/${tierId}/plans`)
        if (response.data.success) {
          return { success: true, plans: response.data.data }
        } else {
          throw new Error("Failed to fetch available plans")
        }
      } catch (error) {
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    // Purchase actions
    async purchasePost(postId, ownerId, amount) {
      this.loading = true
      this.error = null

      try {
        // Get tracking link ID from localStorage if available
        const trackingLinkId = localStorage.getItem('tracking_link_id')
        console.log('Making purchase request:', {
          postId,
          ownerId,
          amount,
          trackingLinkId
        })

        const response = await axiosInstance.post("/purchase", {
          purchasable_id: postId,
          purchasable_type: "post",
          receiver_id: ownerId,
          amount: amount,
          tracking_link_id: trackingLinkId || null
        })

        console.log('Purchase response:', response.data)
        return response.data
      } catch (error) {
        console.error("Error purchasing post:", error)
        this.error = error.response?.data?.message || "Failed to purchase post"
        return {
          success: false,
          message: this.error,
          error: error.response?.data?.error || "purchase_error",
        }
      } finally {
        this.loading = false
      }
    },

    async purchaseMessage(messageId, ownerId, amount) {
      this.loading = true
      this.error = null

      try {
        const response = await axiosInstance.post("/purchase-message", {
          message_id: messageId,
          entity_type: "App\\Models\\Message",
          receiver_id: ownerId,
          amount: amount,
        })

        return response.data
      } catch (error) {
        console.error("Error purchasing message:", error)
        this.error = error.response?.data?.message || "Failed to purchase message"
        return {
          success: false,
          message: this.error,
          error: error.response?.data?.error || "purchase_error",
        }
      } finally {
        this.loading = false
      }
    },

    // Subscription actions
    async subscribeTier(tierId, duration) {
      this.loading = true
      this.error = null
      try {
        // Get tracking link ID from localStorage if available
        const trackingLinkId = localStorage.getItem('tracking_link_id')
        console.log('Starting subscription process', {
          tierId,
          duration,
          trackingLinkId
        })

        const response = await axiosInstance.post(`/tiers/${tierId}/subscribe`, { 
          duration,
          tracking_link_id: trackingLinkId || null
        })

        console.log("Subscription response:", response.data)
        if (response.data.success) {
          // Check if this is a test mode payment (no redirect required)
          if (!response.data.data.redirect_required) {
            console.log('Processing test mode payment', {
              transactionId: response.data.data.transaction_id,
              paymentMethod: response.data.data.payment_method
            })
            // Test mode payment was processed directly
            return {
              success: true,
              message: response.data.message || "Payment processed successfully",
              data: {
                ...response.data.data,
                payment_method: response.data.data.payment_method || "test",
                redirect_required: false,
              },
            }
          }
          // Check if this is a wallet payment (no redirect required)
          else if (response.data.data.payment_method === "wallet" && !response.data.data.redirect_required) {
            console.log('Processing wallet payment', {
              transactionId: response.data.data.transaction_id
            })
            // Process wallet payment immediately
            try {
              const walletResult = await axiosInstance.post("/payments/process-wallet", {
                transaction_id: response.data.data.transaction_id,
              })

              console.log('Wallet payment result:', walletResult.data)

              if (walletResult.data.success) {
                return {
                  success: true,
                  message: "Payment processed successfully using your wallet balance",
                  data: {
                    ...walletResult.data.data,
                    payment_method: "wallet",
                    redirect_required: false,
                  },
                }
              } else {
                throw new Error(walletResult.data.message || "Wallet payment processing failed")
              }
            } catch (walletError) {
              console.error("Error processing wallet payment:", walletError)
              throw new Error("Failed to process wallet payment")
            }
          } else {
            console.log('Redirecting to payment page', {
              paymentUrl: response.data.data.payment_url,
              transactionId: response.data.data.transaction_id
            })
            // Return payment URL for redirect (live mode)
            return {
              success: true,
              message: response.data.message || "Please complete payment on the payment page",
              data: {
                ...response.data.data,
                payment_method: response.data.data.payment_method || "ccbill",
                redirect_required: true,
              },
            }
          }
        } else {
          console.error('Subscription failed:', response.data)
          throw new Error(response.data.message || "Subscription processing error")
        }
      } catch (error) {
        console.error("Error in subscribeTier:", error)
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async getSubscriberCount(tierId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get(`/tiers/${tierId}/subscriber-count`)
        if (response.data.success) {
          return { success: true, count: response.data.data.count }
        } else {
          throw new Error("Failed to fetch subscriber count")
        }
      } catch (error) {
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async enableTier(tierId) {
      this.loading = true
      this.error = null
      try {
        console.log('enableTier called with tierId:', tierId)
        
        if (!tierId) {
          throw new Error('Tier ID is required')
        }

        const numericTierId = Number(tierId)
        if (isNaN(numericTierId)) {
          throw new Error('Invalid tier ID')
        }

        const response = await axiosInstance.post(`/tiers/${numericTierId}/enable`)
        console.log('Enable tier API response:', response.data)
        
        if (response.data.success) {
          const updatedTier = response.data.data
          console.log('Updating store with enabled tier:', updatedTier)
          
          // Update the tier in the main tiers array
          const index = this.tiers.findIndex((t) => t.id === numericTierId)
          if (index !== -1) {
            this.tiers[index] = updatedTier
          }
          
          // Update enabledTiers based on both is_enabled and subscriptions_enabled
          this.enabledTiers = this.tiers.filter(t => t.is_enabled && t.subscriptions_enabled)
          
          console.log('Store state after enable:', {
            tier: this.tiers.find(t => t.id === numericTierId),
            enabledTiersCount: this.enabledTiers.length,
            disabledTiersCount: this.disabledTiers.length
          })
          
          return { success: true, message: response.data.message, tier: updatedTier }
        } else {
          throw new Error(response.data.message || 'Failed to enable tier')
        }
      } catch (error) {
        console.error('Error enabling tier:', error.response?.data || error)
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async disableTier(tierId) {
      this.loading = true
      this.error = null
      try {
        console.log('disableTier called with tierId:', tierId)
        
        if (!tierId) {
          throw new Error('Tier ID is required')
        }

        const numericTierId = Number(tierId)
        if (isNaN(numericTierId)) {
          throw new Error('Invalid tier ID')
        }

        const response = await axiosInstance.post(`/tiers/${numericTierId}/disable`)
        console.log('Disable tier API response:', response.data)
        
        if (response.data.success) {
          const updatedTier = response.data.data
          console.log('Updating store with disabled tier:', updatedTier)
          
          // Update the tier in the main tiers array
          const index = this.tiers.findIndex((t) => t.id === numericTierId)
          if (index !== -1) {
            this.tiers[index] = updatedTier
          }
          
          // Update enabledTiers based on both is_enabled and subscriptions_enabled
          this.enabledTiers = this.tiers.filter(t => t.is_enabled && t.subscriptions_enabled)
          
          console.log('Store state after disable:', {
            tier: this.tiers.find(t => t.id === numericTierId),
            enabledTiersCount: this.enabledTiers.length,
            disabledTiersCount: this.disabledTiers.length
          })
          
          return { success: true, message: response.data.message, tier: updatedTier }
        } else {
          throw new Error(response.data.message || 'Failed to disable tier')
        }
      } catch (error) {
        console.error('Error disabling tier:', error.response?.data || error)
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    // User-specific tier actions
    async getUserActiveTiers(userId) {
      this.loading = true
      this.error = null
      try {
        console.log('Fetching tiers for user:', userId)
        const response = await axiosInstance.get(`/tiers/user/${userId}`)
        console.log('API Response:', response.data)
        if (response.data.success) {
          console.log('Setting enabledTiers:', response.data.data)
          this.enabledTiers = response.data.data
          return { success: true, tiers: response.data.data }
        } else {
          throw new Error(response.data.message || "Failed to fetch user tiers")
        }
      } catch (error) {
        console.error('Error fetching user active tiers:', error)
        this.handleError(error)
        return { success: false, tiers: [], error: this.error }
      } finally {
        this.loading = false
      }
    },

    // User subscription management
    async fetchUserSubscriptions() {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get("/subscriptions")
        if (response.data.success) {
          this.userSubscriptions = response.data.data

          // Update subscription counts
          if (response.data.counts) {
            this.subscriptionCounts = response.data.counts
          } else {
            // Calculate counts if not provided by API
            this.subscriptionCounts = {
              active: this.userSubscriptions.filter((sub) => sub.status === "completed").length,
              expired: this.userSubscriptions.filter((sub) => sub.status === "expired").length,
              all: this.userSubscriptions.length,
            }
          }

          return {
            success: true,
            data: response.data.data,
            counts: this.subscriptionCounts,
          }
        } else {
          throw new Error("Failed to fetch user subscriptions")
        }
      } catch (error) {
        console.error("Error fetching user subscriptions:", error)
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async renewSubscription(subscriptionId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post(`/subscriptions/${subscriptionId}/renew`)
        if (response.data.success) {
          // Update the subscription in the store
          const updatedSubscription = response.data.data
          const index = this.userSubscriptions.findIndex((sub) => sub.id === subscriptionId)

          if (index !== -1) {
            this.userSubscriptions[index] = updatedSubscription
          }

          // Recalculate counts
          this.subscriptionCounts = {
            active: this.userSubscriptions.filter((sub) => sub.status === "completed").length,
            expired: this.userSubscriptions.filter((sub) => sub.status === "expired").length,
            all: this.userSubscriptions.length,
          }

          return {
            success: true,
            message: response.data.message || "Subscription renewed successfully",
            data: updatedSubscription,
            counts: this.subscriptionCounts,
          }
        } else {
          throw new Error(response.data.message || "Failed to renew subscription")
        }
      } catch (error) {
        console.error("Error renewing subscription:", error)
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async cancelSubscription(subscriptionId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post(`/subscriptions/${subscriptionId}/cancel`)
        if (response.data.success) {
          // Update the subscription in the store
          const updatedSubscription = response.data.data
          const index = this.userSubscriptions.findIndex((sub) => sub.id === subscriptionId)

          if (index !== -1) {
            this.userSubscriptions[index] = updatedSubscription
          }

          // Recalculate counts
          this.subscriptionCounts = {
            active: this.userSubscriptions.filter((sub) => sub.status === "completed").length,
            expired: this.userSubscriptions.filter((sub) => sub.status === "expired").length,
            all: this.userSubscriptions.length,
          }

          return {
            success: true,
            message: response.data.message || "Subscription cancelled successfully",
            data: updatedSubscription,
            counts: this.subscriptionCounts,
          }
        } else {
          throw new Error(response.data.message || "Failed to cancel subscription")
        }
      } catch (error) {
        console.error("Error canceling subscription:", error)
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    // Creator blocking functionality
    async blockCreator(creatorId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post(`/users/block`, { blocked_user_id: creatorId })
        if (response.data.success) {
          // Update blocked status in subscriptions
          this.userSubscriptions = this.userSubscriptions.map((sub) => {
            if (sub.creator.id === creatorId) {
              return {
                ...sub,
                creator: {
                  ...sub.creator,
                  blocked: true,
                },
              }
            }
            return sub
          })

          return {
            success: true,
            message: response.data.message || "Creator blocked successfully",
          }
        } else {
          throw new Error(response.data.message || "Failed to block creator")
        }
      } catch (error) {
        console.error("Error blocking creator:", error)
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async unblockCreator(creatorId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.delete(`/users/unblock/${creatorId}`)
        if (response.data.success) {
          // Update blocked status in subscriptions
          this.userSubscriptions = this.userSubscriptions.map((sub) => {
            if (sub.creator.id === creatorId) {
              return {
                ...sub,
                creator: {
                  ...sub.creator,
                  blocked: false,
                },
              }
            }
            return sub
          })

          return {
            success: true,
            message: response.data.message || "Creator unblocked successfully",
          }
        } else {
          throw new Error(response.data.message || "Failed to unblock creator")
        }
      } catch (error) {
        console.error("Error unblocking creator:", error)
        this.handleError(error)
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    // Helper method to toggle block status
    async toggleBlockCreator(creatorId, isCurrentlyBlocked) {
      if (isCurrentlyBlocked) {
        return this.unblockCreator(creatorId)
      } else {
        return this.blockCreator(creatorId)
      }
    },

    // Error handling
    handleError(error) {
      if (error.response && error.response.data) {
        this.error = error.response.data.message || "An error occurred"
      } else {
        this.error = error.message || "An unexpected error occurred"
      }
    },
  },
})

