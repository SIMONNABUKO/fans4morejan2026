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
                  {{ t('verify_your_email') }}
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
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                      {{ t('enter_6_digit_code') }}
                    </label>
                    <input
                      id="code"
                      v-model="code"
                      type="text"
                      maxlength="6"
                      required
                      class="w-full px-3 py-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 rounded-lg focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200 text-gray-900 dark:text-white text-center text-lg font-mono tracking-widest"
                      :placeholder="t('verification_code_placeholder')"
                      autocomplete="one-time-code"
                    />
                  </div>
                  
                  <div v-if="error" class="p-3 bg-error-light/20 dark:bg-error-dark/20 border border-error-light/30 dark:border-error-dark/30 rounded-lg">
                    <p class="text-error-light dark:text-error-dark text-sm">{{ error }}</p>
                  </div>
                  
                  <div v-if="success" class="p-3 bg-success-light/20 dark:bg-success-dark/20 border border-success-light/30 dark:border-success-dark/30 rounded-lg">
                    <p class="text-success-light dark:text-success-dark text-sm">{{ success }}</p>
                  </div>

                  <!-- Resend Code Section -->
                  <div class="flex justify-center">
                    <button
                      type="button"
                      @click="handleResend"
                      :disabled="isResending || resendCooldown > 0"
                      class="text-primary-light dark:text-primary-dark hover:text-primary-light/80 dark:hover:text-primary-dark/80 text-sm font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <i v-if="isResending" class="ri-loader-4-line animate-spin mr-2"></i>
                      <span v-if="resendCooldown === 0">{{ t('resend_code') }}</span>
                      <span v-else>{{ t('resend_in_seconds', { seconds: resendCooldown }) }}</span>
                    </button>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex gap-3 pt-4">
                    <button
                      type="button"
                      @click="$emit('close')"
                      class="flex-1 py-2 px-4 bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition-all duration-200"
                    >
                      {{ t('cancel') }}
                    </button>
                    <button
                      type="submit"
                      :disabled="isLoading"
                      class="flex-1 py-2 px-4 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-lg font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    >
                      <i v-if="isLoading" class="ri-loader-4-line animate-spin mr-2"></i>
                      <i v-else class="ri-mail-check-line mr-2"></i>
                      {{ t('verify') }}
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
import { ref, watch, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'

const { t } = useI18n();

const emit = defineEmits(['close', 'verified'])
const props = defineProps({
  requestCode: Function,
  verifyCode: Function,
})

const code = ref('')
const error = ref('')
const success = ref('')
const isLoading = ref(false)
const isResending = ref(false)
const resendCooldown = ref(0)
let cooldownTimer = null

const handleSubmit = async () => {
  error.value = ''
  success.value = ''
  isLoading.value = true
  try {
    await props.verifyCode(code.value)
    success.value = t('email_verified')
    emit('verified')
    setTimeout(() => emit('close'), 1000)
  } catch (e) {
    error.value = e?.response?.data?.message || t('invalid_or_expired_code')
  } finally {
    isLoading.value = false
  }
}

const handleResend = async () => {
  error.value = ''
  success.value = ''
  isResending.value = true
  try {
    await props.requestCode()
    success.value = t('new_code_sent')
    startCooldown()
  } catch (e) {
    error.value = e?.response?.data?.message || t('failed_to_resend_code')
  } finally {
    isResending.value = false
  }
}

function startCooldown() {
  resendCooldown.value = 30
  if (cooldownTimer) clearInterval(cooldownTimer)
  cooldownTimer = setInterval(() => {
    resendCooldown.value--
    if (resendCooldown.value <= 0) {
      clearInterval(cooldownTimer)
      cooldownTimer = null
    }
  }, 1000)
}

onUnmounted(() => {
  if (cooldownTimer) clearInterval(cooldownTimer)
})
</script> 