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
                {{ t('vault') }}
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
              <div class="text-lg font-bold text-primary-light dark:text-primary-dark">{{ vaultStore.albums.length }}</div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="vaultStore.loading" class="flex justify-center items-center py-16">
        <div class="flex flex-col items-center gap-4">
          <div class="animate-spin rounded-full h-12 w-12 border-4 border-primary-light dark:border-primary-dark border-t-transparent"></div>
          <p class="text-text-light-secondary dark:text-text-dark-secondary">{{ t('loading_albums') }}</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="vaultStore.error" class="flex justify-center items-center py-16">
        <div class="text-center">
          <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-error-warning-line text-2xl text-red-600 dark:text-red-400"></i>
          </div>
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Error Loading Albums</h3>
          <p class="text-red-500 dark:text-red-400">{{ vaultStore.error }}</p>
        </div>
      </div>

      <!-- Albums Section -->
      <div v-else class="space-y-6">
        <!-- Section Header -->
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
              <i class="ri-folder-line text-purple-600 dark:text-purple-400 text-lg"></i>
            </div>
            <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">{{ t('my_albums') }}</h2>
          </div>
          <div class="flex items-center gap-3">
            <button 
              @click="showCreateAlbumDialog = true"
              class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 dark:bg-purple-500 text-white rounded-lg font-semibold hover:bg-purple-600/90 dark:hover:bg-purple-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-purple-600 dark:focus:ring-purple-500"
            >
              <div class="w-5 h-5 rounded-full bg-white/20 flex items-center justify-center">
                <i class="ri-add-line text-sm"></i>
              </div>
              <span class="text-sm">Create Album</span>
            </button>
            <button class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200">
              <i class="ri-more-2-fill text-lg"></i>
            </button>
          </div>
        </div>

        <!-- Albums Grid -->
        <div v-if="vaultStore.albums.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div v-for="album in vaultStore.albums" :key="album.id" class="group">
            <router-link :to="{ name: 'album-detail', params: { id: album.id }}">
              <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-all duration-300 hover:scale-[1.02] group-hover:shadow-xl">
                <!-- Album Thumbnail -->
                <div class="aspect-square relative overflow-hidden">
                  <img 
                    v-if="album.thumbnail"
                    :src="album.thumbnail.url" 
                    :alt="album.name"
                    class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                  />
                  <div v-else class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800">
                    <i :class="getAlbumIcon(album.name)" class="text-4xl text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-400 transition-colors duration-200"></i>
                  </div>
                  
                  <!-- Media Count Badge -->
                  <div class="absolute top-3 right-3 bg-black/70 text-white px-2 py-1 rounded-full text-xs font-medium backdrop-blur-sm">
                    {{ album.media_count }}
                  </div>
                </div>
                
                <!-- Album Info -->
                <div class="p-4">
                  <h3 class="font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors duration-200">
                    {{ album.name }}
                  </h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ t('album_media_count', { count: album.media_count }) }}
                  </p>
                </div>
              </div>
            </router-link>
            
            <!-- Messages Preview (only for Messages album) -->
            <div v-if="album.name === 'Messages' && album.media_count > 0" class="mt-3 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200 dark:border-blue-800">
              <div class="flex items-start gap-3">
                <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                  <i class="ri-information-line text-white text-sm"></i>
                </div>
                <div>
                  <p class="text-sm text-blue-900 dark:text-blue-100 font-medium mb-1">You can even...</p>
                  <div class="text-xs text-blue-800 dark:text-blue-200">
                    {{ t('show_messages', { count: Math.min(3, album.media_count) }) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16">
          <div class="w-20 h-20 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="ri-folder-line text-3xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No Albums Yet</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto">
            Create your first album to start organizing your media content
          </p>
          <button 
            @click="showCreateAlbumDialog = true"
            class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 dark:bg-purple-500 text-white rounded-lg font-semibold hover:bg-purple-600/90 dark:hover:bg-purple-500/90 transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl"
          >
            <i class="ri-add-line"></i>
            Create First Album
          </button>
        </div>
      </div>
    </div>

    <!-- Create Album Dialog -->
    <dialog v-if="showCreateAlbumDialog" @close="showCreateAlbumDialog = false">
      <!-- Add your dialog content here -->
    </dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useVaultStore } from '@/stores/vaultStore'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const vaultStore = useVaultStore()
const showCreateAlbumDialog = ref(false)

// Fetch albums when component mounts
onMounted(async () => {
  try {
    await vaultStore.fetchAlbums()
  } catch (error) {
    console.error('Failed to fetch albums:', error)
  }
})

// Helper function to get album icon
const getAlbumIcon = (albumName) => {
  switch (albumName.toLowerCase()) {
    case 'all':
      return 'ri-image-line'
    case 'posts':
      return 'ri-file-list-line'
    case 'messages':
      return 'ri-message-3-line'
    default:
      return 'ri-folder-line'
  }
}
</script>