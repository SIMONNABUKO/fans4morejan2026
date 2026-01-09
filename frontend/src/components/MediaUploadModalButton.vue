<template>
  <div class="relative">
    <Menu as="div" class="relative">
      <MenuButton class="text-primary-light dark:text-primary-dark hover:opacity-80 transition-opacity">
        <slot name="trigger">
          <i class="ri-image-line text-xl"></i>
        </slot>
      </MenuButton>

      <transition
        enter-active-class="transition duration-100 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-75 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <MenuItems 
          class="absolute bottom-full mb-2 w-40 rounded-lg bg-surface-light dark:bg-surface-dark shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none border border-border-light dark:border-border-dark"
        >
          <div class="py-1">
            <MenuItem v-slot="{ active }">
              <button
                @click.stop="openMediaUploadModal"
                :class="[
                  active ? 'bg-secondary-light dark:bg-secondary-dark' : '',
                  'flex items-center w-full px-4 py-2 text-sm text-text-light-primary dark:text-text-dark-primary'
                ]"
              >
                <i class="ri-upload-2-line mr-2"></i>
                {{ uploadNewTextComputed }}
              </button>
            </MenuItem>
            <MenuItem v-if="showFromVault" v-slot="{ active }">
              <button
                @click="fromVault"
                :class="[
                  active ? 'bg-secondary-light dark:bg-secondary-dark' : '',
                  'flex items-center w-full px-4 py-2 text-sm text-text-light-primary dark:text-text-dark-primary'
                ]"
              >
                <i class="ri-folder-line mr-2"></i>
                {{ fromVaultTextComputed }}
              </button>
            </MenuItem>
            <slot name="additional-menu-items"></slot>
          </div>
        </MenuItems>
      </transition>
    </Menu>

    <!-- Media Upload Modal -->
    <Teleport to="body">
      <MediaUploadModal
        :is-open="isModalOpen"
        :context-id="contextId"
        :title="modalTitleComputed"
        :accept-types="acceptTypes"
        :add-media-text="addMediaTextComputed"
        :cancel-text="cancelTextComputed"
        :save-text="saveTextComputed"
        :show-permissions="showPermissions"
        :info-text="infoText"
        @close="closeModal"
        @upload="handleUpload"
      >
        <template #additional-content>
          <slot name="modal-content"></slot>
        </template>
      </MediaUploadModal>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { useMediaUpload } from '@/composables/useMediaUpload'
import { useMediaUploadStore } from '@/stores/mediaUploadStore'
import MediaUploadModal from './posts/MediaUploadModal.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  contextId: {
    type: String,
    default: () => `upload-${Date.now()}`
  },
  uploadNewText: {
    type: String,
    default: ''
  },
  fromVaultText: {
    type: String,
    default: ''
  },
  showFromVault: {
    type: Boolean,
    default: true
  },
  modalTitle: {
    type: String,
    default: ''
  },
  acceptTypes: {
    type: String,
    default: 'image/*,video/*'
  },
  addMediaText: {
    type: String,
    default: ''
  },
  cancelText: {
    type: String,
    default: ''
  },
  saveText: {
    type: String,
    default: ''
  },
  showPermissions: {
    type: Boolean,
    default: true
  },
  infoText: {
    type: String,
    default: null
  },
  endpoint: {
    type: String,
    default: '/media'
  },
  initialMetadata: {
    type: Object,
    default: () => ({})
  }
})

const uploadNewTextComputed = computed(() => props.uploadNewText || t('upload_new'))
const fromVaultTextComputed = computed(() => props.fromVaultText || t('from_vault'))
const modalTitleComputed = computed(() => props.modalTitle || t('upload_media'))
const addMediaTextComputed = computed(() => props.addMediaText || t('add_more_images'))
const cancelTextComputed = computed(() => props.cancelText || t('cancel'))
const saveTextComputed = computed(() => props.saveText || t('save'))

const emit = defineEmits(['upload-new', 'from-vault', 'upload-complete', 'upload-error'])

const {
  isModalOpen,
  openModal: openMediaUpload,
  closeModal,
  uploadMedia
} = useMediaUpload({
  contextId: props.contextId,
  endpoint: props.endpoint,
  initialMetadata: props.initialMetadata
})

const mediaUploadStore = useMediaUploadStore()

const openMediaUploadModal = () => {
  openMediaUpload()
  emit('upload-new')
}

const fromVault = () => {
  emit('from-vault')
}

const handleUpload = async (data) => {
  try {
    // Update the store's media with the new data
    mediaUploadStore.currentContent.media = data.files
    mediaUploadStore.currentContent.permissions = data.permissions
    
    // Emit the upload event for the parent component
    emit('upload-complete', data)
  } catch (error) {
    console.error('Upload failed:', error)
    emit('upload-error', error)
  }
}
</script>