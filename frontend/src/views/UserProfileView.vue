<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-light dark:border-primary-dark mx-auto mb-4"></div>
        <p class="text-text-light-secondary dark:text-text-dark-secondary">{{ t('loading_user_profile') }}</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center max-w-md mx-auto px-4">
        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-4">
          <i class="ri-error-warning-line text-2xl text-red-500"></i>
        </div>
        <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
          {{ t('error_loading_profile') }}
        </h3>
        <p class="text-text-light-secondary dark:text-text-dark-secondary mb-6">
          {{ error }}
        </p>
        <button 
          @click="fetchUserProfile" 
          class="px-6 py-3 bg-primary-light dark:bg-primary-dark text-white rounded-full hover:bg-primary-dark dark:hover:bg-primary-light transition-colors font-medium"
        >
          <i class="ri-refresh-line mr-2"></i>
          {{ t('try_again') }}
        </button>
      </div>
    </div>

    <!-- Profile Content -->
    <div v-else class="max-w-3xl mx-auto">
      <!-- Profile Header -->
      <div class="bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark" style="overflow: visible;">
        <UserProfileHeader
          :cover-photo="user.cover_photo || ''"
          :avatar-url="user.avatar || ''"
          :username="user.name || ''"
          :handle="user.handle || ''"
          :status="userStatus"
          :stats="userStats"
          :bio="user.bio || ''"
          :role="user.role || 'user'"
          :social-links="user.social_links || {}"
          :last-seen-at="user.last_seen_at || ''"
          :can-be-followed="user.can_be_followed || false"
          :is-following="!!user.is_following || !!user.followed_by_current_user"
          :user="user"
          @follow="handleFollow"
          @unfollow="handleUnfollow"
          @show-tip-modal="handleShowTipModal"
          @show-add-to-list-modal="handleShowAddToListModal"
          @show-earnings-modal="handleShowEarningsModal"
          @mute-user="handleMuteUser"
          @block-user="handleBlockUser"
          @report-user="handleReportUser"
        />
      </div>

      <!-- Profile Tabs -->
      <div class="bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark sticky top-0 z-20 backdrop-blur-lg">
        <ProfileTabs v-model="activeTab" />
      </div>

      <!-- Timeline Search -->
      <div class="bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
        <TimelineSearch />
      </div>

      <!-- Content Sections -->
      <div class="bg-background-light dark:bg-background-dark">
        <!-- Posts Section -->
        <div v-if="activeTab === 'posts'" class="divide-y divide-border-light dark:divide-border-dark">
          <div v-if="!user.posts || user.posts.length === 0" class="flex items-center justify-center py-16">
            <div class="text-center max-w-md mx-auto px-4">
              <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ri-file-list-line text-2xl text-gray-400"></i>
              </div>
              <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                {{ t('no_posts_yet') }}
              </h3>
              <p class="text-text-light-secondary dark:text-text-dark-secondary">
                {{ t('this_user_hasnt_posted_anything_yet') }}
              </p>
            </div>
          </div>
          <div v-else>
            <UserPost 
              v-for="post in user.posts" 
              :key="post.id" 
              :post="post"
              @post-action="handlePostAction"
              @like="handleLike"
              @comment="handleComment"
            />
          </div>
        </div>

        <!-- Media Section -->
        <div v-else-if="activeTab === 'media'" class="p-4">
          <div v-if="!user.media || user.media.length === 0" class="flex items-center justify-center py-16">
            <div class="text-center max-w-md mx-auto px-4">
              <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ri-image-line text-2xl text-gray-400"></i>
              </div>
              <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                {{ t('no_media_yet') }}
              </h3>
              <p class="text-text-light-secondary dark:text-text-dark-secondary">
                {{ t('this_user_hasnt_uploaded_any_media_yet') }}
              </p>
            </div>
          </div>
          <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div 
              v-for="media in user.media" 
              :key="media.id"
              class="aspect-square rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800"
            >
              <img 
                :src="media.url" 
                :alt="media.alt || 'Media content'"
                class="w-full h-full object-cover"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tip Modal -->
    <TipModal
      :is-open="showTipModal"
      :recipient="user"
      @close="showTipModal = false"
      @tip="handleTip"
    />

    <!-- Add to List Modal -->
    <AddToListModal
      :is-open="showAddToListModal"
      :user="user"
      @close="showAddToListModal = false"
    />

    <!-- User Earnings Modal -->
    <UserEarningsModal
      :is-open="showEarningsModal"
      :user-id="user.id"
      @close="showEarningsModal = false"
    />

    <!-- Report Modal -->
    <ReportModal
      :is-open="showReportModal"
      :content-id="user.id"
      content-type="user"
      @close="showReportModal = false"
    />

    <!-- Comment Modal -->
    <CommentModal
      v-if="selectedPostForComment"
      :is-open="isCommentModalOpen"
      :post="selectedPostForComment"
      :user-has-permission="selectedPostForComment.user_has_permission"
      :current-user="authStore.user"
      @close="closeCommentModal"
      @commentSubmitted="handleCommentSubmitted"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { useFeedStore } from '@/stores/feedStore'
import { useAuthStore } from '@/stores/authStore'
import { useSettingsStore } from '@/stores/settingsStore'
import { useCreatorSettingsStore } from '@/stores/creatorSettingsStore'
import { useCommentStore } from '@/stores/commentStore'
import { useListStore } from '@/stores/listStore'
import { useToast } from 'vue-toastification'
import UserProfileHeader from '@/components/user/UserProfileHeader.vue'
import ProfileTabs from '@/components/user/ProfileTabs.vue'
import TimelineSearch from '@/components/user/TimelineSearch.vue'
import UserPost from '@/components/user/UserPost.vue'
import TipModal from '@/components/modals/TipModal.vue'
import AddToListModal from '@/components/modals/AddToListModal.vue'
import UserEarningsModal from '@/components/user/UserEarningsModal.vue'
import ReportModal from '@/components/modals/ReportModal.vue'
import CommentModal from '@/components/modals/CommentModal.vue'
import axiosInstance from '@/axios'

const { t } = useI18n()
const route = useRoute()
const feedStore = useFeedStore()
const authStore = useAuthStore()
const settingsStore = useSettingsStore()
const creatorSettingsStore = useCreatorSettingsStore()
const commentStore = useCommentStore()
const listStore = useListStore()
const toast = useToast()

const loading = ref(true)
const error = ref(null)
const user = ref({})
const activeTab = ref('posts')
const showTipModal = ref(false)
const showAddToListModal = ref(false)
const showEarningsModal = ref(false)
const showReportModal = ref(false)
const isCommentModalOpen = ref(false)
const selectedPostForComment = ref(null)

// Computed properties
const userStatus = computed(() => {
  if (!user.value.last_seen_at) return t('offline')
  const lastSeen = new Date(user.value.last_seen_at)
  const now = new Date()
  const diffMinutes = Math.floor((now - lastSeen) / (1000 * 60))
  
  if (diffMinutes < 5) return t('active_now')
  if (diffMinutes < 60) return t('active_minutes_ago', { minutes: diffMinutes })
  if (diffMinutes < 1440) return t('active_hours_ago', { hours: Math.floor(diffMinutes / 60) })
  return t('active_days_ago', { days: Math.floor(diffMinutes / 1440) })
})

const userStats = computed(() => ({
  likes: user.value.total_likes_received || 0,
  followers: user.value.total_followers || 0,
  posts: user.value.posts?.length || 0,
  media: (user.value.total_video_uploads || 0) + (user.value.total_image_uploads || 0)
}))

// Methods
const fetchUserProfile = async () => {
  const username = route.params.username
  try {
    loading.value = true
    error.value = null
    user.value = await feedStore.fetchUserByUsername(username)

    // Debug: Log the user data to see what we're getting
    console.log('🔍 User data from API:', user.value)
    console.log('🔍 Mute/Block status:', {
      is_muted: user.value.is_muted,
      is_blocked: user.value.is_blocked
    })
    console.log('🔍 Follow status:', {
      is_following: user.value.is_following,
      followed_by_current_user: user.value.followed_by_current_user,
      can_be_followed: user.value.can_be_followed
    })

    // Enhanced logging for posts data
    if (user.value.posts) {
      console.log('🔍 Posts before processing:', user.value.posts)
      console.log('🔍 Number of posts:', user.value.posts.length)
      
      user.value.posts = user.value.posts.map((post, index) => {
        console.log(`🔍 Processing post ${index + 1}:`, {
          id: post.id,
          user_has_permission: post.user_has_permission,
          required_permissions: post.required_permissions,
          media: post.media,
          media_previews: post.media_previews,
          content: post.content?.substring(0, 50) + '...',
          user: post.user
        })
        
        const processedPost = {
          ...post,
          user_has_permission: post.user_has_permission ?? false,
          media: post.media || [],
          media_previews: post.media_previews || [],
          required_permissions: post.required_permissions || []
        }
        
        console.log(`🔍 Processed post ${index + 1}:`, {
          id: processedPost.id,
          user_has_permission: processedPost.user_has_permission,
          required_permissions: processedPost.required_permissions,
          media_count: processedPost.media.length,
          media_previews_count: processedPost.media_previews.length
        })
        
        return processedPost
      })
      
      console.log('🔍 Posts after processing:', user.value.posts)
    } else {
      console.log('🔍 No posts found in user data')
    }
  } catch (err) {
    console.error('Error fetching user data:', err)
    error.value = t('failed_to_load_user_profile')
  } finally {
    loading.value = false
  }
}

const handleFollow = async () => {
  console.log('🔍 Follow button clicked for user:', user.value.id)
  
  // Check if user's email is verified using settingsStore like in UserSuggestions
  if (!settingsStore.account.is_email_verified) {
    toast.error(t('verify_email_before_following'))
    settingsStore.triggerEmailVerificationModal()
    return
  }

  try {
    console.log('🔍 Calling feedStore.followUser...')
    const result = await feedStore.followUser(user.value.id)
    console.log('🔍 Follow result:', result)
    
    if (result.success) {
      // Update the user state to reflect the follow action
      user.value.is_following = true
      user.value.followed_by_current_user = true
      console.log('🔍 Updated user follow status:', {
        is_following: user.value.is_following,
        followed_by_current_user: user.value.followed_by_current_user
      })
      toast.success(result.message || t('user_followed_successfully'))
    } else {
      toast.error(result.error || t('failed_to_follow_user'))
    }
  } catch (error) {
    console.error('Error following user:', error)
    toast.error(t('failed_to_follow_user'))
  }
}

const handleUnfollow = async () => {
  console.log('🔍 Unfollow button clicked for user:', user.value.id)
  
  try {
    console.log('🔍 Calling feedStore.unfollowUser...')
    const result = await feedStore.unfollowUser(user.value.id)
    console.log('🔍 Unfollow result:', result)
    
    if (result.success) {
      // Update the user state to reflect the unfollow action
      user.value.is_following = false
      user.value.followed_by_current_user = false
      console.log('🔍 Updated user follow status:', {
        is_following: user.value.is_following,
        followed_by_current_user: user.value.followed_by_current_user
      })
      toast.success(result.message || t('user_unfollowed_successfully'))
    } else {
      toast.error(result.error || t('failed_to_unfollow_user'))
    }
  } catch (error) {
    console.error('Error unfollowing user:', error)
    toast.error(t('failed_to_unfollow_user'))
  }
}

const handlePostAction = async ({ postId, action }) => {
  try {
    switch (action) {
      case 'pin':
        await axiosInstance.post(`/posts/${postId}/pin`);
        // Refetch posts to get correct order with pinned posts first
        await fetchUserProfile();
        toast.success(t('post_pinned_successfully'));
        break;
        
      case 'unpin':
        await axiosInstance.delete(`/posts/${postId}/pin`);
        // Refetch posts to get correct order with pinned posts first
        await fetchUserProfile();
        toast.success(t('post_unpinned_successfully'));
        break;
        
      case 'copyLink':
        const post = user.value.posts.find(p => p.id === postId);
        if (post) {
          const postUrl = `${window.location.origin}/${user.value.handle}/posts/${postId}`;
          await navigator.clipboard.writeText(postUrl);
          toast.success(t('post_link_copied'));
        }
        break;
        
      case 'delete':
        if (confirm(t('confirm_delete_post'))) {
          await axiosInstance.delete(`/posts/${postId}`);
          // Remove the post from the local state
          const postIndex = user.value.posts.findIndex(p => p.id === postId);
          if (postIndex > -1) {
            user.value.posts.splice(postIndex, 1);
          }
          toast.success(t('post_deleted_successfully'));
        }
        break;
        
      case 'refresh':
        // Refresh the user profile data after unlock/subscribe/follow
        await fetchUserProfile();
        break;
        
      default:
        console.log('Unhandled post action:', action);
    }
  } catch (error) {
    console.error('Error handling post action:', error);
    toast.error(t('failed_to_process_action'));
  }
}

// Handle like action from UserPost component
const handleLike = async ({ postId, liked }) => {
  try {
    console.log('🔍 handleLike called:', { postId, liked });
    
    // Make the API call directly since feedStore methods work with feed posts array
    const response = liked 
      ? await axiosInstance.post(`/posts/${postId}/like`)
      : await axiosInstance.delete(`/posts/${postId}/like`);

    console.log('🔍 API response:', response.data);

    if (response.data.success) {
      // Update the post in the local state
      const postIndex = user.value.posts.findIndex(p => p.id === postId);
      console.log('🔍 Found post at index:', postIndex);
      
      if (postIndex > -1) {
        const post = user.value.posts[postIndex];
        console.log('🔍 Post before update:', {
          id: post.id,
          likes_count: post.likes_count || post.stats?.total_likes,
          is_liked: post.is_liked || post.stats?.is_liked,
          has_stats: !!post.stats
        });
        
        if (liked) {
          // Handle both data structures
          if (user.value.posts[postIndex].stats) {
            user.value.posts[postIndex].stats.total_likes++;
            user.value.posts[postIndex].stats.is_liked = true;
          } else {
            user.value.posts[postIndex].likes_count++;
            user.value.posts[postIndex].is_liked = true;
          }
        } else {
          // Handle both data structures
          if (user.value.posts[postIndex].stats) {
            user.value.posts[postIndex].stats.total_likes--;
            user.value.posts[postIndex].stats.is_liked = false;
          } else {
            user.value.posts[postIndex].likes_count--;
            user.value.posts[postIndex].is_liked = false;
          }
        }
        
        console.log('🔍 Post after update:', {
          id: user.value.posts[postIndex].id,
          likes_count: user.value.posts[postIndex].likes_count || user.value.posts[postIndex].stats?.total_likes,
          is_liked: user.value.posts[postIndex].is_liked || user.value.posts[postIndex].stats?.is_liked,
          has_stats: !!user.value.posts[postIndex].stats
        });
      }
      toast.success(response.data.message);
    } else {
      toast.error(response.data.message || 'Failed to process like');
    }
  } catch (error) {
    console.error('Error handling like:', error);
    toast.error('Failed to process like');
  }
}

// Handle comment action from UserPost component
const handleComment = async (postId) => {
  try {
    // Find the post and open comment modal
    const post = user.value.posts.find(p => p.id === postId);
    if (post) {
      selectedPostForComment.value = post;
      isCommentModalOpen.value = true;
    }
  } catch (error) {
    console.error('Error handling comment:', error);
    toast.error('Failed to process comment');
  }
}

// Handle comment modal close
const closeCommentModal = () => {
  isCommentModalOpen.value = false;
  selectedPostForComment.value = null;
}

// Handle comment submitted
const handleCommentSubmitted = async (commentText) => {
  try {
    if (!selectedPostForComment.value) return;
    
    const result = await commentStore.addComment(selectedPostForComment.value.id, commentText);
    
    if (result.success) {
      toast.success(result.message || 'Comment posted!');
      // Update the post's comment count
      const postIndex = user.value.posts.findIndex(p => p.id === selectedPostForComment.value.id);
      if (postIndex > -1) {
        user.value.posts[postIndex].comments_count++;
      }
      closeCommentModal();
    } else {
      toast.error(result.error || 'Failed to post comment');
    }
  } catch (error) {
    console.error('Error posting comment:', error);
    toast.error('Failed to post comment');
  }
}

const handleShowTipModal = () => {
  // Show tip modal for the current user
  showTipModal.value = true;
}

const handleShowAddToListModal = () => {
  // Show add to list modal for the current user
  showAddToListModal.value = true;
}

const handleShowEarningsModal = () => {
  // Show earnings modal for the current user
  showEarningsModal.value = true;
}

const handleMuteUser = async () => {
  try {
    const isCurrentlyMuted = user.value.is_muted
    
    if (isCurrentlyMuted) {
      // Unmute user - remove from muted list
      await listStore.removeMemberFromList('Muted Accounts', user.value.id)
      toast.success(t('user_unmuted_successfully'))
    } else {
      // Mute user - add to muted list
      await listStore.addMemberToList('Muted Accounts', user.value.id)
      toast.success(t('user_muted_successfully'))
    }
    
    // Refresh user profile to get updated mute status
    await fetchUserProfile()
  } catch (error) {
    console.error('Error muting/unmuting user:', error)
    toast.error(t('failed_to_update_mute_status'))
  }
}

const handleBlockUser = async () => {
  try {
    const isCurrentlyBlocked = user.value.is_blocked
    
    if (isCurrentlyBlocked) {
      // Unblock user - remove from blocked list
      await listStore.removeMemberFromList('Blocked Accounts', user.value.id)
      toast.success(t('user_unblocked_successfully'))
    } else {
      // Block user - add to blocked list
      await listStore.addMemberToList('Blocked Accounts', user.value.id)
      toast.success(t('user_blocked_successfully'))
    }
    
    // Refresh user profile to get updated block status
    await fetchUserProfile()
  } catch (error) {
    console.error('Error blocking/unblocking user:', error)
    toast.error(t('failed_to_update_block_status'))
  }
}

const handleReportUser = () => {
  showReportModal.value = true
}

const handleTip = async (tipData) => {
  try {
    // TODO: Implement actual tip processing
    console.log('Processing tip:', tipData);
    console.log('Recipient:', user.value);
    
    // For now, just show a success message
    toast.success(`Tip of $${tipData.amount} sent successfully!`);
    showTipModal.value = false;
  } catch (error) {
    console.error('Error processing tip:', error);
    toast.error('Failed to process tip');
  }
}

// Watch for route changes
watch(() => route.params.username, () => {
  if (route.params.username) {
    fetchUserProfile()
  }
})

// Lifecycle
onMounted(() => {
  fetchUserProfile()
})
</script>

<style scoped>
/* Custom animations for smooth transitions */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out;
}

/* Enhanced glassmorphism effects */
.backdrop-blur-xl {
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>

