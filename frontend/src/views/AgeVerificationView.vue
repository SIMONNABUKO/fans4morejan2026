<template>
  <div class="min-h-screen w-full flex items-center justify-center p-4 relative overflow-hidden bg-gradient-to-br from-background-light via-surface-light to-background-light dark:from-background-dark dark:via-surface-dark dark:to-background-dark">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute top-20 left-10 w-72 h-72 bg-primary-light/10 dark:bg-primary-dark/10 rounded-full blur-3xl animate-pulse-slow"></div>
      <div class="absolute bottom-20 right-10 w-96 h-96 bg-success-light/10 dark:bg-success-dark/10 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 1s"></div>
    </div>

    <!-- Main Card -->
    <div class="relative z-10 w-full max-w-2xl animate-fade-in-up">
      <!-- Glassmorphism Card -->
      <div class="backdrop-blur-xl bg-white/80 dark:bg-gray-900/80 rounded-3xl shadow-2xl border border-white/20 dark:border-gray-700/50 overflow-hidden">
        <!-- Header Section -->
        <div class="relative p-8 sm:p-12 bg-gradient-to-br from-primary-light/5 to-transparent dark:from-primary-dark/5">
          <h1 class="text-3xl sm:text-4xl font-bold text-center bg-gradient-to-r from-text-light-primary to-text-light-secondary dark:from-text-dark-primary dark:to-text-dark-secondary bg-clip-text text-transparent mb-3">
            Age Verification Required
          </h1>
          
          <p class="text-center text-text-light-secondary dark:text-text-dark-secondary text-sm sm:text-base">
            Please confirm you are of legal age to continue
          </p>
        </div>

        <!-- Content Section -->
        <div class="px-8 sm:px-12 py-8 space-y-6">
          <!-- Info Cards -->
          <div class="space-y-4">
            <div class="flex items-start gap-4 p-4 rounded-2xl bg-surface-light/50 dark:bg-surface-dark/50 border border-border-light dark:border-border-dark">
              <div class="flex-shrink-0 w-8 h-8 rounded-full bg-error-light/10 dark:bg-error-dark/10 flex items-center justify-center">
                <i class="ri-error-warning-line text-error-light dark:text-error-dark"></i>
              </div>
              <div class="flex-1">
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
                  This website contains age-restricted content. If you are <strong class="text-text-light-primary dark:text-text-dark-primary">under 18 years</strong> or under the age of majority in your location, you do not have authorization to enter.
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4 p-4 rounded-2xl bg-surface-light/50 dark:bg-surface-dark/50 border border-border-light dark:border-border-dark">
              <div class="flex-shrink-0 w-8 h-8 rounded-full bg-info-light/10 dark:bg-info-dark/10 flex items-center justify-center">
                <i class="ri-information-line text-info-light dark:text-info-dark"></i>
              </div>
              <div class="flex-1">
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
                  By entering, you agree to comply with our <span class="text-primary-light dark:text-primary-dark font-medium">Terms of Service</span> and confirm you meet the legal age requirements.
                </p>
              </div>
            </div>

            <div class="flex items-start gap-4 p-4 rounded-2xl bg-surface-light/50 dark:bg-surface-dark/50 border border-border-light dark:border-border-dark">
              <div class="flex-shrink-0 w-8 h-8 rounded-full bg-success-light/10 dark:bg-success-dark/10 flex items-center justify-center">
                <i class="ri-checkbox-circle-line text-success-light dark:text-success-dark"></i>
              </div>
              <div class="flex-1">
                <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
                  You certify under penalty of perjury that you are <strong class="text-text-light-primary dark:text-text-dark-primary">18 years or older</strong> or have reached the age of majority in your location.
                </p>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 pt-6">
            <button
              @click="handleReject"
              class="flex-1 group relative px-8 py-4 rounded-2xl font-semibold text-text-light-primary dark:text-text-dark-primary transition-all duration-300 overflow-hidden hover:scale-105 active:scale-95"
            >
              <div class="absolute inset-0 bg-surface-light dark:bg-surface-dark transition-opacity duration-300"></div>
              <div class="absolute inset-0 bg-gradient-to-r from-text-light-secondary/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              <div class="relative flex items-center justify-center gap-2">
                <i class="ri-logout-box-line text-lg"></i>
                <span>Leave Site</span>
              </div>
            </button>
            
            <button
              @click="handleAccept"
              class="flex-1 group relative px-8 py-4 rounded-2xl font-semibold text-white transition-all duration-300 overflow-hidden hover:scale-105 active:scale-95 shadow-lg hover:shadow-xl"
            >
              <div class="absolute inset-0 bg-gradient-to-r from-success-light to-success-dark dark:from-success-dark dark:to-success-light"></div>
              <div class="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              <div class="relative flex items-center justify-center gap-2">
                <i class="ri-check-line text-lg"></i>
                <span>I'm 18+, Enter</span>
              </div>
            </button>
          </div>

          <!-- Legal Notice -->
          <div class="pt-4 text-center">
            <p class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
              By proceeding, you acknowledge that you have read and understood our age requirements
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAgeVerificationStore } from '@/stores/ageVerificationStore';

const router = useRouter();
const ageVerificationStore = useAgeVerificationStore();

const handleAccept = () => {
  ageVerificationStore.setVerified(true);
  
  // Check if user is already authenticated
  const isAuthenticated = !!localStorage.getItem('auth_token');
  
  // Redirect to auth page if not authenticated, otherwise go to home
  if (isAuthenticated) {
    router.push('/');
  } else {
    router.push('/auth');
  }
};

const handleReject = () => {
  window.location.href = 'https://google.com';
};
</script>

