<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-50" @close="closeModal">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all">
              <!-- Header -->
              <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                {{ t('customize_feed_filters') }}
              </DialogTitle>

              <!-- Loading State -->
              <div v-if="feedFilterStore.loading || listStore.loading" class="flex justify-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-blue-500"></div>
              </div>

              <!-- Error State -->
              <div v-else-if="feedFilterStore.error" class="text-red-500 text-center py-4">
                {{ feedFilterStore.error }}
              </div>

              <!-- Content -->
              <div v-else class="space-y-6">
                <!-- Feed Filters -->
                <div class="space-y-3">
                  <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ t('feed_filters') }}
                  </h3>
                  
                  <div class="space-y-2">
                    <div 
                      v-for="filter in feedFilterStore.availableFilters" 
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
                      
                      <!-- Delete buttons removed in feed customization modal -->
                    </div>
                  </div>
                </div>

                <!-- Add List Filter Section -->
                <div>
                  <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    {{ t('add_list_as_filter') }}
                  </h4>
                  <!-- Create New List -->
                  <div class="flex items-center gap-3 mb-4">
                    <input
                      v-model="newListName"
                      type="text"
                      :placeholder="t('new_list_name')"
                      class="flex-1 px-3 py-2 text-sm bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <button
                      @click="createNewList"
                      :disabled="creatingList || !newListName.trim()"
                      class="px-3 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 disabled:opacity-50 transition-colors"
                    >
                      <i v-if="creatingList" class="ri-loader-4-line animate-spin" />
                      <span v-else>{{ t('create') }}</span>
                    </button>
                  </div>
                  <!-- Loading Lists -->
                  <div v-if="listStore.loading" class="text-center py-4">
                    <div class="animate-spin rounded-full h-6 w-6 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
                  </div>

                  <!-- Available Lists -->
                  <div v-else class="space-y-2">
                    <div
                      v-for="list in availableLists"
                      :key="list.id"
                      class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                    >
                      <div class="flex items-center gap-3">
                        <div class="w-5 h-5 flex items-center justify-center">
                          <i class="ri-list-check text-gray-400 text-sm"></i>
                        </div>
                        <div>
                          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ list.name }}
                          </span>
                          <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ list.count || 0 }} {{ t('members') }}
                          </div>
                        </div>
                      </div>
                      <button
                        @click="addListAsFilter(list.id, list.name)"
                        class="text-blue-500 hover:text-blue-700 dark:hover:text-blue-400 p-1"
                      >
                        <i class="ri-add-line text-sm"></i>
                      </button>
                    </div>
                  </div>

                  <!-- Message removed when no lists are available -->
                </div>
              </div>

              <!-- Footer -->
              <div class="mt-6 flex justify-end gap-3">
                <button
                  @click="closeModal"
                  class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                >
                  {{ t('close') }}
                </button>
                <button
                  @click="refreshLists"
                  class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors"
                >
                  {{ t('refresh_lists') }}
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
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useFeedFilterStore } from '@/stores/feedFilterStore'
import { useListStore } from '@/stores/listStore'
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from '@headlessui/vue'

const { t } = useI18n()

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close'])

const feedFilterStore = useFeedFilterStore()
const listStore = useListStore()

const newListName = ref('')
const creatingList = ref(false)

// Computed properties
const availableLists = computed(() => {
  const userLists = listStore.lists
  const addedListIds = feedFilterStore.availableFilters
    .filter(filter => filter.type === 'list' && filter.list_id)
    .map(filter => filter.list_id)
  
  // Show lists that are not yet added as filters
  return userLists.filter(list => !addedListIds.includes(list.id))
})

// Methods
const closeModal = () => {
  emit('close')
}

const toggleFilter = async (filterId) => {
  try {
    await feedFilterStore.toggleFilter(filterId)
  } catch (error) {
    console.error('Error toggling filter:', error)
  }
}

const addListAsFilter = async (listId, listName) => {
  try {
    await feedFilterStore.addListAsFilter(listId, listName)
  } catch (error) {
    console.error('Error adding list as filter:', error)
  }
}

const removeListFilter = async (filterId) => {
  try {
    await feedFilterStore.removeListFilter(filterId)
  } catch (error) {
    console.error('Error removing list filter:', error)
  }
}

// Delete actions are disabled in this modal
const canDeleteFilter = () => false

const createNewList = async () => {
  if (!newListName.value.trim()) return
  creatingList.value = true
  try {
    const newList = await listStore.createList({ name: newListName.value.trim() })
    // Refresh lists to include the new list
    await listStore.fetchLists(true)
    // Sync filters to automatically add the new list as a filter
    await feedFilterStore.syncFiltersFromLists()
    newListName.value = ''
  } catch (error) {
    console.error('Error creating new list:', error)
  } finally {
    creatingList.value = false
  }
}

const refreshLists = async () => {
  try {
    await Promise.all([
      feedFilterStore.refreshAvailableLists(),
      feedFilterStore.fetchUserFilterPreferences()
    ])
  } catch (error) {
    console.error('Error refreshing lists:', error)
  }
}

// Lifecycle
onMounted(async () => {
  if (props.isOpen) {
    try {
      await Promise.all([
        listStore.fetchLists(),
        feedFilterStore.fetchUserFilterPreferences()
      ])
    } catch (error) {
      console.error('Error loading initial data:', error)
    }
  }
})
</script> 