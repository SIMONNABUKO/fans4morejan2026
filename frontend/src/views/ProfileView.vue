<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-[60vh]">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-light dark:border-primary-dark mx-auto mb-4"></div>
        <p class="text-text-light-secondary dark:text-text-dark-secondary">{{ t('loading_profile') }}</p>
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
          @click="fetchProfile" 
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
        <ProfileHeader
          :avatar-url="user.avatar || ''"
          :cover-photo="user.cover_photo || ''"
          :username="user.name || ''"
          :handle="user.handle || ''"
          :status="userStatus"
          :stats="userStats"
          :bio="user.bio || ''"
          :role="user.role || 'user'"
          :social-links="user.social_links || {}"
          :last-seen-at="user.last_seen_at || ''"
          :can-be-followed="user.can_be_followed || false"
          :bio_color="user.bio_color || '#222222'"
          :bio_font="user.bio_font || 'inherit'"
          :view-as-follower="isOwnProfile ? viewAsFollower : false"
          :is-own-profile="isOwnProfile"
          @update:profile="handleProfileUpdate"
          @toggle-follower-view="handleToggleFollowerView"
        />
      </div>

      <!-- Subscription Tiers -->
      <div v-if="isOwnProfile" class="bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
        <SubscriptionTiers />
      </div>

      <!-- Follower View Mode Indicator -->
      <div v-if="isOwnProfile && viewAsFollower" class="bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center">
              <i class="ri-eye-line text-blue-600 dark:text-blue-400 text-sm"></i>
            </div>
            <div>
              <p class="text-blue-800 dark:text-blue-200 font-medium text-sm">{{ t('viewing_as_follower') }}</p>
              <p class="text-blue-600 dark:text-blue-400 text-xs">{{ t('see_how_your_profile_looks_to_followers') }}</p>
            </div>
          </div>
          <button 
            @click="handleToggleFollowerView(false)" 
            class="px-4 py-2 bg-blue-600 dark:bg-blue-500 text-white rounded-full hover:bg-blue-700 dark:hover:bg-blue-600 transition-colors text-sm font-medium"
          >
            {{ t('exit_follower_view') }}
          </button>
        </div>
      </div>

      <!-- Profile Tabs -->
      <div v-if="isOwnProfile" class="bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark sticky top-0 z-20 backdrop-blur-lg">
        <ProfileTabs v-model="activeTab" />
      </div>

      <!-- Timeline Search -->
      <div v-if="isOwnProfile" class="bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
        <TimelineSearch />
      </div>

      <!-- Content Sections -->
      <div class="bg-background-light dark:bg-background-dark">
        <!-- Posts Section -->
        <div v-if="activeTab === 'posts'" class="divide-y divide-border-light dark:divide-border-dark">
          <div v-if="user.posts.length === 0" class="flex items-center justify-center py-16">
            <div class="text-center max-w-md mx-auto px-4">
              <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ri-file-list-line text-2xl text-gray-400"></i>
              </div>
              <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                {{ t('no_posts_yet') }}
              </h3>
              <p class="text-text-light-secondary dark:text-text-dark-secondary">
                {{ t('start_sharing_to_see_your_posts_here') }}
              </p>
            </div>
          </div>
          <UserPost 
            v-for="post in user.posts" 
            :key="post.id" 
            :post="post"
            :view-as-follower="isOwnProfile ? viewAsFollower : false"
            :is-own-profile="isOwnProfile"
            @edit="handleEditPost"
            @post-action="handlePostAction"
          />
        </div>

        <!-- Media Section -->
        <div v-else-if="activeTab === 'media'" class="p-4">
          <div v-if="user.media.length === 0" class="flex items-center justify-center py-16">
            <div class="text-center max-w-md mx-auto px-4">
              <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ri-image-line text-2xl text-gray-400"></i>
              </div>
              <h3 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary mb-2">
                {{ t('no_media_yet') }}
              </h3>
              <p class="text-text-light-secondary dark:text-text-dark-secondary">
                {{ t('upload_photos_and_videos_to_see_them_here') }}
              </p>
            </div>
          </div>
          <div v-else class="grid grid-cols-3 gap-2">
            <div 
              v-for="media in user.media" 
              :key="media.id" 
              class="aspect-square overflow-hidden rounded-lg bg-surface-light dark:bg-surface-dark group cursor-pointer"
            >
              <img 
                :src="media.url" 
                :alt="media.type" 
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
              />
              <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Post Edit Modal -->
    <MobilePostModal
      v-if="selectedPost"
      :is-open="showEditModal"
      :post="selectedPost"
      :edit-mode="true"
      @close="closeEditModal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useUploadStore } from '@/stores/uploadStore'
import { useToast } from 'vue-toastification'
import ProfileHeader from '@/components/user/ProfileHeader.vue'
import ProfileTabs from '@/components/user/ProfileTabs.vue'
import SubscriptionTiers from '@/components/user/SubscriptionTiers.vue'
import TimelineSearch from '@/components/user/TimelineSearch.vue'
import UserPost from '@/components/user/UserPost.vue'
import MobilePostModal from '@/components/posts/MobilePostModal.vue'
import axiosInstance from '@/axios'
import { useI18n } from 'vue-i18n'

// Define props for the component
const props = defineProps({
  username: {
    type: String,
    required: true
  }
})

const { t } = useI18n()
const authStore = useAuthStore()
const uploadStore = useUploadStore()
const toast = useToast()
const activeTab = ref('posts')
const showEditModal = ref(false)
const selectedPost = ref(null)
const loading = ref(true)
const error = ref(null)
const viewAsFollower = ref(false)

// Local profile user state
const profileUser = ref(null)

const fetchProfile = async () => {
  loading.value = true
  error.value = null
  try {
    // Check if viewing own profile or another user's profile
    const isOwnProfile = props.username === authStore.user?.username
    
    if (isOwnProfile) {
      // Fetch current user data
      await authStore.fetchCurrentUser()
      console.log('🔍 Current user from authStore:', authStore.user)
      
      // Fetch current user's posts with viewAsFollower parameter
      const postsResponse = await axiosInstance.get('/posts', {
        params: {
          view_as_follower: viewAsFollower.value
        }
      })
      const userPosts = postsResponse.data.data || postsResponse.data
      console.log('🔍 Posts fetched from /posts endpoint:', userPosts)
      
      // Combine user data with posts
      profileUser.value = {
        ...authStore.user,
        posts: userPosts
      }
    } else {
      // Fetch another user's profile data
      console.log('🔍 Making API call to fetch user profile:', props.username)
      console.log('🔍 Auth token available:', !!localStorage.getItem('auth_token'))
      console.log('🔍 Auth store user:', authStore.user)
      
      const userResponse = await axiosInstance.get(`/users/${props.username}`)
      const userData = userResponse.data
      console.log('🔍 User data fetched:', userData)
      
      // For other users' profiles, we don't fetch posts separately since they're included in the user response
      // The posts are already loaded with the user relationship in the backend
      profileUser.value = userData
    }
    
    console.log('🔍 Combined profileUser:', profileUser.value)
  } catch (err) {
    console.error('🔍 Error in fetchProfile:', err)
    
    // Handle 403 errors (unauthorized)
    if (err.response && err.response.status === 403) {
      console.error('🔍 403 Error in fetchProfile - Token may be invalid');
      if (confirm('Your session may have expired. Would you like to log in again?')) {
        authStore.logout();
        window.location.href = '/login';
        return;
      }
    }
    
    // Handle 404 errors (user not found)
    if (err.response && err.response.status === 404) {
      error.value = t('user_not_found')
    } else {
      error.value = t('failed_to_load_profile')
    }
    
    profileUser.value = null
  } finally {
    loading.value = false
  }
}

const user = computed(() => {
  // Get the base user object (could come from API, store or fallback defaults)
  const baseUser = profileUser.value || {
    name: '',
    handle: '',
    avatar: '',
    cover_photo: '',
    bio: '',
    role: 'user',
    social_links: {},
    last_seen_at: '',
    can_be_followed: false,
    posts: [],
    media: [],
    total_likes_received: 0,
    total_followers: 0,
    total_video_uploads: 0,
    total_image_uploads: 0
  };

  // Ensure posts have proper permission structure for follower view **without** mutating the original array/object
  const processedPosts = Array.isArray(baseUser.posts)
    ? baseUser.posts.map((post) => {
        const hasPermissions = Array.isArray(post.permission_sets) && post.permission_sets.length > 0;
        return {
          ...post,
          permission_sets: post.permission_sets || [],
          // In follower mode: show locked posts (follower view), in normal mode: show unlocked posts (owner view)
          user_has_permission: viewAsFollower.value
            ? false // Follower view: always show locked posts
            : true, // Normal view: owner sees their posts unlocked
          // Provide safe default for media_previews to avoid undefined errors
          media_previews: post.media_previews || []
        };
      })
    : [];

  // Return a fresh object so that the computed getter remains **pure** (no side-effects)
  return {
    ...baseUser,
    posts: processedPosts
  };
});

const userStatus = computed(() => user.value.is_online ? t('active_now') : t('offline'))

// Check if viewing own profile
const isOwnProfile = computed(() => {
  return props.username === authStore.user?.username
})

const userStats = computed(() => ({
  likes: user.value.total_likes_received || 0,
  posts: user.value.posts?.length || 0,
  followers: user.value.total_followers || 0,
  media: (user.value.total_video_uploads || 0) + (user.value.total_image_uploads || 0)
}))

const handleEditPost = async (post) => {
  try {
    // Fetch the latest post data with all relationships
    const response = await axiosInstance.get(`/posts/${post.id}`);
    const postData = response.data.data || response.data;

    // Initialize the upload store with the post data and context
    const contextId = `edit-post-${postData.id}`;
    uploadStore.initializeFromPost(postData, contextId);
    uploadStore.setContext(contextId);

    selectedPost.value = postData;
    showEditModal.value = true;
  } catch (err) {
    // Handle error (show toast, etc.)
    console.error(t('failed_to_load_post_for_editing'), err);
  }
}

const handlePostAction = async ({ postId, action }) => {
  try {
    // Debug logging
    console.log('🔍 handlePostAction called:', { postId, action });
    console.log('🔍 Current user ID:', authStore.user?.id);
    console.log('🔍 Current user from authStore:', authStore.user);
    console.log('🔍 All posts in user.value.posts:', user.value.posts);
    console.log('🔍 Post being acted on:', user.value.posts.find(p => p.id === postId));
    
    // Only allow certain actions for own profile
    if (!isOwnProfile.value && ['pin', 'unpin', 'edit', 'delete'].includes(action)) {
      console.log('🔍 Action not allowed for other user profiles:', action);
      return;
    }
    
    switch (action) {
      case 'pin':
        await axiosInstance.post(`/posts/${postId}/pin`);
        // Refetch posts to get correct order with pinned posts first
        await fetchProfile();
        break;
        
      case 'unpin':
        await axiosInstance.delete(`/posts/${postId}/pin`);
        // Refetch posts to get correct order with pinned posts first
        await fetchProfile();
        break;
        
      case 'copyLink':
        const post = user.value.posts.find(p => p.id === postId);
        if (post) {
          const postUrl = `${window.location.origin}/${user.value.username}/posts/${postId}`;
          await navigator.clipboard.writeText(postUrl);
          toast.success(t('post_link_copied'));
        }
        break;
        
      case 'edit':
        const postToEdit = user.value.posts.find(p => p.id === postId);
        if (postToEdit) {
          await handleEditPost(postToEdit);
        }
        break;
        
      case 'delete':
        if (confirm('Are you sure you want to delete this post?')) {
          await axiosInstance.delete(`/posts/${postId}`);
          // Remove the post from the local state
          const postIndex = user.value.posts.findIndex(p => p.id === postId);
          if (postIndex > -1) {
            user.value.posts.splice(postIndex, 1);
          }
        }
        break;
        
      default:
        console.log('Unhandled post action:', action);
    }
  } catch (error) {
    console.error('Error handling post action:', error);
    
    // Handle 403 errors (unauthorized)
    if (error.response && error.response.status === 403) {
      console.error('🔍 403 Error detected - Token may be invalid');
      console.error('🔍 Current token user ID:', authStore.user?.id);
      console.error('🔍 Post user ID:', user.value.posts.find(p => p.id === postId)?.user_id);
      
      // Prompt user to log in again
      if (confirm('Your session may have expired. Would you like to log in again?')) {
        authStore.logout();
        // Redirect to login page
        window.location.href = '/login';
      }
    }
  }
}

const closeEditModal = () => {
  selectedPost.value = null;
  showEditModal.value = false;
};

const handleToggleFollowerView = (isFollowerMode) => {
  viewAsFollower.value = isFollowerMode;
};

const handleProfileUpdate = (updatedProfile) => {
  // Update the profile user data (which is the source for the computed user)
  if (profileUser.value) {
    Object.assign(profileUser.value, updatedProfile);
  }
};

onMounted(async () => {
  await fetchProfile()
})

// Watch for username changes
watch(() => props.username, async () => {
  await fetchProfile()
})
</script>