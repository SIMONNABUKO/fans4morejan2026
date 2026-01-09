<template>
  <div class="relative">
    <Menu as="div" class="relative">
      <MenuButton class="text-primary-light dark:text-primary-dark hover:opacity-80 transition-opacity">
        <i class="ri-image-line text-xl"></i>
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
                @click="openMediaUploadModal"
                :class="[
                  active ? 'bg-secondary-light dark:bg-secondary-dark' : '',
                  'flex items-center w-full px-4 py-2 text-sm text-text-light-primary dark:text-text-dark-primary'
                ]"
              >
                <i class="ri-upload-2-line mr-2"></i>
                Upload New
              </button>
            </MenuItem>
            <MenuItem v-slot="{ active }">
              <button
                @click="fromVault"
                :class="[
                  active ? 'bg-secondary-light dark:bg-secondary-dark' : '',
                  'flex items-center w-full px-4 py-2 text-sm text-text-light-primary dark:text-text-dark-primary'
                ]"
              >
                <i class="ri-folder-line mr-2"></i>
                From Vault
              </button>
            </MenuItem>
          </div>
        </MenuItems>
      </transition>
    </Menu>
  </div>
</template>

<script setup>
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { useMediaUploadStore } from '@/stores/mediaUploadStore'

const props = defineProps({
  contextId: {
    type: String,
    default: () => `post-media-${Date.now()}`
  }
})

const emit = defineEmits(['upload-new', 'from-vault'])

// Initialize the media upload store with the context
const mediaUploadStore = useMediaUploadStore()
mediaUploadStore.initContext(props.contextId)

const openMediaUploadModal = () => {
  // Set the active context before emitting the event
  mediaUploadStore.activeContext = props.contextId
  emit('upload-new')
}

const fromVault = () => {
  // Set the active context before emitting the event
  mediaUploadStore.activeContext = props.contextId
  emit('from-vault')
}
</script>