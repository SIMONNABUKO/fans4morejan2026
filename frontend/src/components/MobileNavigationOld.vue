<template>
  <div class="lg:hidden">
    <!-- Floating Action Button for Home -->
    <button 
      v-if="isHomePage"
      @click="openPostModal"
      class="fixed bottom-20 right-4 z-30 w-14 h-14 bg-primary-light dark:bg-primary-dark rounded-full flex items-center justify-center shadow-lg hover:opacity-90 transition-opacity"
    >
      <i class="ri-add-line text-2xl text-white"></i>
    </button>

    <!-- Floating Action Button for Messages -->
    <button 
      v-if="isMessagesPage"
      @click="openNewMessageModal"
      class="fixed bottom-20 right-4 z-30 w-12 h-12 bg-primary-light dark:bg-primary-dark rounded-full flex items-center justify-center shadow-lg hover:opacity-90 transition-opacity"
    >
      <i class="ri-mail-add-line text-xl text-white"></i>
    </button>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-surface-light dark:bg-surface-dark border-t border-border-light dark:border-border-dark z-20">
      <div class="flex items-center justify-around h-16">
        <router-link 
          to="/" 
          class="flex flex-col items-center justify-center text-text-light-secondary dark:text-text-dark-secondary"
          active-class="text-primary-light dark:text-primary-dark"
        >
          <i class="ri-home-5-line text-2xl"></i>
        </router-link>
        <router-link 
          to="/search" 
          class="flex flex-col items-center justify-center text-text-light-secondary dark:text-text-dark-secondary"
          active-class="text-primary-light dark:text-primary-dark"
        >
          <i class="ri-search-line text-2xl"></i>
        </router-link>
        <router-link 
          to="/messages" 
          class="flex flex-col items-center justify-center text-text-light-secondary dark:text-text-dark-secondary"
          active-class="text-primary-light dark:text-primary-dark"
        >
          <i class="ri-message-3-line text-2xl"></i>
        </router-link>
        <div class="w-12">
          <!-- Spacer for floating button -->
        </div>
        <router-link 
          to="/notifications" 
          class="flex flex-col items-center justify-center text-text-light-secondary dark:text-text-dark-secondary relative"
          active-class="text-primary-light dark:text-primary-dark"
        >
          <i class="ri-notification-3-line text-2xl"></i>
          <span class="absolute -top-1 -right-1 bg-primary-light dark:bg-primary-dark text-white text-xs rounded-full h-5 min-w-[20px] flex items-center justify-center px-1">
            500
          </span>
        </router-link>
      </div>
    </nav>

    <!-- Post Modal -->
    <MobilePostModal 
      :is-open="isPostModalOpen"
      :should-reopen="true"
      @close="closePostModal"
      @open-upload-preview="openUploadPreview"
      @close-upload-preview="closeUploadPreview"
    />
    <MediaUploadModal 
      :is-open="isMediaUploadModalOpen"
      @close="closeUploadPreview"
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
import { ref, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import MobilePostModal from './posts/MobilePostModal.vue'
import MediaUploadModal from './posts/MediaUploadModal.vue'
import NewMessageModal from './messages/NewMessageModal.vue'
import ScheduledMessagesModal from './messages/ScheduledMessagesModal.vue'
import { useUploadStore } from '@/stores/uploadStore'

const route = useRoute()
const router = useRouter()
const isPostModalOpen = ref(false)
const isMediaUploadModalOpen = ref(false)
const isNewMessageModalOpen = ref(false)
const isScheduledMessagesModalOpen = ref(false)
const uploadStore = useUploadStore()

const isHomePage = computed(() => {
  return route.name === 'home'
})

const isMessagesPage = computed(() => {
  return route.path.startsWith('/messages')
})

const openPostModal = () => {
  isPostModalOpen.value = true
}

const closePostModal = () => {
  isPostModalOpen.value = false
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
  openPostModal()
}

watch(() => uploadStore.isPreviewModalOpen, (newValue) => {
  isMediaUploadModalOpen.value = newValue
})

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
</script>