<template>
<div class="bg-surface-light dark:bg-surface-dark rounded-lg p-4 space-y-3">
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
      <img 
        :src="moderator.avatar" 
        :alt="moderator.username"
        class="w-10 h-10 rounded-full"
      />
      <div>
        <h3 class="font-medium text-text-light-primary dark:text-text-dark-primary">{{ moderator.username }}</h3>
        <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
          Added {{ formatDate(moderator.addedAt) }}
        </p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <button 
        @click="$emit('edit', moderator.id)"
        class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary rounded-full hover:bg-surface-light dark:hover:bg-surface-dark"
      >
        <i class="ri-edit-line text-lg"></i>
      </button>
      <button 
        @click="$emit('remove', moderator.id)"
        class="p-2 text-accent-danger hover:text-accent-danger rounded-full hover:bg-surface-light dark:hover:bg-surface-dark"
      >
        <i class="ri-delete-bin-line text-lg"></i>
      </button>
    </div>
  </div>

  <div class="flex flex-wrap gap-2">
    <span 
      v-for="permission in moderator.permissions"
      :key="permission"
      class="px-2 py-1 text-xs bg-surface-light dark:bg-surface-dark text-text-light-secondary dark:text-text-dark-secondary rounded-full"
    >
      {{ permission }}
    </span>
  </div>
</div>
</template>

<script setup>
const props = defineProps({
  moderator: {
    type: Object,
    required: true
  }
})

defineEmits(['edit', 'remove'])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric'
  })
}
</script>

