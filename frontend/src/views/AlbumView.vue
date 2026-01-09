<template>
  <div v-if="album" class="min-h-screen bg-background-light dark:bg-background-dark">
    <!-- Header -->
    <header class="sticky top-0 z-10 bg-background-light dark:bg-background-dark border-b border-border-light dark:border-border-dark">
      <div class="px-4 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <router-link 
              to="/media" 
              class="p-2 -ml-2 rounded-full text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark"
            >
              <i class="ri-arrow-left-line text-xl"></i>
            </router-link>
            <h1 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">
              {{ album.title }}
            </h1>
          </div>
          <button class="px-4 py-1.5 text-sm rounded-full bg-surface-light dark:bg-surface-dark text-text-light-primary dark:text-text-dark-primary">
            {{ t('actions') }}
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="px-4">
        <nav class="flex gap-6">
          <router-link 
            to="/media" 
            custom
            v-slot="{ navigate }"
          >
            <button
              @click="navigate"
              class="pb-3 px-1 relative text-sm font-medium transition-colors text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary"
            >
              {{ t('overview') }}
            </button>
          </router-link>
          <button 
            class="pb-3 px-1 relative text-sm font-medium transition-colors text-text-light-primary dark:text-text-dark-primary"
          >
            {{ album.title }}
            <div 
              class="absolute bottom-0 left-0 right-0 h-0.5 bg-primary-light dark:bg-primary-dark"
            ></div>
          </button>
        </nav>
      </div>
    </header>

    <!-- Loading state -->
    <div v-if="loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary-light dark:border-primary-dark"></div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="p-4 text-center text-red-500">
      <p>{{ error }}</p>
      <button 
        @click="fetchAlbumData" 
        class="mt-4 px-4 py-2 bg-primary-light dark:bg-primary-dark text-white rounded-md"
      >
        {{ t('try_again') }}
      </button>
    </div>

    <!-- Empty state -->
    <div v-else-if="!mediaItems.length" class="p-4 text-center text-text-light-secondary dark:text-text-dark-secondary">
      <div class="py-12">
        <i class="ri-image-line text-5xl mb-4"></i>
        <p>{{ t('no_media_in_album') }}</p>
      </div>
    </div>

    <!-- Content -->
    <div v-else class="p-4 space-y-6">
      <div v-for="group in groupedMedia" :key="group.date" class="space-y-4">
        <h3 class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">
          {{ formatDate(group.date) }}
        </h3>
        
        <!-- Use PostGrid for each group of media -->
        <div v-for="(mediaGroup, index) in groupMediaByPermission(group.items)" :key="index" class="mb-4">
          <PostGrid
            :media="mediaGroup.userHasPermission ? mediaGroup.media : mediaGroup.previews"
            :author="author"
            :description="album.description"
            :user-has-permission="mediaGroup.userHasPermission"
            :required-permissions="mediaGroup.requiredPermissions"
            :total-media-count="mediaGroup.media.length"
            @mediaLike="handleMediaLike"
            @mediaBookmark="handleMediaBookmark"
            @mediaStats="handleMediaStats"
            @unlock="() => handleUnlock(mediaGroup)"
          />
        </div>
      </div>
    </div>

    <!-- Permission Unlock Modal -->
    <div v-if="showUnlockModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-background-light dark:bg-background-dark rounded-xl p-6 max-w-md w-full">
        <h3 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary mb-4">{{ t('unlock_content') }}</h3>
        
        <div v-if="selectedMediaGroup && selectedMediaGroup.requiredPermissions" class="mb-4">
          <p class="text-text-light-secondary dark:text-text-dark-secondary mb-2">
            {{ t('content_requires_permissions') }}
          </p>
          
          <div class="space-y-2">
            <div 
              v-for="(permissionGroup, groupIndex) in selectedMediaGroup.requiredPermissions" 
              :key="groupIndex"
              class="p-3 bg-surface-light dark:bg-surface-dark rounded-lg"
            >
              <p class="text-sm text-text-light-tertiary dark:text-text-dark-tertiary mb-2">
                {{ t('satisfy_one_of_following') }}
              </p>
              
              <div class="space-y-2">
                <div 
                  v-for="(permission, permIndex) in permissionGroup" 
                  :key="permIndex"
                  class="flex items-center justify-between"
                >
                  <div>
                    <span v-if="permission.type === 'subscribed_all_tiers'" class="text-text-light-primary dark:text-text-dark-primary">
                      {{ t('subscribe_to_creator') }}
                    </span>
                    <span v-else-if="permission.type === 'add_price'" class="text-text-light-primary dark:text-text-dark-primary">
                      {{ t('pay_credits', { credits: permission.value }) }}
                    </span>
                    <span v-else-if="permission.type === 'following'" class="text-text-light-primary dark:text-text-dark-primary">
                      {{ t('follow_creator') }}
                    </span>
                    <span v-else-if="permission.type === 'limited_time'" class="text-text-light-primary dark:text-text-dark-primary">
                      {{ t('time_limited_access') }}
                    </span>
                  </div>
                  
                  <button 
                    v-if="!permission.is_met"
                    @click="processPermission(permission)"
                    class="px-3 py-1 bg-primary-light dark:bg-primary-dark text-white rounded-lg hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover transition-colors"
                  >
                    {{ t('unlock') }}
                  </button>
                  <span v-else class="text-green-500">
                    <i class="ri-check-line"></i> {{ t('met') }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="flex justify-end gap-2 mt-4">
          <button 
            @click="closeUnlockModal"
            class="px-4 py-2 text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark rounded-lg transition-colors"
          >
            {{ t('cancel') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { format } from 'date-fns'
import { useBookmarkStore } from '@/stores/bookmarkStore'
import PostGrid from '@/components/user/PostGrid.vue'
import ImageBundleModal from '@/components/user/ImageBundleModal.vue'
import VideoPlayerModal from '@/components/posts/VideoPlayerModal.vue'
import { useI18n } from 'vue-i18n'

const route = useRoute()
const bookmarkStore = useBookmarkStore()
const { t } = useI18n()
const loading = ref(true)
const error = ref(null)
const album = ref(null)
const mediaItems = ref([])
const showUnlockModal = ref(false)
const selectedMediaGroup = ref(null)
const processingPermission = ref(false)
const author = ref({
  username: 'You',
  handle: '',
  avatar: '/path/to/default/avatar.png' // Replace with your default avatar
})

// Fetch album data and bookmarks
const fetchAlbumData = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Get the album ID from the route
    const albumId = route.params.id
    
    // First, make sure we have the album details
    if (!album.value) {
      // If we don't have albums loaded yet, fetch them
      if (!bookmarkStore.albums.length) {
        await bookmarkStore.fetchUserAlbums()
      }
      
      // Find the album in the store
      album.value = bookmarkStore.getAlbumById(albumId)
      
      if (!album.value) {
        throw new Error('Album not found')
      }
    }
    
    // Now fetch the bookmarks for this album
    const bookmarks = await bookmarkStore.fetchAlbumBookmarks(albumId)
    console.log('Fetched bookmarks:', bookmarks)
    
    // Transform bookmarks into media items
    mediaItems.value = bookmarks.map(bookmark => {
      // Check if bookmarkable exists
      if (!bookmark.bookmarkable) {
        console.warn('Bookmark has no bookmarkable object:', bookmark)
        return null
      }
      
      const media = bookmark.bookmarkable
      
      // Determine if it's a video or image based on mime type or extension
      const isVideo = media.mime_type ? 
        media.mime_type.startsWith('video') : 
        (media.url && ['mp4', 'webm', 'mov'].includes(media.url.split('.').pop().toLowerCase()))
      
      // Check if user has permission to view this media (from bookmark level)
      const hasPermission = bookmark.user_has_permission !== undefined ? 
        bookmark.user_has_permission : true
      
      // Get the media previews if available (from bookmark level, set by backend when no permission)
      const previews = bookmark.media || []
      
      return {
        id: media.id,
        type: isVideo ? 'video' : 'image',
        url: media.url,
        thumbnail: media.thumbnail_url || media.url,
        duration: media.duration || '0:00',
        createdAt: bookmark.created_at || new Date().toISOString(),
        bookmarkId: bookmark.id,
        hasPermission: hasPermission,
        requiredPermissions: bookmark.required_permissions || [],
        previews: previews.map(preview => ({
          id: preview.id,
          url: preview.url,
          thumbnail: preview.thumbnail_url || preview.url,
          type: isVideo ? 'video' : 'image',
          isPreview: true,
          stats: preview.stats || {}
        })),
        stats: media.stats || {
          total_likes: 0,
          total_views: 0,
          total_bookmarks: 0,
          is_liked: false,
          is_bookmarked: true
        }
      }
    }).filter(item => item !== null) // Filter out any null items
    
    console.log('Transformed media items:', mediaItems.value)
  } catch (err) {
    console.error('Error fetching album data:', err)
    error.value = err.message || 'Failed to load album data'
  } finally {
    loading.value = false
  }
}

onMounted(fetchAlbumData)

// Group media by date
const groupedMedia = computed(() => {
  if (!mediaItems.value.length) return []

  const groups = {}

  mediaItems.value.forEach(item => {
    const date = item.createdAt.split('T')[0]
    if (!groups[date]) {
      groups[date] = {
        date,
        items: []
      }
    }
    groups[date].items.push(item)
  })

  return Object.values(groups).sort((a, b) => b.date.localeCompare(a.date))
})

// Group media by permission status for PostGrid
const groupMediaByPermission = (items) => {
  // Group items by their permission status and required permissions
  const permissionGroups = {}
  
  items.forEach(item => {
    // Create a key based on permission status and required permissions
    const permKey = `${item.hasPermission}-${JSON.stringify(item.requiredPermissions)}`
    
    if (!permissionGroups[permKey]) {
      permissionGroups[permKey] = {
        userHasPermission: item.hasPermission,
        requiredPermissions: item.requiredPermissions,
        media: [],
        previews: []
      }
    }
    
    // Add the item to the media array
    permissionGroups[permKey].media.push({
      id: item.id,
      url: item.url,
      thumbnail: item.thumbnail,
      type: item.type,
      duration: item.duration,
      stats: item.stats,
      alt: `Media ${item.id}`
    })
    
    // Add previews if available
    if (item.previews && item.previews.length > 0) {
      permissionGroups[permKey].previews = [
        ...permissionGroups[permKey].previews,
        ...item.previews.map(preview => ({
          id: preview.id,
          url: preview.url,
          thumbnail: preview.thumbnail,
          type: preview.type,
          stats: preview.stats,
          alt: `Preview ${preview.id}`
        }))
      ]
    }
  })
  
  return Object.values(permissionGroups)
}

const formatDate = (dateString) => {
  try {
    const date = new Date(dateString)
    return format(date, 'd MMMM')
  } catch (e) {
    console.error('Error formatting date:', e)
    return dateString
  }
}

// Media interaction handlers
const handleMediaLike = async (mediaId) => {
  try {
    console.log(`Liking media ${mediaId}`)
    await bookmarkStore.likeMedia(mediaId)
  } catch (error) {
    console.error('Error liking media:', error)
  }
}

const handleMediaBookmark = async (mediaId) => {
  try {
    console.log(`Bookmarking media ${mediaId}`)
    await bookmarkStore.bookmarkMedia(mediaId)
  } catch (error) {
    console.error('Error bookmarking media:', error)
  }
}

const handleMediaStats = async (mediaId) => {
  console.log(`Viewing media stats for ${mediaId}`)
  // Implement stats viewing logic if needed
}

// Permission handling
const handleUnlock = (mediaGroup) => {
  console.log('Opening unlock modal for media group:', mediaGroup)
  
  // Check if the media group has required permissions
  if (!mediaGroup.requiredPermissions || mediaGroup.requiredPermissions.length === 0) {
    console.log('No required permissions defined for this media group')
    return
  }
  
  // Set the selected media group and show the unlock modal
  selectedMediaGroup.value = mediaGroup
  showUnlockModal.value = true
}

const closeUnlockModal = () => {
  console.log('Closing unlock modal')
  showUnlockModal.value = false
  selectedMediaGroup.value = null
}

const processPermission = async (permission) => {
  if (processingPermission.value || !selectedMediaGroup.value) return
  
  processingPermission.value = true
  try {
    console.log('Processing permission:', permission)
    
    // Implement your permission processing logic here
    // This would typically involve making an API call to unlock the content
    
    // For now, we'll just close the modal
    closeUnlockModal()
    
    // Refresh the album data to show the unlocked content
    await fetchAlbumData()
  } catch (error) {
    console.error('Error processing permission:', error)
  } finally {
    processingPermission.value = false
  }
}
</script>