<template>
  <TransitionRoot appear :show="show" as="template">
    <Dialog as="div" class="relative z-50" @close="closeModal">
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
                <div>
                  <DialogTitle as="h3" class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ t('edit_profile') }}
                  </DialogTitle>
                  <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ t('customize_your_profile_appearance') }}
                  </p>
                </div>
                <button 
                  @click="closeModal" 
                  class="p-2 rounded-full bg-gray-100/80 dark:bg-gray-700/80 hover:bg-gray-200/80 dark:hover:bg-gray-600/80 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-all duration-200"
                >
                  <i class="ri-close-line text-xl"></i>
                </button>
              </div>

              <!-- Cover Photo Section -->
              <div class="relative w-full h-32 md:h-40 overflow-hidden bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-800">
                <img
                  v-if="coverPhotoPreview || authStore.user?.cover_photo"
                  :src="coverPhotoPreview || authStore.user?.cover_photo"
                  class="w-full h-full object-cover"
                  alt="Cover photo"
                />
                <div v-else class="w-full h-full flex items-center justify-center">
                  <i class="ri-image-line text-4xl text-gray-400"></i>
                </div>
                
                <!-- Cover Photo Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                
                <!-- Cover Photo Upload Button -->
                <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity duration-200">
                  <label class="cursor-pointer p-3 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors">
                    <i class="ri-camera-line text-lg"></i>
                    <input type="file" class="hidden" @change="handleCoverPhotoChange" accept="image/*" />
                  </label>
                </div>
              </div>

              <!-- Profile Picture Section -->
              <div class="flex justify-center -mt-16 mb-6">
                <div class="relative">
                  <div class="w-32 h-32 rounded-full border-4 border-background-light dark:border-background-dark overflow-hidden bg-surface-light dark:bg-surface-dark">
                    <img
                      v-if="avatarPreview || authStore.user?.avatar"
                      :src="avatarPreview || authStore.user?.avatar"
                      class="w-full h-full object-cover"
                      alt="Profile picture"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <i class="ri-user-3-line text-4xl text-text-light-secondary dark:text-text-dark-secondary"></i>
                    </div>
                  </div>
                  
                  <!-- Avatar Upload Button -->
                  <label class="absolute bottom-0 right-0 cursor-pointer p-2 rounded-full bg-primary-light dark:bg-primary-dark text-white hover:bg-primary-dark dark:hover:bg-primary-light transition-colors shadow-lg">
                    <i class="ri-camera-line text-sm"></i>
                    <input type="file" class="hidden" @change="handleAvatarChange" accept="image/*" />
                  </label>
                </div>
              </div>

              <!-- Enhanced Form Content -->
              <form @submit.prevent="handleSubmit" class="p-4 space-y-4">
                <div class="space-y-6">
                  <!-- Basic Information Section -->
                  <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary flex items-center gap-2">
                      <i class="ri-user-line text-primary-light dark:text-primary-dark"></i>
                      {{ t('basic_information') }}
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <!-- Name -->
                      <div>
                        <label class="block text-sm font-medium text-text-light-primary dark:text-text-dark-primary mb-2">
                          {{ t('name') }}
                        </label>
                        <input
                          type="text"
                          v-model="form.name"
                          class="w-full px-3 py-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 rounded-lg focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200"
                          :placeholder="t('your_full_name')"
                        />
                      </div>

                      <!-- Username -->
                      <div>
                        <label class="block text-sm font-medium text-text-light-primary dark:text-text-dark-primary mb-2">
                          {{ t('username') }}
                        </label>
                        <input
                          type="text"
                          v-model="form.username"
                          class="w-full px-3 py-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 rounded-lg focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200"
                          :placeholder="t('your_username')"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Bio Section -->
                  <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary flex items-center gap-2">
                      <i class="ri-file-text-line text-primary-light dark:text-primary-dark"></i>
                      {{ t('bio') }}
                    </h4>
                    
                    <div>
                      <label class="block text-sm font-medium text-text-light-primary dark:text-text-dark-primary mb-2">
                        {{ t('about_you') }}
                      </label>
                      <textarea
                        v-model="form.bio"
                        rows="4"
                        class="w-full px-3 py-2 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-white/20 dark:border-gray-700/50 rounded-lg focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200 resize-none"
                        :placeholder="t('tell_us_about_yourself')"
                        :style="{ color: form.bio_color, fontFamily: form.bio_font }"
                      ></textarea>
                      
                      <!-- Bio Customization -->
                      <div class="flex items-center gap-4 mt-3">
                        <div class="flex items-center gap-2">
                          <label class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
                            {{ t('text_color') }}
                          </label>
                          <input 
                            type="color" 
                            v-model="form.bio_color" 
                            class="w-8 h-8 p-0 border-none bg-transparent rounded cursor-pointer" 
                          />
                        </div>
                        <div class="flex items-center gap-2">
                          <label class="text-xs text-text-light-secondary dark:text-text-dark-secondary">
                            {{ t('font_style') }}
                          </label>
                          <select 
                            v-model="form.bio_font" 
                            class="px-3 py-1 rounded-lg border border-white/20 dark:border-gray-700/50 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm text-sm focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark transition-all duration-200"
                          >
                            <option value="inherit">{{ t('default') }}</option>
                            <option value="Arial, sans-serif">Arial</option>
                            <option value="Georgia, serif">Georgia</option>
                            <option value="'Courier New', monospace">Courier New</option>
                            <option value="'Times New Roman', serif">Times New Roman</option>
                            <option value="Verdana, Geneva, sans-serif">Verdana</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Social Links Section -->
                  <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary flex items-center gap-2">
                      <i class="ri-links-line text-primary-light dark:text-primary-dark"></i>
                      {{ t('social_links') }}
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <!-- Facebook -->
                      <div>
                        <label class="block text-sm font-medium text-text-light-primary dark:text-text-dark-primary mb-2 flex items-center gap-2">
                          <i class="ri-facebook-fill text-blue-600"></i>
                          {{ t('facebook') }}
                        </label>
                        <input
                          type="text"
                          v-model="form.facebook"
                          class="w-full px-4 py-3 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all"
                          :placeholder="t('your_facebook_profile_url')"
                        />
                      </div>

                      <!-- Twitter -->
                      <div>
                        <label class="block text-sm font-medium text-text-light-primary dark:text-text-dark-primary mb-2 flex items-center gap-2">
                          <i class="ri-twitter-fill text-blue-400"></i>
                          {{ t('twitter') }}
                        </label>
                        <input
                          type="text"
                          v-model="form.twitter"
                          class="w-full px-4 py-3 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all"
                          :placeholder="t('your_twitter_handle')"
                        />
                      </div>

                      <!-- Instagram -->
                      <div>
                        <label class="block text-sm font-medium text-text-light-primary dark:text-text-dark-primary mb-2 flex items-center gap-2">
                          <i class="ri-instagram-line text-pink-600"></i>
                          {{ t('instagram') }}
                        </label>
                        <input
                          type="text"
                          v-model="form.instagram"
                          class="w-full px-4 py-3 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all"
                          :placeholder="t('your_instagram_handle')"
                        />
                      </div>

                      <!-- LinkedIn -->
                      <div>
                        <label class="block text-sm font-medium text-text-light-primary dark:text-text-dark-primary mb-2 flex items-center gap-2">
                          <i class="ri-linkedin-fill text-blue-700"></i>
                          {{ t('linkedin') }}
                        </label>
                        <input
                          type="text"
                          v-model="form.linkedin"
                          class="w-full px-4 py-3 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all"
                          :placeholder="t('your_linkedin_profile_url')"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Additional Settings Section - HIDDEN -->
                  <!-- Uncomment to show Media Watermark, Location, and Privacy Settings -->
                  <!--
                  <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-text-light-primary dark:text-text-dark-primary flex items-center gap-2">
                      <i class="ri-settings-3-line text-primary-light dark:text-primary-dark"></i>
                      {{ t('additional_settings') }}
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-medium text-text-light-primary dark:text-text-dark-primary mb-2">
                          {{ t('media_watermark') }}
                        </label>
                        <input
                          type="text"
                          demo="form.media_watermark"
                          class="w-full px-4 py-3 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all"
                          :placeholder="t('your_media_watermark')"
                        />
                      </div>

                      <div class="relative">
                        <label class="block text-sm font-medium text-text-light-primary dark:text-text-dark-primary mb-2">
                          {{ t('location') }}
                        </label>
                        <div class="relative">
                          <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-text-light-secondary dark:text-text-dark-secondary"></i>
                          <input
                            type="text"
                            demo="locationSearchQuery"
                            @input="searchLocations"
                            @focus="showLocationSuggestions = true"
                            class="w-full pl-10 pr-4 py-3 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-primary-light dark: Duncefocus:ring-primary-dark focus:border-transparent transition-all"
                            :placeholder="t('search_for_your_location')"
                          />
                        </div>
                        
                        <div 
                          v-if="showLocationSuggestions && locationSuggestions.length > 0"
                          class="absolute z-10 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg max-h-60 overflow-y-auto"
                        >
                          <div 
                            v-for="location in locationSuggestions" 
                            :key="`${location.country_code}-${location.display_name}`"
                            @click="selectLocation(location)"
                            class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                          >
                            <div class="font-medium text-gray-900 dark:text-white">{{ location.country }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ location.display_name }}</div>
                          </div>
                        </div>

                        <div v-if="loadingLocations" class="flex items-center gap-2 text-sm text-text-light-secondary dark:text-text-dark-secondary mt-2">
                          <i class="ri-loader-4-line animate-spin"></i>
                          {{ t('searching_locations') }}...
                        </div>
                      </div>
                    </div>

                    <div class="bg-surface-light dark:bg-surface-dark rounded-xl p-4">
                      <div class="flex items-center justify-between">
                        <div>
                          <h5 class="font-medium text-text-light-primary dark:text-text-dark-primary">
                            {{ t('privacy_settings') }}
                          </h5>
                          <p class="text-sm text-text-light-secondary dark:text-text-dark-secondary mt-1">
                            {{ t('control_who_can_follow_you') }}
                          </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                          <input
                            type="checkbox"
                            demo="form.can_be_followed"
                            class="sr-only peer"
                          />
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-light/20 dark:peer-focus:ring-primary-dark/20 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-light dark:peer-checked:bg-primary-dark"></div>
                        </label>
                      </div>
                      <p class="text-xs text-text-light-secondary dark:text-text-dark-secondary mt-2">
                        {{ t('allow_others_to_follow_me') }}
                      </p>
                    </div>
                  </div>
                  -->
                </div>

                <!-- Footer -->
                <div class="flex flex-col gap-3 pt-6 border-t border-border-light dark:border-border-dark">
                  <!-- Error Message -->
                  <div v-if="submitError" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                    <div class="flex items-center gap-2">
                      <i class="ri-error-warning-line text-red-500"></i>
                      <p class="text-red-700 dark:text-red-300 text-sm">{{ submitError }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-end gap-3">
                    <button
                      type="button"
                      @click="closeModal"
                      :disabled="isSubmitting"
                      class="px-6 py-3 text-sm font-medium text-text-light-secondary dark:text-text-dark-secondary hover:text-text-light-primary dark:hover:text-text-dark-primary hover:bg-surface-light dark:hover:bg-surface-dark rounded-xl transition-colors disabled:opacity-50"
                    >
                      {{ t('cancel') }}
                    </button>
                    <button
                      type="submit"
                      :disabled="isSubmitting"
                      class="px-6 py-3 text-sm font-medium text-white bg-primary-light dark:bg-primary-dark rounded-xl hover:bg-primary-dark dark:hover:bg-primary-light transition-colors shadow-lg disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                      <i v-if="isSubmitting" class="ri-loader-4-line animate-spin"></i>
                      {{ isSubmitting ? t('saving') : t('save_changes') }}
                    </button>
                  </div>
                </div>
              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from '@headlessui/vue'
import { useUserProfileStore } from '@/stores/userProfileStore'
import { useAuthStore } from '@/stores/authStore'
import { useI18n } from 'vue-i18n'
import axiosInstance from '@/axios'

const props = defineProps({
  show: {
    type: Boolean,
    required: true
  },
  open: {
    type: Boolean,
    required: true
  }
})

const emit = defineEmits(['close', 'update'])

const userProfileStore = useUserProfileStore()
const authStore = useAuthStore()
const i18n = useI18n()

const form = ref({
  name: '',
  email: '',
  username: '',
  handle: '',
  bio: '',
  bio_color: '#222222',
  bio_font: 'inherit',
  avatar: '',
  cover_photo: '',
  facebook: '',
  twitter: '',
  instagram: '',
  linkedin: '',
  media_watermark: '',
  location: '',
  can_be_followed: true,
})

const avatarFile = ref(null)
const coverPhotoFile = ref(null)
const avatarPreview = ref(null)
const coverPhotoPreview = ref(null)

// Location search state
const locationSearchQuery = ref('')
const locationSuggestions = ref([])
const showLocationSuggestions = ref(false)
const loadingLocations = ref(false)

// Form submission state
const isSubmitting = ref(false)
const submitError = ref(null)

let searchTimeout = null

onMounted(() => {
  updateFormWithUserData()
  document.addEventListener('click', handleClickOutside)
})

watch(() => authStore.user, updateFormWithUserData, { deep: true })

function updateFormWithUserData() {
  if (authStore.user) {
    Object.keys(form.value).forEach(key => {
      if (key in authStore.user) {
        form.value[key] = authStore.user[key]
      }
    })
    // Set location search query if user has location
    if (authStore.user.location) {
      locationSearchQuery.value = authStore.user.location
    }
  }
}

const closeModal = () => {
  emit('close')
}

const handleSubmit = async (event) => {
  event.preventDefault()
  
  // Prevent duplicate submissions
  if (isSubmitting.value) {
    console.log('Profile update already in progress, ignoring duplicate submission')
    return
  }
  
  try {
    isSubmitting.value = true
    submitError.value = null
    
    // Generate unique request ID for tracking
    const requestId = `profile-update-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`
    
    // Ensure the form data has the correct structure with proper value handling
    const profileData = {
      name: form.value.name || '',
      username: form.value.username || '',
      bio: form.value.bio || '',
      bio_color: form.value.bio_color || '#222222',
      bio_font: form.value.bio_font || 'inherit',
      facebook: form.value.facebook || '',
      twitter: form.value.twitter || '',
      instagram: form.value.instagram || '',
      linkedin: form.value.linkedin || '',
      // Additional settings commented out - using defaults
      // media_watermark: form.value.media_watermark || '',
      // location: form.value.location || '',
      // can_be_followed: (form.value.can_be_followed !== undefined ? form.value.can_be_followed : true).toString()
    }

    // Only add file fields if they exist
    if (avatarFile.value) {
      profileData.avatar = avatarFile.value
    }
    if (coverPhotoFile.value) {
      profileData.cover_photo = coverPhotoFile.value
    }

    // Location capture commented out (Additional Settings hidden)
    // if (locationSearchQuery.value && !profileData.location) {
    //   profileData.location = locationSearchQuery.value
    // }

    console.log('Submitting profile data with request ID:', requestId, profileData)
    
    // Add timeout handling (60 seconds)
    const updatePromise = userProfileStore.updateUserProfile(profileData)
    const timeoutPromise = new Promise((_, reject) => {
      setTimeout(() => {
        reject(new Error('Request timeout - please try again'))
      }, 60000) // 60 second timeout
    })
    
    // Race between update and timeout
    const updatedProfile = await Promise.race([updatePromise, timeoutPromise])
    
    console.log('Profile update successful:', updatedProfile)
    emit('update', updatedProfile)
    closeModal()
  } catch (error) {
    console.error('Error updating profile:', error)
    
    // Handle specific error types
    if (error.message === 'Request timeout - please try again') {
      submitError.value = 'Update is taking longer than expected. Please try again in a few moments.'
    } else if (error.response?.status === 429) {
      submitError.value = 'An update is already in progress. Please wait a moment and try again.'
    } else if (error.response?.status === 422) {
      submitError.value = error.response?.data?.message || 'Please check your input and try again.'
    } else if (error.response?.status >= 500) {
      submitError.value = 'Server error occurred. Please try again in a few moments.'
    } else {
      submitError.value = error.response?.data?.message || error.message || 'Failed to update profile. Please try again.'
    }
  } finally {
    // Add a small delay before allowing another submission
    setTimeout(() => {
      isSubmitting.value = false
    }, 1000) // 1 second cooldown
  }
}

// Location search functionality
const searchLocations = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }

  if (locationSearchQuery.value.length < 2) {
    locationSuggestions.value = []
    showLocationSuggestions.value = false
    return
  }

  searchTimeout = setTimeout(async () => {
    try {
      loadingLocations.value = true
      const response = await axiosInstance.get('/blocked-locations/search', {
        params: { query: locationSearchQuery.value }
      })
      locationSuggestions.value = response.data
      showLocationSuggestions.value = true
    } catch (error) {
      console.error('Error searching locations:', error)
      locationSuggestions.value = []
    } finally {
      loadingLocations.value = false
    }
  }, 300)
}

// Select a location
const selectLocation = (location) => {
  form.value.location = location.display_name
  locationSearchQuery.value = location.display_name
  locationSuggestions.value = []
  showLocationSuggestions.value = false
}

// Hide suggestions when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showLocationSuggestions.value = false
  }
}

const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    avatarFile.value = file
    avatarPreview.value = URL.createObjectURL(file)
  }
}

const handleCoverPhotoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    coverPhotoFile.value = file
    coverPhotoPreview.value = URL.createObjectURL(file)
  }
}

const t = (key) => {
  return i18n.t(key)
}
</script>