<template>
  <article
    :class="[
      'bg-white dark:bg-gray-900 rounded-2xl overflow-visible shadow-sm border border-gray-100 dark:border-gray-800',
      !props.isSinglePost ? 'cursor-pointer hover:shadow-lg hover:scale-[1.01] transition-all duration-300' : ''
    ]"
    @click="handlePostClick"
  >
    <!-- Header -->
    <div class="flex items-center justify-between p-5" @click.stop>
      <div class="flex items-center gap-4">
        <div class="relative group">
          <div class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-gray-100 dark:ring-gray-800 group-hover:ring-blue-200 dark:group-hover:ring-blue-800 transition-all duration-300">
            <img
              :src="props.post.user.avatar"
              :alt="props.post.user.username"
              class="w-full h-full object-cover cursor-pointer group-hover:scale-110 transition-transform duration-300"
              @click="navigateToUserProfile"
            />
          </div>
          <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full border-2 border-white dark:border-gray-900 shadow-sm"></div>
        </div>
        <div class="flex-1">
          <div class="flex items-center gap-2">
            <span
              class="font-bold text-gray-900 dark:text-white cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200"
              @click="navigateToUserProfile"
            >
              {{ props.post.user.username }}
            </span>
            <i class="ri-checkbox-circle-fill text-blue-500 text-base"></i>
            <!-- Preview Video Indicator -->
            <div v-if="props.post.isPreviewVideo" class="flex items-center gap-1 px-2 py-1 bg-purple-100 dark:bg-purple-900/30 rounded-full">
              <i class="ri-video-line text-purple-600 dark:text-purple-400 text-xs"></i>
              <span class="text-purple-600 dark:text-purple-400 text-xs font-medium">Preview</span>
            </div>
          </div>
          <span
            class="text-gray-500 dark:text-gray-400 text-sm font-medium"
            >{{ props.post.user.handle }}</span
          >
        </div>
      </div>
      <PostOptionsMenu
        :user-id="props.post.user.id"
        :username="props.post.user.username"
        :post-creator-role="props.post.user.role"
        :is-own-post="isOwnPost"
        :post-pinned="props.post.pinned"
        @unfollow="handleUnfollow"
        @subscribe="handleSubscribeFromMenu"
        @copy-link="handleCopyLink"
        @report="handleReport"
        @vip="handleVip"
        @mute="handleMute"
        @block="handleBlock"
        @pin="handlePin"
        @unpin="handleUnpin"
        @edit="handleEdit"
        @delete="handleDelete"
      />
    </div>

    <!-- Post Content -->
    <div class="px-5 pb-5">
      <p class="text-gray-900 dark:text-white text-base leading-relaxed mb-4">
        {{ props.post.content }}
      </p>

      <!-- Tagged Users -->
      <div v-if="props.post.tagged_users && props.post.tagged_users.length > 0" class="flex flex-wrap items-center gap-2 mb-3">
        <span 
          v-for="user in props.post.tagged_users" 
          :key="user.id"
          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-800/30 transition-all duration-200"
          @click="navigateToTaggedUserProfile(user.username)"
        >
          @{{ user.name }}
        </span>
      </div>

      <!-- Hashtags -->
      <div class="flex flex-wrap gap-2">
        <span
          v-for="tag in props.post.hashtags"
          :key="tag"
          class="text-blue-600 dark:text-blue-400 cursor-pointer hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium transition-colors duration-200"
          @click="navigateToHashtag(tag)"
        >
          #{{ tag }}
        </span>
      </div>
    </div>

    <!-- Enhanced Media Container -->
    <div class="overflow-hidden">
      <PostGrid
        :media="
          props.post.isPreviewVideo
            ? props.post.media
            : (props.post.user_has_permission
                ? props.post.media
                : props.post.media_previews)
        "
        :original-media="props.post.media"
        :author="props.post.user"
        :description="props.post.content"
        :user-has-permission="props.post.isPreviewVideo ? true : props.post.user_has_permission"
        :total-media-count="props.post.media.length"
        :is-preview-video="props.post.isPreviewVideo"
        :required-permissions="props.post.required_permissions || []"
        :permission-sets="props.post.permission_sets || []"
        @mediaLike="handleMediaLike"
        @mediaBookmark="handleMediaBookmark"
        @mediaStats="handleMediaStats"
        @unlock="handleUnlock"
      />
    </div>

    <!-- Footer Actions -->
    <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800">
      <div class="flex items-center gap-8">
        <button
          class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300 group"
          @click="openCommentModal"
        >
          <i class="ri-chat-1-line text-xl group-hover:scale-110 transition-transform duration-200"></i>
          <span class="text-sm font-semibold">{{ props.post.stats.total_comments }}</span>
        </button>
        <button
          class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-all duration-300 group"
          @click="toggleLike"
          :disabled="likeLoading"
        >
          <i
            :class="[
              props.post.stats.is_liked
                ? 'ri-heart-fill text-red-500'
                : 'ri-heart-line',
            ]"
            class="text-xl group-hover:scale-110 transition-transform duration-200"
          ></i>
          <span class="text-sm font-semibold">{{ props.post.stats.total_likes }}</span>
        </button>
        <button
          class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300 group"
          @click="toggleBookmark"
        >
          <i
            :class="[
              props.post.stats.is_bookmarked
                ? 'ri-bookmark-fill text-blue-500'
                : 'ri-bookmark-line',
            ]"
            class="text-xl group-hover:scale-110 transition-transform duration-200"
          ></i>
          <span class="text-sm font-semibold">{{ props.post.stats.total_bookmarks || 0 }}</span>
        </button>
        <button
          class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-green-600 dark:hover:text-green-400 transition-all duration-300 group"
          @click="isTipModalOpen = true"
        >
          <i class="ri-money-dollar-circle-fill text-xl group-hover:scale-110 transition-transform duration-200"></i>
          <span class="text-sm font-semibold">{{ props.post.total_tips }}</span>
        </button>
      </div>
    </div>

    <!-- Tip Modal -->
    <TipModal
      :is-open="isTipModalOpen"
      :recipient="props.post.user"
      @close="isTipModalOpen = false"
      @tip="handleTip"
    />

    <!-- Unlock Bundle Modal -->
    <UnlockBundleModal
      v-if="isUnlockModalOpen"
      :is-open="isUnlockModalOpen"
      :creator-name="props.post.user.name"
      :required-permissions="props.post.required_permissions"
      :post-owner-id="String(props.post.user.id)"
      :post-id="String(props.post.id)"
      @close="isUnlockModalOpen = false"
      @follow="handleFollow"
      @subscribe="handleSubscribe"
      @pay="handlePayment"
    />

    <!-- Report Modal -->
    <ReportModal
      :is-open="isReportModalOpen"
      :content-id="props.post.id"
      content-type="post"
      @close="isReportModalOpen = false"
    />

    <CommentModal
      :is-open="isCommentModalOpen"
      :post="props.post"
      :user-has-permission="props.post.user_has_permission"
      :current-user="authStore.user"
      @close="closeCommentModal"
      @commentSubmitted="handleCommentSubmitted"
    />
  </article>
</template>

<script setup>
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { useFeedStore } from "@/stores/feedStore";
import { useSubscriptionStore } from "@/stores/subscriptionStore";
import { usePostOptionsStore } from "@/stores/postOptionsStore";
import PostGrid from "@/components/user/PostGrid.vue";
import TipModal from "@/components/modals/TipModal.vue";
import UnlockBundleModal from "@/components/modals/UnlockBundleModal.vue";
import PostOptionsMenu from "@/components/posts/PostOptionsMenu.vue";
import { useToast } from "vue-toastification";
import ReportModal from '@/components/modals/ReportModal.vue'
import CommentModal from '@/components/modals/CommentModal.vue'
import { useAuthStore } from '@/stores/authStore'

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
  needsRefresh: {
    type: Boolean,
    default: false,
  },
  isSinglePost: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['post-deleted']);

const router = useRouter();
const feedStore = useFeedStore();
const subscriptionStore = useSubscriptionStore();
const postOptionsStore = usePostOptionsStore();
const toast = useToast();
const isTipModalOpen = ref(false);
const isUnlockModalOpen = ref(false);
const isReportModalOpen = ref(false)
const likeLoading = ref(false);
const isCommentModalOpen = ref(false)
const authStore = useAuthStore()

const navigateToUserProfile = () => {
  router.push({
    name: "userProfile",
    params: { username: props.post.user.username },
  });
};

// New function to navigate to tagged user's profile
const navigateToTaggedUserProfile = (username) => {
  router.push({
    name: "userProfile",
    params: { username },
  });
};

const navigateToPost = (event) => {
  // Don't navigate if the click was on a button or interactive element
  if (event.target.closest('button') || event.target.closest('a')) {
    return;
  }
  
  // Don't navigate if we're already on the single post page
  if (props.isSinglePost) {
    return;
  }
  
  router.push({
    name: "singlePost",
    params: { id: props.post.id }
  });
};

// New function to handle post clicks - always navigate to single post page
const handlePostClick = (event) => {
  // Don't handle if the click was on a button or interactive element
  if (event.target.closest('button') || event.target.closest('a')) {
    return;
  }
  
  // Don't handle if we're already on the single post page
  if (props.isSinglePost) {
    return;
  }
  
  // Always navigate to single post page when clicking the post card
  navigateToPost(event);
};

const handleMediaLike = async (mediaId) => {
  const media = props.post.media.find((m) => m.id === mediaId);
  if (!media) return;

  try {
    const result = media.is_liked
      ? await feedStore.unlikeMedia(mediaId)
      : await feedStore.likeMedia(mediaId);

    if (result.success) {
      toast.success(result.message);
    } else {
      toast.error(result.error);
    }
  } catch (error) {
    toast.error("An error occurred while processing your request");
  }
};

const handleMediaBookmark = (mediaId) => {
  console.log("Bookmark media:", mediaId);
  // Implement media bookmark functionality
};

const handleMediaStats = (mediaId) => {
  console.log("View media stats:", mediaId);
  // Implement media stats functionality
};

const toggleLike = async () => {
  if (likeLoading.value) return;
  likeLoading.value = true;
  try {
    const result = props.post.stats.is_liked
      ? await feedStore.unlikePost(props.post.id)
      : await feedStore.likePost(props.post.id);

    if (result.success) {
      toast.success(result.message);
    } else {
      toast.error(result.error);
    }
  } catch (error) {
    toast.error("An error occurred while processing your request");
  } finally {
    likeLoading.value = false;
  }
};

const toggleBookmark = async () => {
  try {
    const result = props.post.stats.is_bookmarked
      ? await feedStore.unbookmarkPost(props.post.id)
      : await feedStore.bookmarkPost(props.post.id);

    if (result.success) {
      toast.success(result.message);
    } else {
      toast.error(result.error);
    }
  } catch (error) {
    toast.error("An error occurred while processing your request");
  }
};

const handleTip = async (tipData) => {
  try {
    const result = await feedStore.sendTip({
      amount: tipData.amount,
      receiverId: props.post.user.id,
      tippableType: "post",
      tippableId: props.post.id,
    });

    if (result.success) {
      // Check if payment was processed via wallet (no redirect needed)
      if (result.data?.payment_method === 'wallet' && !result.data?.redirect_required) {
        toast.success("Tip sent successfully using your wallet balance");
        isTipModalOpen.value = false;
      } else if (result.data?.payment_url && typeof result.data.payment_url === 'string') {
        // For CCBill payments, show a different message and open payment URL
        toast.success("Please complete your tip on the payment page");
        window.open(result.data.payment_url, "_blank");
        isTipModalOpen.value = false;
      } else {
        toast.success(result.message || "Tip initiated successfully");
        isTipModalOpen.value = false;
      }
    } else {
      if (result.error === 'insufficient_balance') {
        toast.error("Insufficient wallet balance. Please add funds to your wallet.");
      } else if (result.errors) {
        Object.values(result.errors).forEach((error) => {
          toast.error(error[0]);
        });
      } else if (result.error) {
        toast.error(result.error);
      } else {
        toast.error(result.message || "Failed to send tip");
      }
    }
  } catch (error) {
    toast.error("An unexpected error occurred while sending the tip");
  }
};

const handleUnlock = () => {
  isUnlockModalOpen.value = true;
};

const handleFollow = async (userId) => {
  try {
    const result = await feedStore.followUser(userId);
    if (result.success) {
      toast.success(result.message);
      await feedStore.fetchFeed(true);
    } else {
      toast.error(result.error);
    }
  } catch (error) {
    toast.error("Failed to follow user");
  }
};

const handleSubscribe = async ({ tierId, duration, postOwnerId }) => {
  try {
    // Prevent any default behavior
    event?.preventDefault?.();
    
    console.log('handleSubscribe called with:', { tierId, duration, postOwnerId });
    
    const result = await subscriptionStore.subscribeTier(
      tierId,
      duration,
      postOwnerId
    );
    
    console.log('Subscribe result:', result);
    
    if (result.success) {
      // Check if payment was processed via wallet (no redirect needed)
      if (result.data?.payment_method === 'wallet' && !result.data?.redirect_required) {
        toast.success(result.message || "Payment processed successfully using your wallet balance");
        // Refresh content immediately since payment is already processed
        await feedStore.fetchFeed(true);
        isUnlockModalOpen.value = false;
      } else if (result.data?.payment_url && typeof result.data.payment_url === 'string') {
        // For CCBill payments, show a different message and open payment URL
        toast.success("Please complete your payment on the payment page");
        // Open in new tab instead of changing current URL
        window.open(result.data.payment_url, "_blank");
      } else {
        toast.success(result.message || "Subscription initiated");
      }
    } else {
      // Handle specific error cases
      if (result.error === 'insufficient_balance') {
        toast.error("Insufficient wallet balance. Please add funds to your wallet.");
      } else {
        toast.error(result.error || "Failed to process subscription");
      }
    }
  } catch (error) {
    console.error('Error in handleSubscribe:', error);
    toast.error("Failed to subscribe to tier");
  }
};

const handlePayment = async (amount) => {
  try {
    // Prevent any default behavior
    event?.preventDefault?.();
    
    const result = await feedStore.unlockPost(props.post.id, amount);
    if (result.success) {
      // Check if payment was processed via wallet (no redirect needed)
      if (result.data?.payment_method === 'wallet' && !result.data?.redirect_required) {
        toast.success("Content unlocked successfully using your wallet balance");
        // Refresh content immediately since payment is already processed
        await feedStore.fetchFeed(true);
        isUnlockModalOpen.value = false;
      } else if (result.data?.payment_url && typeof result.data.payment_url === 'string') {
        // For CCBill payments, show a different message and open payment URL
        toast.success("Please complete your payment on the payment page");
        window.open(result.data.payment_url, "_blank");
      } else {
        toast.success(result.message || "Payment initiated");
      }
    } else {
      // Handle specific error cases
      if (result.error === 'insufficient_balance') {
        toast.error("Insufficient wallet balance. Please add funds to your wallet.");
      } else {
        toast.error(result.error || "Failed to process payment");
      }
    }
  } catch (error) {
    toast.error("Failed to process payment");
  }
};

// New methods for handling menu actions
const handleUnfollow = async (userId) => {
  // The unfollow action has already been performed in the PostOptionsMenu component
  // Here we can update the local state or refetch the feed if necessary
  await feedStore.fetchFeed(true);
};

const handleSubscribeFromMenu = async (userId) => {
  navigateToUserProfile();
};

const handleCopyLink = async () => {
  try {
    const postUrl = `${window.location.origin}/posts/${props.post.id}`;
    await navigator.clipboard.writeText(postUrl);
    toast.success("Post link copied to clipboard");
  } catch (error) {
    toast.error("Failed to copy link");
  }
};

const handleReport = () => {
  isReportModalOpen.value = true
}

const handleVip = async (userId) => {
  try {
    toast.info("VIP feature coming soon");
  } catch (error) {
    toast.error("Failed to process VIP action");
  }
};

const handleMute = async (userId) => {
  try {
    const result = await feedStore.muteUser(userId);
    if (result.success) {
      toast.success(result.message);
    } else {
      toast.error(result.error);
    }
  } catch (error) {
    toast.error("Failed to mute user");
  }
};

const handleBlock = async (userId) => {
  try {
    const result = await feedStore.blockUser(userId);
    if (result.success) {
      toast.success(result.message);
    } else {
      toast.error(result.error);
    }
  } catch (error) {
    toast.error("Failed to block user");
  }
};

const openCommentModal = () => {
  isCommentModalOpen.value = true
}
const closeCommentModal = () => {
  isCommentModalOpen.value = false
}

const handleCommentSubmitted = async (commentText) => {
  try {
    const response = await feedStore.addComment(props.post.id, commentText)
    if (response.success) {
      toast.success('Comment posted!')
      props.post.stats.total_comments++
      closeCommentModal()
    } else {
      toast.error(response.error || 'Failed to post comment')
    }
  } catch (e) {
    toast.error('Failed to post comment')
  }
}

const navigateToHashtag = (tag) => {
  router.push({
    name: "search",
    query: { q: `#${tag}` },
  });
};

// Check if this is the user's own post
const isOwnPost = computed(() => {
  return authStore.user?.id === props.post.user_id;
});

// Pin/Unpin handlers
const handlePin = async () => {
  try {
    const response = await fetch(`/api/posts/${props.post.id}/pin`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
      },
    });
    
    if (response.ok) {
      toast.success('Post pinned successfully');
      props.post.pinned = true;
      props.post.pinned_at = new Date().toISOString();
    } else {
      toast.error('Failed to pin post');
    }
  } catch (error) {
    toast.error('Failed to pin post');
  }
};

const handleUnpin = async () => {
  try {
    const response = await fetch(`/api/posts/${props.post.id}/pin`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.token}`,
      },
    });
    
    if (response.ok) {
      toast.success('Post unpinned successfully');
      props.post.pinned = false;
      props.post.pinned_at = null;
    } else {
      toast.error('Failed to unpin post');
    }
  } catch (error) {
    toast.error('Failed to unpin post');
  }
};

const handleEdit = () => {
  // Navigate to edit post page or open edit modal
  router.push({
    name: "editPost",
    params: { id: props.post.id }
  });
};

const handleDelete = async () => {
  if (confirm('Are you sure you want to delete this post?')) {
    try {
      const response = await fetch(`/api/posts/${props.post.id}`, {
        method: 'DELETE',
        headers: {
          'Authorization': `Bearer ${authStore.token}`,
        },
      });
      
      if (response.ok) {
        toast.success('Post deleted successfully');
        // Emit event to parent to remove post from list
        emit('post-deleted', props.post.id);
      } else {
        toast.error('Failed to delete post');
      }
    } catch (error) {
      toast.error('Failed to delete post');
    }
  }
};
</script>

<style scoped>
/* Enhanced animations */
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

@keyframes slideInFromBottom {
  from {
    opacity: 0;
    transform: translateY(100%);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Apply animations to elements */
.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out;
}

.animate-slideInFromBottom {
  animation: slideInFromBottom 0.5s ease-out;
}

.animate-scaleIn {
  animation: scaleIn 0.4s ease-out;
}

.animate-pulse {
  animation: pulse 2s infinite;
}

/* Enhanced focus states */
button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
  border-radius: 0.5rem;
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Enhanced scrollbar styling */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}

/* Dark mode scrollbar */
.dark ::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.7);
}

/* Glassmorphism effects */
.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .glass {
  background: rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Enhanced button hover effects */
.btn-hover {
  position: relative;
  overflow: hidden;
}

.btn-hover::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.btn-hover:hover::before {
  left: 100%;
}

/* Enhanced loading spinner */
.spinner {
  border: 2px solid rgba(156, 163, 175, 0.3);
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Enhanced tooltip */
.tooltip {
  position: relative;
}

.tooltip::before {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
  z-index: 10;
}

.tooltip:hover::before {
  opacity: 1;
}

/* Enhanced focus ring for accessibility */
.focus-ring:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

/* Enhanced gradient text */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Enhanced shadow effects */
.shadow-soft {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.shadow-medium {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.shadow-strong {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Enhanced post card hover effects */
.post-card {
  position: relative;
  overflow: hidden;
}

.post-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.post-card:hover::after {
  opacity: 1;
}

/* Enhanced verified badge */
.verified-badge {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  font-weight: 600;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Enhanced action buttons */
.action-button {
  position: relative;
  overflow: hidden;
}

.action-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.action-button:hover::before {
  left: 100%;
}

/* Enhanced disabled state */
.disabled-button {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.disabled-button:hover {
  transform: none !important;
  box-shadow: none !important;
}

/* Enhanced like button animation */
.like-button:active {
  transform: scale(0.95);
}

/* Enhanced tip button gradient */
.tip-button {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  font-weight: 600;
}

.tip-button:hover {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-1px);
  box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
}

/* Enhanced comment button */
.comment-button:hover {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  transform: translateY(-1px);
}

/* Enhanced bookmark button */
.bookmark-button:hover {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  transform: translateY(-1px);
}
</style>

