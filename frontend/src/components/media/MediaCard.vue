<template>
  <router-link 
    :to="{ name: 'album-view', params: { id: item.id }}"
    class="relative aspect-square rounded-lg overflow-hidden cursor-pointer group bg-background-light dark:bg-background-dark"
  >
    <img 
      v-if="item.thumbnail"
      :src="item.thumbnail"
      :alt="item.title"
      class="w-full h-full object-cover"
    />
    <div 
      v-else
      class="w-full h-full flex items-center justify-center"
    >
      <i class="ri-image-line text-4xl text-text-light-secondary dark:text-text-dark-secondary"></i>
    </div>
    <div class="absolute inset-0 flex flex-col justify-end p-3 bg-gradient-to-t from-black/80 to-transparent">
      <h3 class="text-white font-medium">{{ item.title }}</h3>
      <p v-if="item.count !== undefined" class="text-sm text-white/70">
        {{ item.count }} items
      </p>
    </div>
  </router-link>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true
  }
})
</script>

