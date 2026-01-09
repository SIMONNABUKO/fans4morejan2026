<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="$emit('close')" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-900 p-6 text-left align-middle shadow-xl transition-all">
              <!-- Header -->
              <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                {{ t('customize_message_filters') }}
              </DialogTitle>

              <!-- Loading State -->
              <div v-if="messagesFilterStore.loading" class="flex justify-center items-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-600"></div>
              </div>

              <!-- Error State -->
              <div v-else-if="messagesFilterStore.error" class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg mb-4">
                <p class="text-sm text-red-600 dark:text-red-400">{{ messagesFilterStore.error }}</p>
                <button 
                  @click="messagesFilterStore.clearError()" 
                  class="mt-2 text-sm text-red-600 dark:text-red-400 underline"
                >
                  {{ t('dismiss') }}
                </button>
              </div>

              <!-- Content -->
              <div v-else class="space-y-6">
                <!-- Message Filters -->
                <div class="space-y-3">
                  <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ t('conversation_filters') }}
                  </h3>
                  
                  <div class="space-y-2">
                    <div 
                      v-for="filter in messagesFilterStore.availableFilters" 
                      :key="filter.id"
                      class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg"
                    >
                      <div class="flex items-center space-x-3">
                        <button
                          @click="toggleFilter(filter.id)"
                          class="flex items-center justify-center w-5 h-5 rounded border-2 transition-colors"
                          :class="[
                            filter.enabled 
                              ? 'bg-blue-600 border-blue-600' 
                              : 'border-gray-300 dark:border-gray-600'
                          ]"
                        >
                          <i v-if="filter.enabled" class="ri-check-line text-white text-xs"></i>
                        </button>
                        
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                          {{ filter.label }}
                        </span>
                        
                        <span v-if="filter.required" class="text-xs text-gray-500 dark:text-gray-400">
                          ({{ t('required') }})
                        </span>
                        
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                          {{ filter.type }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Description -->
                <div class="text-sm text-gray-600 dark:text-gray-400">
                  {{ t('select_which_lists_to_show_as_conversation_filters') }}
                </div>
              </div>

              <!-- Actions -->
              <div class="mt-6 flex justify-end gap-3">
                <button
                  @click="$emit('close')"
                  class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                >
                  {{ t('cancel') }}
                </button>
                <button
                  @click="saveAndClose"
                  class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors"
                  :disabled="messagesFilterStore.loading"
                >
                  {{ t('save_changes') }}
                </button>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { useMessagesFilterStore } from '@/stores/messagesFilterStore'
import { useI18n } from 'vue-i18n'
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close'])

const { t } = useI18n()
const messagesFilterStore = useMessagesFilterStore()

const toggleFilter = async (filterId) => {
  try {
    await messagesFilterStore.toggleFilter(filterId)
  } catch (error) {
    console.error('Failed to toggle message filter:', error)
  }
}

const saveAndClose = async () => {
  try {
    await messagesFilterStore.updateFilterPreferences()
    emit('close')
  } catch (error) {
    console.error('Failed to save message filter preferences:', error)
  }
}

// Load filter preferences when modal opens
watch(() => props.isOpen, (isOpen) => {
  if (isOpen) {
    messagesFilterStore.fetchUserFilterPreferences()
  }
})
</script> 