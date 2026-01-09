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
        <div class="fixed inset-0 bg-black/30" />
      </TransitionChild>

      <div class="fixed inset-0">
        <div class="flex min-h-full">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 translate-y-full"
            enter-to="opacity-100 translate-y-0"
            leave="duration-200 ease-in"
            leave-from="opacity-100 translate-y-0"
            leave-to="opacity-0 translate-y-full"
          >
            <DialogPanel class="w-full min-h-full bg-surface-light dark:bg-surface-dark transform transition-all">
              <!-- Header -->
              <div class="flex items-center justify-between p-4 border-b border-border-light dark:border-border-dark">
                <DialogTitle class="text-lg font-medium text-text-light-primary dark:text-text-dark-primary">
                  Post
                </DialogTitle>
                <button 
                  @click="close"
                  class="text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary"
                >
                  <i class="ri-close-line text-2xl"></i>
                </button>
              </div>

              <!-- Content -->
              <div class="p-4 space-y-4">
                <!-- User Input Section -->
                <div class="flex items-start gap-3">
                  <div class="relative">
                    <img 
                      :src="userAvatar" 
                      alt="User Avatar" 
                      class="w-10 h-10 rounded-full object-cover"
                    />
                    <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-accent-success rounded-full border-2 border-surface-light dark:border-surface-dark"></div>
                  </div>
                  <div class="flex-1">
                    <div class="text-text-light-secondary dark:text-text-dark-secondary text-sm mb-1">
                      <button class="hover:underline">
                        No Previews Promotion (click to learn more)
                      </button>
                    </div>
                    <textarea
                      v-model="postContent"
                      placeholder="Type a message..."
                      class="w-full bg-transparent text-text-light-primary dark:text-text-dark-primary placeholder-text-light-secondary dark:placeholder-text-dark-secondary text-base outline-none resize-none"
                      rows="3"
                    ></textarea>
                  </div>
                </div>

                <!-- Post Options -->
                <div class="space-y-1">
                  <label 
                    v-for="(option, index) in postOptions" 
                    :key="index"
                    class="flex items-center gap-2 text-text-light-secondary dark:text-text-dark-secondary text-sm py-1 cursor-pointer"
                  >
                    <div class="relative flex items-center">
                      <input 
                        type="checkbox" 
                        v-model="option.checked"
                        class="w-4 h-4 border border-border-light dark:border-border-dark rounded bg-transparent appearance-none cursor-pointer checked:border-primary-light dark:checked:border-primary-dark"
                      >
                      <i class="ri-check-line absolute left-0 text-sm text-primary-light dark:text-primary-dark pointer-events-none" 
                         :class="option.checked ? 'opacity-100' : 'opacity-0'"></i>
                    </div>
                    <span>{{ option.label }}</span>
                  </label>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-4">
                    <ImageUploadMenu 
                      @upload-new="handleUploadNew"
                      @from-vault="handleFromVault"
                    />
                    <button 
                      v-for="(action, index) in actionButtons" 
                      :key="index"
                      class="text-primary-light dark:text-primary-dark hover:opacity-80 transition-opacity"
                    >
                      <i :class="action.icon" class="text-xl"></i>
                    </button>
                  </div>
                  <div class="flex items-center gap-3">
                    <button class="text-primary-light dark:text-primary-dark hover:opacity-80 transition-opacity">
                      <i class="ri-mic-line text-xl"></i>
                    </button>
                    <button 
                      @click="handlePostSubmission"
                      class="bg-primary-light dark:bg-primary-dark hover:opacity-90 transition-opacity text-white px-6 py-1.5 rounded text-sm font-medium"
                    >
                      Post
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
  <MediaUploadModal
    v-if="showMediaUploadModal"
    :is-open="showMediaUploadModal"
    :initial-permissions="uploadStore.post.permissions"
    @close="closeMediaUploadModal"
    @upload="handleMediaUpload"
  />
</template>

<script setup>
import { ref, computed } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionRoot, TransitionChild } from '@headlessui/vue'
import ImageUploadMenu from './ImageUploadMenu.vue'
import { useUploadStore } from '@/stores/uploadStore'
import MediaUploadModal from './MediaUploadModal.vue';

const props = defineProps({
  isOpen: Boolean,
})

const emit = defineEmits(['close', 'open-upload-preview', 'close-upload-preview'])

const uploadStore = useUploadStore()

const postContent = computed({
  get: () => uploadStore.post.content,
  set: (value) => uploadStore.setPostContent(value)
})

const userAvatar = 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-uI1JDg5gboqSkniC9SI1Grz2b6DmWa.png'

const postOptions = ref([
  { label: 'Promote Post', checked: false },
  { label: 'Pin to Timeline', checked: false },
  { label: 'Add to Walls', checked: false },
  { label: 'Lock Replies', checked: false },
])

const actionButtons = [
  { icon: 'ri-calendar-line' },
  { icon: 'ri-delete-bin-line' },
  { icon: 'ri-loop-left-line' },
  { icon: 'ri-bar-chart-line' },
  { icon: 'ri-menu-line' },
]

const handleUploadNew = () => {
  emit('open-upload-preview')
}

const handleFromVault = () => {
  console.log('From vault clicked')
  // Implement vault functionality here
}

const close = () => {
  emit('close')
}

const showMediaUploadModal = ref(false);

const handleMediaUpload = ({ files, permissions }) => {
  uploadStore.addMedia(files)
  uploadStore.setPermissions(permissions)
  closeMediaUploadModal()
}

const closeMediaUploadModal = () => {
  showMediaUploadModal.value = false
}

const handlePostSubmission = async () => {
  try {

    console.log('Submitting post:', uploadStore.post);
    await uploadStore.submitPost();
    close(); // Close the modal after successful submission
  } catch (error) {
    console.error('Error submitting post:', error);
    // Handle error (e.g., show an error message to the user)
  }
}
</script>

<style scoped>
textarea::placeholder {
  opacity: 0.5;
}
</style>

