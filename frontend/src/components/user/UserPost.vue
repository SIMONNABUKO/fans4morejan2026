<template>
  <article
    class="bg-white dark:bg-gray-900 rounded-2xl overflow-visible shadow-sm border border-gray-100 dark:border-gray-800 w-full hover:shadow-lg hover:scale-[1.01] transition-all duration-300"
  >
    <!-- Header Section -->
    <div class="p-5">
      <!-- Header -->
      <div class="flex items-center justify-between p-5" @click.stop>
        <div class="flex items-center gap-4">
          <div class="relative group">
            <div class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-gray-100 dark:ring-gray-800 group-hover:ring-blue-200 dark:group-hover:ring-blue-800 transition-all duration-300">
              <img
                :src="post.user.avatar"
                :alt="post.user.name"
                class="w-full h-full object-cover cursor-pointer group-hover:scale-110 transition-transform duration-300"
              />
            </div>
            <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full border-2 border-white dark:border-gray-900 shadow-sm"></div>
          </div>
          <div class="flex-1">
            <div class="flex items-center gap-2">
              <span class="font-bold text-gray-900 dark:text-white cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
                {{ post.user.name }}
              </span>
              <i v-if="post.user.role === 'admin'" class="ri-checkbox-circle-fill text-blue-500 text-base"></i>
              <i v-if="post.pinned" class="ri-pushpin-fill text-orange-500 text-base" :title="t('pinned_post')"></i>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
              <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">
                {{ post.user.handle }}
              </span>
              <span class="text-gray-500 dark:text-gray-400 text-sm font-medium">·</span>
              <span class="text-gray-500 dark:text-gray-400 text-sm font-medium whitespace-nowrap">
                {{ formatDate(post.created_at) }}
              </span>
            </div>
          </div>
        </div>
        <PostOptionsMenu
          :user-id="post.user.id"
          :username="post.user.name"
          :post-creator-role="post.user.role"
          :is-own-post="isOwnPost"
          :post-pinned="post.pinned"
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
    </div>

    <!-- Post Content -->
    <div class="px-5 pb-5">
      <p
        v-if="post.content"
        class="whitespace-pre-line text-gray-900 dark:text-white text-base leading-relaxed"
      >
        {{ post.content }}
      </p>
    </div>

    <!-- Media Grid -->
    <div class="overflow-hidden">
              <PostGrid
          :media="
            post.user_has_permission
              ? post.media
              : (post.media_previews && post.media_previews.length > 0 ? post.media_previews : post.media)
          "
          :original-media="post.media"
          :author="post.user"
          :description="post.content"
          :user-has-permission="!!post.user_has_permission"
          :total-media-count="post.media.length"
          :required-permissions="post.required_permissions || []"
          :permission-sets="post.permission_sets || []"
          @like="handleMediaLike"
          @bookmark="handleMediaBookmark"
          @stats="handleMediaStats"
          @unlock="handleUnlock"
        />
    </div>

    <!-- Footer Actions -->
    <div
      class="flex items-center justify-between px-5 py-4 border-t border-gray-100 dark:border-gray-800"
    >
      <div class="flex items-center gap-8">
        <button
          class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-all duration-300 group"
          @click="handleComment"
        >
          <i
            class="ri-chat-1-line text-xl group-hover:scale-110 transition-transform duration-200"
          ></i>
          <span class="text-sm font-semibold">{{ formatNumber(post.comments_count || post.stats?.total_comments || 0) }}</span>
        </button>

        <button
          class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-all duration-300 group"
          @click="handleLike"
        >
          <i
            :class="[
              isLiked ? 'ri-heart-fill text-red-500' : 'ri-heart-line',
              'text-xl group-hover:scale-110 transition-transform duration-200'
            ]"
          ></i>
          <span class="text-sm font-semibold">{{ formatNumber(post.likes_count || post.stats?.total_likes || 0) }}</span>
        </button>

        <button
          class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-all duration-300 group"
          @click="handleShare"
        >
          <i
            class="ri-share-line text-xl group-hover:scale-110 transition-transform duration-200"
          ></i>
        </button>
      </div>
    </div>

    <!-- Unlock Bundle Modal -->
    <UnlockBundleModal
      v-if="isUnlockModalOpen"
      :is-open="isUnlockModalOpen"
      :creator-name="post.user.name"
      :required-permissions="post.required_permissions || []"
      :post-owner-id="String(post.user.id)"
      :post-id="String(post.id)"
      @close="isUnlockModalOpen = false"
      @follow="handleFollowFromModal"
      @subscribe="handleSubscribeFromModal"
      @pay="handlePaymentFromModal"
    />
  </article>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import PostGrid from './PostGrid.vue'
import PostOptionsMenu from '../posts/PostOptionsMenu.vue'
import UnlockBundleModal from '@/components/modals/UnlockBundleModal.vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/authStore'
import { useFeedStore } from '@/stores/feedStore'
import { useSubscriptionStore } from '@/stores/subscriptionStore'
import { useToast } from 'vue-toastification'

const props = defineProps({
  post: {
    type: Object,
    required: true,
    validator: (post) => {
      return (
        post.user &&
        post.media &&
        Array.isArray(post.media) &&
        post.content &&
        post.created_at
      )
    }
  },
  viewAsFollower: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits([
  'like',
  'comment',
  'share',
  'bookmark',
  'stats',
  'post-action',
  'edit'
])

const { t } = useI18n()
const authStore = useAuthStore()
const feedStore = useFeedStore()
const subscriptionStore = useSubscriptionStore()
const toast = useToast()

// State
// Handle both data structures: direct fields and stats object
const isLiked = ref(props.post.is_liked || props.post.stats?.is_liked || false)
const showOptionsMenu = ref(false)
const isUnlockModalOpen = ref(false)

// Watch for changes in post like status
watch(() => props.post.is_liked || props.post.stats?.is_liked, (newValue) => {
  isLiked.value = newValue || false
})

// Watch for changes in post likes count
watch(() => props.post.likes_count || props.post.stats?.total_likes, (newValue) => {
  // This ensures the UI updates when the parent updates the likes count
})

// Check if this is the user's own post
const isOwnPost = computed(() => {
  // If viewing as follower, treat as if it's not own post to show follower options
  if (props.viewAsFollower) {
    return false
  }
  return authStore.user?.id === props.post.user_id
})



// Menu handlers
const toggleOptionsMenu = (event) => {
  event.stopPropagation()
  showOptionsMenu.value = !showOptionsMenu.value
}

const handleMenuOption = (optionId) => {
  if (optionId === 'edit') {
    emit('edit', props.post)
  } else {
    emit('post-action', { postId: props.post.id, action: optionId })
  }
  showOptionsMenu.value = false
}

// Click outside handler
const handleClickOutside = (event) => {
  if (
    !event.target.closest('[data-menu-trigger]') &&
    !event.target.closest('.post-options-menu') &&
    showOptionsMenu.value
  ) {
    showOptionsMenu.value = false
  }
}
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Other handlers
const handleLike = async () => {
  // Don't update the UI state immediately - wait for the API response
  const newLikedState = !isLiked.value
  emit('like', { postId: props.post.id, liked: newLikedState })
}
const handleComment = () => {
  emit('comment', props.post.id)
}
const handleShare = () => {
  emit('share', props.post.id)
}
const handleMediaLike = () => {
  handleLike()
}
const handleMediaBookmark = () => {
  emit('bookmark', props.post.id)
}
const handleMediaStats = () => {
  emit('stats', props.post.id)
}

// PostOptionsMenu handlers
const handleUnfollow = () => {
  emit('post-action', { postId: props.post.id, action: 'unfollow' })
}

const handleSubscribeFromMenu = () => {
  emit('post-action', { postId: props.post.id, action: 'subscribe' })
}

const handleCopyLink = () => {
  emit('post-action', { postId: props.post.id, action: 'copyLink' })
}

const handleReport = () => {
  emit('post-action', { postId: props.post.id, action: 'report' })
}

const handleVip = () => {
  emit('post-action', { postId: props.post.id, action: 'vip' })
}

const handleMute = () => {
  emit('post-action', { postId: props.post.id, action: 'mute' })
}

const handleBlock = () => {
  emit('post-action', { postId: props.post.id, action: 'block' })
}

const handlePin = () => {
  emit('post-action', { postId: props.post.id, action: 'pin' })
}

const handleUnpin = () => {
  emit('post-action', { postId: props.post.id, action: 'unpin' })
}

const handleEdit = () => {
  emit('edit', props.post)
}

const handleDelete = () => {
  emit('post-action', { postId: props.post.id, action: 'delete' })
}

// Unlock functionality
const handleUnlock = () => {
  console.log('🔍 UserPost: handleUnlock called, opening unlock modal')
  console.log('🔍 UserPost: Post data being passed to modal:', {
    id: props.post.id,
    user: props.post.user,
    required_permissions: props.post.required_permissions,
    user_has_permission: props.post.user_has_permission,
    media: props.post.media,
    media_previews: props.post.media_previews
  })
  isUnlockModalOpen.value = true
}

const handleFollowFromModal = async (userId) => {
  try {
    const result = await feedStore.followUser(userId)
    if (result.success) {
      toast.success(result.message)
      // Refresh the user profile data
      emit('post-action', { postId: props.post.id, action: 'refresh' })
    } else {
      toast.error(result.error)
    }
  } catch (error) {
    toast.error('Failed to follow user')
  }
}

const handleSubscribeFromModal = async ({ tierId, duration, postOwnerId }) => {
  try {
    console.log('handleSubscribeFromModal called with:', { tierId, duration, postOwnerId })
    
    const result = await subscriptionStore.subscribeTier(tierId, duration, postOwnerId)
    
    console.log('Subscribe result:', result)
    
    if (result.success) {
      if (result.data?.payment_method === 'wallet' && !result.data?.redirect_required) {
        toast.success(result.message || 'Payment processed successfully using your wallet balance')
        emit('post-action', { postId: props.post.id, action: 'refresh' })
        isUnlockModalOpen.value = false
      } else if (result.data?.payment_url && typeof result.data.payment_url === 'string') {
        toast.success('Please complete your payment on the payment page')
        window.open(result.data.payment_url, '_blank')
      } else {
        toast.success(result.message || 'Subscription initiated')
      }
    } else {
      if (result.error === 'insufficient_balance') {
        toast.error('Insufficient wallet balance. Please add funds to your wallet.')
      } else {
        toast.error(result.error || 'Failed to process subscription')
      }
    }
  } catch (error) {
    console.error('Error in handleSubscribeFromModal:', error)
    toast.error('Failed to subscribe to tier')
  }
}

const handlePaymentFromModal = async (amount) => {
  try {
    const result = await feedStore.unlockPost(props.post.id, amount)
    if (result.success) {
      if (result.data?.payment_method === 'wallet' && !result.data?.redirect_required) {
        toast.success('Content unlocked successfully using your wallet balance')
        emit('post-action', { postId: props.post.id, action: 'refresh' })
        isUnlockModalOpen.value = false
      } else if (result.data?.payment_url && typeof result.data.payment_url === 'string') {
        toast.success('Please complete your payment on the payment page')
        window.open(result.data.payment_url, '_blank')
      } else {
        toast.success(result.message || 'Payment initiated')
      }
    } else {
      if (result.error === 'insufficient_balance') {
        toast.error('Insufficient wallet balance. Please add funds to your wallet.')
      } else {
        toast.error(result.error || 'Failed to process payment')
      }
    }
  } catch (error) {
    toast.error('Failed to process payment')
  }
}

// Util
const formatNumber = (num) => {
  if (!num) return '0'
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num.toString()
}

const formatDate = (date) => {
  if (!date) return ''
  const d = new Date(date)
  if (isNaN(d.getTime())) return ''
  return d.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}
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
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}
.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out;
}
.animate-slideInFromBottom {
  animation: slideInFromBottom 0.5s ease-out;
}
.animate-scaleIn {
  animation: scaleIn 0.3s ease-out;
}
.animate-pulse {
  animation: pulse 2s infinite;
}

/* Enhanced focus states */
button:focus {
  outline: none;
  ring: 2px;
  ring-color: rgb(59 130 246);
  ring-offset: 2px;
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color,
    text-decoration-color, fill, stroke, opacity, box-shadow, transform,
    filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Custom scrollbar */
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
  background: rgba(156, 163, 175, 0.8);
}

/* Glassmorphism effects */
.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
.glass-dark {
  background: rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Button hover effects */
button:hover {
  transform: translateY(-1px);
}
button:active {
  transform: translateY(0);
}

/* Tooltip styling */
[data-tooltip] {
  position: relative;
}
[data-tooltip]:before {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
}
[data-tooltip]:hover:before {
  opacity: 1;
}

/* Gradient text */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Shadow effects */
.shadow-soft {
  box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07),
    0 10px 20px -2px rgba(0, 0, 0, 0.04);
}
.shadow-soft-dark {
  box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.3),
    0 10px 20px -2px rgba(0, 0, 0, 0.2);
}
</style>
