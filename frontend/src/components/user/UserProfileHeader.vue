<template>
  <div class="relative" style="overflow: visible;">
          <!-- Cover Photo -->
      <div class="relative w-full h-32 md:h-40 lg:h-48 overflow-hidden bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800">
        <img
          v-if="coverPhoto && coverPhoto !== '#'"
          :src="coverPhoto"
          :alt="t('profile_banner')"
          class="w-full h-full object-cover"
        />
        <div v-else class="w-full h-full flex items-center justify-center">
          <i class="ri-image-line text-4xl text-gray-400"></i>
        </div>
        
        <!-- Cover Photo Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
      </div>

    <!-- Profile Info Section -->
    <div class="px-4 pb-6">
      <div class="flex justify-between items-end">
        <!-- Profile Picture -->
        <div class="relative -mt-16 md:-mt-20">
          <div class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-background-light dark:border-background-dark overflow-hidden bg-surface-light dark:bg-surface-dark">
            <img
              v-if="avatarUrl && avatarUrl !== '#'"
              :src="avatarUrl"
              :alt="username"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center">
              <i class="ri-user-3-line text-4xl text-text-light-secondary dark:text-text-dark-secondary"></i>
            </div>
          </div>
          
          <!-- Online Status Indicator -->
          <div v-if="status === t('active_now')" class="absolute bottom-2 right-2 w-6 h-6 bg-green-500 rounded-full border-3 border-background-light dark:border-background-dark shadow-lg"></div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 mt-2">
          <button 
            @click="handleMessage"
            class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm text-text-light-primary dark:text-text-dark-primary rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-800 transition-all duration-200"
            :aria-label="t('send_message')"
          >
            <i class="ri-mail-line text-lg"></i>
          </button>
          <button 
            @click="handleTip"
            class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm text-text-light-primary dark:text-text-dark-primary rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-800 transition-all duration-200"
            :aria-label="t('send_tip')"
          >
            <i class="ri-money-dollar-circle-line text-lg"></i>
          </button>
          <div class="relative">
            <button 
              class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm text-text-light-primary dark:text-text-dark-primary rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-800 transition-all duration-200" 
              @click="actionsMenuOpen = !actionsMenuOpen"
            >
              <i class="ri-more-fill text-lg"></i>
            </button>
            <UserProfileActionsMenu 
              :is-open="actionsMenuOpen" 
              :is-muted="user.is_muted || false"
              :is-blocked="user.is_blocked || false"
              @close="actionsMenuOpen = false" 
              @action="handleActionsMenu" 
            />
          </div>
          
          <!-- Follow Button -->
          <button
            v-if="canBeFollowed && !isFollowing"
            @click="handleFollowClick"
            :disabled="feedStore?.followingInProgress?.[user?.id]"
            class="px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded-full shadow-lg hover:bg-primary-dark dark:hover:bg-primary-light transition-all duration-200 font-medium text-sm flex items-center gap-2"
          >
            <i v-if="feedStore?.followingInProgress?.[user?.id]" class="ri-loader-4-line animate-spin"></i>
            <i v-else class="ri-user-follow-line"></i>
            {{ feedStore?.followingInProgress?.[user?.id] ? t('processing') : t('follow') }}
          </button>
          
          <!-- Unfollow Button -->
          <button
            v-if="canBeFollowed && isFollowing"
            @click="handleFollowClick"
            :disabled="feedStore?.followingInProgress?.[user?.id]"
            class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-full shadow-lg transition-all duration-200 font-medium text-sm flex items-center gap-2"
          >
            <i v-if="feedStore?.followingInProgress?.[user?.id]" class="ri-loader-4-line animate-spin"></i>
            <i v-else class="ri-user-unfollow-line"></i>
            {{ feedStore?.followingInProgress?.[user?.id] ? t('processing') : t('following') }}
          </button>
        </div>
      </div>

      <!-- User Info -->
      <div class="mt-4">
        <div class="flex items-center gap-2 mb-2">
          <h1 class="text-2xl md:text-3xl font-bold text-text-light-primary dark:text-text-dark-primary truncate">
            {{ username }}
          </h1>
          <i v-if="role === 'admin'" class="ri-verified-badge-fill text-primary-light dark:text-primary-dark text-xl"></i>
          <i v-if="role === 'creator'" class="ri-star-fill text-yellow-500 text-xl"></i>
        </div>
        <p class="text-text-light-secondary dark:text-text-dark-secondary text-lg mb-1">{{ handle }}</p>
        <div class="flex items-center gap-2 text-sm text-text-light-secondary dark:text-text-dark-secondary">
          <i class="ri-circle-fill text-xs"></i>
          <span>{{ status }}</span>
        </div>
      </div>

      <!-- Stats -->
      <div class="flex flex-wrap gap-6 mt-6">
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center">
            <i class="ri-heart-fill text-red-500 text-sm"></i>
          </div>
          <div>
            <div class="font-bold text-text-light-primary dark:text-text-dark-primary">{{ formatNumber(stats.likes) }}</div>
            <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">{{ t('likes') }}</div>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
            <i class="ri-user-follow-fill text-blue-500 text-sm"></i>
          </div>
          <div>
            <div class="font-bold text-text-light-primary dark:text-text-dark-primary">{{ formatNumber(stats.followers) }}</div>
            <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">{{ t('followers') }}</div>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center">
            <i class="ri-file-list-fill text-green-500 text-sm"></i>
          </div>
          <div>
            <div class="font-bold text-text-light-primary dark:text-text-dark-primary">{{ stats.posts }}</div>
            <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">{{ t('posts') }}</div>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900/20 rounded-full flex items-center justify-center">
            <i class="ri-image-fill text-yellow-500 text-sm"></i>
          </div>
          <div>
            <div class="font-bold text-text-light-primary dark:text-text-dark-primary">{{ stats.media }}</div>
            <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">{{ t('media') }}</div>
          </div>
        </div>
      </div>

      <!-- Bio -->
      <div v-if="bio" class="mt-6">
        <p 
          class="text-text-light-primary dark:text-text-dark-primary leading-relaxed"
          :style="{ color: bioColor, fontFamily: bioFont }"
        >
          {{ bio }}
        </p>
      </div>

      <!-- Social Links -->
      <div v-if="hasSocialLinks" class="flex gap-4 mt-6">
        <a 
          v-if="socialLinks.facebook" 
          :href="socialLinks.facebook" 
          target="_blank" 
          rel="noopener noreferrer"
          class="p-2 bg-gray-100 dark:bg-gray-800 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        >
          <i class="ri-facebook-fill text-blue-600 text-lg"></i>
        </a>
        <a 
          v-if="socialLinks.twitter" 
          :href="socialLinks.twitter" 
          target="_blank" 
          rel="noopener noreferrer"
          class="p-2 bg-gray-100 dark:bg-gray-800 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        >
          <i class="ri-twitter-fill text-blue-400 text-lg"></i>
        </a>
        <a 
          v-if="socialLinks.instagram" 
          :href="socialLinks.instagram" 
          target="_blank" 
          rel="noopener noreferrer"
          class="p-2 bg-gray-100 dark:bg-gray-800 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        >
          <i class="ri-instagram-line text-pink-600 text-lg"></i>
        </a>
        <a 
          v-if="socialLinks.linkedin" 
          :href="socialLinks.linkedin" 
          target="_blank" 
          rel="noopener noreferrer"
          class="p-2 bg-gray-100 dark:bg-gray-800 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
        >
          <i class="ri-linkedin-fill text-blue-700 text-lg"></i>
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import ProfileEditor from '@/components/ProfileEditor.vue'
import { TransitionRoot } from '@headlessui/vue'
import { useI18n } from 'vue-i18n'
import UserProfileActionsMenu from './UserProfileActionsMenu.vue'
import { useFeedStore } from '@/stores/feedStore'
import { useMessagesStore } from '@/stores/messagesStore'

const { t } = useI18n()
const feedStore = useFeedStore()
const router = useRouter()

const props = defineProps({
  coverPhoto: {
    type: String,
    default: '#'
  },
  avatarUrl: {
    type: String,
    required: true
  },
  username: {
    type: String,
    required: true
  },
  handle: {
    type: String,
    required: true
  },
  status: {
    type: String,
    required: true
  },
  stats: {
    type: Object,
    required: true
  },
  bio: {
    type: String,
    default: null
  },
  role: {
    type: String,
    required: true
  },
  socialLinks: {
    type: Object,
    default: () => ({})
  },
  lastSeenAt: {
    type: String,
    default: null
  },
  canBeFollowed: {
    type: Boolean,
    default: false
  },
  bio_color: {
    type: String,
    default: '#222222'
  },
  bio_font: {
    type: String,
    default: 'inherit'
  },
  isFollowing: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['follow', 'unfollow', 'update:profile', 'view-as-follower', 'show-tip-modal', 'show-add-to-list-modal', 'show-earnings-modal', 'report-user', 'mute-user', 'block-user'])

// State for modal visibility
const showProfileEditor = ref(false)
const dropdownOpen = ref(false)
const moreBtn = ref(null)
const actionsMenuOpen = ref(false)

const handleActionsMenu = (action) => {
  actionsMenuOpen.value = false
  
  switch (action) {
    case 'add-to-list':
      emit('show-add-to-list-modal')
      break
    case 'copy-profile-link':
      copyProfileLink()
      break
    case 'earnings':
      emit('show-earnings-modal')
      break
    case 'report':
      emit('report-user')
      break
    case 'mute':
      emit('mute-user')
      break
    case 'block':
      emit('block-user')
      break
    default:
      console.log('Unhandled action:', action)
  }
}

// Handle message button click
const handleMessage = async () => {
  try {
    // Get or create conversation with this user
    const messagesStore = useMessagesStore()
    const result = await messagesStore.getOrCreateConversation(props.user.id)
    
    if (result.success) {
      // Navigate to the specific conversation
      router.push({
        name: 'single-message',
        params: { id: props.user.id }
      })
    } else {
      console.error('Failed to get or create conversation:', result.error)
      // Fallback to messages page
      router.push({ name: 'messages' })
    }
  } catch (error) {
    console.error('Error handling message:', error)
    // Fallback to messages page
    router.push({ name: 'messages' })
  }
}

// Handle tip button click
const handleTip = () => {
  // Emit event to parent to show tip modal
  emit('show-tip-modal')
}

// Computed property to create user data object for the editor
const userData = computed(() => ({
  name: props.username,
  username: props.username,
  handle: props.handle,
  bio: props.bio,
  avatar: props.avatarUrl,
  cover_photo: props.coverPhoto,
  twitter: props.socialLinks?.twitter,
  instagram: props.socialLinks?.instagram,
  facebook: props.socialLinks?.facebook,
  linkedin: props.socialLinks?.linkedin
}))

const hasSocialLinks = computed(() => {
  return Object.values(props.socialLinks).some(link => link !== null)
})

const formatNumber = (num) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  }
  if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'k'
  }
  return num.toString()
}

// Handler for profile updates
const handleProfileUpdate = (updatedProfile) => {
  emit('update:profile', updatedProfile)
}

function toggleDropdown() {
  dropdownOpen.value = !dropdownOpen.value
}

function closeDropdown(e) {
  if (
    dropdownOpen.value &&
    moreBtn.value &&
    !moreBtn.value.contains(e.target)
  ) {
    dropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('mousedown', closeDropdown)
})

onBeforeUnmount(() => {
  document.removeEventListener('mousedown', closeDropdown)
})

function copyProfileLink() {
  const profileUrl = window.location.origin + '/' + props.username + '/posts'
  navigator.clipboard.writeText(profileUrl)
  dropdownOpen.value = false
  // Optionally show a toast/notification
}

const bioColor = computed(() => props.bio_color || '#222222')
const bioFont = computed(() => props.bio_font || 'inherit')

const handleFollowClick = () => {
  console.log('🔍 Follow button clicked in UserProfileHeader')
  console.log('🔍 Props received:', {
    isFollowing: props.isFollowing,
    canBeFollowed: props.canBeFollowed,
    userId: props.user?.id
  })
  
  if (props.isFollowing) {
    console.log('🔍 Emitting unfollow event')
    emit('unfollow')
  } else {
    console.log('🔍 Emitting follow event')
    emit('follow')
  }
}
</script>

<style scoped>
/* Enhanced glassmorphism effects */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>

