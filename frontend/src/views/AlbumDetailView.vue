<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary">
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
      <div class="px-4 py-4 flex items-center gap-3">
        <router-link 
          to="/dashboard/vault" 
          class="p-2 -ml-2 rounded-full text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark"
        >
          <i class="ri-arrow-left-line text-xl"></i>
        </router-link>
        <h1 class="text-xl font-semibold">Vault</h1>
      </div>
      
      <!-- Secondary Navigation -->
      <div class="px-4 pb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <h2 class="text-lg">{{ albumData?.album?.name || 'Album' }}</h2>
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

    <!-- Loading State -->
    <div v-if="loading" class="p-4 text-center">
      <p class="text-text-light-secondary dark:text-text-dark-secondary">Loading album details...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="p-4 text-center text-red-500">
      {{ error }}
    </div>

    <template v-else-if="albumData">
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
        <button @click="showUploadDialog = true" class="w-full py-3 flex items-center justify-center gap-2 bg-surface-light dark:bg-surface-dark rounded-lg text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light-hover dark:hover:bg-surface-dark-hover">
          <i class="ri-upload-2-line"></i>
          <span>Upload to Vault</span>
        </button>
      </div>

      <!-- Media Grid -->
      <div class="p-4">
        <div v-for="(group, date) in groupedMedia" :key="date" class="mb-6">
          <h3 class="mb-2 text-sm font-medium">{{ formatDate(date) }}</h3>
          <div class="grid grid-cols-2 gap-2">
            <div 
              v-for="item in group" 
              :key="item.id"
              class="relative aspect-square rounded-lg overflow-hidden bg-surface-light dark:bg-surface-dark"
            >
              <img :src="item.previews[0]?.url || item.url" :alt="item.id" class="w-full h-full object-cover" />
              <button @click="editMedia(item)" class="absolute top-2 left-2 p-1 rounded-full bg-black/50">
                <i class="ri-edit-line text-white text-sm"></i>
              </button>
              <div class="absolute top-2 right-2 size-6 rounded-full border-2 border-white"></div>
              <div v-if="item.duration" class="absolute bottom-2 right-2 px-1 py-0.5 rounded bg-black/50 text-white text-xs">
                {{ formatDuration(item.duration) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Upload Dialog -->
    <dialog v-if="showUploadDialog" @close="showUploadDialog = false">
      <!-- Implement your upload dialog here -->
    </dialog>

    <!-- Edit Media Dialog -->
    <dialog v-if="showEditDialog" @close="showEditDialog = false">
      <!-- Implement your edit media dialog here -->
    </dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import { useVaultStore } from '@/stores/vaultStore'

const route = useRoute()
const vaultStore = useVaultStore()

const albumId = computed(() => route.params.id)
const albumData = ref(null)
const loading = ref(true)
const error = ref(null)

const tabs = ['Overview', 'All', 'Posts', 'Messages']
const activeTab = ref('All')

const showUploadDialog = ref(false)
const showEditDialog = ref(false)
const editingMedia = ref(null)

onMounted(async () => {
  try {
    loading.value = true
    error.value = null
    albumData.value = await vaultStore.fetchAlbumDetails(albumId.value)
  } catch (err) {
    console.error('Failed to fetch album details:', err)
    error.value = 'Failed to load album details. Please try again.'
  } finally {
    loading.value = false
  }
})

const groupedMedia = computed(() => {
  if (!albumData.value || !albumData.value.contents) return {}
  
  return albumData.value.contents.reduce((acc, media) => {
    const date = new Date(media.created_at).toISOString().split('T')[0]
    if (!acc[date]) acc[date] = []
    acc[date].push(media)
    return acc
  }, {})
})

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

const formatDuration = (seconds) => {
  if (!seconds) return ''
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

const editMedia = (media) => {
  editingMedia.value = media
  showEditDialog.value = true
}
</script>