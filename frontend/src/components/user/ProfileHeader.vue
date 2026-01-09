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
          <!-- Edit Profile Button -->
          <button 
            @click="showProfileEditor = true" 
            class="px-4 py-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm text-text-light-primary dark:text-text-dark-primary rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-800 transition-all duration-200 font-medium text-sm flex items-center gap-2"
          >
            <i class="ri-edit-line text-sm"></i>
            {{ t('edit_profile') }}
          </button>
          
          <!-- More Options Dropdown -->
          <div class="relative" style="z-index: 9999;">
            <button 
              ref="moreBtn" 
              @click="toggleDropdown" 
              class="p-2 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm text-text-light-primary dark:text-text-dark-primary rounded-full shadow-lg hover:bg-white dark:hover:bg-gray-800 transition-all duration-200"
              :aria-label="t('more_options')"
            >
              <i class="ri-more-fill text-lg"></i>
            </button>
            
            <!-- Dropdown Menu -->
            <Teleport to="body">
              <div 
                v-if="dropdownOpen" 
                ref="dropdownRef" 
                class="fixed bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                style="max-height: 300px; overflow-y: auto; z-index: 9999; width: 256px;"
                :style="dropdownPosition"
              >
                <div class="py-2">
                  <!-- Normal Mode Menu -->
                  <template v-if="!viewAsFollower">
                    <button 
                      @click="toggleFollowerView" 
                      class="w-full text-left px-4 py-3 text-sm text-text-light-primary dark:text-text-dark-primary hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
                    >
                      <i class="ri-eye-line text-lg"></i>
                      <div>
                        <div class="font-medium">{{ t('view_as_follower') }}</div>
                        <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">{{ t('see_how_your_profile_looks_to_followers') }}</div>
                      </div>
                    </button>
                    <button 
                      @click="copyProfileLink" 
                      class="w-full text-left px-4 py-3 text-sm text-text-light-primary dark:text-text-dark-primary hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
                    >
                      <i class="ri-link text-lg"></i>
                      <div>
                        <div class="font-medium">{{ t('copy_profile_link') }}</div>
                        <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">{{ t('share_your_profile_with_others') }}</div>
                      </div>
                    </button>
                  </template>
                  
                  <!-- Follower View Mode Menu -->
                  <template v-else>
                    <button 
                      @click="exitFollowerView" 
                      class="w-full text-left px-4 py-3 text-sm text-text-light-primary dark:text-text-dark-primary hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
                    >
                      <i class="ri-close-line text-lg"></i>
                      <div>
                        <div class="font-medium">{{ t('exit_follower_view') }}</div>
                        <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">{{ t('return_to_normal_view') }}</div>
                      </div>
                    </button>
                    <button 
                      @click="copyProfileLink" 
                      class="w-full text-left px-4 py-3 text-sm text-text-light-primary dark:text-text-dark-primary hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center gap-3 transition-colors"
                    >
                      <i class="ri-link text-lg"></i>
                      <div>
                        <div class="font-medium">{{ t('copy_profile_link') }}</div>
                        <div class="text-xs text-text-light-secondary dark:text-text-dark-secondary">{{ t('share_your_profile_with_others') }}</div>
                      </div>
                    </button>
                  </template>
                </div>
              </div>
            </Teleport>
          </div>
        </div>
      </div>

      <!-- User Info -->
      <div class="mt-4">
        <div class="flex items-center gap-2 mb-2">
          <h1 class="text-2xl md:text-3xl font-bold text-text-light-primary dark:text-text-dark-primary truncate">
            {{ username }}
          </h1>
          <i v-if="role === 'admin'" class="ri-verified-badge-fill text-primary-light dark:text-primary-dark text-xl"></i>
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
          class="w-10 h-10 bg-blue-600 hover:bg-blue-700 rounded-full flex items-center justify-center text-white transition-colors"
          :aria-label="t('facebook_profile')"
        >
          <i class="ri-facebook-fill text-lg"></i>
        </a>
        <a 
          v-if="socialLinks.twitter" 
          :href="socialLinks.twitter" 
          target="_blank" 
          rel="noopener noreferrer"
          class="w-10 h-10 bg-blue-400 hover:bg-blue-500 rounded-full flex items-center justify-center text-white transition-colors"
          :aria-label="t('twitter_profile')"
        >
          <i class="ri-twitter-fill text-lg"></i>
        </a>
        <a 
          v-if="socialLinks.instagram" 
          :href="socialLinks.instagram" 
          target="_blank" 
          rel="noopener noreferrer"
          class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 rounded-full flex items-center justify-center text-white transition-colors"
          :aria-label="t('instagram_profile')"
        >
          <i class="ri-instagram-line text-lg"></i>
        </a>
        <a 
          v-if="socialLinks.linkedin" 
          :href="socialLinks.linkedin" 
          target="_blank" 
          rel="noopener noreferrer"
          class="w-10 h-10 bg-blue-700 hover:bg-blue-800 rounded-full flex items-center justify-center text-white transition-colors"
          :aria-label="t('linkedin_profile')"
        >
          <i class="ri-linkedin-fill text-lg"></i>
        </a>
      </div>
    </div>

    <!-- Profile Editor Modal -->
    <ProfileEditor
      v-if="showProfileEditor"
      :show="showProfileEditor"
      :open="showProfileEditor"
      :user="userData"
      @close="showProfileEditor = false"
      @update:profile="handleProfileUpdate"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import ProfileEditor from '@/components/ProfileEditor.vue'
import { TransitionRoot } from '@headlessui/vue'
import { useI18n } from 'vue-i18n'
import { useToast } from 'vue-toastification'
import { Teleport } from 'vue'

const { t } = useI18n()
const toast = useToast()

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
  viewAsFollower: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:profile', 'toggle-follower-view'])

const showProfileEditor = ref(false)
const dropdownOpen = ref(false)
const moreBtn = ref(null)
const dropdownRef = ref(null)
const dropdownPosition = ref({})

const bioColor = computed(() => props.bio_color)
const bioFont = computed(() => props.bio_font)

const hasSocialLinks = computed(() => {
  return Object.values(props.socialLinks).some(link => link && link.trim() !== '')
})

const userData = computed(() => ({
  name: props.username,
  handle: props.handle,
  avatar: props.avatarUrl,
  cover_photo: props.coverPhoto,
  bio: props.bio,
  bio_color: props.bio_color,
  bio_font: props.bio_font,
  social_links: props.socialLinks
}))

const formatNumber = (num) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  } else if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num.toString()
}

const toggleDropdown = () => {
  if (!dropdownOpen.value) {
    // Calculate position when opening
    const buttonRect = moreBtn.value.getBoundingClientRect()
    const viewportWidth = window.innerWidth
    const dropdownWidth = 256 // w-64 = 16rem = 256px
    
    // Check if dropdown would overflow right edge
    const rightEdge = buttonRect.right + dropdownWidth
    const leftEdge = buttonRect.left - dropdownWidth
    
    if (rightEdge > viewportWidth && leftEdge > 0) {
      // Position to the left of the button
      dropdownPosition.value = {
        left: `${buttonRect.left - dropdownWidth}px`,
        top: `${buttonRect.bottom + 8}px`
      }
    } else {
      // Position to the right of the button (default)
      dropdownPosition.value = {
        left: `${buttonRect.right}px`,
        top: `${buttonRect.bottom + 8}px`
      }
    }
  }
  
  dropdownOpen.value = !dropdownOpen.value
}

const toggleFollowerView = () => {
  dropdownOpen.value = false
  emit('toggle-follower-view', true)
}

const exitFollowerView = () => {
  dropdownOpen.value = false
  emit('toggle-follower-view', false)
}

const copyProfileLink = async () => {
  try {
    const profileUrl = `${window.location.origin}/${props.username}/posts`
    await navigator.clipboard.writeText(profileUrl)
    toast.success(t('profile_link_copied'))
    dropdownOpen.value = false
  } catch (err) {
    console.error('Failed to copy profile link:', err)
    toast.error(t('failed_to_copy_link'))
  }
}

const handleProfileUpdate = (updatedProfile) => {
  emit('update:profile', updatedProfile)
  showProfileEditor.value = false
}

// Close dropdown when clicking outside
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target) && 
      moreBtn.value && !moreBtn.value.contains(event.target)) {
    dropdownOpen.value = false
  }
}

// Handle window resize to reposition dropdown
const handleResize = () => {
  if (dropdownOpen.value) {
    dropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('resize', handleResize)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', handleResize)
})
</script>

