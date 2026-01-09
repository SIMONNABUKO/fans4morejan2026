<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 bg-background-light dark:bg-background-dark">
      <!-- Header -->
      <div class="absolute top-0 left-0 right-0 p-4 flex justify-between items-center z-10">
        <button 
          class="flex items-center gap-2 text-text-light-primary dark:text-text-dark-primary hover:text-text-light-secondary dark:hover:text-text-dark-secondary transition-colors"
          @click="$emit('close')"
        >
          <i class="ri-arrow-left-line text-xl"></i>
          <span>Bundle</span>
        </button>
        <div class="flex gap-4">
          <button class="text-text-light-primary dark:text-text-dark-primary hover:text-text-light-secondary dark:hover:text-text-dark-secondary transition-colors">
            <i class="ri-more-fill text-xl"></i>
          </button>
          <button 
            class="text-text-light-primary dark:text-text-dark-primary hover:text-text-light-secondary dark:hover:text-text-dark-secondary transition-colors"
            @click="$emit('close')"
          >
            <i class="ri-close-line text-xl"></i>
          </button>
        </div>
      </div>

      <!-- Thumbnails Section -->
      <div 
        class="absolute left-[1px] top-1/2 -translate-y-1/2 z-10 flex flex-col gap-2"
      >
        <!-- Toggle Button -->
        <button 
          class="px-3 py-2 bg-surface-light/50 dark:bg-surface-dark/50 rounded-lg text-text-light-primary dark:text-text-dark-primary flex items-center gap-2 hover:bg-surface-light/70 dark:hover:bg-surface-dark/70 transition-colors w-fit"
          @click="toggleThumbnails"
        >
          <template v-if="!isThumbnailsHidden">
            <span>Bundle</span>
            <i class="ri-arrow-left-s-line"></i>
          </template>
          <i v-else class="ri-image-line"></i>
        </button>

        <!-- Thumbnails Container -->
        <div 
          v-show="!isThumbnailsHidden" 
          class="w-16 max-h-[70vh] overflow-y-auto bg-surface-light/50 dark:bg-surface-dark/50 rounded-lg p-2 transition-transform duration-300"
          :class="isThumbnailsHidden ? '-translate-x-full' : 'translate-x-0'"
        >
          <div class="space-y-2">
            <button
              v-for="(media, index) in mediaItems"
              :key="media.id"
              @click="scrollToMedia(index)"
              class="w-full aspect-square rounded-lg overflow-hidden relative"
              :class="currentIndex === index ? 'ring-2 ring-primary-light dark:ring-primary-dark' : ''"
            >
              <!-- Image Thumbnail -->
              <img 
                v-if="media.type === 'image'"
                :src="media.url" 
                :alt="media.alt"
                class="w-full h-full object-cover"
              />
              <!-- Video Thumbnail -->
              <div v-else-if="media.type === 'video'" class="relative w-full h-full">
                <video
                  :src="media.url"
                  class="w-full h-full object-cover"
                  muted
                  preload="metadata"
                />
                <div class="absolute inset-0 flex items-center justify-center bg-black/20">
                  <div class="w-6 h-6 bg-white/90 rounded-full flex items-center justify-center">
                    <i class="ri-play-fill text-gray-800 text-sm ml-0.5"></i>
                  </div>
                </div>
              </div>
            </button>
          </div>
        </div>
      </div>

      <!-- Main Media Container -->
      <div 
        class="h-full overflow-y-auto snap-y snap-mandatory"
        @scroll="handleScroll"
        ref="scrollContainer"
      >
        <div 
          v-for="media in mediaItems" 
          :key="media.id"
          class="h-full w-full snap-start flex items-center justify-center relative"
        >
          <!-- Image Media -->
          <img 
            v-if="media.type === 'image'"
            :src="media.url" 
            :alt="media.alt"
            class="max-h-full max-w-full object-contain"
          />
          
          <!-- Video Media -->
          <video
            v-else-if="media.type === 'video'"
            :src="media.url"
            class="max-h-full max-w-full object-contain"
            controls
            autoplay
            muted
          />

          <!-- Bottom Info -->
          <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-background-light/80 dark:from-background-dark/80 to-transparent">
            <div class="flex items-start justify-between text-text-light-primary dark:text-text-dark-primary">
              <div>
                <div class="flex items-center gap-2">
                  <span class="font-semibold">{{ author.username }}</span>
                  <i class="ri-checkbox-circle-fill text-primary-light dark:text-primary-dark text-sm"></i>
                  <span class="text-sm text-text-light-secondary dark:text-text-dark-secondary">@{{ author.handle }}</span>
                </div>
                <p class="mt-1 text-sm">{{ description }}</p>
              </div>
              <div class="flex flex-col gap-2">
                <button class="p-2 bg-surface-light/50 dark:bg-surface-dark/50 rounded-full hover:bg-surface-light/70 dark:hover:bg-surface-dark/70 transition-colors group">
                  <i class="ri-heart-line text-text-light-primary dark:text-text-dark-primary group-hover:text-primary-light dark:group-hover:text-primary-dark text-xl"></i>
                </button>
                <button class="p-2 bg-surface-light/50 dark:bg-surface-dark/50 rounded-full hover:bg-surface-light/70 dark:hover:bg-surface-dark/70 transition-colors group relative">
                  <i class="ri-bar-chart-2-line text-text-light-primary dark:text-text-dark-primary group-hover:text-primary-light dark:group-hover:text-primary-dark text-xl"></i>
                  <span 
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
                  >
                    0
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue'

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
  author: {
    type: Object,
    required: true
  },
  description: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['close'])

const currentIndex = ref(props.initialIndex)
const scrollContainer = ref(null)
const isThumbnailsHidden = ref(false)

const formatNumber = (num) => {
  if (num >= 1000) {
    return Math.floor(num / 1000) + 'k'
  }
  return num
}

const toggleThumbnails = () => {
  isThumbnailsHidden.value = !isThumbnailsHidden.value
}

const scrollToMedia = (index) => {
  if (scrollContainer.value) {
    const targetElement = scrollContainer.value.children[index]
    if (targetElement) {
      targetElement.scrollIntoView({ behavior: 'smooth' })
    }
  }
}

const handleScroll = () => {
  if (scrollContainer.value) {
    const containerHeight = scrollContainer.value.clientHeight
    const scrollPosition = scrollContainer.value.scrollTop
    const newIndex = Math.round(scrollPosition / containerHeight)
    if (newIndex !== currentIndex.value) {
      currentIndex.value = newIndex
    }
  }
}

// Keyboard navigation
const handleKeydown = (event) => {
  if (event.key === 'ArrowUp' || event.key === 'ArrowLeft') {
    if (currentIndex.value > 0) {
      scrollToMedia(currentIndex.value - 1)
    }
  } else if (event.key === 'ArrowDown' || event.key === 'ArrowRight') {
    if (currentIndex.value < props.mediaItems.length - 1) {
      scrollToMedia(currentIndex.value + 1)
    }
  } else if (event.key === 'Escape') {
    emit('close')
  } else if (event.key === 'b') {
    // Toggle thumbnails with 'b' key
    toggleThumbnails()
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
  scrollToMedia(currentIndex.value)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})

watch(() => props.initialIndex, (newIndex) => {
  currentIndex.value = newIndex
  scrollToMedia(newIndex)
})
</script>

<style scoped>
.snap-y {
  scroll-snap-type: y mandatory;
}

.snap-start {
  scroll-snap-align: start;
}

/* Hide scrollbar but maintain functionality */
.overflow-y-auto {
  scrollbar-width: none;
  -ms-overflow-style: none;
}
.overflow-y-auto::-webkit-scrollbar {
  display: none;
}

/* Smooth transitions for thumbnails panel */
.transition-transform {
  transition-property: transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
</style>

