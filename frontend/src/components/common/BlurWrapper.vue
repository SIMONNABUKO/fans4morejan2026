<template>
  <div 
    class="relative"
    :class="blurClass"
  >
    <!-- Content -->
    <slot />
    
    <!-- Blur Overlay -->
    <div 
      v-if="shouldShowBlur"
      class="absolute inset-0 bg-black/10 pointer-events-none z-10"
    ></div>
    
    <!-- Blur Indicator -->
    <div 
      v-if="shouldShowBlur && showIndicator"
      class="absolute top-2 right-2 bg-black/50 text-white px-2 py-1 rounded-full text-xs font-medium z-20"
    >
      <i class="ri-eye-off-line mr-1"></i>
      Blurred
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useBlur } from '@/composables/useBlur'

const props = defineProps({
  // Whether to show the blur indicator
  showIndicator: {
    type: Boolean,
    default: true
  },
  // Force blur regardless of settings (for testing)
  forceBlur: {
    type: Boolean,
    default: false
  },
  // Custom blur level override
  customBlurLevel: {
    type: Number,
    default: null
  }
})

const { 
  shouldBlurContent,
  getBlurClass
} = useBlur()

// Determine if content should be blurred
const shouldShowBlur = computed(() => {
  if (props.forceBlur) return true
  return shouldBlurContent.value
})

// Get the appropriate blur class
const blurClass = computed(() => {
  if (!shouldShowBlur.value) return ''
  
  if (props.customBlurLevel !== null) {
    // Use custom blur level
    const blurValues = {
      0: '',
      1: 'blur-content-1',
      2: 'blur-content-2', 
      3: 'blur-content-3'
    }
    return blurValues[props.customBlurLevel] || ''
  }
  
  return getBlurClass.value
})
</script> 