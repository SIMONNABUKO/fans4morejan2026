<template>
  <div class="h-screen bg-black">
    <VideoPlayerModal
      v-if="isModalOpen"
      :is-open="isModalOpen"
      :media-items="safeMediaItems"
      :initial-index="currentMediaIndex"
      @close="closeVideoModal"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, provide } from 'vue'
import { useFeedStore } from '@/stores/feedStore'
import VideoPlayerModal from '../components/posts/VideoPlayerModal.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

// Initialize store
const feedStore = useFeedStore()

// Modal state
const isModalOpen = ref(false)
const currentMediaIndex = ref(0)

// Provide a flag to indicate if previews are active
// This can be used by other components to avoid accessing undefined data
provide('previewsActive', isModalOpen)

// Safe version of media items that won't cause errors
const safeMediaItems = computed(() => {
  if (!isModalOpen.value || !feedStore.posts || feedStore.posts.length === 0) {
    return []
  }
  
  return feedStore.posts
    .filter(post => post && post.media_previews && post.media_previews.length > 0)
    .map(post => {
      // Get all media previews from the post
      const previews = post.media_previews || []
      
      // Determine media type based on URL extension
      const mediaPreview = previews[0] || {}
      const mediaUrl = mediaPreview.url || ''
      const isVideo = mediaUrl.endsWith('.mp4') || 
                     mediaUrl.endsWith('.webm') || 
                     mediaUrl.endsWith('.ogg') ||
                     mediaUrl.includes('video')
      
      return {
        id: post.id || Date.now(), // Fallback ID if none exists
        mediaUrl: mediaUrl,
        mediaType: isVideo ? 'video' : 'image',
        userAvatar: post.user?.avatar || '/placeholder.svg?height=32&width=32',
        username: post.user?.username ? `@${post.user.username}` : '@user',
        caption: post.content || t('no_caption')
      }
    })
    .filter(item => item.mediaUrl) // Only include items with a URL
})

// Modal functions
const closeVideoModal = async () => {
  // Set modal to closed
  isModalOpen.value = false
  
  // Reset the current index
  currentMediaIndex.value = 0
  
  // Wait for the next tick to ensure Vue has updated the DOM
  await nextTick()
  
  // Force a refresh of the feed store to ensure clean state
  // This is a defensive measure to prevent stale data issues
  setTimeout(() => {
    if (!isModalOpen.value) {
      feedStore.$reset && feedStore.$reset()
    }
  }, 100)
}

// Lifecycle hooks
onMounted(async () => {
  try {
    // Reset any previous state
    isModalOpen.value = false
    currentMediaIndex.value = 0
    
    // Fetch feed previews
    await feedStore.fetchFeedPreviews()
    
    // Wait for the next tick to ensure data is processed
    await nextTick()
    
    // Only open the modal if we have media items
    if (feedStore.posts && feedStore.posts.length > 0 && 
        feedStore.posts.some(post => post.media_previews && post.media_previews.length > 0)) {
      isModalOpen.value = true
    }
  } catch (error) {
    console.error('Error loading previews:', error)
  }
})
</script>