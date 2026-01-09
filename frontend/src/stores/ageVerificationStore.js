import { defineStore } from "pinia"

export const useAgeVerificationStore = defineStore("ageVerification", {
  state: () => ({
    isVerified: localStorage.getItem("age-verified") === "true",
  }),

  actions: {
    setVerified(value) {
      this.isVerified = value
      localStorage.setItem("age-verified", value)
    },

    checkVerification() {
      return this.isVerified
    },
  },
})

