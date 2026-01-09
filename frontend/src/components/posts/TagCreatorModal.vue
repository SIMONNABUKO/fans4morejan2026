<template>
  <TransitionRoot appear :show="isOpen" as="div">
    <Dialog as="div" @close="close" class="relative z-50">
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
                  Tag Creators
                </DialogTitle>
                <div class="flex items-center gap-3">
                  <button 
                    @click="toggleSearchMode"
                    class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200"
                    tabindex="0"
                  >
                    <i class="ri-search-line text-xl"></i>
                  </button>
                  <button 
                    class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200"
                    tabindex="0"
                  >
                    <i class="ri-menu-line text-xl"></i>
                  </button>
                </div>
              </div>

              <!-- Enhanced Search Bar -->
              <div v-if="isSearchMode" class="p-6">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <i class="ri-search-line text-gray-500 dark:text-gray-400"></i>
                  </div>
                  <input 
                    type="text" 
                    v-model="searchQuery" 
                    class="block w-full pl-12 pr-4 py-3 border border-white/30 dark:border-gray-600/30 rounded-2xl bg-white/80 dark:bg-gray-700/80 backdrop-blur-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200"
                    placeholder="Search creators..."
                    @input="debouncedSearch"
                  />
                </div>
              </div>

              <!-- Enhanced Loading State -->
              <div v-if="isLoading" class="flex justify-center my-12">
                <div class="relative">
                  <div class="w-12 h-12 border-4 border-gray-200 dark:border-gray-700 border-t-blue-500 rounded-full animate-spin"></div>
                  <div class="absolute inset-0 w-12 h-12 border-4 border-transparent border-t-blue-400 rounded-full animate-ping"></div>
                </div>
              </div>

              <!-- Enhanced No Results -->
              <div v-else-if="isSearchMode && searchQuery && creators.length === 0" class="text-center py-12">
                <div class="w-16 h-16 mx-auto mb-4 bg-gray-100/80 dark:bg-gray-700/80 rounded-full flex items-center justify-center">
                  <i class="ri-search-line text-2xl text-gray-400 dark:text-gray-500"></i>
                </div>
                <div class="text-gray-500 dark:text-gray-400 font-medium">No creators found</div>
                <div class="text-gray-400 dark:text-gray-500 text-sm mt-1">Try a different search term</div>
              </div>

              <!-- Enhanced Suggested Users Section -->
              <div v-else-if="!isSearchMode" class="px-6 pb-24">
                <div v-if="isPreviouslyTaggedLoading" class="flex justify-center my-12">
                  <div class="relative">
                    <div class="w-12 h-12 border-4 border-gray-200 dark:border-gray-700 border-t-blue-500 rounded-full animate-spin"></div>
                    <div class="absolute inset-0 w-12 h-12 border-4 border-transparent border-t-blue-400 rounded-full animate-ping"></div>
                  </div>
                </div>
                <div v-else-if="previouslyTaggedUsers.length === 0" class="text-center py-12">
                  <div class="w-16 h-16 mx-auto mb-4 bg-gray-100/80 dark:bg-gray-700/80 rounded-full flex items-center justify-center">
                    <i class="ri-user-line text-2xl text-gray-400 dark:text-gray-500"></i>
                  </div>
                  <div class="text-gray-500 dark:text-gray-400 font-medium">No suggested users available</div>
                  <div class="text-gray-400 dark:text-gray-500 text-sm mt-1">Start tagging users to see suggestions</div>
                </div>
                <div v-else>
                  <div class="text-sm font-semibold text-gray-900 dark:text-white mb-4 mt-2 flex items-center gap-2">
                    <i class="ri-user-star-line text-blue-500"></i>
                    Suggested Users
                  </div>
                  <div class="space-y-2">
                    <div 
                      v-for="(creator, index) in previouslyTaggedUsers" 
                      :key="creator.id || index"
                      class="flex items-center justify-between p-4 bg-white/60 dark:bg-gray-700/60 backdrop-blur-sm rounded-2xl border border-white/30 dark:border-gray-600/30 hover:bg-white/80 dark:hover:bg-gray-700/80 transition-all duration-200 group"
                    >
                      <div class="flex items-center gap-4">
                        <button 
                          class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center transition-all duration-200 hover:border-blue-500 dark:hover:border-blue-400"
                          :class="{ 'bg-blue-500 border-blue-500 dark:bg-blue-400 dark:border-blue-400': isCreatorSelected(creator) }"
                          @click="toggleCreatorSelection(creator)"
                          tabindex="0"
                        >
                          <i v-if="isCreatorSelected(creator)" class="ri-check-line text-white text-xs"></i>
                        </button>
                        <div class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-white/30 dark:ring-gray-600/30 shadow-lg">
                          <img 
                            :src="creator.avatar || defaultAvatar" 
                            :alt="`${creator.name}'s avatar`" 
                            class="w-full h-full object-cover"
                          />
                        </div>
                        <div>
                          <div class="flex items-center gap-2">
                            <span class="text-gray-900 dark:text-white font-semibold">{{ creator.name }}</span>
                            <span v-if="creator.verified" class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs px-2 py-0.5 rounded-full font-medium">
                              Verified
                            </span>
                          </div>
                          <div class="text-gray-500 dark:text-gray-400 text-sm">
                            @{{ creator.username }}
                          </div>
                        </div>
                      </div>
                      <button 
                        class="p-2 rounded-full bg-gray-100/60 dark:bg-gray-600/60 hover:bg-gray-200/60 dark:hover:bg-gray-500/60 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all duration-200 opacity-0 group-hover:opacity-100"
                        tabindex="0"
                      >
                        <i class="ri-more-fill text-lg"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Enhanced Search Results -->
              <div v-else-if="isSearchMode && creators.length > 0" class="px-6 pb-24">
                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                  <i class="ri-search-line text-blue-500"></i>
                  Search Results ({{ creators.length }})
                </div>
                <div class="space-y-2">
                  <div 
                    v-for="(creator, index) in creators" 
                    :key="creator.id || index"
                    class="flex items-center justify-between p-4 bg-white/60 dark:bg-gray-700/60 backdrop-blur-sm rounded-2xl border border-white/30 dark:border-gray-600/30 hover:bg-white/80 dark:hover:bg-gray-700/80 transition-all duration-200 group"
                  >
                    <div class="flex items-center gap-4">
                      <button 
                        class="w-6 h-6 rounded-full border-2 border-gray-300 dark:border-gray-600 flex items-center justify-center transition-all duration-200 hover:border-blue-500 dark:hover:border-blue-400"
                        :class="{ 'bg-blue-500 border-blue-500 dark:bg-blue-400 dark:border-blue-400': isCreatorSelected(creator) }"
                        @click="toggleCreatorSelection(creator)"
                        tabindex="0"
                      >
                        <i v-if="isCreatorSelected(creator)" class="ri-check-line text-white text-xs"></i>
                      </button>
                      <div class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-white/30 dark:ring-gray-600/30 shadow-lg">
                        <img 
                          :src="creator.avatar || defaultAvatar" 
                          :alt="`${creator.name}'s avatar`" 
                          class="w-full h-full object-cover"
                        />
                      </div>
                      <div>
                        <div class="flex items-center gap-2">
                          <span class="text-gray-900 dark:text-white font-semibold">{{ creator.name }}</span>
                        </div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">
                          @{{ creator.username }}
                        </div>
                      </div>
                    </div>
                    <button 
                      class="p-2 rounded-full bg-gray-100/60 dark:bg-gray-600/60 hover:bg-gray-200/60 dark:hover:bg-gray-500/60 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-all duration-200 opacity-0 group-hover:opacity-100"
                      tabindex="0"
                    >
                      <i class="ri-more-fill text-lg"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Enhanced Selected Users Preview -->
              <div v-if="selectedUsers.length > 0" class="fixed bottom-20 left-0 right-0 bg-white/90 dark:bg-gray-800/90 backdrop-blur-xl border-t border-white/20 dark:border-gray-700/50 p-6">
                <div class="text-sm font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                  <i class="ri-user-add-line text-blue-500"></i>
                  Selected ({{ selectedUsers.length }})
                </div>
                <div class="flex flex-wrap gap-2">
                  <div 
                    v-for="user in selectedUsers" 
                    :key="user.id"
                    class="bg-gradient-to-r from-blue-50/80 to-indigo-50/80 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-full px-4 py-2 flex items-center gap-2 shadow-sm border border-blue-200/50 dark:border-blue-700/30"
                  >
                    <div class="w-6 h-6 rounded-full overflow-hidden ring-1 ring-white/30 dark:ring-gray-600/30">
                      <img :src="user.avatar || defaultAvatar" alt="User Avatar" class="w-full h-full object-cover">
                    </div>
                    <span class="text-gray-900 dark:text-white text-sm font-medium">@{{ user.username }}</span>
                    <button @click="removeUser(user.id)" class="text-gray-400 hover:text-red-500 dark:text-gray-500 dark:hover:text-red-400 transition-colors duration-200">
                      <i class="ri-close-line"></i>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Enhanced Bottom Actions -->
              <div class="fixed bottom-0 left-0 right-0 flex items-center justify-end p-6 border-t border-white/20 dark:border-gray-700/50 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl">
                <div class="flex items-center gap-4">
                  <button 
                    @click="tagSelectedUsers"
                    class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-8 py-3 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    tabindex="0"
                    :disabled="!hasSelectedUsers"
                  >
                    Tag {{ selectedCount > 1 ? selectedCount + ' users' : 'user' }}
                  </button>
                  <button 
                    @click="close"
                    class="px-6 py-3 rounded-xl border border-gray-300 dark:border-gray-600 bg-white/80 dark:bg-gray-700/80 text-gray-700 dark:text-gray-300 hover:bg-gray-50/80 dark:hover:bg-gray-600/80 transition-all duration-200 font-semibold"
                    tabindex="0"
                  >
                    Close
                  </button>
                </div>
              </div>
              
              <!-- Hidden button to fix FocusTrap warning -->
              <button ref="initialFocusRef" class="sr-only" tabindex="0">Focus trap anchor</button>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { useUploadStore } from '@/stores/uploadStore'

const props = defineProps({
  isOpen: Boolean,
  selectedUsers: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'tag-users', 'reopen-post-modal'])

const uploadStore = useUploadStore()
const searchQuery = ref('')
const creators = ref([])
const isLoading = ref(false)
const debounceTimeout = ref(null)
const selectedUsers = ref([])
const isSearchMode = ref(false)
const defaultAvatar = 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-uI1JDg5gboqSkniC9SI1Grz2b6DmWa.png'
const initialFocusRef = ref(null)
const previouslyTaggedUsers = ref([])
const isPreviouslyTaggedLoading = ref(false)

// Initialize selected users from props
onMounted(() => {
  selectedUsers.value = [...props.selectedUsers]
  fetchPreviouslyTaggedUsers()
})

// Update selected users when props change
watch(() => props.selectedUsers, (newUsers) => {
  selectedUsers.value = [...newUsers]
}, { deep: true })

// Watch for isOpen changes to fix focus issues and fetch data
watch(() => props.isOpen, async (isOpen) => {
  if (isOpen) {
    await nextTick()
    // Focus the initial element to fix FocusTrap warning
    if (initialFocusRef.value) {
      initialFocusRef.value.focus()
    }
    
    // Fetch previously tagged users when modal opens
    fetchPreviouslyTaggedUsers()
  }
})

const selectedCount = computed(() => {
  return selectedUsers.value.length
})

const hasSelectedUsers = computed(() => {
  return selectedUsers.value.length > 0
})

const toggleSearchMode = () => {
  isSearchMode.value = !isSearchMode.value
  if (!isSearchMode.value) {
    searchQuery.value = ''
    creators.value = []
  }
}

const debouncedSearch = () => {
  if (debounceTimeout.value) {
    clearTimeout(debounceTimeout.value)
  }
  
  debounceTimeout.value = setTimeout(() => {
    searchCreators()
  }, 300)
}

// Fetch previously tagged users using the store method
const fetchPreviouslyTaggedUsers = async () => {
  if (isPreviouslyTaggedLoading.value) return
  
  isPreviouslyTaggedLoading.value = true
  
  try {
    const users = await uploadStore.getPreviouslyTaggedUsers()
    
    previouslyTaggedUsers.value = users.map(user => ({
      id: user.id,
      name: user.name || user.username,
      username: user.username,
      avatar: user.avatar || defaultAvatar
    }))
    
    console.log('Fetched previously tagged users:', previouslyTaggedUsers.value.length)
  } catch (error) {
    console.error('Error fetching previously tagged users:', error)
    previouslyTaggedUsers.value = []
  } finally {
    isPreviouslyTaggedLoading.value = false
  }
}

// Search for users using the store method
const searchCreators = async () => {
  if (!searchQuery.value.trim()) {
    creators.value = []
    return
  }
  
  isLoading.value = true
  
  try {
    const users = await uploadStore.searchUsers(searchQuery.value.trim())
    
    creators.value = users.map(user => ({
      id: user.id,
      name: user.name || user.username,
      username: user.username,
      avatar: user.avatar || defaultAvatar
    }))
  } catch (error) {
    console.error('Error searching creators:', error)
    creators.value = []
  } finally {
    isLoading.value = false
  }
}

const isCreatorSelected = (creator) => {
  return selectedUsers.value.some(user => user.id === creator.id)
}

const toggleCreatorSelection = (creator) => {
  const index = selectedUsers.value.findIndex(user => user.id === creator.id)
  
  if (index === -1) {
    // Add creator to selected list
    selectedUsers.value.push({
      id: creator.id,
      name: creator.name,
      username: creator.username,
      avatar: creator.avatar || defaultAvatar
    })
  } else {
    // Remove creator from selected list
    selectedUsers.value.splice(index, 1)
  }
}

const removeUser = (userId) => {
  selectedUsers.value = selectedUsers.value.filter(user => user.id !== userId)
}

const tagSelectedUsers = () => {
  // First, update the store with selected users
  uploadStore.setTaggedUsers(selectedUsers.value)
  
  // Emit tag-users event
  emit('tag-users', selectedUsers.value)
  
  // Close this modal
  emit('close')
  
  // Emit reopen-post-modal event
  emit('reopen-post-modal')
}

const close = () => {
  emit('close')
  // Reset search state if in search mode
  if (isSearchMode.value) {
    searchQuery.value = ''
    creators.value = []
  }
}
</script>

<style scoped>
/* Enhanced animations */
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
input:focus-visible {
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

/* Enhanced user card hover effects */
.user-card {
  position: relative;
  overflow: hidden;
}

.user-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.user-card:hover::after {
  opacity: 1;
}

/* Enhanced verified badge */
.verified-badge {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  font-weight: 600;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Enhanced search input focus */
.search-input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  transform: translateY(-1px);
}

/* Enhanced selected users preview */
.selected-users-preview {
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Enhanced action buttons */
.action-button {
  position: relative;
  overflow: hidden;
}

.action-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.action-button:hover::before {
  left: 100%;
}

/* Enhanced disabled state */
.disabled-button {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.disabled-button:hover {
  transform: none !important;
  box-shadow: none !important;
}
</style>