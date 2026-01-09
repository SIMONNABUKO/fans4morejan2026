<template>
  <div class="p-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Blur Functionality Demo</h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Posts Content Demo -->
      <div class="space-y-4">
        <h4 class="font-medium text-gray-700 dark:text-gray-300">Posts Content Blur</h4>
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span>Current Level: {{ blurLevel }}</span>
            <span>{{ blurIntensities[blurLevel] }}</span>
          </div>
          <input 
            type="range" 
            min="0" 
            max="3" 
            :value="blurLevel"
            @input="handleBlurChange"
            class="w-full"
          />
        </div>
        
        <BlurWrapper :show-indicator="true">
          <div class="w-full h-32 bg-gradient-to-br from-pink-400 to-purple-500 rounded-lg flex items-center justify-center">
            <div class="text-white text-center">
              <i class="ri-image-line text-2xl mb-2"></i>
              <p class="text-sm">Posts Content</p>
            </div>
          </div>
        </BlurWrapper>
      </div>

      <!-- Messages Content Demo -->
      <div class="space-y-4">
        <h4 class="font-medium text-gray-700 dark:text-gray-300">Messages Content Blur</h4>
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span>Current Level: {{ blurLevel }}</span>
            <span>{{ blurIntensities[blurLevel] }}</span>
          </div>
          <input 
            type="range" 
            min="0" 
            max="3" 
            :value="blurLevel"
            @input="handleBlurChange"
            class="w-full"
          />
        </div>
        
        <BlurWrapper :show-indicator="true">
          <div class="w-full h-32 bg-gradient-to-br from-blue-400 to-green-500 rounded-lg flex items-center justify-center">
            <div class="text-white text-center">
              <i class="ri-message-2-line text-2xl mb-2"></i>
              <p class="text-sm">Messages Content</p>
            </div>
          </div>
        </BlurWrapper>
      </div>
    </div>

    <!-- Status -->
    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
      <h5 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Current Status</h5>
      <div class="space-y-1 text-sm">
        <div class="flex justify-between">
          <span>Content Blur:</span>
          <span :class="shouldBlurContent ? 'text-green-600' : 'text-gray-500'">
            {{ shouldBlurContent ? 'Enabled' : 'Disabled' }}
          </span>
        </div>
        <div class="flex justify-between">
          <span>Blur Level:</span>
          <span class="text-gray-700 dark:text-gray-300">
            {{ blurIntensities[blurLevel] }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useBlur } from '@/composables/useBlur'
import BlurWrapper from './BlurWrapper.vue'

const { 
  blurLevel, 
  blurIntensities,
  shouldBlurContent,
  updateBlur
} = useBlur()

const handleBlurChange = async (event) => {
  const level = parseInt(event.target.value)
  try {
    await updateBlur(level)
  } catch (error) {
    console.error('Error updating blur settings:', error)
  }
}
</script> 