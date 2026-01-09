import { defineStore } from "pinia"
import { useToast } from "vue-toastification"
import axiosInstance from "@/axios"
import { useListStore } from "./listStore"
import { useFeedStore } from "./feedStore"

export const usePostOptionsStore = defineStore("postOptions", {
  state: () => ({
    processing: false,
  }),

  actions: {
    async unfollow(userId) {
      const feedStore = useFeedStore()
      return await feedStore.unfollowUser(userId)
    },

    async mute(userId) {
      const feedStore = useFeedStore()
      return await feedStore.muteUser(userId)
    },

    async block(userId) {
      const feedStore = useFeedStore()
      return await feedStore.blockUser(userId)
    },

    async copyPostLink(postId) {
      const toast = useToast()
      try {
        const postUrl = `${window.location.origin}/posts/${postId}`
        await navigator.clipboard.writeText(postUrl)
        toast.success('Post link copied to clipboard')
      } catch (error) {
        toast.error('Failed to copy link')
        throw error
      }
    }
  }
})