<template>
  <div class="lg:hidden">
    <!-- Floating Action Button for Home -->
    <button 
      v-if="isHomePage && isCreator"
      @click="openPostModal"
      class="fixed bottom-24 right-4 z-30 w-14 h-14 bg-gradient-to-r from-primary-light to-primary-dark dark:from-primary-dark dark:to-primary-light rounded-full flex items-center justify-center shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 transition-all duration-200 ease-out"
      tabindex="0"
      aria-label="Create new post"
    >
      <i class="ri-add-line text-2xl text-white drop-shadow-sm"></i>
    </button>

    <!-- Floating Action Button for Messages -->
    <button 
      v-if="isMainMessagesPage"
      @click="openNewMessageModal"
      class="fixed bottom-24 right-4 z-30 w-14 h-14 bg-gradient-to-r from-primary-light to-primary-dark dark:from-primary-dark dark:to-primary-light rounded-full flex items-center justify-center shadow-xl hover:shadow-2xl hover:scale-105 active:scale-95 transition-all duration-200 ease-out"
      tabindex="0"
      aria-label="Start new message"
    >
      <i class="ri-mail-add-line text-xl text-white drop-shadow-sm"></i>
    </button>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border-t border-gray-200/50 dark:border-gray-700/50 z-20 shadow-lg">
      <div class="flex items-center justify-around h-20 px-2">
        <!-- Home -->
        <router-link 
          to="/" 
          class="flex flex-col items-center justify-center py-2 px-3 rounded-xl transition-all duration-200 ease-out group"
          active-class="text-primary-light dark:text-primary-dark bg-primary-light/10 dark:bg-primary-dark/10"
          tabindex="0"
          aria-label="Home"
        >
          <div class="relative">
            <i class="ri-home-5-line text-2xl transition-transform duration-200 group-hover:scale-110"></i>
            <div class="absolute inset-0 bg-primary-light/20 dark:bg-primary-dark/20 rounded-full scale-0 group-hover:scale-100 transition-transform duration-200"></div>
          </div>
          <span class="text-xs font-medium mt-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">Home</span>
        </router-link>

        <!-- Search -->
        <router-link 
          to="/search" 
          class="flex flex-col items-center justify-center py-2 px-3 rounded-xl transition-all duration-200 ease-out group"
          active-class="text-primary-light dark:text-primary-dark bg-primary-light/10 dark:bg-primary-dark/10"
          tabindex="0"
          aria-label="Search"
        >
          <div class="relative">
            <i class="ri-search-line text-2xl transition-transform duration-200 group-hover:scale-110"></i>
            <div class="absolute inset-0 bg-primary-light/20 dark:bg-primary-dark/20 rounded-full scale-0 group-hover:scale-100 transition-transform duration-200"></div>
          </div>
          <span class="text-xs font-medium mt-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">Search</span>
        </router-link>

        <!-- Messages -->
        <router-link 
          to="/messages" 
          class="flex flex-col items-center justify-center py-2 px-3 rounded-xl transition-all duration-200 ease-out group"
          active-class="text-primary-light dark:text-primary-dark bg-primary-light/10 dark:bg-primary-dark/10"
          tabindex="0"
          aria-label="Messages"
        >
          <div class="relative">
            <i class="ri-message-3-line text-2xl transition-transform duration-200 group-hover:scale-110"></i>
            <div class="absolute inset-0 bg-primary-light/20 dark:bg-primary-dark/20 rounded-full scale-0 group-hover:scale-100 transition-transform duration-200"></div>
          </div>
          <span class="text-xs font-medium mt-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">Messages</span>
        </router-link>

        <!-- Add Post Button (only for creators) -->
        <button
          v-if="isCreator"
          @click="openPostModal"
          class="flex flex-col items-center justify-center py-2 px-3 rounded-xl transition-all duration-200 ease-out group hover:bg-primary-light/10 dark:hover:bg-primary-dark/10 focus:outline-none focus:ring-2 focus:ring-primary-light/20 dark:focus:ring-primary-dark/20"
          tabindex="0"
          aria-label="Create post"
        >
          <div class="relative">
            <i class="ri-add-box-fill text-2xl transition-transform duration-200 group-hover:scale-110"></i>
            <div class="absolute inset-0 bg-primary-light/20 dark:bg-primary-dark/20 rounded-full scale-0 group-hover:scale-100 transition-transform duration-200"></div>
          </div>
          <span class="text-xs font-medium mt-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">Post</span>
        </button>

        <!-- Notifications -->
        <router-link 
          to="/notifications" 
          class="flex flex-col items-center justify-center py-2 px-3 rounded-xl transition-all duration-200 ease-out group relative"
          active-class="text-primary-light dark:text-primary-dark bg-primary-light/10 dark:bg-primary-dark/10"
          tabindex="0"
          aria-label="Notifications"
        >
          <div class="relative">
            <i class="ri-notification-3-line text-2xl transition-transform duration-200 group-hover:scale-110"></i>
            <div class="absolute inset-0 bg-primary-light/20 dark:bg-primary-dark/20 rounded-full scale-0 group-hover:scale-100 transition-transform duration-200"></div>
            
            <!-- Notification Badge -->
            <span v-if="unreadCount > 0" 
                  class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 min-w-[20px] flex items-center justify-center px-1 shadow-lg animate-pulse">
              {{ formatCount(unreadCount) }}
            </span>
          </div>
          <span class="text-xs font-medium mt-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">Alerts</span>
        </router-link>
      </div>

      <!-- Safe Area Bottom Spacer -->
      <div class="h-safe-area-bottom bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl"></div>
    </nav>

    <!-- Post Modal -->
    <MobilePostModal 
      :is-open="isPostModalOpen"
      :should-reopen="shouldReopenPostModal"
      @close="closePostModal"
      @open-upload-preview="openUploadPreview"
      @close-upload-preview="closeUploadPreview"
      @open-tag-modal="openTagModal"
    />
    
    <!-- Tag Creator Modal -->
    <TagCreatorModal
      :is-open="isTagModalOpen"
      :selected-users="uploadStore.taggedUsers"
      @close="closeTagModal"
      @tag-users="handleTagUsers"
      @reopen-post-modal="reopenPostModal"
    />
    
    <!-- Media Upload Modal -->
    <MediaUploadModal 
      :is-open="isMediaUploadModalOpen"
      :context-id="navigationUploadContextId"
      @close="closeUploadPreview"
      @upload="handleMediaUpload"
    />

    <!-- New Message Modal -->
    <NewMessageModal
      :is-open="isNewMessageModalOpen"
      @close="closeNewMessageModal"
      @open-scheduled="openScheduledMessagesModal"
    />
    <ScheduledMessagesModal
      :is-open="isScheduledMessagesModalOpen"
      @close="closeScheduledMessagesModal"
    />
  </div>
</template>

<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import MobilePostModal from './posts/MobilePostModal.vue'
import TagCreatorModal from './posts/TagCreatorModal.vue'
import MediaUploadModal from './posts/MediaUploadModal.vue'
import NewMessageModal from './messages/NewMessageModal.vue'
import ScheduledMessagesModal from './messages/ScheduledMessagesModal.vue'
import { useUploadStore } from '@/stores/uploadStore'
import { useNotificationService } from '../services/notificationService'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from 'vue-i18n'

const { t } = useI18n();

const route = useRoute()
const router = useRouter()
const isPostModalOpen = ref(false)
const isTagModalOpen = ref(false)
const isMediaUploadModalOpen = ref(false)
const isNewMessageModalOpen = ref(false)
const isScheduledMessagesModalOpen = ref(false)
const shouldReopenPostModal = ref(false)
const uploadStore = useUploadStore()
const notificationService = useNotificationService()
const authStore = useAuthStore()
const unreadCount = ref(0)

// Create a unique context ID for navigation-level media uploads
const navigationUploadContextId = 'navigation-media-upload'

const isHomePage = computed(() => {
  return route.name === 'home'
})

// Check if the current user has the creator role
const isCreator = computed(() => {
  return authStore.user?.role === 'creator'
})

// Keep the original isMessagesPage for other functionality if needed
const isMessagesPage = computed(() => {
  return route.path.startsWith('/messages')
})

// Add a new computed property that only returns true for the exact /messages path
const isMainMessagesPage = computed(() => {
  return route.path === '/messages'
})

// Format large notification counts (e.g., 1000 -> 999+)
const formatCount = (count) => {
  if (count > 999) {
    return '999+';
  }
  return count;
};

const openPostModal = () => {
  isPostModalOpen.value = true
  isTagModalOpen.value = false
}

const closePostModal = () => {
  isPostModalOpen.value = false
  shouldReopenPostModal.value = false
}

const openTagModal = () => {
  console.log('Opening tag modal')
  isTagModalOpen.value = true
  isPostModalOpen.value = false // Close post modal when opening tag modal
}

const closeTagModal = () => {
  isTagModalOpen.value = false
}

const reopenPostModal = () => {
  console.log('Reopening post modal after tagging')
  isTagModalOpen.value = false
  // Use a small timeout to ensure the tag modal has time to close
  setTimeout(() => {
    isPostModalOpen.value = true
  }, 50)
}

const handleTagUsers = (users) => {
  uploadStore.setTaggedUsers(users)
  // After setting tagged users, reopen the post modal
  reopenPostModal()
}

const openNewMessageModal = () => {
  isNewMessageModalOpen.value = true
  isScheduledMessagesModalOpen.value = false
}

const closeNewMessageModal = () => {
  isNewMessageModalOpen.value = false
}

const openUploadPreview = () => {
  isMediaUploadModalOpen.value = true
  uploadStore.openPreviewModal()
}

const closeUploadPreview = () => {
  isMediaUploadModalOpen.value = false
  uploadStore.closePreviewModal()
  shouldReopenPostModal.value = true
  openPostModal()
}

watch(() => uploadStore.isPreviewModalOpen, (newValue) => {
  isMediaUploadModalOpen.value = newValue
})

const handleMediaUpload = ({ files, permissions }) => {
  // Handle the uploaded media
  console.log('Media uploaded:', files, permissions)
  closeUploadPreview()
}

const reopenNewMessageModal = () => {
  isNewMessageModalOpen.value = true
}

const openScheduledMessagesModal = () => {
  isScheduledMessagesModalOpen.value = true
  isNewMessageModalOpen.value = false
}

const closeScheduledMessagesModal = () => {
  isScheduledMessagesModalOpen.value = false
  isNewMessageModalOpen.value = true // Reopen the NewMessageModal
}

// Initialize notification service and set up watchers
onMounted(() => {
  // Initialize notification service
  notificationService.initialize();
  
  // Get initial unread count
  unreadCount.value = notificationService.unreadCount.value;
  
  // Set up watcher for unread count changes
  const unsubscribe = watch(
    () => notificationService.unreadCount.value,
    (newCount) => {
      unreadCount.value = newCount;
    }
  );
  
  // Mark all notifications as read when navigating to notifications page
  const routeWatcher = watch(
    () => route.path,
    (newPath) => {
      if (newPath === '/notifications') {
        notificationService.markAllAsRead();
      }
    }
  );
  
  // Store the unsubscribe functions for cleanup
  onUnmounted(() => {
    unsubscribe();
    routeWatcher();
  });
});
</script>

<style scoped>
/* Custom CSS for enhanced mobile navigation */
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Safe area support for devices with home indicators */
.h-safe-area-bottom {
  height: env(safe-area-inset-bottom);
}

/* Enhanced focus states for accessibility */
button:focus-visible,
a:focus-visible {
  outline: 2px solid currentColor;
  outline-offset: 2px;
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 4px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 2px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}
</style>