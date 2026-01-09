<template>
  <div 
    class="relative rounded-lg overflow-hidden aspect-square cursor-pointer"
    @click="$emit('click', item)"
  >
    <!-- Media thumbnail -->
    <img 
      :src="item.thumbnail" 
      :alt="'Media ' + item.id" 
      class="w-full h-full object-cover"
      :class="{ 'opacity-70': !item.hasPermission && !item.isPreview }"
    />
    
    <!-- Video indicator -->
    <div v-if="item.type === 'video'" class="absolute bottom-2 right-2 bg-black bg-opacity-60 text-white text-xs px-1.5 py-0.5 rounded">
      <i class="ri-play-fill mr-0.5"></i>
      {{ item.duration }}
    </div>
    
    <!-- Lock indicator for content that requires permission -->
    <div v-if="!item.hasPermission && !item.isPreview" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-40">
      <div class="text-white text-center">
        <i class="ri-lock-fill text-3xl mb-1"></i>
        <p class="text-xs">Locked Content</p>
      </div>
    </div>
    
    <!-- Preview indicator -->
    <div v-if="item.isPreview" class="absolute top-2 left-2 bg-yellow-500 text-white text-xs px-1.5 py-0.5 rounded">
      Preview
    </div>
    
    <!-- Stats indicator -->
    <div class="absolute bottom-2 left-2 flex items-center space-x-2 text-white text-xs">
      <div class="flex items-center">
        <i class="ri-heart-fill mr-0.5 text-red-500"></i>
        {{ item.stats?.total_likes || 0 }}
      </div>
      <div class="flex items-center">
        <i class="ri-eye-fill mr-0.5"></i>
        {{ item.stats?.total_views || 0 }}
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  item: {
    type: Object,
    required: true
  }
})

defineEmits(['click'])
</script>