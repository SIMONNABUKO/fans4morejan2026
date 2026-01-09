import { defineStore } from 'pinia'
import axiosInstance from '@/axios'

export const useCreatorSettingsStore = defineStore('creatorSettings', {
  state: () => ({
    followButtonEnabled: true,
    requirePaymentMethod: false,
    loading: false,
    error: null
  }),

  actions: {
    async fetchSettings() {
      this.loading = true
      try {
        const response = await axiosInstance.get('/user/settings/creator')
        this.followButtonEnabled = response.data.follow_button_enabled
        this.requirePaymentMethod = response.data.require_payment_method
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch creator settings'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateSettings(settings) {
      this.loading = true
      try {
        const response = await axiosInstance.put('/user/settings/creator', settings)
        this.followButtonEnabled = response.data.follow_button_enabled
        this.requirePaymentMethod = response.data.require_payment_method
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update creator settings'
        throw error
      } finally {
        this.loading = false
      }
    },

    async togglePaymentMethodRequirement() {
      return this.updateSettings({
        follow_button_enabled: this.followButtonEnabled,
        require_payment_method: !this.requirePaymentMethod
      })
    }
  },

  get followButtonEnabled() {
    return true;
  },

  get requirePaymentMethod() {
    return false;
  }
}) 