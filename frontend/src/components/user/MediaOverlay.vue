<template>
  <div class="absolute bottom-2 right-2 flex flex-col gap-2">
    <button 
      class="p-2 bg-black/50 rounded-full hover:bg-black/70 transition-colors group"
      @click="$emit('like')"
    >
      <i 
        :class="[
          stats && stats.is_liked 
            ? 'ri-heart-fill text-accent-danger' 
            : 'ri-heart-line text-white group-hover:text-primary-light dark:group-hover:text-primary-dark'
        ]" 
        class="text-xl"
      ></i>
      <span class="sr-only">Like</span>
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  stats: {
    type: Object,
    required: true,
    // Should have: { likes, bookmarks, views, is_liked, is_bookmarked }
  }
})

defineEmits(['like', 'bookmark', 'stats'])

const formatNumber = (num) => {
  if (num >= 1000000) {
    return Math.floor(num / 1000000) + 'M'
  } else if (num >= 1000) {
    return Math.floor(num / 1000) + 'K'
  }
  return num
}
</script>

