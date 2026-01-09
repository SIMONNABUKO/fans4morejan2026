<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
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
        <div class="flex min-h-full items-center justify-center p-2 text-center">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95 translate-y-4"
            enter-to="opacity-100 scale-100 translate-y-0"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100 translate-y-0"
            leave-to="opacity-0 scale-95 translate-y-4"
          >
            <DialogPanel
              class="w-full h-full max-w-none transform overflow-hidden bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 p-6 text-left align-middle shadow-2xl transition-all animate-scaleIn"
            >
              <!-- Enhanced Header -->
              <DialogTitle class="mb-8">
                <div class="flex items-center gap-4 mb-2">
                  <div class="w-12 h-12 rounded-full bg-primary-light dark:bg-primary-dark flex items-center justify-center shadow-lg">
                    <i class="ri-error-warning-line text-white text-xl"></i>
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                      {{ contentType === 'user' ? 'Report User' : $t('report_content') }}
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Help us keep the community safe</p>
                  </div>
                </div>
              </DialogTitle>

              <div class="space-y-6">
                <!-- Enhanced Report Reasons -->
                <div class="space-y-4">
                  <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Select a reason for reporting:</h4>
                  
                  <div class="space-y-3">
                    <label class="flex items-center p-4 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 cursor-pointer transition-all duration-200 hover:bg-white/70 dark:hover:bg-gray-800/70 hover:shadow-lg">
                      <input
                        type="radio"
                        name="report-reason"
                        value="tos"
                        v-model="selectedReason"
                        class="w-5 h-5 text-red-500 bg-white/50 dark:bg-gray-700/50 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800"
                      />
                      <div class="ml-4 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-light/20 dark:bg-primary-dark/20 flex items-center justify-center">
                          <i class="ri-file-damage-line text-primary-light dark:text-primary-dark text-lg"></i>
                        </div>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">
                          {{ $t('report_reason_tos') }}
                        </span>
                      </div>
                    </label>

                    <label class="flex items-center p-4 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 cursor-pointer transition-all duration-200 hover:bg-white/70 dark:hover:bg-gray-800/70 hover:shadow-lg">
                      <input
                        type="radio"
                        name="report-reason"
                        value="dmca"
                        v-model="selectedReason"
                        class="w-5 h-5 text-red-500 bg-white/50 dark:bg-gray-700/50 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800"
                      />
                      <div class="ml-4 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-light/20 dark:bg-primary-dark/20 flex items-center justify-center">
                          <i class="ri-copyright-line text-primary-light dark:text-primary-dark text-lg"></i>
                        </div>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">
                          {{ $t('report_reason_dmca') }}
                        </span>
                      </div>
                    </label>

                    <label class="flex items-center p-4 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 cursor-pointer transition-all duration-200 hover:bg-white/70 dark:hover:bg-gray-800/70 hover:shadow-lg">
                      <input
                        type="radio"
                        name="report-reason"
                        value="spam"
                        v-model="selectedReason"
                        class="w-5 h-5 text-red-500 bg-white/50 dark:bg-gray-700/50 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800"
                      />
                      <div class="ml-4 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-light/20 dark:bg-primary-dark/20 flex items-center justify-center">
                          <i class="ri-spam-line text-primary-light dark:text-primary-dark text-lg"></i>
                        </div>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">
                          {{ $t('report_reason_spam') }}
                        </span>
                      </div>
                    </label>

                    <label class="flex items-center p-4 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 cursor-pointer transition-all duration-200 hover:bg-white/70 dark:hover:bg-gray-800/70 hover:shadow-lg">
                      <input
                        type="radio"
                        name="report-reason"
                        value="abuse"
                        v-model="selectedReason"
                        class="w-5 h-5 text-red-500 bg-white/50 dark:bg-gray-700/50 border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-red-500/50 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800"
                      />
                      <div class="ml-4 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-light/20 dark:bg-primary-dark/20 flex items-center justify-center">
                          <i class="ri-user-forbid-line text-primary-light dark:text-primary-dark text-lg"></i>
                        </div>
                        <span class="text-gray-700 dark:text-gray-300 font-medium">
                          {{ $t('report_reason_abuse') }}
                        </span>
                      </div>
                    </label>
                  </div>
                </div>

                <!-- Enhanced Additional Information -->
                <div class="space-y-3">
                  <label class="block text-lg font-semibold text-gray-900 dark:text-white">
                    Additional Information
                  </label>
                  <textarea
                    v-model="additionalInfo"
                    rows="4"
                    :placeholder="$t('report_additional_info')"
                    class="w-full p-4 rounded-xl bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500/50 focus:border-transparent resize-none transition-all duration-200 focus:bg-white/80 dark:focus:bg-gray-800/80"
                  ></textarea>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    Please provide any additional details that will help us understand the issue better.
                  </p>
                </div>

                <!-- Enhanced Action Buttons -->
                <div class="flex justify-end gap-4 mt-8">
                  <button
                    @click="closeModal"
                    class="px-6 py-3 rounded-xl bg-gray-100/60 dark:bg-gray-700/60 hover:bg-gray-200/60 dark:hover:bg-gray-600/60 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 font-semibold focus:outline-none focus:ring-2 focus:ring-gray-500/50"
                  >
                    {{ $t('cancel') }}
                  </button>
                  <button
                    @click="() => { console.log('🟡 Button clicked!', {selectedReason, isSubmitting}); handleSubmit() }"
                    :disabled="!selectedReason || isSubmitting"
                    class="px-6 py-3 rounded-xl bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:shadow-lg"
                  >
                    <i v-if="isSubmitting" class="ri-loader-4-line animate-spin mr-2"></i>
                    <i v-else class="ri-send-plane-line mr-2"></i>
                    {{ isSubmitting ? 'Submitting...' : $t('confirm') }}
                  </button>
                </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch } from 'vue'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import { useToast } from 'vue-toastification'
import axiosInstance from '@/axios'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  contentId: {
    type: [Number, String],
    required: true
  },
  contentType: {
    type: String,
    required: true,
    validator: (value) => ['post', 'comment', 'media', 'user'].includes(value)
  }
})

const emit = defineEmits(['close'])
const toast = useToast()

const selectedReason = ref('')
const additionalInfo = ref('')
const isSubmitting = ref(false)

// Debug: Watch for modal open
watch(() => props.isOpen, (newVal) => {
  console.log('🔵 Modal isOpen changed:', newVal)
  if (newVal) {
    console.log('Modal opened with props:', {
      contentId: props.contentId,
      contentType: props.contentType
    })
  }
})

// Debug: Watch selectedReason
watch(selectedReason, (newVal) => {
  console.log('🟢 selectedReason changed:', newVal)
})

const closeModal = () => {
  selectedReason.value = ''
  additionalInfo.value = ''
  emit('close')
}

const handleSubmit = async () => {
  console.log('🔴 handleSubmit called!')
  
  if (isSubmitting.value) {
    console.log('Already submitting, returning')
    return
  }
  
  isSubmitting.value = true
  console.log('isSubmitting set to true')
  
  try {
    console.log('Submitting report:', {
      content_id: props.contentId,
      content_type: props.contentType,
      reason: selectedReason.value,
      additional_info: additionalInfo.value
    })
    
    const response = await axiosInstance.post('/reports', {
      content_id: props.contentId,
      content_type: props.contentType,
      reason: selectedReason.value,
      additional_info: additionalInfo.value
    })

    console.log('Report API response:', response.data)

    if (response.data.success) {
      toast.success('Report submitted successfully')
      closeModal()
    } else {
      console.log('🔴 Report submission failed:', response.data.message)
      toast.error(response.data.message || 'Failed to submit report')
    }
  } catch (error) {
    console.error('🔴 Error submitting report:', error)
    console.error('🔴 Error response:', error.response)
    console.error('🔴 Error response data:', error.response?.data)
    console.error('🔴 Error status:', error.response?.status)
    
    // Handle different error statuses
    if (error.response?.status === 422) {
      const errorMessage = error.response.data?.message || 'Failed to submit report'
      console.log('🔴 Showing 422 error toast:', errorMessage)
      toast.error(errorMessage)
      // Close modal after showing error
      setTimeout(() => {
        closeModal()
      }, 1500)
    } else if (error.response?.status === 500) {
      toast.error('Server error occurred. Please try again later.')
    } else {
      toast.error(error.response?.data?.message || 'An error occurred while submitting the report')
    }
  } finally {
    console.log('🔴 Setting isSubmitting to false')
    isSubmitting.value = false
  }
}
</script>

<style scoped>
/* Enhanced animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideInFromBottom {
  from {
    opacity: 0;
    transform: translateY(100%);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

/* Apply animations to elements */
.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out;
}

.animate-slideInFromBottom {
  animation: slideInFromBottom 0.5s ease-out;
}

.animate-scaleIn {
  animation: scaleIn 0.4s ease-out;
}

.animate-pulse {
  animation: pulse 2s infinite;
}

/* Enhanced focus states */
button:focus-visible,
input:focus-visible,
textarea:focus-visible {
  outline: 2px solid #ef4444;
  outline-offset: 2px;
  border-radius: 0.5rem;
}

/* Smooth transitions for all interactive elements */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Enhanced scrollbar styling */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.5);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.7);
}

/* Dark mode scrollbar */
.dark ::-webkit-scrollbar-thumb {
  background: rgba(75, 85, 99, 0.5);
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: rgba(75, 85, 99, 0.7);
}

/* Glassmorphism effects */
.glass {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .glass {
  background: rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Enhanced button hover effects */
.btn-hover {
  position: relative;
  overflow: hidden;
}

.btn-hover::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.btn-hover:hover::before {
  left: 100%;
}

/* Enhanced radio button styling */
input[type="radio"] {
  appearance: none;
  width: 1.25rem;
  height: 1.25rem;
  border: 2px solid #d1d5db;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  position: relative;
  cursor: pointer;
  transition: all 0.2s;
}

input[type="radio"]:checked {
  border-color: #ef4444;
  background: #ef4444;
}

input[type="radio"]:checked::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 0.5rem;
  height: 0.5rem;
  background: white;
  border-radius: 50%;
}

.dark input[type="radio"] {
  border-color: #4b5563;
  background: rgba(55, 65, 81, 0.5);
}

.dark input[type="radio"]:checked {
  border-color: #ef4444;
  background: #ef4444;
}

/* Enhanced label hover effects */
label:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Enhanced textarea styling */
textarea {
  font-family: inherit;
  line-height: 1.5;
}

textarea:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

/* Enhanced report reason icons */
.report-icon {
  transition: all 0.2s;
}

label:hover .report-icon {
  transform: scale(1.1);
}

/* Enhanced submit button */
.submit-button {
  position: relative;
  overflow: hidden;
}

.submit-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.submit-button:hover::before {
  left: 100%;
}

/* Enhanced disabled state */
.disabled-button {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.disabled-button:hover {
  transform: none !important;
  box-shadow: none !important;
}

/* Enhanced modal backdrop */
.modal-backdrop {
  backdrop-filter: blur(8px);
  background: rgba(0, 0, 0, 0.6);
}

/* Enhanced modal content */
.modal-content {
  backdrop-filter: blur(20px);
  background: rgba(255, 255, 255, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .modal-content {
  background: rgba(0, 0, 0, 0.9);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

/* Enhanced warning icon */
.warning-icon {
  background: var(--primary-light);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
  animation: pulse 2s infinite;
}

/* Enhanced report reason cards */
.report-reason-card {
  position: relative;
  overflow: hidden;
}

.report-reason-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.report-reason-card:hover::after {
  opacity: 1;
}

/* Enhanced cancel button */
.cancel-button {
  position: relative;
  overflow: hidden;
}

.cancel-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.cancel-button:hover::before {
  left: 100%;
}

/* Enhanced focus ring for accessibility */
.focus-ring:focus {
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.5);
}

/* Enhanced gradient text */
.gradient-text {
  background: var(--primary-light);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Enhanced shadow effects */
.shadow-soft {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.shadow-medium {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.shadow-strong {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}
</style>