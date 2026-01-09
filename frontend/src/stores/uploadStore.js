import { defineStore } from "pinia"
import { useMediaUploadStore } from "./mediaUploadStore"
import axiosInstance from "@/axios"

// This is a compatibility layer to ensure existing code continues to work
export const useUploadStore = defineStore("upload", {
  state: () => ({
    // Empty state as we'll delegate to mediaUploadStore
    taggedUsers: [],
  }),
  getters: {
    previews() {
      const mediaStore = useMediaUploadStore()
      return mediaStore.previews
    },
    isPreviewModalOpen() {
      const mediaStore = useMediaUploadStore()
      return mediaStore.isPreviewModalOpen
    },
    isSubmitting() {
      const mediaStore = useMediaUploadStore()
      return mediaStore.isSubmitting
    },
    post() {
      const mediaStore = useMediaUploadStore()
      return {
        content: mediaStore.currentContent.content,
        media: mediaStore.currentContent.media,
        permissions: mediaStore.currentContent.permissions,
        tagged_users: this.taggedUsers.map((user) => user.id),
      }
    },
  },
  actions: {
    setPostContent(content) {
      const mediaStore = useMediaUploadStore()
      mediaStore.setContent(content)
    },
    addMedia(files) {
      const mediaStore = useMediaUploadStore()
      mediaStore.addMedia(files)
    },
    removeMedia(id) {
      const mediaStore = useMediaUploadStore()
      mediaStore.removeMedia(id)
    },
    clearMedia() {
      const mediaStore = useMediaUploadStore();
      mediaStore.clearMedia();
    },
    openPreviewModal() {
      const mediaStore = useMediaUploadStore()
      mediaStore.openPreviewModal()
    },
    closePreviewModal() {
      const mediaStore = useMediaUploadStore()
      mediaStore.closePreviewModal()
    },
    reorderMedia(fromIndex, toIndex) {
      const mediaStore = useMediaUploadStore()
      mediaStore.reorderMedia(fromIndex, toIndex)
    },
    addPreviewVersion(previewId, file) {
      const mediaStore = useMediaUploadStore()
      mediaStore.addPreviewVersion(previewId, file)
    },
    updatePreviewVersion(previewId, versionId, file) {
      const mediaStore = useMediaUploadStore()
      mediaStore.updatePreviewVersion(previewId, versionId, file)
    },
    setPermissions(permissions) {
      const mediaStore = useMediaUploadStore()
      mediaStore.setPermissions(permissions)
    },
    clearPost() {
      const mediaStore = useMediaUploadStore()
      mediaStore.clearContent()
      this.taggedUsers = []
    },
    addPermissionSet() {
      const mediaStore = useMediaUploadStore()
      mediaStore.addPermissionSet()
    },
    removePermissionSet(index) {
      const mediaStore = useMediaUploadStore()
      mediaStore.removePermissionSet(index)
    },
    addPermission(setIndex) {
      const mediaStore = useMediaUploadStore()
      mediaStore.addPermission(setIndex)
    },
    removePermission(setIndex, permissionIndex) {
      const mediaStore = useMediaUploadStore()
      mediaStore.removePermission(setIndex, permissionIndex)
    },
    updatePermission(setIndex, permissionIndex, permission) {
      const mediaStore = useMediaUploadStore()
      mediaStore.updatePermission(setIndex, permissionIndex, permission)
    },
    updatePermissionValue(setIndex, permissionIndex, value) {
      const mediaStore = useMediaUploadStore()
      mediaStore.updatePermissionValue(setIndex, permissionIndex, value)
    },
    async submitPost() {
      const mediaStore = useMediaUploadStore()

      // Add tagged users to the post data
      if (this.taggedUsers.length > 0) {
        mediaStore.setPostAdditionalData({
          tagged_users: this.taggedUsers.map((user) => user.id),
        })
      }

      const result = await mediaStore.submitPost()

      // Clear tagged users after successful submission
      this.taggedUsers = []

      return result
    },

    // New methods for tagging functionality
    setTaggedUsers(users) {
      this.taggedUsers = users
    },

    addTaggedUser(user) {
      // Check if user is already tagged
      const existingIndex = this.taggedUsers.findIndex((u) => u.id === user.id)
      if (existingIndex === -1) {
        this.taggedUsers.push(user)
      }
    },

    removeTaggedUser(userId) {
      this.taggedUsers = this.taggedUsers.filter((user) => user.id !== userId)
    },

    clearTaggedUsers() {
      this.taggedUsers = []
    },

    async searchUsers(query = "") {
      try {
        const response = await axiosInstance.get("/users/search", {
          params: { query },
        })
        return response.data
      } catch (error) {
        console.error("Error searching users:", error)
        // Return empty array if API fails
        return []
      }
    },

    // Method to get previously tagged users
    async getPreviouslyTaggedUsers() {
      const token = localStorage.getItem("auth_token")
      if (!token) {
        return []
      }

      try {
        const response = await axiosInstance.get('/tags/history/users');
        return response.data;
      } catch (error) {
        console.error('Error fetching previously tagged users:', error);
        return [];
      }
    },

    setPostAdditionalData(data) {
      const mediaStore = useMediaUploadStore()

      // Check if mediaStore has this method
      if (typeof mediaStore.setPostAdditionalData === "function") {
        mediaStore.setPostAdditionalData(data)
      } else {
        // Fallback: add the data directly to the current content
        Object.keys(data).forEach((key) => {
          mediaStore.currentContent[key] = data[key]
        })
      }
    },

    // Initialize the upload store from a post (for edit mode)
    initializeFromPost(post, contextId) {
      const mediaStore = useMediaUploadStore()
      mediaStore.initializeFromPost(post, contextId)
      // Set tagged users as objects for UI
      this.taggedUsers = post.tagged_users ? post.tagged_users.map(u => ({ id: u.id, name: u.name, username: u.username, avatar: u.avatar })) : []
    },

    setContext(contextId) {
      const mediaStore = useMediaUploadStore()
      mediaStore.setContext(contextId)
    }
  },
})
