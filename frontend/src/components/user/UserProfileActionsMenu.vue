<template>
  <div class="relative">
    <transition name="fade">
      <div v-if="isOpen" class="absolute right-0 mt-2 w-56 bg-surface-light dark:bg-surface-dark rounded-lg shadow-lg z-50 border border-border-light dark:border-border-dark">
        <ul class="py-2">
          <li v-for="item in menuItems" :key="item.id">
            <button
              @click="$emit('action', item.id); $emit('close')"
              class="w-full flex items-center gap-3 px-4 py-2 text-left text-sm text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark/80 transition-colors"
            >
              <i :class="item.icon" class="text-lg"></i>
              <span>{{ item.label }}</span>
            </button>
          </li>
        </ul>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  isMuted: {
    type: Boolean,
    default: false
  },
  isBlocked: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'action'])

// Debug logging
console.log('🔍 UserProfileActionsMenu props:', {
  isMuted: props.isMuted,
  isBlocked: props.isBlocked
})

const menuItems = computed(() => [
  { id: 'add-to-list', label: 'Add To List', icon: 'ri-list-check' },
  { id: 'copy-profile-link', label: 'Copy Profile Link', icon: 'ri-upload-2-line' },
  { id: 'earnings', label: 'Earnings', icon: 'ri-money-dollar-circle-line' },
  { id: 'report', label: 'Report', icon: 'ri-flag-line' },
  { 
    id: 'mute', 
    label: props.isMuted ? 'Unmute user' : 'Mute user', 
    icon: props.isMuted ? 'ri-volume-up-line' : 'ri-user-forbid-line' 
  },
  { 
    id: 'block', 
    label: props.isBlocked ? 'Unblock user' : 'Block user', 
    icon: props.isBlocked ? 'ri-checkbox-circle-line' : 'ri-forbid-line' 
  },
])
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.15s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style> 