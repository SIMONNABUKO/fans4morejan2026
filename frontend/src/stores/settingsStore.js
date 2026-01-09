import { defineStore } from "pinia"
import axiosInstance from "@/axios"
import { ref } from "vue"

export const shouldShowEmailVerificationModal = ref(false)

export const useSettingsStore = defineStore("settings", {
  state: () => ({
    account: {
      username: "",
      display_name: "",
      email: "",
      is_email_verified: false,
      has_2fa: false,
    },
    privacyAndSecurity: {
      twoFactorAuth: false,
      privateAccount: false,
      showActivity: true,
      pushNotifications: false,
      sensitiveContentLevel: 0,
      appear_in_suggestions: true,
      enable_preview_discovery: true,
    },
    emailNotifications: {
      new_messages: true,
      subscription_purchases: true,
      subscription_renews: true,
      media_purchases: true,
      media_likes: true,
      post_replies: true,
      post_likes: true,
      tips_received: true,
      new_followers: true,
    },
    isLoading: false,
    error: null,
    isChangeEmailModalOpen: false,
    isChangePasswordModalOpen: false,
    isConfirm2FAModalOpen: false,
    recentlyUpdatedSettings: new Set(),
  }),

  actions: {
    async fetchAccountSettings() {
      this.isLoading = true
      try {
        const response = await axiosInstance.get("/user")
        this.account = {
          username: response.data.username,
          display_name: response.data.display_name,
          email: response.data.email,
          is_email_verified: response.data.email_verified_at !== null,
          has_2fa: response.data.has_2fa,
        }
      } catch (error) {
        this.error = "Failed to load account settings"
        console.error("Error fetching account settings:", error)
      } finally {
        this.isLoading = false
      }
    },

    async updateDisplayName(newDisplayName) {
      this.isLoading = true
      try {
        const response = await axiosInstance.put("/user/display-name", {
          display_name: newDisplayName,
        })
        this.account.display_name = response.data.display_name
      } catch (error) {
        this.error = "Failed to update display name"
        console.error("Error updating display name:", error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async changeEmail(newEmail, password) {
      this.isLoading = true
      this.error = null
      try {
        await axiosInstance.put("/user/email", {
          email: newEmail,
          password: password,
        })
        this.account.email = newEmail
        this.account.is_email_verified = false
        this.isChangeEmailModalOpen = false
        shouldShowEmailVerificationModal.value = true
      } catch (error) {
        this.error = "Failed to change email"
        console.error("Error changing email:", error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async changePassword(currentPassword, newPassword) {
      this.isLoading = true
      this.error = null
      try {
        await axiosInstance.put("/user/password", {
          current_password: currentPassword,
          new_password: newPassword,
          new_password_confirmation: newPassword,
        })
        this.isChangePasswordModalOpen = false
      } catch (error) {
        this.error = "Failed to change password"
        console.error("Error changing password:", error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async toggle2FA(password) {
      this.isLoading = true
      try {
        const response = await axiosInstance.post("/user/toggle-2fa", {
          password: password,
        })
        this.account.has_2fa = response.data.has_2fa
        this.isConfirm2FAModalOpen = false
      } catch (error) {
        this.error = "Failed to toggle 2FA"
        console.error("Error toggling 2FA:", error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    openChangeEmailModal() {
      this.isChangeEmailModalOpen = true
    },

    closeChangeEmailModal() {
      this.isChangeEmailModalOpen = false
    },

    openChangePasswordModal() {
      this.isChangePasswordModalOpen = true
    },

    closeChangePasswordModal() {
      this.isChangePasswordModalOpen = false
    },

    openConfirm2FAModal() {
      this.isConfirm2FAModal = true
    },

    closeConfirm2FAModal() {
      this.isConfirm2FAModal = false
    },

    async fetchAllSettings() {
      this.isLoading = true
      try {
        const response = await axiosInstance.get("/user/settings")
        this.privacyAndSecurity = response.data.privacyAndSecurity
        this.emailNotifications = response.data.emailNotifications
      } catch (error) {
        this.error = "Failed to load settings"
        console.error("Error fetching settings:", error)
      } finally {
        this.isLoading = false
      }
    },

    // Initialize settings on app startup
    async initializeSettings() {
      try {
        await this.fetchAllSettings()
      } catch (error) {
        console.error("Error initializing settings:", error)
      }
    },

    async fetchCategorySettings(category) {
      this.isLoading = true
      try {
        const response = await axiosInstance.get(`/user/settings/${category}`)
        this[category] = response.data
      } catch (error) {
        this.error = `Failed to load ${category} settings`
        console.error(`Error fetching ${category} settings:`, error)
      } finally {
        this.isLoading = false
      }
    },

    async updateSettings(category, settings) {
      this.isLoading = true
      try {
        const response = await axiosInstance.put(`/user/settings/${category}`, settings)
        this[category] = response.data
      } catch (error) {
        this.error = `Failed to update ${category} settings`
        console.error(`Error updating ${category} settings:`, error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async updateCategorySetting(category, key, value) {
      this.isLoading = true
      try {
        console.log('🔄 Store: Updating category setting:', { category, key, value })
        console.log('📊 Store: Current state before update:', JSON.stringify(this[category], null, 2))
        
        const response = await axiosInstance.put(`/user/settings/${category}`, { [key]: value })
        
        console.log('📡 Store: API response:', JSON.stringify(response.data, null, 2))
        console.log('🔍 Store: Specific key value in response:', response.data[key])
        
        // Update the local state directly with the new value
        this[category][key] = value
        console.log('✅ Store: Local state updated with value:', value)
        
        // Also update with the response data to ensure consistency
        Object.assign(this[category], response.data)
        console.log('✅ Store: State merged with response data')
        
        console.log('📊 Store: Current state after update:', JSON.stringify(this[category], null, 2))
        console.log('🔍 Store: Final value for key:', this[category][key])
      } catch (error) {
        this.error = `Failed to update ${category} setting`
        console.error(`❌ Store: Error updating ${category} setting:`, error)
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async toggleSetting(category, key) {
      const currentValue = this[category][key]
      await this.updateSettings(category, { [key]: !currentValue })
    },

    async requestEmailVerificationCode() {
      this.isLoading = true
      try {
        await axiosInstance.post('/email/request-code')
      } catch (error) {
        this.error = error?.response?.data?.message || 'Failed to send verification code.'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async verifyEmailCode(code) {
      this.isLoading = true
      try {
        await axiosInstance.post('/email/verify-code', { code })
        this.account.is_email_verified = true
      } catch (error) {
        this.error = error?.response?.data?.message || 'Invalid or expired code.'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    triggerEmailVerificationModal() {
      shouldShowEmailVerificationModal.value = true
    },

    closeEmailVerificationModal() {
      shouldShowEmailVerificationModal.value = false
    },
  },
})

