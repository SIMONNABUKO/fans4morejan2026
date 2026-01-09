<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-50">
      <!-- Enhanced Backdrop -->
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
        <div class="flex min-h-full items-center justify-center p-2">
          <TransitionChild
            as="template"
            enter="duration-300 ease-out"
            enter-from="opacity-0 scale-95 translate-y-4"
            enter-to="opacity-100 scale-100 translate-y-0"
            leave="duration-200 ease-in"
            leave-from="opacity-100 scale-100 translate-y-0"
            leave-to="opacity-0 scale-95 translate-y-4"
          >
            <DialogPanel class="w-full h-full max-w-none transform overflow-hidden bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border border-white/20 dark:border-gray-700/50 p-6 text-left align-middle shadow-2xl transition-all animate-scaleIn">
              <!-- Enhanced Header -->
              <DialogTitle as="div" class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-primary-light dark:bg-primary-dark flex items-center justify-center shadow-lg">
                    <i class="ri-heart-fill text-white text-xl"></i>
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                      Send a Tip
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Support your favorite creator</p>
                  </div>
                </div>
                <button 
                  @click="closeModal"
                  class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                >
                  <i class="ri-close-line text-xl"></i>
                </button>
              </DialogTitle>

              <div class="space-y-6">
                <!-- Enhanced Recipient Info -->
                <div class="flex items-center gap-3 p-4 rounded-xl bg-gray-100/60 dark:bg-gray-700/60 border border-white/20 dark:border-gray-600/50">
                  <div class="relative">
                    <div class="w-12 h-12 rounded-full overflow-hidden ring-4 ring-white/20 dark:ring-gray-700/30 shadow-lg">
                      <img 
                        :src="recipient.avatar" 
                        :alt="recipient.username"
                        class="w-full h-full object-cover transition-transform duration-200 hover:scale-110"
                      />
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full border-3 border-white dark:border-gray-900 shadow-lg"></div>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center gap-2">
                      <span class="font-bold text-lg text-gray-900 dark:text-white">{{ recipient.username }}</span>
                      <i class="ri-checkbox-circle-fill text-blue-500 text-lg"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">How much would you like to tip?</p>
                  </div>
                </div>

                <!-- Enhanced Amount Input -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Amount
                  </label>
                  <div class="relative">
                    <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400 font-bold text-xl z-10" style="backdrop-filter: none; -webkit-backdrop-filter: none;">$</span>
                    <input
                      type="number"
                      v-model="amount"
                      class="w-full pl-8 pr-4 py-4 rounded-xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white/80 dark:focus:bg-gray-800/80 transition-all duration-200 text-lg font-semibold"
                      placeholder="0.00"
                    />
                  </div>
                </div>

                <!-- Enhanced Message Input -->
                <div>
                  <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                    Message (optional)
                  </label>
                  <textarea
                    v-model="message"
                    rows="3"
                    class="w-full p-4 rounded-xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:bg-white/80 dark:focus:bg-gray-800/80 transition-all duration-200 resize-none"
                    placeholder="Type a message to accompany your tip..."
                  ></textarea>
                </div>

                <!-- Enhanced Purchase Balance Section -->
                <Disclosure v-slot="{ open }">
                  <DisclosureButton class="flex w-full justify-between rounded-xl bg-gray-100/60 dark:bg-gray-700/60 hover:bg-gray-200/60 dark:hover:bg-gray-600/60 px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white transition-all duration-200 border border-white/20 dark:border-gray-600/50">
                    <span class="flex items-center gap-2">
                      <i class="ri-wallet-3-line text-lg"></i>
                      Purchase More Balance
                    </span>
                    <i :class="open ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line'" class="text-xl transition-transform duration-200"></i>
                  </DisclosureButton>
                  <DisclosurePanel class="px-4 pt-4 pb-2 space-y-2">
                    <button 
                      v-for="amount in walletAmounts" 
                      :key="amount"
                      class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-gray-200/60 dark:hover:bg-gray-600/60 text-gray-900 dark:text-white transition-all duration-200 hover:scale-[1.02] border border-white/20 dark:border-gray-600/50"
                      @click="selectWalletAmount(amount)"
                    >
                      <span class="font-medium">${{ amount }} Wallet Balance</span>
                      <span class="font-bold text-blue-600 dark:text-blue-400">${{ amount }}</span>
                    </button>
                  </DisclosurePanel>
                </Disclosure>

                <!-- Enhanced Submit Button -->
                <div class="pt-4">
                  <button
                    type="button"
                    class="w-full rounded-xl bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 px-6 py-4 text-lg font-bold text-white transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    @click="handleTip"
                    :disabled="isProcessing"
                  >
                    <span v-if="!isProcessing" class="flex items-center justify-center gap-2">
                      <i class="ri-heart-fill text-xl"></i>
                      Send Tip
                    </span>
                    <span v-else class="flex items-center justify-center gap-2">
                      <div class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                      Processing...
                    </span>
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
import { ref } from 'vue'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
} from '@headlessui/vue'
import { useToast } from 'vue-toastification'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  recipient: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'tip'])

const toast = useToast()
const amount = ref('')
const message = ref('')
const walletAmounts = [10, 25, 50, 100, 500]
const isProcessing = ref(false)

const closeModal = () => {
  emit('close')
}

const selectWalletAmount = (value) => {
  amount.value = value
}

const handleTip = async () => {
  if (!amount.value || amount.value <= 0) {
    toast.error('Please enter a valid amount')
    return
  }

  isProcessing.value = true

  try {
    await emit('tip', {
      amount: Number(amount.value),
      message: message.value
    })
    // The modal will be closed by the parent component if the tip is successful
  } catch (error) {
    toast.error('Failed to process tip')
  } finally {
    isProcessing.value = false
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
  outline: 2px solid #3b82f6;
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

/* Enhanced loading spinner */
.spinner {
  border: 2px solid rgba(156, 163, 175, 0.3);
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Enhanced tooltip */
.tooltip {
  position: relative;
}

.tooltip::before {
  content: attr(data-tooltip);
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.9);
  color: white;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  white-space: nowrap;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
  z-index: 10;
}

.tooltip:hover::before {
  opacity: 1;
}

/* Enhanced focus ring for accessibility */
.focus-ring:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

/* Enhanced gradient text */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

/* Enhanced modal card hover effects */
.modal-card {
  position: relative;
  overflow: hidden;
}

.modal-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.modal-card:hover::after {
  opacity: 1;
}

/* Enhanced verified badge */
.verified-badge {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  font-weight: 600;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Enhanced action buttons */
.action-button {
  position: relative;
  overflow: hidden;
}

.action-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.action-button:hover::before {
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

/* Enhanced input styling */
.modern-input {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.modern-input:focus {
  background: rgba(255, 255, 255, 0.2);
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.dark .modern-input {
  background: rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.dark .modern-input:focus {
  background: rgba(0, 0, 0, 0.2);
  border-color: #3b82f6;
}

/* Enhanced textarea styling */
.modern-textarea {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  transition: all 0.3s ease;
}

.modern-textarea:focus {
  background: rgba(255, 255, 255, 0.2);
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.dark .modern-textarea {
  background: rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.dark .modern-textarea:focus {
  background: rgba(0, 0, 0, 0.2);
  border-color: #3b82f6;
}

/* Enhanced close button */
.close-button {
  position: relative;
  overflow: hidden;
}

.close-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.close-button:hover::before {
  left: 100%;
}

/* Enhanced tip button */
.tip-button {
  position: relative;
  overflow: hidden;
}

.tip-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.tip-button:hover::before {
  left: 100%;
}

/* Enhanced recipient card */
.recipient-card {
  position: relative;
  overflow: hidden;
}

.recipient-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.recipient-card:hover::after {
  opacity: 1;
}

/* Enhanced wallet amount buttons */
.wallet-amount-button {
  position: relative;
  overflow: hidden;
}

.wallet-amount-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.wallet-amount-button:hover::before {
  left: 100%;
}

/* Enhanced disclosure button */
.disclosure-button {
  position: relative;
  overflow: hidden;
}

.disclosure-button::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.disclosure-button:hover::before {
  left: 100%;
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

/* Enhanced heart icon animation */
.heart-icon {
  animation: pulse 2s infinite;
}

/* Enhanced amount input */
.amount-input {
  position: relative;
}

.amount-input::before {
  content: '$';
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  font-weight: bold;
  pointer-events: none;
}

/* Enhanced tip header icon */
.tip-header-icon {
  background: linear-gradient(135deg, #10b981, #059669);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
  animation: pulse 2s infinite;
}
</style>

