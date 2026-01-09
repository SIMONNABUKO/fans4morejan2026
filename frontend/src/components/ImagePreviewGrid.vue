<template>
  <div>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 p-4">
      <div
        v-for="(preview, idx) in previews"
        :key="preview.id"
        class="relative group cursor-pointer"
        @click="openModal(idx)"
      >
        <img
          :src="preview.url"
          :alt="'Preview ' + preview.id"
          class="w-full h-48 object-cover rounded-lg shadow-md group-hover:opacity-80 transition"
        />
        <div class="absolute bottom-2 left-2 flex items-center gap-2 bg-black/60 px-2 py-1 rounded-full">
          <img
            v-if="preview.user && preview.user.avatar"
            :src="preview.user.avatar"
            :alt="preview.user.username"
            class="w-6 h-6 rounded-full border border-white"
          />
          <span class="text-xs text-white font-medium">{{ preview.user?.username }}</span>
        </div>
      </div>
    </div>
    <ImagePreviewModal
      v-if="isModalOpen"
      :preview="previews[selectedIndex]"
      :is-open="isModalOpen"
      @close="isModalOpen = false"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import ImagePreviewModal from './ImagePreviewModal.vue'

const props = defineProps({
  previews: {
    type: Array,
    required: true
  }
})

const isModalOpen = ref(false)
const selectedIndex = ref(0)

function openModal(idx) {
  selectedIndex.value = idx
  isModalOpen.value = true
}
</script> 