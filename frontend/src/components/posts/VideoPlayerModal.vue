<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div 
        v-if="isOpen"
        class="fixed inset-0 z-50 bg-black flex items-center justify-center"
        @click="handleBackdropClick"
      >
        <!-- Close Button -->
        <button 
          @click="closeModal"
          class="absolute top-6 right-6 z-20 w-10 h-10 bg-black/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-black/60 transition-all duration-200 hover:scale-110"
        >
          <i class="ri-close-line text-xl"></i>
        </button>
        
        <!-- Previous Video Button -->
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 -translate-x-4"
          enter-to-class="opacity-100 translate-x-0"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 translate-x-0"
          leave-to-class="opacity-0 -translate-x-4"
        >
          <button 
            v-if="currentIndex > 0"
            @click="previousVideo"
            class="absolute left-6 z-20 w-12 h-12 bg-black/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-black/60 transition-all duration-200 hover:scale-110"
          >
            <i class="ri-arrow-left-line text-xl"></i>
          </button>
        </Transition>

        <!-- Current Video Container -->
        <Transition
          enter-active-class="transition duration-300 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
          mode="out-in"
        >
          <div 
            :key="currentIndex"
            class="relative w-full h-full flex items-center justify-center"
            @touchstart="handleTouchStart"
            @touchend="handleTouchEnd"
            @mousedown="handleMouseDown"
            @mouseup="handleMouseUp"
          >
            <!-- Video Element -->
            <video
              v-if="currentVideo"
              :src="currentVideo.mediaUrl"
              class="max-w-full max-h-full object-contain"
              ref="videoElement"
              @loadedmetadata="onVideoLoaded"
              @timeupdate="onTimeUpdate"
              @ended="onVideoEnded"
              @play="onVideoPlay"
              @pause="onVideoPause"
              @click="togglePlayPause"
            />

            <!-- Custom Video Controls Overlay -->
            <div 
              v-if="currentVideo"
              class="absolute inset-0 flex flex-col justify-between p-6 pointer-events-none"
            >
              <!-- Top Controls -->
              <div class="flex items-center justify-between pointer-events-auto">
                <!-- Video Counter -->
                <div class="bg-black/40 backdrop-blur-sm rounded-full px-4 py-2 text-white text-sm font-medium">
                  {{ currentIndex + 1 }} / {{ mediaItems.length }}
                </div>
                
                <!-- Video Info -->
                <div class="flex items-center gap-3">
                  <div class="bg-black/40 backdrop-blur-sm rounded-full px-4 py-2 text-white text-sm">
                    <span class="font-medium">{{ formatTime(currentTime) }}</span>
                    <span class="mx-2">/</span>
                    <span>{{ formatTime(duration) }}</span>
                  </div>
                </div>
              </div>

              <!-- Center Play/Pause Button -->
              <div class="flex items-center justify-center pointer-events-auto">
                <!-- Show play button when video is paused -->
                <button
                  v-if="!isPlaying"
                  @click="togglePlayPause"
                  class="w-20 h-20 bg-black/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-black/60 transition-all duration-200 hover:scale-110"
                >
                  <i class="ri-play-fill text-3xl ml-1"></i>
                </button>
                
                <!-- Call to Action Button (after watching preview, when paused) -->
                <div
                  v-if="hasWatchedPreview && isPreviewVideo && !isPlaying"
                  class="flex flex-col items-center gap-4"
                >
                  <div class="text-center">
                    <div class="text-white text-lg font-semibold mb-2">Want to see more?</div>
                    <div class="text-white/80 text-sm mb-4">Unlock the full video to continue watching</div>
                  </div>
                  <button
                    @click="emit('showUnlockModal')"
                    class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-8 py-3 rounded-full font-semibold hover:from-purple-600 hover:to-pink-600 transition-all duration-200 hover:scale-105 shadow-lg"
                  >
                    <i class="ri-lock-unlock-line mr-2"></i>
                    Unlock Full Video
                  </button>
                  <button
                    @click="togglePlayPause"
                    class="text-white/80 hover:text-white transition-colors text-sm"
                  >
                    <i class="ri-replay-line mr-1"></i>
                    Watch Again
                  </button>
                </div>
              </div>

              <!-- Bottom Controls -->
              <div class="flex flex-col gap-4 pointer-events-auto">
                <!-- Progress Bar -->
                <div class="w-full">
                  <div 
                    class="w-full h-1 bg-white/20 rounded-full cursor-pointer"
                    @click="seekTo"
                    ref="progressBar"
                  >
                    <div 
                      class="h-full bg-white rounded-full transition-all duration-100"
                      :style="{ width: progressPercentage + '%' }"
                    ></div>
                  </div>
                </div>

                <!-- Bottom Controls Row -->
                <div class="flex items-center justify-between">
                  <!-- Left Controls -->
                  <div class="flex items-center gap-4">
                    <!-- Play/Pause Button -->
                    <button
                      @click="togglePlayPause"
                      class="w-12 h-12 bg-black/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-black/60 transition-all duration-200"
                    >
                      <i v-if="isPlaying" class="ri-pause-fill text-xl"></i>
                      <i v-else class="ri-play-fill text-xl ml-0.5"></i>
                    </button>

                    <!-- Volume Control -->
                    <div class="flex items-center gap-2">
                      <button
                        @click="toggleMute"
                        class="w-10 h-10 bg-black/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-black/60 transition-all duration-200"
                      >
                        <i v-if="isMuted" class="ri-volume-mute-line text-lg"></i>
                        <i v-else-if="volume < 0.5" class="ri-volume-down-line text-lg"></i>
                        <i v-else class="ri-volume-up-line text-lg"></i>
                      </button>
                      <div class="w-16">
                        <input
                          type="range"
                          min="0"
                          max="1"
                          step="0.1"
                          v-model="volume"
                          @input="onVolumeChange"
                          class="w-full h-1 bg-white/20 rounded-full appearance-none cursor-pointer slider"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Right Controls -->
                  <div class="flex items-center gap-3">
                    <!-- Speed Control -->
                    <div class="relative">
                      <button
                        @click="toggleSpeedMenu"
                        class="w-10 h-10 bg-black/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-black/60 transition-all duration-200"
                      >
                        <span class="text-sm font-medium">{{ playbackSpeed }}x</span>
                      </button>
                      <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 scale-100"
                        leave-to-class="opacity-0 scale-95"
                      >
                        <div
                          v-if="showSpeedMenu"
                          class="absolute bottom-full right-0 mb-2 bg-black/80 backdrop-blur-sm rounded-lg p-2 min-w-[80px]"
                        >
                          <button
                            v-for="speed in [0.5, 0.75, 1, 1.25, 1.5, 2]"
                            :key="speed"
                            @click="setPlaybackSpeed(speed)"
                            class="w-full text-left px-3 py-1 text-white text-sm hover:bg-white/20 rounded transition-colors"
                            :class="{ 'bg-white/20': playbackSpeed === speed }"
                          >
                            {{ speed }}x
                          </button>
                        </div>
                      </Transition>
                    </div>

                    <!-- Fullscreen Button -->
                    <button
                      @click="toggleFullscreen"
                      class="w-10 h-10 bg-black/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-black/60 transition-all duration-200"
                    >
                      <i class="ri-fullscreen-line text-lg"></i>
                    </button>
                  </div>
                </div>

                <!-- Video Info Overlay -->
                <div class="bg-gradient-to-t from-black/80 via-black/40 to-transparent p-4 rounded-lg">
                  <div class="flex items-center gap-3 mb-2">
                    <img 
                      :src="currentVideo?.userAvatar" 
                      :alt="currentVideo?.username"
                      class="w-10 h-10 rounded-full border-2 border-white/20"
                    />
                    <div>
                      <div class="text-white font-semibold">{{ currentVideo?.username }}</div>
                      <div class="text-white/80 text-sm">{{ formatTime(currentTime) }} / {{ formatTime(duration) }}</div>
                    </div>
                  </div>
                  <p class="text-white/90 text-sm leading-relaxed">{{ currentVideo?.caption }}</p>
                </div>
              </div>
            </div>
          </div>
        </Transition>

        <!-- Next Video Button -->
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 translate-x-4"
          enter-to-class="opacity-100 translate-x-0"
          leave-active-class="transition duration-200 ease-in"
          leave-from-class="opacity-100 translate-x-0"
          leave-to-class="opacity-0 translate-x-4"
        >
          <button 
            v-if="currentIndex < mediaItems.length - 1"
            @click="nextVideo"
            class="absolute right-6 z-20 w-12 h-12 bg-black/40 backdrop-blur-sm rounded-full flex items-center justify-center text-white hover:bg-black/60 transition-all duration-200 hover:scale-110"
          >
            <i class="ri-arrow-right-line text-xl"></i>
          </button>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  mediaItems: {
    type: Array,
    required: true
  },
  initialIndex: {
    type: Number,
    default: 0
  },
  isPreviewVideo: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'showUnlockModal'])

const currentIndex = ref(props.initialIndex)
const videoElement = ref(null)
const progressBar = ref(null)
const touchStartY = ref(0)
const touchEndY = ref(0)
const mouseStartY = ref(0)
const mouseEndY = ref(0)

// Video state
const isPlaying = ref(false)
const isMuted = ref(false)
const volume = ref(1)
const currentTime = ref(0)
const duration = ref(0)
const playbackSpeed = ref(1)
const showSpeedMenu = ref(false)
const hasWatchedPreview = ref(false)

const currentVideo = computed(() => {
  return props.mediaItems[currentIndex.value] || null
})

const progressPercentage = computed(() => {
  if (duration.value === 0) return 0
  return (currentTime.value / duration.value) * 100
})

const closeModal = () => {
  emit('close')
}

const handleBackdropClick = (event) => {
  if (event.target === event.currentTarget) {
    closeModal()
  }
}

const nextVideo = () => {
  if (currentIndex.value < props.mediaItems.length - 1) {
    currentIndex.value++
    resetVideoState()
  }
}

const previousVideo = () => {
  if (currentIndex.value > 0) {
    currentIndex.value--
    resetVideoState()
  }
}

const resetVideoState = () => {
  isPlaying.value = false
  currentTime.value = 0
  duration.value = 0
  showSpeedMenu.value = false
}

const togglePlayPause = () => {
  if (!videoElement.value) return
  
  if (isPlaying.value) {
    videoElement.value.pause()
  } else {
    videoElement.value.play()
  }
}

const toggleMute = () => {
  if (!videoElement.value) return
  
  isMuted.value = !isMuted.value
  videoElement.value.muted = isMuted.value
}

const onVolumeChange = () => {
  if (!videoElement.value) return
  
  videoElement.value.volume = volume.value
  if (volume.value === 0) {
    isMuted.value = true
  } else if (isMuted.value) {
    isMuted.value = false
  }
}

const toggleSpeedMenu = () => {
  showSpeedMenu.value = !showSpeedMenu.value
}

const setPlaybackSpeed = (speed) => {
  if (!videoElement.value) return
  
  playbackSpeed.value = speed
  videoElement.value.playbackRate = speed
  showSpeedMenu.value = false
}

const toggleFullscreen = () => {
  if (!videoElement.value) return
  
  if (document.fullscreenElement) {
    document.exitFullscreen()
  } else {
    videoElement.value.requestFullscreen()
  }
}

const seekTo = (event) => {
  if (!videoElement.value || !progressBar.value) return
  
  const rect = progressBar.value.getBoundingClientRect()
  const clickX = event.clientX - rect.left
  const percentage = clickX / rect.width
  const newTime = percentage * duration.value
  
  videoElement.value.currentTime = newTime
  currentTime.value = newTime
}

const formatTime = (seconds) => {
  if (isNaN(seconds)) return '0:00'
  
  const mins = Math.floor(seconds / 60)
  const secs = Math.floor(seconds % 60)
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

const onVideoLoaded = () => {
  if (videoElement.value) {
    duration.value = videoElement.value.duration
    videoElement.value.volume = volume.value
    videoElement.value.muted = isMuted.value
    videoElement.value.playbackRate = playbackSpeed.value
  }
}

const onTimeUpdate = () => {
  if (videoElement.value) {
    currentTime.value = videoElement.value.currentTime
  }
}

const onVideoEnded = () => {
  isPlaying.value = false
  currentTime.value = 0
  
  // If this is a preview video, mark as watched (but don't prevent replay)
  if (props.isPreviewVideo) {
    hasWatchedPreview.value = true
  }
}

const onVideoPlay = () => {
  isPlaying.value = true
}

const onVideoPause = () => {
  isPlaying.value = false
}

const handleTouchStart = (event) => {
  touchStartY.value = event.touches[0].clientY
}

const handleTouchEnd = (event) => {
  touchEndY.value = event.changedTouches[0].clientY
  const diff = touchStartY.value - touchEndY.value
  
  if (Math.abs(diff) > 50) { // Minimum swipe distance
    if (diff > 0) {
      // Swipe up - next video
      nextVideo()
    } else {
      // Swipe down - previous video
      previousVideo()
    }
  }
}

const handleMouseDown = (event) => {
  mouseStartY.value = event.clientY
}

const handleMouseUp = (event) => {
  mouseEndY.value = event.clientY
  const diff = mouseStartY.value - mouseEndY.value
  
  if (Math.abs(diff) > 50) { // Minimum swipe distance
    if (diff > 0) {
      // Swipe up - next video
      nextVideo()
    } else {
      // Swipe down - previous video
      previousVideo()
    }
  }
}

// Keyboard navigation
const handleKeydown = (event) => {
  if (!props.isOpen) return
  
  switch (event.key) {
    case 'Escape':
      closeModal()
      break
    case 'ArrowRight':
    case 'ArrowUp':
      nextVideo()
      break
    case 'ArrowLeft':
    case 'ArrowDown':
      previousVideo()
      break
    case ' ':
      event.preventDefault()
      togglePlayPause()
      break
    case 'm':
      toggleMute()
      break
    case 'f':
      toggleFullscreen()
      break
  }
}

// Click outside speed menu to close
const handleClickOutside = (event) => {
  if (showSpeedMenu.value && !event.target.closest('.speed-menu')) {
    showSpeedMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  document.addEventListener('click', handleClickOutside)
  document.body.style.overflow = 'hidden'
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
  document.removeEventListener('click', handleClickOutside)
  document.body.style.overflow = ''
})

// Watch for prop changes
watch(() => props.initialIndex, (newIndex) => {
  currentIndex.value = newIndex
  resetVideoState()
})
</script>

<style scoped>
.slider {
  -webkit-appearance: none;
  appearance: none;
  background: transparent;
  cursor: pointer;
}

.slider::-webkit-slider-track {
  background: rgba(255, 255, 255, 0.2);
  height: 4px;
  border-radius: 2px;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  background: white;
  height: 12px;
  width: 12px;
  border-radius: 50%;
  cursor: pointer;
  margin-top: -4px;
}

.slider::-moz-range-track {
  background: rgba(255, 255, 255, 0.2);
  height: 4px;
  border-radius: 2px;
  border: none;
}

.slider::-moz-range-thumb {
  background: white;
  height: 12px;
  width: 12px;
  border-radius: 50%;
  cursor: pointer;
  border: none;
}
</style>
