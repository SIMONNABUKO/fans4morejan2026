<template>
  <div class="space-y-6">
    <div v-if="!isEditing" class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
      {{ $t('automated_messages_description') }}
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <!-- Message Trigger -->
      <div class="space-y-2">
        <label class="text-sm text-text-light-secondary dark:text-text-dark-secondary">{{ $t('message_trigger') }}</label>
        <div class="relative">
          <select
            v-model="formData.trigger"
            class="w-full bg-surface-light dark:bg-surface-dark text-sm rounded-lg p-2 border border-border-light dark:border-border-dark appearance-none"
            :disabled="isEditing"
          >
            <option value="" disabled>{{ $t('select_trigger') }}</option>
            <option v-for="trigger in triggers" :key="trigger.value" :value="trigger.value">
              {{ trigger.label }}
            </option>
          </select>
          <i class="ri-arrow-down-s-line absolute right-2 top-1/2 -translate-y-1/2 text-text-light-secondary dark:text-text-dark-secondary"></i>
        </div>
      </div>

      <!-- Sent Delay -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-sm text-text-light-secondary dark:text-text-dark-secondary">{{ $t('sent_delay') }}</label>
          <button 
            type="button"
            class="text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark"
            @click.prevent="showDelayInfo = true"
          >
            <i class="ri-question-line"></i>
          </button>
        </div>
        <input
          v-model.number="formData.sentDelay"
          type="number"
          min="0"
          class="w-full bg-surface-light dark:bg-surface-dark text-sm rounded-lg p-2 border border-border-light dark:border-border-dark"
          placeholder="0"
        />
      </div>

      <!-- Cooldown -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-sm text-text-light-secondary dark:text-text-dark-secondary">{{ $t('cooldown') }}</label>
          <button 
            type="button"
            class="text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark"
            @click.prevent="showCooldownInfo = true"
          >
            <i class="ri-question-line"></i>
          </button>
        </div>
        <input
          v-model.number="formData.cooldown"
          type="number"
          min="0"
          class="w-full bg-surface-light dark:bg-surface-dark text-sm rounded-lg p-2 border border-border-light dark:border-border-dark"
          placeholder="0"
        />
      </div>

      <!-- Message Content -->
      <div class="space-y-2">
        <label class="text-sm text-text-light-secondary dark:text-text-dark-secondary">{{ $t('message_content') }}</label>
        <div class="border border-border-light dark:border-border-dark rounded-lg p-4">
          <div class="space-y-4">
            <textarea
              v-model="formData.content"
              rows="4"
              class="w-full bg-transparent text-sm resize-none focus:outline-none placeholder:text-text-light-secondary dark:placeholder:text-text-dark-secondary"
              :placeholder="$t('type_message_here')"
            ></textarea>

            <!-- Media Previews -->
            <div v-if="mediaPreviews.length > 0" class="border-t border-border-light dark:border-border-dark pt-4">
              <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">
                  {{ $t('attached_media') }} ({{ mediaPreviews.length }})
                </span>
                <button 
                  type="button"
                  @click="clearAllMedia"
                  class="text-xs text-red-500 hover:text-red-600 transition-colors"
                >
                  {{ $t('clear_all') }}
                </button>
              </div>
              
              <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                <div 
                  v-for="media in mediaPreviews" 
                  :key="media.id"
                  class="relative group aspect-square rounded-lg overflow-hidden border border-border-light dark:border-border-dark"
                >
                  <!-- Image Preview -->
                  <img 
                    v-if="media.type === 'image'" 
                    :src="media.url" 
                    :alt="$t('media_preview')"
                    class="w-full h-full object-cover"
                  />
                  
                  <!-- Video Preview -->
                  <video 
                    v-else-if="media.type === 'video'" 
                    :src="media.url" 
                    class="w-full h-full object-cover"
                    muted
                  >
                    <source :src="media.url" :type="media.file?.type || 'video/mp4'">
                  </video>
                  
                  <!-- Media Type Icon -->
                  <div class="absolute top-2 left-2 bg-black/50 rounded-full p-1">
                    <i 
                      :class="media.type === 'image' ? 'ri-image-line' : 'ri-video-line'" 
                      class="text-white text-xs"
                    ></i>
                  </div>
                  
                  <!-- Remove Button -->
                  <button 
                    type="button"
                    @click="removeMedia(media.id)"
                    class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity"
                    :title="$t('remove_media')"
                  >
                    <i class="ri-close-line text-xs"></i>
                  </button>
                  
                  <!-- Permissions Indicator -->
                  <div 
                    v-if="hasPermissions"
                    class="absolute bottom-2 left-2 bg-primary-light/90 dark:bg-primary-dark/90 text-white text-xs px-2 py-1 rounded-full"
                  >
                    <i class="ri-lock-line mr-1"></i>
                    {{ $t('restricted') }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-4 pt-2">
              <!-- Wrap ImageUploadMenu in a div to prevent form submission -->
              <div @click.prevent>
                <ImageUploadMenu 
                  @upload-new="handleUploadNew"
                  @from-vault="handleFromVault"
                />
              </div>
              <button 
                type="button"
                @click.prevent="addEmoji"
                class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark hover:bg-surface-light dark:hover:bg-surface-dark rounded-full transition-colors"
                :title="$t('add_emoji')"
              >
                <i class="ri-emotion-line text-xl"></i>
              </button>
              <button 
                type="button"
                @click.prevent
                class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-primary-light dark:hover:text-primary-dark hover:bg-surface-light dark:hover:bg-surface-dark rounded-full transition-colors"
                :title="$t('record_audio')"
              >
                <i class="ri-mic-line text-xl"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div v-if="error && showValidationErrors" class="text-red-500 text-sm">
        {{ error }}
      </div>

      <!-- Bottom Buttons -->
      <div class="flex items-center gap-4 pt-2">
        <button 
          type="button"
          @click.prevent="$emit('cancel')"
          class="flex-1 py-2 border border-red-500 text-red-500 rounded-lg hover:bg-red-500/10"
        >
          {{ $t('cancel') }}
        </button>
        <button 
          type="submit"
          class="flex-1 py-2 bg-primary-light dark:bg-primary-dark text-white rounded-lg hover:opacity-90 disabled:opacity-50"
          :disabled="saving || !isValid"
          @click="showValidationErrors = true"
        >
          {{ saving ? $t('saving') : (isEditing ? $t('save') : $t('create')) }}
        </button>
      </div>
    </form>

    <!-- Delay Info Modal -->
    <Modal v-if="showDelayInfo" @close="showDelayInfo = false">
      <div class="space-y-4">
        <h3 class="text-lg font-medium">{{ $t('sent_delay') }}</h3>
        <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
          {{ $t('sent_delay_description') }}
        </p>
      </div>
    </Modal>

    <!-- Cooldown Info Modal -->
    <Modal v-if="showCooldownInfo" @close="showCooldownInfo = false">
      <div class="space-y-4">
        <h3 class="text-lg font-medium">{{ $t('cooldown') }}</h3>
        <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
          {{ $t('cooldown_description') }}
        </p>
      </div>
    </Modal>

    <!-- Media Upload Modal -->
    <MediaUploadModal 
      :is-open="isMediaUploadModalOpen"
      :context-id="getMessageContextId"
      @close="closeUploadModal"
      @upload="handleMediaUpload"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useI18n } from 'vue-i18n'
import { useMessageSettingsStore } from '@/stores/messageSettingsStore'
import { useMediaUploadStore } from '@/stores/mediaUploadStore'
import Modal from '@/components/common/Modal.vue'
import MediaUploadModal from '@/components/posts/MediaUploadModal.vue'
import ImageUploadMenu from '@/components/posts/ImageUploadMenu.vue'

const { t } = useI18n()

const props = defineProps({
  message: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['cancel', 'save'])

const messageSettingsStore = useMessageSettingsStore()
const mediaUploadStore = useMediaUploadStore()
const { saving, error } = storeToRefs(messageSettingsStore)

const showDelayInfo = ref(false)
const showCooldownInfo = ref(false)
const isMediaUploadModalOpen = ref(false)
const showValidationErrors = ref(false) // Only show validation errors after submit attempt
const permissions = ref([]) // Store permissions from media upload modal

const isEditing = computed(() => !!props.message?.id)

const triggers = [
  { value: 'new_follower', label: t('new_follower') },
  { value: 'new_subscriber', label: t('new_subscriber') },
  { value: 'tip_received', label: t('tip_received') },
  { value: 'media_purchased', label: t('media_purchased') },
]

const formData = ref({
  trigger: props.message?.trigger || '',
  content: props.message?.content || '',
  sentDelay: props.message?.sent_delay || 0,
  cooldown: props.message?.cooldown || 0,
  isActive: props.message?.is_active ?? true
})

const isValid = computed(() => {
  const hasContent = formData.value.content && formData.value.content.trim().length > 0;
  const hasMedia = mediaPreviews.value && mediaPreviews.value.length > 0;
  
  return formData.value.trigger && 
         (hasContent || hasMedia) && 
         formData.value.sentDelay >= 0 && 
         formData.value.cooldown >= 0
})

// Create a context ID for this automated message
const getMessageContextId = computed(() => {
  return isEditing.value ? `automated-message-${props.message.id}` : 'automated-message-new'
})

// Computed property to access the previews
const mediaPreviews = computed(() => {
  return mediaUploadStore.previews || []
})

// Computed property to check if there are permissions set
const hasPermissions = computed(() => {
  return permissions.value && permissions.value.length > 0 && 
         permissions.value.some(permissionSet => 
           permissionSet.some(permission => 
             permission.type !== 'subscribed_all_tiers' || permission.value !== null
           )
         )
})

// Set the context when the component mounts or when the context ID changes
watch(() => getMessageContextId.value, (newContextId) => {
  console.log('Context ID changed to:', newContextId)
  mediaUploadStore.setContext(newContextId)
}, { immediate: true })

// Watch for changes in the media upload store to ensure reactivity
watch(() => mediaUploadStore.previews, (newPreviews) => {
  console.log('Media previews changed:', newPreviews)
}, { deep: true })

// Set the context when the component mounts
onMounted(() => {
  console.log('AutomatedMessageForm mounted, setting context to:', getMessageContextId.value)
  
  // Initialize the context first
  mediaUploadStore.initContext(getMessageContextId.value, {
    permissions: [[{ type: "subscribed_all_tiers", value: null }]],
    metadata: { type: 'automated_message' }
  })
  
  // Set the context for media uploads
  mediaUploadStore.setContext(getMessageContextId.value)
  
  // If editing and there's media, add it to the media upload store
  if (isEditing.value && props.message.media && props.message.media.length > 0) {
    console.log('Loading existing media for editing:', props.message.media)
    
    const existingMedia = props.message.media.map(media => ({
      id: `existing-${media.id}`,
      file: null,
      url: media.full_url || media.url,
      type: media.type && media.type.startsWith('image/') ? 'image' : 'video',
      previewVersions: media.previews ? media.previews.map(preview => ({
        id: `preview-${preview.id}`,
        url: preview.url,
        file: null
      })) : []
    }))
    
    // Clear any existing media first
    mediaUploadStore.clearMedia()
    
    // Add the existing media
    existingMedia.forEach(media => {
      mediaUploadStore.addMedia(media)
    })
    
    console.log('Added existing media to store, current previews:', mediaUploadStore.previews)
  } else {
    // Clear media for new messages
    mediaUploadStore.clearMedia()
  }
  
  // If editing and there are permissions, load them
  if (isEditing.value && props.message.permissionSets && props.message.permissionSets.length > 0) {
    console.log('Loading existing permissions:', props.message.permissionSets)
    
    const formattedPermissions = props.message.permissionSets.map(set => {
      return set.permissions.map(permission => ({
        type: permission.type,
        value: permission.value
      }))
    })
    
    permissions.value = formattedPermissions
    
    // Also set permissions in the media store
    mediaUploadStore.setPermissions(formattedPermissions)
  } else {
    // Reset permissions for new messages
    permissions.value = []
  }
})

// Cleanup when component is unmounted
onBeforeUnmount(() => {
  console.log('AutomatedMessageForm unmounting, cleaning up context:', getMessageContextId.value)
  // Don't clear media here as it might be needed for submission
  // The media will be cleared after successful submission in handleSubmit
})

// Media upload methods
const handleUploadNew = (event) => {
  // Prevent form submission
  if (event) event.preventDefault()
  
  isMediaUploadModalOpen.value = true
  console.log('🖼️ Opening media upload modal');
}

const handleFromVault = async (event) => {
  // Prevent form submission
  if (event) event.preventDefault()
  
  console.log('🗄️ Opening vault selector');
  
  try {
    // In a real implementation, you would:
    // 1. Fetch the user's vault albums/media
    // 2. Show a modal with the media items
    // 3. Allow the user to select items
    // 4. Add the selected items to the message
    
    // Simulate selecting media from vault
    const mockVaultMedia = [
      {
        id: 'vault-' + Date.now(),
        file: null, // No file since it's already uploaded
        url: 'https://example.com/vault-image.jpg',
        type: 'image',
        // Add preview versions if needed
        previewVersions: []
      }
    ];
    
    // Add the selected media from vault
    mediaUploadStore.addMediaFromVault(mockVaultMedia);
    
    console.log('✅ Added media from vault:', mockVaultMedia);
  } catch (error) {
    console.error('❌ Error accessing vault:', error);
  }
}

const closeUploadModal = () => {
  isMediaUploadModalOpen.value = false
  console.log('🖼️ Closed media upload modal');
}

const handleMediaUpload = ({ files, permissions: uploadPermissions }) => {
  console.log('🖼️ Media uploaded to AutomatedMessageForm:', files);
  console.log('🔒 Upload permissions received:', uploadPermissions);
  
  // The files should already be added to the media store by the MediaUploadModal
  // But let's ensure they're properly set in our context
  if (files && files.length > 0) {
    // Clear existing media first
    mediaUploadStore.clearMedia();
    
    // Add the new files
    files.forEach(file => {
      mediaUploadStore.addMedia(file);
    });
    
    console.log('✅ Added media to store, current previews:', mediaUploadStore.previews);
  }
  
  // If permissions were set in the upload modal, update our permissions
  if (uploadPermissions && uploadPermissions.length > 0) {
    permissions.value = uploadPermissions;
    mediaUploadStore.setPermissions(uploadPermissions);
    console.log('🔒 Updated permissions from upload modal:', permissions.value);
  }
  
  closeUploadModal();
}

const removeMedia = (id) => {
  console.log(`🗑️ Removing media with ID ${id}`);
  mediaUploadStore.removeMedia(id);
}

const clearAllMedia = () => {
  console.log('🗑️ Clearing all media');
  mediaUploadStore.clearMedia();
}

const addEmoji = (event) => {
  // Prevent form submission
  if (event) event.preventDefault()
  
  // Implement emoji picker integration
  console.log('Adding emoji');
}

const handleSubmit = async () => {
  // Show validation errors now that submit was attempted
  showValidationErrors.value = true
  
  if (!isValid.value) {
    console.log('Form validation failed');
    return;
  }
  
  try {
    // Create form data to submit
    const submitData = {
      trigger: formData.value.trigger,
      content: formData.value.content,
      sent_delay: formData.value.sentDelay,
      cooldown: formData.value.cooldown,
      is_active: formData.value.isActive
    }
    
    // Add permissions if they exist (from media upload modal)
    if (permissions.value.length > 0) {
      submitData.permissions = permissions.value;
    }
    
    // Get media files from the store
    const mediaFiles = mediaUploadStore.previews || []
    
    console.log(`📤 Preparing to save automated message:`);
    console.log('- Content:', submitData.content);
    console.log('- Media files:', mediaFiles.length);
    console.log('- Permissions:', permissions.value.length > 0 ? permissions.value : 'None');
    console.log('- Media store context:', mediaUploadStore.currentContext);
    console.log('- Current media previews:', mediaFiles);
    
    if (isEditing.value) {
      // For editing, we need to pass the media files to the update method
      await messageSettingsStore.updateAutomatedMessage(props.message.id, submitData, mediaFiles)
    } else {
      // For creating, we need to pass the media files to the create method
      await messageSettingsStore.createAutomatedMessage(submitData, mediaFiles)
    }
    
    // Clear the media upload store after successful submission
    mediaUploadStore.clearMedia()
    
    emit('save')
  } catch (error) {
    console.error('Error saving automated message:', error)
  }
}
</script>

<style scoped>
/* Custom scrollbar styling */
.scrollbar-thin {
  scrollbar-width: thin;
}

.scrollbar-thin::-webkit-scrollbar {
  height: 4px;
}

.scrollbar-thin::-webkit-scrollbar-track {
  background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: var(--border-light);
  border-radius: 2px;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
  background-color: var(--border-dark);
}
</style>