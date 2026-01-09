<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  media: {
    type: Object,
    required: true,
    validator: (value) => {
      return value && value.url && value.type
    }
  }
})

const emit = defineEmits(['close'])
</script>

<template>
  <div class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50">
    <div class="relative max-w-4xl w-full mx-4">
      <!-- Close Button -->
      <button
        @click="emit('close')"
        class="absolute -top-10 right-0 text-white hover:text-gray-300"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <!-- Media Content -->
      <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
        <div class="relative aspect-video">
          <!-- Video Player -->
          <video
            v-if="media.type === 'video'"
            :src="media.url"
            controls
            class="w-full h-full object-contain"
          >
            Your browser does not support the video tag.
          </video>

          <!-- Image Viewer -->
          <img
            v-else
            :src="media.url"
            class="w-full h-full object-contain"
            alt="Media preview"
          >
        </div>
      </div>
    </div>
  </div>
</template> 