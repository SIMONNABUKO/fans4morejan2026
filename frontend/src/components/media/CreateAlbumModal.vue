<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div 
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center px-4"
      >
        <!-- Backdrop -->
        <div 
          class="absolute inset-0 bg-black/50 backdrop-blur-sm"
          @click="$emit('close')"
        ></div>
  
        <!-- Modal -->
        <Transition
          enter-active-class="transition duration-200 ease-out"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition duration-150 ease-in"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <div 
            v-if="isOpen"
            class="relative w-full sm:max-w-md rounded-lg p-4 sm:p-6 bg-background-light dark:bg-background-dark shadow-2xl"
          >
          <!-- Close button -->
          <button 
            class="absolute right-3 top-3 p-1 rounded-full transition-colors text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary"
            @click="$emit('close')"
          >
            <i class="ri-close-line text-xl"></i>
          </button>
  
          <!-- Content -->
          <div class="space-y-4">
            <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">
              {{ t('create_new_album') }}
            </h2>
  
            <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary">
              {{ t('album_description') }}
            </p>
  
            <div class="space-y-4">
              <div>
                <input
                  v-model="title"
                  type="text"
                  :placeholder="t('album_title')"
                  class="w-full px-3 py-2 rounded-lg border text-sm transition-colors bg-background-light dark:bg-background-dark border-border-light dark:border-border-dark text-text-light-primary dark:text-text-dark-primary placeholder-text-light-secondary dark:placeholder-text-dark-secondary focus:border-primary-light dark:focus:border-primary-dark focus:ring-1 focus:ring-primary-light dark:focus:ring-primary-dark"
                />
              </div>
  
              <div>
                <textarea
                  v-model="description"
                  :placeholder="t('album_description_placeholder')"
                  rows="3"
                  class="w-full px-3 py-2 rounded-lg border text-sm transition-colors resize-none bg-background-light dark:bg-background-dark border-border-light dark:border-border-dark text-text-light-primary dark:text-text-dark-primary placeholder-text-light-secondary dark:placeholder-text-dark-secondary focus:border-primary-light dark:focus:border-primary-dark focus:ring-1 focus:ring-primary-light dark:focus:ring-primary-dark"
                ></textarea>
              </div>
  
              <label class="flex items-center gap-2 cursor-pointer text-text-light-primary dark:text-text-dark-primary">
                <input
                  v-model="isPublic"
                  type="checkbox"
                  class="w-4 h-4 rounded border-border-light dark:border-border-dark text-primary-light dark:text-primary-dark focus:ring-primary-light dark:focus:ring-primary-dark"
                />
                <span class="text-sm">{{ t('public') }}</span>
              </label>
            </div>
  
            <!-- Actions -->
            <div class="flex justify-end gap-3 mt-6">
              <button
                @click="$emit('close')"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors text-text-light-primary dark:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark"
              >
                {{ t('dismiss') }}
              </button>
              <button
                @click="handleCreate"
                class="px-4 py-2 rounded-lg bg-primary-light dark:bg-primary-dark text-white text-sm font-medium transition-colors hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 disabled:opacity-50 disabled:cursor-not-allowed"
                :disabled="!isValid"
              >
                {{ t('create') }}
              </button>
            </div>
          </div>
        </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close', 'create'])

const title = ref('')
const description = ref('')
const isPublic = ref(false)

const isValid = computed(() => title.value.trim().length > 0)

const handleCreate = () => {
  if (!isValid.value) return

  emit('create', {
    title: title.value,
    description: description.value,
    isPublic: isPublic.value
  })

  // Reset form
  title.value = ''
  description.value = ''
  isPublic.value = false
}
</script>

