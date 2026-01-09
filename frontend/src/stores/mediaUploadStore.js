import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useMediaUploadStore = defineStore("mediaUpload", {
  state: () => ({
    previews: [],
    isPreviewModalOpen: false,
    isSubmitting: false,
    currentContext: "default",
    currentContent: {
      content: "",
      media: [],
      permissions: [],
      scheduled_for: null,
      expires_at: null,
      tagged_users: [],
    },
    contentByContext: {
      default: {
        content: "",
        media: [],
        permissions: [[{ type: "subscribed_all_tiers", value: null }]],
        scheduled_for: null,
        expires_at: null,
        tagged_users: [],
        metadata: {},
      },
    }, // Will store content, media, permissions for each context
  }),

  getters: {
    currentContent(state) {
      // Pure getter: do not mutate state here!
      return state.contentByContext[state.currentContext] || {
        content: "",
        media: [],
        permissions: [[{ type: "subscribed_all_tiers", value: null }]],
        scheduled_for: null,
        expires_at: null,
        tagged_users: [],
      };
    },
    content() {
      return this.currentContent.content;
    },
  },

  actions: {
    initContext(contextId, { permissions = [[{ type: "subscribed_all_tiers", value: null }]], metadata = {} } = {}) {
      if (!this.contentByContext[contextId]) {
        this.contentByContext[contextId] = {
          content: "",
          media: [],
          permissions: permissions,
          tagged_users: [],
          scheduled_for: null,
          expires_at: null,
          metadata: metadata,
        };
      }
    },

    setContext(contextId) {
      this.currentContext = contextId || "default"
      if (!this.contentByContext[this.currentContext]) {
        this.contentByContext[this.currentContext] = {
          content: "",
          media: [],
          permissions: [[{ type: "subscribed_all_tiers", value: null }]],
          tagged_users: [], // Add tagged_users field
          scheduled_for: null,
          expires_at: null,
          metadata: {},
        }
      }
      // Update previews to match the current context
      this.previews = [...this.contentByContext[this.currentContext].media]
      console.log(`Context set to ${this.currentContext}, previews:`, this.previews)
    },

    setContent(content) {
      this.currentContent.content = content
    },

    addMedia(files) {
      console.log(`Adding media to context ${this.currentContext}:`, files)

      // Handle both array and single file
      const filesArray = Array.isArray(files) ? files : [files]

      const newMedia = filesArray
        .map((file) => {
          // Check if this is already a processed media object
          if (file && typeof file === "object" && "id" in file && "url" in file) {
            // If it's already a processed media object, just return it
            return file
          }

          // Check if the file is valid for URL.createObjectURL
          if (!(file instanceof Blob || file instanceof File || file instanceof MediaSource)) {
            console.warn("Invalid file object for URL.createObjectURL:", file)
            return null
          }

          // Create a new media object
          return {
            id: Date.now() + Math.random(),
            url: URL.createObjectURL(file),
            file: file,
            type: file.type?.startsWith("image/") ? "image" : "video",
            previewVersions: [],
          }
        })
        .filter(Boolean) // Remove any null entries

      this.previews.push(...newMedia)
      this.currentContent.media.push(...newMedia)

      console.log(
        `Current ${this.currentContext} state after adding media:`,
        JSON.parse(JSON.stringify(this.currentContent)),
      )
      console.log(`Current previews:`, this.previews)
    },

    removeMedia(id) {
      const media = this.previews.find((p) => p.id === id)
      if (media) {
        // Revoke all preview version URLs
        media.previewVersions?.forEach((version) => {
          if (version.url && version.url.startsWith("blob:")) {
            URL.revokeObjectURL(version.url)
          }
        })
        // Revoke main preview URL
        if (media.url && media.url.startsWith("blob:")) {
          URL.revokeObjectURL(media.url)
        }
      }
      this.previews = this.previews.filter((preview) => preview.id !== id)
      this.currentContent.media = this.currentContent.media.filter((media) => media.id !== id)
    },

    clearMedia() {
      this.previews.forEach((preview) => {
        // Revoke all preview version URLs
        preview.previewVersions?.forEach((version) => {
          if (version.url && version.url.startsWith("blob:")) {
            URL.revokeObjectURL(version.url)
          }
        })
        // Revoke main preview URL
        if (preview.url && preview.url.startsWith("blob:")) {
          URL.revokeObjectURL(preview.url)
        }
      })
      this.previews = []
      this.currentContent.media = []
    },

    openPreviewModal() {
      this.isPreviewModalOpen = true
    },

    closePreviewModal() {
      this.isPreviewModalOpen = false
    },

    reorderMedia(fromIndex, toIndex) {
      const [movedItem] = this.previews.splice(fromIndex, 1)
      this.previews.splice(toIndex, 0, movedItem)

      // Also reorder in current context media
      const [movedMediaItem] = this.currentContent.media.splice(fromIndex, 1)
      this.currentContent.media.splice(toIndex, 0, movedMediaItem)
    },

    addPreviewVersion(previewId, file) {
      const preview = this.previews.find((p) => p.id === previewId)
      if (preview) {
        if (!preview.previewVersions) {
          preview.previewVersions = []
        }
        preview.previewVersions.push({
          id: Date.now() + Math.random(),
          url: URL.createObjectURL(file),
          file: file,
        })
      }
    },

    updatePreviewVersion(previewId, versionId, file) {
      const preview = this.previews.find((p) => p.id === previewId)
      if (preview && preview.previewVersions) {
        const versionIndex = preview.previewVersions.findIndex((v) => v.id === versionId)
        if (versionIndex !== -1) {
          // Revoke old URL
          if (
            preview.previewVersions[versionIndex].url &&
            preview.previewVersions[versionIndex].url.startsWith("blob:")
          ) {
            URL.revokeObjectURL(preview.previewVersions[versionIndex].url)
          }
          // Update version
          preview.previewVersions[versionIndex] = {
            ...preview.previewVersions[versionIndex],
            url: URL.createObjectURL(file),
            file: file,
          }

          console.log("Updated preview version:", preview.previewVersions)
        }
      }
    },

    setPermissions(permissions) {
      this.currentContent.permissions = JSON.parse(JSON.stringify(permissions))
      console.log("Permissions updated in store:", this.currentContent.permissions)
    },

    clearContent() {
      if (this.contentByContext[this.currentContext]) {
        this.contentByContext[this.currentContext] = {
          content: "",
          media: [],
          permissions: [[{ type: "subscribed_all_tiers", value: null }]],
          scheduled_for: null,
          expires_at: null,
          tagged_users: [],
        };
      }
    },

    addPermissionSet() {
      this.currentContent.permissions.push([{ type: "subscribed_all_tiers", value: null }])
      console.log("Permission set added:", this.currentContent.permissions)
    },

    removePermissionSet(index) {
      this.currentContent.permissions.splice(index, 1)
      if (this.currentContent.permissions.length === 0) {
        this.currentContent.permissions.push([{ type: "subscribed_all_tiers", value: null }])
      }
      console.log("Permission set removed:", this.currentContent.permissions)
    },

    addPermission(setIndex) {
      this.currentContent.permissions[setIndex].push({ type: "subscribed_all_tiers", value: null })
    },

    removePermission(setIndex, permissionIndex) {
      this.currentContent.permissions[setIndex].splice(permissionIndex, 1)
      if (this.currentContent.permissions[setIndex].length === 0) {
        this.removePermissionSet(setIndex)
      }
    },

    updatePermission(setIndex, permissionIndex, permission) {
      this.currentContent.permissions[setIndex][permissionIndex] = permission
    },

    updatePermissionValue(setIndex, permissionIndex, value) {
      this.currentContent.permissions[setIndex][permissionIndex].value = value
    },

    // New method to add additional data to the current content
    setPostAdditionalData(data) {
      // Add additional data to the current content
      Object.keys(data).forEach((key) => {
        this.currentContent[key] = data[key]
      })
      console.log("Additional data added to content:", data)
    },

    async submitContent(endpoint = "/posts") {
      try {
        this.isSubmitting = true
        // Log the media array and full payload before building FormData
        console.log('[DEBUG] Media array being sent to backend:', JSON.stringify(this.currentContent.media, null, 2));
        console.log('[DEBUG] Full post payload being sent to backend:', JSON.stringify(this.currentContent, null, 2));
        const formData = new FormData()

        // Log the current content state before creating FormData
        console.log('Current content state before submission:', {
          content: this.currentContent.content,
          media: this.currentContent.media,
          permissions: this.currentContent.permissions,
          scheduled_for: this.currentContent.scheduled_for,
          expires_at: this.currentContent.expires_at,
          tagged_users: this.currentContent.tagged_users
        });

        // Handle different endpoint types
        if (endpoint.includes("/messages/")) {
          // Extract receiverId from endpoint if it's in the format "/messages/{id}"
          const messageRegex = /\/messages\/(\d+)/
          const match = endpoint.match(messageRegex)

          if (match && match[1]) {
            const receiverId = match[1]
            // Use the base endpoint without the ID
            endpoint = "/messages"
            // Add receiver_id to the form data
            formData.append("receiver_id", receiverId)
          }
        }

        // Add content
        formData.append("content", this.currentContent.content)
        console.log("Added content to FormData:", this.currentContent.content)

        // Add scheduling and expiration if set
        if (this.currentContent.scheduled_for) {
          formData.append("scheduled_for", this.currentContent.scheduled_for)
          console.log("Added scheduled_for to FormData:", this.currentContent.scheduled_for)
        }

        if (this.currentContent.expires_at) {
          formData.append("expires_at", this.currentContent.expires_at)
          console.log("Added expires_at to FormData:", this.currentContent.expires_at)
        }

        // Add tagged users if present
        if (this.currentContent.tagged_users && Array.isArray(this.currentContent.tagged_users)) {
          // Use the correct format for arrays in FormData
          this.currentContent.tagged_users.forEach((userId, index) => {
            formData.append(`tagged_users[${index}]`, userId)
          })
          console.log("Added tagged users to FormData:", this.currentContent.tagged_users)
        }

        // Add media files and their preview versions
        if (this.currentContent.media && Array.isArray(this.currentContent.media)) {
          this.currentContent.media.forEach((media, index) => {
            if (media.file instanceof File) {
              formData.append(`media[${index}][file]`, media.file)
              formData.append(`media[${index}][type]`, media.type)
            } else if (media.id && media.url) {
              // Existing media: must include id, url, and type
              formData.append(`media[${index}][id]`, media.id)
              formData.append(`media[${index}][url]`, media.url)
              formData.append(`media[${index}][type]`, media.type)
            }

            // Add preview versions if they exist
            if (media.previewVersions && Array.isArray(media.previewVersions)) {
              media.previewVersions.forEach((preview, previewIndex) => {
                if (preview.file instanceof File) {
                  formData.append(`media[${index}][previewVersions][${previewIndex}]`, preview.file)
                } else if (preview.id && preview.url) {
                  // Existing preview: must include id and url
                  formData.append(`media[${index}][previewVersions][${previewIndex}][id]`, preview.id)
                  formData.append(`media[${index}][previewVersions][${previewIndex}][url]`, preview.url)
                }
              })
            }
          })
        } else {
          console.warn("No media array found or it's not an array:", this.currentContent.media)
        }

        // Add permissions
        if (this.currentContent.permissions) {
          formData.append("permissions", JSON.stringify(this.currentContent.permissions))
          console.log("Added permissions to FormData:", this.currentContent.permissions)
        } else {
          console.warn("No permissions found in the content data")
        }

        // Log the entire FormData contents
        console.log("FormData contents before sending to API:")
        for (const [key, value] of formData.entries()) {
          if (value instanceof File) {
            console.log(key, "File:", value.name, "Size:", value.size, "Type:", value.type)
          } else {
            console.log(key, value)
          }
        }

        // Send the formData to the backend
        console.log(`Sending request to ${endpoint}...`)
        const response = await axiosInstance.post(endpoint, formData, {
          headers: {
            "Content-Type": "multipart/form-data",
          },
        })

        console.log("Content submitted successfully:", response.data)

        // Clear the content data after successful submission
        this.clearContent()

        return response.data
      } catch (error) {
        console.error("Error submitting content:", error)
        if (error.response) {
          console.error("Response data:", error.response.data)
          console.error("Response status:", error.response.status)
          console.error("Response headers:", error.response.headers)
        } else if (error.request) {
          console.error("No response received:", error.request)
        } else {
          console.error("Error setting up request:", error.message)
        }
        throw error
      } finally {
        this.isSubmitting = false
      }
    },

    // Backward compatibility for post-specific methods
    setPostContent(content) {
      this.setContent(content)
    },

    submitPost() {
      // Check if we're in edit mode by looking for a post ID in the current context
      const contextParts = this.currentContext.split('-');
      let postId = null;
      let endpoint = "/posts";
      if (contextParts[0] === 'edit' && contextParts[1] === 'post') {
        postId = contextParts[2];
        endpoint = `/posts/${postId}?_method=PUT`;
      }
      // Log for debugging
      console.log('[mediaUploadStore] submitPost:', {
        currentContext: this.currentContext,
        postId,
        endpoint
      });
      return this.submitContent(endpoint);
    },

    // Method to get media URLs for a specific context
    getMediaUrls(contextId = null) {
      const context = contextId || this.currentContext
      if (this.contentByContext[context] && this.contentByContext[context].media) {
        return this.contentByContext[context].media.map((media) => media.url)
      }
      return []
    },

    // Method to check if there are media files in a specific context
    hasMedia(contextId = null) {
      const context = contextId || this.currentContext
      return (
        this.contentByContext[context] &&
        this.contentByContext[context].media &&
        this.contentByContext[context].media.length > 0
      )
    },

    // Method to get the count of media files in a specific context
    getMediaCount(contextId = null) {
      const context = contextId || this.currentContext
      if (this.contentByContext[context] && this.contentByContext[context].media) {
        return this.contentByContext[context].media.length
      }
      return 0
    },

    // Initialize the store from a post object (for edit mode)
    initializeFromPost(post, contextId = "default") {
      this.contentByContext[contextId] = {
        content: post.content || "",
        media: post.media?.map(media => ({
          id: media.id,
          url: media.url,
          type: media.type,
          file: null, // We don't have the actual file when editing
          previewVersions: media.previews?.map(preview => ({
            id: preview.id,
            url: preview.url,
            file: null // We don't have the actual file when editing
          })) || []
        })) || [],
        permissions: post.permission_sets?.map(set =>
          set.permissions.map(permission => ({
            type: permission.type,
            value: permission.value
          }))
        ) || [[{ type: "subscribed_all_tiers", value: null }]],
        scheduled_for: post.scheduled_for || null,
        expires_at: post.expires_at || null,
        tagged_users: post.tags?.map(tag => tag.user_id) || [],
      };
      this.setContext(contextId);
      this.previews = this.contentByContext[contextId].media.map(m => ({ ...m, file: null }));
    },
  },
})
