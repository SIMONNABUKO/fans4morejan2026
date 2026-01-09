<template>
  <TransitionRoot appear :show="true" as="template">
    <Dialog as="div" @close="handleClose" class="relative z-50">
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
                    <i class="ri-add-line text-white text-lg"></i>
                  </div>
                  <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                      Create New List
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Organize your users into meaningful groups</p>
                  </div>
                </div>
                <button 
                  @click="handleClose"
                  class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                >
                  <i class="ri-close-line text-xl"></i>
                </button>
              </DialogTitle>

              <div class="space-y-4">
                <!-- List Name Input -->
                <div>
                  <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    List Name
                  </label>
                  <div class="relative">
                    <i class="ri-list-check absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-base"></i>
                    <input
                      v-model="newListName"
                      type="text"
                      required
                      placeholder="Enter list name..."
                      class="w-full pl-10 pr-3 py-3 rounded-xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:bg-white/80 dark:focus:bg-gray-800/80 transition-all duration-200 text-base font-medium"
                    />
                  </div>
                </div>

                <!-- List Description Input -->
                <div>
                  <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Description (Optional)
                  </label>
                  <textarea
                    v-model="newListDescription"
                    rows="3"
                    placeholder="Describe what this list is for..."
                    class="w-full p-3 rounded-xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:bg-white/80 dark:focus:bg-gray-800/80 transition-all duration-200 resize-none text-base"
                  ></textarea>
                </div>

                <!-- Create Button -->
                <div class="pt-3">
                  <button
                    type="button"
                    @click="createList"
                    :disabled="!newListName.trim() || isCreating"
                    class="w-full py-3 px-4 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 text-base"
                  >
                    <i v-if="isCreating" class="ri-loader-4-line animate-spin mr-2"></i>
                    <i v-else class="ri-add-line mr-2"></i>
                    {{ isCreating ? 'Creating List...' : 'Create List' }}
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
import { ref } from 'vue'
import { useListStore } from '@/stores/listStore'
import { useToast } from 'vue-toastification'
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue'

const emit = defineEmits(['close', 'create'])

const toast = useToast()
const listStore = useListStore()
const newListName = ref('')
const newListDescription = ref('')
const isCreating = ref(false)

const handleClose = () => {
  console.log('Closing CreateListModal')
  newListName.value = ''
  newListDescription.value = ''
  emit('close')
}

const createList = async () => {
  if (!newListName.value.trim()) {
    toast.error('List name cannot be empty')
    return
  }

  isCreating.value = true
  try {
    // Only emit the event, let parent handle the API call
    const listData = {
      name: newListName.value,
      description: newListDescription.value
    }
    emit('create', listData)
    handleClose()
  } catch (error) {
    console.error('Error creating list:', error)
    toast.error(error.message || 'Failed to create list')
  } finally {
    isCreating.value = false
  }
}
</script>