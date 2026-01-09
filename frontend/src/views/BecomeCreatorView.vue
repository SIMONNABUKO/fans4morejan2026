<template>
  <div class="min-h-screen bg-gradient-to-b from-background-light to-gray-50 dark:from-background-dark dark:to-gray-900">
    <!-- Modern Hero Section -->
    <div class="relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-purple-600 via-blue-600 to-pink-600 opacity-90"></div>
      <div class="absolute inset-0 bg-[url('/model.jpg')] bg-cover bg-center mix-blend-overlay opacity-20"></div>
      
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32">
        <div class="text-center">
          <!-- Badge -->
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/10 backdrop-blur-md border border-white/20 mb-6">
            <i class="ri-star-fill text-yellow-300"></i>
            <span class="text-white font-medium text-sm">{{ t('creator_program') }}</span>
          </div>
          
          <!-- Title -->
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white mb-6 leading-tight">
            {{ t('become_a_model') }}
          </h1>
          
          <!-- Subtitle -->
          <p class="text-xl sm:text-2xl text-white/90 mb-8 max-w-3xl mx-auto">
            {{ t('apply_verified_model') }}
          </p>
          
          <!-- CTA Button -->
          <button 
            @click="handleApplicationClick"
            class="group inline-flex items-center gap-3 px-8 py-4 bg-white text-purple-600 rounded-full font-bold text-lg hover:bg-gray-50 transition-all duration-300 hover:scale-105 shadow-2xl hover:shadow-purple-500/50"
          >
            <span>{{ t('application') }}</span>
            <i class="ri-arrow-right-line group-hover:translate-x-1 transition-transform"></i>
          </button>
        </div>
      </div>
      
      <!-- Wave Decoration -->
      <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-12">
          <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="currentColor" class="text-gray-50 dark:text-gray-900"/>
        </svg>
      </div>
    </div>

    <!-- Benefits Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
      <div class="text-center mb-16">
        <h2 class="text-3xl sm:text-4xl font-bold text-text-light-primary dark:text-text-dark-primary mb-4">
          {{ t('what_sets_us_apart') }}
        </h2>
        <p class="text-lg text-text-light-secondary dark:text-text-dark-secondary max-w-2xl mx-auto">
          Everything you need to grow your creator business
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Internal Promotion -->
        <div class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100 dark:border-gray-700">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <i class="ri-rocket-line text-white text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary mb-3">
            {{ t('internal_promotion') }}
          </h3>
          <p class="text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
            {{ t('internal_promotion_description') }}
          </p>
        </div>

        <!-- Referral Bonuses -->
        <div class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100 dark:border-gray-700">
          <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <i class="ri-user-add-line text-white text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary mb-3">
            {{ t('referral_bonuses') }}
          </h3>
          <p class="text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
            {{ t('referral_bonuses_description') }}
          </p>
        </div>

        <!-- Emojis -->
        <div class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100 dark:border-gray-700">
          <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <i class="ri-emotion-happy-line text-white text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary mb-3">
            {{ t('emojis') }}
          </h3>
          <p class="text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
            {{ t('emojis_description') }}
          </p>
        </div>

        <!-- Followers and Subscribers -->
        <div class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100 dark:border-gray-700">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <i class="ri-group-2-line text-white text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary mb-3">
            {{ t('followers_and_subscribers') }}
          </h3>
          <p class="text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
            {{ t('followers_and_subscribers_description') }}
          </p>
        </div>

        <!-- 80-20 Split -->
        <div class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100 dark:border-gray-700">
          <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <i class="ri-money-dollar-circle-line text-white text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary mb-3">
            {{ t('80_20_split') }}
          </h3>
          <p class="text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
            {{ t('80_20_split_description') }}
          </p>
        </div>

        <!-- Advanced Analytics -->
        <div class="group bg-white dark:bg-gray-800 rounded-2xl p-8 shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 border border-gray-100 dark:border-gray-700">
          <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-rose-500 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300">
            <i class="ri-bar-chart-box-line text-white text-2xl"></i>
          </div>
          <h3 class="text-xl font-bold text-text-light-primary dark:text-text-dark-primary mb-3">
            Advanced Analytics
          </h3>
          <p class="text-text-light-secondary dark:text-text-dark-secondary leading-relaxed">
            Track your performance with detailed insights and analytics to optimize your content strategy.
          </p>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
      <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-3xl p-12 text-center shadow-2xl">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">
          Ready to Start Your Journey?
        </h2>
        <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
          Join thousands of creators earning on Fans4More. Start your application today.
        </p>
        <button 
          @click="handleApplicationClick"
          class="inline-flex items-center gap-3 px-8 py-4 bg-white text-purple-600 rounded-full font-bold text-lg hover:bg-gray-50 transition-all duration-300 hover:scale-105 shadow-xl"
        >
          <span>{{ t('application') }}</span>
          <i class="ri-arrow-right-line"></i>
        </button>
      </div>
    </div>

    <!-- FAQ Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
      <div class="text-center mb-12">
        <h2 class="text-3xl sm:text-4xl font-bold text-text-light-primary dark:text-text-dark-primary mb-4">
          {{ t('becoming_a_model_faq') }}
        </h2>
        <p class="text-lg text-text-light-secondary dark:text-text-dark-secondary">
          Everything you need to know about becoming a creator
        </p>
      </div>
      
      <div class="space-y-4">
        <div 
          v-for="(faq, index) in faqs" 
          :key="index" 
          class="group bg-white dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-purple-500 dark:hover:border-purple-500 transition-all duration-300"
        >
          <button 
            @click="toggleFaq(index)"
            class="w-full py-5 px-6 flex justify-between items-center text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
          >
            <span class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary pr-4">
              {{ t(faq.question) }}
            </span>
            <i :class="[
              expandedFaq === index ? 'ri-subtract-line' : 'ri-add-line',
              'text-2xl transition-transform text-purple-600 dark:text-purple-400 group-hover:scale-110'
            ]"></i>
          </button>
          <div 
            v-show="expandedFaq === index"
            class="px-6 pb-5 text-text-light-secondary dark:text-text-dark-secondary border-t border-gray-200 dark:border-gray-700 pt-4 animate-fadeIn"
          >
            <p class="leading-relaxed">{{ t(faq.answer) }}</p>
            <a 
              v-if="faq.link" 
              :href="faq.link.url"
              class="inline-flex items-center gap-2 text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 mt-3 font-medium transition-colors"
            >
              {{ t(faq.link.text) }}
              <i class="ri-external-link-line"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useSettingsStore } from '@/stores/settingsStore'
import { useToast } from 'vue-toastification'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const router = useRouter()
const expandedFaq = ref(null)
const settingsStore = useSettingsStore()
const toast = useToast()

const faqs = [
  {
    question: 'how_much_money',
    answer: 'how_much_money_answer'
  },
  {
    question: 'payment_methods',
    answer: 'payment_methods_answer'
  },
  {
    question: 'earnings_calculation',
    answer: 'earnings_calculation_answer'
  },
  {
    question: 'payout_frequency',
    answer: 'payout_frequency_answer',
    link: {
      text: 'register_for_payouts',
      url: '#'
    }
  },
  {
    question: 'affiliate_system',
    answer: 'affiliate_system_answer'
  }
]

const toggleFaq = (index) => {
  expandedFaq.value = expandedFaq.value === index ? null : index
}

const handleApplicationClick = () => {
  // Check if user's email is verified
  if (!settingsStore.account.is_email_verified) {
    toast.error(t('verify_email_before_applying'))
    settingsStore.triggerEmailVerificationModal()
    return
  }

  router.push('/creator-application')
}
</script>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}
</style>
