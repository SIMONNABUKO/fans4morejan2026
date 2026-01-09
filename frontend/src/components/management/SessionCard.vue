<template>
<div class="bg-surface-light dark:bg-surface-dark rounded-lg p-4 space-y-3">
  <div class="flex items-center justify-between">
    <div class="space-y-1">
      <h3 class="font-medium text-text-light-primary dark:text-text-dark-primary">{{ session.name }}</h3>
      <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
        Created {{ formatDate(session.createdAt) }}
      </p>
    </div>
    <div class="flex items-center gap-2">
      <button 
        v-if="session.status === 'unclaimed'"
        @click="$emit('copy', session.id)"
        class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary rounded-full hover:bg-surface-light dark:hover:bg-surface-dark"
        title="Copy access link"
      >
        <i class="ri-file-copy-line text-lg"></i>
      </button>
      <button 
        @click="$emit('delete', session.id)"
        class="p-2 text-accent-danger hover:text-accent-danger rounded-full hover:bg-surface-light dark:hover:bg-surface-dark"
        title="Delete session"
      >
        <i class="ri-delete-bin-line text-lg"></i>
      </button>
    </div>
  </div>

  <div class="flex items-center gap-2">
    <span 
      class="px-2 py-1 text-xs rounded-full"
      :class="statusClasses[session.status]"
    >
      {{ session.status }}
    </span>
    <span class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
      {{ session.permissions.join(', ') }}
    </span>
  </div>
</div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  session: {
    type: Object,
    required: true
  }
})

defineEmits(['copy', 'delete'])

const statusClasses = {
  unclaimed: 'bg-primary-light/20 dark:bg-primary-dark/20 text-primary-light dark:text-primary-dark',
  copied: 'bg-accent-warning/20 text-accent-warning',
  claimed: 'bg-accent-success/20 text-accent-success',
  expired: 'bg-text-light-secondary/20 dark:bg-text-dark-secondary/20 text-text-light-secondary dark:text-text-dark-secondary'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  })
}
</script>

