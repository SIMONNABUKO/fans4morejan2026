import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useUploadStore = defineStore("upload", {
  state: () => ({
    previews: [],
    isPreviewModalOpen: false,
    post: {
      content: "",
      media: [],
      permissions: [],
    },
  }),
  actions: {
    setPostContent(content) {
      this.post.content = content
    },
    addMedia(files) {
      const newMedia = files.map((file) => ({
        id: Date.now() + Math.random(),
        url: URL.createObjectURL(file),
        file: file,
        type: file.type.startsWith("image/") ? "image" : "video",
        previewVersions: [],
      }))
      this.previews.push(...newMedia)
      this.post.media.push(...newMedia)
      console.log("Current post state after adding media:", JSON.parse(JSON.stringify(this.post)))
    },
    removeMedia(id) {
      const media = this.previews.find((p) => p.id === id)
      if (media) {
        // Revoke all preview version URLs
        media.previewVersions?.forEach((version) => {
          if (version.url.startsWith("blob:")) {
            URL.revokeObjectURL(version.url)
          }
        })
        // Revoke main preview URL
        if (media.url.startsWith("blob:")) {
          URL.revokeObjectURL(media.url)
        }
      }
      this.previews = this.previews.filter((preview) => preview.id !== id)
      this.post.media = this.post.media.filter((media) => media.id !== id)
    },
    clearMedia() {
      this.previews.forEach((preview) => {
        // Revoke all preview version URLs
        preview.previewVersions?.forEach((version) => {
          if (version.url.startsWith("blob:")) {
            URL.revokeObjectURL(version.url)
          }
        })
        // Revoke main preview URL
        if (preview.url.startsWith("blob:")) {
          URL.revokeObjectURL(preview.url)
        }
      })
      this.previews = []
      this.post.media = []
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

      // Also reorder in post.media
      const [movedMediaItem] = this.post.media.splice(fromIndex, 1)
      this.post.media.splice(toIndex, 0, movedMediaItem)
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
          if (preview.previewVersions[versionIndex].url.startsWith("blob:")) {
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
      this.post.permissions = JSON.parse(JSON.stringify(permissions))
      console.log("Permissions updated in store:", this.post.permissions)
    },
    clearPost() {
      this.post.content = ""
      this.post.permissions = []
      this.clearMedia()
    },
    addPermissionSet() {
      this.post.permissions.push([{ type: "subscribed_all_tiers", value: null }])
      console.log("Permission set added:", this.post.permissions)
    },
    removePermissionSet(index) {
      this.post.permissions.splice(index, 1)
      if (this.post.permissions.length === 0) {
        this.post.permissions.push([{ type: "subscribed_all_tiers", value: null }])
      }
      console.log("Permission set removed:", this.post.permissions)
    },
    addPermission(setIndex) {
      this.post.permissions[setIndex].push({ type: "subscribed_all_tiers", value: null })
    },
    removePermission(setIndex, permissionIndex) {
      this.post.permissions[setIndex].splice(permissionIndex, 1)
      if (this.post.permissions[setIndex].length === 0) {
        this.removePermissionSet(setIndex)
      }
    },
    updatePermission(setIndex, permissionIndex, permission) {
      this.post.permissions[setIndex][permissionIndex] = permission
    },
    updatePermissionValue(setIndex, permissionIndex, value) {
      this.post.permissions[setIndex][permissionIndex].value = value
    },
    
    async submitPost() {
      try {
        const formData = new FormData();
    
        // Add post content
        formData.append("content", this.post.content);
        console.log("Added content to FormData:", this.post.content);
    
        // Add media files and their preview versions
        if (this.post.media && Array.isArray(this.post.media)) {
          this.post.media.forEach((media, index) => {
            if (media.file instanceof File) {
              formData.append(`media[${index}][file]`, media.file);
              formData.append(`media[${index}][type]`, media.type);
              console.log(`Added media ${index}:`, media.file.name, 'Size:', media.file.size, 'Type:', media.file.type);
            } else {
              console.warn(`Media ${index} is not a File object:`, media.file);
            }
    
            // Add preview versions if they exist
            if (media.previewVersions && Array.isArray(media.previewVersions)) {
              media.previewVersions.forEach((preview, previewIndex) => {
                if (preview.file instanceof File) {
                  formData.append(`media[${index}][previewVersions][${previewIndex}]`, preview.file);
                  console.log(`Added preview ${previewIndex} for media ${index}:`, preview.file.name, 'Size:', preview.file.size, 'Type:', preview.file.type);
                } else {
                  console.warn(`Preview ${previewIndex} for media ${index} is not a File object:`, preview.file);
                }
              });
            }
          });
        } else {
          console.warn("No media array found or it's not an array:", this.post.media);
        }
    
        // Add permissions
        if (this.post.permissions) {
          formData.append("permissions", JSON.stringify(this.post.permissions));
          console.log("Added permissions to FormData:", this.post.permissions);
        } else {
          console.warn("No permissions found in the post data");
        }
    
        // Log the entire FormData contents
        console.log("FormData contents:");
        for (let [key, value] of formData.entries()) {
          if (value instanceof File) {
            console.log(key, 'File:', value.name, 'Size:', value.size, 'Type:', value.type);
          } else {
            console.log(key, value);
          }
        }

     
    
        // Send the formData to the backend
        console.log("Sending post request to backend...");
        const response = await axiosInstance.post(`/posts`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
    
        console.log("Post submitted successfully:", response.data);
    
        // Clear the post data after successful submission
        this.clearPost();
    
        return response.data;
      } catch (error) {
        console.error("Error submitting post:", error);
        if (error.response) {
          console.error("Response data:", error.response.data);
          console.error("Response status:", error.response.status);
          console.error("Response headers:", error.response.headers);
        } else if (error.request) {
          console.error("No response received:", error.request);
        } else {
          console.error("Error setting up request:", error.message);
        }
        throw error;
      }
    },
    
  
  
  },
})

