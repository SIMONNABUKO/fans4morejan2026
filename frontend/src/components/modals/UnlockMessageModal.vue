<template>
  <TransitionRoot appear :show="isOpen" as="template">
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
                  {{ $t('unlock_content') }}
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
                <!-- Subscribe Option -->
                <div v-if="!isSubscribed" class="p-4 rounded-xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm transition-all duration-300 hover:bg-white/70 dark:hover:bg-gray-800/70 hover:shadow-lg">
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-primary-light/20 dark:bg-primary-dark/20 flex items-center justify-center">
                        <i class="ri-user-star-fill text-primary-light dark:text-primary-dark text-sm"></i>
                      </div>
                      <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $t('subscribe') }}</span>
                    </div>
                    <span class="text-xs px-2 py-1 bg-primary-light dark:bg-primary-dark text-white rounded-full font-semibold">{{ $t('recommended') }}</span>
                  </div>
                  <p class="text-gray-600 dark:text-gray-300 mb-3 text-sm">
                    {{ $t('subscribe_any_tier') }}
                  </p>
                  <button 
                    @click="handleSubscribe"
                    class="w-full py-2 px-4 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-lg font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
                  >
                    <i class="ri-vip-crown-line mr-2"></i>
                    {{ $t('subscribe_to') }} {{ message.creator.name }}
                  </button>
                </div>
                <div v-else class="p-4 rounded-xl border border-success-light/30 dark:border-success-dark/30 bg-success-muted/30 dark:bg-success-dark/10 backdrop-blur-sm">
                  <div class="flex items-center gap-3 text-success-light dark:text-success-dark">
                    <div class="w-8 h-8 rounded-full bg-success-light/20 dark:bg-success-dark/20 flex items-center justify-center">
                      <i class="ri-checkbox-circle-fill text-success-light dark:text-success-dark text-sm"></i>
                    </div>
                    <span class="font-semibold text-sm">{{ $t('already_subscribed') }}</span>
                  </div>
                </div>

                <!-- Follow Option -->
                <div v-if="!isFollowing" class="p-4 rounded-xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm transition-all duration-300 hover:bg-white/70 dark:hover:bg-gray-800/70 hover:shadow-lg">
                  <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-primary-light/20 dark:bg-primary-dark/20 flex items-center justify-center">
                      <i class="ri-user-follow-fill text-primary-light dark:text-primary-dark text-sm"></i>
                    </div>
                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $t('follow') }}</span>
                  </div>
                  <p class="text-gray-600 dark:text-gray-300 mb-3 text-sm">
                    {{ $t('follow_creator') }}
                  </p>
                  <button 
                    @click="handleFollow"
                    class="w-full py-2 px-4 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-lg font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
                  >
                    <i class="ri-user-add-line mr-2"></i>
                    {{ $t('follow') }} {{ message.creator.name }}
                  </button>
                </div>
                <div v-else class="p-4 rounded-xl border border-success-light/30 dark:border-success-dark/30 bg-success-muted/30 dark:bg-success-dark/10 backdrop-blur-sm">
                  <div class="flex items-center gap-3 text-success-light dark:text-success-dark">
                    <div class="w-8 h-8 rounded-full bg-success-light/20 dark:bg-success-dark/20 flex items-center justify-center">
                      <i class="ri-checkbox-circle-fill text-success-light dark:text-success-dark text-sm"></i>
                    </div>
                    <span class="font-semibold text-sm">{{ $t('already_following') }}</span>
                  </div>
                </div>

                <!-- Pay Option -->
                <div v-if="!hasPaid" class="p-4 rounded-xl border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm transition-all duration-300 hover:bg-white/70 dark:hover:bg-gray-800/70 hover:shadow-lg">
                  <div class="flex items-center gap-3 mb-3">
                    <div class="w-8 h-8 rounded-full bg-primary-light/20 dark:bg-primary-dark/20 flex items-center justify-center">
                      <i class="ri-money-dollar-circle-fill text-primary-light dark:text-primary-dark text-sm"></i>
                    </div>
                    <span class="text-base font-semibold text-gray-900 dark:text-white">{{ $t('pay') }}</span>
                  </div>
                  <p class="text-gray-600 dark:text-gray-300 mb-3 text-sm">
                    {{ $t('pay_dollar') }}{{ message.price }}
                  </p>
                  <button 
                    @click="handlePay"
                    class="w-full py-2 px-4 bg-primary-light dark:bg-primary-dark hover:bg-primary-light/90 dark:hover:bg-primary-dark/90 text-white rounded-lg font-semibold transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark"
                  >
                    <i class="ri-money-dollar-circle-line mr-2"></i>
                    {{ $t('pay') }} ${{ message.price }}
                  </button>
                </div>
                <div v-else class="p-4 rounded-xl border border-success-light/30 dark:border-success-dark/30 bg-success-muted/30 dark:bg-success-dark/10 backdrop-blur-sm">
                  <div class="flex items-center gap-3 text-success-light dark:text-success-dark">
                    <div class="w-8 h-8 rounded-full bg-success-light/20 dark:bg-success-dark/20 flex items-center justify-center">
                      <i class="ri-checkbox-circle-fill text-success-light dark:text-success-dark text-sm"></i>
                    </div>
                    <span class="font-semibold text-sm">{{ $t('already_paid') }}</span>
                  </div>
                </div>

                <!-- All Tiers Subscribed Message -->
                <div v-if="isSubscribedToAllTiers" class="text-center py-4">
                  <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-success-light/20 dark:bg-success-dark/20 flex items-center justify-center">
                    <i class="ri-checkbox-circle-fill text-success-light dark:text-success-dark text-xl"></i>
                  </div>
                  <p class="text-gray-600 dark:text-gray-300 text-base font-medium">
                    {{ $t('already_subscribed_all_tiers') }}
                  </p>
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
import { defineProps, defineEmits } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
  message: {
    type: Object,
    required: true
  },
  isSubscribed: {
    type: Boolean,
    default: false
  },
  isFollowing: {
    type: Boolean,
    default: false
  },
  hasPaid: {
    type: Boolean,
    default: false
  },
  isSubscribedToAllTiers: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'subscribe', 'follow', 'pay']);

const handleSubscribe = () => {
  emit('subscribe', props.message);
};

const handleFollow = () => {
  emit('follow', props.message);
};

const handlePay = () => {
  emit('pay', props.message);
};
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
button:focus-visible {
  outline: 2px solid var(--primary-light);
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

/* Enhanced option cards */
.option-card {
  position: relative;
  overflow: hidden;
}

.option-card::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s;
}

.option-card:hover::after {
  opacity: 1;
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

/* Enhanced unlock icon */
.unlock-icon {
  background: var(--primary-light);
  box-shadow: 0 8px 15px -3px rgba(0, 0, 0, 0.2);
  animation: pulse 2s infinite;
}

/* Enhanced success states */
.success-state {
  background: var(--success-muted);
  border-color: var(--success-light);
  color: var(--success-light);
}

.dark .success-state {
  background: var(--success-dark);
  border-color: var(--success-dark);
  color: var(--success-dark);
}

/* Enhanced focus ring for accessibility */
.focus-ring:focus {
  box-shadow: 0 0 0 3px rgba(65, 105, 225, 0.5);
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