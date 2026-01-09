<template>
<div class="min-h-screen bg-background-light dark:bg-background-dark">
  <!-- Modern Header -->
  <header class="sticky top-0 z-20 bg-background-light/95 dark:bg-background-dark/95 backdrop-blur-lg border-b border-border-light dark:border-border-dark shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20 py-6">
        <!-- Left Side: Navigation and Title -->
        <div class="flex items-center gap-4">
          <router-link 
            to="/dashboard" 
            class="p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-white/70 dark:hover:bg-gray-800/70 transition-all duration-200 hover:scale-105 shadow-sm hover:shadow-md"
          >
            <i class="ri-arrow-left-line text-lg"></i>
          </router-link>
          
          <div class="flex flex-col">
            <h1 class="text-2xl font-bold text-text-light-primary dark:text-text-dark-primary leading-tight">
              {{ t('uploads') }}
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Configure your upload settings and watermark preferences
            </p>
          </div>
        </div>
        
        <!-- Right Side: Quick Stats -->
        <div class="hidden md:flex items-center gap-6">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Quality</div>
            <div class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ defaultQuality }}</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="space-y-8">
      <!-- Change Watermark Section -->
      <section class="space-y-6">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
            <i class="ri-image-line text-purple-600 dark:text-purple-400 text-lg"></i>
          </div>
          <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">{{ t('change_watermark') }}</h2>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="space-y-4">
            <div>
              <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary mb-4">
                {{ t('watermark_description') }}
              </p>
            </div>
            
            <div class="space-y-3">
              <label class="text-sm font-medium text-text-light-primary dark:text-text-dark-primary">
                {{ t('media_watermark') }}
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                  <i class="ri-link text-text-light-tertiary dark:text-text-dark-tertiary text-lg"></i>
                </div>
                <input
                  v-model="watermark"
                  type="text"
                  placeholder="fans4more.com/BethyBarbie"
                  class="w-full pl-12 pr-24 py-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-text-light-primary dark:text-text-dark-primary placeholder:text-text-light-tertiary dark:placeholder:text-text-dark-tertiary focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent transition-all duration-200"
                />
                <div class="absolute right-2 top-1/2 -translate-y-1/2 flex items-center gap-2">
                  <button 
                    v-if="watermark"
                    @click="watermark = ''"
                    class="p-2 text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200"
                  >
                    <i class="ri-close-line text-lg"></i>
                  </button>
                  <button
                    @click="saveWatermark"
                    :disabled="loading"
                    class="px-4 py-2 bg-purple-600 dark:bg-purple-500 hover:bg-purple-600/90 dark:hover:bg-purple-500/90 text-white text-sm font-semibold rounded-lg transition-all duration-200 hover:scale-105 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                  >
                    <span v-if="loading" class="flex items-center gap-2">
                      <div class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></div>
                      {{ t('saving') }}
                    </span>
                    <span v-else class="flex items-center gap-2">
                      <i class="ri-save-line"></i>
                      {{ t('save') }}
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Upload Settings -->
      <section class="space-y-6">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
            <i class="ri-settings-3-line text-blue-600 dark:text-blue-400 text-lg"></i>
          </div>
          <h2 class="text-xl font-semibold text-text-light-primary dark:text-text-dark-primary">{{ t('upload_settings') }}</h2>
        </div>
        
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
          <div class="space-y-6">
            <!-- Auto-Watermark Toggle -->
            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-image-add-line text-green-600 dark:text-green-400 text-lg"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('auto_watermark_uploads') }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ t('auto_watermark_description') }}
                  </p>
                </div>
              </div>
              <ToggleSwitch v-model="autoWatermark" />
            </div>

            <!-- Preserve Original Files -->
            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-folder-line text-blue-600 dark:text-blue-400 text-lg"></i>
                </div>
                <div>
                  <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('preserve_original_files') }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ t('preserve_originals_description') }}
                  </p>
                </div>
              </div>
              <ToggleSwitch v-model="preserveOriginals" />
            </div>

            <!-- Default Upload Quality -->
            <div class="space-y-4 p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-hd-line text-purple-600 dark:text-purple-400 text-lg"></i>
                </div>
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900 dark:text-white">{{ t('default_upload_quality') }}</h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ t('default_quality_description') }}
                  </p>
                </div>
              </div>
              <div class="pl-14">
                <select
                  v-model="defaultQuality"
                  class="w-full px-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-text-light-primary dark:text-text-dark-primary focus:outline-none focus:ring-2 focus:ring-purple-500 dark:focus:ring-purple-400 focus:border-transparent transition-all duration-200"
                >
                  <option value="high">{{ t('high_quality') }}</option>
                  <option value="medium">{{ t('medium_quality') }}</option>
                  <option value="low">{{ t('low_quality') }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ToggleSwitch from '@/components/common/ToggleSwitch.vue'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/authStore'
import { useToast } from 'vue-toastification'
import axiosInstance from '@/axios'

const { t } = useI18n()
const authStore = useAuthStore()
const toast = useToast()

// Form state
const watermark = ref('')
const autoWatermark = ref(true)
const preserveOriginals = ref(false)
const defaultQuality = ref('high')
const loading = ref(false)

// Load user's current watermark on mount
onMounted(async () => {
  try {
    const user = authStore.user
    if (user && user.media_watermark) {
      watermark.value = user.media_watermark
    }
  } catch (error) {
    console.error('Failed to load watermark:', error)
  }
})

const saveWatermark = async () => {
  if (loading.value) return
  
  loading.value = true
  
  try {
    // Update user profile with new watermark
    const response = await axiosInstance.post('/user/profile', {
      media_watermark: watermark.value
    })
    
    // Update local user state
    if (authStore.user) {
      authStore.user.media_watermark = watermark.value
    }
    
    toast.success(t('watermark_saved_successfully'))
    console.log('Watermark saved:', watermark.value)
  } catch (error) {
    console.error('Failed to save watermark:', error)
    toast.error(t('failed_to_save_watermark'))
  } finally {
    loading.value = false
  }
}
</script>

