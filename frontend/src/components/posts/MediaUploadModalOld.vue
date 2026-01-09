<template>
<TransitionRoot appear :show="isOpen" as="template">
  <Dialog as="div" @close="close" class="relative z-50">
    <TransitionChild
      as="template"
      enter="duration-300 ease-out"
      enter-from="opacity-0"
      enter-to="opacity-100"
      leave="duration-200 ease-in"
      leave-from="opacity-100"
      leave-to="opacity-0"
    >
      <div class="fixed inset-0 bg-background-light/30 dark:bg-background-dark/80" />
    </TransitionChild>

    <div class="fixed inset-0 overflow-y-auto">
      <div class="flex min-h-full items-center justify-center">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="w-full max-w-2xl h-full transform overflow-hidden bg-background-light dark:bg-background-dark text-text-light-primary dark:text-text-dark-primary transition-all flex flex-col">
            <!-- Header -->
            <div class="flex-shrink-0 flex items-center justify-between p-4 border-b border-border-light dark:border-border-dark">
              <DialogTitle as="h3" class="text-lg font-medium text-text-dark-primary dark:text-text-light-primary">
                Upload media
              </DialogTitle>
              <button 
                @click="close"
                class="text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary"
              >
                <i class="ri-close-line text-2xl"></i>
              </button>
            </div>
            
            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-4">
              <!-- Preview Area -->
              <div class="mb-2">
                <div class="flex gap-6 overflow-x-auto p-2">
                  <TransitionGroup name="preview-list" tag="div" class="flex gap-6">
                    <MediaPreviewItem
                      v-for="(preview, index) in uploadStore.previews"
                      :key="preview.id"
                      :preview="preview"
                      :index="index"
                      @dragStart="startDrag"
                      @drop="onDrop"
                      @dragMove="handleDragMove"
                      @settings="handleSettings"
                      @addFreePreview="handleAddFreePreview"
                    />
                  </TransitionGroup>
                  
                  <!-- Add More Images Button -->
                  <div class="flex-shrink-0 w-[75vw] max-w-2xl h-48">
                    <label class="flex flex-col items-center justify-center w-full h-full border-2 border-dashed border-border-light dark:border-border-dark rounded-lg cursor-pointer hover:border-primary-light dark:hover:border-primary-dark transition-colors">
                      <input 
                        type="file" 
                        multiple 
                        accept="image/*,video/*" 
                        class="hidden" 
                        @change="handleAddMedia"
                      >
                      <i class="ri-add-line text-3xl mb-2 text-text-light-secondary dark:text-text-dark-secondary"></i>
                      <span class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Add More Images</span>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Lock Media Section -->
              <PermissionsManager
                v-model="permissions"
                class="mb-6"
              />

              <!-- Co-Performer Notice -->
              <div class="flex items-start gap-2 text-sm">
                <i class="ri-information-line text-text-light-tertiary dark:text-text-dark-tertiary mt-0.5"></i>
                <p class="text-text-light-secondary dark:text-text-dark-secondary">
                  Uploading with a Co-Performer? Make sure to tag or send in Consent Documents. 
                  <button class="text-primary-500 dark:text-primary-400 hover:underline">Learn more</button>.
                </p>
              </div>
            </div>

            <!-- Footer -->
            <div class="flex-shrink-0 p-4 border-t border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark">
              <div class="flex justify-end gap-3">
                <button 
                  @click="close"
                  class="px-6 py-2 text-text-light-primary dark:text-text-dark-primary hover:text-text-light-primary-hover dark:hover:text-text-dark-primary-hover"
                >
                  Cancel
                </button>
                <button 
                  @click="handleUpload"
                  class="px-6 py-2 bg-primary-light dark:bg-primary-dark text-white rounded hover:bg-primary-light-hover dark:hover:bg-primary-dark-hover"
                >
                  save
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
import { onUnmounted, ref, watch } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { useUploadStore } from '@/stores/uploadStore'
import MediaPreviewItem from './MediaPreviewItem.vue'
import PermissionsManager from './PermissionsManager.vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  initialPermissions: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['close', 'upload'])

const uploadStore = useUploadStore()
const permissions = ref([
  [{ type: 'subscribed_all_tiers', value: null }]
])

const handleAddMedia = (event) => {
  const files = Array.from(event.target.files || [])
  uploadStore.addMedia(files)
  event.target.value = '' // Reset input
}

const startDrag = ({ event, index }) => {
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', index.toString())
}

const onDrop = ({ event, index }) => {
  const draggedIndex = parseInt(event.dataTransfer.getData('text/plain'))
  if (draggedIndex === index) return

  uploadStore.reorderMedia(draggedIndex, index)
}

const handleDragMove = (index) => {
  console.log('Drag move for index:', index)
  // Implement drag move functionality
}

const handleSettings = (index) => {
  console.log('Settings for index:', index)
  // Implement settings functionality
}

const handleAddFreePreview = (index) => {
  console.log('Add free preview for index:', index)
  // Implement add free preview functionality
}

const close = () => {
  emit('close')
}

const handleUpload = () => {
  // console.log('Uploading files:', uploadStore.previews)
  // console.log('With permissions:', permissions.value)
  emit('upload', { files: uploadStore.previews, permissions: permissions.value })
  close()
}

watch(permissions, (newPermissions) => {
  console.log('Permissions updated:', newPermissions)
}, { deep: true })

onUnmounted(() => {
  uploadStore.clearMedia()
})
</script>

<style scoped>
.preview-list-move {
  transition: transform 0.3s ease;
}

.preview-list-enter-active,
.preview-list-leave-active {
  transition: all 0.3s ease;
}

.preview-list-enter-from,
.preview-list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>

