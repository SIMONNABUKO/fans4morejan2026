<template>
  <div class="relative">
    <!-- Banner -->
    <div class="w-full h-24 overflow-hidden">
      <img
        :src="coverPhoto"
        :alt="t('profile_banner')"
        class="w-full h-full object-cover"
      />
    </div>

    <!-- Profile Edit Button (only for own profile) -->
    <button v-if="props.isOwnProfile" @click="showProfileEditor = true" class="absolute top-0 right-0 mt-2 mr-2 px-3 py-1 bg-primary text-white rounded-full shadow hover:bg-primary-dark transition">
      <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2H7v-2a2 2 0 012-2h2v2a2 2 0 01-2 2z"></path></svg>
      {{ t('edit_profile') }}
    </button>

    <!-- Profile Info Section -->
    <div class="px-1">
      <div class="flex justify-between items-end">
        <!-- Profile Picture -->
        <div class="relative -mt-12">
          <img
            :src="avatarUrl"
            :alt="username"
            class="w-24 h-24 rounded-full border-4 border-background object-cover"
          />
        </div>

        <!-- Navigation buttons -->
        <div class="flex items-center gap-2 mt-2 relative">
          <!-- Show Profile button only for own profile -->
          <button v-if="props.isOwnProfile" @click="showProfileEditor = true" class="px-4 py-1.5 rounded-full bg-black/50 text-white hover:bg-black/60 transition">
            {{ t('profile') }}
          </button>
          <!-- Show action buttons and follow/following only for other users -->
          <template v-else>
            <button class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
              <i class="ri-mail-line text-xl"></i>
            </button>
            <button class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
              <i class="ri-money-dollar-circle-line text-xl"></i>
            </button>
            <button class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
              <i class="ri-more-line text-xl"></i>
            </button>
            <button
              class="px-4 py-1.5 rounded-full transition font-medium"
              :class="props.isFollowing
                ? 'bg-white text-primary-light border border-primary-light hover:bg-primary-light hover:text-white dark:bg-background-dark dark:text-primary-dark dark:border-primary-dark dark:hover:bg-primary-dark dark:hover:text-white'
                : 'bg-primary-light text-white hover:bg-primary-dark dark:bg-primary-dark dark:text-white dark:hover:bg-primary-light'"
            >
              {{ props.isFollowing ? t('following') : t('follow') }}
            </button>
          </template>
        </div>
      </div>

      <!-- User Info -->
      <div class="space-y-2 mt-4">
        <div class="flex items-center gap-2">
          <span class="text-xl font-bold flex items-center gap-1">
            {{ username }}
            <i v-if="role === 'admin'" class="ri-verified-badge-fill text-primary text-sm"></i>
          </span>
        </div>
        <div class="text-sm text-muted-foreground">{{ handle }}</div>
        <div class="text-sm text-muted-foreground">{{ status }}</div>
      </div>

      <!-- Stats -->
      <div class="flex gap-6 mt-4">
        <div class="flex items-center gap-1">
          <i class="ri-heart-fill text-red-500"></i>
          <span class="font-medium">{{ formatNumber(stats.likes) }}</span>
        </div>
        <div class="flex items-center gap-1">
          <i class="ri-user-follow-fill text-blue-500"></i>
          <span class="font-medium">{{ formatNumber(stats.followers) }}</span>
        </div>
        <div class="flex items-center gap-1">
          <i class="ri-file-list-fill text-green-500"></i>
          <span class="font-medium">{{ stats.posts }}</span>
        </div>
        <div class="flex items-center gap-1">
          <i class="ri-image-fill text-yellow-500"></i>
          <span class="font-medium">{{ stats.media }}</span>
        </div>
      </div>

      <!-- Bio -->
      <div class="mt-4 space-y-1 text-sm">
        <p :style="{ color: bioColor, fontFamily: bioFont }">{{ bio }}</p>
      </div>

      <!-- Social links -->
      <div v-if="hasSocialLinks" class="flex gap-4 mt-4">
        <a v-if="socialLinks.facebook" :href="socialLinks.facebook" target="_blank" rel="noopener noreferrer">
          <i class="ri-facebook-fill text-blue-600"></i>
        </a>
        <a v-if="socialLinks.twitter" :href="socialLinks.twitter" target="_blank" rel="noopener noreferrer">
          <i class="ri-twitter-fill text-blue-400"></i>
        </a>
        <a v-if="socialLinks.instagram" :href="socialLinks.instagram" target="_blank" rel="noopener noreferrer">
          <i class="ri-instagram-line text-pink-600"></i>
        </a>
        <a v-if="socialLinks.linkedin" :href="socialLinks.linkedin" target="_blank" rel="noopener noreferrer">
          <i class="ri-linkedin-fill text-blue-700"></i>
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

const { t } = useI18n()

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
  },
  isOwnProfile: {
    type: Boolean,
    default: false
  },
  isFollowing: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:profile', 'view-as-follower'])

// State for modal visibility
const showProfileEditor = ref(false)
const dropdownOpen = ref(false)
const moreBtn = ref(null)

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
  const profileUrl = window.location.origin + '/profile/' + props.username
  navigator.clipboard.writeText(profileUrl)
  dropdownOpen.value = false
  // Optionally show a toast/notification
}

const bioColor = computed(() => props.bio_color || '#222222')
const bioFont = computed(() => props.bio_font || 'inherit')
</script>

