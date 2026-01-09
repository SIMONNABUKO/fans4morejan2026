<template>
  <div>
    {{ showScheduleModal }}
    <TransitionRoot appear :show="isOpen" as="div">
      <Dialog as="div" @close="close" class="relative z-50" :initialFocus="initialFocusRef">
        <TransitionChild
          as="div"
          enter="duration-500 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-300 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
          class="fixed inset-0"
        >
          <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full w-full">
            <TransitionChild
              as="div"
              enter="duration-500 ease-out"
              enter-from="opacity-0 translate-y-full scale-95"
              enter-to="opacity-100 translate-y-0 scale-100"
              leave="duration-300 ease-in"
              leave-from="opacity-100 translate-y-0 scale-100"
              leave-to="opacity-0 translate-y-full scale-95"
              class="w-full"
            >
              <DialogPanel class="w-full min-h-full bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl transform transition-all">
                <!-- Modern Header -->
                <div class="flex items-center justify-between p-6 border-b border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm">
                  <DialogTitle class="text-xl font-bold text-gray-900 dark:text-white">
                    Create Post
                  </DialogTitle>
                  <button 
                    @click="close"
                    :disabled="uploadStore.isSubmitting"
                    class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    tabindex="0"
                    ref="initialFocusRef"
                  >
                    <i class="ri-close-line text-xl"></i>
                  </button>
                </div>

                                  <!-- Enhanced Content -->
                <div class="p-6 space-y-6">
                  <!-- Status Message -->
                  <div v-if="uploadStore.isSubmitting" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                    <div class="flex items-center gap-3">
                      <div class="w-5 h-5 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                      <div>
                        <div class="text-blue-800 dark:text-blue-200 font-medium">Creating your post...</div>
                        <div class="text-blue-600 dark:text-blue-300 text-sm">Please wait while we upload your content</div>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Modern User Input Section -->
                  <div class="flex items-start gap-4">
                    <div class="relative">
                      <div class="w-12 h-12 rounded-full overflow-hidden ring-4 ring-white/20 dark:ring-gray-700/30 shadow-lg">
                        <img 
                          :src="userAvatar" 
                          alt="User Avatar" 
                          class="w-full h-full object-cover"
                        />
                      </div>
                      <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full border-3 border-white dark:border-gray-900 shadow-lg"></div>
                    </div>
                    <div class="flex-1">
                      <textarea
                        v-model="postContent"
                        :disabled="uploadStore.isSubmitting"
                        placeholder="What's on your mind? Share your thoughts..."
                        class="w-full bg-transparent text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 text-lg leading-relaxed outline-none resize-none border-0 focus:ring-0 disabled:opacity-50 disabled:cursor-not-allowed"
                        rows="4"
                        tabindex="0"
                        @input="checkForMentions"
                      ></textarea>
                      
                      <!-- Enhanced User mentions suggestions dropdown -->
                      <div v-if="showUserSuggestions && mentionQuery.length > 0" class="mt-3 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl border border-white/30 dark:border-gray-600/30 rounded-2xl shadow-2xl max-h-60 overflow-y-auto z-10">
                        <div v-if="isSearchingUsers" class="p-4 text-center text-gray-500 dark:text-gray-400">
                          <div class="flex items-center justify-center gap-2">
                            <div class="w-4 h-4 border-2 border-gray-300 dark:border-gray-600 border-t-transparent rounded-full animate-spin"></div>
                            <span>Searching...</span>
                          </div>
                        </div>
                        <div v-else-if="userSuggestions.length === 0" class="p-4 text-center text-gray-500 dark:text-gray-400">
                          No users found
                        </div>
                        <div 
                          v-else
                          v-for="user in userSuggestions" 
                          :key="user.id"
                          @click="selectUserMention(user)"
                          class="flex items-center gap-3 p-3 hover:bg-gray-50/80 dark:hover:bg-gray-700/80 cursor-pointer transition-colors duration-200 first:rounded-t-2xl last:rounded-b-2xl"
                        >
                          <div class="w-8 h-8 rounded-full overflow-hidden ring-2 ring-white/30 dark:ring-gray-600/30">
                            <img :src="user.avatar" alt="User Avatar" class="w-full h-full object-cover">
                          </div>
                          <div>
                            <div class="text-gray-900 dark:text-white font-medium">{{ user.name }}</div>
                            <div class="text-gray-500 dark:text-gray-400 text-sm">@{{ user.username }}</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Enhanced Tagged Users Preview -->
                  <div v-if="uploadStore.taggedUsers.length > 0" class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-4 border border-blue-200/50 dark:border-blue-700/30">
                    <div class="text-sm font-semibold text-blue-900 dark:text-blue-100 mb-3 flex items-center gap-2">
                      <i class="ri-user-add-line text-blue-600 dark:text-blue-400"></i>
                      Tag request will be sent to:
                    </div>
                    <div class="flex flex-wrap gap-2">
                      <div 
                        v-for="user in uploadStore.taggedUsers" 
                        :key="user.id"
                        class="bg-white/80 dark:bg-gray-700/80 backdrop-blur-sm rounded-full px-4 py-2 flex items-center gap-2 shadow-sm border border-white/50 dark:border-gray-600/50"
                      >
                        <div class="w-6 h-6 rounded-full overflow-hidden ring-1 ring-white/30 dark:ring-gray-600/30">
                          <img :src="user.avatar || userAvatar" alt="User Avatar" class="w-full h-full object-cover">
                        </div>
                        <span class="text-gray-900 dark:text-white text-sm font-medium">@{{ user.username }}</span>
                        <button @click="removeTaggedUser(user.id)" class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors duration-200">
                          <i class="ri-close-line"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Enhanced Media Previews -->
                  <div v-if="uploadStore.previews.length > 0" class="space-y-3">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                      <i class="ri-image-line text-gray-600 dark:text-gray-400"></i>
                      Media ({{ uploadStore.previews.length }})
                    </div>
                    <div class="grid grid-cols-4 gap-3">
                      <div 
                        v-for="preview in uploadStore.previews" 
                        :key="preview.id"
                        class="relative group aspect-square rounded-2xl overflow-hidden shadow-lg"
                      >
                        <img 
                          v-if="preview.type === 'image'" 
                          :src="preview.url" 
                          class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                          alt="Media preview"
                        />
                        <video 
                          v-else 
                          :src="preview.url" 
                          class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                        ></video>
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-300"></div>
                        
                        <!-- Upload Progress Overlay -->
                        <div v-if="uploadStore.isSubmitting" class="absolute inset-0 bg-black/50 flex items-center justify-center">
                          <div class="bg-white/90 dark:bg-gray-800/90 rounded-lg p-3 flex items-center gap-2">
                            <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                            <span class="text-xs font-medium text-gray-900 dark:text-white">Uploading...</span>
                          </div>
                        </div>
                        
                        <button 
                          v-if="!uploadStore.isSubmitting"
                          @click="removeMedia(preview.id)"
                          class="absolute top-2 right-2 bg-black/60 hover:bg-red-500/80 text-white rounded-full w-6 h-6 flex items-center justify-center transition-all duration-200 opacity-0 group-hover:opacity-100"
                          tabindex="0"
                        >
                          <i class="ri-close-line text-sm"></i>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Enhanced Post Options -->
                  <div class="space-y-2">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Post Options</div>
                    <label 
                      v-for="(option, index) in postOptions" 
                      :key="index"
                      class="flex items-center gap-3 text-gray-700 dark:text-gray-300 py-2 cursor-pointer hover:bg-gray-50/50 dark:hover:bg-gray-700/30 rounded-xl px-3 transition-colors duration-200"
                    >
                      <div class="relative flex items-center">
                                              <input 
                        type="checkbox" 
                        v-model="option.checked"
                        :disabled="uploadStore.isSubmitting"
                        class="w-5 h-5 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-transparent appearance-none cursor-pointer checked:border-blue-500 dark:checked:border-blue-400 checked:bg-blue-500 dark:checked:bg-blue-400 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        tabindex="0"
                      >
                        <i class="ri-check-line absolute left-0 text-sm text-white pointer-events-none transition-all duration-200" 
                           :class="option.checked ? 'opacity-100 scale-100' : 'opacity-0 scale-75'"></i>
                      </div>
                      <span class="font-medium">{{ option.label }}</span>
                    </label>
                  </div>
                </div>

                <!-- Enhanced Action Buttons -->
                <div class="absolute bottom-0 left-0 right-0 p-6 border-t border-white/20 dark:border-gray-700/50 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                      <template v-if="editMode">
                        <MediaUploadModalButton
                          v-if="editContextId"
                          :context-id="editContextId"
                          upload-new-text="Upload New"
                          from-vault-text="From Vault"
                          :show-from-vault="true"
                          modal-title="Upload Media"
                          accept-types="image/*,video/*"
                          add-media-text="Add More Images"
                          cancel-text="Cancel"
                          save-text="Save"
                          :show-permissions="true"
                          info-text=""
                        />
                      </template>
                      <template v-else>
                        <ImageUploadMenu 
                          @upload-new="handleUploadNew"
                          @from-vault="handleFromVault"
                        />
                      </template>
                      <div class="flex items-center gap-2">
                        <button 
                          v-for="(action, index) in actionButtons" 
                          :key="index"
                          :disabled="uploadStore.isSubmitting"
                          @click="action.icon === 'ri-at-line' ? openTagModal() : 
                                  action.icon === 'ri-calendar-line' ? openScheduleModal() :
                                  action.icon === 'ri-timer-line' ? openExpirationModal() : null"
                          class="p-3 rounded-xl bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 relative group disabled:opacity-50 disabled:cursor-not-allowed"
                          tabindex="0"
                        >
                          <i :class="action.icon" class="text-xl"></i>
                          <span v-if="action.tooltip" class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap shadow-lg">
                            {{ action.tooltip }}
                          </span>
                        </button>
                      </div>
                    </div>
                    <div class="flex items-center gap-3">
                      <button 
                        @click="handlePostSubmission"
                        :disabled="uploadStore.isSubmitting"
                        class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed text-white px-8 py-3 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 disabled:transform-none"
                        tabindex="0"
                      >
                        <div v-if="uploadStore.isSubmitting" class="flex items-center gap-2">
                          <div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                          <span>Creating Post...</span>
                        </div>
                        <span v-else>{{ editMode ? 'Update Post' : 'Create Post' }}</span>
                      </button>
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Enhanced Scheduling Modal -->
    <Teleport to="body">
      <div v-if="showScheduleModal" class="fixed inset-0 flex flex-col justify-center items-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:9999;">
        <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl w-full max-w-md rounded-3xl p-8 shadow-2xl border border-white/30 dark:border-gray-600/30">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
              Schedule Post
            </h3>
            <button @click="closeScheduleModal" class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 transition-all duration-200">
              <i class="ri-close-line text-xl"></i>
            </button>
          </div>
          <div class="mb-6">
            <VueDatePicker v-model="scheduledFor" />
          </div>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              class="px-6 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:bg-gray-50/80 dark:hover:bg-gray-600/80 transition-all duration-200 font-medium"
              @click="closeScheduleModal"
            >
              Cancel
            </button>
            <button
              type="button"
              class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200"
              @click="handleSchedule"
            >
              Schedule
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Enhanced Expiration Modal -->
    <Teleport to="body">
      <div v-if="showExpirationModal" class="fixed inset-0 flex flex-col justify-center items-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:9999;">
        <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl w-full max-w-md rounded-3xl p-8 shadow-2xl border border-white/30 dark:border-gray-600/30">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
              Set Post Expiration
            </h3>
            <button @click="closeExpirationModal" class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 transition-all duration-200">
              <i class="ri-close-line text-xl"></i>
            </button>
          </div>
          <div class="mb-6">
            <VueDatePicker v-model="expiresAt" />
          </div>
          <div class="flex justify-end gap-3">
            <button
              type="button"
              class="px-6 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:bg-gray-50/80 dark:hover:bg-gray-600/80 transition-all duration-200 font-medium"
              @click="closeExpirationModal"
            >
              Cancel
            </button>
            <button
              type="button"
              class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200"
              @click="handleExpiration"
            >
              Set Expiration
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Enhanced PermissionsManager Modal -->
    <Teleport to="body">
      <div v-if="showPermissionsManagerModal" class="fixed inset-0 flex flex-col justify-center items-center p-4 bg-black/50 backdrop-blur-sm" style="z-index:9999;">
        <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-xl w-full max-w-lg rounded-3xl p-8 shadow-2xl border border-white/30 dark:border-gray-600/30">
          <PermissionsManager @close="closePermissionsManagerModal" />
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import ImageUploadMenu from './ImageUploadMenu.vue'
import MediaUploadModalButton from '../MediaUploadModalButton.vue'
import { useUploadStore } from '@/stores/uploadStore'
import { useAuthStore } from '@/stores/authStore'
import { useFeedStore } from '@/stores/feedStore'
import VueDatePicker from '@vuepic/vue-datepicker'
import PermissionsManager from './PermissionsManager.vue'

const props = defineProps({
  isOpen: Boolean,
  shouldReopen: {
    type: Boolean,
    default: false
  },
  post: {
    type: Object,
    default: null
  },
  editMode: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'open-upload-preview', 'close-upload-preview', 'open', 'open-tag-modal'])

const uploadStore = useUploadStore()
const authStore = useAuthStore()
const feedStore = useFeedStore()
const initialFocusRef = ref(null)

// Debug: Log uploadStore state
console.log('UploadStore state:', {
  isSubmitting: uploadStore.isSubmitting,
  previews: uploadStore.previews.length
})

// Watch for isSubmitting changes
watch(() => uploadStore.isSubmitting, (newValue) => {
  console.log('isSubmitting changed to:', newValue)
})

// Initialize post content when in edit mode
watch(() => props.post, (newPost) => {
  if (props.editMode && newPost) {
    uploadStore.setPostContent(newPost.content)
    // Set any other post data that needs to be initialized
  }
}, { immediate: true })

const postContent = computed({
  get: () => uploadStore.post.content,
  set: (value) => uploadStore.setPostContent(value)
})

const userAvatar = computed(() => {
  return authStore.user?.avatar || 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-uI1JDg5gboqSkniC9SI1Grz2b6DmWa.png'
})

const postOptions = ref([
  { label: 'Pin to Timeline', checked: false },
  { label: 'Lock Replies', checked: false },
])

const actionButtons = [
  { icon: 'ri-at-line' },
  { icon: 'ri-calendar-line', tooltip: 'Schedule Post' },
  { icon: 'ri-timer-line', tooltip: 'Set Expiration' },
]

const showUserSuggestions = ref(false)
const mentionQuery = ref('')
const userSuggestions = ref([])
const isSearchingUsers = ref(false)
const debounceTimeout = ref(null)

// Add new state for scheduling
const showScheduleModal = ref(false)
const showExpirationModal = ref(false)
const scheduledFor = ref(null)
const expiresAt = ref(null)

const showPermissionsManagerModal = ref(false)

const editContextId = ref(null);
const lastInitializedPostId = ref(null);

// Watch for isOpen changes to fix focus issues
watch(() => props.isOpen, async (isOpen) => {
  if (isOpen) {
    await nextTick();
    // Focus the initial element to fix FocusTrap warning
    if (initialFocusRef.value) {
      initialFocusRef.value.focus();
    }
  }
});

onMounted(() => {
  // Ensure there's a focusable element
  if (initialFocusRef.value) {
    initialFocusRef.value.tabIndex = 0
  }
})

const handleUploadNew = () => {
  emit('open-upload-preview')
}

const handleFromVault = () => {
  console.log('From vault clicked')
  // Implement vault functionality here
}

const close = () => {
  if (!showScheduleModal.value && !showExpirationModal.value) {
    emit('close')
  }
  // If a child modal is open, do nothing
}

const removeMedia = (id) => {
  uploadStore.removeMedia(id)
}

const handlePostSubmission = async () => {
  // Validate content before submission
  if (!postContent.value.trim() && uploadStore.previews.length === 0) {
    alert('Please add some content or media to your post.')
    return
  }

  // Log the scheduled time details for debugging
  if (scheduledFor.value) {
    console.log('[DEBUG] scheduledFor.value:', scheduledFor.value);
    console.log('[DEBUG] scheduledFor.value.toISOString():', scheduledFor.value.toISOString());
    console.log('[DEBUG] User timezone offset (minutes):', scheduledFor.value.getTimezoneOffset());
  }
  
  // If expiration is not after scheduled, remove expiration and alert the user
  if (scheduledFor.value && expiresAt.value && expiresAt.value <= scheduledFor.value) {
    alert('Expiration date/time was removed because it was not after the scheduled publication date/time.')
    uploadStore.setPostAdditionalData({ expires_at: null })
  }
  
  // Always send ISO strings to the backend
  if (scheduledFor.value) {
    uploadStore.setPostAdditionalData({ scheduled_for: scheduledFor.value.toISOString() })
  }
  if (expiresAt.value) {
    uploadStore.setPostAdditionalData({ expires_at: expiresAt.value.toISOString() })
  }
  
  try {
    console.log('Submitting post:', uploadStore.post)
    await uploadStore.submitPost()
    
    // Show success feedback
    console.log('Post created successfully!')
    close() // Close the modal after successful submission
    
    // Refresh the feed to show the new post
    if (feedStore) {
      await feedStore.fetchFeed()
    }
  } catch (error) {
    console.error('Error submitting post:', error)
    
    // Show user-friendly error message
    let errorMessage = 'Failed to create post. Please try again.'
    
    if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    } else if (error.message) {
      errorMessage = error.message
    }
    
    alert(errorMessage)
  }
}

const openTagModal = () => {
  // Emit event to open tag modal
  emit('open-tag-modal')
}

const removeTaggedUser = (userId) => {
  uploadStore.removeTaggedUser(userId)
}

// For handling mentions in the content
const checkForMentions = () => {
  const content = postContent.value
  const cursorPosition = document.activeElement.selectionStart
  
  // Get the current word based on cursor position
  let startPos = cursorPosition
  while (startPos > 0 && content[startPos - 1] !== ' ' && content[startPos - 1] !== '\n') {
    startPos--
  }
  
  const currentWord = content.substring(startPos, cursorPosition)
  
  // Check if the word is a mention
  if (currentWord.startsWith('@') && currentWord.length > 1) {
    showUserSuggestions.value = true
    mentionQuery.value = currentWord.substring(1) // Remove @ from query
    searchUsersByMention()
  } else {
    showUserSuggestions.value = false
    mentionQuery.value = ''
  }
}

const searchUsersByMention = () => {
  // Debounce the search to avoid too many API calls
  if (debounceTimeout.value) {
    clearTimeout(debounceTimeout.value)
  }
  
  debounceTimeout.value = setTimeout(async () => {
    if (mentionQuery.value.trim().length > 0) {
      isSearchingUsers.value = true
      
      try {
        const users = await uploadStore.searchUsers(mentionQuery.value)
        
        userSuggestions.value = users.map(user => ({
          id: user.id,
          name: user.name,
          username: user.username,
          avatar: user.avatar || userAvatar
        }))
      } catch (error) {
        console.error('Error searching users:', error)
        userSuggestions.value = []
      } finally {
        isSearchingUsers.value = false
      }
    } else {
      userSuggestions.value = []
    }
  }, 300)
}

const selectUserMention = (user) => {
  const content = postContent.value
  const cursorPosition = document.activeElement.selectionStart
  
  // Find the start of the @mention
  let startPos = cursorPosition
  while (startPos > 0 && content[startPos - 1] !== ' ' && content[startPos - 1] !== '\n') {
    startPos--
  }
  
  // Replace the @mention with the selected username
  const beforeMention = content.substring(0, startPos)
  const afterMention = content.substring(cursorPosition)
  const newContent = beforeMention + '@' + user.username + ' ' + afterMention
  
  // Update the content
  uploadStore.setPostContent(newContent)
  
  // Add to tagged users if not already there
  uploadStore.addTaggedUser({
    id: user.id,
    name: user.name,
    username: user.username,
    avatar: user.avatar
  })
  
  // Hide suggestions
  showUserSuggestions.value = false
}

// Add methods for scheduling
const openScheduleModal = () => {
  console.log('openScheduleModal called')
  // In edit mode, pre-fill with the post's scheduled_for if available
  if (props.editMode && props.post?.scheduled_for) {
    // Convert to local Date object
    scheduledFor.value = new Date(props.post.scheduled_for)
  }
  showScheduleModal.value = true
}

const openExpirationModal = () => {
  showExpirationModal.value = true
}

const closeScheduleModal = () => {
  showScheduleModal.value = false
}

const closeExpirationModal = () => {
  showExpirationModal.value = false
}

const handleSchedule = () => {
  uploadStore.setPostAdditionalData({ scheduled_for: scheduledFor.value })
  closeScheduleModal()
}

const handleExpiration = () => {
  uploadStore.setPostAdditionalData({ expires_at: expiresAt.value })
  closeExpirationModal()
}

const closePermissionsManagerModal = () => {
  showPermissionsManagerModal.value = false
}

watch(showScheduleModal, (val) => {
  console.log('showScheduleModal changed:', val)
})

// Sync all local refs from post data in edit mode
watch(
  () => [props.isOpen, props.editMode, props.post?.id],
  ([isOpen, editMode, postId]) => {
    if (isOpen && editMode && postId && lastInitializedPostId.value !== postId) {
      // Only run if the modal is opening for a new post
      const contextId = `edit-post-${postId}`;
      editContextId.value = contextId;
      uploadStore.initializeFromPost(props.post, contextId);
      uploadStore.setContext(contextId);
      lastInitializedPostId.value = postId;
    }
    if (!isOpen) {
      lastInitializedPostId.value = null; // Reset when modal closes
    }
  },
  { immediate: true }
);
</script>

<style scoped>
/* Enhanced placeholder styling */
textarea::placeholder {
  opacity: 0.6;
  font-style: italic;
}

/* Custom animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInFromBottom {
  from {
    opacity: 0;
    transform: translateY(100%);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Apply animations to elements */
.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out;
}

.animate-slideInFromBottom {
  animation: slideInFromBottom 0.5s ease-out;
}

.animate-scaleIn {
  animation: scaleIn 0.4s ease-out;
}

.animate-pulse {
  animation: pulse 2s infinite;
}

/* Enhanced focus states */
button:focus-visible,
input:focus-visible,
textarea:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
  border-radius: 0.5rem;
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Enhanced scrollbar styling */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}

/* Dark mode scrollbar */
.dark ::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.7);
}

/* Enhanced textarea styling */
textarea {
  min-height: 120px;
  line-height: 1.6;
}

/* Glassmorphism effects */
.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .glass {
  background: rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Enhanced button hover effects */
.btn-hover {
  position: relative;
  overflow: hidden;
}

.btn-hover::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.btn-hover:hover::before {
  left: 100%;
}

/* Enhanced media preview hover effects */
.media-preview {
  position: relative;
  overflow: hidden;
}

.media-preview::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.media-preview:hover::after {
  opacity: 1;
}

/* Enhanced checkbox styling */
input[type="checkbox"] {
  position: relative;
  cursor: pointer;
}

input[type="checkbox"]:checked {
  animation: scaleIn 0.2s ease-out;
}

/* Enhanced loading spinner */
.spinner {
  border: 2px solid rgba(156, 163, 175, 0.3);
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Enhanced tooltip */
.tooltip {
  position: relative;
}

.tooltip::before {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
  z-index: 10;
}

.tooltip:hover::before {
  opacity: 1;
}

/* Enhanced focus ring for accessibility */
.focus-ring:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

/* Enhanced gradient text */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Enhanced shadow effects */
.shadow-soft {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.shadow-medium {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.shadow-strong {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>