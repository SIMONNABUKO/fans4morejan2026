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
              {{ t('privacy_protection') }}
            </h1>
            <p class="text-text-light-secondary dark:text-text-dark-secondary text-sm mt-0.5">
              Control your privacy and content visibility
            </p>
          </div>
        </div>
        
        <!-- Right Side: Privacy Status -->
        <div class="hidden md:flex items-center gap-4">
          <div class="text-right">
            <div class="text-sm text-text-light-secondary dark:text-text-dark-secondary">Privacy Level</div>
            <div class="text-lg font-bold text-green-600 dark:text-green-400">Protected</div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <!-- Content Blur Filter -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-eye-off-line text-purple-600 dark:text-purple-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('content_blur_filter') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
              {{ t('configures_the_amount_of_blur_on_sensitive_content_in_posts_and_messages') }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ t('enabling_the_content_blur_filter_will_also_show_less_nsfw_content_in_the_fyp') }}
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6">
        <!-- Preview Card -->
        <div class="mb-6">
          <!-- Posts Preview -->
          <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 max-w-md mx-auto">
            <div class="relative">
              <img 
                src="https://hebbkx1anhila5yf.public.blob.vercel-storage.com/image-V2GbJCFRNRmG9XOTBHvpug3sA1jjQD.png" 
                alt="Posts Preview" 
                class="w-full h-32 object-cover rounded-lg"
                :class="getBlurClass"
              />
              <div class="absolute inset-0 flex items-center justify-center">
                <div class="bg-black/50 backdrop-blur-sm text-white px-4 py-2 rounded-full text-sm font-semibold">
                  Posts
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Slider -->
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ t('blur_intensity') }}</span>
            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">{{ blurIntensities[blurLevel] }}</span>
          </div>
          
          <div class="relative">
            <input 
              type="range" 
              min="0" 
              max="3" 
              :value="blurLevel"
              @input="handleBlurChange"
              class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer slider"
            />
            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-3">
              <span class="font-medium">{{ t('off') }}</span>
              <span class="font-medium">{{ t('light') }}</span>
              <span class="font-medium">{{ t('medium') }}</span>
              <span class="font-medium">{{ t('strong') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Discovery Settings -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-eye-line text-blue-600 dark:text-blue-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('discovery_settings') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              {{ t('control_how_your_profile_and_content_appear_to_others') }}
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6 space-y-6">
        <!-- Appear in Suggestions -->
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
              {{ t('appear_in_suggestions') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              {{ t('let_your_profile_appear_in_who_to_follow_section') }}
            </p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input 
              type="checkbox"
              v-model="settingsStore.privacyAndSecurity.appear_in_suggestions"
              @change="updatePrivacySetting('appear_in_suggestions', settingsStore.privacyAndSecurity.appear_in_suggestions)"
              class="sr-only peer"
            >
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
          </label>
        </div>

        <!-- Enable Preview Discovery -->
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
              {{ t('enable_preview_discovery') }}
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              {{ t('allow_preview_videos_to_appear_for_random_users') }}
            </p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input 
              type="checkbox"
              v-model="settingsStore.privacyAndSecurity.enable_preview_discovery"
              @change="updatePrivacySetting('enable_preview_discovery', settingsStore.privacyAndSecurity.enable_preview_discovery)"
              class="sr-only peer"
            >
            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
          </label>
        </div>
      </div>
    </div>

    <!-- Location Blocking -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
      <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-start gap-4">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-xl flex items-center justify-center">
              <i class="ri-map-pin-line text-red-600 dark:text-red-400 text-xl"></i>
            </div>
          </div>
          <div class="flex-1">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ t('add_blocked_location') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">
              {{ t('you_can_block_countries_states_or_cities_from_accessing_your_profile') }}
            </p>
          </div>
        </div>
      </div>
      
      <div class="p-6 space-y-6">
        <!-- Location Search -->
        <div class="relative">
          <div class="relative">
            <i class="ri-search-line absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            <input 
              type="text" 
              v-model="locationSearchQuery"
              @input="searchLocations"
              @focus="showLocationSuggestions = true"
              :placeholder="t('enter_blocked_location')"
              class="w-full pl-12 pr-4 py-4 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-primary-light dark:focus:ring-primary-dark focus:border-transparent transition-all duration-200"
            />
          </div>
        
          <!-- Location Suggestions Dropdown -->
          <div 
            v-if="showLocationSuggestions && locationSuggestions.length > 0"
            class="absolute z-10 w-full mt-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl max-h-60 overflow-y-auto"
          >
            <div 
              v-for="location in locationSuggestions" 
              :key="`${location.country_code}-${location.display_name}`"
              @click="selectLocation(location)"
              class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer border-b border-gray-100 dark:border-gray-600 last:border-b-0 transition-colors duration-200"
            >
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="font-semibold text-gray-900 dark:text-white">
                    {{ getLocationSuggestionTitle(location) }}
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ getLocationSuggestionSubtitle(location) }}
                  </div>
                </div>
                <div class="ml-2">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                    {{ getLocationTypeLabel(getLocationType(location)) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Blocked Locations List -->
        <div v-if="blockedLocations.length > 0" class="space-y-3">
          <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Blocked Locations</h3>
          <div class="space-y-2">
            <div 
              v-for="location in blockedLocations" 
              :key="location.id"
              class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-xl"
            >
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center">
                  <i class="ri-map-pin-line text-red-600 dark:text-red-400 text-sm"></i>
                </div>
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">
                    {{ location.display_name || getLocationDisplayName(location) }}
                  </p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ getLocationTypeLabel(location.location_type) }}
                    <span v-if="location.latitude && location.longitude" class="ml-2">
                      • {{ formatCoordinate(location.latitude) }}, {{ formatCoordinate(location.longitude) }}
                    </span>
                  </p>
                </div>
              </div>
              <button 
                @click="removeBlockedLocation(location.id)"
                class="p-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors duration-200"
              >
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-8">
          <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="ri-map-pin-line text-2xl text-gray-400 dark:text-gray-500"></i>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">No Blocked Locations</h3>
          <p class="text-gray-500 dark:text-gray-400">Add locations to block them from accessing your profile</p>
        </div>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { useBlur } from '@/composables/useBlur'
import { useSettingsStore } from '@/stores/settingsStore'
import axios from '@/axios'

const { t } = useI18n()
const { 
  blurLevel, 
  blurIntensities,
  getBlurClass,
  updateBlur
} = useBlur()

const settingsStore = useSettingsStore()

// Location blocking state
const locationSearchQuery = ref('')
const locationSuggestions = ref([])
const showLocationSuggestions = ref(false)
const loadingLocations = ref(false)
const blockedLocations = ref([])
const loadingBlockedLocations = ref(false)
const removingLocation = ref(null)

let searchTimeout = null

// Handle blur change
const handleBlurChange = async (event) => {
  const level = parseInt(event.target.value)
  try {
    await updateBlur(level)
  } catch (error) {
    console.error('Error updating blur settings:', error)
  }
}

// Search locations with debounce
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
      const response = await axios.get('/blocked-locations/search', {
        params: { query: locationSearchQuery.value }
      })
      
      // Filter out already blocked locations (more granular)
      locationSuggestions.value = response.data.filter(location => {
        // Determine location type
        let locationType = 'country'
        if (location.city && location.city !== 'Unknown') {
          locationType = 'city'
        } else if (location.region && location.region !== 'Unknown') {
          locationType = 'region'
        }

        // Check if this exact location is already blocked
        return !blockedLocations.value.some(blocked => {
          if (locationType === 'city') {
            return blocked.country_code === location.country_code && 
                   blocked.city_name === location.city
          } else if (locationType === 'region') {
            return blocked.country_code === location.country_code && 
                   blocked.region_name === location.region
          } else {
            return blocked.country_code === location.country_code && 
                   blocked.location_type === 'country'
          }
        })
      })
      
      showLocationSuggestions.value = true
    } catch (error) {
      console.error('Error searching locations:', error)
      locationSuggestions.value = []
    } finally {
      loadingLocations.value = false
    }
  }, 300)
}

// Select a location to block
const selectLocation = async (location) => {
  try {
    // Determine location type based on available data
    let locationType = 'country'
    if (location.city && location.city !== 'Unknown') {
      locationType = 'city'
    } else if (location.region && location.region !== 'Unknown') {
      locationType = 'region'
    }

    // Check if this exact location is already blocked
    const isAlreadyBlocked = blockedLocations.value.some(blocked => {
      if (locationType === 'city') {
        return blocked.country_code === location.country_code && 
               blocked.city_name === location.city
      } else if (locationType === 'region') {
        return blocked.country_code === location.country_code && 
               blocked.region_name === location.region
      } else {
        return blocked.country_code === location.country_code && 
               blocked.location_type === 'country'
      }
    })
    
    if (isAlreadyBlocked) {
      console.log('Location already blocked:', location.display_name || location.country)
      return
    }

    await axios.post('/blocked-locations', {
      country_code: location.country_code,
      country_name: location.country,
      location_type: locationType,
      region_name: location.region !== 'Unknown' ? location.region : null,
      city_name: location.city !== 'Unknown' ? location.city : null,
      latitude: location.latitude,
      longitude: location.longitude,
      display_name: location.display_name
    })

    // Clear search and hide suggestions
    locationSearchQuery.value = ''
    locationSuggestions.value = []
    showLocationSuggestions.value = false

    // Refresh blocked locations list
    await fetchBlockedLocations()
  } catch (error) {
    console.error('Error blocking location:', error)
  }
}

// Remove a blocked location
const removeBlockedLocation = async (locationId) => {
  try {
    removingLocation.value = locationId
    await axios.delete(`/blocked-locations/${locationId}`)
    await fetchBlockedLocations()
  } catch (error) {
    console.error('Error removing blocked location:', error)
  } finally {
    removingLocation.value = null
  }
}

// Fetch blocked locations
const fetchBlockedLocations = async () => {
  try {
    loadingBlockedLocations.value = true
    const response = await axios.get('/blocked-locations')
    blockedLocations.value = response.data
  } catch (error) {
    console.error('Error fetching blocked locations:', error)
    blockedLocations.value = []
  } finally {
    loadingBlockedLocations.value = false
  }
}

// Helper function to get location display name
const getLocationDisplayName = (location) => {
  if (location.city_name && location.city_name !== 'Unknown') {
    return `${location.city_name}, ${location.country_name}`
  } else if (location.region_name && location.region_name !== 'Unknown') {
    return `${location.region_name}, ${location.country_name}`
  } else {
    return location.country_name
  }
}

// Helper function to get location type label
const getLocationTypeLabel = (locationType) => {
  switch (locationType) {
    case 'city':
      return 'City'
    case 'region':
      return 'Region'
    case 'country':
      return 'Country'
    default:
      return 'Location'
  }
}

// Helper function to get location suggestion title
const getLocationSuggestionTitle = (location) => {
  if (location.city && location.city !== 'Unknown') {
    return location.city
  } else if (location.region && location.region !== 'Unknown') {
    return location.region
  } else {
    return location.country
  }
}

// Helper function to get location suggestion subtitle
const getLocationSuggestionSubtitle = (location) => {
  const parts = []
  
  if (location.region && location.region !== 'Unknown' && location.region !== location.city) {
    parts.push(location.region)
  }
  
  if (location.country && location.country !== 'Unknown') {
    parts.push(location.country)
  }
  
  return parts.join(', ')
}

// Helper function to determine location type from location data
const getLocationType = (location) => {
  if (location.city && location.city !== 'Unknown') {
    return 'city'
  } else if (location.region && location.region !== 'Unknown') {
    return 'region'
  } else {
    return 'country'
  }
}

// Helper function to format coordinates safely
const formatCoordinate = (coordinate) => {
  if (coordinate === null || coordinate === undefined || coordinate === '') {
    return '0.0000'
  }
  
  const num = parseFloat(coordinate)
  if (isNaN(num)) {
    return '0.0000'
  }
  
  return num.toFixed(4)
}

// Hide suggestions when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showLocationSuggestions.value = false
  }
}

// Update privacy setting
const updatePrivacySetting = async (key, value) => {
  try {
    console.log('🔄 Updating privacy setting:', { key, value })
    console.log('📊 Current settings before update:', JSON.stringify(settingsStore.privacyAndSecurity, null, 2))
    console.log('🔍 Specific key value before update:', settingsStore.privacyAndSecurity[key])
    
    await settingsStore.updateCategorySetting('privacyAndSecurity', key, value)
    
    console.log('✅ Settings updated successfully')
    console.log('📊 Current settings after update:', JSON.stringify(settingsStore.privacyAndSecurity, null, 2))
    console.log('🔍 Specific key value after update:', settingsStore.privacyAndSecurity[key])
  } catch (error) {
    console.error('❌ Error updating privacy setting:', error)
  }
}

onMounted(async () => {
  // Don't call fetchAllSettings here as it can override recent updates
  // await settingsStore.fetchAllSettings()
  await fetchBlockedLocations()
  document.addEventListener('click', handleClickOutside)
})

// Clean up event listener
import { onBeforeUnmount } from 'vue'
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
})
</script>

<style scoped>
.slider {
  background: linear-gradient(to right, #e5e7eb 0%, #e5e7eb 25%, #3b82f6 25%, #3b82f6 100%);
}

.slider::-webkit-slider-thumb {
  appearance: none;
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.slider::-moz-range-thumb {
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: none;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.dark .slider {
  background: linear-gradient(to right, #374151 0%, #374151 25%, #3b82f6 25%, #3b82f6 100%);
}
</style> 