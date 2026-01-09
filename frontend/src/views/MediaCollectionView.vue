<template>
  <div class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Modern Header -->
    <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20 py-6">
          <!-- Left Side: Navigation and Title -->
          <div class="flex items-center gap-4">
            <router-link 
              to="/dashboard" 
              class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
            >
              <i class="ri-arrow-left-line text-lg"></i>
            </router-link>
            
            <div class="flex flex-col">
              <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
                {{ t('media_collection.title') }}
              </h1>
              <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
                Organize and manage your media collections
              </p>
            </div>
          </div>
          
          <!-- Right Side: Quick Stats -->
          <div class="hidden md:flex items-center gap-6">
            <div class="text-right">
              <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Albums</div>
              <div class="text-lg font-bold text-primary-light dark:text-primary-dark">{{ albums.length }}</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-16">
        <div class="flex flex-col items-center gap-4">
          <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light dark:border-primary-dark border-t-transparent"></div>
          <p class="text-text-light-secondary dark:text-text-dark-secondary">Loading media collections...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl p-6 mb-8">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
            <i class="ri-error-warning-line text-red-600 dark:text-red-400 text-lg"></i>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-red-900 dark:text-red-100">Error Loading Collections</h3>
            <p class="text-red-600 dark:text-red-400">{{ error }}</p>
          </div>
        </div>
      </div>

      <!-- Content -->
      <template v-else>
        <!-- Section Header -->
        <div class="flex items-center justify-between mb-8">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-folder-line text-purple-600 dark:text-purple-400 text-lg"></i>
            </div>
            <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">{{ t('media_collection.albums') }}</h2>
          </div>
          <button 
            @click="createNewAlbum"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 dark:bg-purple-500 text-white rounded-lg font-semibold hover:bg-purple-600/90 dark:hover:bg-purple-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-purple-600 dark:focus:ring-purple-500"
          >
            <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
              <i class="ri-add-line text-sm"></i>
            </div>
            <span class="text-sm">Create Album</span>
          </button>
        </div>

        <!-- Albums Grid -->
        <div v-if="albums.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="album in albums" :key="album.id" class="group">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-all duration-300 hover:scale-[1.02] group-hover:shadow-xl">
              <!-- Album Thumbnail -->
              <div class="aspect-square relative overflow-hidden">
                <img 
                  v-if="album.thumbnail"
                  :src="album.thumbnail" 
                  :alt="album.title"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                />
                <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800">
                  <i class="ri-folder-line text-4xl text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-400 transition-colors duration-200"></i>
                </div>
                
                <!-- Album Actions Overlay -->
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-300 flex items-center justify-center opacity-0 group-hover:opacity-100">
                  <div class="flex items-center gap-2">
                    <button class="p-2 bg-white/90 dark:bg-gray-800/90 rounded-full text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 hover:scale-110">
                      <i class="ri-edit-line text-lg"></i>
                    </button>
                    <button class="p-2 bg-white/90 dark:bg-gray-800/90 rounded-full text-gray-700 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-all duration-200 hover:scale-110">
                      <i class="ri-delete-bin-line text-lg"></i>
                    </button>
                  </div>
                </div>
              </div>
              
              <!-- Album Info -->
              <div class="p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-1 truncate">{{ album.title }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ album.count || 0 }} items</p>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-400 dark:text-gray-500">{{ formatDate(album.created_at) }}</span>
                  <router-link 
                    :to="{ name: 'album-view', params: { id: album.id } }"
                    class="text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 text-sm font-medium transition-colors"
                  >
                    View Album
                  </router-link>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
          <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="ri-folder-line text-3xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Albums Yet</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">
            Create your first album to start organizing your media collections.
          </p>
          <button 
            @click="createNewAlbum"
            class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 dark:bg-purple-500 text-white rounded-lg font-semibold hover:bg-purple-600/90 dark:hover:bg-purple-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <i class="ri-add-line"></i>
            Create Your First Album
          </button>
        </div>
      </template>
    </div>

    <!-- Create Album Modal -->
    <CreateAlbumModal
      :is-open="showCreateModal"
      @close="showCreateModal = false"
      @create="handleCreateAlbum"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useBookmarkStore } from '@/stores/bookmarkStore'
import MediaSection from '@/components/media/MediaSection.vue'
import CreateAlbumModal from '@/components/media/CreateAlbumModal.vue'
import { useI18n } from 'vue-i18n'

const bookmarkStore = useBookmarkStore()
const showCreateModal = ref(false)
const { t } = useI18n()

// Computed properties to access store state
const albums = computed(() => bookmarkStore.albums)
const loading = computed(() => bookmarkStore.loading)
const error = computed(() => bookmarkStore.error)

// Helper function to format dates
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString()
}

// Fetch albums when component is mounted
onMounted(async () => {
  try {
    await bookmarkStore.fetchUserAlbums()
  } catch (error) {
    console.error('Failed to load albums:', error)
  }
})

const createNewAlbum = () => {
  showCreateModal.value = true
}

const handleCreateAlbum = async (albumData) => {
  try {
    await bookmarkStore.createAlbum(albumData)
    showCreateModal.value = false
  } catch (error) {
    console.error('Failed to create album:', error)
  }
}
</script>