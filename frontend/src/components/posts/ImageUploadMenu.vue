<template>
  <div class="relative">
    <button 
      @click="toggleMenu"
      class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary"
      tabindex="0"
    >
      <i class="ri-image-line text-xl"></i>
    </button>
    
    <!-- Dropdown Menu -->
    <div 
      v-if="isMenuOpen"
      class="absolute bottom-full left-0 mb-2 bg-surface-light dark:bg-surface-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark w-48 z-50"
    >
      <div class="py-1">
        <button 
          @click="handleUploadNew"
          class="w-full px-4 py-2 text-left text-text-light-primary dark:text-text-dark-primary hover:bg-background-light dark:hover:bg-background-dark flex items-center gap-2"
          tabindex="0"
        >
          <i class="ri-upload-2-line"></i>
          <span>Upload New</span>
        </button>
        <button 
          @click="handleFromVault"
          class="w-full px-4 py-2 text-left text-text-light-primary dark:text-text-dark-primary hover:bg-background-light dark:hover:bg-background-dark flex items-center gap-2"
          tabindex="0"
        >
          <i class="ri-gallery-line"></i>
          <span>From Vault</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const emit = defineEmits(['upload-new', 'from-vault'])

const isMenuOpen = ref(false)

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const handleUploadNew = () => {
  emit('upload-new')
  isMenuOpen.value = false
}

const handleFromVault = () => {
  emit('from-vault')
  isMenuOpen.value = false
}

// Close menu when clicking outside
const handleClickOutside = (event) => {
  if (isMenuOpen.value && !event.target.closest('.relative')) {
    isMenuOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>