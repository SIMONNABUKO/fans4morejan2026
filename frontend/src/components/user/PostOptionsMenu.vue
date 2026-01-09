<template>
  <div class="relative">
    <Transition
      enter-active-class="transition duration-100 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-75 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div 
        v-if="isOpen"
        class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg z-50 post-options-menu bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
        @click.stop
      >
        <div class="py-0.5">
          <button 
            v-for="option in menuOptions" 
            :key="option.id"
            @click.stop="handleOption(option.id)"
            class="w-full px-4 py-1 text-left text-sm flex items-center gap-3 transition-colors text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/50"
          >
            <i :class="option.icon" class="text-lg"></i>
            {{ option.label }}
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['select', 'close'])

const { t } = useI18n()

const menuOptions = [
  { id: 'copy', label: t('copy_post_link'), icon: 'ri-share-line' },
  { id: 'moveUp', label: t('move_up'), icon: 'ri-arrow-up-line' },
  { id: 'moveDown', label: t('move_down'), icon: 'ri-arrow-down-line' },
  { id: 'unpin', label: t('unpin_post'), icon: 'ri-pushpin-line' },
  { id: 'addToWalls', label: t('add_to_walls'), icon: 'ri-layout-line' },
  { id: 'edit', label: t('edit_post'), icon: 'ri-edit-line' },
  { id: 'delete', label: t('delete_post'), icon: 'ri-delete-bin-line' }
]

const handleOption = (optionId) => {
  emit('select', optionId)
  emit('close')
}
</script>

<style scoped>
.post-options-menu {
  min-width: 180px;
}

.post-options-menu button {
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
}

.post-options-menu button:first-child {
  padding-top: 0.375rem;
}

.post-options-menu button:last-child {
  padding-bottom: 0.5rem;
}

.post-options-menu > div {
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
}
</style>

