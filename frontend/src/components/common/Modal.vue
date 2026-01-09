<template>
  <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-background-light/75 dark:bg-background-dark/75 transition-opacity" aria-hidden="true" @click="$emit('close')"></div>
    
    <div class="inline-block w-full max-w-lg bg-surface-light dark:bg-surface-dark rounded-lg text-left overflow-hidden shadow-xl transform transition-all">
      <div class="bg-surface-light dark:bg-surface-dark px-4 pt-5 pb-4 sm:p-6 sm:pb-4 text-text-light-primary dark:text-text-dark-primary">
        <slot class="w-full"></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
defineEmits(['close']);
</script>