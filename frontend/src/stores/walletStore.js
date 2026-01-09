import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useWalletStore = defineStore("wallet", {
  state: () => ({
    walletBalance: 0,
    loading: false,
    error: null,
  }),
  actions: {
    async fetchWalletBalance() {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get("/wallet/balance")
        this.walletBalance = response.data.total_balance
        return { success: true }
      } catch (error) {
        if (error.response && error.response.data) {
          this.error = error.response.data.message
          return { success: false, error: this.error }
        } else {
          this.error = "Failed to fetch wallet data. Please try again."
          return { success: false, error: this.error }
        }
      } finally {
        this.loading = false
      }
    }
  },
})