<template>
  <div class="bg-background-light dark:bg-background-dark">
    <div class="max-w-3xl mx-auto px-4">
      <div class="flex items-center justify-between">
        <div class="flex gap-6 md:gap-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="$emit('update:modelValue', tab.id)"
            class="relative py-4 px-1 font-medium text-sm transition-colors duration-200"
            :class="[
              modelValue === tab.id 
                ? 'text-primary-light dark:text-primary-dark' 
                : 'text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary'
            ]"
          >
            <div class="flex items-center gap-2">
              <i :class="tab.icon" class="text-lg"></i>
              <span class="hidden sm:inline">{{ t(tab.label) }}</span>
              <span v-if="tab.count !== undefined" class="ml-1 px-2 py-0.5 bg-gray-100 dark:bg-gray-800 text-xs rounded-full">
                {{ tab.count }}
              </span>
            </div>
            
            <!-- Active Indicator -->
            <div
              v-if="modelValue === tab.id"
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-light dark:bg-primary-dark rounded-full"
            ></div>
            
            <!-- Hover Indicator -->
            <div
              v-else
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-transparent group-hover:bg-gray-200 dark:group-hover:bg-gray-700 rounded-full transition-colors duration-200"
            ></div>
          </button>
        </div>
        
        <!-- Additional Actions -->
        <div class="flex items-center gap-2">
          <button
            class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors"
            :aria-label="t('more_options')"
          >
            <i class="ri-more-fill text-lg"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  modelValue: {
    type: String,
    required: true
  }
})

defineEmits(['update:modelValue'])

const tabs = [
  { 
    id: 'posts', 
    label: 'posts',
    icon: 'ri-file-list-line',
    count: undefined
  },
  { 
    id: 'media', 
    label: 'media',
    icon: 'ri-image-line',
    count: undefined
  }
]
</script>

<style scoped>
/* Enhanced glassmorphism effects */
.backdrop-blur-xl {
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
}

.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Enhanced hover effects */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

/* Active indicator animation */
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
</style>

