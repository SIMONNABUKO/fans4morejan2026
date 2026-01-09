<template>
  <div class="min-h-full">
    <div class="py-6">
      <div class="px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Media Moderation</h1>
      </div>

      <!-- Filters -->
      <div class="mt-6 px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Media Type Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Media Type</label>
              <select
                v-model="filters.type"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="">All Types</option>
                <option value="image">Images</option>
                <option value="video">Videos</option>
                <option value="audio">Audio</option>
              </select>
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
              <select
                v-model="filters.status"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending Review</option>
                <option value="flagged">Flagged</option>
                <option value="removed">Removed</option>
              </select>
            </div>

            <!-- Date Range Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
              <input
                type="date"
                v-model="dateRange.start"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              />
            </div>

            <div class="flex items-end">
              <button
                @click="applyFilters"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Apply Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Media Grid -->
      <div class="mt-6 px-4 sm:px-6 lg:px-8">
        <div v-if="mediaStore.loading" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="mediaStore.error" class="bg-red-50 dark:bg-red-900/50 p-4 rounded-md">
          <p class="text-red-700 dark:text-red-200">{{ mediaStore.error }}</p>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          <div
            v-for="media in mediaStore.media"
            :key="media.id"
            class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden"
          >
            <!-- Media Preview -->
            <div class="aspect-w-16 aspect-h-9 bg-gray-200 dark:bg-gray-700">
              <img
                v-if="media.type === 'image'"
                :src="media.url"
                :alt="'Media by ' + media.user.username"
                class="object-cover"
              />
              <video
                v-else-if="media.type === 'video'"
                :src="media.url"
                controls
                class="object-cover"
              ></video>
              <audio
                v-else-if="media.type === 'audio'"
                :src="media.url"
                controls
                class="w-full"
              ></audio>
            </div>

            <!-- Media Info -->
            <div class="p-4">
              <div class="flex items-center space-x-3">
                <img
                  :src="media.user.avatar || '/default-avatar.png'"
                  :alt="media.user.username"
                  class="h-8 w-8 rounded-full"
                />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ media.user.username }}
                  </p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ new Date(media.created_at).toLocaleDateString() }}
                  </p>
                </div>
              </div>

              <!-- Stats -->
              <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                <div class="flex items-center space-x-4">
                  <span class="flex items-center">
                    <i class="ri-heart-line mr-1"></i>
                    {{ media.stats.total_likes }}
                  </span>
                  <span class="flex items-center">
                    <i class="ri-eye-line mr-1"></i>
                    {{ media.stats.total_views }}
                  </span>
                </div>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="{
                    'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300': media.status === 'active',
                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300': media.status === 'pending',
                    'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300': media.status === 'flagged',
                    'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300': media.status === 'removed'
                  }"
                >
                  {{ media.status.charAt(0).toUpperCase() + media.status.slice(1) }}
                </span>
              </div>

              <!-- Actions -->
              <div class="mt-4 flex space-x-3">
                <button
                  v-if="media.status !== 'active'"
                  @click="approveMedia(media)"
                  class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  Approve
                </button>
                <button
                  v-if="media.status !== 'removed'"
                  @click="removeMedia(media)"
                  class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="mediaStore.totalMedia > 0" class="mt-6 flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              :disabled="mediaStore.currentPage === 1"
              @click="mediaStore.setPage(mediaStore.currentPage - 1)"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Previous
            </button>
            <button
              :disabled="mediaStore.currentPage * mediaStore.perPage >= mediaStore.totalMedia"
              @click="mediaStore.setPage(mediaStore.currentPage + 1)"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Showing
                <span class="font-medium">{{ (mediaStore.currentPage - 1) * mediaStore.perPage + 1 }}</span>
                to
                <span class="font-medium">
                  {{ Math.min(mediaStore.currentPage * mediaStore.perPage, mediaStore.totalMedia) }}
                </span>
                of
                <span class="font-medium">{{ mediaStore.totalMedia }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  v-for="page in totalPages"
                  :key="page"
                  @click="mediaStore.setPage(page)"
                  :class="[
                    page === mediaStore.currentPage
                      ? 'z-10 bg-indigo-50 dark:bg-indigo-900/50 border-indigo-500 text-indigo-600 dark:text-indigo-300'
                      : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                  ]"
                >
                  {{ page }}
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useMediaStore } from '@/stores/media'
import type { Media, MediaFilters } from '@/types/media'

const mediaStore = useMediaStore()

const filters = ref<MediaFilters>({
  type: '',
  status: undefined,
  date_range: undefined
})

const dateRange = ref({
  start: '',
  end: ''
})

const totalPages = computed(() => Math.ceil(mediaStore.totalMedia / mediaStore.perPage))

const applyFilters = () => {
  mediaStore.setFilters({
    ...filters.value,
    date_range: dateRange.value.start ? {
      start: dateRange.value.start,
      end: dateRange.value.end
    } : undefined
  })
}

const approveMedia = async (media: Media) => {
  try {
    await mediaStore.updateMediaStatus(media.id, 'active')
  } catch (error) {
    console.error('Failed to approve media:', error)
  }
}

const removeMedia = async (media: Media) => {
  if (!confirm('Are you sure you want to remove this media?')) return
  try {
    await mediaStore.updateMediaStatus(media.id, 'removed')
  } catch (error) {
    console.error('Failed to remove media:', error)
  }
}

onMounted(() => {
  mediaStore.fetchMedia()
})
</script> 