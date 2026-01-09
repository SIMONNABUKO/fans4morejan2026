<template>
<router-link 
  :to="to"
  class="flex items-center justify-between py-3 px-4 rounded-lg text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light-hover dark:hover:bg-surface-dark-hover transition-colors"
>
  <div class="flex items-center gap-3">
    <i :class="icon" class="text-xl text-primary-light dark:text-primary-dark"></i>
    <span>{{ label }}</span>
    <span 
      v-if="beta"
      class="px-1.5 py-0.5 text-[10px] font-medium bg-primary-light dark:bg-primary-dark text-white rounded"
    >
      Beta
    </span>
  </div>
  <i class="ri-arrow-right-s-line text-xl text-text-light-secondary dark:text-text-dark-secondary"></i>
</router-link>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    required: true
  },
  label: {
    type: String,
    required: true
  },
  beta: {
    type: Boolean,
    default: false
  },
  to: {
    type: String,
    required: true
  }
})
</script>

