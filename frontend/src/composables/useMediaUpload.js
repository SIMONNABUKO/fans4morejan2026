import { ref, computed } from 'vue'
import { useMediaUploadStore } from '@/stores/mediaUploadStore'

export function useMediaUpload(options = {}) {
  const {
    contextId = `upload-${Date.now()}`,
    initialPermissions = [[{ type: 'subscribed_all_tiers', value: null }]],
    initialMetadata = {},
    endpoint = '/media',
  } = options
  
  const mediaUploadStore = useMediaUploadStore()
  const isModalOpen = ref(false)
  
  // Initialize the context only if it doesn't exist
  if (!mediaUploadStore.contentByContext[contextId]) {
    mediaUploadStore.initContext(contextId, {
      permissions: initialPermissions,
      metadata: initialMetadata
    })
  }
  
  // Computed properties
  const previews = computed(() => {
    return mediaUploadStore.contentByContext[contextId]?.previews || []
  })
  
  const permissions = computed({
    get: () => mediaUploadStore.contentByContext[contextId]?.permissions || [],
    set: (value) => {
      if (mediaUploadStore.contentByContext[contextId]) {
        mediaUploadStore.contentByContext[contextId].permissions = value
      }
    }
  })
  
  // Methods
  const openModal = () => {
    mediaUploadStore.currentContext = contextId
    isModalOpen.value = true
  }
  
  const closeModal = () => {
    isModalOpen.value = false
  }
  
  const addMedia = (files) => {
    mediaUploadStore.currentContext = contextId
    return mediaUploadStore.addMedia(files)
  }
  
  const removeMedia = (id) => {
    mediaUploadStore.currentContext = contextId
    mediaUploadStore.removeMedia(id)
  }
  
  const clearMedia = () => {
    // If you have a clearContext method, use it. Otherwise, clear manually.
    if (mediaUploadStore.contentByContext[contextId]) {
      mediaUploadStore.contentByContext[contextId].media = []
    }
  }
  
  const uploadMedia = async (customOptions = {}) => {
    mediaUploadStore.currentContext = contextId
    
    const uploadOptions = {
      endpoint,
      ...customOptions
    }
    
    return mediaUploadStore.uploadMedia(uploadOptions)
  }
  
  const updateMetadata = (metadata) => {
    if (mediaUploadStore.contentByContext[contextId]) {
      mediaUploadStore.contentByContext[contextId].metadata = {
        ...mediaUploadStore.contentByContext[contextId].metadata,
        ...metadata
      }
    }
  }
  
  return {
    contextId,
    isModalOpen,
    previews,
    permissions,
    openModal,
    closeModal,
    addMedia,
    removeMedia,
    clearMedia,
    uploadMedia,
    updateMetadata
  }
}