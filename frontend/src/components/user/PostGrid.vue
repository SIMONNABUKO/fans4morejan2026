<template>
  <div class="relative w-full">
    <!-- Unlock Media Overlay with media count (when user doesn't have permission) -->
    <div 
      v-if="!userHasPermission && !isPreviewVideo && hasRequiredPermissions"
      class="absolute inset-0 z-20 flex items-center justify-center gap-3"
    >
      <!-- Main Unlock Button -->
      <button 
        class="group relative bg-white/10 dark:bg-black/20 backdrop-blur-md rounded-2xl px-6 py-3 flex items-center gap-3 transition-all duration-300 hover:scale-105 hover:bg-white/20 dark:hover:bg-black/30 border border-white/20 dark:border-white/10 shadow-xl"
        @click="handleUnlock"
      >
        <!-- Glow effect -->
        <div class="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-2xl blur-sm group-hover:blur-md transition-all duration-300"></div>
        
        <!-- Content -->
        <div class="relative flex items-center gap-3">
          <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center shadow-lg">
            <i class="ri-lock-unlock-line text-white text-sm font-semibold"></i>
          </div>
          <div class="flex flex-col items-start">
            <span class="text-white dark:text-white text-sm font-semibold tracking-wide">Unlock Media</span>
            <span class="text-white/70 dark:text-white/70 text-xs">Access exclusive content</span>
          </div>
        </div>
      </button>
      
      <!-- Media Count Badge -->
      <div v-if="!mediaCountDisplay.showMultiple" class="bg-white/10 dark:bg-black/20 backdrop-blur-md rounded-xl px-4 py-2 flex items-center gap-2 border border-white/20 dark:border-white/10 shadow-lg">
        <div class="w-6 h-6 bg-white/20 dark:bg-black/30 rounded-full flex items-center justify-center">
          <i :class="mediaCountDisplay.icon + ' text-white text-sm'"></i>
        </div>
        <span class="text-white text-sm font-semibold">{{ mediaCountDisplay.count }}</span>
      </div>
      
      <!-- Multiple Media Count Badge -->
      <div v-else class="bg-white/10 dark:bg-black/20 backdrop-blur-md rounded-xl px-4 py-2 flex items-center gap-3 border border-white/20 dark:border-white/10 shadow-lg">
        <div class="flex items-center gap-2">
          <div class="w-5 h-5 bg-white/20 dark:bg-black/30 rounded-full flex items-center justify-center">
            <i class="ri-camera-line text-white text-xs"></i>
          </div>
          <span class="text-white text-xs font-semibold">{{ mediaCountDisplay.imageCount }}</span>
        </div>
        <div class="w-px h-4 bg-white/30"></div>
        <div class="flex items-center gap-2">
          <div class="w-5 h-5 bg-white/20 dark:bg-black/30 rounded-full flex items-center justify-center">
            <i class="ri-video-line text-white text-xs"></i>
          </div>
          <span class="text-white text-xs font-semibold">{{ mediaCountDisplay.videoCount }}</span>
        </div>
      </div>
    </div>

    <!-- Grid Layout -->
    <div :class="gridClass">
      <div 
        v-for="(item, index) in displayedMedia" 
        :key="index" 
        class="relative"
      >
        <BlurWrapper :blur-type="isMessageMedia ? 'message' : 'sensitive'" :show-indicator="false">
          <!-- Image Media -->
          <img 
            v-if="item.type === 'image' || (!item.type && !isPreviewVideo)"
            :src="item.url" 
            :alt="item.alt || description"
            class="w-full rounded-lg aspect-[4/5] object-cover cursor-pointer"
            @click.stop="!userHasPermission ? handleUnlock() : openBundle(index)"
          />
          
          <!-- Video Media (including preview videos) -->
          <div 
            v-else-if="item.type === 'video' || isPreviewVideo"
            class="relative w-full rounded-lg aspect-[4/5] cursor-pointer"
            @click.stop="isPreviewVideo ? openVideoModal(index) : (!userHasPermission ? handleUnlock() : openVideoModal(index))"
            :ref="el => setVideoRef(el, index)"
          >
            <video
              :src="item.url"
              class="w-full h-full object-cover rounded-lg"
              muted
              preload="metadata"
              loop
              :ref="el => setVideoElementRef(el, index)"
              @loadedmetadata="onVideoLoaded(index)"
              @loadeddata="onVideoLoaded(index)"
              @ended="onVideoEnded(index)"
              @click.stop="isPreviewVideo ? openVideoModal(index) : (!userHasPermission ? handleUnlock() : openVideoModal(index))"
            />
            <!-- Video Play Button Overlay (hidden when playing) -->
            <div 
              v-show="!isVideoPlaying(index)"
              class="absolute inset-0 flex items-center justify-center bg-black/20"
            >
              <div class="w-12 h-12 bg-white/90 rounded-full flex items-center justify-center">
                <i class="ri-play-fill text-gray-800 text-xl ml-1"></i>
              </div>
            </div>
            <!-- Video Duration Badge -->
            <div class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-1.5 py-0.5 rounded duration-badge">
              <i class="ri-time-line mr-0.5"></i>
              <span class="duration-text">{{ item.duration || '0:00' }}</span>
            </div>
            
            <!-- Preview Video CTA Button (transparent, non-blocking) -->
            <div
              v-if="isPreviewVideo"
              class="absolute top-3 left-3 z-10"
            >
              <button
                @click.stop="handleUnlock"
                class="group bg-white/10 dark:bg-black/20 backdrop-blur-md text-white text-xs px-4 py-2 rounded-xl border border-white/20 dark:border-white/10 hover:bg-white/20 dark:hover:bg-black/30 transition-all duration-300 flex items-center gap-2 shadow-lg"
              >
                <div class="w-4 h-4 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                  <i class="ri-lock-unlock-line text-white text-xs"></i>
                </div>
                <span class="font-semibold tracking-wide">Unlock Full Video</span>
              </button>
            </div>
          </div>
        </BlurWrapper>
        <MediaOverlay 
          v-if="userHasPermission"
          :stats="item.stats" 
          @like="handleMediaLike(item.id)" 
          @bookmark="handleMediaBookmark(item.id)" 
          @stats="handleMediaStats(item.id)" 
        />
      </div>
    </div>

    <!-- Bundle Modal -->
    <ImageBundleModal
      v-if="showBundle"
      :is-open="showBundle"
      :media-items="media"
      :initial-index="selectedIndex"
      :author="author"
      :description="description"
      @close="showBundle = false"
    />

    <!-- Video Player Modal -->
    <VideoPlayerModal
      v-if="showVideoModal"
      :is-open="showVideoModal"
      :media-items="videoMediaItems"
      :initial-index="selectedVideoIndex"
      :is-preview-video="isPreviewVideo"
      @close="closeVideoModal"
      @showUnlockModal="handlePreviewVideoEnded"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import MediaOverlay from './MediaOverlay.vue'
import ImageBundleModal from './ImageBundleModal.vue'
import VideoPlayerModal from '@/components/posts/VideoPlayerModal.vue'
import BlurWrapper from '@/components/common/BlurWrapper.vue'

const props = defineProps({
  media: {
    type: Array,
    required: true
  },
  originalMedia: {
    type: Array,
    required: true
  },
  author: {
    type: Object,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  userHasPermission: {
    type: Boolean,
    default: false
  },
  totalMediaCount: {
    type: Number,
    default: 0
  },
  isPreviewVideo: {
    type: Boolean,
    default: false
  },
  requiredPermissions: {
    type: Array,
    default: () => []
  },
  permissionSets: {
    type: Array,
    default: () => []
  },
  isMessageMedia: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['unlock', 'mediaLike', 'mediaBookmark', 'mediaStats'])

// Check if there are permission sets that require unlocking
const hasRequiredPermissions = computed(() => {
  return props.permissionSets && Array.isArray(props.permissionSets) && props.permissionSets.length > 0
})

const showBundle = ref(false)
const selectedIndex = ref(0)
const showVideoModal = ref(false)
const selectedVideoIndex = ref(0)

// Video auto-play management
const videoRefs = ref({})
const videoElements = ref({})
const playingVideos = ref(new Set())
const intersectionObserver = ref(null)

// Video management functions
const setVideoRef = (el, index) => {
  if (el) {
    videoRefs.value[index] = el
  }
}

const setVideoElementRef = (el, index) => {
  if (el) {
    videoElements.value[index] = el
  }
}

const isVideoPlaying = (index) => {
  return playingVideos.value.has(index)
}

const onVideoLoaded = (index) => {
  const video = videoElements.value[index]
  if (video) {
    // Format duration as MM:SS
    const formatDuration = (seconds) => {
      if (!seconds || isNaN(seconds)) return '0:00'
      const mins = Math.floor(seconds / 60)
      const secs = Math.floor(seconds % 60)
      return `${mins}:${secs.toString().padStart(2, '0')}`
    }
    
    // Update the duration display
    const videoContainer = video.closest('.relative')
    const durationText = videoContainer?.querySelector('.duration-text')
    if (durationText && video.duration) {
      durationText.textContent = formatDuration(video.duration)
    }
  }
}

const onVideoEnded = (index) => {
  // Don't remove from playing videos since it will loop
  // The video will automatically restart due to the loop attribute
}

const playVideo = (index) => {
  const video = videoElements.value[index]
  if (video && !video.paused) {
    playingVideos.value.add(index)
  }
}

const pauseVideo = (index) => {
  const video = videoElements.value[index]
  if (video) {
    video.pause()
    playingVideos.value.delete(index)
  }
}

const pauseAllVideos = () => {
  Object.keys(videoElements.value).forEach(index => {
    pauseVideo(parseInt(index))
  })
}

// Intersection Observer for auto-play
const setupIntersectionObserver = () => {
  if (intersectionObserver.value) {
    intersectionObserver.value.disconnect()
  }

  intersectionObserver.value = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        const index = parseInt(entry.target.dataset.videoIndex)
        const video = videoElements.value[index]
        
        if (entry.isIntersecting && video) {
          // Video is in view, start playing
          video.play().then(() => {
            playingVideos.value.add(index)
          }).catch((error) => {
            console.log('Auto-play failed:', error)
          })
        } else if (video) {
          // Video is out of view, pause it
          pauseVideo(index)
        }
      })
    },
    {
      threshold: 0.5, // 50% of video must be visible
      rootMargin: '0px 0px -10% 0px' // Start playing slightly before fully in view
    }
  )

  // Observe all video containers
  Object.keys(videoRefs.value).forEach(index => {
    const videoRef = videoRefs.value[index]
    if (videoRef) {
      videoRef.dataset.videoIndex = index
      intersectionObserver.value.observe(videoRef)
    }
  })
}

// Lifecycle hooks
onMounted(() => {
  // Setup observer after a short delay to ensure DOM is ready
  setTimeout(() => {
    setupIntersectionObserver()
  }, 100)
})

onUnmounted(() => {
  if (intersectionObserver.value) {
    intersectionObserver.value.disconnect()
  }
  pauseAllVideos()
})

const gridClass = computed(() => {
  switch (Math.min(props.media.length, 4)) {
    case 1: return 'grid-cols-1'
    case 2: return 'grid grid-cols-2 gap-1'
    case 3: return 'grid grid-cols-2 grid-rows-2 gap-1'
    case 4: return 'grid grid-cols-2 grid-rows-2 gap-1'
    default: return 'grid grid-cols-2 grid-rows-2 gap-1'
  }
})

const displayedMedia = computed(() => {
  return props.media.slice(0, 4)
})

// Calculate media type breakdown
const mediaTypeBreakdown = computed(() => {
  // If user has permission, use the actual media types
  if (props.userHasPermission) {
    const images = props.media.filter(item => item.type === 'image' || !item.type).length
    const videos = props.media.filter(item => item.type === 'video').length
    
    return {
      images,
      videos,
      total: props.media.length
    }
  } else {
    // For locked content, use the original media types to determine what icons to show
    const images = props.originalMedia.filter(item => item.type === 'image' || !item.type).length
    const videos = props.originalMedia.filter(item => item.type === 'video').length
    
    return {
      images,
      videos,
      total: props.originalMedia.length
    }
  }
})

// Determine what to display in the media count badge
const mediaCountDisplay = computed(() => {
  const { images, videos } = mediaTypeBreakdown.value
  
  if (images > 0 && videos > 0) {
    // Mixed media - show both counts
    return {
      showMultiple: true,
      imageCount: images,
      videoCount: videos
    }
  } else if (videos > 0) {
    // Videos only
    return {
      showMultiple: false,
      icon: 'ri-video-line',
      count: videos
    }
  } else {
    // Images only (or no type specified)
    return {
      showMultiple: false,
      icon: 'ri-camera-line',
      count: images
    }
  }
})

const openBundle = (index) => {
  if (props.userHasPermission) {
    selectedIndex.value = index
    showBundle.value = true
  } else {
    handleUnlock()
  }
}

const openVideoModal = (gridIndex) => {
  if (props.userHasPermission) {
    // Find which video this is in the grid
    const videoItems = props.media.filter(item => item.type === 'video')
    let videoCount = 0
    let videoIndex = 0
    
    for (let i = 0; i <= gridIndex; i++) {
      if (props.media[i] && props.media[i].type === 'video') {
        if (i === gridIndex) {
          videoIndex = videoCount
          break
        }
        videoCount++
      }
    }
    
    selectedVideoIndex.value = videoIndex
    showVideoModal.value = true
  } else {
    handleUnlock()
  }
}

const closeVideoModal = () => {
  showVideoModal.value = false
  selectedVideoIndex.value = 0
}

const handlePreviewVideoEnded = () => {
  // For preview videos, the unlock modal is now handled within the video modal
  // This function is called when user clicks the "Unlock Full Video" button
  closeVideoModal()
  handleUnlock()
}

// Transform media items for video modal
const videoMediaItems = computed(() => {
  return props.media
    .filter(item => item.type === 'video')
    .map((item, index) => ({
      id: item.id || index,
      mediaUrl: item.url,
      mediaType: 'video',
      userAvatar: props.author.avatar || '/placeholder.svg',
      username: props.author.username || props.author.name || 'User',
      caption: props.description || ''
    }))
})

const handleUnlock = () => {
  console.log('🔍 PostGrid: handleUnlock called, emitting unlock event')
  console.log('🔍 PostGrid: Required permissions:', props.requiredPermissions)
  console.log('🔍 PostGrid: User has permission:', props.userHasPermission)
  console.log('🔍 PostGrid: Media count:', props.media.length)
  console.log('🔍 PostGrid: Original media count:', props.originalMedia.length)
  emit('unlock')
}

const handleMediaLike = (mediaId) => {
  emit('mediaLike', mediaId)
}

const handleMediaBookmark = (mediaId) => {
  emit('mediaBookmark', mediaId)
}

const handleMediaStats = (mediaId) => {
  emit('mediaStats', mediaId)
}
</script>