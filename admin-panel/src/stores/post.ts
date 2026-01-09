import { defineStore } from 'pinia'
import axios from '@/plugins/axios'
import type { Post, PostState, PostFilters, PostStats, PostPagination } from '@/types/post'

export const usePostStore = defineStore('post', {
  state: (): PostState => ({
    posts: [],
    postStats: {
      total: 0,
      pending: 0,
      published: 0,
      rejected: 0,
      reported: 0
    },
    loading: false,
    statsLoading: false,
    error: null,
    totalPosts: 0,
    currentPage: 1,
    perPage: 10,
    filters: {
      status: '',
      search: ''
    },
    pagination: {
      currentPage: 1,
      totalPages: 1,
      perPage: 10,
      totalItems: 0
    },
    filteredPosts: []
  }),

  actions: {
    async fetchPostStats() {
      this.statsLoading = true
      try {
        const response = await axios.get<PostStats>('/admin/posts/stats')
        this.postStats = response.data
      } catch (error: any) {
        console.error('Failed to fetch post stats:', error)
        // Don't throw error for stats to prevent breaking the main content
        // Just keep the default values
      } finally {
        this.statsLoading = false
      }
    },

    async fetchPosts() {
      this.loading = true
      this.error = null
      try {
        const params = {
          page: this.currentPage,
          per_page: this.perPage,
          ...this.filters
        }
        const response = await axios.get('/admin/posts', { params })
        this.posts = response.data.data
        this.totalPosts = response.data.total
        this.filteredPosts = response.data.data
        
        // Update pagination
        this.pagination = {
          currentPage: response.data.meta.current_page,
          totalPages: response.data.meta.last_page,
          perPage: response.data.meta.per_page,
          totalItems: response.data.meta.total
        }
        
        // Fetch stats when posts are loaded
        await this.fetchPostStats()
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Failed to fetch posts'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updatePostStatus(postId: number, status: string, moderationNote?: string) {
      try {
        const response = await axios.patch(`/admin/posts/${postId}/status`, {
          status,
          moderation_note: moderationNote
        })
        const updatedPost = response.data
        const index = this.posts.findIndex(post => post.id === postId)
        if (index !== -1) {
          this.posts[index] = updatedPost
          this.filteredPosts[index] = updatedPost
        }
        // Refresh stats after status update
        await this.fetchPostStats()
        return updatedPost
      } catch (error: any) {
        throw error.response?.data?.message || 'Failed to update post status'
      }
    },

    updateFilters(filters: Partial<PostFilters>) {
      this.filters = { ...this.filters, ...filters }
    },

    setFilters(filters: Partial<PostFilters>) {
      this.filters = { ...this.filters, ...filters }
      this.currentPage = 1 // Reset to first page when filters change
      return this.fetchPosts()
    },

    setPage(page: number) {
      this.currentPage = page
      this.pagination.currentPage = page
      return this.fetchPosts()
    },

    async reviewReport(postId: number) {
      try {
        await axios.post(`/admin/posts/${postId}/review-report`)
        await this.fetchPosts() // Refresh posts after review
      } catch (error: any) {
        throw error.response?.data?.message || 'Failed to review report'
      }
    },

    async moderatePost({ post, action }: { post: Post; action: string }) {
      try {
        await axios.post(`/admin/posts/${post.id}/moderate`, { action })
        await this.fetchPosts() // Refresh posts after moderation
      } catch (error: any) {
        throw error.response?.data?.message || 'Failed to moderate post'
      }
    }
  }
}) 