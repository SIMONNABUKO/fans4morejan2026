<template>
  <TransitionRoot appear :show="true" as="template">
    <Dialog as="div" @close="$emit('close')" class="relative z-50">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full w-full">
          <TransitionChild
            as="template"
            enter="duration-500 ease-out"
            enter-from="opacity-0 translate-y-full scale-95"
            enter-to="opacity-100 translate-y-0 scale-100"
            leave="duration-300 ease-in"
            leave-from="opacity-100 translate-y-0 scale-100"
            leave-to="opacity-0 translate-y-full scale-95"
            class="w-full"
          >
            <DialogPanel class="w-full min-h-full bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl transform transition-all">
              <!-- Modern Header -->
              <div class="flex items-center justify-between p-4 border-b border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm">
                <DialogTitle class="text-xl font-bold text-gray-900 dark:text-white">
                  {{ $t('change_display_name') }}
                </DialogTitle>
                <button 
                  @click="$emit('close')"
                  class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200"
                >
                  <i class="ri-close-line text-xl"></i>
                </button>
              </div>

              <!-- Enhanced Content -->
              <div class="p-4 space-y-4">
                <form @submit.prevent="handleSubmit" class="space-y-4">
                  <div>
                    <label for="newDisplayName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      {{ $t('new_display_name') }}
                    </label>
                    <input
                      id="newDisplayName"
                      v-model="newDisplayName"
                      type="text"
                      required
                      class="w-full px-3 py-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 rounded-lg focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200 text-gray-900 dark:text-white"
                      :placeholder="currentName"
                    />
                  </div>
                  
                  <div v-if="error" class="p-3 bg-error-light/20 dark:bg-error-dark/20 border border-error-light/30 dark:border-error-dark/30 rounded-lg">
                    <p class="text-error-light dark:text-error-dark text-sm">{{ error }}</p>
                  </div>
                  
                  <div v-if="success" class="p-3 bg-success-light/20 dark:bg-success-dark/20 border border-success-light/30 dark:border-success-dark/30 rounded-lg">
                    <p class="text-success-light dark:text-success-dark text-sm">{{ success }}</p>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex gap-3 pt-4">
                    <button
                      type="button"
                      @click="$emit('close')"
                      class="flex-1 py-2 px-4 bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-all duration-200"
                    >
                      {{ $t('cancel') }}
                    </button>
                    <button
                      type="submit"
                      class="flex-1 py-2 px-4 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-lg font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                      :disabled="saving"
                    >
                      <i v-if="saving" class="ri-loader-4-line animate-spin mr-2"></i>
                      {{ saving ? $t('saving') : $t('save') }}
                    </button>
                  </div>
                </form>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
  currentName: {
    type: String,
    required: true
  },
  saving: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  success: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['close', 'update']);

const newDisplayName = ref(props.currentName);

const handleSubmit = () => {
  if (newDisplayName.value && newDisplayName.value !== props.currentName) {
    emit('update', newDisplayName.value);
  } else {
    emit('close');
  }
};
</script>

