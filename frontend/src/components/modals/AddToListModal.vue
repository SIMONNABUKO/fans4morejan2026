<template>
  <TransitionRoot appear :show="isOpen" as="template">
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
                    <i class="ri-list-check text-white text-lg"></i>
                  </div>
                  <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                      Add to List
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Select a list to add this user</p>
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
                <!-- Enhanced User Info -->
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-100/60 dark:bg-gray-700/60 border border-white/20 dark:border-gray-600/50">
                  <div class="relative">
                    <div class="w-10 h-10 rounded-full overflow-hidden ring-4 ring-white/20 dark:ring-gray-700/30 shadow-lg">
                      <img 
                        :src="user?.avatar || '/default-avatar.png'" 
                        :alt="user?.name || 'User'"
                        class="w-full h-full object-cover transition-transform duration-200 hover:scale-110"
                      />
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full border-2 border-white dark:border-gray-900 shadow-lg"></div>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-base text-gray-900 dark:text-white">{{ user?.name || 'User' }}</span>
                      <i class="ri-checkbox-circle-fill text-blue-500 text-base"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Which list would you like to add them to?</p>
                  </div>
                </div>

                <!-- Lists Section -->
                <div>
                  <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                      My Lists
                    </label>
                    <div class="flex items-center gap-2">
                      <button 
                        @click="fetchCustomLists"
                        class="p-1.5 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200"
                        title="Refresh lists"
                      >
                        <i class="ri-refresh-line text-base"></i>
                      </button>
                      <button 
                        @click="handleCreateList"
                        class="p-1.5 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200"
                        title="Create new list"
                      >
                        <i class="ri-add-line text-lg"></i>
                      </button>
                    </div>
                  </div>
                  
                  <div v-if="customLists.length === 0" class="text-center py-6">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100/80 dark:bg-gray-700/80 flex items-center justify-center">
                      <i class="ri-list-check text-xl text-gray-400 dark:text-gray-500"></i>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                      You do not have any lists yet, please create your lists
                    </p>
                  </div>

                  <div v-else class="space-y-2">
                    <button
                      v-for="list in customLists"
                      :key="list.id"
                      type="button"
                      @click.stop="addToList(list.id, $event)"
                      :class="[
                        'w-full text-left p-3 rounded-xl border transition-all duration-200',
                        selectedListId === parseInt(list.id) 
                          ? 'border-primary-light dark:border-primary-dark bg-primary-light/10 dark:bg-primary-dark/10 text-primary-light dark:text-primary-dark'
                          : 'border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm hover:bg-white/70 dark:hover:bg-gray-800/70 text-gray-900 dark:text-white'
                      ]"
                    >
                      <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                          <div class="w-8 h-8 rounded-full bg-primary-light/20 dark:bg-primary-dark/20 flex items-center justify-center">
                            <i class="ri-list-check text-primary-light dark:text-primary-dark text-base"></i>
                          </div>
                          <div>
                            <p class="font-semibold text-base">{{ list.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ list.count }} members</p>
                          </div>
                        </div>
                        <div v-if="selectedListId === parseInt(list.id)" class="w-5 h-5 rounded-full bg-primary-light dark:bg-primary-dark flex items-center justify-center">
                          <i class="ri-check-line text-white text-sm"></i>
                        </div>
                      </div>
                    </button>
                  </div>
                </div>

                <!-- Save Button -->
                <div class="pt-3">
                  <button
                    type="button"
                    class="w-full py-3 px-4 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 text-base"
                    :disabled="!selectedListId || isSaving"
                    @click="handleSave"
                  >
                    <i v-if="isSaving" class="ri-loader-4-line animate-spin mr-2"></i>
                    <i v-else class="ri-save-line mr-2"></i>
                    {{ isSaving ? 'Adding to List...' : 'Save to List' }}
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
import { ref, onMounted, watch, computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { useListStore } from '@/stores/listStore'
import { usePostOptionsStore } from '@/stores/postOptionsStore'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  user: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])
const router = useRouter()
const listStore = useListStore()
const postOptionsStore = usePostOptionsStore()
const toast = useToast()

const customLists = ref([])
const selectedListId = ref(null)
const isSaving = ref(false)

// Computed property to track the selected list
const selectedList = computed(() => {
  return customLists.value.find(list => parseInt(list.id) === selectedListId.value)
})

// Debug computed property
const debugInfo = computed(() => {
  return {
    customListsCount: customLists.value.length,
    selectedListId: selectedListId.value,
    selectedList: selectedList.value,
    modalOpen: props.isOpen
  }
})

// Watch for modal opening to refresh lists
watch(() => props.isOpen, async (isOpen) => {
  console.log('Modal open state changed:', isOpen)
  if (isOpen) {
    // Only fetch if we don't have any lists yet
    if (customLists.value.length === 0) {
      await fetchCustomLists()
    }
  } else {
    // Reset selection when modal closes
    selectedListId.value = null
  }
})

// Watch for selectedListId changes
watch(selectedListId, (newValue, oldValue) => {
  console.log('selectedListId changed:', { oldValue, newValue })
})

onMounted(async () => {
  await fetchCustomLists()
})

const fetchCustomLists = async () => {
  try {
    console.log('Fetching custom lists...')
    await listStore.fetchLists()
    const newCustomLists = listStore.lists.filter(list => !list.is_default)
    console.log('New custom lists:', newCustomLists)
    console.log('Current selectedListId:', selectedListId.value)
    
    // Check if the currently selected list still exists in the new list
    if (selectedListId.value && !newCustomLists.find(list => parseInt(list.id) === selectedListId.value)) {
      console.log('Selected list no longer exists, clearing selection')
      selectedListId.value = null
    }
    
    customLists.value = newCustomLists
    console.log('Updated customLists:', customLists.value)
  } catch (error) {
    console.error('Error fetching custom lists:', error)
    toast.error('Failed to fetch lists')
  }
}

const closeModal = () => {
  emit('close')
  selectedListId.value = null
}

const handleCreateList = () => {
  router.push({ name: 'lists' }) // Assuming you have a route for lists management
  closeModal()
}

const addToList = (listId, event) => {
  console.log('addToList called with listId:', listId, 'type:', typeof listId)
  console.log('Current selectedListId before:', selectedListId.value)
  
  // Prevent any default behavior
  if (event) {
    event.preventDefault()
    event.stopPropagation()
  }
  
  selectedListId.value = parseInt(listId)
  console.log('Selected list ID set to:', selectedListId.value)
}

const handleSave = async () => {
  console.log('handleSave called with selectedListId:', selectedListId.value)
  
  if (!selectedListId.value) {
    toast.error('Please select a list first')
    return
  }

  if (!props.user || !props.user.id) {
    toast.error('No user selected')
    return
  }

  isSaving.value = true
  try {
    console.log('Calling listStore.addMemberToList with listId:', selectedListId.value, 'userId:', props.user.id)
    const result = await listStore.addMemberToList(selectedListId.value, props.user.id)
    
    console.log('Add member result:', result)
    
    // If we get here without an error, the operation was successful
    toast.success('User added to list successfully')
    closeModal()
  } catch (error) {
    console.error('Error adding user to list:', error)
    const errorMessage = error.response?.data?.message || error.message || 'Failed to add user to list'
    toast.error(errorMessage)
  } finally {
    isSaving.value = false
  }
}
</script>