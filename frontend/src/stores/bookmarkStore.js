import { defineStore } from "pinia"
import axiosInstance from "@/axios"

export const useBookmarkStore = defineStore("bookmark", {
  state: () => ({
    albums: [],
    currentAlbum: null,
    albumBookmarks: [],
    loading: false,
    error: null,
  }),

  getters: {
    getAlbumById: (state) => (id) => {
      return state.albums.find(album => album.id === parseInt(id))
    },
    
    getLikesAlbum: (state) => {
      return state.albums.find(album => album.title === "Likes")
    }
  },

  actions: {
    // Fetch all albums for the current user
    async fetchUserAlbums() {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get('/bookmark-albums')
        console.log("Fetched albums:", response.data)
        this.albums = response.data
        return response.data
      } catch (error) {
        console.error("Error fetching albums:", error)
        this.error = error.response?.data?.message || "An error occurred while fetching albums"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Fetch albums for a specific user
    async fetchAlbumsByUser(userId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get(`/bookmark-albums/user/${userId}`)
        console.log("Fetched user albums:", response.data)
        this.albums = response.data
        return response.data
      } catch (error) {
        console.error("Error fetching user albums:", error)
        this.error = error.response?.data?.message || "An error occurred while fetching user albums"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Fetch bookmarks for a specific album
    async fetchAlbumBookmarks(albumId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.get(`/bookmark-albums/${albumId}/bookmarks`)
        console.log("Fetched album bookmarks:", response.data)
        this.albumBookmarks = response.data
        
        // Update the album count with the actual number of bookmarks
        this.updateAlbumCount(albumId, response.data.length)
        
        return response.data
      } catch (error) {
        console.error("Error fetching album bookmarks:", error)
        this.error = error.response?.data?.message || "An error occurred while fetching album bookmarks"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Update album count with actual number of bookmarks
    updateAlbumCount(albumId, count) {
      const albumIndex = this.albums.findIndex(album => album.id === parseInt(albumId))
      if (albumIndex !== -1) {
        this.albums[albumIndex] = {
          ...this.albums[albumIndex],
          count: count
        }
      }
    },

    // Create a new album
    async createAlbum(albumData) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post('/bookmark-albums', {
          name: albumData.title,
          description: albumData.description || null,
          is_public: albumData.isPublic || false
        })
        console.log("Created album:", response.data)
        
        // Add the new album to the state
        const newAlbum = {
          id: response.data.id,
          title: response.data.name,
          description: response.data.description,
          isPublic: response.data.is_public || false,
          thumbnail: '',
          count: 0,
          created_at: response.data.created_at,
          updated_at: response.data.updated_at
        }
        
        this.albums.push(newAlbum)
        return newAlbum
      } catch (error) {
        console.error("Error creating album:", error)
        this.error = error.response?.data?.message || "An error occurred while creating the album"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Update an existing album
    async updateAlbum(albumId, albumData) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.put(`/bookmark-albums/${albumId}`, {
          name: albumData.title,
          description: albumData.description || null,
          is_public: albumData.isPublic || false
        })
        console.log("Updated album:", response.data)
        
        // Update the album in the state
        const index = this.albums.findIndex(album => album.id === parseInt(albumId))
        if (index !== -1) {
          this.albums[index] = {
            ...this.albums[index],
            title: response.data.name,
            description: response.data.description,
            isPublic: response.data.is_public || false,
            updated_at: response.data.updated_at
          }
        }
        
        return response.data
      } catch (error) {
        console.error("Error updating album:", error)
        this.error = error.response?.data?.message || "An error occurred while updating the album"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Delete an album
    async deleteAlbum(albumId) {
      this.loading = true
      this.error = null
      try {
        await axiosInstance.delete(`/bookmark-albums/${albumId}`)
        console.log("Deleted album:", albumId)
        
        // Remove the album from the state
        this.albums = this.albums.filter(album => album.id !== parseInt(albumId))
        
        return true
      } catch (error) {
        console.error("Error deleting album:", error)
        this.error = error.response?.data?.message || "An error occurred while deleting the album"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Like a post (backend will handle adding media to Likes album)
    async likePost(postId) {
      try {
        console.log("Liking post:", postId)
        const response = await axiosInstance.post(`/posts/${postId}/like`)
        console.log("Liked post:", postId)
        
        // Force refresh albums to get updated counts
        await this.fetchUserAlbums()
        
        return response.data
      } catch (error) {
        console.error("Error liking post:", error)
        this.error = error.response?.data?.message || "An error occurred while liking the post"
        throw error
      }
    },

    // Unlike a post
    async unlikePost(postId) {
      try {
        console.log("Unliking post:", postId)
        const response = await axiosInstance.delete(`/posts/${postId}/like`)
        console.log("Unliked post:", postId)
        
        // Force refresh albums to get updated counts
        await this.fetchUserAlbums()
        
        return response.data
      } catch (error) {
        console.error("Error unliking post:", error)
        this.error = error.response?.data?.message || "An error occurred while unliking the post"
        throw error
      }
    },

    // Like media (backend will handle adding to Likes album)
    async likeMedia(mediaId) {
      try {
        console.log("Liking media:", mediaId)
        const response = await axiosInstance.post(`/media/${mediaId}/like`)
        console.log("Liked media:", mediaId)
        
        // Force refresh albums to get updated counts
        await this.fetchUserAlbums()
        
        return response.data
      } catch (error) {
        console.error("Error liking media:", error)
        this.error = error.response?.data?.message || "An error occurred while liking the media"
        throw error
      }
    },

    // Unlike media
    async unlikeMedia(mediaId) {
      try {
        console.log("Unliking media:", mediaId)
        const response = await axiosInstance.delete(`/media/${mediaId}/like`)
        console.log("Unliked media:", mediaId)
        
        // Force refresh albums to get updated counts
        await this.fetchUserAlbums()
        
        return response.data
      } catch (error) {
        console.error("Error unliking media:", error)
        this.error = error.response?.data?.message || "An error occurred while unliking the media"
        throw error
      }
    },

    // Bookmark a post to an album
    async bookmarkPost(postId, albumId = null) {
      this.loading = true
      this.error = null
      try {
        const params = albumId ? { album_id: albumId } : {}
        const response = await axiosInstance.post(`/bookmarks/posts/${postId}`, params)
        console.log("Bookmarked post:", response.data)
        
        // Force refresh albums to get updated counts
        await this.fetchUserAlbums()
        
        return response.data
      } catch (error) {
        console.error("Error bookmarking post:", error)
        this.error = error.response?.data?.message || "An error occurred while bookmarking the post"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Unbookmark a post
    async unbookmarkPost(postId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.delete(`/bookmarks/posts/${postId}`)
        console.log("Unbookmarked post:", response.data)
        
        // Force refresh albums to get updated counts
        await this.fetchUserAlbums()
        
        return response.data
      } catch (error) {
        console.error("Error unbookmarking post:", error)
        this.error = error.response?.data?.message || "An error occurred while unbookmarking the post"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Bookmark media to an album
    async bookmarkMedia(mediaId, albumId = null) {
      this.loading = true
      this.error = null
      try {
        const params = albumId ? { album_id: albumId } : {}
        const response = await axiosInstance.post(`/bookmarks/media/${mediaId}`, params)
        console.log("Bookmarked media:", response.data)
        
        // Force refresh albums to get updated counts
        await this.fetchUserAlbums()
        
        return response.data
      } catch (error) {
        console.error("Error bookmarking media:", error)
        this.error = error.response?.data?.message || "An error occurred while bookmarking the media"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Unbookmark media
    async unbookmarkMedia(mediaId) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.delete(`/bookmarks/media/${mediaId}`)
        console.log("Unbookmarked media:", response.data)
        
        // Force refresh albums to get updated counts
        await this.fetchUserAlbums()
        
        return response.data
      } catch (error) {
        console.error("Error unbookmarking media:", error)
        this.error = error.response?.data?.message || "An error occurred while unbookmarking the media"
        throw error
      } finally {
        this.loading = false
      }
    },

    // Move a bookmark to a different album
    async moveBookmark(bookmarkId, albumId = null) {
      this.loading = true
      this.error = null
      try {
        const response = await axiosInstance.post(`/bookmarks/move/${bookmarkId}`, {
          album_id: albumId
        })
        console.log("Moved bookmark:", response.data)
        
        // Force refresh albums to get updated counts
        await this.fetchUserAlbums()
        
        return response.data
      } catch (error) {
        console.error("Error moving bookmark:", error)
        this.error = error.response?.data?.message || "An error occurred while moving the bookmark"
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})