<template>
  <section class="mb-8">
    <div class="flex items-center gap-4 mb-4">
      <div class="flex items-center gap-2">
        <button
          class="p-1 rounded-full transition-colors text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark"
          @click="$emit('add')"
        >
          <i class="ri-add-line text-xl"></i>
        </button>
        <h2 class="text-lg font-medium text-text-light-primary dark:text-text-dark-primary">
          {{ title }}
        </h2>
      </div>
    </div>
  
    <div class="grid grid-cols-2 gap-4">
      <MediaCard
        v-for="item in items"
        :key="item.id"
        :item="item"
      />
    </div>
  </section>
</template>

<script setup>
import MediaCard from './MediaCard.vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  items: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['add'])
</script>