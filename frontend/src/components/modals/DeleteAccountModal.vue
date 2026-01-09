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
                <DialogTitle class="text-xl font-bold text-error-light dark:text-error-dark">
                  <i class="ri-error-warning-line mr-2"></i>
                  Delete Account
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
                <div class="p-4 bg-error-light/10 dark:bg-error-dark/10 border border-error-light/20 dark:border-error-dark/20 rounded-lg">
                  <p class="text-gray-700 dark:text-gray-300 text-sm">
                    <i class="ri-alert-line mr-2 text-error-light dark:text-error-dark"></i>
                    Are you sure you want to delete your account? This action cannot be undone.
                  </p>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-4">
                  <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      Enter Your Password to Confirm
                    </label>
                    <input
                      id="password"
                      v-model="password"
                      type="password"
                      required
                      class="w-full px-3 py-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 rounded-lg focus:ring-2 focus:ring-error-light dark:focus:ring-error-dark focus:border-transparent transition-all duration-200 text-gray-900 dark:text-white"
                    />
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
                      class="flex-1 py-2 px-4 bg-error-light dark:bg-error-dark hover:bg-error-light/90 dark:hover:bg-error-dark/90 text-white rounded-lg font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-error-light dark:focus:ring-error-dark"
                    >
                      <i class="ri-delete-bin-line mr-2"></i>
                      Delete Account
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

const emit = defineEmits(['close', 'confirm']);

const password = ref('');

const handleSubmit = () => {
  if (password.value) {
    emit('confirm', password.value);
    password.value = ''; // Clear the password after submission
  }
};
</script>

