import { defineStore } from 'pinia';
import axiosInstance from "@/axios";

export const useScheduledPostsStore = defineStore('scheduledPosts', {
  state: () => ({
    posts: [],
    loading: false,
    error: null,
  }),

  getters: {
    sortedPosts() {
      return [...this.posts].sort((a, b) => {
        if (!a.scheduled_for) return 1;
        if (!b.scheduled_for) return -1;
        return new Date(a.scheduled_for) - new Date(b.scheduled_for);
      });
    },

    hasPosts() {
      return this.posts.length > 0;
    }
  },

  actions: {
    async fetchScheduledPosts() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axiosInstance.get('/posts/scheduled');
        console.log('Scheduled posts API response:', response.data);

        if (Array.isArray(response.data.data)) {
          this.posts = response.data.data;
        } else {
          console.error('Unexpected API response format:', response.data);
          this.error = 'Failed to load scheduled posts. Invalid response format.';
        }

        console.log('Scheduled posts loaded:', this.posts.length);
      } catch (error) {
        console.error('Error fetching scheduled posts:', error);
        this.error = 'Failed to load scheduled posts. Please try again.';
      } finally {
        this.loading = false;
      }
    },

    // Helper method to transform post data for consistency
    transformPost(post) {
      if (!post) {
        console.error('Attempted to transform null or undefined post');
        return {
          id: 0,
          content: '',
          user_has_permission: false,
          required_permissions: [],
          media_previews: [],
          stats: {
            total_likes: 0,
            total_views: 0,
            total_bookmarks: 0,
            total_comments: 0,
            total_tips: 0,
            total_tip_amount: 0,
            is_liked: false,
            is_bookmarked: false
          }
        };
      }

      // Create a new object to avoid mutating the original
      const transformedPost = { ...post };

      // Ensure user_has_permission is a boolean
      transformedPost.user_has_permission = !!transformedPost.user_has_permission;

      // Ensure required_permissions is an array
      if (!transformedPost.required_permissions) {
        transformedPost.required_permissions = [];
      } else if (typeof transformedPost.required_permissions === 'object' && !Array.isArray(transformedPost.required_permissions)) {
        transformedPost.required_permissions = [];
      }

      // Ensure media_previews is an array
      if (!transformedPost.media_previews) {
        transformedPost.media_previews = [];
      }

      // Ensure stats exists
      if (!transformedPost.stats) {
        transformedPost.stats = {
          total_likes: 0,
          total_views: 0,
          total_bookmarks: 0,
          total_comments: 0,
          total_tips: 0,
          total_tip_amount: 0,
          is_liked: false,
          is_bookmarked: false
        };
      }

      return transformedPost;
    },

    // Clear all posts (used for logout)
    clearPosts() {
      this.posts = [];
      this.error = null;
      console.log('Scheduled posts store cleared');
    }
  }
}); 