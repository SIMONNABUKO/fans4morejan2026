<template>
  <div class="my-8 flex justify-center">
    <div class="grid grid-cols-2 gap-4 bg-white dark:bg-gray-900 rounded-lg shadow-lg p-4 w-full max-w-xl">
      <div
        v-for="preview in randomPreviews"
        :key="preview.id"
        class="relative group cursor-pointer"
        @click="openModal(preview)"
      >
        <img
          :src="preview.url"
          :alt="'Preview ' + preview.id"
          class="w-full h-40 object-cover rounded-md group-hover:opacity-80 transition"
        />
        <div class="absolute bottom-2 left-2 flex items-center gap-2 bg-black/60 px-2 py-1 rounded-full">
          <img
            v-if="preview.user && preview.user.avatar"
            :src="preview.user.avatar"
            :alt="preview.user.username"
            class="w-6 h-6 rounded-full border border-white"
          />
          <span class="text-xs text-white font-semibold">{{ preview.user?.username }}</span>
        </div>
      </div>
    </div>
    <ImagePreviewModal
      v-if="selectedPreview"
      :isOpen="!!selectedPreview"
      :preview="selectedPreview"
      @close="selectedPreview = null"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import ImagePreviewModal from './ImagePreviewModal.vue';

const props = defineProps({
  previews: {
    type: Array,
    required: true,
  },
});

const selectedPreview = ref(null);

function openModal(preview) {
  selectedPreview.value = preview;
}

const randomPreviews = computed(() => {
  if (!props.previews || props.previews.length <= 4) return props.previews;
  // Shuffle and pick 4
  return [...props.previews].sort(() => 0.5 - Math.random()).slice(0, 4);
});
</script> 