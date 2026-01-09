import { defineStore } from 'pinia'
import axios from '@/plugins/axios'
import type { Media, MediaState, MediaFilters } from '@/types/media'

export const useMediaStore = defineStore('media', {
  state: (): MediaState => ({
    media: [],
    loading: false,
    error: null,
    totalMedia: 0,
    currentPage: 1,
    perPage: 20,
    filters: {
      type: '',
      user_id: undefined,
      date_range: undefined,
      status: undefined
    },
    selectedMedia: null
  }),

  actions: {
    async fetchMedia() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('/admin/media', {
          params: {
            page: this.currentPage,
            per_page: this.perPage,
            ...this.filters
          }
        })
        this.media = response.data.data
        this.totalMedia = response.data.total
      } catch (error: any) {
        console.error('Failed to fetch media:', error)
        this.error = error.response?.data?.message || 'Failed to fetch media'
      } finally {
        this.loading = false
      }
    },

    async updateMediaStatus(mediaId: number, status: string) {
      try {
        await axios.patch(`/admin/media/${mediaId}/status`, { status })
        const mediaIndex = this.media.findIndex(m => m.id === mediaId)
        if (mediaIndex !== -1) {
          this.media[mediaIndex] = {
            ...this.media[mediaIndex],
            status
          }
        }
      } catch (error: any) {
        console.error('Failed to update media status:', error)
        throw error
      }
    },

    async deleteMedia(mediaId: number) {
      try {
        await axios.delete(`/admin/media/${mediaId}`)
        this.media = this.media.filter(m => m.id !== mediaId)
        this.totalMedia--
      } catch (error: any) {
        console.error('Failed to delete media:', error)
        throw error
      }
    },

    setFilters(filters: MediaFilters) {
      this.filters = filters
      this.currentPage = 1
      this.fetchMedia()
    },

    setPage(page: number) {
      this.currentPage = page
      this.fetchMedia()
    },

    selectMedia(media: Media | null) {
      this.selectedMedia = media
    }
  }
}) 