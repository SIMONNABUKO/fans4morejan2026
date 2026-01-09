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
                  Change Password
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
                    <label for="currentPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Current Password
                    </label>
                    <input
                      id="currentPassword"
                      v-model="currentPassword"
                      type="password"
                      required
                      class="w-full px-3 py-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 rounded-lg focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200 text-gray-900 dark:text-white"
                    />
                  </div>
                  
                  <div>
                    <label for="newPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      New Password
                    </label>
                    <input
                      id="newPassword"
                      v-model="newPassword"
                      type="password"
                      required
                      class="w-full px-3 py-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 rounded-lg focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200 text-gray-900 dark:text-white"
                    />
                  </div>
                  
                  <div>
                    <label for="confirmPassword" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Confirm New Password
                    </label>
                    <input
                      id="confirmPassword"
                      v-model="confirmPassword"
                      type="password"
                      required
                      class="w-full px-3 py-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 rounded-lg focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200 text-gray-900 dark:text-white"
                    />
                  </div>

                  <!-- Error Message -->
                  <div v-if="localError || error" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <p class="text-red-700 dark:text-red-400 text-sm">{{ localError || error }}</p>
                  </div>

                  <!-- Success Message -->
                  <div v-if="success" class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                    <p class="text-green-700 dark:text-green-400 text-sm">{{ success }}</p>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex gap-3 pt-4">
                    <button
                      type="button"
                      @click="$emit('close')"
                      class="flex-1 py-2 px-4 bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-all duration-200"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      :disabled="saving"
                      class="flex-1 py-2 px-4 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-lg font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    >
                      <i v-if="saving" class="ri-loader-4-line animate-spin mr-2"></i>
                      <i v-else class="ri-lock-password-line mr-2"></i>
                      {{ saving ? 'Saving...' : 'Save Changes' }}
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
  error: {
    type: String,
    default: ''
  },
  success: {
    type: String,
    default: ''
  },
  saving: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'update']);

const currentPassword = ref('');
const newPassword = ref('');
const confirmPassword = ref('');
const localError = ref('');

const handleSubmit = () => {
  localError.value = ''
  
  // Validate passwords
  if (!currentPassword.value || !newPassword.value || !confirmPassword.value) {
    localError.value = 'All password fields are required';
    return;
  }
  
  if (newPassword.value !== confirmPassword.value) {
    localError.value = 'New passwords do not match';
    return;
  }
  
  if (newPassword.value.length < 8) {
    localError.value = 'New password must be at least 8 characters long';
    return;
  }
  
  emit('update', currentPassword.value, newPassword.value);
};
</script>

