import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useVaultStore = defineStore("vault", {
  state: () => ({
    albums: [],
    loading: false,
    error: null,
  }),

  actions: {
    // Fetch all media albums
    async fetchAlbums() {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get("/media-albums")
        console.log("Fetched albums:", response.data)
        if (response.data.status === "success" && response.data.data.albums) {
          this.albums = response.data.data.albums
        } else {
          throw new Error("Invalid response structure")
        }
        return response.data
      } catch (error) {
        console.error("Error fetching albums:", error)
        this.error = error.message || "An error occurred while fetching albums"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Create new media album
    async createAlbum(albumData) {
      this.loading = true 
      this.error = null
      try {
        const response = await axiosInstance.post("/media-albums", albumData)
        console.log("Created album:", response.data)
        if (response.data.status === "success" && response.data.data) {
          this.albums.push(response.data.data)
        } else {
          throw new Error("Invalid response structure")
        }
        return response.data
      } catch (error) {
        console.error("Error creating album:", error)
        this.error = error.message || "An error occurred while creating album"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Update media album
    async updateAlbum(albumId, albumData) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.put(`/media-albums/${albumId}`, albumData)
        console.log("Updated album:", response.data)
        
        if (response.data.status === "success" && response.data.data) {
          const index = this.albums.findIndex(album => album.id === albumId)
          if (index !== -1) {
            this.albums[index] = response.data.data
          }
        } else {
          throw new Error("Invalid response structure")
        }
        
        return response.data
      } catch (error) {
        console.error("Error updating album:", error)
        this.error = error.message || "An error occurred while updating album"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Delete media album
    async deleteAlbum(albumId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.delete(`/media-albums/${albumId}`)
        
        if (response.data.status === "success") {
          this.albums = this.albums.filter(album => album.id !== albumId)
        } else {
          throw new Error("Invalid response structure")
        }
        
        return true
      } catch (error) {
        console.error("Error deleting album:", error)
        this.error = error.message || "An error occurred while deleting album"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Add media to album
    async addMediaToAlbum(albumId, mediaData) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post(`/media-albums/${albumId}/add-media`, mediaData)
        console.log("Added media to album:", response.data)
        
        if (response.data.status === "success" && response.data.data) {
          const index = this.albums.findIndex(album => album.id === albumId)
          if (index !== -1) {
            this.albums[index] = response.data.data
          }
        } else {
          throw new Error("Invalid response structure")
        }
        
        return response.data
      } catch (error) {
        console.error("Error adding media to album:", error)
        this.error = error.message || "An error occurred while adding media"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Remove media from album
    async removeMediaFromAlbum(albumId, mediaData) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post(`/media-albums/${albumId}/remove-media`, mediaData)
        console.log("Removed media from album:", response.data)
        
        if (response.data.status === "success" && response.data.data) {
          const index = this.albums.findIndex(album => album.id === albumId)
          if (index !== -1) {
            this.albums[index] = response.data.data
          }
        } else {
          throw new Error("Invalid response structure")
        }
        
        return response.data
      } catch (error) {
        console.error("Error removing media from album:", error)
        this.error = error.message || "An error occurred while removing media"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Fetch album details
    async fetchAlbumDetails(albumId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get(`/media-albums/${albumId}`)
        console.log("Fetched album details:", response.data)
        if (response.data.status === "success" && response.data.data) {
          return response.data.data
        } else {
          throw new Error("Invalid response structure")
        }
      } catch (error) {
        console.error("Error fetching album details:", error)
        this.error = error.message || "An error occurred while fetching album details"
        throw error
      } finally {
        this.loading = false
      }
    },
  },

  getters: {
    // Get album by ID
    getAlbumById: (state) => (id) => {
      return state.albums.find((album) => album.id === parseInt(id))
    },

    // Get total media count across all albums
    totalMediaCount: (state) => {
      return state.albums.reduce((total, album) => total + (album.media_count || 0), 0)
    },

    // Get albums sorted by media count
    albumsSortedByMediaCount: (state) => {
      return [...state.albums].sort((a, b) => (b.media_count || 0) - (a.media_count || 0))
    }
  },
})