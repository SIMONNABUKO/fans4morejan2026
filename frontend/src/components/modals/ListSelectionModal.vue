<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-[9999]">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full w-full">
          <TransitionChild
            as="template"
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
              <div class="flex items-center justify-between p-4 border-b border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm">
                <DialogTitle as="h3" class="text-xl font-bold text-gray-900 dark:text-white">
                  Add to List
                </DialogTitle>
                <button 
                  @click="closeModal"
                  class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200"
                >
                  <i class="ri-close-line text-xl"></i>
                </button>
              </div>

              <!-- Enhanced Content -->
              <div class="p-4 space-y-4">
                <!-- Loading State -->
                <div v-if="loading" class="text-center py-4">
                  <div class="w-8 h-8 border-2 border-primary-light dark:border-primary-dark border-t-transparent rounded-full animate-spin mx-auto"></div>
                  <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Loading lists...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="text-center py-4">
                  <p class="text-error-light dark:text-error-dark text-sm">{{ error }}</p>
                  <button @click="loadLists" class="mt-2 text-primary-light dark:text-primary-dark text-sm hover:underline">
                    Try again
                  </button>
                </div>

                <!-- Content -->
                <div v-else>
                  <!-- Existing Lists -->
                  <div v-if="customLists.length > 0" class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select a list:</h4>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                      <button
                        v-for="list in customLists"
                        :key="list.id"
                        @click="addToExistingList(list)"
                        :disabled="addingToList"
                        class="w-full text-left p-3 rounded-lg border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 disabled:opacity-50"
                      >
                        <div class="flex items-center justify-between">
                          <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ list.name }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ list.count || 0 }} members</p>
                          </div>
                          <i v-if="addingToList" class="ri-loader-4-line animate-spin text-primary-light dark:text-primary-dark"></i>
                        </div>
                      </button>
                    </div>
                  </div>

                  <!-- No Lists Message -->
                  <div v-else class="mb-4 p-3 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-lg border border-white/20 dark:border-gray-700/50">
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                      No lists available. Create your first list below!
                    </p>
                  </div>

                  <!-- Create New List -->
                  <div class="border-t border-white/20 dark:border-gray-700/50 pt-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      {{ customLists.length > 0 ? 'Or create a new list:' : 'Create a new list:' }}
                    </h4>
                    <div class="space-y-3">
                      <input
                        v-model="newListName"
                        type="text"
                        placeholder="Enter list name"
                        class="w-full px-3 py-2 border border-white/20 dark:border-gray-700/50 rounded-lg text-sm text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
                        :disabled="creatingList"
                      />
                      <button
                        @click="createNewList"
                        :disabled="!newListName.trim() || creatingList"
                        class="w-full px-4 py-2 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-lg text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
                      >
                        <span v-if="creatingList" class="flex items-center justify-center">
                          <i class="ri-loader-4-line animate-spin mr-2"></i>
                          Creating...
                        </span>
                        <span v-else>Create List</span>
                      </button>
                    </div>
                  </div>
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
import { ref, onMounted, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { useListStore } from '@/stores/listStore'
import { useToast } from '@/composables/useToast'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  userId: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['close', 'user-added'])

const listStore = useListStore()
const toast = useToast()

const loading = ref(false)
const error = ref(null)
const customLists = ref([])
const newListName = ref('')
const creatingList = ref(false)
const addingToList = ref(false)

const loadLists = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Always fetch fresh lists when modal opens to ensure most up-to-date data
    await listStore.fetchLists(true)

    // Exclude system lists from selection (blocked / muted). Keep default + custom lists
    customLists.value = listStore.lists.filter(
      (list) => !['Blocked Accounts', 'Muted Accounts'].includes(list.name)
    )
  } catch (err) {
    error.value = 'Failed to load lists. Please try again.'
    console.error('Error loading lists:', err)
  } finally {
    loading.value = false
  }
}

const addToExistingList = async (list) => {
  addingToList.value = true
  
  try {
    const result = await listStore.addMemberToList(list.id, props.userId)
    if (result.message) {
      toast.success(`User added to ${list.name} list`)
      emit('user-added', { listId: list.id, listName: list.name })
      closeModal()
    } else {
      toast.error('Failed to add user to list')
    }
  } catch (err) {
    console.error('Error adding user to list:', err)
    toast.error('Failed to add user to list')
  } finally {
    addingToList.value = false
  }
}

const createNewList = async () => {
  if (!newListName.value.trim()) return
  
  creatingList.value = true
  
  try {
    const newList = await listStore.createList({
      name: newListName.value.trim(),
      description: 'List created from conversation'
    })
    
    // Add user to the newly created list
    const result = await listStore.addMemberToList(newList.id, props.userId)
    if (result.message) {
      toast.success(`User added to ${newList.name} list`)
      emit('user-added', { listId: newList.id, listName: newList.name })
      closeModal()
    } else {
      toast.error('Failed to add user to new list')
    }
  } catch (err) {
    console.error('Error creating list:', err)
    toast.error('Failed to create list')
  } finally {
    creatingList.value = false
  }
}

const closeModal = () => {
  newListName.value = ''
  error.value = null
  emit('close')
}

// Watch for modal open state to (re)load lists
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    loadLists()
  }
})

onMounted(() => {
  console.log('🎯 ListSelectionModal mounted, isOpen:', props.isOpen)
  if (props.isOpen) {
    loadLists()
  }
})
</script> 