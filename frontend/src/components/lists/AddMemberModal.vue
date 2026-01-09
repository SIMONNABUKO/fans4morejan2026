<template>
  <TransitionRoot appear :show="true" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
      <!-- Enhanced Backdrop -->
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-2">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95 translate-y-4"
            enter-to="opacity-100 scale-100 translate-y-0"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100 translate-y-0"
            leave-to="opacity-0 scale-95 translate-y-4"
          >
            <DialogPanel class="w-full h-full max-w-none transform overflow-hidden bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 p-4 text-left align-middle shadow-2xl transition-all animate-scaleIn">
              <!-- Enhanced Header -->
              <DialogTitle as="div" class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-primary-light dark:bg-primary-dark flex items-center justify-center shadow-lg">
                    <i class="ri-user-add-line text-white text-lg"></i>
                  </div>
                  <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                      Add Member
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Search and add users to your list</p>
                  </div>
                </div>
                <button 
                  @click="closeModal"
                  class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                >
                  <i class="ri-close-line text-xl"></i>
                </button>
              </DialogTitle>

              <div class="space-y-4">
                <!-- Search Input -->
                <div>
                  <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Search Users
                  </label>
                  <div class="relative">
                    <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-base"></i>
                    <input
                      v-model="searchQuery"
                      @input="searchUsers"
                      type="text"
                      placeholder="Search by username or name..."
                      class="w-full pl-10 pr-3 py-3 rounded-xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:bg-white/80 dark:focus:bg-gray-800/80 transition-all duration-200 text-base font-medium"
                    />
                  </div>
                </div>

                <!-- Search Results -->
                <div v-if="loading" class="text-center py-6">
                  <div class="inline-flex items-center gap-3">
                    <div class="w-6 h-6 border-2 border-primary-light dark:border-primary-dark border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-gray-500 dark:text-gray-400">Searching users...</span>
                  </div>
                </div>

                <div v-else-if="searchResults.length > 0" class="space-y-2">
                  <h4 class="text-base font-semibold text-gray-700 dark:text-gray-300">Search Results</h4>
                  <div class="max-h-80 overflow-y-auto space-y-2">
                    <div 
                      v-for="user in searchResults" 
                      :key="user.id"
                      @click="selectUser(user)"
                      class="flex items-center gap-3 p-3 rounded-xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm hover:bg-white/70 dark:hover:bg-gray-800/70 cursor-pointer transition-all duration-200 hover:scale-105"
                    >
                      <div class="relative">
                        <img 
                          :src="user.avatar || '/default-avatar.png'" 
                          :alt="user.name"
                          class="w-10 h-10 rounded-full object-cover ring-2 ring-white/20 dark:ring-gray-700/30"
                        />
                        <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white dark:border-gray-900"></div>
                      </div>
                      <div class="flex-1">
                        <div class="flex items-center gap-2">
                          <span class="font-semibold text-gray-900 dark:text-white">{{ user.name }}</span>
                          <i class="ri-checkbox-circle-fill text-blue-500 text-base"></i>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">@{{ user.username }}</p>
                      </div>
                      <i class="ri-arrow-right-s-line text-lg text-gray-400"></i>
                    </div>
                  </div>
                </div>

                <div v-else-if="searchQuery && !loading" class="text-center py-6">
                  <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="ri-user-search-line text-xl text-gray-400 dark:text-gray-500"></i>
                  </div>
                  <p class="text-gray-500 dark:text-gray-400">No users found matching "{{ searchQuery }}"</p>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                  <div class="flex items-center gap-2">
                    <i class="ri-error-warning-line text-red-500"></i>
                    <span class="text-red-700 dark:text-red-300 text-sm">{{ error }}</span>
                  </div>
                </div>

                <!-- Add Button -->
                <div class="pt-3">
                  <button
                    @click="addMember"
                    :disabled="!selectedUser || isAdding"
                    class="w-full py-3 px-4 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 text-base"
                  >
                    <i v-if="isAdding" class="ri-loader-4-line animate-spin mr-2"></i>
                    <i v-else class="ri-user-add-line mr-2"></i>
                    {{ isAdding ? 'Adding Member...' : (selectedUser ? `Add ${selectedUser.name}` : 'Select a user first') }}
                  </button>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useListStore } from '@/stores/listStore'
import { useToast } from 'vue-toastification'
import axiosInstance from '@/axios'
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue'

const props = defineProps({
  listId: {
    type: [Number, String],
    required: true
  },
  listName: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['member-added', 'close'])

const listStore = useListStore()
const toast = useToast()
const searchQuery = ref('')
const searchResults = ref([])
const loading = ref(false)
const selectedUser = ref(null)
const error = ref('')
const isAdding = ref(false)

const searchUsers = async () => {
  if (searchQuery.value.length < 2) {
    searchResults.value = []
    return
  }

  loading.value = true
  error.value = ''
  try {
    const response = await axiosInstance.get('/users/search', {
      params: { query: searchQuery.value }
    })
    // Backend returns UserResource::collection which wraps data in 'data' key
    // Handle both wrapped and unwrapped responses
    searchResults.value = Array.isArray(response.data) ? response.data : (response.data.data || [])
    console.log('Search results:', searchResults.value)
    console.log('Response structure:', response.data)
  } catch (err) {
    console.error('Error searching users:', err)
    console.error('Error details:', err.response?.data)
    error.value = 'Failed to search users'
    searchResults.value = []
  } finally {
    loading.value = false
  }
}

const selectUser = (user) => {
  selectedUser.value = user
}

const addMember = async () => {
  if (!selectedUser.value) {
    toast.error('Please select a user first')
    return
  }

  isAdding.value = true
  try {
    await listStore.addMemberToList(props.listId, selectedUser.value.id)
    toast.success(`${selectedUser.value.name} added to list successfully`)
    emit('member-added', selectedUser.value)
    closeModal()
  } catch (err) {
    console.error('Error adding member:', err)
    const errorMessage = err.response?.data?.message || 'Failed to add member to list'
    toast.error(errorMessage)
  } finally {
    isAdding.value = false
  }
}

const closeModal = () => {
  searchQuery.value = ''
  searchResults.value = []
  selectedUser.value = null
  error.value = ''
  emit('close')
}

// Debounced search
let searchTimeout
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    searchUsers()
  }, 300)
})
</script>