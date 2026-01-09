<template>
  <div class="relative">
    <button @click="toggleDropdown" class="flex items-center gap-2 rounded-full bg-secondary dark:bg-secondary-dark p-1 hover:bg-secondary-dark dark:hover:bg-secondary">
      <i :class="themeIcon" class="text-xl text-content-light dark:text-content-dark-light"></i>
    </button>
    <div v-if="isOpen" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-base dark:bg-base-dark ring-1 ring-black ring-opacity-5">
      <div class="py-1">
        <button
          v-for="option in themeOptions"
          :key="option.value"
          @click="setTheme(option.value)"
          class="flex items-center w-full text-left px-4 py-2 text-sm text-content dark:text-content-dark hover:bg-secondary dark:hover:bg-secondary-dark"
        >
          <i :class="option.icon" class="mr-2"></i> {{ option.label }}
          <i v-if="theme === option.value" class="ri-check-line ml-auto"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useTheme } from '../composables/useTheme'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const { theme, updateTheme } = useTheme()
const isOpen = ref(false)

const themeIcon = computed(() => {
  switch (theme.value) {
    case 'light': return 'ri-sun-line'
    case 'dark': return 'ri-moon-line'
    default: return 'ri-contrast-2-line'
  }
})

const themeOptions = [
  { label: 'light_mode', value: 'light', icon: 'ri-sun-line' },
  { label: 'dark_mode', value: 'dark', icon: 'ri-moon-line' },
  { label: 'system_mode', value: 'system', icon: 'ri-contrast-2-line' },
]

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const setTheme = (newTheme) => {
  updateTheme(newTheme)
  isOpen.value = false
}

const closeDropdown = (event) => {
  if (!event.target.closest('.relative')) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
})
</script>