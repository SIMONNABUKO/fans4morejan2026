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
                  <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center shadow-lg">
                    <i class="ri-delete-bin-line text-white text-lg"></i>
                  </div>
                  <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                      Delete List
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">This action cannot be undone</p>
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
                <!-- Warning Message -->
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                  <div class="flex items-center gap-3">
                    <i class="ri-error-warning-line text-red-500 text-xl"></i>
                    <div>
                      <h4 class="font-semibold text-red-700 dark:text-red-300">Warning</h4>
                      <p class="text-red-600 dark:text-red-400 text-sm">Deleting this list will permanently remove all members and cannot be undone.</p>
                    </div>
                  </div>
                </div>

                <!-- List Info -->
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-100/60 dark:bg-gray-700/60 border border-white/20 dark:border-gray-600/50">
                  <div class="w-10 h-10 rounded-full bg-primary-light/20 dark:bg-primary-dark/20 flex items-center justify-center">
                    <i class="ri-list-check text-primary-light dark:text-primary-dark text-lg"></i>
                  </div>
                  <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-white">{{ list?.name }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ list?.count || 0 }} members will be removed</p>
                  </div>
                </div>

                <!-- Confirmation Input -->
                <div>
                  <label class="block text-base font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Type "DELETE" to confirm
                  </label>
                  <input
                    v-model="confirmationText"
                    type="text"
                    placeholder="Type DELETE to confirm..."
                    class="w-full p-3 rounded-xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:bg-white/80 dark:focus:bg-gray-800/80 transition-all duration-200 text-base font-medium"
                  />
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-3">
                  <button
                    type="button"
                    @click="closeModal"
                    class="flex-1 py-3 px-4 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-semibold transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-500/50 text-base"
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    @click="deleteList"
                    :disabled="confirmationText !== 'DELETE' || isDeleting"
                    class="flex-1 py-3 px-4 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-red-500/50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 text-base"
                  >
                    <i v-if="isDeleting" class="ri-loader-4-line animate-spin mr-2"></i>
                    <i v-else class="ri-delete-bin-line mr-2"></i>
                    {{ isDeleting ? 'Deleting...' : 'Delete List' }}
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
import { useToast } from 'vue-toastification'
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue'

const props = defineProps({
  isOpen: { type: Boolean, required: true },
  list: { type: Object, default: null }
})

const emit = defineEmits(['close', 'deleted'])

const toast = useToast()
const confirmationText = ref('')
const isDeleting = ref(false)

const closeModal = () => {
  confirmationText.value = ''
  emit('close')
}

const deleteList = async () => {
  if (confirmationText.value !== 'DELETE') {
    toast.error('Please type "DELETE" to confirm')
    return
  }

  if (!props.list?.id) {
    toast.error('Invalid list data')
    return
  }

  isDeleting.value = true
  try {
    emit('deleted', props.list.id)
    closeModal()
  } catch (error) {
    console.error('Error deleting list:', error)
    toast.error('Failed to delete list')
  } finally {
    isDeleting.value = false
  }
}
</script> 