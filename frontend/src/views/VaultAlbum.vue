<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary">
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
      <div class="px-4 py-4 flex items-center gap-3">
        <router-link 
          to="/dashboard" 
          class="p-2 -ml-2 rounded-full text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark"
        >
          <i class="ri-arrow-left-line text-xl"></i>
        </router-link>
        <h1 class="text-xl font-semibold">Vault</h1>
      </div>
      
      <!-- Secondary Navigation -->
      <div class="px-4 pb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <h2 class="text-lg">Posts</h2>
          <button class="p-2 rounded-full bg-surface-light dark:bg-surface-dark">
            <i class="ri-search-line"></i>
          </button>
          <button class="p-2 rounded-full bg-surface-light dark:bg-surface-dark">
            <i class="ri-filter-3-line"></i>
          </button>
        </div>
        <button class="p-2 rounded-full bg-surface-light dark:bg-surface-dark">
          <i class="ri-more-2-fill"></i>
        </button>
      </div>
    </header>

    <!-- Navigation Pills -->
    <div class="px-4 flex gap-2 border-b border-border-light dark:border-border-dark">
      <button 
        v-for="tab in tabs" 
        :key="tab"
        :class="[
          'px-4 py-2 text-sm rounded-t-lg',
          activeTab === tab 
            ? 'bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary' 
            : 'text-text-light-secondary dark:text-text-dark-secondary'
        ]"
        @click="activeTab = tab"
      >
        {{ tab }}
      </button>
    </div>

    <!-- Upload Button -->
    <div class="p-4">
      <button class="w-full py-3 flex items-center justify-center gap-2 bg-surface-light dark:bg-surface-dark rounded-lg text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light-hover dark:hover:bg-surface-dark-hover">
        <i class="ri-upload-2-line"></i>
        <span>{{ t('upload_to_vault') }}</span>
      </button>
    </div>

    <!-- Media Grid -->
    <div class="p-4">
      <div v-for="(group, date) in mediaGroups" :key="date" class="mb-6">
        <h3 class="mb-2 text-sm font-medium">{{ date }}</h3>
        <div class="grid grid-cols-2 gap-2">
          <div 
            v-for="(item, index) in group" 
            :key="index"
            class="relative aspect-square rounded-lg overflow-hidden bg-surface-light dark:bg-surface-dark"
          >
            <img :src="item.thumbnail" :alt="item.title" class="w-full h-full object-cover" />
            <button class="absolute top-2 left-2 p-1 rounded-full bg-black/50">
              <i class="ri-edit-line text-white text-sm"></i>
            </button>
            <div class="absolute top-2 right-2 size-6 rounded-full border-2 border-white"></div>
            <div v-if="item.duration" class="absolute bottom-2 right-2 px-1 py-0.5 rounded bg-black/50 text-white text-xs">
              {{ item.duration }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const tabs = [t('overview'), t('all'), t('posts'), t('messages')]
const activeTab = ref(t('posts'))

const mediaGroups = {
  '2 December': [
    { thumbnail: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-yRlI7uTdYvuSHgYm51kmSHdyHhlACQ.png', title: 'Media 1' },
    { thumbnail: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-yRlI7uTdYvuSHgYm51kmSHdyHhlACQ.png', title: 'Media 2' }
  ],
  '30 October': [
    { thumbnail: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-yRlI7uTdYvuSHgYm51kmSHdyHhlACQ.png', title: 'Media 3', duration: '0:57' },
    { thumbnail: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-yRlI7uTdYvuSHgYm51kmSHdyHhlACQ.png', title: 'Media 4' },
    { thumbnail: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-yRlI7uTdYvuSHgYm51kmSHdyHhlACQ.png', title: 'Media 5' },
    { thumbnail: 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-yRlI7uTdYvuSHgYm51kmSHdyHhlACQ.png', title: 'Media 6' }
  ]
}
</script>