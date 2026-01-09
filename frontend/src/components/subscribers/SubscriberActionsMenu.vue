<template>
<div class="relative">
  <button 
    @click.stop="toggleMenu"
    class="p-1.5 sm:p-2 text-text-light-secondary dark:text-text-dark-secondary hover:bg-surface-light dark:hover:bg-surface-dark rounded-full"
  >
    <i class="ri-settings-3-line text-lg sm:text-xl"></i>
  </button>

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
      class="absolute right-0 mt-2 w-48 py-0.5 bg-surface-light dark:bg-surface-dark rounded-lg shadow-lg z-50 border border-border-light dark:border-border-dark"
      v-click-outside="closeMenu"
    >
      <button 
        v-for="item in menuItems" 
        :key="item.id"
        @click.stop="handleAction(item.id)"
        class="w-full px-4 py-1 text-left text-sm flex items-center gap-2 text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light-hover dark:hover:bg-surface-dark-hover hover:text-text-light-primary dark:hover:text-text-dark-primary transition-colors"
      >
        <i :class="item.icon" class="text-base"></i>
        {{ item.label }}
      </button>
    </div>
  </Transition>
</div>
</template>

<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  subscriberId: {
    type: [String, Number],
    required: true
  }
})

const emit = defineEmits(['action'])

const isOpen = ref(false)

const { t } = useI18n()

const menuItems = [
  { id: 'earnings', label: t('earnings'), icon: 'ri-money-dollar-circle-line' },
  { id: 'addToList', label: t('add_to_list'), icon: 'ri-list-check' },
  { id: 'report', label: t('report_post'), icon: 'ri-flag-line' },
  { id: 'vip', label: t('vip'), icon: 'ri-user-star-line' },
  { id: 'mute', label: t('mute'), icon: 'ri-volume-mute-line' },
  { id: 'block', label: t('block'), icon: 'ri-forbid-line' }
]

const toggleMenu = () => {
  isOpen.value = !isOpen.value
}

const closeMenu = () => {
  isOpen.value = false
}

const handleAction = (actionId) => {
  emit('action', { subscriberId: props.subscriberId, action: actionId })
  closeMenu()
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

