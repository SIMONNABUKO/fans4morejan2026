<template>
<div class="relative z-[9998]">
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
      class="absolute right-0 mt-1 w-40 py-1 bg-surface-light dark:bg-surface-dark rounded-lg shadow-lg border border-border-light dark:border-border-dark z-[9999]"
      v-click-outside="close"
      @click.stop
    >
      <button 
        v-for="item in menuItems" 
        :key="item.id"
        @click.stop="handleAction(item.id)"
        class="w-full px-3 py-1.5 text-left text-sm flex items-center gap-2 text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light-hover dark:hover:bg-surface-dark-hover transition-colors"
      >
        <i :class="item.icon" class="text-base"></i>
        {{ item.label }}
      </button>
    </div>
  </Transition>
</div>
</template>

<script setup>
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

const emit = defineEmits(['close', 'action', 'show-list-modal'])
const listStore = useListStore()
const toast = useToast()

const menuItems = [
  { id: 'add-to-list', label: 'Add to List', icon: 'ri-list-check' },
  { id: 'block', label: 'Block user', icon: 'ri-forbid-line' }
]

const handleAction = async (actionId) => {
  try {
    switch (actionId) {
      case 'add-to-list':
        // Show list selection modal instead of auto-adding
        emit('show-list-modal', props.userId)
        break
      case 'block':
        await handleBlockUser()
        break
      default:
        emit('action', actionId)
    }
  } catch (error) {
    console.error('Error handling menu action:', error)
    toast.error('An error occurred while processing your request')
  }
  
  emit('close')
}

const handleAddToList = async () => {
  // This function is now replaced by the modal approach
  // Keeping it for reference but it's not used anymore
}

const handleBlockUser = async () => {
  try {
    const result = await listStore.addToBlockedList(props.userId)
    if (result.message) {
      toast.success('User blocked successfully')
      emit('action', 'block')
    } else {
      toast.error(result.message || 'Failed to block user')
    }
  } catch (error) {
    console.error('Error blocking user:', error)
    toast.error('Failed to block user')
  }
}

const close = () => {
  emit('close')
}

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el._clickOutside = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value(event)
      }
    }
    document.addEventListener('click', el._clickOutside)
  },
  unmounted(el) {
    document.removeEventListener('click', el._clickOutside)
  }
}
</script>

