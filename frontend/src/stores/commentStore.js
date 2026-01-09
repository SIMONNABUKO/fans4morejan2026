import { defineStore } from 'pinia'
import axiosInstance from '@/axios'

export const useCommentStore = defineStore('comment', {
  state: () => ({
    commentsByPost: {}, // { [postId]: [comments] }
    loading: {},        // { [postId]: boolean }
    error: {},          // { [postId]: string|null }
  }),
  actions: {
    // Fetch comments for a post
    async fetchComments(postId) {
      this.loading[postId] = true
      this.error[postId] = null
      try {
        const res = await axiosInstance.get(`/posts/${postId}/comments`)
        this.commentsByPost[postId] = res.data.comments || res.data.data || []
      } catch (e) {
        this.error[postId] = e.response?.data?.message || 'Failed to load comments.'
      } finally {
        this.loading[postId] = false
      }
    },
    // Add a comment to a post
    async addComment(postId, commentText) {
      console.log('[addComment] Sending:', { postId, commentText });
      try {
        const res = await axiosInstance.post(`/posts/${postId}/comments`, { content: commentText })
        console.log('[addComment] Response:', res.data);
        if (!this.commentsByPost[postId]) this.commentsByPost[postId] = []
        this.commentsByPost[postId].unshift(res.data.comment || res.data.data)
        return { success: true, message: res.data.message || 'Comment added', comment: res.data.comment || res.data.data }
      } catch (e) {
        console.error('[addComment] Error:', e);
        return { success: false, error: e.response?.data?.message || 'Failed to add comment' }
      }
    },
    // Delete a comment
    async deleteComment(commentId, postId) {
      try {
        await axiosInstance.delete(`/comments/${commentId}`)
        if (this.commentsByPost[postId]) {
          this.commentsByPost[postId] = this.commentsByPost[postId].filter(c => c.id !== commentId)
        }
        return { success: true }
      } catch (e) {
        return { success: false, error: e.response?.data?.message || 'Failed to delete comment' }
      }
    },
    // Edit a comment
    async editComment(commentId, newText, postId) {
      try {
        const res = await axiosInstance.put(`/comments/${commentId}`, { content: newText })
        if (this.commentsByPost[postId]) {
          const idx = this.commentsByPost[postId].findIndex(c => c.id === commentId)
          if (idx !== -1) this.commentsByPost[postId][idx] = res.data.comment || res.data.data
        }
        return { success: true, comment: res.data.comment || res.data.data }
      } catch (e) {
        return { success: false, error: e.response?.data?.message || 'Failed to edit comment' }
      }
    },
  },
}) 