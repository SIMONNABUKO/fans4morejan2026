<template>
   <div class="hidden lg:block bg-surface-light dark:bg-surface-dark rounded-lg p-4 space-y-4">
    <!-- User Input Section -->
    <div class="flex items-start gap-3">
      <div class="relative">
        <img 
          :src="userAvatar" 
          alt="User Avatar" 
          class="w-10 h-10 rounded-full object-cover"
        />
        <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-accent-success rounded-full border-2 border-surface-light dark:border-surface-dark"></div>
      </div>
      <div class="flex-1">
        <div class="text-text-light-secondary dark:text-text-dark-secondary text-sm mb-1">
          <button class="hover:underline">
            No Previews Promotion (click to learn more)
          </button>
        </div>
        <textarea
          v-model="postContent"
          placeholder="Type a message..."
          class="w-full bg-transparent text-text-light-primary dark:text-text-dark-primary placeholder-text-light-secondary dark:placeholder-text-dark-secondary text-base outline-none resize-none"
          rows="3"
        ></textarea>
      </div>
    </div>

    <!-- Post Options -->
    <div class="space-y-1">
      <label 
        v-for="(option, index) in postOptions" 
        :key="index"
        class="flex items-center gap-2 text-text-light-secondary dark:text-text-dark-secondary text-sm py-1 cursor-pointer"
      >
        <div class="relative flex items-center">
          <input 
            type="checkbox" 
            v-model="option.checked"
            class="w-4 h-4 border border-border-light dark:border-border-dark rounded bg-transparent appearance-none cursor-pointer checked:border-primary-light dark:checked:border-primary-dark"
          >
          <i class="ri-check-line absolute left-0 text-sm text-primary-light dark:text-primary-dark pointer-events-none" 
             :class="option.checked ? 'opacity-100' : 'opacity-0'"></i>
        </div>
        <span>{{ option.label }}</span>
      </label>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <button 
          v-for="(action, index) in actionButtons" 
          :key="index"
          class="text-primary-light dark:text-primary-dark hover:opacity-80 transition-opacity"
        >
          <i :class="action.icon" class="text-xl"></i>
        </button>
      </div>
      <div class="flex items-center gap-3">
        <button class="text-primary-light dark:text-primary-dark hover:opacity-80 transition-opacity">
          <i class="ri-mic-line text-xl"></i>
        </button>
        <button class="bg-primary-light dark:bg-primary-dark hover:opacity-90 transition-opacity text-white px-6 py-1.5 rounded text-sm font-medium">
          Post
        </button>
      </div>
    </div>

    <!-- Reaction Section -->
    <div class="flex items-center gap-2">
      <div class="relative">
        <button class="relative">
          <img 
            :src="userAvatar" 
            alt="User Avatar" 
            class="w-10 h-10 rounded-full object-cover"
          />
          <div class="absolute -top-1 -right-1 bg-primary-light dark:bg-primary-dark text-white text-xs w-5 h-5 rounded-full flex items-center justify-center border-2 border-surface-light dark:border-surface-dark">
            +
          </div>
        </button>
      </div>
      <div class="w-10 h-10 rounded-full bg-primary-light dark:bg-primary-dark flex items-center justify-center border-2 border-yellow-400">
        <i class="ri-heart-3-fill text-white text-xl"></i>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const postContent = ref('')
const userAvatar = 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-hdk1YbcRvmO3KhClIfB1E9kMkm6wCZ.png'

const postOptions = ref([
  { label: 'Promote Post', checked: false },
  { label: 'Pin to Timeline', checked: false },
  { label: 'Add to Walls', checked: false },
  { label: 'Lock Replies', checked: false },
])

const actionButtons = [
  { icon: 'ri-image-line' },
  { icon: 'ri-calendar-line' },
  { icon: 'ri-delete-bin-line' },
  { icon: 'ri-loop-left-line' },
  { icon: 'ri-bar-chart-line' },
  { icon: 'ri-menu-line' },
]
</script>

<style scoped>
textarea::placeholder {
  opacity: 0.5;
}
</style>

