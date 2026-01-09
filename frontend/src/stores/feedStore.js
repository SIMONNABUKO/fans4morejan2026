// resources/js/stores/feedStore.js
import { defineStore } from 'pinia';
import axiosInstance from "@/axios"; // Use the custom axios instance
import { useWebSocketService } from '@/services/websocketService';
import { useAuthStore } from './authStore'; // Added import for authStore

export const useFeedStore = defineStore('feed', {
  state: () => ({
    posts: [],
    suggestedUsers: [],
    loading: false,
    error: null,
    hasNewPosts: false,
    newPostsCount: 0,
    lastPostId: 0,
    newPostsInfo: [], // Store info about new posts for the notification
    isLoadingNewPosts: false,
    hasMorePages: false,
    // Added back from old store
    followingInProgress: {},
    page: 1,
  }),

  getters: {
    sortedPosts() {
      // Ensure posts are sorted by ID in descending order (newest first)
      return [...this.posts].sort((a, b) => b.id - a.id);
    },

    // Get the most recent post ID
    mostRecentPostId() {
      if (this.posts.length === 0) return 0;
      return Math.max(...this.posts.map(post => post.id));
    },

    // Check if there are any posts
    hasPosts() {
      return this.posts.length > 0;
    },

    // Added back from old store
    hasSuggestedUsers() {
      return this.suggestedUsers.length > 0;
    },


  },

  actions: {
    async fetchFeed(filterId = 'all') {
      this.loading = true;
      this.error = null;

      try {
        // Use axiosInstance instead of axios
        const response = await axiosInstance.get('/feed', {
          params: { filter_id: filterId }
        });

        // Debug the response structure
        console.log('Feed API response:', response.data);

        // Handle different response structures
        let posts = [];
        let suggestedUsers = [];

        if (response.data.posts) {
          // Standard response format
          posts = response.data.posts;
          suggestedUsers = response.data.suggested_users || [];
        } else if (Array.isArray(response.data)) {
          // Array response format
          posts = response.data;
        } else if (response.data.data && Array.isArray(response.data.data)) {
          // Paginated response format
          posts = response.data.data;
          this.hasMorePages = !!response.data.next_page_url;
        } else {
          console.error('Unexpected API response format:', response.data);
        }

        // Transform posts to ensure consistent data format
        if (posts.length > 0) {
          const now = new Date()
          const transformedPosts = posts
            .map(post => this.transformPost(post))
            .filter(post => !post.scheduled_for || new Date(post.scheduled_for) <= now)
          
          // Fetch and merge preview videos for discovery
          const previewVideos = await this.fetchPreviewVideos();
          this.posts = this.mergePreviewVideosIntoFeed(transformedPosts, previewVideos);

          // Update the last post ID
          this.lastPostId = this.mostRecentPostId;
        }

        this.suggestedUsers = suggestedUsers;

        console.log('Feed loaded with', this.posts.length, 'posts (including preview videos). Last post ID:', this.lastPostId);
        console.log('feed posts:', this.posts);
      } catch (error) {
        console.error('Error fetching feed:', error);
        this.error = 'Failed to load feed. Please try again.';
      } finally {
        this.loading = false;
      }
    },

    async fetchFilteredFeed(filterId) {
      console.log('🔎 fetchFilteredFeed called with filterId:', filterId)
      console.log('🔎 filterId type:', typeof filterId)
      
      // Check current user authentication
      const authStore = useAuthStore()
      console.log('🔎 Current authenticated user:', authStore.user)
      console.log('🔎 Current user ID:', authStore.user?.id)
      console.log('🔎 Current user name:', authStore.user?.name)
      console.log('🔎 Is authenticated:', authStore.isAuthenticated)
      
      this.loading = true;
      this.error = null;

      try {
        console.log('🔎 Making API call to /user/feed-filters/filtered-feed')
        
        // Log the exact request parameters
        const requestParams = { filter_id: filterId };
        console.log('🔎 Request parameters:', requestParams)
        
        // Use axiosInstance instead of axios
        const response = await axiosInstance.get('/user/feed-filters/filtered-feed', {
          params: requestParams
        });

        console.log('🔎 API call successful!')
        console.log('🔎 Response status:', response.status)
        console.log('🔎 Response headers:', response.headers)
        console.log('🔎 Full response object:', response)
        console.log('🔎 Response data:', response.data)
        console.log('🔎 Response data type:', typeof response.data)
        console.log('🔎 Response data keys:', Object.keys(response.data || {}))

        // Debug the response structure
        console.log('Filtered Feed API response:', response.data);

        // Handle different response structures
        let posts = [];
        let suggestedUsers = [];

        if (response.data.posts) {
          // Standard response format
          posts = response.data.posts;
          suggestedUsers = response.data.suggested_users || [];
          console.log('🔎 Using standard response format')
          console.log('🔎 Posts from response.data.posts:', posts)
          console.log('🔎 Posts type:', Array.isArray(posts) ? 'Array' : typeof posts)
          console.log('🔎 Posts length:', posts ? posts.length : 'null/undefined')
        } else if (Array.isArray(response.data)) {
          // Array response format
          posts = response.data;
          console.log('🔎 Using array response format')
        } else if (response.data.data && Array.isArray(response.data.data)) {
          // Paginated response format
          posts = response.data.data;
          this.hasMorePages = !!response.data.next_page_url;
          console.log('🔎 Using paginated response format')
        } else {
          console.error('🔎 Unexpected API response format:', response.data);
        }

        console.log('🔎 Posts extracted:', posts.length)
        console.log('🔎 Posts content preview:', posts.slice(0, 2))
        console.log('🔎 Suggested users extracted:', suggestedUsers.length)

        // Transform posts to ensure consistent data format
        if (posts.length > 0) {
          const now = new Date()
          const transformedPosts = posts
            .map(post => this.transformPost(post))
            .filter(post => !post.scheduled_for || new Date(post.scheduled_for) <= now)
          this.posts = transformedPosts;

          // Update the last post ID
          this.lastPostId = this.mostRecentPostId;
          
          console.log('🔎 Transformed posts count:', transformedPosts.length)
        } else {
          // Clear posts when no posts are returned (e.g., empty list)
          this.posts = [];
          this.lastPostId = 0;
          console.log('🔎 No posts returned, clearing posts array')
        }

        this.suggestedUsers = suggestedUsers;

        console.log('🔎 Final state - posts count:', this.posts.length)
        console.log('🔎 Final state - suggested users count:', this.suggestedUsers.length)
        console.log('Filtered feed loaded with', this.posts.length, 'posts. Filter ID:', filterId);
        console.log('filtered feed posts:', this.posts);
      } catch (error) {
        console.error('🔎 Error in fetchFilteredFeed:', error);
        console.error('🔎 Error response:', error.response?.data);
        console.error('🔎 Error status:', error.response?.status);
        console.error('🔎 Error headers:', error.response?.headers);
        
        this.error = 'Failed to load filtered feed. Please try again.';
      } finally {
        this.loading = false;
        console.log('🔎 fetchFilteredFeed completed, loading set to false')
      }
    },

    // Add this action to your feedStore.js
    async fetchFeedPreviews() {
      this.loading = true;
      this.error = null;

      try {
        // Use axiosInstance instead of axios
        const response = await axiosInstance.get('/feed/previews');

        // Debug the response structure
        console.log('Feed Previews API response:', response.data);

        // Handle different response structures
        let posts = [];
        let suggestedUsers = [];

        if (response.data.posts) {
          // Standard response format
          posts = response.data.posts;
          suggestedUsers = response.data.suggested_users || [];
        } else if (Array.isArray(response.data)) {
          // Array response format
          posts = response.data;
        } else if (response.data.data && Array.isArray(response.data.data)) {
          // Paginated response format
          posts = response.data.data;
          this.hasMorePages = !!response.data.next_page_url;
        } else {
          console.error('Unexpected API response format:', response.data);
        }

        // Transform posts to ensure consistent data format
        if (posts.length > 0) {
          const now = new Date()
          const transformedPosts = posts
            .map(post => this.transformPost(post))
            .filter(post => !post.scheduled_for || new Date(post.scheduled_for) <= now)
          this.posts = transformedPosts;

          // Update the last post ID
          this.lastPostId = this.mostRecentPostId;
        }

        this.suggestedUsers = suggestedUsers;

        console.log('Feed previews loaded with', this.posts.length, 'posts. Last post ID:', this.lastPostId);
      } catch (error) {
        console.error('Error fetching feed previews:', error);
        this.error = 'Failed to load feed previews. Please try again.';
      } finally {
        this.loading = false;
      }
    },

    // New method to fetch preview videos for discovery feed
    async fetchPreviewVideos() {
      try {
        const response = await axiosInstance.get('/feed/previews');
        
        let posts = [];
        if (response.data.posts) {
          posts = response.data.posts;
        } else if (Array.isArray(response.data)) {
          posts = response.data;
        } else if (response.data.data && Array.isArray(response.data.data)) {
          posts = response.data.data;
        }

        // Transform posts and mark them as preview videos
        if (posts.length > 0) {
          const now = new Date()
          const transformedPosts = posts
            .map(post => ({
              ...this.transformPost(post),
              isPreviewVideo: true // Mark as preview video for discovery
            }))
            .filter(post => !post.scheduled_for || new Date(post.scheduled_for) <= now)
          
          return transformedPosts;
        }

        return [];
      } catch (error) {
        console.error('Error fetching preview videos:', error);
        return [];
      }
    },

    // Method to merge preview videos randomly into the main feed
    mergePreviewVideosIntoFeed(mainPosts, previewVideos) {
      if (!previewVideos || previewVideos.length === 0) {
        return mainPosts;
      }

      const mergedFeed = [...mainPosts];
      const previewVideosCopy = [...previewVideos];

      // Insert preview videos randomly into the feed
      // Insert one preview video for every 3-5 regular posts
      const insertInterval = Math.floor(Math.random() * 3) + 3; // Random interval between 3-5
      
      for (let i = 0; i < mergedFeed.length && previewVideosCopy.length > 0; i += insertInterval) {
        if (previewVideosCopy.length > 0) {
          const randomIndex = Math.floor(Math.random() * previewVideosCopy.length);
          const previewVideo = previewVideosCopy.splice(randomIndex, 1)[0];
          
          // Insert the preview video at this position
          mergedFeed.splice(i, 0, previewVideo);
        }
      }

      return mergedFeed;
    },

    async fetchNewPosts() {
      if (!this.lastPostId || this.isLoadingNewPosts) return;

      this.isLoadingNewPosts = true;

      try {
        console.log('Fetching new posts since ID:', this.lastPostId);
        // Use axiosInstance instead of axios
        const response = await axiosInstance.get('/feed/new-posts', {
          params: { last_post_id: this.lastPostId }
        });

        // Debug the response structure
        console.log('New posts API response:', response.data);

        // Handle different response structures
        let newPosts = [];

        if (response.data.posts) {
          // Standard response format
          newPosts = response.data.posts;
        } else if (Array.isArray(response.data)) {
          // Array response format
          newPosts = response.data;
        } else if (response.data.data && Array.isArray(response.data.data)) {
          // Paginated response format
          newPosts = response.data.data;
        } else {
          console.error('Unexpected API response format for new posts:', response.data);
        }

        // Transform and add new posts to the beginning of the array
        if (newPosts.length > 0) {
          const now = new Date()
          const transformedPosts = newPosts
            .map(post => this.transformPost(post))
            .filter(post => !post.scheduled_for || new Date(post.scheduled_for) <= now)
          console.log('Received', transformedPosts.length, 'new posts');
          this.posts = [...transformedPosts, ...this.posts];

          // Update the last post ID
          this.lastPostId = this.mostRecentPostId;
        } else {
          console.log('No new posts received');
        }

        // Reset the new posts indicator
        this.hasNewPosts = false;
        this.newPostsCount = 0;
        this.newPostsInfo = [];

      } catch (error) {
        console.error('Error fetching new posts:', error);
      } finally {
        this.isLoadingNewPosts = false;
      }
    },

    setupWebSocketListeners() {
      const wsService = useWebSocketService();

      // Use initialize() instead of init()
      wsService.initialize();

      // Add event listeners for post events
      this.setupPostCreatedListener(wsService);
      this.setupPostUpdatedListener(wsService);
      this.setupPostDeletedListener(wsService);
    },

    setupPostCreatedListener(wsService) {
      wsService.on('PostCreated', (data) => {
        if (data && data.type === 'new_post_available') {
          console.log('New post notification received:', data);

          // Check if this post is already in our list
          const existingPost = this.posts.find(post => post.id === data.post_id);
          if (existingPost) {
            console.log('Post already in feed, skipping notification');
            return;
          }

          // Check if this notification is already in our list
          const existingNotification = this.newPostsInfo.find(info => info.id === data.post_id);
          if (existingNotification) {
            console.log('Notification already shown, skipping');
            return;
          }

          // Show the new post indicator
          this.hasNewPosts = true;
          this.newPostsCount++;

          // Store info about the new post for the notification
          this.newPostsInfo.push({
            id: data.post_id,
            userId: data.user_id,
            username: data.username,
            avatar: data.avatar || '/default-avatar.png', // Provide a default avatar
            timestamp: data.timestamp,
            hasMedia: data.has_media
          });
        }
      });
    },

    setupPostUpdatedListener(wsService) {
      wsService.on('PostUpdated', (data) => {
        if (data && data.type === 'post_updated') {
          console.log('Post update notification received:', data);

          // Find the post in our list
          const postIndex = this.posts.findIndex(post => post.id === data.post_id);
          if (postIndex !== -1) {
            // Mark this post as needing refresh
            this.posts[postIndex] = {
              ...this.posts[postIndex],
              needsRefresh: true,
              lastUpdated: data.timestamp
            };

            console.log('Post marked for refresh:', data.post_id);
          }
        }
      });
    },

    setupPostDeletedListener(wsService) {
      wsService.on('PostDeleted', (postId) => {
        console.log('Post deletion notification received:', postId);

        // Remove the deleted post if it exists in our feed
        const initialCount = this.posts.length;
        this.posts = this.posts.filter(post => post.id !== postId);

        // Also remove from new posts notifications if present
        this.newPostsInfo = this.newPostsInfo.filter(info => info.id !== postId);
        if (this.newPostsInfo.length < this.newPostsCount) {
          this.newPostsCount = this.newPostsInfo.length;
          if (this.newPostsCount === 0) {
            this.hasNewPosts = false;
          }
        }

        console.log(`Removed post ${postId}. Posts before: ${initialCount}, after: ${this.posts.length}`);
      });
    },

    // Refresh a specific post that has been updated
    async refreshPost(postId) {
      try {
        console.log('Refreshing post:', postId);
        // Use axiosInstance instead of axios
        const response = await axiosInstance.get(`/posts/${postId}`);

        // Debug the response structure
        console.log('Post refresh API response:', response.data);

        // Handle different response structures
        let post = null;

        if (response.data.post) {
          post = response.data.post;
        } else if (response.data.id) {
          post = response.data;
        } else {
          console.error('Unexpected API response format for post refresh:', response.data);
          return;
        }

        // Find and update the post
        const index = this.posts.findIndex(p => p.id === postId);
        if (index !== -1) {
          // Transform the post data
          const updatedPost = this.transformPost(post);

          // Remove the needsRefresh flag
          updatedPost.needsRefresh = false;

          // Update the post in the array
          this.posts[index] = updatedPost;
          console.log('Post refreshed successfully:', postId);
        }
      } catch (error) {
        console.error('Error refreshing post:', error, postId);
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
        // Convert object to array if needed
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

    // Add a new post (used when the user creates a post)
    addNewPost(post) {
      if (!post) return;

      // Transform the post data
      const transformedPost = this.transformPost(post);

      // Add to the beginning of the posts array
      this.posts = [transformedPost, ...this.posts];

      // Update the last post ID
      if (transformedPost.id > this.lastPostId) {
        this.lastPostId = transformedPost.id;
      }

      console.log('New post added to feed:', transformedPost.id);
    },

    // Clear all posts (used for logout)
    clearPosts() {
      this.posts = [];
      this.suggestedUsers = [];
      this.hasNewPosts = false;
      this.newPostsCount = 0;
      this.lastPostId = 0;
      this.newPostsInfo = [];
      console.log('Feed store cleared');
    },

    // ADDED BACK FROM OLD STORE - These methods were missing in the new version

    async followUser(userId) {
      if (this.followingInProgress[userId]) return;

      // Find the user in suggestedUsers to check their role
      const user = this.suggestedUsers.find((u) => u.id === userId);
      if (!user || user.role !== 'creator') {
        return {
          success: false,
          error: 'Only creators can be followed.'
        };
      }

      this.followingInProgress[userId] = true;
      try {
        const response = await axiosInstance.post(`/users/${userId}/follow`);
        if (response.data.success) {
          // Update suggested users
          const userIndex = this.suggestedUsers.findIndex((user) => user.id === userId);
          if (userIndex !== -1) {
            this.suggestedUsers[userIndex].is_followed = true;
          }
          
          // Update followStore if it exists
          try {
            const { useFollowStore } = await import('./followStore');
            const followStore = useFollowStore();
            followStore.updateFollowingStatus(userId, true);
          } catch (error) {
            console.warn('FollowStore not available for update:', error);
          }
          
          return { success: true, message: "Successfully followed user" };
        } else {
          throw new Error(response.data.error || "Failed to follow user");
        }
      } catch (error) {
        console.error("Error following user:", error);
        return {
          success: false,
          error: error.response?.data?.error || "An error occurred while trying to follow the user",
        };
      } finally {
        this.followingInProgress[userId] = false;
      }
    },

    async unfollowUser(userId) {
      if (this.followingInProgress[userId]) return;

      this.followingInProgress[userId] = true;
      try {
        const response = await axiosInstance.post(`/users/${userId}/unfollow`);
        if (response.data.success) {
          // Update suggested users
          const userIndex = this.suggestedUsers.findIndex((user) => user.id === userId);
          if (userIndex !== -1) {
            this.suggestedUsers[userIndex].is_followed = false;
          }
          
          // Update followStore if it exists
          try {
            const { useFollowStore } = await import('./followStore');
            const followStore = useFollowStore();
            followStore.removeFromFollowing(userId);
          } catch (error) {
            console.warn('FollowStore not available for update:', error);
          }
          
          return { success: true, message: "Successfully unfollowed user" };
        } else {
          throw new Error(response.data.error || "Failed to unfollow user");
        }
      } catch (error) {
        console.error("Error unfollowing user:", error);
        return {
          success: false,
          error: error.response?.data?.error || "An error occurred while trying to unfollow the user",
        };
      } finally {
        this.followingInProgress[userId] = false;
      }
    },

    async likePost(postId) {
      try {
        const response = await axiosInstance.post(`/posts/${postId}/like`);
        if (response.data.success) {
          const index = this.posts.findIndex((post) => post.id === postId);
          if (index !== -1) {
            // Update the post stats
            if (this.posts[index].stats) {
              this.posts[index].stats.is_liked = true;
              this.posts[index].stats.total_likes++;
            } else {
              // For backward compatibility
              this.posts[index].is_liked = true;
              this.posts[index].likes_count++;
            }
          }
          return { success: true, message: "Post liked successfully" };
        } else {
          throw new Error(response.data.message || "Failed to like post");
        }
      } catch (error) {
        console.error("Error liking post:", error);
        return { success: false, error: error.message || "Failed to like the post" };
      }
    },

    async unlikePost(postId) {
      try {
        const response = await axiosInstance.delete(`/posts/${postId}/like`);
        if (response.data.success) {
          const index = this.posts.findIndex((post) => post.id === postId);
          if (index !== -1) {
            // Update the post stats
            if (this.posts[index].stats) {
              this.posts[index].stats.is_liked = false;
              this.posts[index].stats.total_likes--;
            } else {
              // For backward compatibility
              this.posts[index].is_liked = false;
              this.posts[index].likes_count--;
            }
          }
          return { success: true, message: "Post unliked successfully" };
        } else {
          throw new Error(response.data.message || "Failed to unlike post");
        }
      } catch (error) {
        console.error("Error unliking post:", error);
        return { success: false, error: error.message || "Failed to unlike the post" };
      }
    },

    async likeMedia(mediaId) {
      try {
        const response = await axiosInstance.post(`/media/${mediaId}/like`);
        if (response.data.success) {
          // Update the media item in the posts array
          this.posts.forEach((post) => {
            if (post.media) {
              const mediaIndex = post.media.findIndex((media) => media.id === mediaId);
              if (mediaIndex !== -1) {
                post.media[mediaIndex].is_liked = true;
                post.media[mediaIndex].likes_count++;
              }
            }
          });
          return { success: true, message: "Media liked successfully" };
        } else {
          throw new Error(response.data.message || "Failed to like media");
        }
      } catch (error) {
        console.error("Error liking media:", error);
        return { success: false, error: error.message || "Failed to like the media" };
      }
    },

    async unlikeMedia(mediaId) {
      try {
        const response = await axiosInstance.delete(`/media/${mediaId}/like`);
        if (response.data.success) {
          // Update the media item in the posts array
          this.posts.forEach((post) => {
            if (post.media) {
              const mediaIndex = post.media.findIndex((media) => media.id === mediaId);
              if (mediaIndex !== -1) {
                post.media[mediaIndex].is_liked = false;
                post.media[mediaIndex].likes_count--;
              }
            }
          });
          return { success: true, message: "Media unliked successfully" };
        } else {
          throw new Error(response.data.message || "Failed to unlike media");
        }
      } catch (error) {
        console.error("Error unliking media:", error);
        return { success: false, error: error.message || "Failed to unlike the media" };
      }
    },

    async sendTip({ amount, receiverId, tippableType, tippableId }) {
      try {
        const response = await axiosInstance.post("/tip", {
          amount,
          receiver_id: receiverId,
          tippable_type: tippableType,
          tippable_id: tippableId,
        });

        if (response.data.success) {
          // Update the post in the store
          const postIndex = this.posts.findIndex((post) => post.id === tippableId);
          if (postIndex !== -1) {
            if (this.posts[postIndex].stats) {
              this.posts[postIndex].stats.total_tips += amount;
            } else {
              // For backward compatibility
              this.posts[postIndex].total_tips += amount;
            }
          }

          return {
            success: true,
            message: response.data.message,
            data: response.data.data,
          };
        } else {
          throw new Error(response.data.message || "Failed to send tip");
        }
      } catch (error) {
        console.error("Error sending tip:", error);
        return {
          success: false,
          message: error.response?.data?.message || "Failed to send the tip",
          errors: error.response?.data?.errors,
          error: error.response?.data?.error,
        };
      }
    },

    async fetchUserByUsername(username) {
      try {
        const response = await axiosInstance.get(`/users/${username}`);
        console.log('🔍 fetchUserByUsername API Response:', response.data);
        console.log('🔍 User is_muted:', response.data.is_muted);
        console.log('🔍 User is_blocked:', response.data.is_blocked);
        if (response.data) {
          return response.data;
        } else {
          throw new Error("User not found");
        }
      } catch (error) {
        console.error("Error fetching user data:", error);
        throw error;
      }
    },

    // Method to clear feed (combined with the new clearPosts method)
    clearFeed() {
      this.clearPosts();
      this.page = 1;
    },

    async fetchSinglePost(postId) {
      try {
        console.log('Fetching single post:', postId);
        const response = await axiosInstance.get(`/posts/${postId}`);
        
        // Debug the response structure
        console.log('Single post API response:', response.data);

        if (!response.data) {
          throw new Error('Post not found');
        }

        // Transform the post data to ensure consistent format
        const transformedPost = this.transformPost(response.data);
        
        return transformedPost;
      } catch (error) {
        console.error('Error fetching single post:', error);
        throw error;
      }
    },

    async updatePost(postId, postData) {
      try {
        const response = await axiosInstance.put(`/posts/${postId}`, postData)
        
        // Update the post in the store
        const updatedPost = this.transformPost(response.data)
        const index = this.posts.findIndex(p => p.id === postId)
        if (index !== -1) {
          this.posts[index] = updatedPost
        }
        
        return { success: true, post: updatedPost }
      } catch (error) {
        console.error('Error updating post:', error)
        return { 
          success: false, 
          error: error.response?.data?.message || 'Failed to update post'
        }
      }
    },





    async addComment(postId, commentText) {
      try {
        const response = await axiosInstance.post(`/posts/${postId}/comments`, { comment: commentText });
        // Find and update the post's comment count in the store
        const post = this.posts.find(p => p.id === postId);
        if (post && post.stats) {
          post.stats.total_comments = (post.stats.total_comments || 0) + 1;
        }
        return { success: true, message: response.data.message || 'Comment added successfully', data: response.data };
      } catch (error) {
        let message = 'Failed to add comment.';
        if (error.response && error.response.data && error.response.data.message) {
          message = error.response.data.message;
        }
        return { success: false, error: message };
      }
    },

    async bookmarkPost(postId) {
      try {
        console.log('🔖 Bookmarking post:', postId);
        console.log('🔖 Post ID type:', typeof postId);
        console.log('🔖 Full URL:', `/bookmarks/posts/${postId}`);
        
        const response = await axiosInstance.post(`/bookmarks/posts/${postId}`);
        console.log('🔖 Bookmark response:', response.data);
        
        if (response.data.success) {
          const index = this.posts.findIndex((post) => post.id === postId);
          if (index !== -1) {
            // Update the post stats
            if (this.posts[index].stats) {
              this.posts[index].stats.is_bookmarked = true;
              this.posts[index].stats.total_bookmarks = (this.posts[index].stats.total_bookmarks || 0) + 1;
            }
          }
          return { success: true, message: "Post bookmarked successfully" };
        } else {
          throw new Error(response.data.message || "Failed to bookmark post");
        }
      } catch (error) {
        console.error("Error bookmarking post:", error);
        console.error("Error response:", error.response?.data);
        return { success: false, error: error.response?.data?.message || "Failed to bookmark the post" };
      }
    },

    async unbookmarkPost(postId) {
      try {
        const response = await axiosInstance.delete(`/bookmarks/posts/${postId}`);
        if (response.data.success) {
          const index = this.posts.findIndex((post) => post.id === postId);
          if (index !== -1) {
            // Update the post stats
            if (this.posts[index].stats) {
              this.posts[index].stats.is_bookmarked = false;
              this.posts[index].stats.total_bookmarks = Math.max((this.posts[index].stats.total_bookmarks || 0) - 1, 0);
            }
          }
          return { success: true, message: "Post unbookmarked successfully" };
        } else {
          throw new Error(response.data.message || "Failed to unbookmark post");
        }
      } catch (error) {
        console.error("Error unbookmarking post:", error);
        return { success: false, error: error.response?.data?.message || "Failed to unbookmark the post" };
      }
    },
  }
});