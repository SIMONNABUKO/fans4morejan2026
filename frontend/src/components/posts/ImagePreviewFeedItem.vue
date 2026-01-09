<template>
  <div class="my-6 flex justify-center">
    <div class="relative group w-full max-w-md cursor-pointer" @click="isModalOpen = true">
      <img :src="preview.media[0]?.previews[0]?.url || preview.media[0]?.url" :alt="'Preview ' + preview.id" class="w-full h-64 object-cover rounded-lg shadow-lg group-hover:opacity-80 transition" />
      <div class="absolute bottom-3 left-3 flex items-center gap-2 bg-black/60 px-3 py-1 rounded-full">
        <img v-if="preview.user && preview.user.avatar" :src="preview.user.avatar" :alt="preview.user.username" class="w-8 h-8 rounded-full border border-white" />
        <button 
          @click.stop="router.push(`/${preview.user?.username}/posts`)"
          class="text-sm text-white font-semibold hover:text-blue-300 transition-colors cursor-pointer"
        >
          {{ preview.user?.username }}
        </button>
      </div>
      <span class="absolute top-3 right-3 bg-primary-light text-xs text-white px-2 py-1 rounded-full shadow">Preview</span>
    </div>
    <ImagePreviewModal v-if="isModalOpen" :preview="{ id: preview.id, url: preview.media[0]?.previews[0]?.url || preview.media[0]?.url, user: preview.user }" :is-open="isModalOpen" @close="isModalOpen = false" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import ImagePreviewModal from '../ImagePreviewModal.vue'

const router = useRouter()

const props = defineProps({
  preview: {
    type: Object,
    required: true
  }
})

const isModalOpen = ref(false)
</script> 