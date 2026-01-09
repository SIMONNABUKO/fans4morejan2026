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
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <div v-else-if="error" class="bg-red-50 dark:bg-red-900/50 p-4 rounded-md">
          <p class="text-red-700 dark:text-red-200">{{ error }}</p>
        </div>

        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
          <div
            v-for="media in mediaItems"
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
                    {{ media.stats?.total_likes || 0 }}
                  </span>
                  <span class="flex items-center">
                    <i class="ri-eye-line mr-1"></i>
                    {{ media.stats?.total_views || 0 }}
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
              <div class="mt-4 flex items-center justify-between">
                <select
                  v-model="media.status"
                  @change="updateMediaStatus(media)"
                  class="block w-40 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                  <option value="active">Active</option>
                  <option value="pending">Pending Review</option>
                  <option value="flagged">Flagged</option>
                  <option value="removed">Removed</option>
                </select>

                <div class="flex space-x-2">
                  <button
                    @click="viewMediaDetails(media)"
                    class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    title="View Details"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button
                    @click="removeMedia(media)"
                    class="p-2 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-200"
                    title="Delete Media"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalItems > 0" class="mt-6 flex items-center justify-between">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              :disabled="currentPage === 1"
              @click="setPage(currentPage - 1)"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Previous
            </button>
            <button
              :disabled="currentPage * perPage >= totalItems"
              @click="setPage(currentPage + 1)"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700 dark:text-gray-300">
                Showing
                <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span>
                to
                <span class="font-medium">
                  {{ Math.min(currentPage * perPage, totalItems) }}
                </span>
                of
                <span class="font-medium">{{ totalItems }}</span>
                results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  v-for="page in totalPages"
                  :key="page"
                  @click="setPage(page)"
                  :class="[
                    page === currentPage
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
import axios from '../../plugins/axios'

interface MediaPreview {
  id: number
  url: string
}

interface Media {
  id: number
  user_id: number
  mediable_id: number
  mediable_type: string
  type: string
  url: string
  full_url: string
  status: string
  created_at: string
  updated_at: string
  user: {
    id: number
    name: string
    username: string
    avatar?: string
  }
  previews: MediaPreview[]
  stats: {
    total_likes: number
    total_views: number
    total_bookmarks: number
  }
}

interface MediaFilters {
  type?: string
  status?: string
  date_range?: {
    start: string
    end: string
  }
}

const loading = ref(false)
const error = ref<string | null>(null)
const mediaItems = ref<Media[]>([])
const totalItems = ref(0)
const currentPage = ref(1)
const perPage = ref(12)
const filters = ref<MediaFilters>({
  type: '',
  status: ''
})
const dateRange = ref({
  start: '',
  end: ''
})

const totalPages = computed(() => Math.ceil(totalItems.value / perPage.value))

async function fetchMedia() {
  loading.value = true
  error.value = null
  try {
    const response = await axios.get('/admin/media', {
      params: {
        page: currentPage.value,
        limit: perPage.value,
        ...filters.value,
        date_range: dateRange.value.start ? {
          start: dateRange.value.start,
          end: dateRange.value.end
        } : undefined
      }
    })
    mediaItems.value = response.data.data
    totalItems.value = response.data.meta.total
  } catch (err: any) {
    console.error('Failed to fetch media:', err)
    error.value = err.response?.data?.message || 'Failed to fetch media'
    if (err.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
  } finally {
    loading.value = false
  }
}

async function approveMedia(media: Media) {
  try {
    await axios.patch(`/admin/media/${media.id}/status`, { status: 'active' })
    await fetchMedia()
  } catch (err: any) {
    console.error('Failed to approve media:', err)
    error.value = err.response?.data?.message || 'Failed to approve media'
  }
}

async function updateMediaStatus(media: Media) {
  try {
    await axios.patch(`/admin/media/${media.id}/status`, { status: media.status })
    await fetchMedia()
  } catch (err: any) {
    console.error('Failed to update media status:', err)
    error.value = err.response?.data?.message || 'Failed to update media status'
  }
}

async function viewMediaDetails(media: Media) {
  // TODO: Implement media details view in a modal or separate route
  console.log('View media details:', media)
}

async function removeMedia(media: Media) {
  if (!confirm('Are you sure you want to delete this media? This action cannot be undone.')) {
    return
  }
  try {
    await axios.patch(`/admin/media/${media.id}/status`, { status: 'removed' })
    await fetchMedia()
  } catch (err: any) {
    console.error('Failed to delete media:', err)
    error.value = err.response?.data?.message || 'Failed to delete media'
  }
}

function setPage(page: number) {
  currentPage.value = page
  fetchMedia()
}

function applyFilters() {
  currentPage.value = 1
  fetchMedia()
}

onMounted(() => {
  fetchMedia()
})
</script> 