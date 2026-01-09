<template>
<div class="min-h-screen bg-background-light dark:bg-background-dark">
  <!-- Modern Header -->
  <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20 py-6">
        <!-- Left Side: Navigation and Title -->
        <div class="flex items-center gap-4">
          <router-link 
            to="/settings" 
            class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
          >
            <i class="ri-arrow-left-line text-lg"></i>
          </router-link>
          
          <div class="flex flex-col">
            <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
              {{ t('display') }}
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Customize your display preferences
            </p>
          </div>
        </div>
        
        <!-- Right Side: Current Language -->
        <div class="hidden md:flex items-center gap-4">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Current Language</div>
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">
              {{ getCurrentLanguageName() }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Language Selection Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-translate-2 text-purple-600 dark:text-purple-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('fansformore_language') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              {{ t('fansformore_available') }}
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <!-- Current Language Display -->
        <div class="mb-6">
          <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl p-4 border border-blue-200 dark:border-blue-700">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-white dark:bg-gray-800 rounded-lg flex items-center justify-center shadow-sm">
                <span class="text-2xl">{{ getCurrentLanguageFlag() }}</span>
              </div>
              <div>
                <p class="font-semibold text-gray-900 dark:text-white">{{ getCurrentLanguageName() }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">Currently selected</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Language Selection -->
        <div class="space-y-3">
          <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Select Language</label>
          <div class="grid grid-cols-1 gap-3">
            <div 
              v-for="lang in languages" 
              :key="lang.code"
              @click="selectLanguage(lang.code)"
              class="relative cursor-pointer group"
            >
              <div 
                class="flex items-center gap-4 p-4 rounded-xl border-2 transition-all duration-200 hover:shadow-md"
                :class="selectedLanguage === lang.code 
                  ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' 
                  : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500 bg-white dark:bg-gray-700'"
              >
                <!-- Flag -->
                <div class="w-12 h-12 bg-white dark:bg-gray-800 rounded-lg flex items-center justify-center shadow-sm">
                  <span class="text-2xl">{{ lang.flag }}</span>
                </div>
                
                <!-- Language Info -->
                <div class="flex-1">
                  <p class="font-semibold text-gray-900 dark:text-white">{{ t(lang.labelKey) }}</p>
                  <p class="text-sm text-gray-600 dark:text-gray-400">{{ lang.nativeName }}</p>
                </div>
                
                <!-- Selection Indicator -->
                <div class="flex-shrink-0">
                  <div 
                    class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all duration-200"
                    :class="selectedLanguage === lang.code 
                      ? 'border-blue-500 bg-blue-500' 
                      : 'border-gray-300 dark:border-gray-500'"
                  >
                    <i 
                      v-if="selectedLanguage === lang.code"
                      class="ri-check-line text-white text-sm"
                    ></i>
                  </div>
                </div>
              </div>
              
              <!-- Hover Effect -->
              <div 
                class="absolute inset-0 rounded-xl bg-gradient-to-r from-blue-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none"
              ></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Language Info Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Language Information</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="text-center">
          <div class="w-16 h-16 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-translate-2 text-purple-600 dark:text-purple-400 text-2xl"></i>
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ languages.length }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">Available Languages</p>
        </div>
        
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-global-line text-blue-600 dark:text-blue-400 text-2xl"></i>
          </div>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ getCurrentLanguageName() }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">Current Language</p>
        </div>
      </div>
    </div>

    <!-- Language Features -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
      <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Language Features</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
          <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
            <i class="ri-check-line text-green-600 dark:text-green-400 text-sm"></i>
          </div>
          <span class="text-sm font-medium text-gray-900 dark:text-white">Full Translation</span>
        </div>
        
        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
          <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
            <i class="ri-check-line text-green-600 dark:text-green-400 text-sm"></i>
          </div>
          <span class="text-sm font-medium text-gray-900 dark:text-white">Localized Content</span>
        </div>
        
        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
          <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
            <i class="ri-check-line text-green-600 dark:text-green-400 text-sm"></i>
          </div>
          <span class="text-sm font-medium text-gray-900 dark:text-white">RTL Support</span>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()
const router = useRouter()
const route = useRoute()

const languages = [
  { code: 'en', labelKey: 'english', flag: '🇬🇧', nativeName: 'English' },
  { code: 'fr', labelKey: 'french', flag: '🇫🇷', nativeName: 'Français' },
  { code: 'es', labelKey: 'spanish', flag: '🇪🇸', nativeName: 'Español' },
  { code: 'de', labelKey: 'german', flag: '🇩🇪', nativeName: 'Deutsch' },
  { code: 'pt', labelKey: 'portuguese', flag: '🇵🇹', nativeName: 'Português' },
  { code: 'tr', labelKey: 'turkish', flag: '🇹🇷', nativeName: 'Türkçe' },
  { code: 'ko', labelKey: 'korean', flag: '🇰🇷', nativeName: '한국어' },
]

const selectedLanguage = ref(locale.value)

const getCurrentLanguageName = () => {
  const currentLang = languages.find(lang => lang.code === selectedLanguage.value)
  return currentLang ? t(currentLang.labelKey) : 'English'
}

const getCurrentLanguageFlag = () => {
  const currentLang = languages.find(lang => lang.code === selectedLanguage.value)
  return currentLang ? currentLang.flag : '🇬🇧'
}

function selectLanguage(code) {
  selectedLanguage.value = code
  locale.value = code
  localStorage.setItem('locale', code)
  
  // If not already on /language, navigate to it
  if (route.name !== 'language') {
    router.push({ name: 'language' })
  }
  
  // TODO: Integrate with i18n or backend
}
</script>

<style scoped>
/* Add your styles here */
</style>
