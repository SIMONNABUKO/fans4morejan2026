<template>
  <div class="relative w-full h-full overflow-hidden" ref="containerRef">
    <!-- Background div for color mirroring (for both videos and images) -->
    <div 
      v-if="isLandscape" 
      class="absolute inset-0 bg-black"
      :style="{ filter: 'blur(30px)', transform: 'scale(1.1)' }"
    >
      <!-- Video background -->
      <video 
        v-if="mediaType === 'video'"
        :src="mediaUrl" 
        class="w-full h-full object-cover opacity-50"
        muted
        loop
        playsinline
        ref="bgVideoRef"
      ></video>
      
      <!-- Image background -->
      <img
        v-else
        :src="mediaUrl"
        class="w-full h-full object-cover opacity-50"
        alt=""
        ref="bgImageRef"
      />
    </div>

    <!-- Media Element (Video or Image) -->
    <template v-if="mediaType === 'video'">
      <video
        ref="videoRef"
        :class="[
          'absolute',
          isLandscape ? 'max-h-full w-auto left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2' : 'w-full h-full object-cover'
        ]"
        @timeupdate="updateProgress"
        @loadedmetadata="onVideoLoaded"
        @loadeddata="onVideoDataLoaded"
        @canplaythrough="onVideoCanPlayThrough"
        @waiting="onVideoBuffering"
        @canplay="onVideoCanPlay"
        @playing="onVideoPlaying"
        @error="onMediaError"
        playsinline
        autoplay
        muted
        loop
      >
        <source :src="mediaUrl" type="video/mp4">
        <source :src="mediaUrl.replace('.mp4', '.webm')" type="video/webm">
        <source :src="mediaUrl.replace('.mp4', '.ogg')" type="video/ogg">
        Your browser does not support the video tag.
      </video>
    </template>
    <template v-else>
      <img
        ref="imageRef"
        :src="mediaUrl"
        :class="[
          'absolute',
          isLandscape ? 'max-h-full w-auto left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2' : 'w-full h-full object-cover'
        ]"
        @load="onImageLoaded"
        @error="onMediaError"
        alt="Media content"
      />
    </template>
    
    <!-- Loading Spinner (for buffering videos) -->
    <div 
      v-if="isLoading" 
      class="absolute inset-0 flex items-center justify-center bg-black/40 z-10"
    >
      <div class="loading-spinner">
        <div class="spinner"></div>
      </div>
    </div>

    <!-- Media Interface Overlay -->
    <div class="absolute inset-0 pointer-events-none">

      <!-- Right Interface -->
      <div class="absolute right-2 top-0 bottom-0 flex flex-col items-center pointer-events-auto">
        <!-- Top group -->
        <div class="flex flex-col items-center gap-4 mt-16">
          <button class="text-white hover:opacity-80 transition-opacity">
            <i class="ri-more-fill text-3xl"></i>
          </button>
          <button 
            v-if="mediaType === 'video'"
            @click="toggleMute"
            class="text-white hover:opacity-80 transition-opacity"
          >
            <i :class="[isMuted ? 'ri-volume-mute-fill' : 'ri-volume-up-fill', 'text-3xl']"></i>
          </button>
          <button class="text-white hover:opacity-80 transition-opacity">
            <i class="ri-search-line text-3xl"></i>
          </button>
        </div>

        <!-- Center group -->
        <div class="flex flex-col items-center gap-4 mt-auto mb-auto">
          <button 
            @click="$emit('scroll-up')"
            class="text-white hover:opacity-80 transition-opacity"
            :class="{ 'opacity-50 cursor-not-allowed': isFirst }"
            :disabled="isFirst"
          >
            <i class="ri-arrow-up-s-line text-4xl font-bold"></i>
          </button>
          <button 
            @click="$emit('scroll-down')"
            class="text-white hover:opacity-80 transition-opacity"
            :class="{ 'opacity-50 cursor-not-allowed': isLast }"
            :disabled="isLast"
          >
            <i class="ri-arrow-down-s-line text-4xl font-bold"></i>
          </button>
        </div>

        <!-- Bottom group -->
        <div class="flex flex-col items-center gap-6 mb-16">
          <div class="relative">
            <img 
              :src="userAvatar" 
              class="w-14 h-14 rounded-full border-2 border-white"
              alt="User avatar"
            />
            <div class="absolute -bottom-3 left-1/2 -translate-x-1/2 bg-primary text-white text-xs px-2 py-0.5 rounded-full whitespace-nowrap">
              Follow
            </div>
          </div>
          <button class="text-white hover:opacity-80 transition-opacity relative">
            <i class="ri-heart-fill text-3xl"></i>
            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 text-xs">92</span>
          </button>
          <button class="text-white hover:opacity-80 transition-opacity relative">
            <i class="ri-message-3-fill text-3xl"></i>
            <span class="absolute -bottom-2 left-1/2 -translate-x-1/2 text-xs">1</span>
          </button>
          <button class="text-white hover:opacity-80 transition-opacity">
            <i class="ri-money-dollar-circle-fill text-3xl"></i>
          </button>
          <button class="text-white hover:opacity-80 transition-opacity">
            <i class="ri-bookmark-fill text-3xl"></i>
          </button>
        </div>
      </div>

      <!-- Bottom Interface -->
      <div class="absolute bottom-0 left-0 right-0 p-4 pointer-events-auto">
        <!-- User Info and Caption -->
        <div class="mb-4 pr-16">
          <div class="flex items-center gap-2 mb-1">
            <span class="text-white font-semibold text-sm">{{ username }}</span>
            <i class="ri-verified-badge-fill text-primary text-sm"></i>
          </div>
          <p class="text-white text-sm">{{ caption }}</p>
          <div class="mt-1 text-white/80 text-xs space-x-1">
            <span v-for="(tag, index) in extractHashtags(caption)" :key="index" class="hover:underline cursor-pointer">
              {{ tag }}
            </span>
          </div>
        </div>

        <!-- Progress Bar (only for videos) -->
        <div v-if="mediaType === 'video'" class="flex items-center gap-4">
          <button 
            @click="togglePlay"
            class="text-white hover:opacity-80 transition-opacity"
          >
            <i v-if="!isPlaying" class="ri-play-fill text-3xl"></i>
            <i v-else class="ri-pause-fill text-3xl"></i>
          </button>

          <div class="flex-1">
            <div class="relative h-1 bg-white/30 rounded">
              <div 
                class="absolute left-0 top-0 h-full bg-white rounded"
                :style="{ width: `${progress}%` }"
              />
            </div>
            <div class="flex justify-between mt-1">
              <span class="text-white text-xs">{{ formatTime(currentTime) }}</span>
              <span class="text-white text-xs">{{ formatTime(duration) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick, watch } from 'vue'
import 'remixicon/fonts/remixicon.css'

const props = defineProps({
  mediaUrl: {
    type: String,
    required: true
  },
  mediaType: {
    type: String,
    default: 'video',
    validator: (value) => ['video', 'image'].includes(value)
  },
  userAvatar: {
    type: String,
    default: '/placeholder.svg?height=32&width=32'
  },
  username: {
    type: String,
    default: '@username'
  },
  caption: {
    type: String,
    default: 'Media caption goes here #hashtag'
  },
  isFirst: {
    type: Boolean,
    default: false
  },
  isLast: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['scroll-up', 'scroll-down', 'media-ended'])

// Refs
const containerRef = ref(null)
const videoRef = ref(null)
const imageRef = ref(null)
const bgVideoRef = ref(null)
const bgImageRef = ref(null)

// Media state
const isPlaying = ref(false)
const isMuted = ref(false)
const duration = ref(0)
const currentTime = ref(0)
const progress = ref(0)
const errorMessage = ref('')
const mediaWidth = ref(0)
const mediaHeight = ref(0)
const isLoading = ref(true) // Start with loading state
const bufferingTimeout = ref(null)
const loadingTimeout = ref(null)

// Computed properties
const isLandscape = computed(() => mediaWidth.value > mediaHeight.value)

// Video controls
const togglePlay = () => {
  if (props.mediaType === 'video' && videoRef.value) {
    if (isPlaying.value) {
      videoRef.value.pause()
      if (bgVideoRef.value) bgVideoRef.value.pause()
    } else {
      videoRef.value.play()
      if (bgVideoRef.value) bgVideoRef.value.play()
    }
    isPlaying.value = !isPlaying.value
  }
}

const toggleMute = () => {
  if (props.mediaType === 'video' && videoRef.value) {
    videoRef.value.muted = !videoRef.value.muted
    isMuted.value = videoRef.value.muted
  }
}

const updateProgress = () => {
  if (props.mediaType === 'video' && videoRef.value) {
    currentTime.value = videoRef.value.currentTime
    progress.value = (currentTime.value / duration.value) * 100
    
    // If we're getting timeupdate events, the video is playing
    // so we can hide the loading spinner after a short delay
    if (isLoading.value) {
      clearLoadingAfterDelay(100)
    }
  }
}

const onVideoLoaded = () => {
  if (props.mediaType === 'video' && videoRef.value) {
    duration.value = videoRef.value.duration
    mediaWidth.value = videoRef.value.videoWidth
    mediaHeight.value = videoRef.value.videoHeight
  }
}

const onVideoDataLoaded = () => {
  // Video data is loaded, but it might not be ready to play yet
  // We'll start a timer to hide the loading spinner
  clearLoadingAfterDelay(500)
}

const onVideoCanPlayThrough = () => {
  // Video can play through without buffering
  clearLoadingAfterDelay(100)
}

const onImageLoaded = () => {
  if (props.mediaType === 'image' && imageRef.value) {
    mediaWidth.value = imageRef.value.naturalWidth
    mediaHeight.value = imageRef.value.naturalHeight
    isLoading.value = false // Hide loader once image is loaded
  }
}

// Clear loading state after a delay
const clearLoadingAfterDelay = (delay) => {
  if (loadingTimeout.value) {
    clearTimeout(loadingTimeout.value)
  }
  
  loadingTimeout.value = setTimeout(() => {
    isLoading.value = false
  }, delay)
}

// Video buffering events
const onVideoBuffering = () => {
  // Clear any existing timeout
  if (bufferingTimeout.value) {
    clearTimeout(bufferingTimeout.value)
  }
  
  // Show loading spinner immediately
  isLoading.value = true
}

const onVideoCanPlay = () => {
  // Set a small timeout to prevent flickering if buffering is brief
  clearLoadingAfterDelay(300)
}

const onVideoPlaying = () => {
  // Video is playing, so it's not buffering
  clearLoadingAfterDelay(100)
  isPlaying.value = true
}

const onMediaError = (e) => {
  console.error('Media error:', e)
  errorMessage.value = `Failed to load ${props.mediaType}. Please try again later.`
  isLoading.value = false // Hide loader on error
}

// Extract hashtags from caption
const extractHashtags = (text) => {
  if (!text) return []
  const hashtags = text.match(/#[a-zA-Z0-9]+/g)
  return hashtags || []
}

// Time formatter
const formatTime = (time) => {
  const minutes = Math.floor(time / 60)
  const seconds = Math.floor(time % 60)
  return `${minutes}:${seconds.toString().padStart(2, '0')}`
}

// Keyboard controls
const handleKeydown = (event) => {
  if (props.mediaType === 'video') {
    switch (event.key.toLowerCase()) {
      case ' ':
        event.preventDefault()
        togglePlay()
        break
      case 'm':
        toggleMute()
        break
    }
  }
}

// Force hide loading after a maximum time
const forceHideLoading = () => {
  setTimeout(() => {
    isLoading.value = false
  }, 3000) // Force hide after 3 seconds max
}

// Lifecycle hooks
onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
  
  // Set initial loading state
  isLoading.value = true
  
  // For images, we need to check if they're already loaded
  if (props.mediaType === 'image') {
    if (imageRef.value && imageRef.value.complete) {
      onImageLoaded()
    } else {
      // Force hide loading after a timeout for images
      forceHideLoading()
    }
  } else {
    // For videos, force hide loading after a timeout
    forceHideLoading()
  }
  
  // Start playback
  nextTick(() => {
    play()
  })
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  
  // Clear any pending timeouts
  if (bufferingTimeout.value) {
    clearTimeout(bufferingTimeout.value)
  }
  
  if (loadingTimeout.value) {
    clearTimeout(loadingTimeout.value)
  }
})

// Watch for changes to the media URL
watch(() => props.mediaUrl, () => {
  // Reset loading state when media URL changes
  isLoading.value = true
  
  // Force hide loading after a timeout
  forceHideLoading()
})

const play = async () => {
  if (props.mediaType === 'video' && videoRef.value) {
    try {
      errorMessage.value = ''
      isLoading.value = true // Show loading state while attempting to play
      
      const playPromise = videoRef.value.play()
      if (playPromise !== undefined) {
        playPromise.then(() => {
          isPlaying.value = true
          if (bgVideoRef.value) bgVideoRef.value.play()
          
          // Hide loading after successful play
          clearLoadingAfterDelay(100)
        }).catch(error => {
          console.error('Error playing video:', error)
          isLoading.value = false // Hide loader on error
        })
      }
    } catch (error) {
      console.error('Error playing video:', error)
      isLoading.value = false // Hide loader on error
    }
  }
}

const pause = () => {
  if (props.mediaType === 'video' && videoRef.value) {
    videoRef.value.pause()
    if (bgVideoRef.value) bgVideoRef.value.pause()
    isPlaying.value = false
  }
}

// Expose methods to parent component
defineExpose({ play, pause })
</script>

<style scoped>
/* Loading spinner styles */
.loading-spinner {
  display: flex;
  justify-content: center;
  align-items: center;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 5px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>